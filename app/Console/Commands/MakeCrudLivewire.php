<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeCrudLivewire extends Command
{
    protected $signature = 'make:crud-livewire {model} {prefix}';
    protected $description = 'Create a full CRUD with Livewire for a given model and prefix';

    public function handle()
    {
        $model = $this->argument('model');
        $prefix = $this->argument('prefix');
        $namespacePrefix = Str::studly($prefix); // Admin or Dr

        // مسیرها و نام‌ها
        $modelLower = Str::lower($model);
        $modelKebab = Str::kebab($model);
        $modelPlural = Str::plural($modelLower);
        $modelPluralKebab = Str::kebab(Str::plural($model));
        $modelStudly = Str::studly($model);

        // بررسی وجود مدل و ساخت آن در صورت نیاز
        $this->createModelIfNotExists($model, $namespacePrefix);

        // ساخت کنترلر
        $this->createController($model, $namespacePrefix, $modelKebab, $modelPluralKebab);

        // ساخت فایل‌های Livewire
        $this->createLivewireComponents($model, $namespacePrefix);

        // ساخت ویوهای Blade
        $this->createViews($model, $prefix);

        // اضافه کردن مسیرها به web.php
        $this->appendRoutes($model, $prefix);

        // ساخت فایل CSS
        $this->createCssFile($model, $prefix);

        $this->info("CRUD for {$model} with prefix {$prefix} created successfully!");
    }

    protected function createModelIfNotExists($model, $namespacePrefix)
    {
        $modelPath = app_path("Models/{$namespacePrefix}/{$model}.php");
        $modelsDir = app_path('Models');

        // جستجو توی کل پوشه Models برای پیدا کردن فایل مدل
        $modelExists = false;
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($modelsDir));
        foreach ($files as $file) {
            if ($file->isFile() && $file->getFilename() === "{$model}.php") {
                $modelExists = true;
                break;
            }
        }

        if (!$modelExists) {
            // اگه مدل وجود نداشت، اون رو می‌سازیم
            $stub = File::get(base_path('stubs/model.stub'));
            $stub = str_replace(
                ['{{namespace}}', '{{class}}'],
                ["App\\Models\\{$namespacePrefix}", $model],
                $stub
            );
            File::ensureDirectoryExists(app_path("Models/{$namespacePrefix}"));
            File::put($modelPath, $stub);

            // ساخت مایگریشن
            $this->call('make:migration', [
                'name' => "create_" . Str::plural(Str::snake($model)) . "_table",
                '--create' => Str::plural(Str::snake($model)),
            ]);
        } else {
            $this->info("Model {$model} already exists somewhere in app/Models. Skipping model and migration creation.");
        }
    }

    protected function createController($model, $namespacePrefix, $modelKebab, $modelPluralKebab)
    {
        $controllerPath = app_path("Http/Controllers/{$namespacePrefix}/Panel/{$model}/{$model}Controller.php");
        $stub = File::get(base_path('stubs/controller.crud.stub'));
        $stub = str_replace(
            [
                '{{namespace}}',
                '{{namespacePrefix}}',
                '{{class}}',
                '{{modelLower}}',
                '{{prefix}}',
                '{{modelPlural}}',
                '{{modelKebab}}',
                '{{modelPluralKebab}}'
            ],
            [
                "App\\Http\\Controllers\\{$namespacePrefix}\\Panel\\{$model}",
                $namespacePrefix,
                "{$model}Controller",
                Str::lower($model),
                Str::kebab($namespacePrefix),
                Str::plural(Str::lower($model)),
                $modelKebab,
                $modelPluralKebab
            ],
            $stub
        );
        File::ensureDirectoryExists(dirname($controllerPath));
        File::put($controllerPath, $stub);
    }

    protected function createLivewireComponents($model, $namespacePrefix)
    {
        $modelLower = Str::lower($model);
        $modelKebab = Str::kebab($model);
        $modelPlural = Str::plural($modelLower);
        $modelPluralKebab = Str::kebab(Str::plural($model));
        $prefixKebab = Str::kebab($namespacePrefix);

        // مسیر فایل‌های PHP لایووایر
        $livewirePath = app_path("Livewire/{$namespacePrefix}/Panel/{$model}");
        File::ensureDirectoryExists($livewirePath);

        // مسیر ویوهای Livewire
        $livewireViewPath = resource_path("views/livewire/{$prefixKebab}/panel/{$modelPluralKebab}");
        File::ensureDirectoryExists($livewireViewPath);

        // حذف هرگونه فایل camelCase احتمالی
        $camelCaseFiles = [
            Str::camel("{$modelLower}-list") . ".blade.php",
            Str::camel("{$modelLower}-create") . ".blade.php",
            Str::camel("{$modelLower}-edit") . ".blade.php",
        ];
        foreach ($camelCaseFiles as $file) {
            $filePath = "{$livewireViewPath}/{$file}";
            if (File::exists($filePath)) {
                File::delete($filePath);
                $this->info("Deleted camelCase file: {$filePath}");
            }
        }

        // جایگزینی محتوای کامپوننت‌ها
        $this->replaceLivewireStubs($model, $namespacePrefix);
    }

    protected function replaceLivewireStubs($model, $namespacePrefix)
    {
        $modelLower = Str::lower($model);
        $modelKebab = Str::kebab($model);
        $modelPlural = Str::plural($modelLower);
        $modelPluralKebab = Str::kebab(Str::plural($model));
        $modelStudly = Str::studly($model);
        $prefixKebab = Str::kebab($namespacePrefix);

        // مسیر فایل‌های PHP لایووایر
        $livewirePath = app_path("Livewire/{$namespacePrefix}/Panel/{$model}");
        File::ensureDirectoryExists($livewirePath);

        // List Component
        $listPath = "{$livewirePath}/{$model}List.php";
        $listStub = File::get(base_path('stubs/livewire.list.stub'));
        $listStub = str_replace(
            ['{{namespace}}', '{{class}}', '{{model}}', '{{modelLower}}', '{{modelKebab}}', '{{modelPlural}}', '{{modelPluralKebab}}', '{{namespacePrefix}}', '{{prefix}}'],
            ["App\\Livewire\\{$namespacePrefix}\\Panel\\{$model}", "{$model}List", $modelStudly, $modelLower, $modelKebab, $modelPlural, $modelPluralKebab, $namespacePrefix, $prefixKebab],
            $listStub
        );
        File::put($listPath, $listStub);
        $this->info("Created Livewire component: {$listPath}");

        // Create Component
        $createPath = "{$livewirePath}/{$model}Create.php";
        $createStub = File::get(base_path('stubs/livewire.create.stub'));
        $createStub = str_replace(
            ['{{namespace}}', '{{class}}', '{{model}}', '{{modelLower}}', '{{modelKebab}}', '{{modelPlural}}', '{{modelPluralKebab}}', '{{namespacePrefix}}', '{{prefix}}'],
            ["App\\Livewire\\{$namespacePrefix}\\Panel\\{$model}", "{$model}Create", $modelStudly, $modelLower, $modelKebab, $modelPlural, $modelPluralKebab, $namespacePrefix, $prefixKebab],
            $createStub
        );
        File::put($createPath, $createStub);
        $this->info("Created Livewire component: {$createPath}");

        // Edit Component
        $editPath = "{$livewirePath}/{$model}Edit.php";
        $editStub = File::get(base_path('stubs/livewire.edit.stub'));
        $editStub = str_replace(
            ['{{namespace}}', '{{class}}', '{{model}}', '{{modelLower}}', '{{modelKebab}}', '{{modelPlural}}', '{{modelPluralKebab}}', '{{namespacePrefix}}', '{{prefix}}'],
            ["App\\Livewire\\{$namespacePrefix}\\Panel\\{$model}", "{$model}Edit", $modelStudly, $modelLower, $modelKebab, $modelPlural, $modelPluralKebab, $namespacePrefix, $prefixKebab],
            $editStub
        );
        File::put($editPath, $editStub);
        $this->info("Created Livewire component: {$editPath}");
    }

    protected function createViews($model, $prefix)
    {
        $modelLower = Str::lower($model);
        $modelKebab = Str::kebab($model);
        $modelPlural = Str::plural($modelLower);
        $modelPluralKebab = Str::kebab(Str::plural($model));
        $prefixKebab = Str::kebab($prefix);

        // ویوهای کنترلر
        $viewPath = resource_path("views/{$prefixKebab}/panel/{$modelPluralKebab}");
        File::ensureDirectoryExists($viewPath);

        $indexStub = File::get(base_path('stubs/view.index.stub'));
        $indexStub = str_replace(
            ['{{prefix}}', '{{modelPlural}}', '{{modelPluralKebab}}', '{{modelKebab}}'],
            [$prefixKebab, $modelPlural, $modelPluralKebab, $modelKebab],
            $indexStub
        );
        File::put("{$viewPath}/index.blade.php", $indexStub);
        $this->info("Created controller view: {$viewPath}/index.blade.php");

        $createStub = File::get(base_path('stubs/view.create.stub'));
        $createStub = str_replace(
            ['{{prefix}}', '{{modelPlural}}', '{{modelPluralKebab}}', '{{modelKebab}}'],
            [$prefixKebab, $modelPlural, $modelPluralKebab, $modelKebab],
            $createStub
        );
        File::put("{$viewPath}/create.blade.php", $createStub);
        $this->info("Created controller view: {$viewPath}/create.blade.php");

        $editStub = File::get(base_path('stubs/view.edit.stub'));
        $editStub = str_replace(
            ['{{prefix}}', '{{modelPlural}}', '{{modelPluralKebab}}', '{{modelKebab}}'],
            [$prefixKebab, $modelPlural, $modelPluralKebab, $modelKebab],
            $editStub
        );
        File::put("{$viewPath}/edit.blade.php", $editStub);
        $this->info("Created controller view: {$viewPath}/edit.blade.php");

        // ویوهای Livewire
        $livewireViewPath = resource_path("views/livewire/{$prefixKebab}/panel/{$modelPluralKebab}");
        File::ensureDirectoryExists($livewireViewPath);

        // حذف فایل‌های camelCase احتمالی
        $camelCaseFiles = [
            Str::camel("{$modelLower}-list") . ".blade.php",
            Str::camel("{$modelLower}-create") . ".blade.php",
            Str::camel("{$modelLower}-edit") . ".blade.php",
        ];
        foreach ($camelCaseFiles as $file) {
            $filePath = "{$livewireViewPath}/{$file}";
            if (File::exists($filePath)) {
                File::delete($filePath);
                $this->info("Deleted camelCase file: {$filePath}");
            }
        }

        // ساخت ویوهای kebab-case
        $listViewStub = File::get(base_path('stubs/livewire.view.list.stub'));
        $listViewStub = str_replace(
            ['{{prefix}}', '{{modelPlural}}', '{{modelPluralKebab}}', '{{modelKebab}}'],
            [$prefixKebab, $modelPlural, $modelPluralKebab, $modelKebab],
            $listViewStub
        );
        File::put("{$livewireViewPath}/{$modelKebab}-list.blade.php", $listViewStub);
        $this->info("Created Livewire view: {$livewireViewPath}/{$modelKebab}-list.blade.php");

        $createViewStub = File::get(base_path('stubs/livewire.view.create.stub'));
        $createViewStub = str_replace(
            ['{{prefix}}', '{{modelPlural}}', '{{modelPluralKebab}}', '{{modelKebab}}'],
            [$prefixKebab, $modelPlural, $modelPluralKebab, $modelKebab],
            $createViewStub
        );
        File::put("{$livewireViewPath}/{$modelKebab}-create.blade.php", $createViewStub);
        $this->info("Created Livewire view: {$livewireViewPath}/{$modelKebab}-create.blade.php");

        $editViewStub = File::get(base_path('stubs/livewire.view.edit.stub'));
        $editViewStub = str_replace(
            ['{{prefix}}', '{{modelPlural}}', '{{modelPluralKebab}}', '{{modelKebab}}'],
            [$prefixKebab, $modelPlural, $modelPluralKebab, $modelKebab],
            $editViewStub
        );
        File::put("{$livewireViewPath}/{$modelKebab}-edit.blade.php", $editViewStub);
        $this->info("Created Livewire view: {$livewireViewPath}/{$modelKebab}-edit.blade.php");
    }

    protected function appendRoutes($model, $prefix)
    {
        $modelLower = Str::lower($model);
        $modelKebab = Str::kebab($model);
        $modelPlural = Str::plural($modelLower);
        $modelPluralKebab = Str::kebab(Str::plural($model));
        $namespacePrefix = Str::studly($prefix);
        $prefixKebab = Str::kebab($prefix);

        $webFile = base_path('routes/web.php');
        if (!File::exists($webFile)) {
            $this->error("File $webFile does not exist!");
            return;
        }
        $webContent = File::get($webFile);

        $fullNamespace = "App\\Http\\Controllers\\{$namespacePrefix}\\Panel\\{$model}\\{$model}Controller";
        $routeContent = "    Route::prefix('{$modelPluralKebab}')->group(function () {\n" .
                        "        Route::get('/', [\\{$fullNamespace}::class, 'index'])->name('{$prefixKebab}.panel.{$modelPluralKebab}.index');\n" .
                        "        Route::get('/create', [\\{$fullNamespace}::class, 'create'])->name('{$prefixKebab}.panel.{$modelPluralKebab}.create');\n" .
                        "        Route::get('/edit/{id}', [\\{$fullNamespace}::class, 'edit'])->name('{$prefixKebab}.panel.{$modelPluralKebab}.edit');\n" .
                        "    });\n";

        $groupPattern = "/Route::prefix\s*\(\s*'$prefixKebab'\s*\)\s*(?:\r?\n\s*)?->namespace\s*\(\s*'$namespacePrefix'\s*\)\s*(?:->middleware\s*\(\s*'[a-zA-Z:]+'\s*\)\s*(?:\r?\n\s*)?)?->group\s*\(\s*function\s*\(\s*\)\s*\{(.*?)\}\s*\);/s";

        if (preg_match($groupPattern, $webContent, $matches)) {
            $groupContent = $matches[1];
            if (strpos($groupContent, "Route::prefix('{$modelPluralKebab}')->group(function () {") === false) {
                $newGroupContent = "\n" . $routeContent . "\n" . trim($groupContent);
                $replacement = "Route::prefix('$prefixKebab')\n    ->namespace('$namespacePrefix')\n";
                if ($prefixKebab === 'admin') {
                    $replacement .= "    ->middleware('manager')\n";
                }
                $replacement .= "    ->group(function () {{$newGroupContent}\n});";

                $webContent = preg_replace($groupPattern, $replacement, $webContent);
                File::put($webFile, $webContent);
                $this->info("Routes for $modelPluralKebab prepended to $prefixKebab group successfully.");
            } else {
                $this->info("Routes for $modelPluralKebab already exist in $prefixKebab group. Skipping.");
            }
        } else {
            $this->error("Could not find $prefixKebab group in routes/web.php.");
        }
    }

    protected function createCssFile($model, $prefix)
    {
        $modelKebab = Str::kebab($model);
        $prefixKebab = Str::kebab($prefix);
        $cssPath = public_path("{$prefixKebab}-assets/css/panel/{$modelKebab}");
        File::ensureDirectoryExists($cssPath);
        $cssStub = File::get(base_path('stubs/css.stub'));
        $cssStub = str_replace(
            ['{{modelKebab}}'],
            [$modelKebab],
            $cssStub
        );
        File::put("{$cssPath}/{$modelKebab}.css", $cssStub);
    }
}

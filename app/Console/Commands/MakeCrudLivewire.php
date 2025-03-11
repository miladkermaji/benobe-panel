<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeCrudLivewire extends Command
{
    protected $signature   = 'make:crud-livewire {model} {prefix=admin}';
    protected $description = 'Generate a full CRUD with Livewire for a given model and prefix (admin/dr)';

    public function handle()
    {
        $model           = $this->argument('model');
        $prefix          = $this->argument('prefix');
        $modelLower      = Str::lower($model);
        $modelPlural     = Str::plural($modelLower);
        $namespacePrefix = $prefix === 'admin' ? 'Admin' : 'Dr';

        // تولید مدل (در مسیر ریشه Models)
        $this->createModelIfNotExists($model);

        // تولید مایگریشن
        $this->createMigrationIfNotExists($modelLower, $modelPlural);

        // تولید کنترلر
        $this->createController($model, $namespacePrefix, $prefix);

        // تولید کامپوننت‌های Livewire در app/Livewire
        $this->createLivewireComponents($model, $namespacePrefix, $prefix);

        // تولید فایل‌های Blade در resources/views/livewire
        $this->createBladeFiles($model, $prefix);

        // تولید فایل CSS
        $this->createCssFile($modelLower, $prefix);

        // اضافه کردن روت‌ها (با بررسی وجود prefix)
        $this->appendRoutes($model, $prefix, $namespacePrefix);

        $this->info("CRUD Livewire for {$model} with prefix {$prefix} created successfully!");
    }

    protected function createModelIfNotExists($model)
    {
        $modelPath = app_path("Models/{$model}.php");
        if (! File::exists($modelPath)) {
            $stub = File::get(base_path('stubs/model.stub'));
            $stub = str_replace(
                ['{{ namespace }}', '{{ class }}', '{{ fillable }}'],
                ["App\\Models", $model, $this->getFillableFields()],
                $stub
            );
            File::put($modelPath, $stub);
            $this->info("Model {$model} created.");
        } else {
            $this->info("Model {$model} already exists, skipping...");
        }
    }

    protected function createMigrationIfNotExists($modelLower, $modelPlural)
    {
        $migrationPath      = database_path("migrations/" . date('Y_m_d_His') . "_create_{$modelPlural}_table.php");
        $existingMigrations = File::glob(database_path('migrations/*_create_' . $modelPlural . '_table.php'));
        if (empty($existingMigrations)) {
            $stub = File::get(base_path('stubs/migration.stub'));
            $stub = str_replace(
                ['{{ table }}', '{{ fields }}'],
                [$modelPlural, $this->getMigrationFields()],
                $stub
            );
            File::put($migrationPath, $stub);
            $this->info("Migration for {$modelPlural} created.");
        } else {
            $this->info("Migration for {$modelPlural} already exists, skipping...");
        }
    }

    protected function createController($model, $namespacePrefix, $prefix)
    {
        $controllerPath = app_path("Http/Controllers/{$namespacePrefix}/Panel/" . Str::plural($model) . "/{$model}Controller.php");
        File::ensureDirectoryExists(dirname($controllerPath));
        $stub = File::get(base_path('stubs/controller.stub'));
        $stub = str_replace(
            [
                '{{ namespace }}',
                '{{ namespacePrefix }}',
                '{{ class }}',
                '{{ prefix }}',
                '{{ modelLowerPlural }}',
                '{{ model }}',
            ],
            [
                "App\\Http\\Controllers\\{$namespacePrefix}\\Panel\\" . Str::plural($model),
                $namespacePrefix,
                "{$model}Controller",
                $prefix,
                Str::plural(Str::lower($model)),
                $model,
            ],
            $stub
        );
        File::put($controllerPath, $stub);
        $this->info("Controller created at {$controllerPath}");
    }

    protected function createLivewireComponents($model, $namespacePrefix, $prefix)
    {
        $modelLower  = Str::lower($model);
        $modelPlural = Str::plural($modelLower);
        $components  = [
            'List'   => 'list.stub',
            'Create' => 'create.stub',
            'Edit'   => 'edit.stub',
        ];

        foreach ($components as $type => $stubFile) {
            $className = "{$model}{$type}";
            $path      = app_path("Livewire/{$namespacePrefix}/Panel/" . Str::plural($model) . "/{$className}.php");
            File::ensureDirectoryExists(dirname($path));
            $stub = File::get(base_path("stubs/livewire-{$stubFile}"));
            $stub = str_replace(
                [
                    '{{ namespace }}',
                    '{{ class }}',
                    '{{ model }}',
                    '{{ modelLower }}',
                    '{{ modelPlural }}',
                    '{{ prefix }}',
                    '{{ namespacePrefix }}',
                ],
                [
                    "App\\Livewire\\{$namespacePrefix}\\Panel\\" . Str::plural($model),
                    $className,
                    $model,
                    $modelLower,
                    $modelPlural,
                    $prefix,
                    $namespacePrefix,
                ],
                $stub
            );
            File::put($path, $stub);
            $this->info("Livewire component {$className} created at {$path}");
        }
    }

    protected function createBladeFiles($model, $prefix)
    {
        $modelLower        = Str::lower($model);
        $modelPlural       = Str::plural($modelLower);
        $livewireViewsPath = resource_path("views/livewire/{$prefix}/panel/" . Str::plural($modelLower));
        $viewsPath         = resource_path("views/{$prefix}/panel/" . Str::plural($modelLower));
        File::ensureDirectoryExists($livewireViewsPath);
        File::ensureDirectoryExists($viewsPath);

        // تولید ویوهای Livewire
        $livewireViews = [
            'list'   => 'livewire-list.stub',
            'create' => 'livewire-create.stub',
            'edit'   => 'livewire-edit.stub',
        ];
        foreach ($livewireViews as $type => $stubFile) {
            $stub = File::get(base_path("stubs/{$stubFile}"));
            $stub = str_replace(
                ['{{ model }}', '{{ modelLower }}', '{{ modelPlural }}', '{{ prefix }}'],
                [$model, $modelLower, $modelPlural, $prefix],
                $stub
            );
            File::put("{$livewireViewsPath}/{$modelLower}-{$type}.blade.php", $stub);
            $this->info("Livewire Blade view {$modelLower}-{$type} created at {$livewireViewsPath}");
        }

        // تولید تمپلیت اصلی
        $templateStub = File::get(base_path('stubs/template.stub'));
        $templateStub = str_replace(
            ['{{ modelPlural }}', '{{ modelLower }}', '{{ prefix }}'],
            [$modelPlural, $modelLower, $prefix],
            $templateStub
        );
        File::put("{$viewsPath}/index.blade.php", $templateStub);
        $this->info("Main Blade template created at {$viewsPath}/index.blade.php");
    }

    protected function createCssFile($modelLower, $prefix)
    {
        $cssPath = public_path("{$prefix}-assets/css/panel/" . Str::plural($modelLower) . "/{$modelLower}.css");
        File::ensureDirectoryExists(dirname($cssPath));
        File::copy(base_path('stubs/styles.stub'), $cssPath);
        $this->info("CSS file created at {$cssPath}");
    }

    protected function appendRoutes($model, $prefix, $namespacePrefix)
    {
        $routesFile  = base_path('routes/web.php');
        $modelLower  = Str::lower($model);
        $modelPlural = Str::plural($modelLower);
        $routeStub   = File::get(base_path('stubs/routes.stub'));

        $existingContent = File::get($routesFile);
        $prefixExists    = Str::contains($existingContent, "Route::prefix('{$prefix}')");

        $routes = str_replace(
            ['{{ prefix }}', '{{ namespace }}', '{{ model }}', '{{ modelLower }}', '{{ modelPlural }}'],
            [$prefix, $namespacePrefix, $model, $modelLower, $modelPlural],
            $routeStub
        );

        if ($prefixExists) {
            // اگر prefix وجود دارد، روت‌ها را داخل گروه prefix اضافه کن
            $pattern = "/Route::prefix\('{$prefix}'\)(.*?)->group\(function\s*\(\)\s*{(.*?)}\);/s";
            if (preg_match($pattern, $existingContent, $matches)) {
                $existingGroup   = $matches[0];
                $groupContent    = $matches[2];
                $newGroupContent = $groupContent . "\n" . trim($routes);
                $newGroup        = str_replace($groupContent, $newGroupContent, $existingGroup);
                $newContent      = str_replace($existingGroup, $newGroup, $existingContent);
                File::put($routesFile, $newContent);
                $this->info("Routes appended inside existing '{$prefix}' prefix.");
            }
        } else {
            // اگر prefix وجود ندارد، گروه جدید اضافه کن
            File::append($routesFile, "\n" . $routes);
            $this->info("New route group with prefix '{$prefix}' created.");
        }
    }

    protected function getFillableFields()
    {
        return "['name', 'description', 'is_active']";
    }

    protected function getMigrationFields()
    {
        return "\$table->id();\n" .
            "            \$table->string('name');\n" .
            "            \$table->text('description')->nullable();\n" .
            "            \$table->boolean('is_active')->default(true);\n" .
            "            \$table->timestamps();";
    }
}

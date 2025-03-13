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
        $namespacePrefix = ucfirst($prefix); // Admin or Dr

        // مسیرها و نام‌ها
        $modelLower = Str::lower($model);
        $modelPlural = Str::plural($modelLower);
        $modelStudly = Str::studly($model);

        // بررسی وجود مدل و ساخت آن در صورت نیاز
        $this->createModelIfNotExists($model, $namespacePrefix);

        // ساخت کنترلر
        $this->createController($model, $namespacePrefix);

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
        if (!File::exists($modelPath)) {
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
                'name' => "create_{$model}_table",
                '--create' => Str::plural(Str::lower($model)),
            ]);
        }
    }

    protected function createController($model, $namespacePrefix)
    {
        $controllerPath = app_path("Http/Controllers/{$namespacePrefix}/Panel/{$model}/{$model}Controller.php");
        $stub = File::get(base_path('stubs/controller.crud.stub'));
        $stub = str_replace(
            ['{{namespace}}', '{{class}}', '{{modelLower}}', '{{prefix}}'],
            ["App\\Http\\Controllers\\{$namespacePrefix}", "{$model}Controller", Str::lower($model), Str::lower($namespacePrefix)],
            $stub
        );
        File::ensureDirectoryExists(dirname($controllerPath));
        File::put($controllerPath, $stub);
    }

    protected function createLivewireComponents($model, $namespacePrefix)
    {
        $modelLower = Str::lower($model);
        $modelPlural = Str::plural($modelLower);

        // ساخت کامپوننت List
        $this->call('make:livewire', [
            'name' => "{$namespacePrefix}.Panel.{$modelPlural}.{$model}List",
        ]);

        // ساخت کامپوننت Create
        $this->call('make:livewire', [
            'name' => "{$namespacePrefix}.Panel.{$modelPlural}.{$model}Create",
        ]);

        // ساخت کامپوننت Edit
        $this->call('make:livewire', [
            'name' => "{$namespacePrefix}.Panel.{$modelPlural}.{$model}Edit",
        ]);

        // جایگزینی محتوای کامپوننت‌ها
        $this->replaceLivewireStubs($model, $namespacePrefix);
    }

    protected function replaceLivewireStubs($model, $namespacePrefix)
    {
        $modelLower = Str::lower($model);
        $modelPlural = Str::plural($modelLower);
        $modelStudly = Str::studly($model);

        // List Component
        $listPath = app_path("Livewire/{$namespacePrefix}/Panel/{$modelPlural}/{$model}List.php");
        $listStub = File::get(base_path('stubs/livewire.list.stub'));
        $listStub = str_replace(
            ['{{namespace}}', '{{class}}', '{{model}}', '{{modelLower}}', '{{modelPlural}}'],
            ["App\\Livewire\\{$namespacePrefix}\\Panel\\{$modelPlural}", "{$model}List", $modelStudly, $modelLower, $modelPlural],
            $listStub
        );
        File::put($listPath, $listStub);

        // Create Component
        $createPath = app_path("Livewire/{$namespacePrefix}/Panel/{$modelPlural}/{$model}Create.php");
        $createStub = File::get(base_path('stubs/livewire.create.stub'));
        $createStub = str_replace(
            ['{{namespace}}', '{{class}}', '{{model}}', '{{modelLower}}', '{{modelPlural}}'],
            ["App\\Livewire\\{$namespacePrefix}\\Panel\\{$modelPlural}", "{$model}Create", $modelStudly, $modelLower, $modelPlural],
            $createStub
        );
        File::put($createPath, $createStub);

        // Edit Component
        $editPath = app_path("Livewire/{$namespacePrefix}/Panel/{$modelPlural}/{$model}Edit.php");
        $editStub = File::get(base_path('stubs/livewire.edit.stub'));
        $editStub = str_replace(
            ['{{namespace}}', '{{class}}', '{{model}}', '{{modelLower}}', '{{modelPlural}}'],
            ["App\\Livewire\\{$namespacePrefix}\\Panel\\{$modelPlural}", "{$model}Edit", $modelStudly, $modelLower, $modelPlural],
            $editStub
        );
        File::put($editPath, $editStub);
    }

    protected function createViews($model, $prefix)
    {
        $modelLower = Str::lower($model);
        $modelPlural = Str::plural($modelLower);

        // ویوهای کنترلر
        $viewPath = resource_path("views/{$prefix}/panel/{$modelPlural}");
        File::ensureDirectoryExists($viewPath);

        $indexStub = File::get(base_path('stubs/view.index.stub'));
        $indexStub = str_replace(
            ['{{prefix}}', '{{modelPlural}}', '{{model}}'],
            [$prefix, $modelPlural, $modelLower],
            $indexStub
        );
        File::put("{$viewPath}/index.blade.php", $indexStub);

        $createStub = File::get(base_path('stubs/view.create.stub'));
        $createStub = str_replace(
            ['{{prefix}}', '{{modelPlural}}', '{{model}}'],
            [$prefix, $modelPlural, $modelLower],
            $createStub
        );
        File::put("{$viewPath}/create.blade.php", $createStub);

        $editStub = File::get(base_path('stubs/view.edit.stub'));
        $editStub = str_replace(
            ['{{prefix}}', '{{modelPlural}}', '{{model}}'],
            [$prefix, $modelPlural, $modelLower],
            $editStub
        );
        File::put("{$viewPath}/edit.blade.php", $editStub);

        // ویوهای Livewire
        $livewireViewPath = resource_path("views/livewire/{$prefix}/panel/{$modelPlural}");
        File::ensureDirectoryExists($livewireViewPath);

        $listViewStub = File::get(base_path('stubs/livewire.view.list.stub'));
        $listViewStub = str_replace(
            ['{{prefix}}', '{{modelPlural}}', '{{model}}'],
            [$prefix, $modelPlural, $modelLower],
            $listViewStub
        );
        File::put("{$livewireViewPath}/{$modelLower}-list.blade.php", $listViewStub);

        $createViewStub = File::get(base_path('stubs/livewire.view.create.stub'));
        $createViewStub = str_replace(
            ['{{prefix}}', '{{modelPlural}}', '{{model}}'],
            [$prefix, $modelPlural, $modelLower],
            $createViewStub
        );
        File::put("{$livewireViewPath}/{$modelLower}-create.blade.php", $createViewStub);

        $editViewStub = File::get(base_path('stubs/livewire.view.edit.stub'));
        $editViewStub = str_replace(
            ['{{prefix}}', '{{modelPlural}}', '{{model}}'],
            [$prefix, $modelPlural, $modelLower],
            $editViewStub
        );
        File::put("{$livewireViewPath}/{$modelLower}-edit.blade.php", $editViewStub);
    }

    protected function appendRoutes($model, $prefix)
    {
        $modelLower = Str::lower($model);
        $modelPlural = Str::plural($modelLower);
        $namespacePrefix = ucfirst($prefix);

        $routeStub = File::get(base_path('stubs/routes.stub'));
        $routeStub = str_replace(
            ['{{prefix}}', '{{modelPlural}}', '{{model}}', '{{namespace}}'],
            [$prefix, $modelPlural, $modelLower, $namespacePrefix],
            $routeStub
        );

        $webFile = base_path('routes/web.php');
        File::append($webFile, "\n\n" . $routeStub);
    }

    protected function createCssFile($model, $prefix)
    {
        $modelLower = Str::lower($model);
        $cssPath = public_path("{$prefix}-assets/css/panel/{$modelLower}");
        File::ensureDirectoryExists($cssPath);
        $cssStub = File::get(base_path('stubs/css.stub'));
        File::put("{$cssPath}/{$modelLower}.css", $cssStub);
    }
}
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

        // بررسی وجود مدل و تولید در صورت عدم وجود
        $this->createModelIfNotExists($model, $namespacePrefix);

        // تولید مایگریشن در صورت عدم وجود
        $this->createMigrationIfNotExists($modelLower, $modelPlural);

        // تولید کنترلر
        $this->createController($model, $namespacePrefix);

        // تولید کامپوننت‌های Livewire
        $this->createLivewireComponents($model, $namespacePrefix, $prefix);

        // تولید فایل‌های Blade
        $this->createBladeFiles($model, $namespacePrefix, $prefix);

        // تولید فایل CSS
        $this->createCssFile($modelLower, $prefix);

        // اضافه کردن روت‌ها
        $this->appendRoutes($model, $prefix, $namespacePrefix);

        $this->info("CRUD Livewire for {$model} with prefix {$prefix} created successfully!");
    }

    protected function createModelIfNotExists($model, $namespacePrefix)
    {
        $modelPath = app_path("Models/{$namespacePrefix}/{$model}.php");
        if (! File::exists($modelPath)) {
            $stub = File::get(base_path('stubs/model.stub'));
            $stub = str_replace(
                ['{{ namespace }}', '{{ class }}', '{{ fillable }}'],
                ["App\\Models\\{$namespacePrefix}", $model, $this->getFillableFields()],
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

    protected function createController($model, $namespacePrefix)
    {
        $controllerPath = app_path("Http/Controllers/{$namespacePrefix}/Panel/" . Str::plural($model) . "/{$model}Controller.php");
        File::ensureDirectoryExists(dirname($controllerPath));
        $stub = File::get(base_path('stubs/controller.stub'));
        $stub = str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ modelLowerPlural }}', '{{ model }}'],
            ["App\\Http\\Controllers\\{$namespacePrefix}\\Panel\\" . Str::plural($model), "{$model}Controller", Str::plural(Str::lower($model)), $model],
            $stub
        );
        File::put($controllerPath, $stub);
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
                ['{{ namespace }}', '{{ class }}', '{{ model }}', '{{ modelLower }}', '{{ modelPlural }}', '{{ prefix }}'],
                ["App\\Livewire\\{$namespacePrefix}\\Panel\\" . Str::plural($model), $className, $model, $modelLower, $modelPlural, $prefix],
                $stub
            );
            File::put($path, $stub);
        }
    }

    protected function createBladeFiles($model, $namespacePrefix, $prefix)
    {
        $modelLower  = Str::lower($model);
        $modelPlural = Str::plural($modelLower);
        $viewsPath   = resource_path("views/{$prefix}/panel/" . Str::plural($modelLower));
        File::ensureDirectoryExists($viewsPath);

        // تولید ویوهای Livewire
        $livewireViews = [
            'list'   => 'livewire-list-view.stub',
            'create' => 'livewire-create-view.stub',
            'edit'   => 'livewire-edit-view.stub',
        ];
        foreach ($livewireViews as $type => $stubFile) {
            $stub = File::get(base_path("stubs/{$stubFile}"));
            $stub = str_replace(
                ['{{ model }}', '{{ modelLower }}', '{{ modelPlural }}', '{{ prefix }}'],
                [$model, $modelLower, $modelPlural, $prefix],
                $stub
            );
            File::put("{$viewsPath}/{$modelLower}-{$type}.blade.php", $stub);
        }

        // تولید تمپلیت اصلی
        $templateStub = File::get(base_path('stubs/template.stub'));
        $templateStub = str_replace(
            ['{{ modelPlural }}', '{{ modelLower }}', '{{ prefix }}'],
            [$modelPlural, $modelLower, $prefix],
            $templateStub
        );
        File::put("{$viewsPath}/index.blade.php", $templateStub);
    }

    protected function createCssFile($modelLower, $prefix)
    {
        $cssPath = public_path("{$prefix}-assets/css/panel/" . Str::plural($modelLower) . "/{$modelLower}.css");
        File::ensureDirectoryExists(dirname($cssPath));
        File::copy(base_path('stubs/styles.stub'), $cssPath);
    }

    protected function appendRoutes($model, $prefix, $namespacePrefix)
    {
        $routesFile  = base_path('routes/web.php');
        $modelLower  = Str::lower($model);
        $modelPlural = Str::plural($modelLower);
        $routeStub   = File::get(base_path('stubs/routes.stub'));
        $routes      = str_replace(
            ['{{ prefix }}', '{{ namespace }}', '{{ model }}', '{{ modelLower }}', '{{ modelPlural }}'],
            [$prefix, $namespacePrefix, $model, $modelLower, $modelPlural],
            $routeStub
        );
        File::append($routesFile, "\n" . $routes);
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

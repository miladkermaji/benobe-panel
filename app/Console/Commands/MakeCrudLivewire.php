<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeCrudLivewire extends Command
{
    protected $signature   = 'make:crud-livewire {model} {prefix}';
    protected $description = 'Generate a full CRUD with Livewire for a given model and prefix';

    public function handle()
    {
        $model           = $this->argument('model');
        $prefix          = $this->argument('prefix');
        $modelLower      = strtolower($model);
        $modelPlural     = Str::plural($modelLower);
        $namespacePrefix = ($prefix === 'admin') ? 'Admin' : 'Dr';

        $this->info("Generating CRUD for {$model} with prefix {$prefix}...");

        // ایجاد پوشه‌ها
        $this->createDirectories($model, $prefix, $namespacePrefix);

        // تولید مایگریشن
        $this->generateMigration($model, $modelLower);

        // تولید مدل
        $this->generateModel($model, $namespacePrefix);

        // تولید کنترلر
        $this->generateController($model, $namespacePrefix);

        // تولید فایل‌های Livewire
        $this->generateLivewireComponents($model, $prefix, $namespacePrefix);

        // تولید روت‌ها
        $this->generateRoutes($model, $prefix, $namespacePrefix);

        // تولید ویوها (Blade)
        $this->generateViews($model, $prefix, $modelLower, $modelPlural);

        // تولید استایل‌ها
        $this->generateStyles($model, $prefix);

        $this->info("CRUD for {$model} with prefix {$prefix} generated successfully!");
    }

    protected function createDirectories($model, $prefix, $namespacePrefix)
    {
        $directories = [
            "app/Models/{$namespacePrefix}",
            "app/Http/Controllers/{$namespacePrefix}/Panel/{$model}",
            "app/Livewire/{$namespacePrefix}/Panel/{$model}",
            "resources/views/{$prefix}/panel/{$model}",
            "public/{$prefix}-assets/css/panel/{$model}",
        ];

        foreach ($directories as $dir) {
            if (! File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
                $this->info("Created directory: {$dir}");
            }
        }
    }

    protected function generateMigration($model, $modelLower)
    {
        $table         = Str::plural($modelLower);
        $migrationPath = database_path("migrations/" . date('Y_m_d_His') . "_create_{$table}_table.php");

        if (! File::exists($migrationPath)) {
            $stub    = $this->getStub('migration');
            $content = str_replace(
                ['{{table}}', '{{model}}'],
                [$table, $model],
                $stub
            );
            File::put($migrationPath, $content);
            $this->info("Migration created: {$migrationPath}");
        }
    }

    protected function generateModel($model, $namespacePrefix)
    {
        $modelPath = "app/Models/{$namespacePrefix}/{$model}.php";
        if (! File::exists($modelPath)) {
            $stub    = $this->getStub('model');
            $content = str_replace(
                ['{{namespacePrefix}}', '{{model}}'],
                [$namespacePrefix, $model],
                $stub
            );
            File::put($modelPath, $content);
            $this->info("Model created: {$modelPath}");
        }
    }

    protected function generateController($model, $namespacePrefix)
    {
        $controllerPath = "app/Http/Controllers/{$namespacePrefix}/Panel/{$model}/{$model}Controller.php";
        $stub           = $this->getStub('controller');
        $content        = str_replace(
            ['{{namespacePrefix}}', '{{model}}'],
            [$namespacePrefix, $model],
            $stub
        );
        File::put($controllerPath, $content);
        $this->info("Controller created: {$controllerPath}");
    }

    protected function generateLivewireComponents($model, $prefix, $namespacePrefix)
    {
        $components = ['List', 'Create', 'Edit'];
        foreach ($components as $component) {
            $path    = "app/Livewire/{$namespacePrefix}/Panel/{$model}/{$model}{$component}.php";
            $stub    = $this->getStub("livewire-{$component}");
            $content = str_replace(
                ['{{namespacePrefix}}', '{{model}}', '{{prefix}}', '{{modelLower}}', '{{modelPlural}}'],
                [$namespacePrefix, $model, $prefix, strtolower($model), Str::plural(strtolower($model))],
                $stub
            );
            File::put($path, $content);
            $this->info("Livewire component created: {$path}");
        }
    }

    protected function generateRoutes($model, $prefix, $namespacePrefix)
    {
        $routePath    = base_path('routes/web.php');
        $routeContent = File::get($routePath);
        $modelLower   = strtolower($model);
        $modelPlural  = Str::plural($modelLower);

        $stub      = $this->getStub('routes');
        $newRoutes = str_replace(
            ['{{prefix}}', '{{namespacePrefix}}', '{{model}}', '{{modelPlural}}'],
            [$prefix, $namespacePrefix, $model, $modelPlural],
            $stub
        );

        // بررسی اینکه آیا روت‌ها قبلاً اضافه شده‌اند یا نه
        if (! Str::contains($routeContent, "Route::prefix('{$prefix}')")) {
            File::append($routePath, "\n" . $newRoutes);
            $this->info("Routes added to web.php");
        } else {
            // اگر پریفیکس وجود داره، بررسی می‌کنیم که گروه مدل خاص اضافه شده یا نه
            if (! Str::contains($routeContent, "Route::prefix('{$modelPlural}/')")) {
                // پیدا کردن محل گروه پریفیکس و اضافه کردن روت‌های جدید داخل اون
                $routeContent = preg_replace(
                    "/(Route::prefix\('{$prefix}'\).*?->group\(function\s*\(\).*?)(\);)/s",
                    "$1\n        Route::prefix('{$modelPlural}/')->group(function () {\n" .
                    "            Route::get('/', [{$model}Controller::class, 'index'])->name('{$prefix}.panel.{$modelPlural}.index');\n" .
                    "            Route::get('/create', [{$model}Controller::class, 'create'])->name('{$prefix}.panel.{$modelPlural}.create');\n" .
                    "            Route::get('/edit/{id}', [{$model}Controller::class, 'edit'])->name('{$prefix}.panel.{$modelPlural}.edit');\n" .
                    "        });\n$2",
                    $routeContent
                );
                File::put($routePath, $routeContent);
                $this->info("Nested routes added to existing {$prefix} prefix in web.php");
            } else {
                $this->info("Routes for {$modelPlural} already exist in web.php");
            }
        }
    }

    protected function generateViews($model, $prefix, $modelLower, $modelPlural)
    {
        $views = ['index', 'create', 'edit'];
        foreach ($views as $view) {
            $path    = "resources/views/{$prefix}/panel/{$modelLower}/{$view}.blade.php";
            $stub    = $this->getStub("view-{$view}");
            $content = str_replace(
                ['{{model}}', '{{prefix}}', '{{modelLower}}', '{{modelPlural}}'],
                [$model, $prefix, $modelLower, $modelPlural],
                $stub
            );
            File::put($path, $content);
            $this->info("View created: {$path}");
        }
    }

    protected function generateStyles($model, $prefix)
    {
        $stylePath = "public/{$prefix}-assets/css/panel/{$model}/{$model}.css";
        $stub      = $this->getStub('styles');
        File::put($stylePath, $stub);
        $this->info("Styles created: {$stylePath}");
    }

    protected function getStub($name)
    {
        return File::get(resource_path("stubs/{$name}.stub"));
    }
}

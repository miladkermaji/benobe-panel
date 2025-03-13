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
    'name' => "create_" . Str::plural(Str::lower($model)) . "_table",
    '--create' => Str::plural(Str::lower($model)),
   ]);
  } else {
   $this->info("Model {$model} already exists somewhere in app/Models. Skipping model and migration creation.");
  }
 }

 protected function createController($model, $namespacePrefix)
 {
     $controllerPath = app_path("Http/Controllers/{$namespacePrefix}/Panel/{$model}/{$model}Controller.php");
     $stub = File::get(base_path('stubs/controller.crud.stub'));
     $stub = str_replace(
         ['{{namespace}}', '{{namespacePrefix}}', '{{class}}', '{{modelLower}}', '{{prefix}}', '{{modelPlural}}'],
         ["App\\Http\\Controllers\\{$namespacePrefix}\\Panel\\{$model}", $namespacePrefix, "{$model}Controller", Str::lower($model), Str::lower($namespacePrefix), Str::plural(Str::lower($model))],
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
   ['{{namespace}}', '{{class}}', '{{model}}', '{{modelLower}}', '{{modelPlural}}', '{{namespacePrefix}}', '{{prefix}}'],
   ["App\\Livewire\\{$namespacePrefix}\\Panel\\{$modelPlural}", "{$model}List", $modelStudly, $modelLower, $modelPlural, $namespacePrefix, Str::lower($namespacePrefix)],
   $listStub
  );
  File::put($listPath, $listStub);

  // Create Component
  $createPath = app_path("Livewire/{$namespacePrefix}/Panel/{$modelPlural}/{$model}Create.php");
  $createStub = File::get(base_path('stubs/livewire.create.stub'));
  $createStub = str_replace(
   ['{{namespace}}', '{{class}}', '{{model}}', '{{modelLower}}', '{{modelPlural}}', '{{namespacePrefix}}', '{{prefix}}'],
   ["App\\Livewire\\{$namespacePrefix}\\Panel\\{$modelPlural}", "{$model}Create", $modelStudly, $modelLower, $modelPlural, $namespacePrefix, Str::lower($namespacePrefix)],
   $createStub
  );
  File::put($createPath, $createStub);

  // Edit Component
  $editPath = app_path("Livewire/{$namespacePrefix}/Panel/{$modelPlural}/{$model}Edit.php");
  $editStub = File::get(base_path('stubs/livewire.edit.stub'));
  $editStub = str_replace(
   ['{{namespace}}', '{{class}}', '{{model}}', '{{modelLower}}', '{{modelPlural}}', '{{namespacePrefix}}', '{{prefix}}'],
   ["App\\Livewire\\{$namespacePrefix}\\Panel\\{$modelPlural}", "{$model}Edit", $modelStudly, $modelLower, $modelPlural, $namespacePrefix, Str::lower($namespacePrefix)],
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
     $namespacePrefix = ucfirst($prefix); // مثلاً Dr یا Admin
     $prefixLower = Str::lower($prefix); // مثلاً dr یا admin
 
     $webFile = base_path('routes/web.php');
     if (!File::exists($webFile)) {
         $this->error("File $webFile does not exist!");
         return;
     }
     $webContent = File::get($webFile);
     $this->info("Reading first 100 chars of routes/web.php: " . substr($webContent, 0, 100));
 
     $fullNamespace = "App\\Http\\Controllers\\{$namespacePrefix}\\Panel\\{$model}\\{$model}Controller";
     $routeContent = "    Route::prefix('$modelPlural')->group(function () {\n" .
                     "        Route::get('/', [\\{$fullNamespace}::class, 'index'])->name('$prefixLower.panel.$modelPlural.index');\n" .
                     "        Route::get('/create', [\\{$fullNamespace}::class, 'create'])->name('$prefixLower.panel.$modelPlural.create');\n" .
                     "        Route::get('/edit/{id}', [\\{$fullNamespace}::class, 'edit'])->name('$prefixLower.panel.$modelPlural.edit');\n" .
                     "    });\n";
 
     // الگوی عمومی‌تر برای تطبیق با گروه‌های مختلف
     $groupPattern = "/Route::prefix\s*\(\s*'$prefixLower'\s*\)\s*(?:\r?\n\s*)?->namespace\s*\(\s*'$namespacePrefix'\s*\)\s*(?:->middleware\s*\(\s*'[a-zA-Z:]+'\s*\)\s*(?:\r?\n\s*)?)?->group\s*\(\s*function\s*\(\s*\)\s*\{(.*?)\}\s*\);/s";
     $this->info("Using pattern: $groupPattern");
 
     if (preg_match($groupPattern, $webContent, $matches)) {
         $groupContent = $matches[1];
         $this->info("Found $prefixLower group with content length: " . strlen($groupContent));
         $this->info("First 200 chars of group content: " . substr($groupContent, 0, 200));
 
         if (strpos($groupContent, "Route::prefix('$modelPlural')->group(function () {") === false) {
             // اضافه کردن مسیر جدید در ابتدای گروه
             $newGroupContent = "\n" . $routeContent . "\n" . trim($groupContent);
             $this->info("New routes to prepend:\n" . $routeContent);
 
             // جایگزینی با توجه به وجود یا عدم وجود middleware
             $replacement = "Route::prefix('$prefixLower')\n    ->namespace('$namespacePrefix')\n";
             if ($prefixLower === 'admin') {
                 $replacement .= "    ->middleware('manager')\n";
             }
             $replacement .= "    ->group(function () {{$newGroupContent}\n});";
 
             $webContent = preg_replace(
                 $groupPattern,
                 $replacement,
                 $webContent
             );
 
             if (File::put($webFile, $webContent) === false) {
                 $this->error("Failed to write routes to $webFile. Check file permissions.");
             } else {
                 $this->info("Routes for $modelPlural prepended to $prefixLower group successfully.");
                 $this->info("Check routes/web.php to confirm placement.");
             }
         } else {
             $this->info("Routes for $modelPlural already exist in $prefixLower group. Skipping.");
         }
     } else {
         $this->error("Could not find $prefixLower group in routes/web.php.");
         $this->info("Current routes/web.php snippet near '$prefixLower':");
         $prefixPos = strpos($webContent, "Route::prefix('$prefixLower')");
         if ($prefixPos !== false) {
             $this->info(substr($webContent, $prefixPos, 200));
         } else {
             $this->info("No 'Route::prefix('$prefixLower')' found in the file!");
         }
     }
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

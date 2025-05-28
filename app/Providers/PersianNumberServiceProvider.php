<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class PersianNumberServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // اضافه کردن فایل JavaScript به لایوت اصلی
        $this->publishes([
            __DIR__.'/../../resources/js/persian-number-converter.js' => public_path('app-assets/js/persian-number-converter.js'),
        ], 'persian-number');

        // اضافه کردن Blade Directive برای تبدیل اعداد
        Blade::directive('persianNumber', function ($expression) {
            return "<?php echo \App\Helpers\PersianNumber::convertToPersian($expression); ?>";
        });

        Blade::directive('englishNumber', function ($expression) {
            return "<?php echo \App\Helpers\PersianNumber::convertToEnglish($expression); ?>";
        });
    }
}

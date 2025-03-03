<?php
namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class NetworkStatusServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // تزریق CSS و JS به همه صفحات
        View::composer('*', function ($view) {
            $view->with('networkStatusScripts', $this->getNetworkStatusScripts());
        });

        // ثبت Blade directive برای راحتی بیشتر
        Blade::directive('networkStatus', function () {
            return $this->getNetworkStatusScripts();
        });
    }

    protected function getNetworkStatusScripts()
    {
        return <<<HTML
            <link href="{{ asset('dr-assets/css/network-status.css') }}" rel="stylesheet">
            <div id="network-modal" class="network-modal hidden">
                <div class="network-modal-content">
                    <div class="flex flex-col items-center">
                        <svg class="w-16 h-16 text-red-500 mb-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h2 class="text-2xl font-bold text-gray-800 mb-2">اتصال اینترنت قطع شده است!</h2>
                        <p class="text-gray-600 text-center">لطفاً اتصال اینترنت خود را بررسی کنید و دوباره تلاش کنید.</p>
                        <div class="mt-6 d-flex justify-content-center w-100">
                            <span class="loading-dot"></span>
                            <span class="loading-dot"></span>
                            <span class="loading-dot"></span>
                        </div>
                    </div>
                </div>
            </div>
            <script src="{{ asset('dr-assets/js/network-status.js') }}"></script>
HTML;
    }
}
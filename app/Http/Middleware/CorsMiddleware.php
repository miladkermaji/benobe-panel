<?php

namespace App\Http\Middleware;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use Closure;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // اگر پاسخ یک فایل است، بدون تغییر آن را برگردان
        if ($response instanceof BinaryFileResponse) {
            return $response;
        }

        return $response->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }

}

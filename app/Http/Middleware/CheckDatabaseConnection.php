<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckDatabaseConnection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Try to connect to database
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            // If it's an API request, return JSON response
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ارتباط با سرور پایگاه داده برقرار نشد. لطفاً کمی بعد مجدداً تلاش کنید.',
                    'data' => null,
                ], 503);
            }

            // For web requests, show the error page
            return response()->view('errors.database-connection', [], 503);
        }

        return $next($request);
    }
}

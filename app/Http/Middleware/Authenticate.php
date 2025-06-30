<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class Authenticate extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        try {
            // Get all guard keys from the auth config
            $allGuards = array_keys(config('auth.guards'));
            $user = null;

            // Try to authenticate against each guard
            foreach ($allGuards as $guard) {
                if (Auth::guard($guard)->check()) {
                    $user = Auth::guard($guard)->user();
                    // Set the authenticated guard for the rest of the request lifecycle
                    Auth::shouldUse($guard);
                    break;
                }
            }

            if (!$user) {
                // If still no user, try to authenticate with JWT from token
                // This is a fallback and helps with stateless requests
                $tokenUser = JWTAuth::parseToken()->authenticate();
                if ($tokenUser) {
                    $user = $tokenUser;
                }
            }

            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'کاربر یافت نشد.'], 401);
            }

        } catch (Exception $e) {
            if ($e instanceof TokenInvalidException) {
                return response()->json(['status' => 'error', 'message' => 'توکن نامعتبر است'], 401);
            } elseif ($e instanceof TokenExpiredException) {
                return response()->json(['status' => 'error', 'message' => 'توکن منقضی شده است'], 401);
            } else {
                return response()->json(['status' => 'error', 'message' => 'کاربر احراز هویت نشده است'], 401);
            }
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\AuthManager;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class Authenticate extends BaseMiddleware
{
    /**
     * The AuthManager instance.
     *
     * @var \Illuminate\Auth\AuthManager
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Auth\AuthManager  $auth
     * @return void
     */
    public function __construct(AuthManager $auth)
    {
        $this->auth = $auth;
    }

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
            // This will throw an exception if token is not provided.
            $payload = JWTAuth::parseToken()->getPayload();
            $guard = $payload->get('guard');

            if (!$guard || !array_key_exists($guard, config('auth.guards'))) {
                return response()->json(['status' => 'error', 'message' => 'اطلاعات نقش کاربر (guard) در توکن نامعتبر است.'], 401);
            }

            // Dynamically set the default guard for this request.
            // This tells Laravel and JWTAuth which guard to use.
            $this->auth->setDefaultDriver($guard);

            // Authenticate the user. This will now use the correct guard.
            $user = $this->auth->authenticate();

            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'کاربر یافت نشد.'], 401);
            }

        } catch (Exception $e) {
            if ($e instanceof TokenInvalidException) {
                return response()->json(['status' => 'error', 'message' => 'توکن نامعتبر است.'], 401);
            } elseif ($e instanceof TokenExpiredException) {
                return response()->json(['status' => 'error', 'message' => 'توکن منقضی شده است.'], 401);
            } else {
                return response()->json(['status' => 'error', 'message' => 'نیاز به احراز هویت است (توکن ارائه نشده یا خطای دیگر).'], 401);
            }
        }

        return $next($request);
    }
}

<?php

namespace App\Exceptions;

use Throwable;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ThrottleRequestsException) {
            $retryAfter = $exception->getHeaders()['Retry-After'] ?? 180;

            return response()->json([
                'message' => 'شما بیش از حد تلاش کرده‌اید. لطفاً کمی بعد مجدداً تلاش کنید.',
                'success' => false,
                'retry_after' => $retryAfter
            ], 429, [
                'Retry-After' => $retryAfter
            ]);
        }
        if ($exception instanceof TokenExpiredException) {
    return response()->json([
        'status'  => 'error',
        'message' => 'توکن منقضی شده است. لطفاً دوباره وارد شوید.',
        'data'    => null,
    ], 401);
}

if ($exception instanceof TokenInvalidException) {
    return response()->json([
        'status'  => 'error',
        'message' => 'توکن نامعتبر است.',
        'data'    => null,
    ], 401);
}

if ($exception instanceof TokenBlacklistedException) {
    return response()->json([
        'status'  => 'error',
        'message' => 'توکن شما دیگر معتبر نیست. لطفاً دوباره وارد شوید.',
        'data'    => null,
    ], 401);
}

if ($exception instanceof JWTException) {
    return response()->json([
        'status'  => 'error',
        'message' => 'خطایی در پردازش توکن رخ داده است.',
        'data'    => null,
    ], 401);
}

if ($exception instanceof UnauthorizedHttpException && $exception->getMessage() === 'The token has been blacklisted') {
    return response()->json([
        'status'  => 'error',
        'message' => 'توکن شما دیگر معتبر نیست. لطفاً دوباره وارد شوید.',
        'data'    => null,
    ], 401);
}


        return parent::render($request, $exception);
    }
}

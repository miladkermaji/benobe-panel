<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            // چک کردن توکن از کوکی یا هدر
            $token = $request->cookie('auth_token') ?? $request->bearerToken();

            if (! $token) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'توکن یافت نشد',
                    'data'    => null,
                ], 401);
            }

            // توکن را برای استفاده‌های بعدی در JWTAuth تنظیم می‌کنیم
            JWTAuth::setToken($token);

            // payload را برای بررسی‌های بعدی استخراج می‌کنیم. در صورت نامعتبر بودن توکن، استثنا پرتاب می‌شود.
            $payload = JWTAuth::getPayload();

            // بررسی می‌کنیم که توکن در لیست سیاه (پس از خروج) نباشد
            if (JWTAuth::manager()->getBlacklist()->has($payload)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'توکن شما دیگر معتبر نیست. لطفاً دوباره وارد شوید.',
                    'data' => null,
                ], 401);
            }

            // گارد و شناسه کاربر را از توکن استخراج می‌کنیم
            $guard = $payload->get('guard');
            $userId = $payload->get('sub');

            if (!$guard || !$userId) {
                return response()->json(['status' => 'error', 'message' => 'اطلاعات مورد نیاز در توکن وجود ندارد.'], 401);
            }

            // مدل کاربر را بر اساس گارد از فایل کانفیگ پیدا می‌کنیم
            $providerName = config("auth.guards.{$guard}.provider");
            $modelClass = config("auth.providers.{$providerName}.model");

            if (!$modelClass || !class_exists($modelClass)) {
                return response()->json(['status' => 'error', 'message' => 'نوع کاربر قابل شناسایی نیست.'], 500);
            }

            // کاربر را با استفاده از مدل صحیح پیدا می‌کنیم
            $user = $modelClass::find($userId);

            if (! $user) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'کاربر یافت نشد',
                    'data'    => null,
                ], 401);
            }

            // کاربر را به عنوان کاربر احراز هویت شده برای این درخواست تنظیم می‌کنیم
            Auth::guard($guard)->setUser($user);
            Auth::setUser($user); // برای سازگاری با بخش‌هایی که از گارد پیش‌فرض استفاده می‌کنند
            $request->attributes->add(['user' => $user]);

        } catch (TokenExpiredException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'توکن منقضی شده است. لطفاً دوباره وارد شوید.',
                'data'    => null,
            ], 401);
        } catch (TokenInvalidException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'توکن نامعتبر است.',
                'data'    => null,
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'خطا در پردازش توکن.',
                'data'    => null,
            ], 401);
        } catch (UnauthorizedHttpException $e) {
            // مدیریت پیام مربوط به توکن‌های بلاک‌شده
            if ($e->getMessage() === 'The token has been blacklisted') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'توکن شما دیگر معتبر نیست. لطفاً دوباره وارد شوید.',
                    'data'    => null,
                ], 401);
            }

            return response()->json([
                'status'  => 'error',
                'message' => 'خطای احراز هویت.',
                'data'    => null,
            ], 401);
        }

        return $next($request);
    }
}

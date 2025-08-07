<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ManagerPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $manager = Auth::guard('manager')->user();

        if (!$manager) {
            return redirect()->route('admin.auth.login-register-form');
        }

        // مدیر ارشد (سطح 1) همه دسترسی‌ها را دارد
        if ($manager->permission_level == 1) {
            return $next($request);
        }

        // مدیر عادی (سطح 2) باید دسترسی داشته باشد
        if ($manager->permission_level == 2) {
            $currentRoute = $request->route()->getName();

            // استفاده از متد getPermissionsAttribute که خودکار دسترسی‌های پیش‌فرض را ایجاد می‌کند
            $permissions = $manager->permissions->permissions ?? [];

            // بررسی دسترسی به مسیر فعلی
            if (in_array($currentRoute, $permissions)) {
                return $next($request);
            }

            // بررسی دسترسی‌های گروهی
            $permissionsConfig = config('admin-permissions');
            foreach ($permissionsConfig as $permissionKey => $permissionData) {
                if (in_array($permissionKey, $permissions)) {
                    if (isset($permissionData['routes']) && array_key_exists($currentRoute, $permissionData['routes'])) {
                        return $next($request);
                    }
                }
            }

            // اگر دسترسی نداشت، به داشبورد ریدایرکت کن
            return redirect()->route('admin-panel')->with('error', 'شما دسترسی به این بخش ندارید.');
        }

        return $next($request);
    }
}

<?php

use App\Http\Middleware\JwtMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // میدلورهای سراسری (Global Middleware)
        $middleware->append([
            \App\Http\Middleware\TrustProxies::class,
            /* \Illuminate\Http\Middleware\HandleCors::class, */
            \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
            \App\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
            \App\Http\Middleware\CorsMiddleware::class,
            \App\Http\Middleware\CheckDatabaseConnection::class,
        ]);

        // گروه‌های میدلور (Middleware Groups)
        $middleware->group('web', [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\LocaleMiddleware::class,
        ]);

        $middleware->group('api', [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            /* \Illuminate\Routing\Middleware\ThrottleRequests::class . ':api', */
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->group('doctor', [
            \App\Http\Middleware\Dr\CheckCompleteProfile::class,
            \App\Http\Middleware\Dr\CheckWorkScheduleClinic::class,
            \App\Http\Middleware\doctor::class,
        ]);

        $middleware->group('manager', [
            \App\Http\Middleware\manager::class,
        ]);

        $middleware->group('secretary', [
            \App\Http\Middleware\Dr\CheckCompleteProfile::class,
            \App\Http\Middleware\Dr\CheckWorkScheduleClinic::class,
            \App\Http\Middleware\secretary::class,
        ]);

        $middleware->group('user', [
            \App\Http\Middleware\user::class,
        ]);

        // اسم مستعار برای میدلورها (Middleware Aliases)
        $middleware->alias([
            'custom-auth.jwt' => JwtMiddleware::class,
            'auth' => \App\Http\Middleware\Authenticate::class,
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
            'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            'signed' => \App\Http\Middleware\ValidateSignature::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'user' => \App\Http\Middleware\user::class,
            'manager' => \App\Http\Middleware\manager::class,
            'doctor' => \App\Http\Middleware\doctor::class,
            'secretary' => \App\Http\Middleware\secretary::class,
            'medical_center' => \App\Http\Middleware\MedicalCenter::class,
            'secretary.permission' => \App\Http\Middleware\Dr\CheckSecretaryPermission::class,
            'medical_center.permission' => \App\Http\Middleware\Mc\CheckMedicalCenterPermission::class,
            'complete-profile' => \App\Http\Middleware\Dr\CheckCompleteProfile::class,
            'manager.permission' => \App\Http\Middleware\ManagerPermissionMiddleware::class,
            'check-database-connection' => \App\Http\Middleware\CheckDatabaseConnection::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Custom exception handling logic here, if needed
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->job(new \App\Jobs\CheckUserBlockingsExpiration())->hourly();
    })
    ->create();

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->commands([
    \App\Console\Commands\AddDefaultDoctorPermissions::class,
    \App\Console\Commands\CheckDatabaseStatus::class,
]);

return $app;

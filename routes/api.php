<?php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('/auth')->group(function () {
    Route::post('/login-register', [AuthController::class, 'loginRegister'])->name('api.auth.login-register');
    Route::post('/login-confirm/{token}', [AuthController::class, 'loginConfirm'])->name('api.auth.login-confirm');
    Route::post('/resend-otp/{token}', [AuthController::class, 'resendOtp'])->name('api.auth.resend-otp');

    // Middleware را به این شکل اعمال کن:
    Route::middleware(['custom-auth.jwt'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        Route::get('/profile', [AuthController::class, 'me']);
        Route::get('/verify-token', [AuthController::class, 'verifyToken']);
    });
});


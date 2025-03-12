<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;




Route::prefix('admin/auth')->group(function () {
    Route::post('/login-register', [AuthController::class, 'loginRegister'])->name('api.admin.auth.login-register');
    Route::post('/login-confirm/{token}', [AuthController::class, 'loginConfirm'])->name('api.admin.auth.login-confirm');
    Route::post('/resend-otp/{token}', [AuthController::class, 'resendOtp'])->name('api.admin.auth.resend-otp');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.admin.auth.logout');
});



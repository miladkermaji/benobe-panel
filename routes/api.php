<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\ZoneController;
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
        // مسیر جدید برای ویرایش اطلاعات کاربر
        Route::post('/update-profile', [AuthController::class, 'updateProfile'])->name('api.auth.update-profile');

    });
});
Route::middleware(['custom-auth.jwt'])->group(function () {
    Route::prefix('appointments')->group(function () {
        Route::post('my_appointments/{id}/cancel', [AppointmentController::class, 'cancelAppointment'])->name('api.appointments.cancel');
        Route::get('/my_appointments', [AppointmentController::class, 'getAppointments'])->name('api.appointments.index');
    });
});

Route::prefix('zone')->group(function () {
    Route::get('/provinces', [ZoneController::class, 'getProvinces'])->name('api.zone.provinces');
    Route::get('/cities', [ZoneController::class, 'getCities'])->name('api.zone.cities');
});

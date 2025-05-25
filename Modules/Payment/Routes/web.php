<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AppointmentBookingController;
use Modules\Payment\App\Http\Controllers\PaymentController;
use App\Http\Controllers\Dr\Panel\Profile\DrUpgradeProfileController;


Route::middleware(['web'])->group(function () {
    Route::get('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');
});

Route::get('/appointments/payment/result', [AppointmentBookingController::class, 'paymentResult'])->name('appointment.payment.result');


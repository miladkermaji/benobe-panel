<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MagController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\ZoneController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\SubUserController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\SpecialtyController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\DoctorFilterController;
use App\Http\Controllers\Api\DoctorListingController;
use App\Http\Controllers\Api\MedicalCentersController;
use App\Http\Controllers\Api\TeleCounselingController;
use App\Http\Controllers\Api\DoctorAppointmentController;

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

Route::prefix('zone')->group(function () {
    Route::get('/provinces', [ZoneController::class, 'getProvinces'])->name('api.zone.provinces');
    Route::get('/cities', [ZoneController::class, 'getCities'])->name('api.zone.cities');
});

Route::middleware(['custom-auth.jwt'])->group(function () {
    Route::prefix('appointments')->group(function () {
        Route::post('my_appointments/{id}/cancel', [AppointmentController::class, 'cancelAppointment'])->name('api.appointments.cancel');
        Route::get('/my_appointments', [AppointmentController::class, 'getAppointments'])->name('api.appointments.index');
    });

    Route::prefix('orders')->group(function () {
        Route::get('/my_orders', [OrderController::class, 'getOrders'])->name('api.orders.index');
    });

    Route::prefix('wallet')->group(function () {
        Route::get('/my_wallet', [WalletController::class, 'getWallet'])->name('api.wallet.index');
        Route::get('/my_transactions', [WalletController::class, 'getTransactions'])->name('api.wallet.transactions');
    });

    Route::prefix('sub_users')->group(function () {
        Route::get('list/', [SubUserController::class, 'getSubUsers'])->name('api.sub_users.index');
    });
    Route::prefix('doctors')->group(function () {
        Route::get('/my_doctors', [DoctorController::class, 'getMyDoctors'])->name('api.doctors.my_doctors');
    });

});

Route::prefix('menus')->group(function () {
    Route::get('/custom', [MenuController::class, 'getCustomMenus'])->name('api.menus.custom');
});

Route::prefix('banner')->group(function () {
    Route::get('/text', [BannerController::class, 'getBannerText'])->name('api.banner.text');
    Route::get('/stats', [BannerController::class, 'getStats'])->name('api.banner.stats');
});

Route::prefix('doctors')->group(function () {
    Route::get('/best', [DoctorController::class, 'getBestDoctors'])->name('api.doctors.best');
    Route::get('/new', [DoctorController::class, 'getNewDoctors'])->name('api.doctors.new'); // مسیر جدید

});
Route::prefix('specialties')->group(function () {
    Route::get('/', [SpecialtyController::class, 'getSpecialties'])->name('api.specialties.index');
});
Route::prefix('medical-centers')->group(function () {
    Route::get('/stats', [MedicalCentersController::class, 'getStats'])->name('api.medical-centers.stats');
    Route::get('/clinics', [MedicalCentersController::class, 'getClinics'])->name('api.medical-centers.clinics');
    Route::get('/treatment-centers', [MedicalCentersController::class, 'getTreatmentCenters'])->name('api.medical-centers.treatment-centers');
    Route::get('/imaging-centers', [MedicalCentersController::class, 'getImagingCenters'])->name('api.medical-centers.imaging-centers');
    Route::get('/hospitals', [MedicalCentersController::class, 'getHospitals'])->name('api.medical-centers.hospitals');
    Route::get('/laboratories', [MedicalCentersController::class, 'getLaboratories'])->name('api.medical-centers.laboratories');
});

Route::prefix('reviews')->group(function () {
    Route::get('/', [ReviewController::class, 'index'])->name('api.reviews.index');
    Route::post('/', [ReviewController::class, 'store'])->middleware('custom-auth.jwt')->name('api.reviews.store');
});
Route::prefix('tele-counseling')->group(function () {
    Route::get('/', [TeleCounselingController::class, 'index'])->name('api.tele-counseling.index');
});

Route::get('/mag/latest-posts', [MagController::class, 'getLatestPosts']);
Route::get('/doctors', [DoctorListingController::class, 'getDoctors']);

Route::get('/doctor-filters', [DoctorFilterController::class, 'getFilterOptions']);
Route::get('/doctors/{doctorId}/appointment-options', [DoctorAppointmentController::class, 'getAppointmentOptions']);



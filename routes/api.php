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
use App\Http\Controllers\Api\HospitalController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\SpecialtyController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\DoctorFilterController;
use App\Http\Controllers\Api\DoctorListingController;
use App\Http\Controllers\Api\DoctorProfileController;
use App\Http\Controllers\Api\MedicalCentersController;
use App\Http\Controllers\Api\TeleCounselingController;
use App\Http\Controllers\Api\DoctorAppointmentController;
use App\Http\Controllers\Api\AppointmentBookingController;
use App\Http\Controllers\Api\UserSubscriptionController;
use App\Http\Controllers\Api\ContactMessageController;
use App\Http\Controllers\Api\FaqController;
use Modules\Payment\App\Http\Controllers\PaymentController;
use Illuminate\Http\Request;
use App\Services\JwtTokenService;
use App\Http\Controllers\Api\SearchController;

// مسیرهای نسخه 1 (یا بدون نسخه - قبلی)
Route::prefix('/auth')->group(function () {
    Route::post('/login-register', [AuthController::class, 'loginRegister'])->name('api.auth.login-register');
    Route::post('/login-confirm/{token}', [AuthController::class, 'loginConfirm'])->name('api.auth.login-confirm');
    Route::post('/resend-otp/{token}', [AuthController::class, 'resendOtp'])->name('api.auth.resend-otp');
    Route::middleware(['custom-auth.jwt'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        Route::get('/profile', [AuthController::class, 'me']);
        Route::get('/verify-token', [AuthController::class, 'verifyToken']);
        Route::post('/update-profile', [AuthController::class, 'updateProfile'])->name('api.auth.update-profile');
    });
});

// مسیرهای جدید نسخه 2
Route::prefix('/v2')->group(function () {
    // مسیرهای جستجو
    Route::get('/search', [SearchController::class, 'search'])->name('api.v2.search');

    // مسیرهای عمومی استوری (بدون نیاز به احراز هویت)
    Route::prefix('stories')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\StoryController::class, 'index'])->name('api.v2.stories.index');
        Route::get('/{id}', [\App\Http\Controllers\Api\StoryController::class, 'show'])->name('api.v2.stories.show');
    });

    // مسیرهای احراز هویت
    Route::prefix('/auth')->group(function () {
        Route::post('/login-register', [AuthController::class, 'loginRegister'])->name('api.v2.auth.login-register');
        Route::post('/login-confirm/{token}', [AuthController::class, 'loginConfirm'])->name('api.v2.auth.login-confirm');
        Route::post('/resend-otp/{token}', [AuthController::class, 'resendOtp'])->name('api.v2.auth.resend-otp');
        Route::middleware(['custom-auth.jwt'])->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('api.v2.auth.logout');
            Route::get('/profile', [AuthController::class, 'me']);
            Route::get('/verify-token', [AuthController::class, 'verifyToken']);
            Route::post('/update-profile', [AuthController::class, 'updateProfile'])->name('api.v2.auth.update-profile');
            Route::post('/refresh-token', [AuthController::class, 'refreshToken'])->name('api.v2.auth.refresh-token');
        });
    });

    // مسیرهای احراز هویت شده


    Route::middleware(['custom-auth.jwt'])->group(function () {
        Route::prefix('appointments')->group(function () {
            Route::get('book/{doctorId}', [AppointmentBookingController::class, 'getBookingDetails'])->name('api.v2.appointments.booking-details');
            Route::post('book/{doctorId}', [AppointmentBookingController::class, 'bookAppointment'])->name('api.v2.appointments.book');
        });

        Route::prefix('subscriptions')->group(function () {
            Route::get('details', [UserSubscriptionController::class, 'getSubscriptionDetails'])->name('api.v2.subscriptions.details');
            Route::post('purchase', [UserSubscriptionController::class, 'purchaseSubscription'])->name('api.v2.subscriptions.purchase');
            Route::get('plans', [UserSubscriptionController::class, 'getAllPlans'])->name('api.v2.subscriptions.plans');
        });
    });
    // اضافه کردن روت callback به صورت مستقل و خارج از middleware
    Route::get('subscriptions/payment/callback', [UserSubscriptionController::class, 'paymentCallback'])->name('api.v2.subscriptions.payment.callback');
});

// Debug route for JWT token validation (remove in production)
Route::post('/debug/jwt-validate', function (Request $request) {
    $token = $request->input('token');

    if (!$token) {
        return response()->json([
            'status' => 'error',
            'message' => 'Token is required'
        ], 400);
    }

    $jwtService = new JwtTokenService();
    $validation = $jwtService->validateToken($token);

    return response()->json([
        'status' => 'success',
        'data' => $validation
    ]);
})->name('api.debug.jwt-validate');

// مسیرهای عمومی
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
        Route::post('add', [SubUserController::class, 'addSubUser'])->name('api.sub_users.add');
        Route::delete('remove/{subUserId}', [SubUserController::class, 'removeSubUser'])->name('api.sub_users.remove');
    });

    Route::prefix('doctors')->group(function () {
        Route::get('/my_doctors', [DoctorController::class, 'getMyDoctors'])->name('api.doctors.my_doctors');
        Route::post('/like', [DoctorController::class, 'likeDoctor'])->middleware('custom-auth.jwt')->name('api.doctors.like');
    });

    // مسیرهای استوری - نیاز به احراز هویت دارند
    Route::prefix('stories')->group(function () {
        Route::post('/like', [\App\Http\Controllers\Api\StoryController::class, 'like'])->name('api.stories.like');
        Route::post('/unlike', [\App\Http\Controllers\Api\StoryController::class, 'unlike'])->name('api.stories.unlike');
        Route::post('/toggle-like', [\App\Http\Controllers\Api\StoryController::class, 'toggleLike'])->name('api.stories.toggle-like');
        Route::get('/check-like-status', [\App\Http\Controllers\Api\StoryController::class, 'checkLikeStatus'])->name('api.stories.check-like-status');
    });

    Route::prefix('prescriptions')->group(function () {
        Route::get('/my', [\App\Http\Controllers\Api\PrescriptionRequestController::class, 'myPrescriptions'])->name('api.prescriptions.my');
        Route::post('/request', [\App\Http\Controllers\Api\PrescriptionRequestController::class, 'requestPrescription'])->name('api.prescriptions.request');
        Route::get('/settings', [\App\Http\Controllers\Api\PrescriptionRequestController::class, 'prescriptionSettings'])->name('api.prescriptions.settings');
    });

    // ثبت‌نام پزشک و مرکز درمانی
    Route::post('/register/doctor', [\App\Http\Controllers\Api\RegisterDoctorMedicalCenterController::class, 'registerDoctor'])->name('api.register.doctor');
    Route::post('/register/medical-center', [\App\Http\Controllers\Api\RegisterDoctorMedicalCenterController::class, 'registerMedicalCenter'])->name('api.register.medical-center');
    Route::post('prescriptions/user-by-national-code', [\App\Http\Controllers\Api\PrescriptionRequestController::class, 'getOrCreateUserByNationalCode'])->name('api.prescriptions.user-by-national-code');
    Route::get('prescriptions/my-sub-users', [\App\Http\Controllers\Api\PrescriptionRequestController::class, 'mySubUsers'])->name('api.prescriptions.my-sub-users');
    Route::apiResource('doctor-comments', \App\Http\Controllers\Api\DoctorCommentController::class)->only(['index', 'store', 'show']);
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
    Route::get('/new', [DoctorController::class, 'getNewDoctors'])->name('api.doctors.new');
    Route::get('/{doctorId}/appointment-options', [DoctorAppointmentController::class, 'getAppointmentOptions'])->name('api.doctors.appointment-options');
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
    Route::get('/cities', [MedicalCentersController::class, 'getCitiesWithCenters'])->name('api.medical-centers.cities');
    Route::get('/all', [MedicalCentersController::class, 'getAllCenters'])->name('api.medical-centers.all');
    Route::get('/specialties', [MedicalCentersController::class, 'getCenterSpecialties'])->name('api.medical-centers.specialties');
    Route::get('/treatment-center-specialties', [MedicalCentersController::class, 'getTreatmentCenterSpecialties'])->name('api.medical-centers.treatment-center-specialties');
    Route::get('/center-types', [MedicalCentersController::class, 'getCenterTypes'])->name('api.medical-centers.center-types');
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

Route::middleware(['api'])->group(function () {
    Route::get('/payment/result', [AppointmentBookingController::class, 'paymentResult'])->name('api.payment.result');
    Route::get('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');
});


Route::prefix('doctors')->group(function () {
    Route::get('/{doctorId}/profile', [DoctorProfileController::class, 'getDoctorProfile'])->name('api.doctors.profile');
});

Route::prefix('hospital')->group(function () {
    Route::get('/{slug}', [HospitalController::class, 'getHospitalDetails'])->name('api.hospital.details');
});

Route::get('/appointments/payment/result', [AppointmentBookingController::class, 'paymentResult'])->name('appointment.payment.result');

Route::post('appointments/reservation-status', [AppointmentBookingController::class, 'getReservationStatus']);

// مسیرهای عمومی
Route::prefix('medical-centers')->group(function () {
    Route::get('/list', [MedicalCentersController::class, 'list'])->name('api.medical-centers.list');

    // مسیرهای جدید برای پروفایل مراکز درمانی (اولویت بالاتر)
    Route::get('/{centerId}/profile', [\App\Http\Controllers\Api\MedicalCenterProfileController::class, 'show'])->name('api.medical-centers.profile.details');
    Route::get('/{centerId}/reviews', [\App\Http\Controllers\Api\MedicalCenterProfileController::class, 'reviews'])->name('api.medical-centers.reviews');
});

Route::get('prescriptions/insurances', [\App\Http\Controllers\Api\PrescriptionRequestController::class, 'prescriptionInsurances']);
Route::get('prescriptions/insulins', [\App\Http\Controllers\Api\PrescriptionRequestController::class, 'insulins']);
Route::match(['get', 'post'], 'prescriptions/payment/callback', [\App\Http\Controllers\Api\PrescriptionRequestController::class, 'prescriptionPaymentCallback'])->name('api.prescriptions.payment.callback');

Route::match(['get', 'post'], 'search', [\App\Http\Controllers\Api\SearchController::class, 'search'])->name('api.search');

Route::get('public-doctor-comments/{doctor_id}', [\App\Http\Controllers\Api\DoctorCommentController::class, 'publicDoctorComments']);

// مسیر قدیمی با slug (در انتها برای جلوگیری از تداخل)
Route::get('medical-centers/{slug}/profile', [MedicalCentersController::class, 'getProfile'])->name('api.medical-centers.profile.old');

// مسیرهای عمومی - بدون نیاز به احراز هویت
Route::post('/contact-messages', [ContactMessageController::class, 'store'])->name('api.contact-messages.store');

// مسیرهای FAQ
Route::prefix('faqs')->group(function () {
    Route::get('/', [FaqController::class, 'index'])->name('api.faqs.index');
    Route::get('/citizens', [FaqController::class, 'citizens'])->name('api.faqs.citizens');
    Route::get('/doctors', [FaqController::class, 'doctors'])->name('api.faqs.doctors');
    Route::get('/search', [FaqController::class, 'search'])->name('api.faqs.search');
});

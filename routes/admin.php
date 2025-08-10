<?php

use App\Livewire\Admin\Auth\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Livewire\Admin\Auth\LoginConfirm;
use App\Livewire\Admin\Auth\LoginRegister;
use App\Livewire\Admin\Auth\LoginUserPass;
use App\Livewire\Admin\Auth\LoginSetPassword;
use App\Http\Controllers\Admin\Panel\Users\UserController;
use App\Http\Controllers\Admin\Panel\Setting\SettingController;
use App\Http\Controllers\Admin\Panel\UserSubscriptionController;
use App\Http\Controllers\Admin\Panel\UserAppointmentFeeController;
use App\Http\Controllers\Admin\Panel\UserMembershipPlanController;
use App\Http\Controllers\Admin\Panel\Profile\AdminProfileController;
use App\Http\Controllers\Admin\Panel\Tools\SiteMap\SitemapController;
use App\Http\Controllers\Admin\Panel\Tools\Redirect\RedirectController;
use App\Http\Controllers\Admin\Panel\Dashboard\AdminDashboardController;
use App\Http\Controllers\Admin\Panel\Tools\NewsLatter\NewsLatterController;
use App\Http\Controllers\Admin\Panel\Tools\SmsGateway\SmsGatewayController;
use App\Http\Controllers\Admin\Panel\Tools\FileManager\FileManagerController;
use App\Http\Controllers\Admin\Panel\Tools\PageBuilder\PageBuilderController;
use App\Http\Controllers\Admin\Panel\Tools\SiteMap\SitemapSettingsController;
use App\Http\Controllers\Admin\Panel\Tools\MailTemplate\MailTemplateController;
use App\Http\Controllers\Admin\Panel\Tools\Notification\NotificationController;
use App\Http\Controllers\Admin\Panel\Tools\PaymentGateways\PaymentGatewaysController;
use App\Http\Controllers\Admin\Panel\Tools\DataMigrationTool\DataMigrationToolController;
use App\Http\Controllers\Dr\Panel\Turn\Schedule\ScheduleSetting\BlockingUsers\BlockingUsersController;

/* login manager routes */
Route::prefix('admin-panel/')->middleware('throttle:10,1')->group(function () {
    Route::get('login', LoginRegister::class)->name('admin.auth.login-register-form');
    Route::get('login-user-pass', LoginUserPass::class)->name('admin.auth.login-user-pass-form');
    Route::get('login-set-password', LoginSetPassword::class)->name('admin.auth.login-set-password-form');
    Route::get('login-confirm/{token}', LoginConfirm::class)->name('admin.auth.login-confirm-form');
    Route::get('login-resend-otp/{token}', LoginConfirm::class)->name('admin.auth.login-resend-otp'); // اضافه شده
    Route::get('logout', Logout::class)->name('admin.auth.logout');
});
Route::prefix('admin')
    ->namespace('Admin')
    ->middleware(['manager', 'manager.permission'])
    ->group(function () {
        Route::prefix('clinic-deposit-settings')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\ClinicDepositSettings\ClinicDepositSettingController::class, 'index'])->name('admin.panel.clinic-deposit-settings.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\ClinicDepositSettings\ClinicDepositSettingController::class, 'create'])->name('admin.panel.clinic-deposit-settings.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\ClinicDepositSettings\ClinicDepositSettingController::class, 'edit'])->name('admin.panel.clinic-deposit-settings.edit');
        });
        Route::prefix('tickets')->name('admin.panel.tickets.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\Tickets\TicketController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\Tickets\TicketController::class, 'create'])->name('create');
            Route::get('/{id}', [\App\Http\Controllers\Admin\Panel\Tickets\TicketController::class, 'show'])->name('show');
            Route::get('/tickets', [\App\Http\Controllers\Admin\Panel\Tickets\TicketController::class, 'index'])->name('tickets.index');
        });
        Route::prefix('doctor-wallets')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\DoctorWallet\DoctorWalletController::class, 'index'])->name('admin.panel.doctor-wallets.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\DoctorWallet\DoctorWalletController::class, 'create'])->name('admin.panel.doctor-wallets.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\DoctorWallet\DoctorWalletController::class, 'edit'])->name('admin.panel.doctor-wallets.edit');
        });
        Route::prefix('blogs')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\Blogs\BlogsController::class, 'index'])->name('admin.panel.blogs.index');
        });
        Route::prefix('setting/')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('admin.panel.setting.index');
            Route::get('/change-logo', [SettingController::class, 'change_logo'])->name('admin.panel.setting.change-logo');
        });
        Route::prefix('transactions')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\Transaction\TransactionController::class, 'index'])->name('admin.panel.transactions.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\Transaction\TransactionController::class, 'create'])->name('admin.panel.transactions.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\Transaction\TransactionController::class, 'edit'])->name('admin.panel.transactions.edit');
        });
        Route::prefix('doctor-insurances')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\DoctorInsurance\DoctorInsuranceController::class, 'index'])->name('admin.panel.doctor-insurances.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\DoctorInsurance\DoctorInsuranceController::class, 'create'])->name('admin.panel.doctor-insurances.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\DoctorInsurance\DoctorInsuranceController::class, 'edit'])->name('admin.panel.doctor-insurances.edit');
        });
        Route::prefix('insurances')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\Insurance\InsuranceController::class, 'index'])->name('admin.panel.insurances.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\Insurance\InsuranceController::class, 'create'])->name('admin.panel.insurances.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\Insurance\InsuranceController::class, 'edit'])->name('admin.panel.insurances.edit');
        });
        Route::prefix('doctor-holidays')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\DoctorHoliday\DoctorHolidayController::class, 'index'])->name('admin.panel.doctor-holidays.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\DoctorHoliday\DoctorHolidayController::class, 'create'])->name('admin.panel.doctor-holidays.create');
            Route::get('edit/{id}/{date?}', [\App\Http\Controllers\Admin\Panel\DoctorHoliday\DoctorHolidayController::class, 'edit'])->name('admin.panel.doctor-holidays.edit');
        });
        Route::prefix('manual-appointment-settings')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\ManualAppointmentSetting\ManualAppointmentSettingController::class, 'index'])->name('admin.panel.manual-appointment-settings.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\ManualAppointmentSetting\ManualAppointmentSettingController::class, 'create'])->name('admin.panel.manual-appointment-settings.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\ManualAppointmentSetting\ManualAppointmentSettingController::class, 'edit'])->name('admin.panel.manual-appointment-settings.edit');
        });
        Route::prefix('manual-appointments')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\ManualAppointment\ManualAppointmentController::class, 'index'])->name('admin.panel.manual-appointments.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\ManualAppointment\ManualAppointmentController::class, 'create'])->name('admin.panel.manual-appointments.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\ManualAppointment\ManualAppointmentController::class, 'edit'])->name('admin.panel.manual-appointments.edit');
        });
        Route::prefix('appointments')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\Appointment\AppointmentController::class, 'index'])->name('admin.panel.appointments.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\Appointment\AppointmentController::class, 'create'])->name('admin.panel.appointments.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\Appointment\AppointmentController::class, 'edit'])->name('admin.panel.appointments.edit');
        });
        Route::prefix('secretaries')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\Secretary\SecretaryController::class, 'index'])->name('admin.panel.secretaries.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\Secretary\SecretaryController::class, 'create'])->name('admin.panel.secretaries.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\Secretary\SecretaryController::class, 'edit'])->name('admin.panel.secretaries.edit');
            Route::get('/secreteries-permissions', [\App\Http\Controllers\Admin\Panel\Secretary\SecretaryController::class, 'adminSecretaryPermission'])->name('admin.panel.secretaries.secreteries-permission');
        });
        Route::prefix('doctor-comments')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\DoctorComment\DoctorCommentController::class, 'index'])->name('admin.panel.doctor-comments.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\DoctorComment\DoctorCommentController::class, 'create'])->name('admin.panel.doctor-comments.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\DoctorComment\DoctorCommentController::class, 'edit'])->name('admin.panel.doctor-comments.edit');
        });
        Route::prefix('doctor-specialties')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\DoctorSpecialty\DoctorSpecialtyController::class, 'index'])->name('admin.panel.doctor-specialties.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\DoctorSpecialty\DoctorSpecialtyController::class, 'create'])->name('admin.panel.doctor-specialties.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\DoctorSpecialty\DoctorSpecialtyController::class, 'edit'])->name('admin.panel.doctor-specialties.edit');
        });
        Route::prefix('doctor-documents')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\DoctorDocument\DoctorDocumentController::class, 'index'])->name('admin.panel.doctor-documents.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\DoctorDocument\DoctorDocumentController::class, 'create'])->name('admin.panel.doctor-documents.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\DoctorDocument\DoctorDocumentController::class, 'edit'])->name('admin.panel.doctor-documents.edit');
        });
        Route::get('/preview-document/{path}', function ($path) {
            $filePath = 'doctor_documents/' . $path;
            if (Storage::disk('public')->exists($filePath)) {
                $fullPath = Storage::disk('public')->path($filePath);
                return response()->file($fullPath, [
                    'Content-Disposition' => 'inline',
                ]);
            }
            abort(404);
        })->name('preview.document');
        Route::prefix('sub-users')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\SubUser\SubUserController::class, 'index'])->name('admin.panel.sub-users.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\SubUser\SubUserController::class, 'create'])->name('admin.panel.sub-users.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\SubUser\SubUserController::class, 'edit'])->name('admin.panel.sub-users.edit');
        });
        Route::prefix('user-blockings')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\UserBlocking\UserBlockingController::class, 'index'])->name('admin.panel.user-blockings.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\UserBlocking\UserBlockingController::class, 'create'])->name('admin.panel.user-blockings.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\UserBlocking\UserBlockingController::class, 'edit'])->name('admin.panel.user-blockings.edit');
            // AJAX search for users/doctors for Select2
            Route::get('/search-users', [\App\Http\Controllers\Admin\Panel\UserBlocking\UserBlockingController::class, 'searchUsers'])->name('admin.panel.user-blockings.search-users');
            Route::post('/doctor-blocking-users/group-action', [BlockingUsersController::class, 'groupAction'])->name('doctor-blocking-users.group-action');
        });
        Route::prefix('user-groups')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\UserGroup\UserGroupController::class, 'index'])->name('admin.panel.user-groups.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\UserGroup\UserGroupController::class, 'create'])->name('admin.panel.user-groups.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\UserGroup\UserGroupController::class, 'edit'])->name('admin.panel.user-groups.edit');
        });
        Route::prefix('footer-contents')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\FooterContent\FooterContentController::class, 'index'])->name('admin.panel.footer-contents.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\FooterContent\FooterContentController::class, 'create'])->name('admin.panel.footer-contents.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\FooterContent\FooterContentController::class, 'edit'])->name('admin.panel.footer-contents.edit');
        });
        Route::prefix('reviews')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\Review\ReviewController::class, 'index'])->name('admin.panel.reviews.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\Review\ReviewController::class, 'create'])->name('admin.panel.reviews.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\Review\ReviewController::class, 'edit'])->name('admin.panel.reviews.edit');
        });
        Route::prefix('imaging-centers')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\ImagingCenter\ImagingCenterController::class, 'index'])->name('admin.panel.imaging-centers.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\ImagingCenter\ImagingCenterController::class, 'create'])->name('admin.panel.imaging-centers.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\ImagingCenter\ImagingCenterController::class, 'edit'])->name('admin.panel.imaging-centers.edit');
            Route::get('/edit/{id}/gallery', [\App\Http\Controllers\Admin\Panel\ImagingCenter\ImagingCenterController::class, 'gallery'])->name('admin.panel.imaging-centers.gallery');
        });
        Route::prefix('treatmentcenters')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\TreatmentCenter\TreatmentCenterController::class, 'index'])->name('admin.panel.treatment-centers.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\TreatmentCenter\TreatmentCenterController::class, 'create'])->name('admin.panel.treatment-centers.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\TreatmentCenter\TreatmentCenterController::class, 'edit'])->name('admin.panel.treatment-centers.edit');
            Route::get('/gallery/{id}', [\App\Http\Controllers\Admin\Panel\TreatmentCenter\TreatmentCenterController::class, 'gallery'])
                ->name('admin.panel.treatment-centers.gallery');
        });
        Route::prefix('clinics')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\Clinic\ClinicController::class, 'index'])->name('admin.panel.clinics.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\Clinic\ClinicController::class, 'create'])->name('admin.panel.clinics.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\Clinic\ClinicController::class, 'edit'])->name('admin.panel.clinics.edit');
            Route::get('/edit/{id}/gallery', [\App\Http\Controllers\Admin\Panel\Clinic\ClinicController::class, 'gallery'])->name('admin.panel.clinics.gallery');
        });
        Route::prefix('laboratories')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\Laboratory\LaboratoryController::class, 'index'])->name('admin.panel.laboratories.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\Laboratory\LaboratoryController::class, 'create'])->name('admin.panel.laboratories.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\Laboratory\LaboratoryController::class, 'edit'])->name('admin.panel.laboratories.edit');
            Route::get('/edit/{id}/gallery', [\App\Http\Controllers\Admin\Panel\Laboratory\LaboratoryController::class, 'gallery'])->name('admin.panel.laboratories.gallery');
        });
        Route::prefix('hospitals')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\Hospital\HospitalController::class, 'index'])->name('admin.panel.hospitals.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\Hospital\HospitalController::class, 'create'])->name('admin.panel.hospitals.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\Hospital\HospitalController::class, 'edit'])->name('admin.panel.hospitals.edit');
            Route::get('/edit/{id}/gallery', [\App\Http\Controllers\Admin\Panel\Hospital\HospitalController::class, 'gallery'])->name('admin.panel.hospitals.gallery');
        });
        Route::prefix('doctor-services')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\DoctorService\DoctorServiceController::class, 'index'])->name('admin.panel.doctor-services.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\DoctorService\DoctorServiceController::class, 'create'])->name('admin.panel.doctor-services.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\DoctorService\DoctorServiceController::class, 'edit'])->name('admin.panel.doctor-services.edit');
        });
        Route::prefix('services')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\Service\ServiceController::class, 'index'])->name('admin.panel.services.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\Service\ServiceController::class, 'create'])->name('admin.panel.services.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\Service\ServiceController::class, 'edit'])->name('admin.panel.services.edit');
        });
        Route::prefix('specialties')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\Specialty\SpecialtyController::class, 'index'])->name('admin.panel.specialties.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\Specialty\SpecialtyController::class, 'create'])->name('admin.panel.specialties.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\Specialty\SpecialtyController::class, 'edit'])->name('admin.panel.specialties.edit');
        });
        Route::prefix('best-doctors')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\BestDoctor\BestDoctorController::class, 'index'])->name('admin.panel.best-doctors.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\BestDoctor\BestDoctorController::class, 'create'])->name('admin.panel.best-doctors.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\BestDoctor\BestDoctorController::class, 'edit'])->name('admin.panel.best-doctors.edit');
        });
        Route::prefix('zones')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\Zone\ZoneController::class, 'index'])->name('admin.panel.zones.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\Zone\ZoneController::class, 'create'])->name('admin.panel.zones.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\Zone\ZoneController::class, 'edit'])->name('admin.panel.zones.edit');
        });
        Route::prefix('cities')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\Zone\ZoneController::class, 'citiesIndex'])->name('admin.panel.cities.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\Zone\ZoneController::class, 'citiesCreate'])->name('admin.panel.cities.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\Zone\ZoneController::class, 'citiesEdit'])->name('admin.panel.cities.edit');
        });
        Route::prefix('bannertexts')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\BannerText\BannerTextController::class, 'index'])->name('admin.panel.banner-texts.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\BannerText\BannerTextController::class, 'create'])->name('admin.panel.banner-texts.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\BannerText\BannerTextController::class, 'edit'])->name('admin.panel.banner-texts.edit');
        });
        Route::prefix('menus')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\Menu\MenuController::class, 'index'])->name('admin.panel.menus.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\Menu\MenuController::class, 'create'])->name('admin.panel.menus.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\Menu\MenuController::class, 'edit'])->name('admin.panel.menus.edit');
        });
        Route::prefix('doctors')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\Doctor\DoctorController::class, 'index'])->name('admin.panel.doctors.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\Doctor\DoctorController::class, 'create'])->name('admin.panel.doctors.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\Doctor\DoctorController::class, 'edit'])->name('admin.panel.doctors.edit');
        });
        Route::prefix('faqs')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\Faq\FaqController::class, 'index'])->name('admin.panel.faqs.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\Faq\FaqController::class, 'create'])->name('admin.panel.faqs.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\Faq\FaqController::class, 'edit'])->name('admin.panel.faqs.edit');
        });
        Route::prefix('contact')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\Contact\ContactController::class, 'index'])->name('admin.panel.contact.index');
            Route::get('/show/{id}', [\App\Http\Controllers\Admin\Panel\Contact\ContactController::class, 'show'])->name('admin.panel.contact.show');
        });
        Route::prefix('stories')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\Stories\StoriesController::class, 'index'])->name('admin.panel.stories.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\Stories\StoriesController::class, 'create'])->name('admin.panel.stories.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\Stories\StoriesController::class, 'edit'])->name('admin.panel.stories.edit');
            Route::get('/analytics', [\App\Http\Controllers\Admin\Panel\Stories\StoriesController::class, 'analytics'])->name('admin.panel.stories.analytics');
        });

        // Ajax routes for story owners
        Route::prefix('stories/ajax')->name('admin.panel.stories.ajax.')->group(function () {
            Route::get('/users', [\App\Http\Controllers\Admin\Panel\Stories\StoriesController::class, 'getUsers'])->name('users');
            Route::get('/doctors', [\App\Http\Controllers\Admin\Panel\Stories\StoriesController::class, 'getDoctors'])->name('doctors');
            Route::get('/medical-centers', [\App\Http\Controllers\Admin\Panel\Stories\StoriesController::class, 'getMedicalCenters'])->name('medical-centers');
            Route::get('/managers', [\App\Http\Controllers\Admin\Panel\Stories\StoriesController::class, 'getManagers'])->name('managers');
        });
        Route::get('/doctor-login/{doctor}', function (\App\Models\Doctor $doctor) {
            // لاگین کردن دکتر با گارد doctor
            Auth::guard('doctor')->login($doctor);
            // ریدایرکت به پنل دکتر
            return redirect()->route('dr-panel');
        })->name('doctor.login');
        Route::get('/secretary-login/{secretary}', function (\App\Models\Secretary $secretary) {
            // لاگین کردن منشی با گارد secretary
            Auth::guard('secretary')->login($secretary);
            // ریدایرکت به پنل منشی
            return redirect()->route('dr-panel');
        })->name('secretary.login');
        Route::get('dashboard/', [AdminDashboardController::class, 'index'])->name('admin-panel');
        Route::post('/upload-profile-photo', [AdminProfileController::class, 'uploadPhoto'])->name('admin.upload-photo')->middleware('auth:manager');
        Route::prefix('tools/')->group(function () {
            Route::get('/file-manager', [FileManagerController::class, 'index'])->name('admin.panel.tools.file-manager')->middleware('auth:manager');
            Route::prefix('payment_gateways/')->group(function () {
                Route::get('/', [PaymentGatewaysController::class, 'index'])->name('admin.panel.tools.payment_gateways.index');
                Route::get('/create', [PaymentGatewaysController::class, 'create'])->name('admin.panel.tools.payment_gateways.create');
                Route::get('/edit/{name}', [PaymentGatewaysController::class, 'edit'])->name('admin.panel.tools.payment_gateways.edit');
                Route::put('/payment-gateways/{name}', [PaymentGatewaysController::class, 'update'])->name('admin.payment_gateways.update');
                Route::post('/payment-gateways/toggle', [PaymentGatewaysController::class, 'toggle'])->name('admin.payment_gateways.toggle');
                Route::delete('/payment-gateways/{name}', [PaymentGatewaysController::class, 'destroy'])->name('admin.panel.tools.payment_gateways.destroy');
            });
            Route::prefix('sms_gateway')->group(function () {
                Route::get('/', [SmsGatewayController::class, 'index'])->name('admin.panel.tools.sms-gateways.index');
                Route::get('/edit/{name}', [SmsGatewayController::class, 'edit'])->name('admin.panel.tools.sms-gateways.edit');
                Route::get('/create', [SmsGatewayController::class, 'create'])->name('admin.panel.tools.sms_gateways.create');
            });
            // روت Telescope با UI پیش‌فرض
            Route::get('/telescope', '\Laravel\Telescope\Http\Controllers\HomeController@index')->name('admin.panel.tools.telescope');
            Route::prefix('redirects/')->group(function () {
                Route::get('/', [RedirectController::class, 'index'])->name('admin.panel.tools.redirects.index');
                Route::get('/create', [RedirectController::class, 'create'])->name('admin.panel.tools.redirects.create'); // روت جدید
                Route::post('/store', [RedirectController::class, 'store'])->name('admin.panel.tools.redirects.store');   // روت جدید
                Route::get('/edit/{id}', [RedirectController::class, 'edit'])->name('admin.panel.tools.redirects.edit');
                Route::put('/update/{id}', [RedirectController::class, 'update'])->name('admin.panel.tools.redirects.update');
                Route::post('/toggle', [RedirectController::class, 'toggle'])->name('admin.panel.tools.redirects.toggle');
                Route::delete('/delete/{id}', [RedirectController::class, 'destroy'])->name('admin.panel.tools.redirects.destroy');
            });
            Route::prefix('sitemap')->group(function () {
                Route::get('/', [SitemapController::class, 'index'])->name('admin.tools.sitemap.index');
                Route::post('/generate', [SitemapController::class, 'generate'])->name('admin.tools.sitemap.generate');
                Route::get('/download', [SitemapController::class, 'download'])->name('admin.tools.sitemap.download');
                Route::get('/settings', [SitemapSettingsController::class, 'index'])->name('admin.tools.sitemap.settings');
                Route::put('/settings', [SitemapSettingsController::class, 'update'])->name('admin.tools.sitemap.settings.update');
            });
            Route::get('mail-template', [MailTemplateController::class, 'index'])->name('admin.panel.tools.mail-template.index');
            Route::get('news-latter/', [NewsLatterController::class, 'index'])->name('admin.tools.news-latter.index');
            Route::get('/data-migration', [DataMigrationToolController::class, 'index'])
                ->name('admin.tools.data-migration.index');
            Route::get('data-migration/download-log/{filename}', [DataMigrationToolController::class, 'downloadLog'])
                ->middleware('manager')
                ->name('admin.tools.data-migration.download-log');
            Route::prefix('tools/')->group(function () {
                Route::get('/page-builder', [PageBuilderController::class, 'index'])->name('admin.tools.page-builder.index');
                Route::post('/page-builder/store', [PageBuilderController::class, 'store'])->name('admin.tools.page-builder.store');
                Route::get('/page-builder/edit/{id}', [PageBuilderController::class, 'edit'])->name('admin.tools.page-builder.edit');
                Route::put('/page-builder/update/{id}', [PageBuilderController::class, 'update'])->name('admin.tools.page-builder.update');
                Route::delete('/page-builder/destroy/{id}', [PageBuilderController::class, 'destroy'])->name('admin.tools.page-builder.destroy');
            });
            Route::prefix('notifications')->group(function () {
                Route::get('/', [NotificationController::class, 'index'])->name('admin.panel.tools.notifications.index');
                Route::get('/notifications/create', [NotificationController::class, 'create'])->name('admin.panel.tools.notifications.create');
                Route::get('/notifications/{id}/edit', [NotificationController::class, 'edit'])->name('admin.panel.tools.notifications.edit');
            });
        });
        Route::prefix('users/')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('admin.panel.users.index');
            Route::get('/create', [UserController::class, 'create'])->name('admin.panel.users.create');
            Route::get('/edit/{id}', [UserController::class, 'edit'])->name('admin.panel.users.edit');
            // عملیات‌های دیگر مثل toggle، update و destroy در Livewire انجام می‌شوند
        });

        // Manager Routes
        Route::prefix('managers/')->name('admin.panel.managers.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\Managers\ManagerController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\Managers\ManagerController::class, 'create'])->name('create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\Managers\ManagerController::class, 'edit'])->name('edit');
            Route::get('/permissions', [\App\Http\Controllers\Admin\Panel\Managers\ManagerController::class, 'permissions'])->name('permissions');
        });

        Route::get('/panel/doctors/permissions', function () {
            return view('admin.panel.doctors.doctors-permissions');
        })->name('admin.panel.doctors.permissions');

        // Medical Center Permissions Routes
        Route::get('/panel/medical-centers/permissions', function () {
            return view('admin.panel.medical_centers.permissions.index');
        })->name('admin.panel.medical-centers.permissions');
        // User Subscriptions Routes
        Route::prefix('user-subscriptions')->group(function () {
            Route::get('/', [UserSubscriptionController::class, 'index'])->name('admin.panel.user-subscriptions.index');
            Route::get('/create', [UserSubscriptionController::class, 'create'])->name('admin.panel.user-subscriptions.create');
            Route::post('/', [UserSubscriptionController::class, 'store'])->name('admin.panel.user-subscriptions.store');
            Route::get('/{userSubscription}/edit', [UserSubscriptionController::class, 'edit'])->name('admin.panel.user-subscriptions.edit');
            Route::put('/{userSubscription}', [UserSubscriptionController::class, 'update'])->name('admin.panel.user-subscriptions.update');
            Route::delete('/{userSubscription}', [UserSubscriptionController::class, 'destroy'])->name('admin.panel.user-subscriptions.destroy');
        });
        // User Membership Plans Routes
        Route::prefix('user-membership-plans')->group(function () {
            Route::get('/', [UserMembershipPlanController::class, 'index'])->name('admin.panel.user-membership-plans.index');
            Route::get('/create', [UserMembershipPlanController::class, 'create'])->name('admin.panel.user-membership-plans.create');
            Route::post('/', [UserMembershipPlanController::class, 'store'])->name('admin.panel.user-membership-plans.store');
            Route::get('/{userMembershipPlan}/edit', [UserMembershipPlanController::class, 'edit'])->name('admin.panel.user-membership-plans.edit');
            Route::put('/{userMembershipPlan}', [UserMembershipPlanController::class, 'update'])->name('admin.panel.user-membership-plans.update');
            Route::delete('/{userMembershipPlan}', [UserMembershipPlanController::class, 'destroy'])->name('admin.panel.user-membership-plans.destroy');
        });
        // User Appointment Fees Routes
        Route::prefix('user-appointment-fees')->group(function () {
            Route::get('/', [UserAppointmentFeeController::class, 'index'])->name('admin.panel.user-appointment-fees.index');
            Route::get('/create', [UserAppointmentFeeController::class, 'create'])->name('admin.panel.user-appointment-fees.create');
            Route::post('/', [UserAppointmentFeeController::class, 'store'])->name('admin.panel.user-appointment-fees.store');
            Route::get('/{userAppointmentFee}/edit', [UserAppointmentFeeController::class, 'edit'])->name('admin.panel.user-appointment-fees.edit');
            Route::put('/{userAppointmentFee}', [UserAppointmentFeeController::class, 'update'])->name('admin.panel.user-appointment-fees.update');
            Route::delete('/{userAppointmentFee}', [UserAppointmentFeeController::class, 'destroy'])->name('admin.panel.user-appointment-fees.destroy');
        });
    });

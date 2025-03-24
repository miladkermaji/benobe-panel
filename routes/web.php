<?php

use App\Http\Controllers\Admin\Panel\Dashboard\AdminDashboardController;
use App\Http\Controllers\Admin\Panel\Profile\AdminProfileController;
use App\Http\Controllers\Admin\Panel\Tools\DataMigrationTool\DataMigrationToolController;
use App\Http\Controllers\Admin\Panel\Tools\FileManager\FileManagerController;
use App\Http\Controllers\Admin\Panel\Tools\MailTemplate\MailTemplateController;
use App\Http\Controllers\Admin\Panel\Tools\NewsLatter\NewsLatterController;
use App\Http\Controllers\Admin\Panel\Tools\Notification\NotificationController;
use App\Http\Controllers\Admin\Panel\Tools\PageBuilder\PageBuilderController;
use App\Http\Controllers\Admin\Panel\Tools\PaymentGateways\PaymentGatewaysController;
use App\Http\Controllers\Admin\Panel\Tools\Redirect\RedirectController;
use App\Http\Controllers\Admin\Panel\Tools\SiteMap\SitemapController;
use App\Http\Controllers\Admin\Panel\Tools\SiteMap\SitemapSettingsController;
use App\Http\Controllers\Admin\Panel\Tools\SmsGateway\SmsGatewayController;
use App\Http\Controllers\Admin\Panel\Users\UserController;
use App\Http\Controllers\Dr\Panel\Activation\Consult\Rules\ConsultRulesController;
use App\Http\Controllers\Dr\Panel\Bime\DRBimeController;
use App\Http\Controllers\Dr\Panel\DoctorsClinic\Activation\ActivationDoctorsClinicController;
use App\Http\Controllers\Dr\Panel\DoctorsClinic\Activation\Cost\CostController;
use App\Http\Controllers\Dr\Panel\DoctorsClinic\Activation\Duration\DurationController;
use App\Http\Controllers\Dr\Panel\DoctorsClinic\Activation\Workhours\ActivationWorkhoursController;
use App\Http\Controllers\Dr\Panel\DoctorsClinic\DoctorsClinicManagementController;
use App\Http\Controllers\Dr\Panel\DoctorServices\DoctorServicesController;
use App\Http\Controllers\Dr\Panel\DrPanelController;
use App\Http\Controllers\Dr\Panel\MyPerformance\MyPerformanceController;
use App\Http\Controllers\Dr\Panel\NoskheElectronic\Favorite\Service\ServiceController;
use App\Http\Controllers\Dr\Panel\NoskheElectronic\Favorite\Templates\FavoriteTemplatesController;
use App\Http\Controllers\Dr\Panel\NoskheElectronic\Prescription\PrescriptionController;
use App\Http\Controllers\Dr\Panel\NoskheElectronic\Providers\ProvidersController;
use App\Http\Controllers\Dr\Panel\PatientRecords\PatientRecordsController;
use App\Http\Controllers\Dr\Panel\Payment\Setting\DrPaymentSettingController;
use App\Http\Controllers\Dr\Panel\Profile\DrProfileController;
use App\Http\Controllers\Dr\Panel\Profile\DrUpgradeProfileController;
use App\Http\Controllers\Dr\Panel\Profile\LoginLogsController;
use App\Http\Controllers\Dr\Panel\Profile\SubUserController;
use App\Http\Controllers\Dr\Panel\SecretaryPermission\SecretaryPermissionController;
use App\Http\Controllers\Dr\Panel\Secretary\SecretaryManagementController;
use App\Http\Controllers\Dr\Panel\Tickets\TicketResponseController;
use App\Http\Controllers\Dr\Panel\Tickets\TicketsController;
use App\Http\Controllers\Dr\Panel\Turn\DrScheduleController;
use App\Http\Controllers\Dr\Panel\Turn\Schedule\AppointmentController;
use App\Http\Controllers\Dr\Panel\Turn\Schedule\Counseling\ConsultTerm\ConsultTermController;
use App\Http\Controllers\Dr\Panel\Turn\Schedule\ManualNobat\ManualNobatController;
use App\Http\Controllers\Dr\Panel\Turn\Schedule\MoshavereSetting\MoshavereSettingController as DrMoshavereSettingController;
use App\Http\Controllers\Dr\Panel\Turn\Schedule\MoshavereSetting\MySpecialDaysCounselingController;
use App\Http\Controllers\Dr\Panel\Turn\Schedule\MoshavereWaiting\MoshavereWaitingController;
use App\Http\Controllers\Dr\Panel\Turn\Schedule\ScheduleSetting\BlockingUsers\BlockingUsersController;
use App\Http\Controllers\Dr\Panel\Turn\Schedule\ScheduleSetting\ScheduleSettingController;
use App\Http\Controllers\Dr\Panel\Turn\Schedule\ScheduleSetting\VacationController;
use App\Http\Controllers\Dr\Panel\Turn\TurnsCatByDays\TurnsCatByDaysController;
use App\Livewire\Admin\Auth\LoginConfirm;
use App\Livewire\Admin\Auth\LoginRegister;
use App\Livewire\Admin\Auth\LoginUserPass;
use App\Livewire\Admin\Auth\Logout;
use App\Livewire\Admin\Auth\TwoFactor;
use App\Livewire\Dr\Auth\DoctorLoginConfirm;
use App\Livewire\Dr\Auth\DoctorLoginRegister;
use App\Livewire\Dr\Auth\DoctorLoginUserPass;
use App\Livewire\Dr\Auth\DoctorLogout;
use App\Livewire\Dr\Auth\DoctorTwoFactor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Modules\SendOtp\App\Http\Controllers\SendOtpController;

//manager login routes
/* login manager routes */
Route::prefix('admin-panel/')->group(function () {
    Route::get('login', LoginRegister::class)->name('admin.auth.login-register-form');
    Route::get('login-user-pass', LoginUserPass::class)->name('admin.auth.login-user-pass-form');
    Route::get('two-factor/{token}', TwoFactor::class)->name('admin-two-factor');
    Route::get('login-confirm/{token}', LoginConfirm::class)->name('admin.auth.login-confirm-form');
    Route::get('login-resend-otp/{token}', LoginConfirm::class)->name('admin.auth.login-resend-otp'); // اضافه شده
    Route::get('logout', Logout::class)->name('admin.auth.logout');
});
/* end login manager routes */
Route::prefix('dr/')->group(function () {
    Route::get('login', DoctorLoginRegister::class)->name('dr.auth.login-register-form');
    Route::get('login-user-pass', DoctorLoginUserPass::class)->name('dr.auth.login-user-pass-form');
    Route::get('two-factor/{token}', DoctorTwoFactor::class)->name('dr-two-factor');
    Route::get('login-confirm/{token}', DoctorLoginConfirm::class)->name('dr.auth.login-confirm-form');
    Route::get('login-resend-otp/{token}', DoctorLoginConfirm::class)->name('dr.auth.login-resend-otp'); // اضافه شده
    Route::get('logout', DoctorLogout::class)->name('dr.auth.logout');
});
Route::post('/send-message', [SendOtpController::class, 'sendMessage'])->name('send.message');
//  manager  routes
Route::prefix('admin')
    ->namespace('Admin')
    ->middleware('manager')
    ->group(function () {
        Route::prefix('footercontents')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\FooterContent\FooterContentController::class, 'index'])->name('admin.panel.footercontents.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\FooterContent\FooterContentController::class, 'create'])->name('admin.panel.footercontents.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\FooterContent\FooterContentController::class, 'edit'])->name('admin.panel.footercontents.edit');
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
            Route::get('/', [\App\Http\Controllers\Admin\Panel\TreatmentCenter\TreatmentCenterController::class, 'index'])->name('admin.panel.treatmentcenters.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\TreatmentCenter\TreatmentCenterController::class, 'create'])->name('admin.panel.treatmentcenters.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\TreatmentCenter\TreatmentCenterController::class, 'edit'])->name('admin.panel.treatmentcenters.edit');
            Route::get('/gallery/{id}', [\App\Http\Controllers\Admin\Panel\TreatmentCenter\TreatmentCenterController::class, 'gallery'])
                ->name('admin.panel.treatmentcenters.gallery');
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

        Route::prefix('doctorservices')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\DoctorService\DoctorServiceController::class, 'index'])->name('admin.panel.doctorservices.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\DoctorService\DoctorServiceController::class, 'create'])->name('admin.panel.doctorservices.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\DoctorService\DoctorServiceController::class, 'edit'])->name('admin.panel.doctorservices.edit');
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

        Route::prefix('bestdoctors')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\Panel\BestDoctor\BestDoctorController::class, 'index'])->name('admin.panel.bestdoctors.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\BestDoctor\BestDoctorController::class, 'create'])->name('admin.panel.bestdoctors.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\BestDoctor\BestDoctorController::class, 'edit'])->name('admin.panel.bestdoctors.edit');
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
            Route::get('/', [\App\Http\Controllers\Admin\Panel\BannerText\BannerTextController::class, 'index'])->name('admin.panel.bannertexts.index');
            Route::get('/create', [\App\Http\Controllers\Admin\Panel\BannerText\BannerTextController::class, 'create'])->name('admin.panel.bannertexts.create');
            Route::get('/edit/{id}', [\App\Http\Controllers\Admin\Panel\BannerText\BannerTextController::class, 'edit'])->name('admin.panel.bannertexts.edit');
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
        Route::get('/doctor-login/{doctor}', function (\App\Models\Doctor $doctor) {
            // لاگین کردن دکتر با گارد doctor
            Auth::guard('doctor')->login($doctor);

            // ریدایرکت به پنل دکتر
            return redirect()->route('dr-panel');
        })->name('doctor.login');

        Route::get('dashboard/', [AdminDashboardController::class, 'index'])->name('admin-panel');
        Route::post('/upload-profile-photo', [AdminProfileController::class, 'uploadPhoto'])->name('admin.upload-photo')->middleware('auth:manager');
        Route::prefix('tools/')->group(function () {
            Route::get('/file-manager', [FileManagerController::class, 'index'])->name('admin.panel.tools.file-manager')->middleware('auth:manager');
            Route::prefix('payment_gateways/')->group(function () {
                Route::get('/', [PaymentGatewaysController::class, 'index'])->name('admin.panel.tools.payment_gateways.index');
                Route::get('/edit/{name}', [PaymentGatewaysController::class, 'edit'])->name('admin.panel.tools.payment_gateways.edit');
                Route::put('/payment-gateways/{name}', [PaymentGatewaysController::class, 'update'])->name('admin.payment_gateways.update');
                Route::post('/payment-gateways/toggle', [PaymentGatewaysController::class, 'toggle'])->name('admin.payment_gateways.toggle');
                Route::delete('/payment-gateways/{name}', [PaymentGatewaysController::class, 'destroy'])->name('admin.panel.tools.payment_gateways.destroy');
            });
            Route::prefix('sms_gateway')->group(function () {
                Route::get('/', [SmsGatewayController::class, 'index'])->name('admin.panel.tools.sms-gateways.index');
                Route::get('/edit/{name}', [SmsGatewayController::class, 'edit'])->name('admin.panel.tools.sms-gateways.edit');
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
    });

// end manager  routes
// dr routes
Route::prefix('dr')
    ->namespace('Dr')
    ->group(function () {


        Route::prefix('panel')->middleware(['doctor', 'secretary', 'complete-profile'])->group(function () {
            Route::get('/', [DrPanelController::class, 'index'])->middleware('secretary.permission:dashboard')->name('dr-panel');


            Route::post('appointments/{id}/end-visit', [DrPanelController::class, 'endVisit'])->name('doctor.end-visit');


            Route::get('/doctor/appointments/by-date', [DrPanelController::class, 'getAppointmentsByDate'])
                ->name('doctor.appointments.by-date');
            Route::get('/search/patients', [DrPanelController::class, 'searchPatients'])->name('search.patients');
            Route::post('/appointments/update-date/{id}', [DrPanelController::class, 'updateAppointmentDate'])
                ->name('updateAppointmentDate');
            Route::prefix('doctornotes')->group(function () {
                Route::get('/', [\App\Http\Controllers\Dr\Panel\DoctorNote\DoctorNoteController::class, 'index'])->name('dr.panel.doctornotes.index');
                Route::get('/create', [\App\Http\Controllers\Dr\Panel\DoctorNote\DoctorNoteController::class, 'create'])->name('dr.panel.doctornotes.create');
                Route::get('/edit/{id}', [\App\Http\Controllers\Dr\Panel\DoctorNote\DoctorNoteController::class, 'edit'])->name('dr.panel.doctornotes.edit');
            });
            Route::get('/doctor/appointments/filter', [DrPanelController::class, 'filterAppointments'])->name('doctor.appointments.filter');

            Route::prefix('turn')->middleware('secretary.permission:appointments')->group(function () {
                Route::prefix('schedule')->group(function () {
                    Route::get('/appointments', [DrScheduleController::class, 'index'])->middleware('secretary.permission:appointments')->name('dr-appointments');

                    Route::get('search-appointments', [DrScheduleController::class, 'searchAppointments'])->name('dr.search.appointments');

                    Route::post('end-visit/{id}', [DrScheduleController::class, 'endVisit'])->name('end.visit');

                    Route::get('/my-appointments', [DrScheduleController::class, 'myAppointments'])->middleware('secretary.permission:my-appointments')->name('my-dr-appointments');

                    Route::get('/my-appointments/by-date', [DrScheduleController::class, 'showByDateAppointments'])->name('dr.turn.my-appointments.by-date');
                    Route::get('filter-appointments', [DrScheduleController::class, 'filterAppointments'])->name('dr.turn.filter-appointments');

                    Route::get('/moshavere_setting', [DrMoshavereSettingController::class, 'index'])->middleware('secretary.permission:appointments')->name('dr-moshavere_setting');
                    Route::post('/copy-work-hours-counseling', [DrMoshavereSettingController::class, 'copyWorkHours'])->middleware('secretary.permission:appointments')->name('copy-work-hours-counseling');
                    Route::get('get-work-schedule-counseling', [DrMoshavereSettingController::class, 'getWorkSchedule'])->middleware('secretary.permission:appointments')->name('dr-get-work-schedule-counseling');
                    Route::post('/copy-single-slot-counseling', [DrMoshavereSettingController::class, 'copySingleSlot'])->middleware('secretary.permission:appointments')->name('copy-single-slot-counseling');
                    Route::post('/save-time-slot-counseling', [DrMoshavereSettingController::class, 'saveTimeSlot'])->middleware('secretary.permission:appointments')->name('save-time-slot-counseling');
                    Route::get('/get-appointment-settings-counseling', [DrMoshavereSettingController::class, 'getAppointmentSettings'])->middleware('secretary.permission:appointments')->name('get-appointment-settings-counseling');
                    Route::delete('/appointment-slots-conseling/{id}', [DrMoshavereSettingController::class, 'destroy'])->middleware('secretary.permission:appointments')->name('appointment.slots.destroy-counseling');
                    Route::post('save-work-schedule-counseling', [DrMoshavereSettingController::class, 'saveWorkSchedule'])->middleware('secretary.permission:appointments')->name('dr-save-work-schedule-counseling');
                    Route::post('/dr/update-work-day-status-counseling', [DrMoshavereSettingController::class, 'updateWorkDayStatus'])->middleware('secretary.permission:appointments')->name('update-work-day-status-counseling');
                    Route::post('/update-auto-scheduling-counseling', [DrMoshavereSettingController::class, 'updateAutoScheduling'])->middleware('secretary.permission:appointments')->name('update-auto-scheduling-counseling');
                    Route::get('/get-all-days-settings-counseling', [DrMoshavereSettingController::class, 'getAllDaysSettings'])->middleware('secretary.permission:appointments')->name('get-all-days-settings-counseling');
                    Route::post('/save-appointment-settings-counseling', [DrMoshavereSettingController::class, 'saveAppointmentSettings'])->middleware('secretary.permission:appointments')->name('save-appointment-settings-counseling');
                    Route::post('/delete-schedule-setting-counseling', [DrMoshavereSettingController::class, 'deleteScheduleSetting'])->middleware('secretary.permission:appointments')->name('delete-schedule-setting-counseling');
                    Route::get('/moshavere_waiting', [MoshavereWaitingController::class, 'index'])->middleware('secretary.permission:appointments')->name('dr-moshavere_waiting');
                    Route::get('/manual_nobat', [ManualNobatController::class, 'index'])->middleware('secretary.permission:appointments')->name('dr-manual_nobat');
                    Route::post('manual_nobat/store', [ManualNobatController::class, 'store'])->middleware('secretary.permission:appointments')->name('manual-nobat.store');
                    Route::post('manual-nobat/store-with-user', [ManualNobatController::class, 'storeWithUser'])->middleware('secretary.permission:appointments')->name('manual-nobat.store-with-user');
                    Route::delete('/manual_appointments/{id}', [ManualNobatController::class, 'destroy'])->middleware('secretary.permission:appointments')->name('manual_appointments.destroy');
                    Route::get('/manual_appointments/{id}/edit', [ManualNobatController::class, 'edit'])->middleware('secretary.permission:appointments')->name('manual-appointments.edit');
                    Route::post('/manual_appointments/{id}', [ManualNobatController::class, 'update'])->middleware('secretary.permission:appointments')->name('manual-appointments.update');
                    Route::post('/manual-nobat/settings/save', [ManualNobatController::class, 'saveSettings'])->middleware('secretary.permission:appointments')->name('manual-nobat.settings.save');

                    Route::get('/manual_nobat_setting', [ManualNobatController::class, 'showSettings'])->middleware('secretary.permission:appointments')->name('dr-manual_nobat_setting');
                    Route::get('/search-users', [ManualNobatController::class, 'searchUsers'])->middleware('secretary.permission:appointments')->name('dr-panel-search.users');
                    Route::get('/scheduleSetting', [ScheduleSettingController::class, 'index'])->middleware('secretary.permission:appointments')->name('dr-scheduleSetting');
                    Route::prefix('scheduleSetting/vacation')->group(function () {
                        Route::get('/', [VacationController::class, 'index'])->middleware('secretary.permission:appointments')->name('dr-vacation');
                        Route::post('/store', [VacationController::class, 'store'])->middleware('secretary.permission:appointments')->name('doctor.vacation.store');
                        Route::post('/update/{id}', [VacationController::class, 'update'])->middleware('secretary.permission:appointments')->name('doctor.vacation.update');
                        Route::delete('/delete/{id}', [VacationController::class, 'destroy'])->middleware('secretary.permission:appointments')->name('doctor.vacation.destroy');
                        Route::get('/doctor/vacation/{id}/edit', [VacationController::class, 'edit'])->middleware('secretary.permission:appointments')->name('doctor.vacation.edit');
                    });

                    Route::prefix('scheduleSetting/blocking_users')->group(function () {
                        Route::get('/', [BlockingUsersController::class, 'index'])
                            ->middleware('secretary.permission:appointments')
                            ->name('doctor-blocking-users.index');

                        Route::post('/store', [BlockingUsersController::class, 'store'])
                            ->middleware('secretary.permission:appointments')
                            ->name('doctor-blocking-users.store');

                        // اضافه کردن روت جدید برای مسدود کردن گروهی کاربران
                        Route::post('/store-multiple', [BlockingUsersController::class, 'storeMultiple'])
                            ->middleware('secretary.permission:appointments')
                            ->name('doctor-blocking-users.store-multiple');

                        Route::post('/send-message', [BlockingUsersController::class, 'sendMessage'])
                            ->middleware('secretary.permission:appointments')
                            ->name('doctor-blocking-users.send-message');

                        Route::get('/messages', [BlockingUsersController::class, 'getMessages'])
                            ->middleware('secretary.permission:appointments')
                            ->name('doctor-blocking-users.messages');

                        Route::delete('/doctor-blocking-users/{id}', [BlockingUsersController::class, 'destroy'])
                            ->middleware('secretary.permission:appointments')
                            ->name('doctor-blocking-users.destroy');

                        Route::patch('/update-status', [BlockingUsersController::class, 'updateStatus'])
                            ->middleware('secretary.permission:appointments')
                            ->name('doctor-blocking-users.update-status');

                        Route::delete('/messages/{id}', [BlockingUsersController::class, 'deleteMessage'])
                            ->middleware('secretary.permission:appointments')
                            ->name('doctor-blocking-users.delete-message');
                    });

                    Route::get('/scheduleSetting/workhours', [ScheduleSettingController::class, 'workhours'])->middleware('secretary.permission:appointments')->name('dr-workhours');
                    Route::post('/save-appointment-settings', [ScheduleSettingController::class, 'saveAppointmentSettings'])->middleware('secretary.permission:appointments')->name('save-appointment-settings');
                    Route::get('/get-appointment-settings', [ScheduleSettingController::class, 'getAppointmentSettings'])->middleware('secretary.permission:appointments')->name('get-appointment-settings');
                    Route::post('/delete-schedule-setting', [ScheduleSettingController::class, 'deleteScheduleSetting'])->middleware('secretary.permission:appointments')->name('delete-schedule-setting');
                    Route::get('/get-all-days-settings', [ScheduleSettingController::class, 'getAllDaysSettings'])->middleware('secretary.permission:appointments')->name('get-all-days-settings');
                    // ذخیره‌سازی تنظیمات ساعات کاری
                    Route::post('save-work-schedule', [ScheduleSettingController::class, 'saveWorkSchedule'])->middleware('secretary.permission:appointments')->name('dr-save-work-schedule');
                    Route::post('save-schedule', [ScheduleSettingController::class, 'saveSchedule'])->middleware('secretary.permission:appointments')->name('save-schedule');
                    Route::delete('/appointment-slots/{id}', [ScheduleSettingController::class, 'destroy'])->middleware('secretary.permission:appointments')->name('appointment.slots.destroy');
                    // بازیابی تنظیمات ساعات کاری
                    Route::get('get-work-schedule', [ScheduleSettingController::class, 'getWorkSchedule'])->middleware('secretary.permission:appointments')->name('dr-get-work-schedule');
                    Route::post('/dr/update-work-day-status', [ScheduleSettingController::class, 'updateWorkDayStatus'])->middleware('secretary.permission:appointments')->name('update-work-day-status');
                    Route::post('/check-day-slots', [ScheduleSettingController::class, 'checkDaySlots'])->middleware('secretary.permission:appointments')->name('check-day-slots');
                    Route::post('/update-auto-scheduling', [ScheduleSettingController::class, 'updateAutoScheduling'])->middleware('secretary.permission:appointments')->name('update-auto-scheduling');
                    // routes/web.php
                    Route::post('/copy-work-hours', [ScheduleSettingController::class, 'copyWorkHours'])->middleware('secretary.permission:appointments')->name('copy-work-hours');
                    Route::post('/copy-single-slot', [ScheduleSettingController::class, 'copySingleSlot'])->middleware('secretary.permission:appointments')->name('copy-single-slot');

                    Route::post('/save-time-slot', [ScheduleSettingController::class, 'saveTimeSlot'])->middleware('secretary.permission:appointments')->name('save-time-slot');
                    Route::get('/scheduleSetting/my-special-days', [ScheduleSettingController::class, 'mySpecialDays'])->middleware('secretary.permission:appointments')->name('dr-mySpecialDays');
                    Route::get('/scheduleSetting/counseling/my-special-days', [MySpecialDaysCounselingController::class, 'mySpecialDays'])->middleware('secretary.permission:appointments')->name('dr-mySpecialDays-counseling');
                    Route::get('/doctor/default-schedule', [ScheduleSettingController::class, 'getDefaultSchedule'])->name('doctor.get_default_schedule');
                    Route::get('/doctor/default-schedule-counseling', [MySpecialDaysCounselingController::class, 'getDefaultSchedule'])->name('doctor.get_default_schedule_counseling');

                    Route::post('/doctor/update-work-schedule', [ScheduleSettingController::class, 'updateWorkSchedule'])->name('doctor.update_work_schedule');
                    Route::post('/doctor/update-work-schedule-counseling', [MySpecialDaysCounselingController::class, 'updateWorkSchedule'])->name('doctor.update_work_schedule_counseling');
                    Route::get('/appointments-count', [ScheduleSettingController::class, 'getAppointmentsCountPerDay'])->middleware('secretary.permission:appointments')->name('appointments.count');
                    Route::get('/appointments-count-counseling', [MySpecialDaysCounselingController::class, 'getAppointmentsCountPerDay'])->middleware('secretary.permission:appointments')->name('appointments.count.counseling');
                    Route::get('/appointments/by-date', [ScheduleSettingController::class, 'getAppointmentsByDate'])->middleware('secretary.permission:appointments')->name('appointments.by_date');
                    Route::post('/doctor/add-holiday', [ScheduleSettingController::class, 'addHoliday'])->middleware('secretary.permission:appointments')->name('doctor.add_holiday');
                    Route::get('/doctor/get-holidays', [ScheduleSettingController::class, 'getHolidayDates'])->middleware('secretary.permission:appointments')->name('doctor.get_holidays');
                    Route::get('/doctor/get-holidays-counseling', [MySpecialDaysCounselingController::class, 'getHolidayDates'])->middleware('secretary.permission:appointments')->name('doctor.get_holidays_counseling');
                    Route::post('/doctor/toggle-holiday', [ScheduleSettingController::class, 'toggleHolidayStatus'])->middleware('secretary.permission:appointments')->name('doctor.toggle_holiday');
                    Route::post('/doctor/toggle-holiday-counseling', [MySpecialDaysCounselingController::class, 'toggleHolidayStatus'])->middleware('secretary.permission:appointments')->name('doctor.toggle_holiday_counseling');
                    Route::post('/doctor/holiday-status', [ScheduleSettingController::class, 'getHolidayStatus'])->middleware('secretary.permission:appointments')->name('doctor.get_holiday_status');
                    Route::post('/doctor/holiday-status-counseling', [MySpecialDaysCounselingController::class, 'getHolidayStatus'])->middleware('secretary.permission:appointments')->name('doctor.get_holiday_status_counseling');
                    Route::post('/doctor/cancel-appointments', [ScheduleSettingController::class, 'cancelAppointments'])->middleware('secretary.permission:appointments')->name('doctor.cancel_appointments');
                    Route::post('/doctor/cancel-appointments-counseling', [MySpecialDaysCounselingController::class, 'cancelAppointments'])->middleware('secretary.permission:appointments')->name('doctor.cancel_appointments_counseling');

                    Route::post('/doctor/reschedule-appointment', [ScheduleSettingController::class, 'rescheduleAppointment'])->middleware('secretary.permission:appointments')->name('doctor.reschedule_appointment');
                    Route::post('/doctor/reschedule-appointment-counseling', [MySpecialDaysCounselingController::class, 'rescheduleAppointment'])->middleware('secretary.permission:appointments')->name('doctor.reschedule_appointment_counseling');
                    Route::get('/turnContract', [ScheduleSettingController::class, 'turnContract'])->middleware('secretary.permission:appointments')->name('dr-scheduleSetting-turnContract');
                    Route::post('/update-first-available-appointment', [ScheduleSettingController::class, 'updateFirstAvailableAppointment'])->middleware('secretary.permission:appointments')->name('doctor.update_first_available_appointment');
                    Route::post('/update-first-available-appointment-counseling', [MySpecialDaysCounselingController::class, 'updateFirstAvailableAppointment'])->middleware('secretary.permission:appointments')->name('doctor.update_first_available_appointment_counseling');
                    Route::get('get-next-available-date', [ScheduleSettingController::class, 'getNextAvailableDate'])->middleware('secretary.permission:appointments')->name('doctor.get_next_available_date');
                    Route::get('get-next-available-date-counseling', [MySpecialDaysCounselingController::class, 'getNextAvailableDate'])->middleware('secretary.permission:appointments')->name('doctor.get_next_available_date_counseling');
                    Route::delete('/appointments/destroy/{id}', [AppointmentController::class, 'destroyAppointment'])->middleware('secretary.permission:appointments')->name('appointments.destroy');
                    Route::post('/toggle-auto-pattern/{id}', [AppointmentController::class, 'toggleAutoPattern'])->middleware('secretary.permission:appointments')->name('toggle-auto-pattern');
                });
                Route::prefix('Counseling')->group(function () {
                    Route::get('/consult-term', [ConsultTermController::class, 'index'])->middleware('secretary.permission:appointments')->name('consult-term.index');
                });
                Route::post('/update-auto-schedule', [DrScheduleController::class, 'updateAutoSchedule'])->middleware('secretary.permission:appointments')->name('update-auto-schedule');
                Route::get('/check-auto-schedule', [DrScheduleController::class, 'checkAutoSchedule'])->middleware('secretary.permission:appointments')->name('check-auto-schedule');
                Route::get('get-available-times', [DrScheduleController::class, 'getAvailableTimes'])->middleware('secretary.permission:appointments')->name('getAvailableTimes');
                Route::post('update-day-status', [DrScheduleController::class, 'updateDayStatus'])->middleware('secretary.permission:appointments')->name('updateDayStatus');
                Route::get('disabled-days', [DrScheduleController::class, 'disabledDays'])->middleware('secretary.permission:appointments')->name('disabledDays');
                Route::post('/convert-to-gregorian', [AppointmentController::class, 'convertToGregorian'])->middleware('secretary.permission:appointments')->name('convert-to-gregorian');
                Route::get('/search-appointments', [AppointmentController::class, 'searchAppointments'])->middleware('secretary.permission:appointments')->name('search.appointments');
                Route::get('/turnsCatByDays', [TurnsCatByDaysController::class, 'index'])->middleware('secretary.permission:appointments')->name('dr-turnsCatByDays');
                Route::post('/appointments/{id}/status', [AppointmentController::class, 'updateStatus'])->middleware('secretary.permission:appointments')->name('updateStatusAppointment');
            });

            Route::get('/patient-records', [PatientRecordsController::class, 'index'])->middleware('secretary.permission:patient_records')->name('dr-patient-records');
            Route::prefix('tickets')->group(function () {
                Route::get('/', [TicketsController::class, 'index'])->name('dr-panel-tickets');
                Route::post('/store', [TicketsController::class, 'store'])->name('dr-panel-tickets.store');

                Route::delete('/destroy/{id}', [TicketsController::class, 'destroy'])->name('dr-panel-tickets.destroy');
                Route::get('/show/{id}', [TicketsController::class, 'show'])->name('dr-panel-tickets.show');

                // مسیرهای مربوط به پاسخ تیکت‌ها
                Route::post('/{id}/responses', [TicketResponseController::class, 'store'])->name('dr-panel-tickets.responses.store');
            });

            Route::get('activation/consult/rules', [ConsultRulesController::class, 'index'])->middleware('secretary.permission:consult')->name('activation.consult.rules');
            Route::get('activation/consult/help', [ConsultRulesController::class, 'help'])->middleware('secretary.permission:consult')->name('activation.consult.help');
            Route::get('activation/consult/messengers', [ConsultRulesController::class, 'messengers'])->middleware('secretary.permission:consult')->name('activation.consult.messengers');
            Route::get('my-performance/', [MyPerformanceController::class, 'index'])->middleware('secretary.permission:statistics')->name('dr-my-performance');
            Route::get('my-performance/doctor-chart', [MyPerformanceController::class, 'chart'])->middleware('secretary.permission:statistics')->name('dr-my-performance-chart');
            Route::get('my-performance/chart-data', [MyPerformanceController::class, 'getChartData'])
                ->name('dr-my-performance-chart-data');

            Route::group(['prefix' => 'secretary'], function () {
                Route::get('/', [SecretaryManagementController::class, 'index'])->middleware('secretary.permission:secretary_management')->name('dr-secretary-management');
                Route::post('/store', [SecretaryManagementController::class, 'store'])->middleware('secretary.permission:secretary_management')->name('dr-secretary-store');
                Route::get('/edit/{id}', [SecretaryManagementController::class, 'edit'])->middleware('secretary.permission:secretary_management')->name('dr-secretary-edit');
                Route::post('/update/{id}', [SecretaryManagementController::class, 'update'])->middleware('secretary.permission:secretary_management')->name('dr-secretary-update');
                Route::delete('/delete/{id}', [SecretaryManagementController::class, 'destroy'])->middleware('secretary.permission:secretary_management')->name('dr-secretary-delete');
            });

            Route::group(['prefix' => 'DoctorsClinic'], function () {
                Route::get('activation/{clinic}', [ActivationDoctorsClinicController::class, 'index'])->middleware('secretary.permission:clinic_management')->name('activation-doctor-clinic');
                Route::post('/dr/panel/DoctorsClinic/activation/{id}/update-address', [ActivationDoctorsClinicController::class, 'updateAddress'])->middleware('secretary.permission:clinic_management')->name('doctors.clinic.update.address');
                Route::get('/doctors/clinic/{id}/phones', [ActivationDoctorsClinicController::class, 'getPhones'])->middleware('secretary.permission:clinic_management')->name('doctors.clinic.get.phones');
                Route::post('/doctors/clinic/{id}/phones', [ActivationDoctorsClinicController::class, 'updatePhones'])->middleware('secretary.permission:clinic_management')->name('doctors.clinic.update.phones');
                Route::post('/doctors/clinic/{id}/phones/delete', [ActivationDoctorsClinicController::class, 'deletePhone'])->middleware('secretary.permission:clinic_management')->name('doctors.clinic.delete.phone');
                Route::get('/clinic/{id}/secretary-phone', [ActivationDoctorsClinicController::class, 'getSecretaryPhone'])->middleware('secretary.permission:clinic_management')->name('doctors.clinic.get.secretary.phone');
                Route::get('/activation/clinic/cost/{clinic}', [CostController::class, 'index'])->middleware('secretary.permission:clinic_management')->name('doctors.clinic.cost');
                Route::get('/costs/{clinic_id}/list', [CostController::class, 'listDeposits'])->middleware('secretary.permission:clinic_management')->name('cost.list');
                Route::post('/costs/delete', [CostController::class, 'deleteDeposit'])->middleware('secretary.permission:clinic_management')->name('cost.delete');
                Route::post('/doctors-clinic/duration/store', [DurationController::class, 'store'])->middleware('secretary.permission:clinic_management')->name('duration.store');

                Route::get('/activation/duration/{clinic}', [DurationController::class, 'index'])->middleware('secretary.permission:clinic_management')->name('duration.index');
                Route::get('/activation/workhours/{clinic}', [ActivationWorkhoursController::class, 'index'])->middleware('secretary.permission:clinic_management')->name('activation.workhours.index');
                Route::get('{clinicId}/{doctorId}', [ActivationWorkhoursController::class, 'getWorkHours'])->middleware('secretary.permission:clinic_management')->name('workhours.get');
                Route::post('/activation/workhours/store', [ActivationWorkhoursController::class, 'store'])->middleware('secretary.permission:clinic_management')->name('activation.workhours.store');
                Route::post('workhours/delete', [ActivationWorkhoursController::class, 'deleteWorkHours'])->middleware('secretary.permission:clinic_management')->name('activation.workhours.delete');
                Route::post('/dr/panel/start-appointment', [ActivationWorkhoursController::class, 'startAppointment'])->middleware('secretary.permission:clinic_management')->name('start.appointment');

                Route::post('/cost/store', [CostController::class, 'store'])->middleware('secretary.permission:clinic_management')->name('cost.store');
                Route::get('gallery', [DoctorsClinicManagementController::class, 'gallery'])->middleware('secretary.permission:clinic_management')->name('dr-office-gallery');
                Route::get('medicalDoc', [DoctorsClinicManagementController::class, 'medicalDoc'])->middleware('secretary.permission:clinic_management')->name('dr-office-medicalDoc');
                Route::get('/', [DoctorsClinicManagementController::class, 'index'])->middleware('secretary.permission:clinic_management')->name('dr-clinic-management');
                Route::post('/store', [DoctorsClinicManagementController::class, 'store'])->middleware('secretary.permission:clinic_management')->name('dr-clinic-store');
                Route::get('/dr/panel/DoctorsClinic/edit/{id}', [DoctorsClinicManagementController::class, 'edit'])->middleware('secretary.permission:clinic_management')->name('dr-clinic-edit');

                Route::post('/update/{id}', [DoctorsClinicManagementController::class, 'update'])->middleware('secretary.permission:clinic_management')->name('dr-clinic-update');
                Route::delete('/delete/{id}', [DoctorsClinicManagementController::class, 'destroy'])->middleware('secretary.permission:clinic_management')->name('dr-clinic-delete');
            });

            Route::get('permission/', [SecretaryPermissionController::class, 'index'])->middleware('secretary.permission:permissions')->name('dr-secretary-permissions');
            Route::post('/permission/update/{secretary_id}', [SecretaryPermissionController::class, 'update'])->middleware('secretary.permission:permissions')->name('dr-secretary-permissions-update');

            Route::group(['prefix' => 'noskhe-electronic'], function () {
                Route::get('prescription/', [PrescriptionController::class, 'index'])->middleware('secretary.permission:prescription')->name('prescription.index');
                Route::get('prescription/create', [PrescriptionController::class, 'create'])->middleware('secretary.permission:prescription')->name('prescription.create');
                Route::get('providers/', [ProvidersController::class, 'index'])->middleware('secretary.permission:prescription')->name('providers.index');
                Route::group(['prefix' => 'favorite'], function () {
                    Route::get('templates/', [FavoriteTemplatesController::class, 'index'])->middleware('secretary.permission:prescription')->name('favorite.templates.index');
                    Route::get('templates/create', [FavoriteTemplatesController::class, 'create'])->middleware('secretary.permission:prescription')->name('favorite.templates.create');
                    Route::get('templates/service', [ServiceController::class, 'index'])->middleware('secretary.permission:prescription')->name('templates.favorite.service.index');
                });
            });

            Route::get('bime', [DRBimeController::class, 'index'])->middleware('secretary.permission:insurance')->name('dr-bime');

            Route::prefix('payment')->group(function () {
                Route::get('/wallet', [DrPaymentSettingController::class, 'wallet'])->middleware('secretary.permission:financial_reports')->name('dr-wallet');
                Route::get('/setting', [DrPaymentSettingController::class, 'index'])->middleware('secretary.permission:financial_reports')->name('dr-payment-setting');
                Route::get('/charge', function () {
                    return view('dr.panel.payment.charge');
                })->middleware('secretary.permission:financial_reports')->name('dr-wallet-charge');
            });

            Route::prefix('profile')->group(function () {
                Route::get('edit-profile', [DrProfileController::class, 'edit'])->middleware('secretary.permission:profile')->name('dr-edit-profile');
                Route::post('/upload-profile-photo', [DrProfileController::class, 'uploadPhoto'])->name('dr.upload-photo')->middleware('auth:doctor');
                Route::post('update-profile', [DrProfileController::class, 'update_profile'])->middleware('secretary.permission:profile')->name('dr-update-profile');
                Route::get('/dr-check-profile-completeness', [DrProfileController::class, 'checkProfileCompleteness'])->middleware('secretary.permission:profile')->name('dr-check-profile-completeness');
                Route::post('/send-mobile-otp', [DrProfileController::class, 'sendMobileOtp'])->middleware('secretary.permission:profile')->name('dr-send-mobile-otp');
                Route::post('/mobile-confirm/{token}', [DrProfileController::class, 'mobileConfirm'])->middleware('secretary.permission:profile')->name('dr-mobile-confirm');
                Route::post('/dr-specialty-update', [DrProfileController::class, 'DrSpecialtyUpdate'])->middleware('secretary.permission:profile')->name('dr-specialty-update');
                Route::delete('/dr/delete-specialty/{id}', [DrProfileController::class, 'deleteSpecialty'])->middleware('secretary.permission:profile')->name('dr-delete-specialty');
                Route::post('/dr-uuid-update', [DrProfileController::class, 'DrUUIDUpdate'])->middleware('secretary.permission:profile')->name('dr-uuid-update');
                Route::put('/dr-profile-messengers', [DrProfileController::class, 'updateMessengers'])->middleware('secretary.permission:profile')->name('dr-messengers-update');
                Route::post('dr-static-password-update', [DrProfileController::class, 'updateStaticPassword'])->middleware('secretary.permission:profile')->name('dr-static-password-update');
                Route::post('dr-two-factor-update', [DrProfileController::class, 'updateTwoFactorAuth'])->middleware('secretary.permission:profile')->name('dr-two-factor-update');
                Route::get('niceId', [DrProfileController::class, 'niceId'])->middleware('secretary.permission:profile')->name('dr-edit-profile-niceId');
                Route::get('security', [LoginLogsController::class, 'security'])->middleware('secretary.permission:profile')->name('dr-edit-profile-security');
                Route::get('/dr/panel/profile/security/doctor-logs', [LoginLogsController::class, 'getDoctorLogs'])->name('dr-get-doctor-logs');
                Route::get('/dr/panel/profile/security/secretary-logs', [LoginLogsController::class, 'getSecretaryLogs'])->name('dr-get-secretary-logs');
                Route::delete('/dr/panel/profile/security/logs/{id}', [LoginLogsController::class, 'deleteLog'])->middleware('secretary.permission:profile')->name('delete-log');

                Route::get('upgrade', [DrUpgradeProfileController::class, 'index'])->middleware('secretary.permission:profile')->name('dr-edit-profile-upgrade');
                Route::delete('/doctor/payments/delete/{id}', [DrUpgradeProfileController::class, 'deletePayment'])->name('dr-payment-delete');
                Route::post('/pay', [DrUpgradeProfileController::class, 'payForUpgrade'])->name('doctor.upgrade.pay');
                Route::get('subuser', [SubUserController::class, 'index'])->middleware('secretary.permission:profile')->name('dr-subuser');
                Route::post('sub-users/store', [SubUserController::class, 'store'])->name('dr-sub-users-store');
                Route::get('sub-users/edit/{id}', [SubUserController::class, 'edit'])->name('dr-sub-users-edit');
                Route::post('sub-users/update/{id}', [SubUserController::class, 'update'])->name('dr-sub-users-update');
                Route::delete('sub-users/delete/{id}', [SubUserController::class, 'destroy'])->name('dr-sub-users-delete');
            });
            Route::prefix('dr-services')->group(function () {
                Route::get('/', [DoctorServicesController::class, 'index'])->name('dr-services.index');
                Route::get('/create', [DoctorServicesController::class, 'create'])->name('dr-services.create');
                Route::post('/store', [DoctorServicesController::class, 'store'])->name('dr-services.store');

                // مسیر ویرایش
                Route::get('/{service}/edit', [DoctorServicesController::class, 'edit'])->name('dr-services.edit');

                // مسیر به‌روزرسانی
                Route::put('/{service}', [DoctorServicesController::class, 'update'])->name('dr-services.update');

                // مسیر حذف (حتماً پارامتر {service} داشته باشد)
                Route::delete('/{service}', [DoctorServicesController::class, 'destroy'])->name('dr-services.destroy');
            });

        });
    });

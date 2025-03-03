<?php
use Mockery\Container;
use Illuminate\Support\Facades\Route;
use App\Models\Dr\SecretaryPermission;
use App\Models\Admin\Dashboard\Cities\Zone;
use App\Http\Controllers\Admin\layouts\Blank;
use App\Http\Controllers\Admin\layouts\Fluid;
use App\Http\Controllers\Dr\Auth\LoginController;
use App\Http\Controllers\Admin\layouts\Horizontal;
use App\Http\Controllers\Admin\Dashboard\Dashboard;
use App\Http\Controllers\Admin\layouts\WithoutMenu;
use App\Http\Controllers\Dr\Panel\DrPanelController;
use App\Http\Controllers\Admin\Agent\AgentController;
use App\Http\Controllers\Admin\layouts\CollapsedMenu;
use App\Http\Controllers\Admin\layouts\ContentNavbar;
use App\Http\Controllers\Admin\layouts\WithoutNavbar;
use App\Http\Controllers\Admin\Tools\ToolsController;
use App\Http\Controllers\Dr\Panel\Bime\DRBimeController;
use App\Http\Controllers\Admin\layouts\ContentNavSidebar;
use App\Http\Controllers\Admin\Authentications\LoginBasic;
use App\Http\Controllers\Admin\Authentications\LoginCover;
use App\Http\Controllers\Admin\Ads\Banner\BannerController;
use App\Http\Controllers\Admin\Agent\AgentWalletController;
use App\Http\Controllers\Admin\language\LanguageController;
use App\Http\Controllers\Dr\Panel\Profile\SubUserController;
use App\Http\Controllers\Dr\Panel\Tickets\TicketsController;
use App\Http\Controllers\Dr\Panel\Turn\DrScheduleController;
use App\Http\Controllers\Secretary\Auth\SecretaryController;
use App\Http\Controllers\Admin\Authentications\RegisterBasic;
use App\Http\Controllers\Admin\Authentications\RegisterCover;
use App\Http\Controllers\Admin\Authentications\TwoStepsBasic;
use App\Http\Controllers\Admin\Authentications\TwoStepsCover;
use App\Http\Controllers\Admin\Dashboard\Amar\AmarController;
use App\Http\Controllers\Admin\Dashboard\Menu\MenuController;
use App\Http\Controllers\Dr\Panel\Profile\DrProfileController;
use App\Http\Controllers\Dr\Panel\Profile\LoginLogsController;
use App\Http\Controllers\Admin\Tools\SiteMap\SiteMapController;
use App\Http\Controllers\Admin\Authentications\VerifyEmailBasic;
use App\Http\Controllers\Admin\Authentications\VerifyEmailCover;
use App\Http\Controllers\Dr\Turn\Schedule\AppointmentController;
use App\Http\Controllers\Admin\Application\ApplicationController;
use App\Http\Controllers\Admin\Dashboard\Cities\CitiesController;
use App\Http\Controllers\Admin\Tools\Redirect\RedirectController;
use App\Http\Controllers\Admin\Ads\Banner\AdsLog\AdsLogController;
use App\Http\Controllers\Admin\Authentications\RegisterMultiSteps;
use App\Http\Controllers\Admin\Authentications\ResetPasswordBasic;
use App\Http\Controllers\Admin\Authentications\ResetPasswordCover;
use App\Http\Controllers\Dr\Panel\Payment\Wallet\WalletController;
use App\Http\Controllers\Admin\Authentications\ForgotPasswordCover;
use App\Http\Controllers\Admin\Dashboard\Holiday\HolidayController;
use App\Http\Controllers\Admin\Dashboard\Setting\SettingController;
use App\Http\Controllers\Admin\UsersManagement\Auth\AuthController;
use App\Http\Controllers\Dr\Panel\Tickets\TicketResponseController;
use App\Http\Controllers\Admin\ContentManagement\Blog\BlogController;
use App\Http\Controllers\Admin\ContentManagement\Tags\TagsController;
use App\Http\Controllers\Admin\Dashboard\HomePage\HomePageController;
use App\Http\Controllers\Admin\Doctors\Moshavere\MoshavereController;
use App\Http\Controllers\Admin\Questions\Question\QuestionController;
use App\Http\Controllers\Admin\Tools\NewsLatter\NewsLatterController;
use App\Http\Controllers\Admin\UsersManagement\Users\UsersController;
use App\Http\Controllers\Dr\Panel\Profile\DrUpgradeProfileController;
use App\Http\Controllers\Admin\Ads\Banner\Packages\PackagesController;
use App\Http\Controllers\Admin\ContentManagement\Links\LinksController;
use App\Http\Controllers\Admin\ContentManagement\Slide\SlideController;
use App\Http\Controllers\Admin\Dashboard\Specialty\SpecialtyController;
use App\Http\Controllers\Admin\Doctors\LogsDoctor\LogsDoctorController;
use App\Http\Controllers\Admin\Doctors\OrderVisit\OrderVisitController;
use App\Http\Controllers\Dr\Panel\MyPerformance\MyPerformanceController;
use App\Http\Controllers\Admin\Tools\MailTemplate\MailTemplateController;
use App\Http\Controllers\Dr\Panel\DoctorServices\DoctorServicesController;
use App\Http\Controllers\Dr\Panel\DoctorServices\DoctorServicesContrroler;
use App\Http\Controllers\Dr\Panel\PatientRecords\PatientRecordsController;
use App\Http\Controllers\Dr\Panel\Secretary\SecretaryManagementController;
use App\Http\Controllers\Admin\Dashboard\UserShipfee\UserShipfeeController;
use App\Http\Controllers\Admin\Questions\QuestionCat\QuestionCatController;
use App\Http\Controllers\Admin\ContentManagement\Comments\CommentController;
use App\Http\Controllers\Admin\Doctors\CommentDoctor\CommentDoctorController;
use App\Http\Controllers\Admin\Doctors\DoctorsManagement\Bime\BimeController;
use App\Http\Controllers\Admin\Hospitals\LogsHospital\LogsHospitalController;
use App\Http\Controllers\Admin\UsersManagement\UserGroup\UserGroupController;
use App\Http\Controllers\Dr\Panel\Payment\Setting\DrPaymentSettingController;
use App\Http\Controllers\Admin\ContentManagement\HomeVideo\HomeVideoController;
use App\Http\Controllers\Admin\Dashboard\Membershipfee\MembershipfeeController;
use App\Http\Controllers\Dr\Panel\DoctorsClinic\Activation\Cost\CostController;
use App\Http\Controllers\Dr\Panel\Turn\TurnsCatByDays\TurnsCatByDaysController;
use App\Http\Controllers\Admin\ContentManagement\FrontPages\FrontPagesController;
use App\Http\Controllers\Admin\UsersManagement\LogsUpgrade\LogsUpgradeController;
use App\Http\Controllers\Dr\Panel\NoskheElectronic\Providers\ProvidersController;
use App\Http\Controllers\Dr\Panel\Activation\Consult\Rules\ConsultRulesController;
use App\Http\Controllers\Dr\Panel\DoctorsClinic\DoctorsClinicManagementController;
use App\Http\Controllers\Dr\Panel\Turn\Schedule\ManualNobat\ManualNobatController;
use App\Http\Controllers\Admin\Dashboard\PaymentGateways\PaymentGatewaysController;
use App\Http\Controllers\Admin\Doctors\DoctorsManagement\Gallery\GalleryController;
use App\Http\Controllers\Dr\Panel\Turn\Schedule\ScheduleSetting\VacationController;
use App\Http\Controllers\Dr\Panel\SecretaryPermission\SecretaryPermissionController;
use App\Http\Controllers\Admin\ContentManagement\CategoryBlog\CategoryBlogController;
use App\Http\Controllers\Admin\Doctors\DoctorsManagement\DoctorsManagementController;
use App\Http\Controllers\Admin\UsersManagement\ChargeAccount\ChargeAccountController;
use App\Http\Controllers\Dr\Panel\NoskheElectronic\Favorite\Service\ServiceController;
use App\Http\Controllers\Dr\Panel\DoctorsClinic\Activation\Duration\DurationController;
use App\Http\Controllers\Dr\Panel\NoskheElectronic\Prescription\PrescriptionController;
use App\Http\Controllers\Admin\Doctors\WalletDoctorRequest\WalletDoctorRequestController;
use App\Http\Controllers\Dr\Panel\Turn\Schedule\ScheduleSetting\ScheduleSettingController;
use App\Http\Controllers\Admin\Hospitals\HospitalsManagement\HospitalsManagementController;
use App\Http\Controllers\Dr\Panel\Turn\Schedule\MoshavereWaiting\MoshavereWaitingController;
use App\Http\Controllers\Admin\UsersManagement\MembershipfeeLogs\MembershipfeeLogsController;
use App\Http\Controllers\Dr\Panel\DoctorsClinic\Activation\ActivationDoctorsClinicController;
use App\Http\Controllers\Dr\Panel\Turn\Schedule\Counseling\ConsultTerm\ConsultTermController;
use App\Http\Controllers\Admin\Hospitals\WalletHospitalRequest\WalletHospitalRequestController;
use App\Http\Controllers\Admin\UsersManagement\WalletCheckoutUser\WalletCheckoutUserController;
use App\Http\Controllers\Dr\Panel\NoskheElectronic\Favorite\Templates\FavoriteTemplatesController;
use App\Http\Controllers\Admin\UsersManagement\WalletCheckoutMonshi\WalletCheckoutMonshiController;
use App\Http\Controllers\Dr\Panel\DoctorsClinic\Activation\Workhours\ActivationWorkhoursController;
use App\Http\Controllers\Dr\Panel\Turn\Schedule\MoshavereSetting\MySpecialDaysCounselingController;
use App\Http\Controllers\Admin\Doctors\DoctorsManagement\MoshavereSetting\MoshavereSettingController;
use App\Http\Controllers\Admin\Doctors\DoctorsManagement\NobatdehiSetting\NobatdehiSettingController;
use App\Http\Controllers\Dr\Panel\Turn\Schedule\ScheduleSetting\BlockingUsers\BlockingUsersController;
use App\Http\Controllers\Admin\Hospitals\HospitalsManagement\DoctorsOfHospital\DoctorsOfHospitalController;
use App\Http\Controllers\Dr\Panel\Turn\Schedule\MoshavereSetting\MoshavereSettingController as DrMoshavereSettingController;
//manager login routes
/* login manager routes */
Route::prefix('admin-panel/')->group(function () {
    /* login routes */
    Route::get('login', [LoginBasic::class, 'loginRegisterForm'])->name('admin.auth.login-register-form');

    Route::get('login-user-pass', [LoginBasic::class, 'loginUserPassForm'])->name('admin.auth.login-user-pass-form');

    Route::get('admin-two-factor', [LoginBasic::class, 'twoFactorForm'])->name('admin-two-factor');

    Route::post('admin-two-factor-store', [LoginBasic::class, 'twoFactorFormCheck'])->name('admin-two-factor-store');

    Route::post('admin-login-with-mobile-pass', [LoginBasic::class, 'loginWithMobilePass'])->name('admin-login-with-mobile-pass');

    Route::post('/login-register', [LoginBasic::class, 'loginRegister'])->name('admin.auth.login-register');

    Route::get('login-confirm/{token}', [LoginBasic::class, 'loginConfirmForm'])->name('admin.auth.login-confirm-form');

    Route::post('/login-confirm/{token}', [LoginBasic::class, 'loginConfirm'])->name('admin.auth.login-confirm');

    Route::get('/login-resend-otp/{token}', [LoginBasic::class, 'loginResendOtp'])->name('admin.auth.login-resend-otp');

    Route::get('/logout', [LoginBasic::class, 'logout'])->name('admin.auth.logout');
    /* login routes */

});
/* end login manager routes */
//  manager  routes
Route::prefix('admin')
    ->namespace('Admin')
    ->middleware('manager')
    ->group(function () {
        // Main Page Route
        Route::get('/', [Dashboard::class, 'index'])->name('admin.index');
        Route::prefix('dashboard/')->group(function () {
            Route::prefix('cities/')->group(function () {
                Route::get('/', [CitiesController::class, 'index'])->name('admin.Dashboard.cities.index');
                Route::get('/search', [CitiesController::class, 'searchZone'])->name('admin.Dashboard.cities.search-zone');
                Route::get('/search-city', [CitiesController::class, 'searchZoneCity'])->name('admin.Dashboard.cities.search-zone-city');
                Route::get('/create', [CitiesController::class, 'create'])->name('admin.Dashboard.cities.create');
                Route::post('/store', [CitiesController::class, 'store'])->name('admin.Dashboard.cities.store');
                Route::post('/store-city', [CitiesController::class, 'storeCity'])->name('admin.Dashboard.cities.store-city');
                Route::get('/create-city', [CitiesController::class, 'createCity'])->name('admin.Dashboard.cities.create-city');
                Route::get('/edit/{id}', [CitiesController::class, 'edit'])->name('admin.Dashboard.cities.edit');
                Route::post('/update/{id}', [CitiesController::class, 'update'])->name('admin.Dashboard.cities.update');
                Route::get('/status/{zone}', [CitiesController::class, 'status'])->name('admin.Dashboard.cities.status');
                Route::get('/status-city/{zone}', [CitiesController::class, 'statusCity'])->name('admin.Dashboard.cities.status-city');
                Route::post('/update-city/{id}', [CitiesController::class, 'updateCity'])->name('admin.Dashboard.cities.update-city');
                Route::delete('/delete/{id}', [CitiesController::class, 'destroy'])->name('admin.Dashboard.cities.delete');
                Route::delete('/delete-city/{id}', [CitiesController::class, 'destroyCity'])->name('admin.Dashboard.cities.delete-city');
                Route::get('/edit-city/{id}', [CitiesController::class, 'editCity'])->name('admin.Dashboard.cities.edit-city');
                Route::get('/show/{id}', [CitiesController::class, 'show'])->name('admin.Dashboard.cities.show');
            });
            Route::prefix('specialty/')->group(function () {
                Route::get('/', [SpecialtyController::class, 'index'])->name('admin.Dashboard.specialty.index');
                Route::get('/create-specialty', [SpecialtyController::class, 'createSpecialty'])->name('admin.Dashboard.specialty.create-specialty');
                Route::get('/create', function () {
                    return view('admin.content.dashboard.specialty.create');
                })->name('admin.Dashboard.specialty.create');
                Route::post('/store', [SpecialtyController::class, 'store'])->name('admin.Dashboard.specialty.store');
                Route::post('/store-specialty', [SpecialtyController::class, 'storeSpecialty'])->name('admin.Dashboard.specialty.store-specialty');
                Route::get('/edit/{id}', [SpecialtyController::class, 'edit'])->name('admin.Dashboard.specialty.edit');
                Route::get('/edit-specialty/{id}', [SpecialtyController::class, 'editSpecialty'])->name('admin.Dashboard.specialty.edit-specialty');
                Route::post('/update-specialty/{id}', [SpecialtyController::class, 'updateSpecialty'])->name('admin.Dashboard.specialty.update-specialty');
                Route::post('/update/{id}', [SpecialtyController::class, 'update'])->name('admin.Dashboard.specialty.update');
                Route::get('/show/{id}', [SpecialtyController::class, 'show'])->name('admin.Dashboard.specialty.show');
                Route::get('/status/{specialty}', [SpecialtyController::class, 'status'])->name('admin.Dashboard.specialty.status');
                Route::get('/status-specialty/{specialty}', [SpecialtyController::class, 'statusSpecialty'])->name('admin.Dashboard.specialty.status-specialty');
                Route::get('/search-specialty', [SpecialtyController::class, 'searchSpecialty'])->name('admin.Dashboard.specialty.search-specialty');
                Route::delete('/delete/{id}', [SpecialtyController::class, 'destroy'])->name('admin.Dashboard.specialty.delete');
                Route::delete('/delete-specialty/{id}', [SpecialtyController::class, 'destroySpecialty'])->name('admin.Dashboard.specialty.delete-specialty');
            });
            Route::prefix('membershipfee/')->group(function () {
                Route::get('/', [MembershipfeeController::class, 'index'])->name('admin.Dashboard.membershipfee.index');
                Route::get('/create', [MembershipfeeController::class, 'create'])->name('admin.Dashboard.membershipfee.create');
                Route::get('/edit/{id}', function ($id) {
                    return view('admin.content.dashboard.membershipfee.edit', ['membershipFeeId' => $id]);
                })->name('admin.Dashboard.membershipfee.edit');

            });
            Route::prefix('usershipfee/')->group(function () {
                Route::get('/', [UserShipfeeController::class, 'index'])->name('admin.Dashboard.usershipfee.index');
                Route::get('/create', [UserShipfeeController::class, 'create'])->name('admin.Dashboard.usershipfee.create');
                Route::get('/edit/{id}', function ($id) {
                    return view('admin.content.dashboard.usershipfee.edit', ['membershipFeeId' => $id]);
                })->name('admin.Dashboard.usershipfee.edit');
            });
            Route::prefix('menu/')->group(function () {
                Route::get('/', [MenuController::class, 'index'])->name('admin.Dashboard.menu.index');
                Route::get('/create', [MenuController::class, 'create'])->name('admin.Dashboard.menu.create');
                Route::get('/edit/{id}', [MenuController::class, 'edit'])->name('admin.Dashboard.menu.edit');
            });
            Route::prefix('home_page/')->group(function () {
                Route::get('/', [HomePageController::class, 'index'])->name('admin.Dashboard.home_page.index');
                Route::get('/create', [HomePageController::class, 'create'])->name('admin.Dashboard.home_page.create');
                Route::get('/edit-best-doctor/{bestDoctorId}', [HomePageController::class, 'editBestDoctor'])->name('admin.Dashboard.home_page.edit');
            });
            Route::prefix('holiday/')->group(function () {
                Route::get('/', [HolidayController::class, 'index'])->name('admin.Dashboard.holiday.index');
            });
            Route::prefix('amar/')->group(function () {
                Route::get('/', [AmarController::class, 'index'])->name('admin.Dashboard.amar.index');
            });
            Route::prefix('payment_gateways/')->group(function () {
                Route::get('/', [PaymentGatewaysController::class, 'index'])->name('admin.Dashboard.payment_gateways.index');
                Route::get('/edit/{name}', [PaymentGatewaysController::class, 'edit'])->name('admin.Dashboard.payment_gateways.edit');
                Route::put('/payment-gateways/{name}', [PaymentGatewaysController::class, 'update'])->name('admin.payment_gateways.update');
                Route::post('/payment-gateways/toggle', [PaymentGatewaysController::class, 'toggle'])->name('admin.payment_gateways.toggle');
            });
            Route::prefix('setting/')->group(function () {
                Route::get('/', [SettingController::class, 'index'])->name('admin.Dashboard.setting.index');
                Route::get('/change-logo', [SettingController::class, 'change_logo'])->name('admin.Dashboard.setting.change-logo');
            });
        });


        Route::prefix('agent')->group(function () {
            Route::get('/', [AgentController::class, 'index'])->name('admin.agent.agent');
            Route::get('/agent-wallet', [AgentController::class, 'agentWallet'])->name('admin.agent.agent-wallet');

            Route::get('/create', [AgentController::class, 'create'])->name('admin.agent.create');
            Route::get('/edit/{agentId}', [AgentController::class, 'edit'])->name('admin.agent.edit');
        });
        Route::prefix('content-management/')->group(function () {
            Route::get('slide/', [SlideController::class, 'index'])->name('admin.content-management.slide.index');
            Route::get('slide/create', [SlideController::class, 'create'])->name('admin.content-management.slide.create');
            Route::get('slide/edit/{id}', [SlideController::class, 'edit'])->name('admin.content-management.slide.edit');
            Route::prefix('blog/')->group(function () {
                Route::get('/', [BlogController::class, 'index'])->name('admin.content-management.blog.index');
                Route::get('create', [BlogController::class, 'create'])->name('admin.content-management.blog.create');
                Route::get('edit/{id}', [BlogController::class, 'edit'])->name('admin.content-management.blog.edit');
            });
            Route::prefix('category-blog')->group(function () {
                Route::get('/', [CategoryBlogController::class, 'index'])->name('admin.content-management.category-blog.index');
                Route::get('/create', [CategoryBlogController::class, 'create'])->name('admin.content-management.category-blog.create');
                Route::get('/edit/{id}', [CategoryBlogController::class, 'edit'])->name('admin.content-management.category-blog.edit');
            });
            Route::prefix('tags/')->group(function () {
                Route::get('/', [TagsController::class, 'index'])->name('admin.content-management.tags.index');
                Route::get('create', [TagsController::class, 'create'])->name('admin.content-management.tags.create');
                Route::get('edit/{id}', [TagsController::class, 'edit'])->name('admin.content-management.tags.edit');
            });

            Route::prefix('comments/')->group(function () {
                Route::get('/', [CommentController::class, 'index'])->name('admin.content-management.comments.index');
                Route::get('show/{commentId}', [CommentController::class, 'show'])->name('admin.content-management.comments.show');
            });
            Route::prefix('home-video/')->group(function () {
                Route::get('/', [HomeVideoController::class, 'index'])->name('admin.content-management.home-video.index');
                Route::get('create', [HomeVideoController::class, 'create'])->name('admin.content-management.home-video.create');
                Route::get('edit/{id}', [HomeVideoController::class, 'edit'])->name('admin.content-management.home-video.edit');
            });
            Route::prefix('links/')->group(function () {
                Route::get('/', [LinksController::class, 'index'])->name('admin.content-management.links.index');
                Route::get('create', [LinksController::class, 'create'])->name('admin.content-management.links.create');
                Route::get('edit/{id}', [LinksController::class, 'edit'])->name('admin.content-management.links.edit');
            });
            Route::prefix('front-pages/')->group(function () {
                Route::get('/', [FrontPagesController::class, 'index'])->name('admin.content-management.front-pages.index');
                Route::get('create', [FrontPagesController::class, 'create'])->name('admin.content-management.front-pages.create');
                Route::get('edit/{id}', [FrontPagesController::class, 'edit'])->name('admin.content-management.front-pages.edit');
            });
        });
        Route::prefix('questions/')->group(function () {
            Route::get('question/', [QuestionController::class, 'index'])->name('admin.questions.question.index');
            Route::get('question/show/{id}', [QuestionController::class, 'show'])->name('admin.questions.question.show');
            Route::get('question-cat/', [QuestionCatController::class, 'index'])->name('admin.questions.question-cat.index');
            Route::get('question-cat/create', [QuestionCatController::class, 'create'])->name('admin.questions.question-cat.create');
        });
        Route::prefix('tools/')->group(function () {
            Route::get('file-manager/', [ToolsController::class, 'index'])->name('admin.tools.file-manager.index');
            Route::get('news-latter/', [NewsLatterController::class, 'index'])->name('admin.tools.news-latter.index');
            Route::get('redirects/', [RedirectController::class, 'index'])->name('admin.tools.redirects.index');
            Route::get('mail-template', [MailTemplateController::class, 'index'])->name('admin.tools.mail-template.index');
            Route::get('site-map', [SiteMapController::class, 'index'])->name('admin.tools.site-map.index');
        });
        Route::prefix('doctors/')->group(function () {
            Route::get('logs-doctor/', [LogsDoctorController::class, 'index'])->name('admin.doctors.logs-doctor.index');
            Route::get('order-visit/', [OrderVisitController::class, 'index'])->name('admin.doctors.order-visit.index');
            Route::get('order-visit/show/{id}', [OrderVisitController::class, 'show'])->name('admin.doctors.order-visit.show');
            Route::prefix('doctors-management/')->group(function () {
                Route::get('/status/{doctor}', [DoctorsManagementController::class, 'status'])->name('admin.doctor.status');
                Route::get('/', [DoctorsManagementController::class, 'index'])->name('admin.doctors.doctors-management.index');
                Route::get('/create', [DoctorsManagementController::class, 'create'])->name('admin.doctors.doctors-management.create');
                Route::get('/edit/{doctor}', [DoctorsManagementController::class, 'edit'])->name('admin.doctors.doctors-management.edit'); // اصلاح شده
                Route::post('/update/{doctor}', [DoctorsManagementController::class, 'update'])->name('admin.doctors.doctors-management.update'); // اضافه شده برای ذخیره تغییرات
                Route::prefix('bime/')->group(function () {
                    Route::get('/{doctor}', [BimeController::class, 'index'])->name('admin.doctors.doctors-management.bime.index');
                    Route::get('/create', [BimeController::class, 'create'])->name('admin.doctors.doctors-management.bime.create');
                    Route::get('/edit', [BimeController::class, 'edit'])->name('admin.doctors.doctors-management.bime.edit');
                });
                Route::prefix('nobatdehi-setting/')->group(function () {
                    Route::get('/', [NobatdehiSettingController::class, 'index'])->name('admin.content.doctors.doctors-management.nobatdehi-setting.index');
                });
                Route::prefix('moshavere-setting/')->group(function () {
                    Route::get('/', [MoshavereSettingController::class, 'index'])->name('admin.content.doctors.doctors-management.moshavere-setting.index');

                });
                Route::prefix('gallery/')->group(function () {
                    Route::get('/', [GalleryController::class, 'index'])->name('admin.content.doctors.doctors-management.gallery.index');
                });
            });
            Route::get('/doctor-login/{doctor}', function (\App\Models\Dr\Doctor $doctor) {
                // لاگین کردن دکتر با گارد doctor
                Auth::guard('doctor')->login($doctor);

                // ریدایرکت به پنل دکتر
                return redirect()->route('dr-panel');
            })->name('doctor.login');
            Route::prefix('wallet-doctor-request/')->group(function () {
                Route::get('/', [WalletDoctorRequestController::class, 'index'])->name('admin.content.doctors.wallet-doctor-request.index');
                Route::get('/show', [WalletDoctorRequestController::class, 'show'])->name('admin.content.doctors.wallet-doctor-request.show');
            });
            Route::prefix('comment-doctor/')->group(function () {
                Route::get('/', [CommentDoctorController::class, 'index'])->name('admin.content.doctors.comment-doctor.index');
                Route::get('show/{id}', [CommentDoctorController::class, 'show'])->name('admin.content.doctors.comment-doctor.show');
            });
            Route::prefix('moshavere/')->group(function () {
                Route::get('/', [MoshavereController::class, 'index'])->name('admin.content.doctors.moshavere.index');
                Route::get('/edit', [MoshavereController::class, 'edit'])->name('admin.content.doctors.moshavere.edit');
            });
        });
        Route::prefix('hospitals/')->group(function () {
            Route::prefix('hospitals-management/')->group(function () {
                Route::get('/', [HospitalsManagementController::class, 'index'])->name('admin.content.hospitals.hospitals-management.index');
                Route::get('/create', [HospitalsManagementController::class, 'create'])->name('admin.content.hospitals.hospitals-management.create');
                Route::get('/edit/{id}', [HospitalsManagementController::class, 'edit'])->name('admin.content.hospitals.hospitals-management.edit');
                Route::post('/store', [HospitalsManagementController::class, 'store'])->name('admin.content.hospitals.hospitals-management.store');
                Route::put('/update/{id}', [HospitalsManagementController::class, 'update'])->name('admin.content.hospitals.hospitals-management.update');
                Route::delete('/destroy/{id}', [HospitalsManagementController::class, 'destroy'])->name('admin.content.hospitals.hospitals-management.destroy');
                Route::get('/get-cities/{province_id}', function ($province_id) {
                    return Zone::where('parent_id', $province_id)
                        ->where('level', 2)
                        ->get(['id', 'name']);
                })->name('admin.content.hospitals.hospitals-management.get-cities');

                Route::prefix('doctors-of-hospital/')->group(function () {
                    Route::get('/', [DoctorsOfHospitalController::class, 'index'])->name('admin.content.hospitals.hospitals-management.doctors-of-hospital.index');
                    Route::get('/create', [DoctorsOfHospitalController::class, 'create'])->name('admin.content.hospitals.hospitals-management.doctors-of-hospital.create');
                    Route::get('/edit', [DoctorsOfHospitalController::class, 'edit'])->name('admin.content.hospitals.hospitals-management.doctors-of-hospital.edit');
                });
            });
            Route::prefix('logs-hospital/')->group(function () {
                Route::get('/', [LogsHospitalController::class, 'index'])->name('admin.content.hospitals.logs-hospital.index');
            });
            Route::prefix('wallet-hospital-request/')->group(function () {
                Route::get('/', [WalletHospitalRequestController::class, 'index'])->name('admin.content.hospitals.wallet-hospital-request.index');
            });
        });
        Route::prefix('application/')->group(function () {
            Route::get('/', [ApplicationController::class, 'index'])->name('admin.content.application.index');
            Route::get('/create', [ApplicationController::class, 'create'])->name('admin.content.application.create');
            Route::get('/edit', [ApplicationController::class, 'edit'])->name('admin.content.application.edit');
        });
        Route::prefix('users-management/')->group(function () {
            Route::prefix('auth/')->group(function () {
                Route::get('/', [AuthController::class, 'index'])->name('admin.content.users-management.auth.index');
                Route::get('/show', [AuthController::class, 'show'])->name('admin.content.users-management.auth.show');
            });
            Route::prefix('charge-account/')->group(function () {
                Route::get('/', [ChargeAccountController::class, 'index'])->name('admin.content.users-management.charge-account.index');
            });
            Route::prefix('wallet-checkout-user/')->group(function () {
                Route::get('/', [WalletCheckoutUserController::class, 'index'])->name('admin.content.users-management.wallet-checkout-user.index');
                Route::get('/show', [WalletCheckoutUserController::class, 'show'])->name('admin.content.users-management.wallet-checkout-user.show');
                Route::get('/edit', [WalletCheckoutUserController::class, 'edit'])->name('admin.content.users-management.wallet-checkout-user.edit');
            });
            Route::prefix('logs-upgrade/')->group(function () {
                Route::get('/', [LogsUpgradeController::class, 'index'])->name('admin.content.users-management.logs-upgrade.index');
            });
            Route::prefix('membershipfee-logs/')->group(function () {
                Route::get('/', [MembershipfeeLogsController::class, 'index'])->name('admin.content.users-management.membershipfee-logs.index');
            });
            Route::prefix('wallet-checkout-monshi/')->group(function () {
                Route::get('/', [WalletCheckoutMonshiController::class, 'index'])->name('admin.content.users-management.wallet-checkout-monshi.index');
            });
            Route::prefix('users/')->group(function () {
                Route::get('/', [UsersController::class, 'index'])->name('admin.content.users-management.users.index');
                Route::get('/show', [UsersController::class, 'show'])->name('admin.content.users-management.users.show');
                Route::get('/edit', [UsersController::class, 'edit'])->name('admin.content.users-management.users.edit');
                Route::get('/create', [UsersController::class, 'create'])->name('admin.content.users-management.users.create');
                Route::get('/profile', [UsersController::class, 'profile'])->name('admin.content.users-management.users.profile.index');
            });
            Route::prefix('user-group/')->group(function () {
                Route::get('/', [UserGroupController::class, 'index'])->name('admin.content.users-management.user-group.index');
                Route::get('/edit', [UserGroupController::class, 'edit'])->name('admin.content.users-management.user-group.edit');
                Route::get('/create', [UserGroupController::class, 'create'])->name('admin.content.users-management.user-group.create');
            });
        });
        Route::prefix('ads/')->group(function () {
            Route::prefix('banner/')->group(function () {
                Route::get('/', [BannerController::class, 'index'])->name('admin.content.ads.banner.index');
                Route::get('/create', [BannerController::class, 'create'])->name('admin.content.ads.banner.create');
                Route::get('/edit', [BannerController::class, 'edit'])->name('admin.content.ads.banner.edit');
                Route::prefix('packages/')->group(function () {
                    Route::get('/', [PackagesController::class, 'index'])->name('admin.content.ads.banner.packages.index');
                    Route::get('/create', [PackagesController::class, 'create'])->name('admin.content.ads.banner.packages.create');
                    Route::get('/edit', [PackagesController::class, 'edit'])->name('admin.content.ads.banner.packages.edit');
                });
                Route::prefix('ads-log/')->group(function () {
                    Route::get('/', [AdsLogController::class, 'index'])->name('admin.content.ads.banner.ads-log.index');
                });
            });
        });
        // locale
        Route::get('lang/{locale}', [LanguageController::class, 'swap']);
        // layout
        Route::get('/layouts/collapsed-menu', [CollapsedMenu::class, 'index'])->name('layouts-collapsed-menu');
        Route::get('/layouts/content-navbar', [ContentNavbar::class, 'index'])->name('layouts-content-navbar');
        Route::get('/layouts/content-nav-sidebar', [ContentNavSidebar::class, 'index'])->name('layouts-content-nav-sidebar');
        // Route::get('/layouts/navbar-full', [NavbarFull::class, 'index'])->name('layouts-navbar-full');
        // Route::get('/layouts/navbar-full-sidebar', [NavbarFullSidebar::class, 'index'])->name('layouts-navbar-full-sidebar');
        Route::get('/layouts/horizontal', [Horizontal::class, 'index'])->name('Dashboard-analytics');
        Route::get('/layouts/without-menu', [WithoutMenu::class, 'index'])->name('layouts-without-menu');
        Route::get('/layouts/without-navbar', [WithoutNavbar::class, 'index'])->name('layouts-without-navbar');
        Route::get('/layouts/fluid', [Fluid::class, 'index'])->name('layouts-fluid');
        Route::get('/layouts/container', [Container::class, 'index'])->name('layouts-container');
        Route::get('/layouts/blank', [Blank::class, 'index'])->name('layouts-blank');
    });
// end manager  routes
// dr routes
Route::prefix('dr')->namespace('Dr')->group(function () {
    /* login routes */
    Route::get('login', [LoginController::class, 'loginRegisterForm'])->name('dr.auth.login-register-form');

    Route::get('login-user-pass', [LoginController::class, 'loginUserPassForm'])->name('dr.auth.login-user-pass-form');

    Route::get('dr-two-factor', [LoginController::class, 'twoFactorForm'])->name('dr-two-factor');

    Route::post('dr-two-factor-store', [LoginController::class, 'twoFactorFormCheck'])->name('dr-two-factor-store');

    Route::post('dr-login-with-mobile-pass', [LoginController::class, 'loginWithMobilePass'])->name('dr-login-with-mobile-pass');

    Route::post('/login-register', [LoginController::class, 'loginRegister'])->name('dr.auth.login-register');

    Route::get('login-confirm/{token}', [LoginController::class, 'loginConfirmForm'])->name('dr.auth.login-confirm-form');

    Route::post('/login-confirm/{token}', [LoginController::class, 'loginConfirm'])->name('dr.auth.login-confirm');

    Route::get('/login-resend-otp/{token}', [LoginController::class, 'loginResendOtp'])->name('dr.auth.login-resend-otp');

    Route::get('/logout', [LoginController::class, 'logout'])->name('dr.auth.logout');
    /* login routes */

    Route::prefix('panel')->middleware(['doctor', 'secretary', 'complete-profile'])->group(function () {
        Route::get('/', [DrPanelController::class, 'index'])->middleware('secretary.permission:dashboard')->name('dr-panel');
        Route::get('/doctor/appointments/by-date', [DrPanelController::class, 'getAppointmentsByDate'])
            ->name('doctor.appointments.by-date');
        Route::get('/search/patients', [DrPanelController::class, 'searchPatients'])->name('search.patients');
        Route::post('/appointments/update-date/{id}', [DrPanelController::class, 'updateAppointmentDate'])
            ->name('updateAppointmentDate');

        Route::get('/doctor/appointments/filter', [DrPanelController::class, 'filterAppointments'])->name('doctor.appointments.filter');

        Route::prefix('turn')->middleware('secretary.permission:appointments')->group(function () {
            Route::prefix('schedule')->group(function () {
                Route::get('/appointments', [DrScheduleController::class, 'index'])->middleware('secretary.permission:appointments')->name('dr-appointments');
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


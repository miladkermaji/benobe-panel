<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Mc\Panel\McPanelController;
use App\Http\Controllers\Mc\Panel\Bime\DRBimeController;
use App\Livewire\Mc\Panel\Payment\WalletChargeComponent;
use App\Http\Controllers\Mc\Panel\Profile\SubUserController;
use App\Http\Controllers\Mc\Panel\Tickets\TicketsController;
use App\Http\Controllers\Mc\Panel\Profile\DrProfileController;
use App\Http\Controllers\Mc\Panel\Profile\LoginLogsController;
use App\Http\Controllers\Mc\Panel\Tickets\TicketResponseController;
use App\Http\Controllers\Mc\Panel\Profile\DrUpgradeProfileController;
use App\Http\Controllers\Mc\Panel\Turn\Schedule\AppointmentController;
use App\Http\Controllers\Mc\Panel\MyPerformance\MyPerformanceController;
use App\Http\Controllers\Mc\Panel\PatientRecords\PatientRecordsController;
use App\Http\Controllers\Mc\Panel\Secretary\SecretaryManagementController;
use App\Http\Controllers\Mc\Panel\Comunication\DoctorSendMessageController;
use App\Http\Controllers\Mc\Panel\Payment\Setting\DrPaymentSettingController;
use App\Http\Controllers\Mc\Panel\DoctorsClinic\Activation\Cost\CostController;
use App\Http\Controllers\Mc\Panel\Turn\TurnsCatByDays\TurnsCatByDaysController;
use App\Http\Controllers\Mc\Panel\NoskheElectronic\Providers\ProvidersController;
use App\Http\Controllers\Mc\Panel\Activation\Consult\Rules\ConsultRulesController;
use App\Http\Controllers\Mc\Panel\DoctorsClinic\DoctorsClinicManagementController;
use App\Http\Controllers\Mc\Panel\Turn\Schedule\ManualNobat\ManualNobatController;
use App\Http\Controllers\Mc\Panel\Turn\Schedule\ScheduleSetting\VacationController;
use App\Http\Controllers\Mc\Panel\SecretaryPermission\SecretaryPermissionController;
use App\Http\Controllers\Mc\Panel\NoskheElectronic\Favorite\Service\ServiceController;
use App\Http\Controllers\mc\Panel\Turn\DrScheduleController as McDrScheduleController;
use App\Http\Controllers\Mc\Panel\DoctorsClinic\Activation\Duration\DurationController;
use App\Http\Controllers\Mc\Panel\NoskheElectronic\Prescription\PrescriptionController;
use App\Http\Controllers\Mc\Panel\Turn\Schedule\ScheduleSetting\ScheduleSettingController;
use App\Http\Controllers\Mc\Panel\Turn\Schedule\MoshavereWaiting\MoshavereWaitingController;
use App\Http\Controllers\Mc\Panel\DoctorsClinic\Activation\ActivationDoctorsClinicController;
use App\Http\Controllers\Mc\Panel\Turn\Schedule\Counseling\ConsultTerm\ConsultTermController;
use App\Http\Controllers\Mc\Panel\NoskheElectronic\Favorite\Templates\FavoriteTemplatesController;
use App\Http\Controllers\Mc\Panel\DoctorsClinic\Activation\Workhours\ActivationWorkhoursController;
use App\Http\Controllers\Mc\Panel\Turn\Schedule\MoshavereSetting\MySpecialDaysCounselingController;
use App\Http\Controllers\Mc\Panel\Turn\Schedule\ScheduleSetting\BlockingUsers\BlockingUsersController;
use App\Http\Controllers\mc\Panel\Turn\Schedule\ScheduleSetting\ScheduleSettingController as McScheduleSettingController;
use App\Http\Controllers\Mc\Panel\Turn\Schedule\MoshavereSetting\MoshavereSettingController as DrMoshavereSettingController;

// dr routes
Route::prefix('mc')
    ->namespace('Mc')
    ->group(function () {
        Route::prefix('doctor-comments')->group(function () {
            Route::get('/', [\App\Http\Controllers\Mc\Panel\DoctorComments\DoctorCommentController::class, 'index'])->name('mc.panel.doctor-comments.index');
        });
        Route::prefix('panel')->middleware(['medical_center'])->group(function () {
            Route::get('mc/panel', [McPanelController::class, 'index'])->name('mc-panel');
            Route::get('/', [McPanelController::class, 'index'])->middleware('secretary.permission:dashboard')->name('mc-panel');
            Route::prefix('doctor-services')->group(function () {
                Route::get('/', [\App\Http\Controllers\Mc\Panel\DoctorService\DoctorServiceController::class, 'index'])->name('mc.panel.doctor-services.index');
                Route::get('/create', [\App\Http\Controllers\Mc\Panel\DoctorService\DoctorServiceController::class, 'create'])->name('mc.panel.doctor-services.create');
                Route::get('/edit/{id}', [\App\Http\Controllers\Mc\Panel\DoctorService\DoctorServiceController::class, 'edit'])->name('mc.panel.doctor-services.edit');
            });
            Route::prefix('patient-contact/send-message')->group(function () {
                Route::get('/', [DoctorSendMessageController::class, 'index'])->name('mc.panel.send-message');
            });
            Route::post('appointments/{id}/end-visit-counseling', [MoshavereWaitingController::class, 'endVisit'])->name('doctor.end-visit-counseling');
            Route::get('/search-appointments-counseling', [MoshavereWaitingController::class, 'searchAppointments'])->middleware('secretary.permission:appointments')->name('search.appointments.counseling');
            Route::post('appointments/{id}/end-visit', [McDrScheduleController::class, 'endVisit'])->name('doctor.end-visit');
            Route::get('/doctor/appointments/by-date', [McDrScheduleController::class, 'getAppointmentsByDate'])
                ->name('doctor.appointments.by-date');
            Route::get('/search/patients', [McDrScheduleController::class, 'searchPatients'])->name('search.patients');
            Route::get('/search/patients-counseling', [MoshavereWaitingController::class, 'searchPatients'])->name('search.patients-counseling');
            Route::post('/appointments/update-date/{id}', [McDrScheduleController::class, 'updateAppointmentDate'])
                ->name('updateAppointmentDate');
            Route::prefix('doctor-notes')->group(function () {
                Route::get('/', [\App\Http\Controllers\Mc\Panel\DoctorNote\DoctorNoteController::class, 'index'])->name('mc.panel.doctornotes.index');
                Route::get('/create', [\App\Http\Controllers\Mc\Panel\DoctorNote\DoctorNoteController::class, 'create'])->name('mc.panel.doctornotes.create');
                Route::get('/edit/{id}', [\App\Http\Controllers\Mc\Panel\DoctorNote\DoctorNoteController::class, 'edit'])->name('mc.panel.doctornotes.edit');
            });
            Route::get('/doctor/appointments/filter', [McDrScheduleController::class, 'filterAppointments'])->name('doctor.appointments.filter');
            Route::get('/doctor/appointments/filter-counseling', [MoshavereWaitingController::class, 'filterAppointments'])->name('doctor.appointments.filter.counseling');
            Route::prefix('turn')->middleware('secretary.permission:appointments')->group(function () {
                Route::prefix('schedule')->group(function () {
                    Route::get('/appointments', [McDrScheduleController::class, 'index'])->middleware('secretary.permission:appointments')->name('mc-appointments');
                    Route::get('search-appointments', [McDrScheduleController::class, 'searchAppointments'])->name('mc.search.appointments');
                    Route::post('end-visit/{id}', [McDrScheduleController::class, 'endVisit'])->name('end.visit');
                    Route::get('/my-appointments', [McDrScheduleController::class, 'myAppointments'])->middleware('secretary.permission:my-appointments')->name('my-mc-appointments');
                    Route::get('/my-appointments/by-date', [McDrScheduleController::class, 'showByDateAppointments'])->name('mc.turn.my-appointments.by-date');
                    Route::get('filter-appointments', [McDrScheduleController::class, 'filterAppointments'])->name('mc.turn.filter-appointments');
                    Route::get('/moshavere_setting', [DrMoshavereSettingController::class, 'index'])->middleware('secretary.permission:appointments')->name('mc-moshavere_setting');
                    Route::post('/copy-work-hours-counseling', [DrMoshavereSettingController::class, 'copyWorkHours'])->middleware('secretary.permission:appointments')->name('copy-work-hours-counseling');
                    Route::get('get-work-schedule-counseling', [DrMoshavereSettingController::class, 'getWorkSchedule'])->middleware('secretary.permission:appointments')->name('mc-get-work-schedule-counseling');
                    Route::post('/copy-single-slot-counseling', [DrMoshavereSettingController::class, 'copySingleSlot'])->middleware('secretary.permission:appointments')->name('copy-single-slot-counseling');
                    Route::post('/save-time-slot-counseling', [DrMoshavereSettingController::class, 'saveTimeSlot'])->middleware('secretary.permission:appointments')->name('save-time-slot-counseling');
                    Route::get('/get-appointment-settings-counseling', [DrMoshavereSettingController::class, 'getAppointmentSettings'])->middleware('secretary.permission:appointments')->name('get-appointment-settings-counseling');
                    Route::delete('/appointment-slots-conseling/{id}', [DrMoshavereSettingController::class, 'destroy'])->middleware('secretary.permission:appointments')->name('appointment.slots.destroy-counseling');
                    Route::post('save-work-schedule-counseling', [DrMoshavereSettingController::class, 'saveWorkSchedule'])->middleware('secretary.permission:appointments')->name('mc-save-work-schedule-counseling');
                    Route::post('/dr/update-work-day-status-counseling', [DrMoshavereSettingController::class, 'updateWorkDayStatus'])->middleware('secretary.permission:appointments')->name('update-work-day-status-counseling');
                    Route::post('/update-auto-scheduling-counseling', [DrMoshavereSettingController::class, 'updateAutoScheduling'])->middleware('secretary.permission:appointments')->name('update-auto-scheduling-counseling');
                    Route::get('/get-all-days-settings-counseling', [DrMoshavereSettingController::class, 'getAllDaysSettings'])->middleware('secretary.permission:appointments')->name('get-all-days-settings-counseling');
                    Route::post('/save-appointment-settings-counseling', [DrMoshavereSettingController::class, 'saveAppointmentSettings'])->middleware('secretary.permission:appointments')->name('save-appointment-settings-counseling');
                    Route::post('/delete-schedule-setting-counseling', [DrMoshavereSettingController::class, 'deleteScheduleSetting'])->middleware('secretary.permission:appointments')->name('delete-schedule-setting-counseling');
                    Route::get('/moshavere_waiting', [MoshavereWaitingController::class, 'index'])->middleware('secretary.permission:appointments')->name('mc-moshavere_waiting');
                    Route::get('/doctor/appointments/by-date-counseling', [MoshavereWaitingController::class, 'getAppointmentsByDate'])
                                                ->name('doctor.appointments.by-date-counseling');
                    Route::get('/manual_nobat', [ManualNobatController::class, 'index'])->middleware('secretary.permission:appointments')->name('mc-manual_nobat');
                    Route::post('manual_nobat/store', [ManualNobatController::class, 'store'])->middleware('secretary.permission:appointments')->name('manual-nobat.store');
                    Route::post('manual-nobat/store-with-user', [ManualNobatController::class, 'storeWithUser'])->middleware('secretary.permission:appointments')->name('manual-nobat.store-with-user');
                    Route::delete('/manual_appointments/{id}', [ManualNobatController::class, 'destroy'])->middleware('secretary.permission:appointments')->name('manual_appointments.destroy');
                    Route::get('/manual_appointments/{id}/edit', [ManualNobatController::class, 'edit'])->middleware('secretary.permission:appointments')->name('manual-appointments.edit');
                    Route::post('/manual_appointments/{id}', [ManualNobatController::class, 'update'])->middleware('secretary.permission:appointments')->name('manual-appointments.update');
                    Route::post('/manual-nobat/settings/save', [ManualNobatController::class, 'saveSettings'])->middleware('secretary.permission:appointments')->name('manual-nobat.settings.save');
                    Route::get('/manual_nobat_setting', [ManualNobatController::class, 'showSettings'])->middleware('secretary.permission:appointments')->name('mc-manual_nobat_setting');
                    Route::get('/search-users', [ManualNobatController::class, 'searchUsers'])->middleware('secretary.permission:appointments')->name('mc-panel-search.users');
                    Route::get('/scheduleSetting', [ScheduleSettingController::class, 'index'])->middleware('secretary.permission:appointments')->name('mc-scheduleSetting');
                    Route::get('/insurances', [ManualNobatController::class, 'getInsurances'])->name('manual-nobat.insurances');
                    Route::get('/services/{insuranceId}', [ManualNobatController::class, 'getServices'])->name('manual-nobat.services');
                    Route::post('/calculate-final-price', [ManualNobatController::class, 'calculateFinalPrice'])->name('manual-nobat.calculate-final-price');
                    Route::post('/{id}/end-visit', [ManualNobatController::class, 'endVisit'])->name('manual-nobat.end-visit');
                    Route::prefix('scheduleSetting/vacation')->group(function () {
                        Route::get('/', [VacationController::class, 'index'])->middleware('secretary.permission:appointments')->name('mc-vacation');
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
                        Route::get('/message-lists', [BlockingUsersController::class, 'getMessages'])
                            ->middleware('secretary.permission:appointments')
                            ->name('doctor-blocking-users.messages');
                        Route::delete('/doctor-blocking-users/{id}', [BlockingUsersController::class, 'destroy'])
                            ->middleware('secretary.permission:appointments')
                            ->name('doctor-blocking-users.destroy');
                        Route::patch('/update-status', [BlockingUsersController::class, 'updateStatus'])
                            ->middleware('secretary.permission:appointments')
                            ->name('doctor-blocking-users.update-status');
                        Route::post('/messages/delete', [BlockingUsersController::class, 'deleteMessage'])
                        ->middleware('secretary.permission:appointments')
                        ->name('doctor-blocking-users.delete-message');
                    });
                    Route::get('/scheduleSetting/workhours', [McScheduleSettingController::class, 'workhours'])->middleware('secretary.permission:appointments')->name('mc-workhours');
                    Route::post('/save-appointment-settings', [ScheduleSettingController::class, 'saveAppointmentSettings'])->middleware('secretary.permission:appointments')->name('save-appointment-settings');
                    Route::get('/get-appointment-settings', [ScheduleSettingController::class, 'getAppointmentSettings'])->middleware('secretary.permission:appointments')->name('get-appointment-settings');
                    Route::post('/delete-schedule-setting', [ScheduleSettingController::class, 'deleteScheduleSetting'])->middleware('secretary.permission:appointments')->name('delete-schedule-setting');
                    Route::get('/get-all-days-settings', [ScheduleSettingController::class, 'getAllDaysSettings'])->middleware('secretary.permission:appointments')->name('get-all-days-settings');
                    // ذخیره‌سازی تنظیمات ساعات کاری
                    Route::post('save-work-schedule', [ScheduleSettingController::class, 'saveWorkSchedule'])->middleware('secretary.permission:appointments')->name('mc-save-work-schedule');
                    Route::post('save-schedule', [ScheduleSettingController::class, 'saveSchedule'])->middleware('secretary.permission:appointments')->name('save-schedule');
                    Route::delete('/appointment-slots/{id}', [ScheduleSettingController::class, 'destroy'])->middleware('secretary.permission:appointments')->name('appointment.slots.destroy');
                    // بازیابی تنظیمات ساعات کاری
                    Route::get('get-work-schedule', [ScheduleSettingController::class, 'getWorkSchedule'])->middleware('secretary.permission:appointments')->name('mc-get-work-schedule');
                    Route::post('/dr/update-work-day-status', [ScheduleSettingController::class, 'updateWorkDayStatus'])->middleware('secretary.permission:appointments')->name('update-work-day-status');
                    Route::post('/check-day-slots', [ScheduleSettingController::class, 'checkDaySlots'])->middleware('secretary.permission:appointments')->name('check-day-slots');
                    Route::post('/update-auto-scheduling', [ScheduleSettingController::class, 'updateAutoScheduling'])->middleware('secretary.permission:appointments')->name('update-auto-scheduling');
                    // routes/web.php
                    Route::post('/copy-work-hours', [ScheduleSettingController::class, 'copyWorkHours'])->middleware('secretary.permission:appointments')->name('copy-work-hours');
                    Route::post('/copy-single-slot', [ScheduleSettingController::class, 'copySingleSlot'])->middleware('secretary.permission:appointments')->name('copy-single-slot');
                    Route::post('/save-time-slot', [ScheduleSettingController::class, 'saveTimeSlot'])->middleware('secretary.permission:appointments')->name('save-time-slot');
                    Route::get('/scheduleSetting/my-special-days', [ScheduleSettingController::class, 'mySpecialDays'])->middleware('secretary.permission:appointments')->name('mc-mySpecialDays');
                    Route::get('/scheduleSetting/counseling/my-special-days', [MySpecialDaysCounselingController::class, 'mySpecialDays'])->middleware('secretary.permission:appointments')->name('mc-mySpecialDays-counseling');
                    Route::get('/appointments-by-date', [ScheduleSettingController::class, 'getAppointmentsByDateSpecial'])->name('doctor.get_appointments_by_date');
                    Route::get('/appointments-by-date-counseling', [MoshavereWaitingController::class, 'getAppointmentsByDateSpecial'])->name('doctor.get_appointments_by_date_counseling');
                    Route::get('/doctor/default-schedule', [ScheduleSettingController::class, 'getDefaultSchedule'])->name('doctor.get_default_schedule');
                    Route::get('/doctor/default-schedule-counseling', [MoshavereWaitingController::class, 'getDefaultSchedule'])->name('doctor.get_default_schedule_counseling');
                    Route::get('/doctor/default-schedule-counseling', [MySpecialDaysCounselingController::class, 'getDefaultSchedule'])->name('doctor.get_default_schedule_counseling');
                    Route::post('/doctor/update-work-schedule', [ScheduleSettingController::class, 'updateWorkSchedule'])->name('doctor.update_work_schedule');
                    Route::post('/doctor/update-work-schedule-counseling', [MoshavereWaitingController::class, 'updateWorkSchedule'])->name('doctor.update_work_schedule_counseling');
                    Route::post('/doctor/update-work-schedule-counseling', [MySpecialDaysCounselingController::class, 'updateWorkSchedule'])->name('doctor.update_work_schedule_counseling');
                    Route::get('/appointments-count', [McScheduleSettingController::class, 'getAppointmentsCountPerDay'])->middleware('secretary.permission:appointments')->name('appointments.count');
                    Route::get('/work-days-and-config', [ScheduleSettingController::class, 'getWorkDaysAndConfig'])->name('work.days.config');
                    Route::get('/appointments-count-counseling', [MySpecialDaysCounselingController::class, 'getAppointmentsCountPerDay'])->middleware('secretary.permission:appointments')->name('appointments.count.counseling');
                    Route::get('/appointments/by-date', [ScheduleSettingController::class, 'getAppointmentsByDate'])->middleware('secretary.permission:appointments')->name('appointments.by_date');
                    Route::post('/doctor/add-holiday', [ScheduleSettingController::class, 'addHoliday'])->middleware('secretary.permission:appointments')->name('doctor.add_holiday');
                    Route::get('/doctor/get-holidays', [ScheduleSettingController::class, 'getHolidayDates'])->middleware('secretary.permission:appointments')->name('doctor.get_holidays');
                    Route::get('/doctor/get-holidays-counseling', [MySpecialDaysCounselingController::class, 'getHolidayDates'])->middleware('secretary.permission:appointments')->name('doctor.get_holidays_counseling');
                    Route::post('/doctor/toggle-holiday', [ScheduleSettingController::class, 'toggleHolidayStatus'])->middleware('secretary.permission:appointments')->name('doctor.toggle_holiday');
                    Route::post('/doctor/toggle-holiday-counseling', [MySpecialDaysCounselingController::class, 'toggleHolidayStatus'])->middleware('secretary.permission:appointments')->name('doctor.toggle_holiday_counseling');
                    Route::post('/doctor/holiday-status', [ScheduleSettingController::class, 'getHolidayStatus'])->middleware('secretary.permission:appointments')->name('doctor.get_holiday_status');
                    Route::post('/doctor/holiday-status-counseling', [MoshavereWaitingController::class, 'getHolidayStatus'])->middleware('secretary.permission:appointments')->name('doctor.get_holiday_status_counseling');
                    Route::post('/doctor/holiday-status-counseling', [MySpecialDaysCounselingController::class, 'getHolidayStatus'])->middleware('secretary.permission:appointments')->name('doctor.get_holiday_status_counseling');
                    Route::post('/doctor/cancel-appointments', [ScheduleSettingController::class, 'cancelAppointments'])->middleware('secretary.permission:appointments')->name('doctor.cancel_appointments');
                    Route::post('/doctor/cancel-appointments-counseling', [MoshavereWaitingController::class, 'cancelAppointments'])->middleware('secretary.permission:appointments')->name('doctor.cancel_appointments_counseling');
                    Route::post('/doctor/cancel-appointments-counseling', [MySpecialDaysCounselingController::class, 'cancelAppointments'])->middleware('secretary.permission:appointments')->name('doctor.cancel_appointments_counseling');
                    Route::post('/doctor/reschedule-appointment', [McScheduleSettingController::class, 'rescheduleAppointment'])->middleware('secretary.permission:appointments')->name('doctor.reschedule_appointment');
                    Route::post('/doctor/reschedule-appointment-counseling', [MoshavereWaitingController::class, 'rescheduleAppointment'])->middleware('secretary.permission:appointments')->name('doctor.reschedule_appointment_counseling');
                    Route::post('/doctor/reschedule-appointment-counseling', [MySpecialDaysCounselingController::class, 'rescheduleAppointment'])->middleware('secretary.permission:appointments')->name('doctor.reschedule_appointment_counseling');
                    Route::get('/turnContract', [ScheduleSettingController::class, 'turnContract'])->middleware('secretary.permission:appointments')->name('mc-scheduleSetting-turnContract');
                    Route::post('/update-first-available-appointment', [ScheduleSettingController::class, 'updateFirstAvailableAppointment'])->middleware('secretary.permission:appointments')->name('doctor.update_first_available_appointment');
                    Route::post('/update-first-available-appointment-counseling', [MoshavereWaitingController::class, 'updateFirstAvailableAppointment'])->middleware('secretary.permission:appointments')->name('doctor.update_first_available_appointment_counseling');
                    Route::post('/update-first-available-appointment-counseling', [MySpecialDaysCounselingController::class, 'updateFirstAvailableAppointment'])->middleware('secretary.permission:appointments')->name('doctor.update_first_available_appointment_counseling');
                    Route::get('get-next-available-date', [ScheduleSettingController::class, 'getNextAvailableDate'])->middleware('secretary.permission:appointments')->name('doctor.get_next_available_date');
                    Route::get('get-next-available-date-counseling', [MoshavereWaitingController::class, 'getNextAvailableDate'])->middleware('secretary.permission:appointments')->name('doctor.get_next_available_date_counseling');
                    Route::get('get-next-available-date-counseling', [MySpecialDaysCounselingController::class, 'getNextAvailableDate'])->middleware('secretary.permission:appointments')->name('doctor.get_next_available_date_counseling');
                    Route::delete('/appointments/destroy/{id}', [AppointmentController::class, 'destroyAppointment'])->middleware('secretary.permission:appointments')->name('appointments.destroy');
                    Route::post('/toggle-auto-pattern/{id}', [AppointmentController::class, 'toggleAutoPattern'])->middleware('secretary.permission:appointments')->name('toggle-auto-pattern');
                });
                Route::prefix('Counseling')->group(function () {
                    Route::get('/consult-term', [ConsultTermController::class, 'index'])->middleware('secretary.permission:appointments')->name('consult-term.index');
                });
                Route::post('/update-auto-schedule', [McDrScheduleController::class, 'updateAutoSchedule'])->middleware('secretary.permission:appointments')->name('update-auto-schedule');
                Route::get('/check-auto-schedule', [McDrScheduleController::class, 'checkAutoSchedule'])->middleware('secretary.permission:appointments')->name('check-auto-schedule');
                Route::get('get-available-times', [McDrScheduleController::class, 'getAvailableTimes'])->middleware('secretary.permission:appointments')->name('getAvailableTimes');
                Route::post('update-day-status', [McDrScheduleController::class, 'updateDayStatus'])->middleware('secretary.permission:appointments')->name('updateDayStatus');
                Route::get('disabled-days', [McDrScheduleController::class, 'disabledDays'])->middleware('secretary.permission:appointments')->name('disabledDays');
                Route::post('/convert-to-gregorian', [AppointmentController::class, 'convertToGregorian'])->middleware('secretary.permission:appointments')->name('convert-to-gregorian');
                Route::get('/search-appointments', [AppointmentController::class, 'searchAppointments'])->middleware('secretary.permission:appointments')->name('search.appointments');
                Route::get('/turnsCatByDays', [TurnsCatByDaysController::class, 'index'])->middleware('secretary.permission:appointments')->name('mc-turnsCatByDays');
                Route::post('/appointments/{id}/status', [AppointmentController::class, 'updateStatus'])->middleware('secretary.permission:appointments')->name('updateStatusAppointment');
                Route::get('/get-appointments-count/{doctorId}/{date}', [McDrScheduleController::class, 'getAppointmentsCount'])->middleware('secretary.permission:appointments')->name('get-appointments-count');
            });
            Route::get('/patient-records', [PatientRecordsController::class, 'index'])->middleware('secretary.permission:patient_records')->name('mc-patient-records');
            Route::prefix('tickets')->group(function () {
                Route::get('/', [TicketsController::class, 'index'])->name('mc-panel-tickets');
                Route::post('/store', [TicketsController::class, 'store'])->name('mc-panel-tickets.store');
                Route::delete('/destroy/{id}', [TicketsController::class, 'destroy'])->name('mc-panel-tickets.destroy');
                Route::get('/show/{id}', [TicketsController::class, 'show'])->name('mc-panel-tickets.show');
                // مسیرهای مربوط به پاسخ تیکت‌ها
                Route::post('/{id}/responses', [TicketResponseController::class, 'store'])->name('mc-panel-tickets.responses.store');
            });
            Route::get('activation/consult/rules', [ConsultRulesController::class, 'index'])->middleware('secretary.permission:consult')->name('activation.consult.rules');
            Route::get('activation/consult/help', [ConsultRulesController::class, 'help'])->middleware('secretary.permission:consult')->name('activation.consult.help');
            Route::get('activation/consult/messengers', [ConsultRulesController::class, 'messengers'])->middleware('secretary.permission:consult')->name('activation.consult.messengers');
            Route::get('my-performance/', [MyPerformanceController::class, 'index'])->middleware('secretary.permission:statistics')->name('mc-my-performance');
            Route::get('/dr/my-performance/data', [MyPerformanceController::class, 'getPerformanceData'])->name('mc-my-performance-data');
            Route::get('my-performance/doctor-chart', [MyPerformanceController::class, 'chart'])->middleware('secretary.permission:statistics')->name('mc-my-performance-chart');
            Route::get('my-performance/chart-data', [MyPerformanceController::class, 'getChartData'])
                ->name('mc-my-performance-chart-data');
            Route::group(['prefix' => 'secretary'], function () {
                Route::get('/', [SecretaryManagementController::class, 'index'])->middleware('secretary.permission:secretary_management')->name('mc-secretary-management');
                Route::get('/create', [SecretaryManagementController::class, 'create'])->middleware('secretary.permission:secretary_management')->name('mc-secretary-create');
                Route::get('/edit/{id}', [SecretaryManagementController::class, 'edit'])->middleware('secretary.permission:secretary_management')->name('mc-secretary-edit');
                Route::post('/store', [SecretaryManagementController::class, 'store'])->middleware('secretary.permission:secretary_management')->name('mc-secretary-store');
                Route::post('/update/{id}', [SecretaryManagementController::class, 'update'])->middleware('secretary.permission:secretary_management')->name('mc-secretary-update');
                Route::delete('/delete/{id}', [SecretaryManagementController::class, 'destroy'])->middleware('secretary.permission:secretary_management')->name('mc-secretary-delete');
                Route::post('group-action', [SecretaryManagementController::class, 'groupAction'])->name('mc-secretary-group-action');
                Route::patch('update-status', [SecretaryManagementController::class, 'updateStatus'])->name('mc-secretary-update-status');
            });
            Route::group(['prefix' => 'doctors-clinic'], function () {
                Route::get('activation/{clinic}', [ActivationDoctorsClinicController::class, 'index'])->middleware('secretary.permission:clinic_management')->name('activation-doctor-clinic');
                Route::post('activation/{id}/update-address', [ActivationDoctorsClinicController::class, 'updateAddress'])->middleware('secretary.permission:clinic_management')->name('doctors.clinic.update.address');
                Route::get('/doctors/clinic/{id}/phones', [ActivationDoctorsClinicController::class, 'getPhones'])->middleware('secretary.permission:clinic_management')->name('doctors.clinic.get.phones');
                Route::post('/doctors/clinic/{id}/phones', [ActivationDoctorsClinicController::class, 'updatePhones'])->middleware('secretary.permission:clinic_management')->name('doctors.clinic.update.phones');
                Route::post('/doctors/clinic/{id}/phones/delete', [ActivationDoctorsClinicController::class, 'deletePhone'])->middleware('secretary.permission:clinic_management')->name('doctors.clinic.delete.phone');
                Route::get('/clinic/{id}/secretary-phone', [ActivationDoctorsClinicController::class, 'getSecretaryPhone'])->middleware('secretary.permission:clinic_management')->name('doctors.clinic.get.secretary.phone');
                Route::get('/activation/clinic/cost/{clinic}', [CostController::class, 'index'])->middleware('secretary.permission:clinic_management')->name('doctors.clinic.cost');
                Route::get('/costs/{medical_center_id}/list', [CostController::class, 'listDeposits'])->middleware('secretary.permission:clinic_management')->name('cost.list');
                Route::post('/costs/delete', [CostController::class, 'deleteDeposit'])->middleware('secretary.permission:clinic_management')->name('cost.delete');
                Route::post('/doctors-clinic/duration/store', [DurationController::class, 'store'])->middleware('secretary.permission:clinic_management')->name('duration.store');
                Route::get('/activation/duration/{clinic}', [DurationController::class, 'index'])->middleware('secretary.permission:clinic_management')->name('duration.index');
                Route::get('/activation/workhours/{clinic}', [ActivationWorkhoursController::class, 'index'])->middleware('secretary.permission:clinic_management')->name('activation.workhours.index');
                Route::get('{clinicId}/{doctorId}', [ActivationWorkhoursController::class, 'getWorkHours'])->middleware('secretary.permission:clinic_management')->name('workhours.get');
                Route::post('/activation/workhours/store', [ActivationWorkhoursController::class, 'store'])->middleware('secretary.permission:clinic_management')->name('activation.workhours.store');
                Route::post('workhours/delete', [ActivationWorkhoursController::class, 'deleteWorkHours'])->middleware('secretary.permission:clinic_management')->name('activation.workhours.delete');
                Route::post('/dr/panel/start-appointment', [ActivationWorkhoursController::class, 'startAppointment'])->middleware('secretary.permission:clinic_management')->name('start.appointment');
                Route::post('/cost/store', [CostController::class, 'store'])->middleware('secretary.permission:clinic_management')->name('cost.store');
                Route::get('gallery', [DoctorsClinicManagementController::class, 'gallery'])->middleware('secretary.permission:clinic_management')->name('mc-office-gallery');
                Route::get('medicalDoc', [DoctorsClinicManagementController::class, 'medicalDoc'])->middleware('secretary.permission:clinic_management')->name('mc-office-medicalDoc');
                Route::get('/', [DoctorsClinicManagementController::class, 'index'])->middleware('secretary.permission:clinic_management')->name('mc-clinic-management');
                Route::get('clinic/{id}/edit', [DoctorsClinicManagementController::class, 'edit'])->name('mc.panel.clinics.edit');
                Route::get('/medical-documents', [DoctorsClinicManagementController::class, 'medicalDoc'])->name('mc.panel.clinics.medical-documents');
                Route::get('/edit/{id}/gallery', [DoctorsClinicManagementController::class, 'gallery'])->name('mc.panel.clinics.gallery');
                Route::post('/store', [DoctorsClinicManagementController::class, 'store'])->middleware('secretary.permission:clinic_management')->name('mc-clinic-store');
                Route::get('/dr/panel/DoctorsClinic/edit/{id}', [DoctorsClinicManagementController::class, 'edit'])->middleware('secretary.permission:clinic_management')->name('mc-clinic-edit');
                Route::post('/update/{id}', [DoctorsClinicManagementController::class, 'update'])->middleware('secretary.permission:clinic_management')->name('mc-clinic-update');
                Route::delete('/delete/{id}', [DoctorsClinicManagementController::class, 'destroy'])->middleware('secretary.permission:clinic_management')->name('mc-clinic-destroy');
                Route::get('/create', [DoctorsClinicManagementController::class, 'create'])->name('mc.panel.clinics.create');
                Route::get('/deposit', [DoctorsClinicManagementController::class, 'deposit'])->middleware('secretary.permission:clinic_management')->name('mc-doctors.clinic.deposit');
                Route::post('/deposit/store', [DoctorsClinicManagementController::class, 'storeDeposit'])->middleware('secretary.permission:clinic_management')->name('doctors.clinic.deposit.store');
                Route::post('/deposit/update/{id}', [DoctorsClinicManagementController::class, 'updateDeposit'])->middleware('secretary.permission:clinic_management')->name('doctors.clinic.deposit.update');
                Route::post('/deposit/destroy/{id}', [DoctorsClinicManagementController::class, 'destroyDeposit'])->middleware('secretary.permission:clinic_management')->name('doctors.clinic.deposit.destroy');
            });
            Route::get('permission/', [SecretaryPermissionController::class, 'index'])->middleware('secretary.permission:permissions')->name('mc-secretary-permissions');
            Route::post('/permission/update/{secretary_id}', [SecretaryPermissionController::class, 'update'])->middleware('secretary.permission:permissions')->name('mc-secretary-permissions-update');
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
            // Doctor FAQs Routes
            Route::prefix('doctor-faqs')->group(function () {
                Route::get('/', [\App\Http\Controllers\Mc\Panel\DoctorFaqs\DoctorFaqController::class, 'index'])->name('mc.panel.doctor-faqs.index');
                Route::get('/create', [\App\Http\Controllers\Mc\Panel\DoctorFaqs\DoctorFaqController::class, 'create'])->name('mc.panel.doctor-faqs.create');
                Route::get('/edit/{id}', [\App\Http\Controllers\Mc\Panel\DoctorFaqs\DoctorFaqController::class, 'edit'])->name('mc.panel.doctor-faqs.edit');
            });
            Route::get('bime', [DRBimeController::class, 'index'])->middleware('secretary.permission:insurance')->name('mc-bime');
            Route::prefix('payment')->group(function () {
                Route::get('/wallet', [DrPaymentSettingController::class, 'wallet'])->middleware('secretary.permission:financial_reports')->name('mc-wallet');
                Route::get('/mc-wallet/verify', [WalletChargeComponent::class, 'verifyPayment'])->name('mc-wallet-verify');
                Route::get('/setting', [DrPaymentSettingController::class, 'index'])->middleware('secretary.permission:financial_reports')->name('mc-payment-setting');
                Route::get('/charge', function () {
                    return view('mc.panel.payment.charge');
                })->middleware('secretary.permission:financial_reports')->name('mc-wallet-charge');
                Route::get('/financial-reports', [App\Http\Controllers\Mc\Panel\FinancialReport\FinancialReportController::class, 'index'])
                                       ->name('mc.panel.financial-reports.index');
                Route::get('/financial-reports/export-excel', [App\Http\Controllers\Mc\Panel\FinancialReport\FinancialReportController::class, 'exportExcel'])
                    ->name('mc.panel.financial-reports.export-excel');
                Route::get('/financial-reports/export-pdf', [App\Http\Controllers\Mc\Panel\FinancialReport\FinancialReportController::class, 'exportPdf'])
                    ->name('mc.panel.financial-reports.export-pdf');
            });
            Route::prefix('profile')->group(function () {
                Route::get('edit-profile', [DrProfileController::class, 'edit'])->middleware('secretary.permission:profile')->name('mc-edit-profile');
                Route::post('/upload-profile-photo', [DrProfileController::class, 'uploadPhoto'])->name('mc.upload-photo')->middleware('auth:medical_center');
                Route::post('update-profile', [DrProfileController::class, 'update_profile'])->middleware('secretary.permission:profile')->name('mc-update-profile');
                Route::get('/mc-check-profile-completeness', [DrProfileController::class, 'checkProfileCompleteness'])->middleware('secretary.permission:profile')->name('mc-check-profile-completeness');
                Route::post('/send-mobile-otp', [DrProfileController::class, 'sendMobileOtp'])->middleware('secretary.permission:profile')->name('mc-send-mobile-otp');
                Route::post('/mobile-confirm/{token}', [DrProfileController::class, 'mobileConfirm'])->middleware('secretary.permission:profile')->name('mc-mobile-confirm');
                Route::post('/mc-specialty-update', [DrProfileController::class, 'DrSpecialtyUpdate'])->middleware('secretary.permission:profile')->name('mc-specialty-update');
                Route::get('/mc-get-current-specialty', [DrProfileController::class, 'getCurrentSpecialtyName'])->middleware('secretary.permission:profile')->name('mc-get-current-specialty');
                Route::delete('/dr/delete-specialty/{id}', [DrProfileController::class, 'deleteSpecialty'])->middleware('secretary.permission:profile')->name('mc-delete-specialty');
                Route::post('/mc-uuid-update', [DrProfileController::class, 'DrUUIDUpdate'])->middleware('secretary.permission:profile')->name('mc-uuid-update');
                Route::put('/mc-profile-messengers', [DrProfileController::class, 'updateMessengers'])->middleware('secretary.permission:profile')->name('mc-messengers-update');
                Route::post('mc-static-password-update', [DrProfileController::class, 'updateStaticPassword'])->middleware('secretary.permission:profile')->name('mc-static-password-update');
                Route::post('mc-two-factor-update', [DrProfileController::class, 'updateTwoFactorAuth'])->middleware('secretary.permission:profile')->name('mc-two-factor-update');
                Route::get('niceId', [DrProfileController::class, 'niceId'])->middleware('secretary.permission:profile')->name('mc-edit-profile-niceId');
                Route::get('security', [LoginLogsController::class, 'security'])->middleware('secretary.permission:profile')->name('mc-edit-profile-security');
                Route::get('/dr/panel/profile/security/doctor-logs', [LoginLogsController::class, 'getDoctorLogs'])->name('mc-get-doctor-logs');
                Route::get('/dr/panel/profile/security/secretary-logs', [LoginLogsController::class, 'getSecretaryLogs'])->name('mc-get-secretary-logs');
                Route::delete('/dr/panel/profile/security/logs/{id}', [LoginLogsController::class, 'deleteLog'])->middleware('secretary.permission:profile')->name('delete-log');
                Route::get('upgrade', [DrUpgradeProfileController::class, 'index'])->middleware('secretary.permission:profile')->name('mc-edit-profile-upgrade');
                Route::delete('/doctor/payments/delete/{id}', [DrUpgradeProfileController::class, 'deletePayment'])->name('mc-payment-delete');
                Route::post('/pay', [DrUpgradeProfileController::class, 'payForUpgrade'])->name('doctor.upgrade.pay');
                Route::get('subuser', [SubUserController::class, 'index'])->middleware('secretary.permission:profile')->name('mc-subuser');
                Route::post('sub-users/store', [SubUserController::class, 'store'])->name('mc-sub-users-store');
                Route::get('sub-users/edit/{id}', [SubUserController::class, 'edit'])->name('mc-sub-users-edit');
                Route::post('sub-users/update/{id}', [SubUserController::class, 'update'])->name('mc-sub-users-update');
                Route::delete('sub-users/delete/{id}', [SubUserController::class, 'destroy'])->name('mc-sub-users-delete');
                Route::get('/dr/get-cities', [DrProfileController::class, 'getCities'])->name('mc-get-cities')->middleware('auth:doctor,secretary');
                Route::get('/debug-profile-completion', [DrProfileController::class, 'debugProfileCompletion'])->name('mc-debug-profile-completion');
                Route::post('/debug-otp-validation', [DrProfileController::class, 'debugOtpValidation'])->name('mc-debug-otp-validation');
                // Routes for Doctor FAQs
                Route::post('/faqs/store', [DrProfileController::class, 'storeFaq'])->name('mc-faqs-store');
                Route::put('/faqs/{id}/update', [DrProfileController::class, 'updateFaq'])->name('mc-faqs-update');
                Route::delete('/faqs/{id}/delete', [DrProfileController::class, 'deleteFaq'])->name('mc-faqs-delete');
                Route::get('/faqs/{id}', [DrProfileController::class, 'getFaq'])->name('mc-faqs-get');
                Route::get('/faqs', [DrProfileController::class, 'indexFaqs'])->name('mc-faqs-index');
            });
            // SubUser routes moved from bottom
            Route::get('/profile/subusers/list', [\App\Http\Controllers\Mc\Panel\Profile\SubUserController::class, 'list'])->name('mc-sub-users-list');
            Route::delete('/profile/subusers/delete-multiple', [\App\Http\Controllers\Mc\Panel\Profile\SubUserController::class, 'destroyMultiple'])->name('mc-sub-users-delete-multiple');
            Route::get('/profile/subusers/search-users', [\App\Http\Controllers\Mc\Panel\Profile\SubUserController::class, 'searchUsers'])->name('mc-sub-users-search-users');
            Route::post('/profile/subusers/quick-create-user', [\App\Http\Controllers\Mc\Panel\Profile\SubUserController::class, 'quickCreateUser'])->name('mc-sub-users-quick-create-user');
            // My Prescriptions routes moved from bottom
            Route::get('my-prescriptions', [\App\Http\Controllers\Mc\Panel\DoctorPrescriptionController::class, 'index'])->name('mc.panel.my-prescriptions');
            Route::get('my-prescriptions/settings', [\App\Http\Controllers\Mc\Panel\DoctorPrescriptionController::class, 'settings'])->name('mc.panel.my-prescriptions.settings');
        });
    });

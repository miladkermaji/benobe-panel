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
            Route::get('/', [McPanelController::class, 'index'])->middleware('medical_center.permission:dashboard')->name('mc-panel');
            Route::prefix('doctor-services')->group(function () {
                Route::get('/', [\App\Http\Controllers\Mc\Panel\DoctorService\DoctorServiceController::class, 'index'])->middleware('medical_center.permission:doctor_services')->name('mc.panel.doctor-services.index');
                Route::get('/create', [\App\Http\Controllers\Mc\Panel\DoctorService\DoctorServiceController::class, 'create'])->middleware('medical_center.permission:doctor_services')->name('mc.panel.doctor-services.create');
                Route::get('/edit/{id}', [\App\Http\Controllers\Mc\Panel\DoctorService\DoctorServiceController::class, 'edit'])->middleware('medical_center.permission:doctor_services')->name('mc.panel.doctor-services.edit');
            });
            Route::prefix('patient-contact/send-message')->group(function () {
                Route::get('/', [DoctorSendMessageController::class, 'index'])->middleware('medical_center.permission:patient_communication')->name('mc.panel.send-message');
            });
            Route::post('appointments/{id}/end-visit-counseling', [MoshavereWaitingController::class, 'endVisit'])->name('doctor.end-visit-counseling');
            Route::get('/search-appointments-counseling', [MoshavereWaitingController::class, 'searchAppointments'])->middleware('medical_center.permission:appointments')->name('search.appointments.counseling');
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
            Route::prefix('turn')->middleware('medical_center.permission:appointments')->group(function () {
                Route::prefix('schedule')->group(function () {
                    Route::get('/appointments', [McDrScheduleController::class, 'index'])->middleware('medical_center.permission:mc-appointments')->name('mc-appointments');
                    Route::get('search-appointments', [McDrScheduleController::class, 'searchAppointments'])->name('mc.search.appointments');
                    Route::post('end-visit/{id}', [McDrScheduleController::class, 'endVisit'])->name('end.visit');
                    Route::get('/my-appointments', [McDrScheduleController::class, 'myAppointments'])->middleware('medical_center.permission:my-mc-appointments')->name('my-mc-appointments');
                    Route::get('/my-appointments/by-date', [McDrScheduleController::class, 'showByDateAppointments'])->name('mc.turn.my-appointments.by-date');
                    Route::get('filter-appointments', [McDrScheduleController::class, 'filterAppointments'])->name('mc.turn.filter-appointments');
                    Route::get('/moshavere_setting', [DrMoshavereSettingController::class, 'index'])->middleware('medical_center.permission:mc-moshavere_setting')->name('mc-moshavere_setting');
                    Route::post('/copy-work-hours-counseling', [DrMoshavereSettingController::class, 'copyWorkHours'])->middleware('medical_center.permission:mc-moshavere_setting')->name('copy-work-hours-counseling');
                    Route::get('get-work-schedule-counseling', [DrMoshavereSettingController::class, 'getWorkSchedule'])->middleware('medical_center.permission:mc-moshavere_setting')->name('mc-get-work-schedule-counseling');
                    Route::post('/copy-single-slot-counseling', [DrMoshavereSettingController::class, 'copySingleSlot'])->middleware('medical_center.permission:mc-moshavere_setting')->name('copy-single-slot-counseling');
                    Route::post('/save-time-slot-counseling', [DrMoshavereSettingController::class, 'saveTimeSlot'])->middleware('medical_center.permission:mc-moshavere_setting')->name('save-time-slot-counseling');
                    Route::get('/get-appointment-settings-counseling', [DrMoshavereSettingController::class, 'getAppointmentSettings'])->middleware('medical_center.permission:mc-moshavere_setting')->name('get-appointment-settings-counseling');
                    Route::delete('/appointment-slots-conseling/{id}', [DrMoshavereSettingController::class, 'destroy'])->middleware('medical_center.permission:mc-moshavere_setting')->name('appointment.slots.destroy-counseling');
                    Route::post('save-work-schedule-counseling', [DrMoshavereSettingController::class, 'saveWorkSchedule'])->middleware('medical_center.permission:mc-moshavere_setting')->name('mc-save-work-schedule-counseling');
                    Route::post('/dr/update-work-day-status-counseling', [DrMoshavereSettingController::class, 'updateWorkDayStatus'])->middleware('medical_center.permission:mc-moshavere_setting')->name('update-work-day-status-counseling');
                    Route::post('/update-auto-scheduling-counseling', [DrMoshavereSettingController::class, 'updateAutoScheduling'])->middleware('medical_center.permission:mc-moshavere_setting')->name('update-auto-scheduling-counseling');
                    Route::get('/get-all-days-settings-counseling', [DrMoshavereSettingController::class, 'getAllDaysSettings'])->middleware('medical_center.permission:mc-moshavere_setting')->name('get-all-days-settings-counseling');
                    Route::post('/save-appointment-settings-counseling', [DrMoshavereSettingController::class, 'saveAppointmentSettings'])->middleware('medical_center.permission:mc-moshavere_setting')->name('save-appointment-settings-counseling');
                    Route::post('/delete-schedule-setting-counseling', [DrMoshavereSettingController::class, 'deleteScheduleSetting'])->middleware('medical_center.permission:mc-moshavere_setting')->name('delete-schedule-setting-counseling');
                    Route::get('/moshavere_waiting', [MoshavereWaitingController::class, 'index'])->middleware('medical_center.permission:mc-moshavere_waiting')->name('mc-moshavere_waiting');
                    Route::get('/doctor/appointments/by-date-counseling', [MoshavereWaitingController::class, 'getAppointmentsByDate'])
                                                ->name('doctor.appointments.by-date-counseling');
                    Route::get('/manual_nobat', [ManualNobatController::class, 'index'])->middleware('medical_center.permission:mc-manual_nobat')->name('mc-manual_nobat');
                    Route::post('manual_nobat/store', [ManualNobatController::class, 'store'])->middleware('medical_center.permission:mc-manual_nobat')->name('manual-nobat.store');
                    Route::post('manual-nobat/store-with-user', [ManualNobatController::class, 'storeWithUser'])->middleware('medical_center.permission:mc-manual_nobat')->name('manual-nobat.store-with-user');
                    Route::delete('/manual_appointments/{id}', [ManualNobatController::class, 'destroy'])->middleware('medical_center.permission:mc-manual_nobat')->name('manual_appointments.destroy');
                    Route::get('/manual_appointments/{id}/edit', [ManualNobatController::class, 'edit'])->middleware('medical_center.permission:mc-manual_nobat')->name('manual-appointments.edit');
                    Route::post('/manual_appointments/{id}', [ManualNobatController::class, 'update'])->middleware('medical_center.permission:mc-manual_nobat')->name('manual-appointments.update');
                    Route::post('/manual-nobat/settings/save', [ManualNobatController::class, 'saveSettings'])->middleware('medical_center.permission:mc-manual_nobat')->name('manual-nobat.settings.save');
                    Route::get('/manual_nobat_setting', [ManualNobatController::class, 'showSettings'])->middleware('medical_center.permission:mc-manual_nobat_setting')->name('mc-manual_nobat_setting');
                    Route::get('/search-users', [ManualNobatController::class, 'searchUsers'])->middleware('medical_center.permission:mc-manual_nobat')->name('mc-panel-search.users');
                    Route::get('/scheduleSetting', [ScheduleSettingController::class, 'index'])->middleware('medical_center.permission:mc-scheduleSetting')->name('mc-scheduleSetting');
                    Route::get('/insurances', [ManualNobatController::class, 'getInsurances'])->name('manual-nobat.insurances');
                    Route::get('/services/{insuranceId}', [ManualNobatController::class, 'getServices'])->name('manual-nobat.services');
                    Route::post('/calculate-final-price', [ManualNobatController::class, 'calculateFinalPrice'])->name('manual-nobat.calculate-final-price');
                    Route::post('/{id}/end-visit', [ManualNobatController::class, 'endVisit'])->name('manual-nobat.end-visit');
                    Route::prefix('scheduleSetting/vacation')->group(function () {
                        Route::get('/', [VacationController::class, 'index'])->middleware('medical_center.permission:mc-vacation')->name('mc-vacation');
                        Route::post('/store', [VacationController::class, 'store'])->middleware('medical_center.permission:mc-vacation')->name('doctor.vacation.store');
                        Route::post('/update/{id}', [VacationController::class, 'update'])->middleware('medical_center.permission:mc-vacation')->name('doctor.vacation.update');
                        Route::delete('/delete/{id}', [VacationController::class, 'destroy'])->middleware('medical_center.permission:mc-vacation')->name('doctor.vacation.destroy');
                        Route::get('/doctor/vacation/{id}/edit', [VacationController::class, 'edit'])->middleware('medical_center.permission:mc-vacation')->name('doctor.vacation.edit');
                    });
                    Route::prefix('scheduleSetting/blocking_users')->group(function () {
                        Route::get('/', [BlockingUsersController::class, 'index'])
                            ->middleware('medical_center.permission:mc-doctor-blocking-users.index')
                            ->name('mc-doctor-blocking-users.index');
                        Route::post('/store', [BlockingUsersController::class, 'store'])
                            ->middleware('medical_center.permission:mc-doctor-blocking-users.index')
                            ->name('doctor-blocking-users.store');
                        // اضافه کردن روت جدید برای مسدود کردن گروهی کاربران
                        Route::post('/store-multiple', [BlockingUsersController::class, 'storeMultiple'])
                            ->middleware('medical_center.permission:mc-doctor-blocking-users.index')
                            ->name('doctor-blocking-users.store-multiple');
                        Route::post('/send-message', [BlockingUsersController::class, 'sendMessage'])
                            ->middleware('medical_center.permission:mc-doctor-blocking-users.index')
                            ->name('doctor-blocking-users.send-message');
                        Route::get('/message-lists', [BlockingUsersController::class, 'getMessages'])
                            ->middleware('medical_center.permission:mc-doctor-blocking-users.index')
                            ->name('doctor-blocking-users.messages');
                        Route::delete('/doctor-blocking-users/{id}', [BlockingUsersController::class, 'destroy'])
                            ->middleware('medical_center.permission:mc-doctor-blocking-users.index')
                            ->name('doctor-blocking-users.destroy');
                        Route::patch('/update-status', [BlockingUsersController::class, 'updateStatus'])
                            ->middleware('medical_center.permission:mc-doctor-blocking-users.index')
                            ->name('doctor-blocking-users.update-status');
                        Route::post('/messages/delete', [BlockingUsersController::class, 'deleteMessage'])
                        ->middleware('medical_center.permission:mc-doctor-blocking-users.index')
                        ->name('doctor-blocking-users.delete-message');
                    });
                    Route::get('/scheduleSetting/workhours', [McScheduleSettingController::class, 'workhours'])->middleware('medical_center.permission:mc-workhours')->name('mc-workhours');
                    Route::post('/save-appointment-settings', [ScheduleSettingController::class, 'saveAppointmentSettings'])->middleware('medical_center.permission:appointments')->name('save-appointment-settings');
                    Route::get('/get-appointment-settings', [ScheduleSettingController::class, 'getAppointmentSettings'])->middleware('medical_center.permission:appointments')->name('get-appointment-settings');
                    Route::post('/delete-schedule-setting', [ScheduleSettingController::class, 'deleteScheduleSetting'])->middleware('medical_center.permission:appointments')->name('delete-schedule-setting');
                    Route::get('/get-all-days-settings', [ScheduleSettingController::class, 'getAllDaysSettings'])->middleware('medical_center.permission:appointments')->name('get-all-days-settings');
                    // ذخیره‌سازی تنظیمات ساعات کاری
                    Route::post('save-work-schedule', [ScheduleSettingController::class, 'saveWorkSchedule'])->middleware('medical_center.permission:appointments')->name('mc-save-work-schedule');
                    Route::post('save-schedule', [ScheduleSettingController::class, 'saveSchedule'])->middleware('medical_center.permission:appointments')->name('save-schedule');
                    Route::delete('/appointment-slots/{id}', [ScheduleSettingController::class, 'destroy'])->middleware('medical_center.permission:appointments')->name('appointment.slots.destroy');
                    // بازیابی تنظیمات ساعات کاری
                    Route::get('get-work-schedule', [ScheduleSettingController::class, 'getWorkSchedule'])->middleware('medical_center.permission:appointments')->name('mc-get-work-schedule');
                    Route::post('/dr/update-work-day-status', [ScheduleSettingController::class, 'updateWorkDayStatus'])->middleware('medical_center.permission:appointments')->name('update-work-day-status');
                    Route::post('/check-day-slots', [ScheduleSettingController::class, 'checkDaySlots'])->middleware('medical_center.permission:appointments')->name('check-day-slots');
                    Route::post('/update-auto-scheduling', [ScheduleSettingController::class, 'updateAutoScheduling'])->middleware('medical_center.permission:appointments')->name('update-auto-scheduling');
                    // routes/web.php
                    Route::post('/copy-work-hours', [ScheduleSettingController::class, 'copyWorkHours'])->middleware('medical_center.permission:appointments')->name('copy-work-hours');
                    Route::post('/copy-single-slot', [ScheduleSettingController::class, 'copySingleSlot'])->middleware('medical_center.permission:appointments')->name('copy-single-slot');
                    Route::post('/save-time-slot', [ScheduleSettingController::class, 'saveTimeSlot'])->middleware('medical_center.permission:appointments')->name('save-time-slot');
                    Route::get('/scheduleSetting/my-special-days', [ScheduleSettingController::class, 'mySpecialDays'])->middleware('medical_center.permission:appointments')->name('mc-mySpecialDays');
                    Route::get('/scheduleSetting/counseling/my-special-days', [MySpecialDaysCounselingController::class, 'mySpecialDays'])->middleware('medical_center.permission:appointments')->name('mc-mySpecialDays-counseling');
                    Route::get('/appointments-by-date', [ScheduleSettingController::class, 'getAppointmentsByDateSpecial'])->name('doctor.get_appointments_by_date');
                    Route::get('/appointments-by-date-counseling', [MoshavereWaitingController::class, 'getAppointmentsByDateSpecial'])->name('doctor.get_appointments_by_date_counseling');
                    Route::get('/doctor/default-schedule', [ScheduleSettingController::class, 'getDefaultSchedule'])->name('doctor.get_default_schedule');
                    Route::get('/doctor/default-schedule-counseling', [MoshavereWaitingController::class, 'getDefaultSchedule'])->name('doctor.get_default_schedule_counseling');
                    Route::get('/doctor/default-schedule-counseling', [MySpecialDaysCounselingController::class, 'getDefaultSchedule'])->name('doctor.get_default_schedule_counseling');
                    Route::post('/doctor/update-work-schedule', [ScheduleSettingController::class, 'updateWorkSchedule'])->name('doctor.update_work_schedule');
                    Route::post('/doctor/update-work-schedule-counseling', [MoshavereWaitingController::class, 'updateWorkSchedule'])->name('doctor.update_work_schedule_counseling');
                    Route::post('/doctor/update-work-schedule-counseling', [MySpecialDaysCounselingController::class, 'updateWorkSchedule'])->name('doctor.update_work_schedule_counseling');
                    Route::get('/appointments-count', [McScheduleSettingController::class, 'getAppointmentsCountPerDay'])->middleware('medical_center.permission:appointments')->name('appointments.count');
                    Route::get('/work-days-and-config', [ScheduleSettingController::class, 'getWorkDaysAndConfig'])->name('work.days.config');
                    Route::get('/appointments-count-counseling', [MySpecialDaysCounselingController::class, 'getAppointmentsCountPerDay'])->middleware('medical_center.permission:appointments')->name('appointments.count.counseling');
                    Route::get('/appointments/by-date', [ScheduleSettingController::class, 'getAppointmentsByDate'])->middleware('medical_center.permission:appointments')->name('appointments.by_date');
                    Route::post('/doctor/add-holiday', [ScheduleSettingController::class, 'addHoliday'])->middleware('medical_center.permission:appointments')->name('doctor.add_holiday');
                    Route::get('/doctor/get-holidays', [ScheduleSettingController::class, 'getHolidayDates'])->middleware('medical_center.permission:appointments')->name('doctor.get_holidays');
                    Route::get('/doctor/get-holidays-counseling', [MySpecialDaysCounselingController::class, 'getHolidayDates'])->middleware('medical_center.permission:appointments')->name('doctor.get_holidays_counseling');
                    Route::post('/doctor/toggle-holiday', [ScheduleSettingController::class, 'toggleHolidayStatus'])->middleware('medical_center.permission:appointments')->name('doctor.toggle_holiday');
                    Route::post('/doctor/toggle-holiday-counseling', [MySpecialDaysCounselingController::class, 'toggleHolidayStatus'])->middleware('medical_center.permission:appointments')->name('doctor.toggle_holiday_counseling');
                    Route::post('/doctor/holiday-status', [ScheduleSettingController::class, 'getHolidayStatus'])->middleware('medical_center.permission:appointments')->name('doctor.get_holiday_status');
                    Route::post('/doctor/holiday-status-counseling', [MoshavereWaitingController::class, 'getHolidayStatus'])->middleware('medical_center.permission:appointments')->name('doctor.get_holiday_status_counseling');
                    Route::post('/doctor/holiday-status-counseling', [MySpecialDaysCounselingController::class, 'getHolidayStatus'])->middleware('medical_center.permission:appointments')->name('doctor.get_holiday_status_counseling');
                    Route::post('/doctor/cancel-appointments', [ScheduleSettingController::class, 'cancelAppointments'])->middleware('medical_center.permission:appointments')->name('doctor.cancel_appointments');
                    Route::post('/doctor/cancel-appointments-counseling', [MoshavereWaitingController::class, 'cancelAppointments'])->middleware('medical_center.permission:appointments')->name('doctor.cancel_appointments_counseling');
                    Route::post('/doctor/cancel-appointments-counseling', [MySpecialDaysCounselingController::class, 'cancelAppointments'])->middleware('medical_center.permission:appointments')->name('doctor.cancel_appointments_counseling');
                    Route::post('/doctor/reschedule-appointment', [McScheduleSettingController::class, 'rescheduleAppointment'])->middleware('medical_center.permission:appointments')->name('doctor.reschedule_appointment');
                    Route::post('/doctor/reschedule-appointment-counseling', [MoshavereWaitingController::class, 'rescheduleAppointment'])->middleware('medical_center.permission:appointments')->name('doctor.reschedule_appointment_counseling');
                    Route::post('/doctor/reschedule-appointment-counseling', [MySpecialDaysCounselingController::class, 'rescheduleAppointment'])->middleware('medical_center.permission:appointments')->name('doctor.reschedule_appointment_counseling');
                    Route::get('/turnContract', [ScheduleSettingController::class, 'turnContract'])->middleware('medical_center.permission:appointments')->name('mc-scheduleSetting-turnContract');
                    Route::post('/update-first-available-appointment', [ScheduleSettingController::class, 'updateFirstAvailableAppointment'])->middleware('medical_center.permission:appointments')->name('doctor.update_first_available_appointment');
                    Route::post('/update-first-available-appointment-counseling', [MoshavereWaitingController::class, 'updateFirstAvailableAppointment'])->middleware('medical_center.permission:appointments')->name('doctor.update_first_available_appointment_counseling');
                    Route::post('/update-first-available-appointment-counseling', [MySpecialDaysCounselingController::class, 'updateFirstAvailableAppointment'])->middleware('medical_center.permission:appointments')->name('doctor.update_first_available_appointment_counseling');
                    Route::get('get-next-available-date', [ScheduleSettingController::class, 'getNextAvailableDate'])->middleware('medical_center.permission:appointments')->name('doctor.get_next_available_date');
                    Route::get('get-next-available-date-counseling', [MoshavereWaitingController::class, 'getNextAvailableDate'])->middleware('medical_center.permission:appointments')->name('doctor.get_next_available_date_counseling');
                    Route::get('get-next-available-date-counseling', [MySpecialDaysCounselingController::class, 'getNextAvailableDate'])->middleware('medical_center.permission:appointments')->name('doctor.get_next_available_date_counseling');
                    Route::delete('/appointments/destroy/{id}', [AppointmentController::class, 'destroyAppointment'])->middleware('medical_center.permission:appointments')->name('appointments.destroy');
                    Route::post('/toggle-auto-pattern/{id}', [AppointmentController::class, 'toggleAutoPattern'])->middleware('medical_center.permission:appointments')->name('toggle-auto-pattern');
                });
                Route::prefix('Counseling')->group(function () {
                    Route::get('/consult-term', [ConsultTermController::class, 'index'])->middleware('medical_center.permission:appointments')->name('consult-term.index');
                });
                Route::post('/update-auto-schedule', [McDrScheduleController::class, 'updateAutoSchedule'])->middleware('medical_center.permission:appointments')->name('update-auto-schedule');
                Route::get('/check-auto-schedule', [McDrScheduleController::class, 'checkAutoSchedule'])->middleware('medical_center.permission:appointments')->name('check-auto-schedule');
                Route::get('get-available-times', [McDrScheduleController::class, 'getAvailableTimes'])->middleware('medical_center.permission:appointments')->name('getAvailableTimes');
                Route::post('update-day-status', [McDrScheduleController::class, 'updateDayStatus'])->middleware('medical_center.permission:appointments')->name('updateDayStatus');
                Route::get('disabled-days', [McDrScheduleController::class, 'disabledDays'])->middleware('medical_center.permission:appointments')->name('disabledDays');
                Route::post('/convert-to-gregorian', [AppointmentController::class, 'convertToGregorian'])->middleware('medical_center.permission:appointments')->name('convert-to-gregorian');
                Route::get('/search-appointments', [AppointmentController::class, 'searchAppointments'])->middleware('medical_center.permission:appointments')->name('search.appointments');
                Route::get('/turnsCatByDays', [TurnsCatByDaysController::class, 'index'])->middleware('medical_center.permission:appointments')->name('mc-turnsCatByDays');
                Route::post('/appointments/{id}/status', [AppointmentController::class, 'updateStatus'])->middleware('medical_center.permission:appointments')->name('updateStatusAppointment');
                Route::get('/get-appointments-count/{doctorId}/{date}', [McDrScheduleController::class, 'getAppointmentsCount'])->middleware('medical_center.permission:appointments')->name('get-appointments-count');
            });
            Route::get('/patient-records', [PatientRecordsController::class, 'index'])->middleware('medical_center.permission:patient_records')->name('mc-patient-records');
            Route::prefix('tickets')->group(function () {
                Route::get('/', [TicketsController::class, 'index'])->name('mc-panel-tickets');
                Route::post('/store', [TicketsController::class, 'store'])->name('mc-panel-tickets.store');
                Route::delete('/destroy/{id}', [TicketsController::class, 'destroy'])->name('mc-panel-tickets.destroy');
                Route::get('/show/{id}', [TicketsController::class, 'show'])->name('mc-panel-tickets.show');
                // مسیرهای مربوط به پاسخ تیکت‌ها
                Route::post('/{id}/responses', [TicketResponseController::class, 'store'])->name('mc-panel-tickets.responses.store');
            });
            Route::get('activation/consult/rules', [ConsultRulesController::class, 'index'])->middleware('medical_center.permission:consult')->name('activation.consult.rules');
            Route::get('activation/consult/help', [ConsultRulesController::class, 'help'])->middleware('medical_center.permission:consult')->name('activation.consult.help');
            Route::get('activation/consult/messengers', [ConsultRulesController::class, 'messengers'])->middleware('medical_center.permission:consult')->name('activation.consult.messengers');
            Route::get('my-performance/', [MyPerformanceController::class, 'index'])->middleware('medical_center.permission:statistics')->name('mc-my-performance');
            Route::get('/dr/my-performance/data', [MyPerformanceController::class, 'getPerformanceData'])->name('mc-my-performance-data');
            Route::get('my-performance/doctor-chart', [MyPerformanceController::class, 'chart'])->middleware('medical_center.permission:statistics')->name('mc-my-performance-chart');
            Route::get('my-performance/chart-data', [MyPerformanceController::class, 'getChartData'])
                ->name('mc-my-performance-chart-data');
            Route::group(['prefix' => 'secretary'], function () {
                Route::get('/', [SecretaryManagementController::class, 'index'])->middleware('medical_center.permission:secretary_management')->name('mc-secretary-management');
                Route::get('/create', [SecretaryManagementController::class, 'create'])->middleware('medical_center.permission:secretary_management')->name('mc-secretary-create');
                Route::get('/edit/{id}', [SecretaryManagementController::class, 'edit'])->middleware('medical_center.permission:secretary_management')->name('mc-secretary-edit');
                Route::post('/store', [SecretaryManagementController::class, 'store'])->middleware('medical_center.permission:secretary_management')->name('mc-secretary-store');
                Route::post('/update/{id}', [SecretaryManagementController::class, 'update'])->middleware('medical_center.permission:secretary_management')->name('mc-secretary-update');
                Route::delete('/delete/{id}', [SecretaryManagementController::class, 'destroy'])->middleware('medical_center.permission:secretary_management')->name('mc-secretary-delete');
                Route::post('group-action', [SecretaryManagementController::class, 'groupAction'])->name('mc-secretary-group-action');
                Route::patch('update-status', [SecretaryManagementController::class, 'updateStatus'])->name('mc-secretary-update-status');
            });
            Route::group(['prefix' => 'doctors-clinic'], function () {
                Route::get('activation/{clinic}', [ActivationDoctorsClinicController::class, 'index'])->middleware('medical_center.permission:clinic_management')->name('activation-doctor-clinic');
                Route::post('activation/{id}/update-address', [ActivationDoctorsClinicController::class, 'updateAddress'])->middleware('medical_center.permission:clinic_management')->name('doctors.clinic.update.address');
                Route::get('/doctors/clinic/{id}/phones', [ActivationDoctorsClinicController::class, 'getPhones'])->middleware('medical_center.permission:clinic_management')->name('doctors.clinic.get.phones');
                Route::post('/doctors/clinic/{id}/phones', [ActivationDoctorsClinicController::class, 'updatePhones'])->middleware('medical_center.permission:clinic_management')->name('doctors.clinic.update.phones');
                Route::post('/doctors/clinic/{id}/phones/delete', [ActivationDoctorsClinicController::class, 'deletePhone'])->middleware('medical_center.permission:clinic_management')->name('doctors.clinic.delete.phone');
                Route::get('/clinic/{id}/secretary-phone', [ActivationDoctorsClinicController::class, 'getSecretaryPhone'])->middleware('medical_center.permission:clinic_management')->name('doctors.clinic.get.secretary.phone');
                Route::get('/activation/clinic/cost/{clinic}', [CostController::class, 'index'])->middleware('medical_center.permission:clinic_management')->name('doctors.clinic.cost');
                Route::get('/costs/{medical_center_id}/list', [CostController::class, 'listDeposits'])->middleware('medical_center.permission:clinic_management')->name('cost.list');
                Route::post('/costs/delete', [CostController::class, 'deleteDeposit'])->middleware('medical_center.permission:clinic_management')->name('cost.delete');
                Route::post('/doctors-clinic/duration/store', [DurationController::class, 'store'])->middleware('medical_center.permission:clinic_management')->name('duration.store');
                Route::get('/activation/duration/{clinic}', [DurationController::class, 'index'])->middleware('medical_center.permission:clinic_management')->name('duration.index');
                Route::get('/activation/workhours/{clinic}', [ActivationWorkhoursController::class, 'index'])->middleware('medical_center.permission:clinic_management')->name('activation.workhours.index');
                Route::get('{clinicId}/{doctorId}', [ActivationWorkhoursController::class, 'getWorkHours'])->middleware('medical_center.permission:clinic_management')->name('workhours.get');
                Route::post('/activation/workhours/store', [ActivationWorkhoursController::class, 'store'])->middleware('medical_center.permission:clinic_management')->name('activation.workhours.store');
                Route::post('workhours/delete', [ActivationWorkhoursController::class, 'deleteWorkHours'])->middleware('medical_center.permission:clinic_management')->name('activation.workhours.delete');
                Route::post('/dr/panel/start-appointment', [ActivationWorkhoursController::class, 'startAppointment'])->middleware('medical_center.permission:clinic_management')->name('start.appointment');
                Route::post('/cost/store', [CostController::class, 'store'])->middleware('medical_center.permission:clinic_management')->name('cost.store');
                Route::get('gallery', [DoctorsClinicManagementController::class, 'gallery'])->middleware('medical_center.permission:clinic_management')->name('mc-office-gallery');
                Route::get('medicalDoc', [DoctorsClinicManagementController::class, 'medicalDoc'])->middleware('medical_center.permission:clinic_management')->name('mc-office-medicalDoc');
                Route::get('/', [DoctorsClinicManagementController::class, 'index'])->middleware('medical_center.permission:clinic_management')->name('mc-clinic-management');
                Route::get('clinic/{id}/edit', [DoctorsClinicManagementController::class, 'edit'])->name('mc.panel.clinics.edit');
                Route::get('/medical-documents', [DoctorsClinicManagementController::class, 'medicalDoc'])->name('mc.panel.clinics.medical-documents');
                Route::get('/edit/{id}/gallery', [DoctorsClinicManagementController::class, 'gallery'])->name('mc.panel.clinics.gallery');
                Route::post('/store', [DoctorsClinicManagementController::class, 'store'])->middleware('medical_center.permission:clinic_management')->name('mc-clinic-store');
                Route::get('/dr/panel/DoctorsClinic/edit/{id}', [DoctorsClinicManagementController::class, 'edit'])->middleware('medical_center.permission:clinic_management')->name('mc-clinic-edit');
                Route::post('/update/{id}', [DoctorsClinicManagementController::class, 'update'])->middleware('medical_center.permission:clinic_management')->name('mc-clinic-update');
                Route::delete('/delete/{id}', [DoctorsClinicManagementController::class, 'destroy'])->middleware('medical_center.permission:clinic_management')->name('mc-clinic-destroy');
                Route::get('/create', [DoctorsClinicManagementController::class, 'create'])->name('mc.panel.clinics.create');
                Route::get('/deposit', [DoctorsClinicManagementController::class, 'deposit'])->middleware('medical_center.permission:clinic_management')->name('mc-doctors.clinic.deposit');
                Route::post('/deposit/store', [DoctorsClinicManagementController::class, 'storeDeposit'])->middleware('medical_center.permission:clinic_management')->name('doctors.clinic.deposit.store');
                Route::post('/deposit/update/{id}', [DoctorsClinicManagementController::class, 'updateDeposit'])->middleware('medical_center.permission:clinic_management')->name('doctors.clinic.deposit.update');
                Route::post('/deposit/destroy/{id}', [DoctorsClinicManagementController::class, 'destroyDeposit'])->middleware('medical_center.permission:clinic_management')->name('doctors.clinic.deposit.destroy');
            });
            Route::get('permission/', [SecretaryPermissionController::class, 'index'])->middleware('medical_center.permission:permissions')->name('mc-secretary-permissions');
            Route::post('/permission/update/{secretary_id}', [SecretaryPermissionController::class, 'update'])->middleware('medical_center.permission:permissions')->name('mc-secretary-permissions-update');
            Route::group(['prefix' => 'noskhe-electronic'], function () {
                Route::get('prescription/', [PrescriptionController::class, 'index'])->middleware('medical_center.permission:prescription')->name('prescription.index');
                Route::get('prescription/create', [PrescriptionController::class, 'create'])->middleware('medical_center.permission:prescription')->name('prescription.create');
                Route::get('providers/', [ProvidersController::class, 'index'])->middleware('medical_center.permission:prescription')->name('providers.index');
                Route::group(['prefix' => 'favorite'], function () {
                    Route::get('templates/', [FavoriteTemplatesController::class, 'index'])->middleware('medical_center.permission:prescription')->name('favorite.templates.index');
                    Route::get('templates/create', [FavoriteTemplatesController::class, 'create'])->middleware('medical_center.permission:prescription')->name('favorite.templates.create');
                    Route::get('templates/service', [ServiceController::class, 'index'])->middleware('medical_center.permission:prescription')->name('templates.favorite.service.index');
                });
            });
            // Doctor FAQs Routes
            Route::prefix('doctor-faqs')->group(function () {
                Route::get('/', [\App\Http\Controllers\Mc\Panel\DoctorFaqs\DoctorFaqController::class, 'index'])->name('mc.panel.doctor-faqs.index');
                Route::get('/create', [\App\Http\Controllers\Mc\Panel\DoctorFaqs\DoctorFaqController::class, 'create'])->name('mc.panel.doctor-faqs.create');
                Route::get('/edit/{id}', [\App\Http\Controllers\Mc\Panel\DoctorFaqs\DoctorFaqController::class, 'edit'])->name('mc.panel.doctor-faqs.edit');
            });
            Route::get('bime', [DRBimeController::class, 'index'])->middleware('medical_center.permission:insurance')->name('mc-bime');
            Route::prefix('payment')->group(function () {
                Route::get('/wallet', [DrPaymentSettingController::class, 'wallet'])->middleware('medical_center.permission:financial_reports')->name('mc-wallet');
                Route::get('/mc-wallet/verify', [WalletChargeComponent::class, 'verifyPayment'])->name('mc-wallet-verify');
                Route::get('/setting', [DrPaymentSettingController::class, 'index'])->middleware('medical_center.permission:financial_reports')->name('mc-payment-setting');
                Route::get('/charge', function () {
                    return view('mc.panel.payment.charge');
                })->middleware('medical_center.permission:financial_reports')->name('mc-wallet-charge');
                Route::get('/financial-reports', [App\Http\Controllers\Mc\Panel\FinancialReport\FinancialReportController::class, 'index'])
                                       ->name('mc.panel.financial-reports.index');
                Route::get('/financial-reports/export-excel', [App\Http\Controllers\Mc\Panel\FinancialReport\FinancialReportController::class, 'exportExcel'])
                    ->name('mc.panel.financial-reports.export-excel');
                Route::get('/financial-reports/export-pdf', [App\Http\Controllers\Mc\Panel\FinancialReport\FinancialReportController::class, 'exportPdf'])
                    ->name('mc.panel.financial-reports.export-pdf');
            });
            Route::prefix('profile')->group(function () {
                Route::get('edit-profile', [DrProfileController::class, 'edit'])->middleware('medical_center.permission:profile')->name('mc-edit-profile');
                Route::post('/upload-profile-photo', [DrProfileController::class, 'uploadPhoto'])->name('mc.upload-photo')->middleware('auth:medical_center');
                Route::post('update-profile', [DrProfileController::class, 'update_profile'])->middleware('medical_center.permission:profile')->name('mc-update-profile');
                Route::get('/mc-check-profile-completeness', [DrProfileController::class, 'checkProfileCompleteness'])->middleware('medical_center.permission:profile')->name('mc-check-profile-completeness');
                Route::post('/send-mobile-otp', [DrProfileController::class, 'sendMobileOtp'])->middleware('medical_center.permission:profile')->name('mc-send-mobile-otp');
                Route::post('/mobile-confirm/{token}', [DrProfileController::class, 'mobileConfirm'])->middleware('medical_center.permission:profile')->name('mc-mobile-confirm');
                Route::post('/mc-specialty-update', [DrProfileController::class, 'DrSpecialtyUpdate'])->middleware('medical_center.permission:profile')->name('mc-specialty-update');
                Route::get('/mc-get-current-specialty', [DrProfileController::class, 'getCurrentSpecialtyName'])->middleware('medical_center.permission:profile')->name('mc-get-current-specialty');
                Route::delete('/dr/delete-specialty/{id}', [DrProfileController::class, 'deleteSpecialty'])->middleware('medical_center.permission:profile')->name('mc-delete-specialty');
                Route::post('/mc-uuid-update', [DrProfileController::class, 'DrUUIDUpdate'])->middleware('medical_center.permission:profile')->name('mc-uuid-update');
                Route::put('/mc-profile-messengers', [DrProfileController::class, 'updateMessengers'])->middleware('medical_center.permission:profile')->name('mc-messengers-update');
                Route::post('mc-static-password-update', [DrProfileController::class, 'updateStaticPassword'])->middleware('medical_center.permission:profile')->name('mc-static-password-update');
                Route::post('mc-two-factor-update', [DrProfileController::class, 'updateTwoFactorAuth'])->middleware('medical_center.permission:profile')->name('mc-two-factor-update');
                Route::get('niceId', [DrProfileController::class, 'niceId'])->middleware('medical_center.permission:profile')->name('mc-edit-profile-niceId');
                Route::get('security', [LoginLogsController::class, 'security'])->middleware('medical_center.permission:profile')->name('mc-edit-profile-security');
                Route::get('/dr/panel/profile/security/doctor-logs', [LoginLogsController::class, 'getDoctorLogs'])->name('mc-get-doctor-logs');
                Route::get('/dr/panel/profile/security/secretary-logs', [LoginLogsController::class, 'getSecretaryLogs'])->name('mc-get-secretary-logs');
                Route::delete('/dr/panel/profile/security/logs/{id}', [LoginLogsController::class, 'deleteLog'])->middleware('medical_center.permission:profile')->name('delete-log');
                Route::get('upgrade', [DrUpgradeProfileController::class, 'index'])->middleware('medical_center.permission:profile')->name('mc-edit-profile-upgrade');
                Route::delete('/doctor/payments/delete/{id}', [DrUpgradeProfileController::class, 'deletePayment'])->name('mc-payment-delete');
                Route::post('/pay', [DrUpgradeProfileController::class, 'payForUpgrade'])->name('doctor.upgrade.pay');
                Route::get('subuser', [SubUserController::class, 'index'])->middleware('medical_center.permission:profile')->name('mc-subuser');
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

            // Doctor Management Routes
            Route::prefix('doctors')->group(function () {
                Route::get('/', [\App\Http\Controllers\Mc\Panel\Doctor\DoctorController::class, 'index'])->middleware('medical_center.permission:mc.panel.doctors.index')->name('mc.panel.doctors.index');
                Route::get('/create', [\App\Http\Controllers\Mc\Panel\Doctor\DoctorController::class, 'create'])->middleware('medical_center.permission:mc.panel.doctors.create')->name('mc.panel.doctors.create');
                Route::get('/edit/{id}', [\App\Http\Controllers\Mc\Panel\Doctor\DoctorController::class, 'edit'])->middleware('medical_center.permission:mc.panel.doctors.edit')->name('mc.panel.doctors.edit');
            });

            // Specialty Management Routes
            Route::prefix('specialties')->group(function () {
                Route::get('/', [\App\Http\Controllers\Mc\Panel\Specialty\SpecialtyController::class, 'index'])->middleware('medical_center.permission:mc.panel.specialties.index')->name('mc.panel.specialties.index');
                Route::get('/create', [\App\Http\Controllers\Mc\Panel\Specialty\SpecialtyController::class, 'create'])->middleware('medical_center.permission:mc.panel.specialties.create')->name('mc.panel.specialties.create');
                Route::get('/edit/{id}', [\App\Http\Controllers\Mc\Panel\Specialty\SpecialtyController::class, 'edit'])->middleware('medical_center.permission:mc.panel.specialties.edit')->name('mc.panel.specialties.edit');
            });

            // Service Management Routes
            Route::prefix('services')->group(function () {
                Route::get('/', [\App\Http\Controllers\Mc\Panel\Service\ServiceController::class, 'index'])->middleware('medical_center.permission:mc.panel.services.index')->name('mc.panel.services.index');
                Route::get('/create', [\App\Http\Controllers\Mc\Panel\Service\ServiceController::class, 'create'])->middleware('medical_center.permission:mc.panel.services.create')->name('mc.panel.services.create');
                Route::get('/edit/{id}', [\App\Http\Controllers\Mc\Panel\Service\ServiceController::class, 'edit'])->middleware('medical_center.permission:mc.panel.services.edit')->name('mc.panel.services.edit');
            });

            // Insurance Management Routes
            Route::prefix('insurances')->group(function () {
                Route::get('/', [\App\Http\Controllers\Mc\Panel\Insurance\InsuranceController::class, 'index'])->middleware('medical_center.permission:mc.panel.insurances.index')->name('mc.panel.insurances.index');
                Route::get('/create', [\App\Http\Controllers\Mc\Panel\Insurance\InsuranceController::class, 'create'])->middleware('medical_center.permission:mc.panel.insurances.create')->name('mc.panel.insurances.create');
                Route::get('/edit/{id}', [\App\Http\Controllers\Mc\Panel\Insurance\InsuranceController::class, 'edit'])->middleware('medical_center.permission:mc.panel.insurances.edit')->name('mc.panel.insurances.edit');
            });

            // Medical Center Profile Edit Routes
            Route::prefix('profile')->group(function () {
                Route::get('/edit', [\App\Http\Controllers\Mc\Panel\Profile\MedicalCenterProfileController::class, 'edit'])->middleware('medical_center.permission:mc.panel.profile.edit')->name('mc.panel.profile.edit');
            });

            // Medical Center Permissions Routes
            Route::prefix('medical-center-permissions')->group(function () {
                Route::get('/', [\App\Http\Controllers\Mc\Panel\MedicalCenterPermission\MedicalCenterPermissionController::class, 'index'])->name('mc.panel.medical-center-permissions.index');
                Route::post('/update', [\App\Http\Controllers\Mc\Panel\MedicalCenterPermission\MedicalCenterPermissionController::class, 'update'])->name('mc.panel.medical-center-permissions.update');
            });
        });
    });

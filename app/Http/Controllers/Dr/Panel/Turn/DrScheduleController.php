<?php
namespace App\Http\Controllers\Dr\Panel\Turn;

use Carbon\Carbon;
use App\Models\Dr\SubUser;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;
use App\Models\Dr\Appointment;
use Illuminate\Support\Facades\Auth;

class DrScheduleController
{
    /**
     * دریافت دکتر مرتبط با کاربر لاگین شده (پزشک یا منشی)
     */
    public function getAuthenticatedDoctor()
    {
        // بررسی پزشک لاگین شده
        $doctor = Auth::guard('doctor')->user();

        // اگر پزشک لاگین نکرده بود، بررسی دکتر مرتبط با منشی
        if (!$doctor) {
            $secretary = Auth::guard('secretary')->user();
            if ($secretary && $secretary->doctor) {
                $doctor = $secretary->doctor;
            }
        }

        // اگر دکتر مرتبط وجود نداشت، بازگشت مقدار null
        return $doctor;
    }

    /**
     * نمایش نوبت‌های امروز
     */
    public function index(Request $request)
    {
        // Get the authenticated doctor
        $doctor = $this->getAuthenticatedDoctor();

        // Check if the doctor is authenticated
        if (!$doctor) {
            abort(403, 'شما به این بخش دسترسی ندارید.');
        }

        // Get inactive clinics associated with the doctor
        $clinics = $doctor->clinics()->where('is_active', 0)->get();

        // Get today's date
        $now = Carbon::now()->format('Y-m-d');
        $selectedClinicId = $request->query('selectedClinicId');
        $filterType = $request->input('type');

        // Get today's appointments for the doctor
        $appointments = Appointment::with(['doctor', 'patient', 'insurance', 'clinic'])
            ->where('doctor_id', $doctor->id)
            ->where('appointment_date', $now);

        // اعمال فیلتر selectedClinicId
        if ($selectedClinicId === 'default') {
            // اگر selectedClinicId برابر با 'default' باشد، clinic_id باید NULL یا خالی باشد
            $appointments->whereNull('clinic_id');
        } elseif ($selectedClinicId) {
            // اگر selectedClinicId مقدار داشت، clinic_id باید با آن مطابقت داشته باشد
            $appointments->where('clinic_id', $selectedClinicId);
        }

        // اعمال فیلتر نوع قرارملاقات (appointment_type)
        if ($filterType) {
            if ($filterType === "in_person") {
                // اگر نوع قرارملاقات "in_person" باشد، clinic_id باید با selectedClinicId برابر باشد
                $appointments->where('clinic_id', $selectedClinicId);
            } elseif ($filterType === "online") {
                // اگر نوع قرارملاقات "online" باشد، clinic_id باید NULL باشد
                $appointments->whereNull('clinic_id');
            }
        }

        // دریافت نتایج
        $appointments = $appointments->get();

        // If the request is AJAX, return JSON response
        if ($request->ajax()) {
            return response()->json([
                'appointments' => $appointments,
                'clinics' => $clinics,
            ]);
        }

        // Return the view with appointments and clinics data
        return view("dr.panel.turn.schedule.appointments", compact('appointments', 'clinics'));
    }


    /**
     * نمایش صفحه نوبت‌های من
     */
    public function myAppointments()
    {
        $doctor = $this->getAuthenticatedDoctor();

        if (!$doctor) {
            abort(403, 'شما به این بخش دسترسی ندارید.');
        }

        // دریافت لیست کاربران زیرمجموعه پزشک
        $subUserIds = SubUser::where('doctor_id', $doctor->id)
            ->pluck('user_id')
            ->toArray();

        // دریافت نوبت‌های کاربران زیرمجموعه
        $appointments = Appointment::with(['patient'])
            ->whereIn('patient_id', $subUserIds)
            ->orderBy('appointment_date', 'desc')
            ->paginate(10); // صفحه‌بندی

        return view("dr.panel.turn.schedule.my-appointments", compact('appointments'));
    }

    public function filterAppointments(Request $request)
    {
        $doctor = $this->getAuthenticatedDoctor();
        if (!$doctor) {
            return response()->json(['error' => 'دسترسی غیرمجاز!'], 403);
        }

        $selectedClinicId = $request->query('selectedClinicId');
        $filterType = $request->input('type');
        $selectedDate = $request->input('date'); // دریافت تاریخ جلالی از فرانت‌اند

        // تبدیل تاریخ جلالی به میلادی
        try {
            $gregorianDate = Jalalian::fromFormat('Y/m/d', $selectedDate)->toCarbon()->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['error' => 'فرمت تاریخ نامعتبر است.'], 400);
        }

        // ایجاد کوئری پایه
        $query = Appointment::with(['patient', 'clinic'])
            ->where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $gregorianDate);

        // اعمال فیلتر selectedClinicId
        if ($selectedClinicId === 'default') {
            // اگر selectedClinicId برابر با 'default' باشد، clinic_id باید NULL یا خالی باشد
            $query->whereNull('clinic_id');
        } elseif ($selectedClinicId) {
            // اگر selectedClinicId مقدار داشت، clinic_id باید با آن مطابقت داشته باشد
            $query->where('clinic_id', $selectedClinicId);
        }

        // اعمال فیلتر نوع قرارملاقات (appointment_type)
        if ($filterType) {
            if ($filterType === "in_person") {
                // اگر نوع قرارملاقات "in_person" باشد، clinic_id باید با selectedClinicId برابر باشد
                $query->where('clinic_id', $selectedClinicId);
            }
        }

        // دریافت نتایج
        $appointments = $query->get();

        return response()->json(['appointments' => $appointments]);
    }




    /**
     * دریافت نوبت‌ها بر اساس تاریخ انتخاب شده
     */
    public function showByDateAppointments(Request $request)
    {
        // دریافت پزشک مرتبط
        $doctor = $this->getAuthenticatedDoctor();
        // اگر دکتر یافت نشد، دسترسی غیرمجاز است
        if (!$doctor) {
            abort(403, 'شما به این بخش دسترسی ندارید.');
        }

        // دریافت تاریخ انتخاب شده یا پیش‌فرض به امروز
        $selectedDate = $request->input('date', Jalalian::now()->format('Y/m/d'));
        $selectedClinicId = $request->query('selectedClinicId');
        $filterType = $request->input('type'); // دریافت نوع فیلتر از درخواست

        // بررسی فرمت تاریخ ورودی
        try {
            $gregorianDate = Jalalian::fromFormat('Y/m/d', $selectedDate)->toCarbon()->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'فرمت تاریخ وارد شده نامعتبر است.',
            ], 400);
        }

        // دریافت نوبت‌ها بر اساس تاریخ انتخاب شده
        $appointments = Appointment::with(['doctor', 'patient', 'insurance', 'clinic'])
            ->where('doctor_id', $doctor->id)
            ->where('appointment_date', '=', $gregorianDate);

        // اعمال فیلتر selectedClinicId
        if ($selectedClinicId === 'default') {
            // اگر selectedClinicId برابر با 'default' باشد، clinic_id باید NULL یا خالی باشد
            $appointments->whereNull('clinic_id');
        } elseif ($selectedClinicId) {
            // اگر selectedClinicId مقدار داشت، clinic_id باید با آن مطابقت داشته باشد
            $appointments->where('clinic_id', $selectedClinicId);
        }

        // اعمال فیلتر نوع قرارملاقات (appointment_type)
        if ($filterType) {
            if ($filterType === "in_person") {
                // اگر نوع قرارملاقات "in_person" باشد، clinic_id باید با selectedClinicId برابر باشد
                $appointments->where('clinic_id', $selectedClinicId);
            } elseif ($filterType === "online") {
                // اگر نوع قرارملاقات "online" باشد، باید فقط نوبت‌های آنلاین را دریافت کنیم
                $appointments->where('appointment_type', 'online');
            }
            // می‌توانید فیلترهای بیشتری اضافه کنید
        }
        $appointments = $appointments->get();

        return response()->json([
            'appointments' => $appointments,
        ]);
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}

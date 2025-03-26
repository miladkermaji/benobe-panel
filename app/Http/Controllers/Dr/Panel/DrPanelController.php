<?php

namespace App\Http\Controllers\Dr\Panel;

use Carbon\Carbon;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendSmsNotificationJob;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Dr\Controller;

class DrPanelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $today    = Carbon::today();
        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id; // گرفتن ID پزشک لاگین‌شده
        // تعداد بیماران امروز فقط برای این پزشک
        $totalPatientsToday = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->count();
        // بیماران ویزیت شده فقط برای این پزشک
        $visitedPatients = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->where('attendance_status', 'attended')
            ->count();
        // بیماران باقی‌مانده فقط برای این پزشک
        $remainingPatients = $totalPatientsToday - $visitedPatients;
        return view("dr.panel.index", compact('totalPatientsToday', 'visitedPatients', 'remainingPatients'));
    }
    public function getAppointmentsByDate(Request $request)
    {

        $selectedClinicId = $request->selectedClinicId;
        $jalaliDate       = $request->input('date'); // دریافت تاریخ جلالی از فرانت‌اند
        // **اصلاح فرمت تاریخ ورودی**
        if (strpos($jalaliDate, '-') !== false) {
            // اگر تاریخ با `-` جدا شده بود، آن را به `/` تبدیل کنیم
            $jalaliDate = str_replace('-', '/', $jalaliDate);
        }
        // بررسی صحت فرمت تاریخ ورودی (1403/11/24)
        if (! preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $jalaliDate)) {
            return response()->json(['error' => 'فرمت تاریخ جلالی نادرست است.'], 400);
        }
        // **تبدیل تاریخ جلالی به میلادی**
        try {
            $gregorianDate = Jalalian::fromFormat('Y/m/d', $jalaliDate)->toCarbon()->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['error' => 'خطا در تبدیل تاریخ جلالی به میلادی.'], 500);
        }
        // لاگ‌گیری برای بررسی تبدیل صحیح
        $doctorId = Auth::guard('doctor')->user()->id; // دریافت ID پزشک لاگین‌شده
        // گرفتن نوبت‌های پزشک جاری در تاریخ تبدیل‌شده به میلادی
        // Fetch appointments
        $query = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $gregorianDate)
            ->with(['patient', 'insurance']);
        // اعمال فیلتر selectedClinicId
        if ($selectedClinicId === 'default') {
            // اگر selectedClinicId برابر با 'default' باشد، clinic_id باید NULL یا خالی باشد
            $query->whereNull('clinic_id');
        } elseif ($selectedClinicId) {
            // اگر selectedClinicId مقدار داشت، clinic_id باید با آن مطابقت داشته باشد
            $query->where('clinic_id', $selectedClinicId);
        }

        $appointments = $query->get();
        return response()->json([
            'appointments' => $appointments,
        ]);
    }
    public function searchPatients(Request $request)
    {
        $query            = $request->query('query'); // مقدار جستجو شده
        $date             = $request->query('date');  // تاریخ انتخاب شده
        $selectedClinicId = $request->query('selectedClinicId');
        // **اصلاح فرمت تاریخ ورودی**
        if (strpos($date, '-') !== false) {
            // اگر تاریخ با `-` جدا شده بود، آن را به `/` تبدیل کنیم
            $date = str_replace('-', '/', $date);
        }

        // **تبدیل تاریخ جلالی به میلادی**
        try {
            $gregorianDate = Jalalian::fromFormat('Y/m/d', $date)->toCarbon()->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['error' => 'خطا در تبدیل تاریخ جلالی به میلادی.'], 500);
        }
        // ایجاد کوئری پایه
        $appointmentsQuery = Appointment::with('patient', 'insurance')
            ->whereDate('appointment_date', $gregorianDate)
            ->whereHas('patient', function ($q) use ($query) {
                $q->where('first_name', 'like', "%$query%")
                    ->orWhere('last_name', 'like', "%$query%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$query%"])
                    ->orWhere('mobile', 'like', "%$query%")
                    ->orWhere('national_code', 'like', "%$query%");
            });

        // اعمال فیلتر selectedClinicId
        if ($selectedClinicId === 'default') {
            // اگر selectedClinicId برابر با 'default' باشد، clinic_id باید NULL یا خالی باشد
            $appointmentsQuery->whereNull('clinic_id');
        } elseif ($selectedClinicId) {
            // اگر selectedClinicId مقدار داشت، clinic_id باید با آن مطابقت داشته باشد
            $appointmentsQuery->where('clinic_id', $selectedClinicId);
        }

        // اجرای کوئری و دریافت نتایج
        $patients = $appointmentsQuery->get();
        return response()->json(['patients' => $patients]);
    }

    public function updateAppointmentDate(Request $request, $id)
    {
        $request->validate([
            'new_date' => 'required|date_format:Y-m-d',
        ]);

        $appointment = Appointment::findOrFail($id);

        // چک کردن وضعیت نوبت
        if ($appointment->status === 'attended' || $appointment->status === 'cancelled') {
            return response()->json(['error' => 'نمی‌توانید نوبت ویزیت‌شده یا لغو شده را جابجا کنید.'], 400);
        }

        $newDate = Carbon::parse($request->new_date);
        if ($newDate->lt(Carbon::today())) {
            return response()->json(['error' => 'امکان جابجایی به تاریخ گذشته وجود ندارد.'], 400);
        }

        $oldDate = $appointment->appointment_date; // تاریخ قبلی
        $appointment->appointment_date = $newDate;
        $appointment->save();

        // تبدیل تاریخ‌ها به فرمت شمسی
        $oldDateJalali = Jalalian::fromDateTime($oldDate)->format('Y/m/d');
        $newDateJalali = Jalalian::fromDateTime($newDate)->format('Y/m/d');

        // ارسال پیامک جابجایی نوبت
        if ($appointment->patient && $appointment->patient->mobile) {
            $message = "کاربر گرامی، نوبت شما از تاریخ {$oldDateJalali} به {$newDateJalali} تغییر یافت.";
            SendSmsNotificationJob::dispatch(
                $message,
                [$appointment->patient->mobile],
                null, // بدون قالب
                [] // بدون پارامتر اضافی
            )->delay(now()->addSeconds(5));
        }

        return response()->json(['message' => 'نوبت با موفقیت جابجا شد و پیامک در صف قرار گرفت.']);
    }
    public function filterAppointments(Request $request)
    {
        $status           = $request->query('status');
        $attendanceStatus = $request->query('attendance_status');
        $selectedClinicId = $request->input('selectedClinicId');
        $query            = Appointment::withTrashed(); // اضافه کردن withTrashed برای نمایش نوبت‌های لغو شده
        if ($selectedClinicId && $selectedClinicId !== 'default') {
            $query->where('clinic_id', $selectedClinicId);
        }
        // فیلتر بر اساس `status`
        if (! empty($status)) {
            $query->where('status', $status);
        }
        // فیلتر بر اساس `attendance_status`
        if (! empty($attendanceStatus)) {
            $query->where('attendance_status', $attendanceStatus);
        }
        // دریافت نوبت‌ها به همراه اطلاعات بیمار، پزشک و کلینیک
        $appointments = $query->with(['patient', 'doctor', 'clinic', 'insurance'])->get();
        return response()->json([
            'success'      => true,
            'appointments' => $appointments,
        ]);
    }
    public function endVisit(Request $request, $id)
    {
        try {
            // پیدا کردن نوبت با شناسه
            $appointment = Appointment::findOrFail($id);

            // اعتبارسنجی درخواست
            $request->validate([
                'description' => 'nullable|string|max:1000', // توضیحات اختیاری
            ]);

            // به‌روزرسانی توضیحات و وضعیت نوبت
            $appointment->description = $request->input('description');
            $appointment->status = 'attended'; // تغییر وضعیت به attended
            $appointment->attendance_status = 'attended'; // هماهنگ کردن attendance_status
            $appointment->save();

            // برگرداندن پاسخ موفقیت‌آمیز
            return response()->json([
                'success' => true,
                'message' => 'ویزیت با موفقیت ثبت شد.',
                'appointment' => $appointment // اطلاعات نوبت به‌روز شده
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ثبت ویزیت: ' . $e->getMessage()
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Dr\Panel\Turn\Schedule\ScheduleSetting;

use App\Http\Controllers\Dr\Controller;
use App\Models\Vacation;
use App\Traits\HasSelectedClinic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Morilog\Jalali\CalendarUtils;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\DB;

class VacationController extends Controller
{
    use HasSelectedClinic;

    public function index(Request $request)
    {
        // Get doctor_id based on guard
        $doctorId = null;
        if (Auth::guard('medical_center')->check()) {
            // For medical_center guard, get the selected doctor
            $medicalCenter = Auth::guard('medical_center')->user();
            $selectedDoctor = DB::table('medical_center_selected_doctors')
                ->where('medical_center_id', $medicalCenter->id)
                ->first();
            $doctorId = $selectedDoctor ? $selectedDoctor->doctor_id : null;
        } else {
            $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
        }

        if (!$doctorId) {
            return response()->json(['success' => false, 'message' => 'پزشک انتخاب نشده است.'], 400);
        }

        $selectedClinicId = $this->getSelectedMedicalCenterId();

        // Get doctor's medical centers (clinics)
        $clinics = \App\Models\MedicalCenter::whereHas('doctors', function ($query) use ($doctorId) {
            $query->where('doctor_id', $doctorId);
        })->where('type', 'policlinic')->get();

        try {
            $year = $request->input('year', Jalalian::now()->getYear());
            $month = str_pad($request->input('month', Jalalian::now()->getMonth()), 2, '0', STR_PAD_LEFT);

            $jalaliStartDate = Jalalian::fromFormat('Y/m/d', "{$year}/{$month}/01");
            $jalaliEndDate = $jalaliStartDate->addMonths(1)->subDays(1);

            $query = Vacation::where('doctor_id', $doctorId)
                ->whereBetween('date', [$jalaliStartDate->toCarbon()->format('Y-m-d'), $jalaliEndDate->toCarbon()->format('Y-m-d')]);

            if ($selectedClinicId && $selectedClinicId !== 'default') {
                $query->where('medical_center_id', $selectedClinicId);
            } else {
                $query->whereNull('medical_center_id');
            }

            $vacations = $query->get();

            if ($request->ajax()) {
                return response()->json(['success' => true, 'vacations' => $vacations, 'year' => $year, 'month' => $month]);
            }

            // Use different view based on guard
            $viewPath = Auth::guard('medical_center')->check()
                ? "mc.panel.turn.schedule.scheduleSetting.vacation"
                : "dr.panel.turn.schedule.scheduleSetting.vacation";

            return view($viewPath, compact('vacations', 'clinics'));
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'خطا در پردازش تاریخ‌ها: ' . $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        // Get doctor_id based on guard
        $doctorId = null;
        if (Auth::guard('medical_center')->check()) {
            // For medical_center guard, get the selected doctor
            $medicalCenter = Auth::guard('medical_center')->user();
            $selectedDoctor = DB::table('medical_center_selected_doctors')
                ->where('medical_center_id', $medicalCenter->id)
                ->first();
            $doctorId = $selectedDoctor ? $selectedDoctor->doctor_id : null;
        } else {
            $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
        }

        if (!$doctorId) {
            return response()->json(['success' => false, 'message' => 'پزشک انتخاب نشده است.'], 400);
        }

        $vacation = Vacation::where('id', $id)
            ->where('doctor_id', $doctorId)
            ->firstOrFail();

        return response()->json(['success' => true, 'vacation' => $vacation]);
    }

    public function store(Request $request)
    {
        // Get doctor_id based on guard
        $doctorId = null;
        if (Auth::guard('medical_center')->check()) {
            // For medical_center guard, get the selected doctor
            $medicalCenter = Auth::guard('medical_center')->user();
            $selectedDoctor = DB::table('medical_center_selected_doctors')
                ->where('medical_center_id', $medicalCenter->id)
                ->first();
            $doctorId = $selectedDoctor ? $selectedDoctor->doctor_id : null;
        } else {
            $doctorId = Auth::guard('doctor')->id() ?? Auth::guard('secretary')->user()->doctor_id;
        }

        if (!$doctorId) {
            return response()->json(['success' => false, 'message' => 'پزشک انتخاب نشده است.'], 400);
        }

        $messages = [
            'date.required' => 'لطفاً تاریخ را وارد کنید.',
            'date.date' => 'تاریخ واردشده معتبر نیست.',
            'start_time.date_format' => 'فرمت ساعت شروع باید به صورت HH:MM باشد (مثال: 14:30).',
            'end_time.date_format' => 'فرمت ساعت پایان باید به صورت HH:MM باشد (مثال: 16:30).',
            'end_time.after' => 'ساعت پایان باید بعد از ساعت شروع باشد.',
        ];

        $validatedData = $request->validate([
            'date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'is_full_day' => 'nullable|boolean',
            'selectedClinicId' => 'nullable|string',
        ], $messages);

        $gregorianDate = CalendarUtils::createDatetimeFromFormat('Y/m/d', $request->date)->format('Y-m-d');
        $validatedData['date'] = $gregorianDate;

        if ($request->is_full_day) {
            $validatedData['start_time'] = '00:00';
            $validatedData['end_time'] = '23:00';
        }

        // بررسی تداخل زمانی
        $exists = Vacation::where('doctor_id', $doctorId)
            ->where('date', $validatedData['date'])
            ->when(
                $this->getSelectedMedicalCenterId() && $this->getSelectedMedicalCenterId() !== 'default',
                fn ($query) => $query->where('medical_center_id', $this->getSelectedMedicalCenterId())
            )
            ->where(function ($query) use ($validatedData) {
                $query->whereBetween('start_time', [$validatedData['start_time'], $validatedData['end_time']])
                    ->orWhereBetween('end_time', [$validatedData['start_time'], $validatedData['end_time']])
                    ->orWhere(function ($query) use ($validatedData) {
                        $query->where('start_time', '<=', $validatedData['start_time'])
                            ->where('end_time', '>=', $validatedData['end_time']);
                    });
            })
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'این بازه زمانی با یک مرخصی دیگر تداخل دارد.'], 422);
        }

        Vacation::create([
            'doctor_id' => $doctorId,
            'medical_center_id' => $this->getSelectedMedicalCenterId() && $this->getSelectedMedicalCenterId() !== 'default' ? $this->getSelectedMedicalCenterId() : null,
            'date' => $validatedData['date'],
            'start_time' => $validatedData['start_time'] ?? null,
            'end_time' => $validatedData['end_time'] ?? null,
            'is_full_day' => $request->is_full_day ? 1 : 0,
        ]);

        return response()->json(['success' => true, 'message' => 'مرخصی با موفقیت ثبت شد.']);
    }

    public function update(Request $request, $id)
    {
        // Get doctor_id based on guard
        $doctorId = null;
        if (Auth::guard('medical_center')->check()) {
            // For medical_center guard, get the selected doctor
            $medicalCenter = Auth::guard('medical_center')->user();
            $selectedDoctor = DB::table('medical_center_selected_doctors')
                ->where('medical_center_id', $medicalCenter->id)
                ->first();
            $doctorId = $selectedDoctor ? $selectedDoctor->doctor_id : null;
        } else {
            $doctorId = Auth::guard('doctor')->id() ?? Auth::guard('secretary')->user()->doctor_id;
        }

        if (!$doctorId) {
            return response()->json(['success' => false, 'message' => 'پزشک انتخاب نشده است.'], 400);
        }

        $messages = [
            'date.required' => 'لطفاً تاریخ را وارد کنید.',
            'date.date' => 'تاریخ واردشده معتبر نیست.',
            'start_time.date_format' => 'فرمت ساعت شروع باید به صورت HH:MM باشد (مثال: 14:30).',
            'end_time.date_format' => 'فرمت ساعت پایان باید به صورت HH:MM باشد (مثال: 16:30).',
            'end_time.after' => 'ساعت پایان باید بعد از ساعت شروع باشد.',
        ];

        $validatedData = $request->validate([
            'date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'is_full_day' => 'nullable|boolean',
            'selectedClinicId' => 'nullable|string',
        ], $messages);

        $vacation = Vacation::where('id', $id)
            ->where('doctor_id', $doctorId)
            ->when(
                $this->getSelectedMedicalCenterId() && $this->getSelectedMedicalCenterId() !== 'default',
                fn ($query) => $query->where('medical_center_id', $this->getSelectedMedicalCenterId())
            )
            ->firstOrFail();

        $gregorianDate = CalendarUtils::createDatetimeFromFormat('Y/m/d', $request->date)->format('Y-m-d');
        $validatedData['date'] = $gregorianDate;

        if ($request->is_full_day) {
            $validatedData['start_time'] = '00:00';
            $validatedData['end_time'] = '23:00';
        }

        // بررسی تداخل زمانی
        $exists = Vacation::where('doctor_id', $doctorId)
            ->where('medical_center_id', $this->getSelectedMedicalCenterId() !== 'default' ? $this->getSelectedMedicalCenterId() : null)
            ->where('date', $validatedData['date'])
            ->where('id', '!=', $vacation->id)
            ->where(function ($query) use ($validatedData) {
                $query->whereBetween('start_time', [$validatedData['start_time'], $validatedData['end_time']])
                    ->orWhereBetween('end_time', [$validatedData['start_time'], $validatedData['end_time']])
                    ->orWhere(function ($query) use ($validatedData) {
                        $query->where('start_time', '<=', $validatedData['start_time'])
                            ->where('end_time', '>=', $validatedData['end_time']);
                    });
            })
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'این بازه زمانی با یک مرخصی دیگر تداخل دارد.'], 422);
        }

        $vacation->update([
            'date' => $validatedData['date'],
            'start_time' => $validatedData['start_time'] ?? null,
            'end_time' => $validatedData['end_time'] ?? null,
            'is_full_day' => $request->is_full_day ? 1 : 0,
        ]);

        return response()->json(['success' => true, 'message' => 'مرخصی با موفقیت به‌روزرسانی شد.']);
    }

    public function destroy(Request $request, $id)
    {
        // Get doctor_id based on guard
        $doctorId = null;
        if (Auth::guard('medical_center')->check()) {
            // For medical_center guard, get the selected doctor
            $medicalCenter = Auth::guard('medical_center')->user();
            $selectedDoctor = DB::table('medical_center_selected_doctors')
                ->where('medical_center_id', $medicalCenter->id)
                ->first();
            $doctorId = $selectedDoctor ? $selectedDoctor->doctor_id : null;
        } else {
            $doctorId = Auth::guard('doctor')->id() ?? Auth::guard('secretary')->user()->doctor_id;
        }

        if (!$doctorId) {
            return response()->json(['success' => false, 'message' => 'پزشک انتخاب نشده است.'], 400);
        }

        $selectedClinicId = $this->getSelectedMedicalCenterId();

        $vacation = Vacation::where('id', $id)
            ->where('doctor_id', $doctorId)
            ->when(
                $selectedClinicId && $selectedClinicId !== 'default',
                fn ($query) => $query->where('medical_center_id', $selectedClinicId)
            )
            ->first();

        if (!$vacation) {
            return response()->json(['success' => false, 'message' => 'مرخصی مورد نظر یافت نشد!'], 404);
        }

        $vacation->delete();

        return response()->json(['success' => true, 'message' => 'مرخصی با موفقیت حذف شد!']);
    }
}

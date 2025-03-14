<?php
namespace App\Http\Controllers\Dr\Panel\Turn\Schedule\ScheduleSetting;

use App\Http\Controllers\Dr\Controller;
use App\Models\Vacation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Morilog\Jalali\CalendarUtils;
use Morilog\Jalali\Jalalian;

class VacationController extends Controller
{

    public function index(Request $request)
    {
        $doctorId         = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
        $selectedClinicId = $request->input('selectedClinicId');

        try {
            $year  = $request->input('year', Jalalian::now()->getYear());
            $month = str_pad($request->input('month', Jalalian::now()->getMonth()), 2, '0', STR_PAD_LEFT);

            $jalaliStartDate = Jalalian::fromFormat('Y/m/d', "{$year}/{$month}/01");
            $jalaliEndDate   = $jalaliStartDate->addMonths(1)->subDays(1);

            $query = Vacation::where('doctor_id', $doctorId)
                ->whereBetween('date', [$jalaliStartDate->toCarbon()->format('Y-m-d'), $jalaliEndDate->toCarbon()->format('Y-m-d')]);

            if ($selectedClinicId && $selectedClinicId !== 'default') {
                $query->where('clinic_id', $selectedClinicId);
            } else {
                $query->whereNull('clinic_id');
            }

            $vacations = $query->get();

            if ($request->ajax()) {
                return response()->json(['success' => true, 'vacations' => $vacations, 'year' => $year, 'month' => $month]);
            }

            return view("dr.panel.turn.schedule.scheduleSetting.vacation", compact('vacations'));
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'خطا در پردازش تاریخ‌ها: ' . $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $vacation = Vacation::where('id', $id)
            ->where('doctor_id', Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id)
            ->firstOrFail();

        return response()->json(['success' => true, 'vacation' => $vacation]);
    }

    public function store(Request $request)
    {
        $doctorId = Auth::guard('doctor')->id() ?? Auth::guard('secretary')->user()->doctor_id;

        $validatedData = $request->validate([
            'date'             => 'required|date',
            'start_time'       => 'nullable|date_format:H:i',
            'end_time'         => 'nullable|date_format:H:i|after:start_time',
            'is_full_day'      => 'nullable|boolean',
            'selectedClinicId' => 'nullable|string',
        ]);

        $gregorianDate         = CalendarUtils::createDatetimeFromFormat('Y/m/d', $request->date)->format('Y-m-d');
        $validatedData['date'] = $gregorianDate;

        // تنظیم ساعت در صورت انتخاب تمام روز
        if ($request->is_full_day) {
            $validatedData['start_time'] = '00:00';
            $validatedData['end_time']   = '23:00';
        }

        // بررسی مرخصی تکراری بر اساس کلینیک
        $exists = Vacation::where('doctor_id', $doctorId)
            ->where('date', $validatedData['date'])
            ->when(
                $request->selectedClinicId && $request->selectedClinicId !== 'default',
                fn($query) => $query->where('clinic_id', $request->selectedClinicId)
            )
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'این بازه زمانی مرخصی قبلاً ثبت شده است.'], 422);
        }

        // ذخیره مرخصی همراه با کلینیک
        Vacation::create([
            'doctor_id'   => $doctorId,
            'clinic_id'   => $request->selectedClinicId !== 'default' ? $request->selectedClinicId : null,
            'date'        => $validatedData['date'],
            'start_time'  => $validatedData['start_time'] ?? null,
            'end_time'    => $validatedData['end_time'] ?? null,
            'is_full_day' => $request->is_full_day ? 1 : 0,
        ]);

        return response()->json(['success' => true, 'message' => 'مرخصی با موفقیت ثبت شد.']);
    }

    public function update(Request $request, $id)
    {
        $doctorId = Auth::guard('doctor')->id() ?? Auth::guard('secretary')->user()->doctor_id;

        $validatedData = $request->validate([
            'date'             => 'required|date',
            'start_time'       => 'nullable|date_format:H:i',
            'end_time'         => 'nullable|date_format:H:i|after:start_time',
            'is_full_day'      => 'nullable|boolean',
            'selectedClinicId' => 'nullable|string',
        ]);

        // پیدا کردن مرخصی مرتبط با پزشک و کلینیک
        $vacation = Vacation::where('id', $id)
            ->where('doctor_id', $doctorId)
            ->when(
                $request->selectedClinicId && $request->selectedClinicId !== 'default',
                fn($query) => $query->where('clinic_id', $request->selectedClinicId)
            )
            ->firstOrFail();

        // تبدیل تاریخ شمسی به میلادی
        $gregorianDate         = CalendarUtils::createDatetimeFromFormat('Y/m/d', $request->date)->format('Y-m-d');
        $validatedData['date'] = $gregorianDate;

        // تنظیم ساعت‌ها در صورت مرخصی روز کامل
        if ($request->is_full_day) {
            $validatedData['start_time'] = '00:00';
            $validatedData['end_time']   = '23:00';
        }

        // بررسی تداخل مرخصی
        $exists = Vacation::where('doctor_id', $doctorId)
            ->where('clinic_id', $request->selectedClinicId !== 'default' ? $request->selectedClinicId : null)
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
            return response()->json(['success' => false, 'message' => 'این بازه زمانی مرخصی قبلاً ثبت شده است.'], 422);
        }

        // به‌روزرسانی مرخصی
        $vacation->update([
            'date'        => $validatedData['date'],
            'start_time'  => $validatedData['start_time'] ?? null,
            'end_time'    => $validatedData['end_time'] ?? null,
            'is_full_day' => $request->is_full_day ? 1 : 0,
        ]);

        return response()->json(['success' => true, 'message' => 'مرخصی با موفقیت به‌روزرسانی شد.']);
    }

    public function destroy(Request $request, $id)
    {
        $doctorId = Auth::guard('doctor')->id() ?? Auth::guard('secretary')->user()->doctor_id;

        // دریافت selectedClinicId از درخواست
        $selectedClinicId = $request->input('selectedClinicId');

        // جستجو و حذف مرخصی با شرط کلینیک و پزشک
        $vacation = Vacation::where('id', $id)
            ->where('doctor_id', $doctorId)
            ->when(
                $selectedClinicId && $selectedClinicId !== 'default',
                fn($query) => $query->where('clinic_id', $selectedClinicId)
            )
            ->first();

        if (! $vacation) {
            return response()->json(['success' => false, 'message' => 'مرخصی مورد نظر یافت نشد!'], 404);
        }

        $vacation->delete();

        return response()->json(['success' => true, 'message' => 'مرخصی با موفقیت حذف شد!']);
    }

}

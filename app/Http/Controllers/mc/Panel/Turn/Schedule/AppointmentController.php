<?php

namespace App\Http\Controllers\Mc\Panel\Turn\Schedule;

use App\Http\Controllers\Mc\Controller;
use App\Models\Appointment;
use App\Models\AppointmentPattern;
// use Hekmatinasser\Verta\Verta; // حذف Verta
use Carbon\Carbon;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;

class AppointmentController extends Controller
{
    public function getAppointmentsForDay(Request $request)
    {
        $day = $request->input('day');
        if (preg_match('/(\d{4})-(\d{2})-(\d{2})/', $day)) {
            $gregorianDate = $day;
        } else {
            preg_match('/(\d+)\s(\S+)\s(\d+)/', $day, $matches);
            if (! isset($matches[1], $matches[2], $matches[3])) {
                return response()->json(['error' => 'فرمت تاریخ نامعتبر است.'], 400);
            }
            $persianDay   = intval($matches[1]) < 10 ? '0' . intval($matches[1]) : intval($matches[1]);
            $persianMonth = $matches[2];
            $persianYear  = intval($matches[3]);
            $monthMap     = [
                "فروردین"  => 1,
                "اردیبهشت" => 2,
                "خرداد"    => 3,
                "تیر"      => 4,
                "مرداد"    => 5,
                "شهریور"   => 6,
                "مهر"      => 7,
                "آبان"     => 8,
                "آذر"      => 9,
                "دی"       => 10,
                "بهمن"     => 11,
                "اسفند"    => 12,
            ];
            if (! isset($monthMap[$persianMonth])) {
                return response()->json(['error' => 'نام ماه نامعتبر است.'], 400);
            }
            $persianMonthNumber = $monthMap[$persianMonth];
            // استفاده از Jalalian به جای Verta
            $gregorianDate = Jalalian::fromFormat('Y/m/d', "$persianYear/$persianMonthNumber/$persianDay")->toCarbon()->format('Y-m-d');
        }
        $manualAppointments = Appointment::where('appointment_date', $gregorianDate)->get();
        $AppointmentsList   = Appointment::where('doctor_id', auth('doctor')->user()->id)->get();
        return response()->json([
            'manualAppointments' => $manualAppointments,
            'gregorianDate'      => $gregorianDate,
            'AppointmentsList'   => $AppointmentsList,
        ]);
    }

    public function setActiveCalendar()
    {
        $appointmentsLists = Appointment::where('doctor_id', auth('doctor')->user()->id)->get();
        return response()->json(["appointmentsLists" => $appointmentsLists]);
    }

    public function storeManualAppointment(Request $request)
    {
        $dateParts    = explode(' ', $request->appointments_date);
        $persianDay   = intval($dateParts[0]) < 10 ? '0' . intval($dateParts[0]) : intval($dateParts[0]);
        $persianMonth = $dateParts[1];
        $persianYear  = intval($dateParts[2]);
        $monthMap     = [
            "فروردین"  => 1,
            "اردیبهشت" => 2,
            "خرداد"    => 3,
            "تیر"      => 4,
            "مرداد"    => 5,
            "شهریور"   => 6,
            "مهر"      => 7,
            "آبان"     => 8,
            "آذر"      => 9,
            "دی"       => 10,
            "بهمن"     => 11,
            "اسفند"    => 12,
        ];
        $persianMonthNumber = $monthMap[$persianMonth];
        // استفاده از Jalalian به جای Verta
        $gregorianDate = Jalalian::fromFormat('Y/m/d', "$persianYear/$persianMonthNumber/$persianDay")->toCarbon()->format('Y-m-d');

        $today = Carbon::now()->format('Y-m-d');
        if ($gregorianDate < $today) {
            return response()->json([
                'error' => 'امکان ثبت نوبت در گذشته وجود ندارد',
            ], 400);
        }
        $overlapping = Appointment::where('doctor_id', auth('doctor')->user()->id)
            ->where('appointment_date', $gregorianDate)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                    ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                    ->orWhere(function ($query) use ($request) {
                        $query->where('start_time', '<', $request->start_time)
                            ->where('end_time', '>', $request->end_time);
                    });
            })
            ->exists();
        if ($overlapping) {
            return response()->json([
                'error' => 'زمان‌ها تداخل دارند',
            ], 400);
        }
        if ($request->end_time <= $request->start_time) {
            return response()->json([
                'error' => 'زمان پایان باید بیشتر از زمان شروع باشد',
            ], 400);
        }
        $startTime = Carbon::createFromFormat('H:i', $request->start_time);
        $endTime   = Carbon::createFromFormat('H:i', $request->end_time);
        $duration  = $startTime->diffInMinutes($endTime);
        if ($duration < 30 && $request->max_appointments > 0) {
            return response()->json([
                'error' => 'باتوجه به تعداد پذیرش و این بازه زمانی کوتاه، این امکان وجود ندارد. لطفا زمان خود را مجدد بررسی کنید',
            ], 400);
        }
        if ($request->max_appointments > 0 && $duration / $request->max_appointments < 10) {
            return response()->json([
                'error' => 'زمان کافی برای پذیرش تعداد نفرات مورد نظر وجود ندارد',
            ], 400);
        }
        $appointment                   = new Appointment();
        $appointment->doctor_id        = auth('doctor')->user()->id;
        $appointment->appointment_date = $gregorianDate;
        $appointment->start_time       = $request->start_time;
        $appointment->end_time         = $request->end_time;
        $appointment->title            = $request->title;
        $appointment->max_appointments = $request->max_appointments;
        $appointment->include_holidays = $request->include_holidays;
        $appointment->save();
        return response()->json(['hasPattern' => true, 'appointment' => $appointment]);
    }

    public function convertToGregorian(Request $request)
    {
        $year  = $request->input('year');
        $month = $request->input('month');
        $day   = $request->input('day');
        // استفاده از Jalalian به جای Verta
        $gregorianDate = Jalalian::fromFormat('Y/m/d', "$year/$month/$day")->toCarbon()->format('Y-m-d');
        return response()->json(['gregorianDate' => $gregorianDate]);
    }

    public function destroyAppointment($id)
    {
        $appointment = Appointment::find($id);
        if ($appointment) {
            $today           = Carbon::now()->format('Y-m-d');
            $appointmentDate = $appointment->appointment_date; // فرض می‌کنیم appointment_date به فرمت میلادی است
            if ($appointmentDate < $today) {
                return response()->json(['error' => 'امکان حذف نوبت‌های گذشته وجود ندارد'], 400);
            }
            $appointment->delete();
            return response()->json(['success' => 'نوبت با موفقیت حذف شد']);
        } else {
            return response()->json(['error' => 'نوبت یافت نشد'], 404);
        }
    }

    public function toggleAutoPattern($id)
    {
        $pattern = AppointmentPattern::find($id);
        if ($pattern) {
            $pattern->is_active = ! $pattern->is_active;
            $pattern->save();
            return response()->json(['success' => true]);
        } else {
            return response()->json(['error' => 'الگو یافت نشد'], 404);
        }
    }

    public function searchAppointments(Request $request)
    {
        $request->validate([
            'date'             => 'nullable|string',
            'user_mobile'      => 'nullable|string',
            'user_name'        => 'nullable|string',
            'user_national_id' => 'nullable|string',
            'selectedClinicId' => 'nullable|string',
        ]);

        $date             = $request->input('date');
        $userMobile       = $request->input('user_mobile');
        $userName         = $request->input('user_name');
        $userNationalId   = $request->input('user_national_id');
        $selectedClinicId = $request->input('selectedClinicId');
        $doctorId         = auth('doctor')->user()->id;

        $gregorianDate = null;
        if ($date) {
            $dateParts = explode(' ', str_replace('  ', ' ', $date));
            if (count($dateParts) === 3) {
                list($day, $monthName, $year) = $dateParts;
                $monthNames                   = [
                    'فروردین'  => 1,
                    'اردیبهشت' => 2,
                    'خرداد'    => 3,
                    'تیر'      => 4,
                    'مرداد'    => 5,
                    'شهریور'   => 6,
                    'مهر'      => 7,
                    'آبان'     => 8,
                    'آذر'      => 9,
                    'دی'       => 10,
                    'بهمن'     => 11,
                    'اسفند'    => 12,
                ];
                if (isset($monthNames[$monthName])) {
                    $month = $monthNames[$monthName];
                    // استفاده از Jalalian به جای Verta
                    $gregorianDate = Jalalian::fromFormat('Y/m/d', "$year/$month/$day")->toCarbon()->format('Y-m-d');
                }
            }
        }

        $appointments = Appointment::with('patientable')
            ->where('doctor_id', $doctorId)
            ->when($selectedClinicId === 'default', function ($query) {
                return $query->whereNull('medical_center_id');
            })
            ->when($selectedClinicId && $selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                return $query->where('medical_center_id', $selectedClinicId);
            })
            ->when($gregorianDate, function ($query) use ($gregorianDate) {
                return $query->where('appointment_date', $gregorianDate);
            })
            ->when($userMobile, function ($query, $userMobile) {
                return $query->whereHas('patientable', function ($query) use ($userMobile) {
                    $query->where('mobile', 'like', '%' . $userMobile . '%');
                });
            })
            ->when($userName, function ($query, $userName) {
                return $query->whereHas('patientable', function ($query) use ($userName) {
                    $query->where(function ($query) use ($userName) {
                        $query->where('first_name', 'like', '%' . $userName . '%')
                            ->orWhere('last_name', 'like', '%' . $userName . '%');
                    });
                });
            })
            ->when($userNationalId, function ($query, $userNationalId) {
                return $query->whereHas('patientable', function ($query) use ($userNationalId) {
                    $query->where('national_code', 'like', '%' . $userNationalId . '%');
                });
            })
            ->paginate(10);

        return response()->json([
            'data'         => $appointments->items(),
            'current_page' => $appointments->currentPage(),
            'last_page'    => $appointments->lastPage(),
            'per_page'     => $appointments->perPage(),
            'total'        => $appointments->total(),
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status'           => 'required|in:scheduled,completed,cancelled',
            'selectedClinicId' => 'nullable|string',
        ]);

        $appointment = Appointment::findOrFail($id);

        $selectedClinicId = $request->input('selectedClinicId');
        if ($selectedClinicId && $selectedClinicId !== 'default') {
            if ($appointment->medical_center_id != $selectedClinicId) {
                return response()->json([
                    'error' => 'این نوبت متعلق به کلینیک انتخاب شده نیست.',
                ], 403);
            }
        }

        if ($request->input('status') === 'cancelled') {
            $appointment->status     = 'cancelled';
            $appointment->deleted_at = now();
        } else {
            $appointment->status     = $request->input('status');
            $appointment->deleted_at = null;
        }

        $appointment->save();

        return response()->json([
            'message'     => 'وضعیت نوبت با موفقیت به‌روز شد.',
            'appointment' => $appointment,
        ]);
    }
}

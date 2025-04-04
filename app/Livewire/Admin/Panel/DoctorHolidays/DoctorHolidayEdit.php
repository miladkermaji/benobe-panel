<?php

namespace App\Livewire\Admin\Panel\DoctorHolidays;

use Carbon\Carbon;
use App\Models\Clinic;
use App\Models\Doctor;
use Livewire\Component;
use Morilog\Jalali\Jalalian;
use App\Models\DoctorHoliday;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DoctorHolidayEdit extends Component
{
    public $doctorholiday;
    public $doctor_id;
    public $clinic_id;
    public $holiday_dates = [];
    public $status;
    public $doctors = [];
    public $clinics = [];
    public $formattedDates;
    public $selectedDate; // تاریخ URL رو اینجا نگه می‌داریم

    public function mount($id, $date = null)
    {
        $this->doctorholiday = DoctorHoliday::findOrFail($id);
        $this->doctor_id = $this->doctorholiday->doctor_id;
        $this->clinic_id = $this->doctorholiday->clinic_id;
        $this->status = $this->doctorholiday->status;
        $this->doctors = Doctor::all();
        $this->clinics = Clinic::all();

        $this->selectedDate = $date; // تاریخ URL رو ذخیره کن

        Log::info('Received date from URL:', [$date]);

        if ($date) {
            try {
                $jalaliDate = str_replace('-', '/', $date);
                $this->holiday_dates = [$jalaliDate];
                $this->formattedDates = Jalalian::fromFormat('Y/m/d', $jalaliDate)->format('%d %B %Y');
                Log::info('Holiday dates after setting URL date:', $this->holiday_dates);
            } catch (\Exception $e) {
                $this->holiday_dates = [];
                $this->formattedDates = 'خطا در پردازش تاریخ';
                Log::error('Date processing error', ['error' => $e->getMessage()]);
            }
        } else {
            $this->holiday_dates = array_map(function ($date) {
                return Jalalian::fromCarbon(Carbon::parse($date))->format('Y/m/d');
            }, $this->doctorholiday->holiday_dates);
            $this->formattedDates = !empty($this->holiday_dates)
                ? Jalalian::fromFormat('Y/m/d', $this->holiday_dates[0])->format('%d %B %Y')
                : '';
            Log::info('No date provided, using DB dates:', $this->holiday_dates);
        }

        Log::info('Final holiday dates:', $this->holiday_dates);
    }

    public function update()
    {
        $validator = Validator::make([
            'doctor_id' => $this->doctor_id,
            'clinic_id' => $this->clinic_id,
            'holiday_dates' => $this->holiday_dates,
            'status' => $this->status,
        ], [
            'doctor_id' => 'required|exists:doctors,id',
            'clinic_id' => 'nullable|exists:clinics,id',
            'holiday_dates' => 'required|array|min:1',
            'holiday_dates.*' => 'required|date',
            'status' => 'required|in:active,inactive',
        ], [
            'doctor_id.required' => 'لطفاً پزشک را انتخاب کنید.',
            'doctor_id.exists' => 'پزشک انتخاب‌شده معتبر نیست.',
            'clinic_id.exists' => 'کلینیک انتخاب‌شده معتبر نیست.',
            'holiday_dates.required' => 'لطفاً حداقل یک تاریخ تعطیل انتخاب کنید.',
            'holiday_dates.array' => 'تاریخ‌های تعطیل باید به‌صورت آرایه باشند.',
            'holiday_dates.*.required' => 'هر تاریخ تعطیل باید معتبر باشد.',
            'holiday_dates.*.date' => 'فرمت تاریخ تعطیل معتبر نیست.',
            'status.required' => 'وضعیت باید مشخص باشد.',
            'status.in' => 'وضعیت انتخاب‌شده معتبر نیست.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        // تاریخ جدید از اینپوت (شمسی)
        $newJalaliDate = $this->holiday_dates[0]; // مثلاً 1404/01/20
        $newGregorianDate = Jalalian::fromFormat('Y/m/d', $newJalaliDate)->toCarbon()->format('Y-m-d'); // مثلاً 2025-04-09

        // تاریخ URL از پراپرتی ذخیره‌شده
        $selectedGregorianDate = $this->selectedDate
            ? Jalalian::fromFormat('Y/m/d', str_replace('-', '/', $this->selectedDate))->toCarbon()->format('Y-m-d')
            : null; // مثلاً 2025-04-18

        // آرایه فعلی تعطیلات از دیتابیس
        $currentDates = $this->doctorholiday->holiday_dates;

        Log::info('Selected Gregorian Date:', [$selectedGregorianDate]);
        Log::info('Current Dates:', $currentDates);
        Log::info('New Gregorian Date:', [$newGregorianDate]);

        $updatedDates = $currentDates; // کپی آرایه فعلی
        if ($selectedGregorianDate && in_array($selectedGregorianDate, $currentDates)) {
            // جایگزینی تاریخ URL با تاریخ جدید
            $updatedDates = array_map(function ($date) use ($selectedGregorianDate, $newGregorianDate) {
                return $date === $selectedGregorianDate ? $newGregorianDate : $date;
            }, $currentDates);
        } else {
            $updatedDates = $currentDates; // بدون تغییر
            $this->dispatch('show-alert', type: 'warning', message: 'تاریخ انتخاب‌شده در لیست تعطیلات وجود ندارد.');
            return;
        }

        // آپدیت دیتابیس
        $this->doctorholiday->update([
            'doctor_id' => $this->doctor_id,
            'clinic_id' => $this->clinic_id,
            'holiday_dates' => $updatedDates,
            'status' => $this->status,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'تعطیلات پزشک با موفقیت به‌روزرسانی شد!');
        return redirect()->route('admin.panel.doctor-holidays.index');
    }

    public function updatedStatus($value)
    {
        $this->status = $value ? 'active' : 'inactive';
    }

    public function render()
    {
        return view('livewire.admin.panel.doctor-holidays.doctor-holiday-edit', [
            'formattedDates' => $this->formattedDates,
        ]);
    }
}

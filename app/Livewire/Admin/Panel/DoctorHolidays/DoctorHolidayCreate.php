<?php

namespace App\Livewire\Admin\Panel\DoctorHolidays;

use Livewire\Component;
use Illuminate\Support\Facades\Validator;
use App\Models\DoctorHoliday;
use App\Models\Doctor;
use App\Models\Clinic;
use Morilog\Jalali\Jalalian;

class DoctorHolidayCreate extends Component
{
    public $doctor_id;
    public $clinic_id;
    public $holiday_dates = [];
    public $status = 'active';
    public $doctors = [];
    public $clinics = [];

    public function mount()
    {
        $this->doctors = Doctor::all();
        $this->clinics = Clinic::all();
    }

    public function store()
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

        $gregorianDates = collect($this->holiday_dates)->map(function ($date) {
            return Jalalian::fromFormat('Y/m/d', $date)->toCarbon()->format('Y-m-d');
        })->toArray();

        $existingHoliday = DoctorHoliday::where('doctor_id', $this->doctor_id)
            ->where('clinic_id', $this->clinic_id)
            ->first();

        if ($existingHoliday) {
            $mergedDates = array_unique(array_merge($existingHoliday->holiday_dates, $gregorianDates));
            $existingHoliday->update([
                'holiday_dates' => $mergedDates,
                'status' => $this->status,
            ]);
        } else {
            DoctorHoliday::create([
                'doctor_id' => $this->doctor_id,
                'clinic_id' => $this->clinic_id,
                'holiday_dates' => $gregorianDates,
                'status' => $this->status,
            ]);
        }

        $this->dispatch('show-alert', type: 'success', message: 'تعطیلات پزشک با موفقیت ایجاد شد!');
        return redirect()->route('admin.panel.doctor-holidays.index');
    }

    public function updatedStatus($value)
    {
        $this->status = $value ? 'active' : 'inactive';
    }

    public function render()
    {
        return view('livewire.admin.panel.doctor-holidays.doctor-holiday-create');
    }
}

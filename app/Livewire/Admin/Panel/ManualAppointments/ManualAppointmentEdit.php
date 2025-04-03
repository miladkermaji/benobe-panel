<?php

namespace App\Livewire\Admin\Panel\ManualAppointments;

use Livewire\Component;
use App\Models\ManualAppointment;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Clinic;
use Illuminate\Support\Facades\Validator;
use Morilog\Jalali\Jalalian;
use Carbon\Carbon;

class ManualAppointmentEdit extends Component
{
    public $manualAppointment;
    public $doctor_id;
    public $user_id;
    public $clinic_id;
    public $appointment_date; // تاریخ جلالی برای نمایش
    public $appointment_time;
    public $status;
    public $payment_status;
    public $tracking_code;
    public $fee;
    public $description;
    public $doctors;
    public $users;

    public function mount($id)
    {
        $this->manualAppointment = ManualAppointment::findOrFail($id);
        $this->doctor_id = $this->manualAppointment->doctor_id;
        $this->user_id = $this->manualAppointment->user_id;
        $this->clinic_id = $this->manualAppointment->clinic_id;
        // تبدیل تاریخ میلادی از دیتابیس به شیء Carbon و سپس به جلالی
        $this->appointment_date = Jalalian::fromCarbon(Carbon::parse($this->manualAppointment->appointment_date))->format('Y/m/d');
        $this->appointment_time = $this->manualAppointment->appointment_time;
        $this->status = $this->manualAppointment->status;
        $this->payment_status = $this->manualAppointment->payment_status;
        $this->tracking_code = $this->manualAppointment->tracking_code;
        $this->fee = $this->manualAppointment->fee;
        $this->description = $this->manualAppointment->description;
        $this->doctors = Doctor::with('specialty')->get();
        $this->users = User::all();
    }

    public function update()
    {
        $validator = Validator::make([
            'doctor_id' => $this->doctor_id,
            'user_id' => $this->user_id,
            'clinic_id' => $this->clinic_id,
            'appointment_date' => $this->appointment_date,
            'appointment_time' => $this->appointment_time,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'tracking_code' => $this->tracking_code,
            'fee' => $this->fee,
            'description' => $this->description,
        ], [
            'doctor_id' => 'required|exists:doctors,id',
            'user_id' => 'nullable|exists:users,id',
            'clinic_id' => 'nullable|exists:clinics,id',
            'appointment_date' => 'required|date_format:Y/m/d',
            'appointment_time' => 'required|date_format:H:i',
            'status' => 'required|in:scheduled,cancelled,attended,missed,pending_review',
            'payment_status' => 'required|in:paid,unpaid,pending',
            'tracking_code' => 'nullable|string|max:255',
            'fee' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ], [
            'doctor_id.required' => 'انتخاب پزشک الزامی است.',
            'doctor_id.exists' => 'پزشک انتخاب‌شده معتبر نیست.',
            'user_id.exists' => 'بیمار انتخاب‌شده معتبر نیست.',
            'clinic_id.exists' => 'کلینیک انتخاب‌شده معتبر نیست.',
            'appointment_date.required' => 'تاریخ نوبت الزامی است.',
            'appointment_date.date_format' => 'فرمت تاریخ نوبت باید به صورت YYYY/MM/DD باشد.',
            'appointment_time.required' => 'ساعت نوبت الزامی است.',
            'appointment_time.date_format' => 'فرمت ساعت نوبت باید به صورت HH:MM باشد.',
            'status.required' => 'وضعیت نوبت الزامی است.',
            'status.in' => 'وضعیت نوبت نامعتبر است.',
            'payment_status.required' => 'وضعیت پرداخت الزامی است.',
            'payment_status.in' => 'وضعیت پرداخت نامعتبر است.',
            'tracking_code.max' => 'کد رهگیری نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
            'fee.numeric' => 'هزینه باید یک عدد باشد.',
            'fee.min' => 'هزینه نمی‌تواند منفی باشد.',
            'description.max' => 'یادداشت نمی‌تواند بیشتر از ۵۰۰ کاراکتر باشد.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        // تبدیل تاریخ جلالی به میلادی
        $miladiDate = Jalalian::fromFormat('Y/m/d', $this->appointment_date)->toCarbon();

        $this->manualAppointment->update([
            'doctor_id' => $this->doctor_id,
            'user_id' => $this->user_id,
            'clinic_id' => $this->clinic_id,
            'appointment_date' => $miladiDate,
            'appointment_time' => $this->appointment_time,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'tracking_code' => $this->tracking_code,
            'fee' => $this->fee,
            'description' => $this->description,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'نوبت دستی با موفقیت به‌روزرسانی شد!');
        return redirect()->route('admin.panel.manual-appointments.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.manual-appointments.manual-appointment-edit');
    }
}

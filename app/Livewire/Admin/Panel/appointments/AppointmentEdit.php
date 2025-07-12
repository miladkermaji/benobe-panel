<?php

namespace App\Livewire\Admin\Panel\Appointments;

use Livewire\Component;
use Illuminate\Support\Facades\Validator;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\User;
use Morilog\Jalali\Jalalian;
use Livewire\WithFileUploads;

class AppointmentEdit extends Component
{
    use WithFileUploads;

    public $appointment;
    public $doctor_id;
    public $patient_id;
    public $appointment_date;
    public $appointment_time;
    public $status;
    public $notes;
    public $tracking_code;
    public $fee;
    public $payment_status;

    public function mount($id)
    {
        $this->appointment = Appointment::findOrFail($id);
        $this->doctor_id = $this->appointment->doctor_id;
        $this->patient_id = $this->appointment->patientable_id;
        $this->appointment_date = Jalalian::fromCarbon($this->appointment->appointment_date)->format('Y/m/d');
        $this->appointment_time = $this->appointment->appointment_time->format('H:i');
        $this->status = $this->appointment->status;
        $this->notes = $this->appointment->notes;
        $this->tracking_code = $this->appointment->tracking_code;
        $this->fee = $this->appointment->fee;
        $this->payment_status = $this->appointment->payment_status;
    }

    public function update()
    {
        $validator = Validator::make([
            'doctor_id' => $this->doctor_id,
            'patient_id' => $this->patient_id,
            'appointment_date' => $this->appointment_date,
            'appointment_time' => $this->appointment_time,
            'status' => $this->status,
            'notes' => $this->notes,
            'tracking_code' => $this->tracking_code,
            'fee' => $this->fee,
            'payment_status' => $this->payment_status,
        ], [
            'doctor_id' => 'required|exists:doctors,id',
            'patient_id' => 'nullable|exists:users,id',
            'appointment_date' => 'required|regex:/^\d{4}\/\d{2}\/\d{2}$/',
            'appointment_time' => 'required|date_format:H:i',
            'status' => 'required|in:scheduled,cancelled,attended,missed,pending_review',
            'notes' => 'nullable|string|max:500',
            'tracking_code' => 'nullable|string|max:255|unique:appointments,tracking_code,' . $this->appointment->id,
            'fee' => 'nullable|numeric|min:0',
            'payment_status' => 'nullable|in:paid,unpaid,pending',
        ], [
            'doctor_id.required' => 'انتخاب پزشک الزامی است.',
            'doctor_id.exists' => 'پزشک انتخاب شده معتبر نیست.',
            'patient_id.exists' => 'بیمار انتخاب شده معتبر نیست.',
            'appointment_date.required' => 'تاریخ نوبت الزامی است.',
            'appointment_date.regex' => 'فرمت تاریخ باید به صورت YYYY/MM/DD باشد.',
            'appointment_time.required' => 'زمان نوبت الزامی است.',
            'appointment_time.date_format' => 'فرمت زمان باید به صورت HH:MM باشد.',
            'status.required' => 'وضعیت نوبت الزامی است.',
            'status.in' => 'وضعیت انتخاب شده معتبر نیست.',
            'notes.max' => 'توضیحات نمی‌تواند بیشتر از 500 کاراکتر باشد.',
            'tracking_code.unique' => 'این کد رهگیری قبلا استفاده شده است.',
            'tracking_code.max' => 'کد رهگیری نمی‌تواند بیشتر از 255 کاراکتر باشد.',
            'fee.numeric' => 'مبلغ باید عددی باشد.',
            'fee.min' => 'مبلغ نمی‌تواند منفی باشد.',
            'payment_status.in' => 'وضعیت پرداخت انتخاب شده معتبر نیست.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        $miladiDate = Jalalian::fromFormat('Y/m/d', $this->appointment_date)->toCarbon();

        $this->appointment->update([
            'doctor_id' => $this->doctor_id,
            'patientable_id' => $this->patient_id,
            'patientable_type' => $this->patient_id ? 'App\\Models\\User' : null,
            'appointment_date' => $miladiDate,
            'appointment_time' => $this->appointment_time,
            'status' => $this->status,
            'notes' => $this->notes,
            'tracking_code' => $this->tracking_code,
            'fee' => $this->fee,
            'payment_status' => $this->payment_status,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'نوبت با موفقیت به‌روزرسانی شد!');
        return redirect()->route('admin.panel.appointments.index');
    }

    public function render()
    {
        $doctors = Doctor::with('specialty')->get();
        $patients = User::all();
        return view('livewire.admin.panel.appointments.appointment-edit', [
            'doctors' => $doctors,
            'patients' => $patients,
        ]);
    }
}

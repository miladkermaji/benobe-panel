<?php

namespace App\Livewire\Dr\Panel\DoctorNotes;

use App\Models\Clinic;
use Livewire\Component;
use App\Models\DoctorNote;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DoctorNoteCreate extends Component
{
    public $clinic_id;
    public $appointment_type = 'in_person';
    public $notes;
    public $clinics;

    public function mount()
    {
        $user = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        if ($user instanceof \App\Models\Doctor) {
            $this->clinics = $user->clinics()->get();
            Log::info('Loading clinics for doctor', [
                'user_type' => 'doctor',
                'doctor_id' => $user->id
            ]);
        } else {
            $this->clinics = $user->doctor->clinics()->get();
            Log::info('Loading clinics for secretary', [
                'user_type' => 'secretary',
                'secretary_id' => $user->id,
                'doctor_id' => $user->doctor_id
            ]);
        }

        if ($this->clinics->isEmpty()) {
            Log::info('No clinics found', [
                'user_type' => $user instanceof \App\Models\Doctor ? 'doctor' : 'secretary',
                'user_id' => $user instanceof \App\Models\Doctor ? $user->id : $user->doctor_id
            ]);
        }
    }

    public function store()
    {
        $validator = Validator::make([
            'clinic_id' => $this->clinic_id,
            'appointment_type' => $this->appointment_type,
            'notes' => $this->notes,
        ], [
            'clinic_id' => 'nullable|exists:clinics,id',
            'appointment_type' => 'required|in:in_person,online_phone,online_text,online_video',
            'notes' => 'nullable|string|max:1000',
        ], [
            'clinic_id.exists' => 'کلینیک انتخاب‌شده معتبر نیست.',
            'appointment_type.required' => 'نوع نوبت الزامی است.',
            'appointment_type.in' => 'نوع نوبت باید یکی از گزینه‌های حضوری، تلفنی ویدیویی یا متنی باشد.',
            'notes.max' => 'یادداشت نمی‌تواند بیشتر از ۱۰۰۰ کاراکتر باشد.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        DoctorNote::create([
            'doctor_id' => Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id,
            'clinic_id' => $this->clinic_id,
            'appointment_type' => $this->appointment_type,
            'notes' => $this->notes,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'یادداشت پزشک با موفقیت ایجاد شد!');
        return redirect()->route('dr.panel.doctornotes.index');
    }

    public function render()
    {
        return view('livewire.dr.panel.doctor-notes.doctor-note-create');
    }
}

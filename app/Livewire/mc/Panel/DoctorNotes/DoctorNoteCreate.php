<?php

namespace App\Livewire\Mc\Panel\DoctorNotes;

use App\Models\Clinic;
use Livewire\Component;
use App\Models\DoctorNote;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Traits\HasSelectedDoctor;
use Livewire\Attributes\On;

class DoctorNoteCreate extends Component
{
    use HasSelectedDoctor;
    public $medical_center_id;
    public $appointment_type = 'in_person';
    public $notes;
    public $clinics;

    public function mount()
    {
        // Handle medical center authentication
        if (Auth::guard('medical_center')->check()) {
            $medicalCenter = Auth::guard('medical_center')->user();
            $selectedDoctorId = $this->getSelectedDoctorId();

            if (!$selectedDoctorId) {
                $this->dispatch('show-alert', type: 'error', message: 'هیچ پزشکی انتخاب نشده است.');
                return;
            }

            Log::info('Loading doctor notes create for medical center', [
                'user_type' => 'medical_center',
                'medical_center_id' => $medicalCenter->id,
                'selected_doctor_id' => $selectedDoctorId,
            ]);
        } else {
            // Handle doctor/secretary authentication (existing logic)
            $user = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
            if ($user instanceof \App\Models\Doctor) {
                $this->clinics = $user->clinics()->get();
                Log::info('Loading clinics for doctor', [
                    'user_type' => 'doctor',
                    'doctor_id' => $user->id,
                    'clinics_count' => $this->clinics->count()
                ]);
            } else {
                $doctor = $user->doctor;

                $this->clinics = $doctor->clinics()->get();
                Log::info('Loading clinics for secretary', [
                    'user_type' => 'secretary',
                    'secretary_id' => $user->id,
                    'doctor_id' => $doctor->id,
                    'doctor_exists' => $doctor ? 'yes' : 'no',
                    'clinics_count' => $this->clinics->count(),
                    'raw_clinics' => $doctor->clinics()->toSql()
                ]);
            }
        }

        if (isset($this->clinics) && $this->clinics->isEmpty()) {
            Log::info('No clinics found', [
                'user_type' => Auth::guard('medical_center')->check() ? 'medical_center' : (Auth::guard('doctor')->check() ? 'doctor' : 'secretary'),
                'user_id' => Auth::guard('medical_center')->check() ? Auth::guard('medical_center')->user()->id : (Auth::guard('doctor')->check() ? Auth::guard('doctor')->user()->id : Auth::guard('secretary')->user()->id),
            ]);
        }
    }

    public function store()
    {
        $validator = Validator::make([
            'appointment_type' => $this->appointment_type,
            'notes' => $this->notes,
        ], [
            'appointment_type' => 'required|in:in_person,online_phone,online_text,online_video',
            'notes' => 'nullable|string|max:1000',
        ], [
            'appointment_type.required' => 'نوع نوبت الزامی است.',
            'appointment_type.in' => 'نوع نوبت باید یکی از گزینه‌های حضوری، تلفنی ویدیویی یا متنی باشد.',
            'notes.max' => 'یادداشت نمی‌تواند بیشتر از ۱۰۰۰ کاراکتر باشد.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        // Handle medical center authentication
        if (Auth::guard('medical_center')->check()) {
            $medicalCenter = Auth::guard('medical_center')->user();
            $selectedDoctorId = $this->getSelectedDoctorId();

            // Debug logging
            \Log::info('Creating doctor note - Medical Center Debug', [
                'medical_center_id' => $medicalCenter->id,
                'selected_doctor_id_from_trait' => $selectedDoctorId,
                'selected_doctor_relationship' => $medicalCenter->selectedDoctor,
                'selected_doctor_raw' => $medicalCenter->selectedDoctor?->toArray(),
            ]);

            if (!$selectedDoctorId) {
                $this->dispatch('show-alert', type: 'error', message: 'هیچ پزشکی انتخاب نشده است.');
                return;
            }

            $doctorId = $selectedDoctorId;
            $medicalCenterId = $medicalCenter->id;

            // Debug logging for final values
            \Log::info('Creating doctor note - Final values', [
                'doctor_id' => $doctorId,
                'medical_center_id' => $medicalCenterId,
                'appointment_type' => $this->appointment_type,
                'notes' => $this->notes,
            ]);
        } else {
            // Handle doctor/secretary authentication
            $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
            $medicalCenterId = $this->medical_center_id;
        }

        DoctorNote::create([
            'doctor_id' => $doctorId,
            'medical_center_id' => $medicalCenterId,
            'appointment_type' => $this->appointment_type,
            'notes' => $this->notes,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'یادداشت پزشک با موفقیت ایجاد شد!');
        return redirect()->route('mc.panel.doctornotes.index');
    }

    public function render()
    {
        return view('livewire.mc.panel.doctor-notes.doctor-note-create');
    }

    #[On('doctorSelected')]
    public function handleDoctorSelected($data)
    {
        // Refresh the component when a new doctor is selected
        $this->mount();
    }
}

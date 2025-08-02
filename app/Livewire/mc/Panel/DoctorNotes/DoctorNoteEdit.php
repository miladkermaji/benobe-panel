<?php

namespace App\Livewire\Mc\Panel\DoctorNotes;

use Livewire\Component;
use Illuminate\Support\Facades\Validator;
use App\Models\DoctorNote;
use App\Models\Clinic;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use App\Traits\HasSelectedDoctor;

class DoctorNoteEdit extends Component
{
    use HasSelectedDoctor;
    public $doctorNote;
    public $medical_center_id;
    public $appointment_type;
    public $notes;
    public $clinics;

    public function mount($id)
    {
        // Handle medical center authentication
        if (Auth::guard('medical_center')->check()) {
            $medicalCenter = Auth::guard('medical_center')->user();
            $selectedDoctorId = $this->getSelectedDoctorId();

            if (!$selectedDoctorId) {
                $this->dispatch('show-alert', type: 'error', message: 'هیچ پزشکی انتخاب نشده است.');
                return;
            }

            // For medical centers, only allow editing notes that belong to the selected doctor and medical center
            $this->doctorNote = DoctorNote::where('id', $id)
                ->where('doctor_id', $selectedDoctorId)
                ->where('medical_center_id', $medicalCenter->id)
                ->firstOrFail();
        } else {
            // Handle doctor/secretary authentication (existing logic)
            $this->doctorNote = DoctorNote::findOrFail($id);

            $user = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
            if ($user instanceof \App\Models\Doctor) {
                $this->clinics = $user->clinics;
            } else {
                $this->clinics = $user->doctor->clinics;
            }
        }

        $this->medical_center_id = $this->doctorNote->medical_center_id;
        $this->appointment_type = $this->doctorNote->appointment_type;
        $this->notes = $this->doctorNote->notes;
    }

    public function update()
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

            if (!$selectedDoctorId) {
                $this->dispatch('show-alert', type: 'error', message: 'هیچ پزشکی انتخاب نشده است.');
                return;
            }

            // For medical centers, always use the current medical center ID
            $this->medical_center_id = $medicalCenter->id;
        }

        $this->doctorNote->update([
            'medical_center_id' => $this->medical_center_id,
            'appointment_type' => $this->appointment_type,
            'notes' => $this->notes,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'یادداشت پزشک با موفقیت به‌روزرسانی شد!');
        return redirect()->route('mc.panel.doctornotes.index');
    }

    public function render()
    {
        return view('livewire.mc.panel.doctor-notes.doctor-note-edit');
    }

    #[On('doctorSelected')]
    public function handleDoctorSelected($data)
    {
        // Refresh the component when a new doctor is selected
        // For edit, we might want to redirect to index if the note doesn't belong to the new doctor
        if (Auth::guard('medical_center')->check()) {
            $medicalCenter = Auth::guard('medical_center')->user();
            $selectedDoctorId = $medicalCenter->selectedDoctor?->id;

            if ($selectedDoctorId && $this->doctorNote->doctor_id !== $selectedDoctorId) {
                $this->dispatch('show-alert', type: 'warning', message: 'یادداشت به پزشک انتخاب شده تعلق ندارد.');
                return redirect()->route('mc.panel.doctornotes.index');
            }
        }
    }
}

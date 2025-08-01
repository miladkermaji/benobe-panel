<?php

namespace App\Livewire\Dr\Panel\DoctorNotes;

use Livewire\Component;
use Illuminate\Support\Facades\Validator;
use App\Models\DoctorNote;
use App\Models\Clinic;
use Illuminate\Support\Facades\Auth;

class DoctorNoteEdit extends Component
{
    public $doctorNote;
    public $medical_center_id;
    public $appointment_type;
    public $notes;
    public $clinics;

    public function mount($id)
    {
        $this->doctorNote = DoctorNote::findOrFail($id);
        $this->medical_center_id = $this->doctorNote->medical_center_id;
        $this->appointment_type = $this->doctorNote->appointment_type;
        $this->notes = $this->doctorNote->notes;

        $user = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        if ($user instanceof \App\Models\Doctor) {
            $this->clinics = $user->clinics;
        } else {
            $this->clinics = $user->doctor->clinics;
        }
    }

    public function update()
    {
        $validator = Validator::make([
            'medical_center_id' => $this->medical_center_id,
            'appointment_type' => $this->appointment_type,
            'notes' => $this->notes,
        ], [
            'medical_center_id' => 'nullable|exists:medical_centers,id',
            'appointment_type' => 'required|in:in_person,online_phone,online_text,online_video',
            'notes' => 'nullable|string|max:1000',
        ], [
            'medical_center_id.exists' => 'کلینیک انتخاب‌شده معتبر نیست.',
            'appointment_type.required' => 'نوع نوبت الزامی است.',
            'appointment_type.in' => 'نوع نوبت باید یکی از گزینه‌های حضوری، تلفنی ویدیویی یا متنی باشد.',
            'notes.max' => 'یادداشت نمی‌تواند بیشتر از ۱۰۰۰ کاراکتر باشد.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        $this->doctorNote->update([
            'medical_center_id' => $this->medical_center_id,
            'appointment_type' => $this->appointment_type,
            'notes' => $this->notes,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'یادداشت پزشک با موفقیت به‌روزرسانی شد!');
        return redirect()->route('dr.panel.doctornotes.index');
    }

    public function render()
    {
        return view('livewire.dr.panel.doctor-notes.doctor-note-edit');
    }
}

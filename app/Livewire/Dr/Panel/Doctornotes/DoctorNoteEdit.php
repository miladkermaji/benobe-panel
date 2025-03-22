<?php

namespace App\Livewire\Dr\Panel\Doctornotes;

use Livewire\Component;
use Illuminate\Support\Facades\Validator;
use App\Models\DoctorNote;
use App\Models\Clinic;
use Illuminate\Support\Facades\Auth;

class DoctorNoteEdit extends Component
{
    public $doctorNote;
    public $clinic_id;
    public $appointment_type;
    public $notes;
    public $clinics;

    public function mount($id)
    {
        $this->doctorNote = DoctorNote::findOrFail($id);
        $this->clinic_id = $this->doctorNote->clinic_id;
        $this->appointment_type = $this->doctorNote->appointment_type;
        $this->notes = $this->doctorNote->notes;
        $this->clinics = Clinic::all(); // کلینیک‌ها برای انتخاب
    }

    public function update()
    {
        $validator = Validator::make([
            'clinic_id' => $this->clinic_id,
            'appointment_type' => $this->appointment_type,
            'notes' => $this->notes,
        ], [
            'clinic_id' => 'nullable|exists:clinics,id',
            'appointment_type' => 'required|in:in_person,online_phone,online_text',
            'notes' => 'nullable|string|max:1000',
        ], [
            'clinic_id.exists' => 'کلینیک انتخاب‌شده معتبر نیست.',
            'appointment_type.required' => 'نوع نوبت الزامی است.',
            'appointment_type.in' => 'نوع نوبت باید یکی از گزینه‌های حضوری، تلفنی یا متنی باشد.',
            'notes.max' => 'یادداشت نمی‌تواند بیشتر از ۱۰۰۰ کاراکتر باشد.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        $this->doctorNote->update([
            'clinic_id' => $this->clinic_id,
            'appointment_type' => $this->appointment_type,
            'notes' => $this->notes,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'یادداشت پزشک با موفقیت به‌روزرسانی شد!');
        return redirect()->route('dr.panel.doctornotes.index');
    }

    public function render()
    {
        return view('livewire.dr.panel.doctornotes.doctornote-edit');
    }
}
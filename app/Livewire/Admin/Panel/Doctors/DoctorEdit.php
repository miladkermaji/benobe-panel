<?php

namespace App\Livewire\Admin\Panel\doctors;

use Livewire\Component;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin\Doctor;

class DoctorEdit extends Component
{
    public $doctor;
    public $name;
    public $description;
    public $status;

    public function mount($id)
    {
        $this->doctor = Doctor::findOrFail($id);
        $this->name = $this->doctor->name;
        $this->description = $this->doctor->description;
        $this->status = $this->doctor->status;
    }

    public function update()
    {
        $validator = Validator::make([
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
        ], [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        $this->doctor->update([
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'doctor با موفقیت به‌روزرسانی شد!');
        return redirect()->route('admin.panel.doctors.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.doctors.doctor-edit');
    }
}
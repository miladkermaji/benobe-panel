<?php

namespace App\Livewire\Admin\Panel\doctors;

use Livewire\Component;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin\Doctor;

class DoctorCreate extends Component
{
    public $name;
    public $description;
    public $status = true;

    public function store()
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

        Doctor::create([
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'doctor با موفقیت ایجاد شد!');
        return redirect()->route('admin.panel.doctors.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.doctors.doctor-create');
    }
}
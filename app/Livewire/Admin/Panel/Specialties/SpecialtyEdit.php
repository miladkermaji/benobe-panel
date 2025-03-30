<?php

namespace App\Livewire\Admin\Panel\Specialties;

use App\Models\Specialty;
use Livewire\Component;

class SpecialtyEdit extends Component
{
    public $specialty;
    public $name;
    public $description;
    public $status;

    public function mount($id)
    {
        $this->specialty   = Specialty::findOrFail($id);
        $this->name        = $this->specialty->name;
        $this->description = $this->specialty->description;
        $this->status      = $this->specialty->status;
    }

    public function update()
    {
        $this->validate([
            'name'        => 'required|string|max:255|unique:specialties,name,' . $this->specialty->id,
            'description' => 'nullable|string|max:500',
            'status'      => 'required|boolean',
        ], [
            'name.required'      => 'لطفاً نام تخصص را وارد کنید.',
            'name.string'        => 'نام تخصص باید یک متن باشد.',
            'name.max'           => 'نام تخصص نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
            'name.unique'        => 'این نام تخصص قبلاً ثبت شده است.',
            'description.string' => 'توضیحات باید یک متن باشد.',
            'description.max'    => 'توضیحات نمی‌تواند بیشتر از ۵۰۰ کاراکتر باشد.',
            'status.required'    => 'لطفاً وضعیت را مشخص کنید.',
            'status.boolean'     => 'وضعیت باید فعال یا غیرفعال باشد.',
        ]);

        $this->specialty->update([
            'name'        => $this->name,
            'description' => $this->description,
            'status'      => $this->status,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'تخصص با موفقیت به‌روزرسانی شد!');
        return redirect()->route('admin.panel.specialties.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.specialties.specialty-edit');
    }
}

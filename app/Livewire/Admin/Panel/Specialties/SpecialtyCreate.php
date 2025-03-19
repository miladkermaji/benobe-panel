<?php
namespace App\Livewire\Admin\Panel\Specialties;

use App\Models\Specialty;
use Livewire\Component;

class SpecialtyCreate extends Component
{
    public $name;
    public $description;
    public $status = true;

    public function store()
    {
        $this->validate([
            'name'        => 'required|string|max:255|unique:specialties,name',
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

        Specialty::create([
            'name'        => $this->name,
            'description' => $this->description,
            'status'      => $this->status,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'تخصص با موفقیت ایجاد شد!');
        return redirect()->route('admin.panel.specialties.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.specialties.specialty-create');
    }
}

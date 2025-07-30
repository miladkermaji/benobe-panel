<?php

namespace App\Livewire\Admin\Panel\UserGroups;

use Livewire\Component;
use Illuminate\Support\Facades\Validator;
use App\Models\UserGroup;

class UserGroupCreate extends Component
{
    public $name;
    public $description;
    public $is_active = false;

    public function store()
    {
        $validator = Validator::make([
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ], [
            'name' => 'required|string|max:255|unique:user_groups,name',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
        ], [
            'name.required' => 'فیلد نام الزامی است.',
            'name.string' => 'نام باید یک رشته باشد.',
            'name.max' => 'نام نمی‌تواند بیشتر از 255 کاراکتر باشد.',
            'name.unique' => 'این نام قبلاً ثبت شده است.',
            'description.string' => 'توضیحات باید یک رشته باشد.',
            'is_active.required' => 'وضعیت باید مشخص باشد.',
            'is_active.boolean' => 'وضعیت باید فعال یا غیرفعال باشد.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        UserGroup::create([
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'گروه کاربری با موفقیت ایجاد شد!');
        return redirect()->route('admin.panel.user-groups.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.user-groups.user-group-create');
    }
}

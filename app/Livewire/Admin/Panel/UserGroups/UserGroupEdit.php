<?php

namespace App\Livewire\Admin\Panel\UserGroups;

use Livewire\Component;
use Illuminate\Support\Facades\Validator;
use App\Models\UserGroup;
use App\Models\User;
use App\Models\Doctor;

class UserGroupEdit extends Component
{
    public $usergroup;
    public $name;
    public $description;
    public $is_active;
    public $type = 'user'; // Default value, change logic as needed

    public function mount($id)
    {
        $this->usergroup = UserGroup::findOrFail($id);
        $this->name = $this->usergroup->name;
        $this->description = $this->usergroup->description;
        $this->is_active = $this->usergroup->is_active;
        // Set type here if you have logic to determine user/doctor
        // $this->type = ...;
    }

    public function update()
    {
        $validator = Validator::make([
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ], [
            'name' => 'required|string|max:255|unique:user_groups,name,' . $this->usergroup->id,
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

        $this->usergroup->update([
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'گروه کاربری با موفقیت به‌روزرسانی شد!');
        return redirect()->route('admin.panel.user-groups.index');
    }

    public function render()
    {
        $users = User::all();
        $doctors = Doctor::all();
        return view('livewire.admin.panel.user-groups.user-group-edit', [
            'type' => $this->type,
            'users' => $users,
            'doctors' => $doctors,
        ]);
    }
}

<?php

namespace App\Livewire\Admin\Panel\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin\Dashboard\Cities\Zone;

class UserEdit extends Component
{
    use WithFileUploads;

    public $user;
    public $first_name;
    public $last_name;
    public $email;
    public $mobile;
    public $password;
    public $national_code;
    public $date_of_birth;
    public $sex;
    public $activation;
    public $photo;
    public $zone_province_id;
    public $zone_city_id;

    public $provinces = [];
    public $cities = [];

    public function mount($id)
    {
        $this->user = User::findOrFail($id);
        $this->first_name = $this->user->first_name;
        $this->last_name = $this->user->last_name;
        $this->email = $this->user->email;
        $this->mobile = $this->user->mobile;
        $this->national_code = $this->user->national_code;
        $this->date_of_birth = $this->user->date_of_birth;
        $this->sex = $this->user->sex;
        $this->activation = $this->user->activation;
        $this->zone_province_id = $this->user->zone_province_id;
        $this->zone_city_id = $this->user->zone_city_id;

        $this->provinces = Zone::where('level', 1)->get();
        $this->cities = Zone::where('level', 2)->where('parent_id', $this->zone_province_id)->get();
    }

    public function updatedZoneProvinceId($value)
    {
        $this->cities = Zone::where('level', 2)->where('parent_id', $value)->get();
        $this->zone_city_id = null;
        $this->dispatch('refresh-select2', cities: $this->cities->toArray());
    }

    public function getPhotoPreviewProperty()
    {
        return $this->photo instanceof \Livewire\TemporaryUploadedFile
            ? $this->photo->temporaryUrl()
            : ($this->user->profile_photo_path
                ? Storage::url($this->user->profile_photo_path)
                : asset('admin-assets/images/default-avatar.png'));
    }

    public function update()
    {
        $validator = Validator::make([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'password' => $this->password,
            'national_code' => $this->national_code,
            'date_of_birth' => $this->date_of_birth,
            'sex' => $this->sex,
            'activation' => $this->activation,
            'photo' => $this->photo,
            'zone_province_id' => $this->zone_province_id,
            'zone_city_id' => $this->zone_city_id,
        ], [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->user->id,
            'mobile' => 'required|string|unique:users,mobile,' . $this->user->id,
            'password' => 'nullable|string|min:8',
            'national_code' => 'nullable|string|unique:users,national_code,' . $this->user->id,
            'date_of_birth' => 'nullable|string',
            'sex' => 'required|in:male,female',
            'activation' => 'required|boolean',
            'photo' => 'nullable|image|max:2048',
            'zone_province_id' => 'required|exists:zone,id',
            'zone_city_id' => 'required|exists:zone,id',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        $data = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'national_code' => $this->national_code,
            'date_of_birth' => $this->date_of_birth,
            'sex' => $this->sex,
            'activation' => $this->activation,
            'zone_province_id' => $this->zone_province_id,
            'zone_city_id' => $this->zone_city_id,
        ];

        if ($this->password) {
            $data['password'] = bcrypt($this->password);
        }

        if ($this->photo instanceof \Livewire\TemporaryUploadedFile) {
            if ($this->user->profile_photo_path) {
                Storage::disk('public')->delete($this->user->profile_photo_path);
            }
            $data['profile_photo_path'] = $this->photo->store('profile-photos', 'public');
        }

        $this->user->update($data);

        $this->dispatch('show-alert', type: 'success', message: 'کاربر با موفقیت به‌روزرسانی شد!');
        return redirect()->route('admin.panel.users.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.users.user-edit');
    }
}
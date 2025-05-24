<?php

namespace App\Livewire\Admin\Panel\Users;

use App\Models\User;
use App\Models\Zone;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithFileUploads;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserCreate extends Component
{
    use WithFileUploads;

    public $first_name;
    public $last_name;
    public $email;
    public $mobile;
    public $password;
    public $national_code;
    public $date_of_birth;
    public $sex        = 'male';
    public $activation = true;
    public $photo;
    public $zone_province_id;
    public $zone_city_id;

    public $provinces = [];
    public $cities    = [];

    public function mount()
    {
        $this->provinces = Zone::where('level', 1)->get();
        $this->cities    = [];
    }

    public function updatedZoneProvinceId($value)
    {
        $this->cities       = Zone::where('level', 2)->where('parent_id', $value)->get();
        $this->zone_city_id = null;
        $this->dispatch('refresh-select2', cities: $this->cities->toArray());
    }

    public function getPhotoPreviewProperty()
    {
        return $this->photo ? $this->photo->temporaryUrl() : asset('admin-assets/images/default-avatar.png');
    }

    public function store()
    {
        $validated = $this->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|unique:users|max:255',
            'mobile' => 'required|string|regex:/^09[0-9]{9}$/|unique:users',
            'password' => 'required|string|min:8|max:50',
            'national_code' => 'nullable|string|digits:10|unique:users',
            'date_of_birth' => 'nullable|string|max:10',
            'sex' => 'required|in:male,female',
            'activation' => 'required|boolean',
            'photo' => 'nullable|image|max:2048',
            'zone_province_id' => 'required|exists:zone,id',
            'zone_city_id' => 'required|exists:zone,id',
        ]);

        if ($this->photo) {
            $validated['profile_photo_path'] = $this->photo->store('profile-photos', 'public');
        }

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        $this->dispatch('show-alert', type: 'success', message: 'کاربر با موفقیت ایجاد شد!');
        return redirect()->route('admin.panel.users.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.users.user-create');
    }
}

<?php

namespace App\Livewire\Admin\Panel\Users;

use App\Models\User;
use App\Models\Zone;
use Livewire\Component;
use Morilog\Jalali\Jalalian;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Facades\Hash;

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
    public $status;
    public $photo;
    public $zone_province_id;
    public $zone_city_id;

    public $provinces = [];
    public $cities    = [];

    public function mount($id)
    {
        $this->user          = User::findOrFail($id);
        $this->first_name    = $this->user->first_name;
        $this->last_name     = $this->user->last_name;
        $this->email         = $this->user->email;
        $this->mobile        = $this->user->mobile;
        $this->national_code = $this->user->national_code;
        $this->date_of_birth = $this->user->date_of_birth
        ? Jalalian::fromCarbon(\Carbon\Carbon::parse($this->user->date_of_birth))->format('Y/m/d')
        : null;
        $this->sex              = $this->user->sex;
        $this->status           = $this->user->status;
        $this->zone_province_id = $this->user->zone_province_id;
        $this->zone_city_id     = $this->user->zone_city_id;

        $this->provinces = Zone::where('level', 1)->get();
        $this->cities    = Zone::where('level', 2)->where('parent_id', $this->zone_province_id)->get();
    }

    public function updatedZoneProvinceId($value)
    {
        $this->cities       = Zone::where('level', 2)->where('parent_id', $value)->get();
        $this->zone_city_id = null;
        $this->dispatch('refresh-select2', cities: $this->cities->toArray());
    }

    public function getPhotoPreviewProperty()
    {
        if ($this->photo) {
            return $this->photo->temporaryUrl();
        }
        return $this->user->profile_photo_url;
    }

    public function update()
    {
        $validated = $this->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|unique:users,email,' . $this->user->id . '|max:255',
            'mobile' => 'required|string|regex:/^09[0-9]{9}$/|unique:users,mobile,' . $this->user->id,
            'password' => 'nullable|string|min:8|max:50',
            'national_code' => 'nullable|string|digits:10|unique:users,national_code,' . $this->user->id,
            'date_of_birth' => 'nullable|string|max:10',
            'sex' => 'required|in:male,female',
            'status' => 'required|boolean',
            'photo' => 'nullable|image|max:2048',
            'zone_province_id' => 'required|exists:zone,id',
            'zone_city_id' => 'required|exists:zone,id',
        ]);

        if ($this->photo) {
            if ($this->user->profile_photo_path) {
                Storage::disk('public')->delete($this->user->profile_photo_path);
            }
            $validated['profile_photo_path'] = $this->photo->store('profile-photos', 'public');
        }

        if ($validated['password']) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $this->user->update($validated);

        $this->dispatch('show-alert', type: 'success', message: 'کاربر با موفقیت بروزرسانی شد!');
        return redirect()->route('admin.panel.users.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.users.user-edit');
    }
}

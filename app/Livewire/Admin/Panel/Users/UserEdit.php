<?php
namespace App\Livewire\Admin\Panel\Users;

use App\Models\User;
use App\Models\Zone;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithFileUploads;
use Morilog\Jalali\Jalalian;

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
        $this->activation       = $this->user->activation;
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
        return $this->photo instanceof \Livewire\TemporaryUploadedFile
        ? $this->photo->temporaryUrl()
        : ($this->user->profile_photo_path
            ? Storage::url($this->user->profile_photo_path)
            : asset('admin-assets/images/default-avatar.png'));
    }

    public function update()
    {
        Log::info('Date of Birth Input (UserEdit): ' . $this->date_of_birth);
        Log::info('Photo Input (UserEdit): ' . ($this->photo ? 'File exists' : 'No file'));

        $validator = Validator::make([
            'first_name'       => $this->first_name,
            'last_name'        => $this->last_name,
            'email'            => $this->email,
            'mobile'           => $this->mobile,
            'password'         => $this->password,
            'national_code'    => $this->national_code,
            'date_of_birth'    => $this->date_of_birth,
            'sex'              => $this->sex,
            'activation'       => $this->activation,
            'photo'            => $this->photo,
            'zone_province_id' => $this->zone_province_id,
            'zone_city_id'     => $this->zone_city_id,
        ], [
            'first_name'       => 'required|string|max:50',
            'last_name'        => 'required|string|max:50',
            'email'            => 'required|email|unique:users,email,' . $this->user->id . '|max:255',
            'mobile'           => 'required|string|regex:/^09[0-9]{9}$/|unique:users,mobile,' . $this->user->id,
            'password'         => 'nullable|string|min:8|max:50',
            'national_code'    => 'nullable|string|digits:10|unique:users,national_code,' . $this->user->id,
            'date_of_birth'    => 'nullable|string|max:10',
            'sex'              => 'required|in:male,female',
            'activation'       => 'required|boolean',
            'photo'            => 'nullable|image|max:2048',
            'zone_province_id' => 'required|exists:zone,id',
            'zone_city_id'     => 'required|exists:zone,id',
        ], [
            'first_name.required'       => 'لطفاً نام را وارد کنید.',
            'first_name.string'         => 'نام باید متن باشد.',
            'first_name.max'            => 'نام نباید بیشتر از ۵۰ حرف باشد.',
            'last_name.required'        => 'لطفاً نام خانوادگی را وارد کنید.',
            'last_name.string'          => 'نام خانوادگی باید متن باشد.',
            'last_name.max'             => 'نام خانوادگی نباید بیشتر از ۵۰ حرف باشد.',
            'email.required'            => 'لطفاً ایمیل را وارد کنید.',
            'email.email'               => 'ایمیل واردشده معتبر نیست.',
            'email.unique'              => 'این ایمیل قبلاً ثبت شده است.',
            'email.max'                 => 'ایمیل نباید بیشتر از ۲۵۵ کاراکتر باشد.',
            'mobile.required'           => 'لطفاً شماره موبایل را وارد کنید.',
            'mobile.string'             => 'شماره موبایل باید متن باشد.',
            'mobile.regex'              => 'شماره موبایل باید با ۰۹ شروع شود و ۱۱ رقم باشد.',
            'mobile.unique'             => 'این شماره موبایل قبلاً ثبت شده است.',
            'password.min'              => 'رمز عبور باید حداقل ۸ حرف باشد.',
            'password.max'              => 'رمز عبور نباید بیشتر از ۵۰ حرف باشد.',
            'password.string'           => 'رمز عبور باید متن باشد.',
            'password.nullable'         => 'رمز عبور باید خالی یا معتبر باشد.',
            'national_code.digits'      => 'کد ملی باید دقیقاً ۱۰ رقم باشد.',
            'national_code.unique'      => 'این کد ملی قبلاً ثبت شده است.',
            'national_code.string'      => 'کد ملی باید متن باشد.',
            'national_code.nullable'    => 'کد ملی باید خالی یا ۱۰ رقم باشد.',
            'date_of_birth.string'      => 'تاریخ تولد باید متن باشد.',
            'date_of_birth.max'         => 'تاریخ تولد نباید بیشتر از ۱۰ کاراکتر باشد.',
            'date_of_birth.nullable'    => 'تاریخ تولد باید خالی یا معتبر باشد.',
            'sex.required'              => 'لطفاً جنسیت را انتخاب کنید.',
            'sex.in'                    => 'جنسیت باید "مرد" یا "زن" باشد.',
            'activation.required'       => 'لطفاً وضعیت را مشخص کنید.',
            'activation.boolean'        => 'وضعیت باید فعال یا غیرفعال باشد.',
            'photo.image'               => 'فایل باید عکس باشد.',
            'photo.max'                 => 'حجم عکس نباید بیشتر از ۲ مگابایت باشد.',
            'photo.nullable'            => 'عکس باید خالی یا یک فایل معتبر باشد.',
            'zone_province_id.required' => 'لطفاً استان را انتخاب کنید.',
            'zone_province_id.exists'   => 'استان انتخاب‌شده معتبر نیست.',
            'zone_city_id.required'     => 'لطفاً شهر را انتخاب کنید.',
            'zone_city_id.exists'       => 'شهر انتخاب‌شده معتبر نیست.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        $dateOfBirthMiladi = null;
        if ($this->date_of_birth) {
            try {
                $dateOfBirthMiladi = Jalalian::fromFormat('Y/m/d', $this->date_of_birth)->toCarbon()->toDateString();
            } catch (\Exception $e) {
                $this->dispatch('show-alert', type: 'error', message: 'تاریخ تولد نامعتبر است. لطفاً به فرمت ۱۴۰۳/۱۲/۱۳ وارد کنید.');
                return;
            }
        }

        $data = [
            'first_name'       => $this->first_name,
            'last_name'        => $this->last_name,
            'email'            => $this->email,
            'mobile'           => $this->mobile,
            'national_code'    => $this->national_code,
            'date_of_birth'    => $dateOfBirthMiladi,
            'sex'              => $this->sex,
            'activation'       => $this->activation,
            'zone_province_id' => $this->zone_province_id,
            'zone_city_id'     => $this->zone_city_id,
        ];

        if ($this->password) {
            $data['password'] = bcrypt($this->password);
        }

        if ($this->photo) {
            if ($this->user->profile_photo_path) {
                Storage::disk('public')->delete($this->user->profile_photo_path);
            }
            $data['profile_photo_path'] = $this->photo->store('profile-photos', 'public');
            Log::info('Photo Path (UserEdit): ' . $data['profile_photo_path']);
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

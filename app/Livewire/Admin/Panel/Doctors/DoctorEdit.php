<?php

namespace App\Livewire\Admin\Panel\Doctors;

use App\Models\Zone;
use App\Models\Doctor;
use Livewire\Component;
use Morilog\Jalali\Jalalian;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class DoctorEdit extends Component
{
    use WithFileUploads;

    public $doctor;
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
    public $appointment_fee;
    public $visit_fee;

    public $provinces = [];
    public $cities    = [];

    public function mount($id)
    {
        $this->doctor        = Doctor::findOrFail($id);
        $this->first_name    = $this->doctor->first_name;
        $this->last_name     = $this->doctor->last_name;
        $this->email         = $this->doctor->email;
        $this->mobile        = $this->doctor->mobile;
        $this->national_code = $this->doctor->national_code;
        $this->date_of_birth = $this->doctor->date_of_birth
        ? Jalalian::fromCarbon(\Carbon\Carbon::parse($this->doctor->date_of_birth))->format('Y/m/d')
        : null;
        $this->sex              = $this->doctor->sex;
        $this->status           = $this->doctor->status;
        $this->zone_province_id = $this->doctor->zone_province_id;
        $this->zone_city_id     = $this->doctor->zone_city_id;
        $this->appointment_fee  = $this->doctor->appointment_fee;
        $this->visit_fee        = $this->doctor->visit_fee;

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
        return $this->photo instanceof TemporaryUploadedFile
        ? $this->photo->temporaryUrl()
        : ($this->doctor->profile_photo_path
            ? Storage::url($this->doctor->profile_photo_path)
            : asset('admin-assets/images/default-avatar.png'));
    }

    public function update()
    {
        Log::info('Date of Birth Input (DoctorEdit): ' . $this->date_of_birth);
        Log::info('Photo Input (DoctorEdit): ' . ($this->photo ? 'File exists' : 'No file'));

        $validator = Validator::make([
            'first_name'       => $this->first_name,
            'last_name'        => $this->last_name,
            'email'            => $this->email,
            'mobile'           => $this->mobile,
            'password'         => $this->password,
            'national_code'    => $this->national_code,
            'date_of_birth'    => $this->date_of_birth,
            'sex'              => $this->sex,
            'status'           => $this->status,
            'photo'            => $this->photo,
            'zone_province_id' => $this->zone_province_id,
            'zone_city_id'     => $this->zone_city_id,
            'appointment_fee'  => $this->appointment_fee,
            'visit_fee'        => $this->visit_fee,
        ], [
            'first_name'       => 'required|string|max:50',
            'last_name'        => 'required|string|max:50',
            'email'            => 'nullable|email|unique:doctors,email,' . $this->doctor->id . '|max:255',
            'mobile'           => 'required|string|regex:/^09[0-9]{9}$/|unique:doctors,mobile,' . $this->doctor->id,
            'password'         => 'nullable|string|min:8|max:50',
            'national_code'    => 'nullable|string|digits:10|unique:doctors,national_code,' . $this->doctor->id,
            'date_of_birth'    => 'nullable|string|max:10',
            'sex'              => 'required|in:male,female',
            'status'           => 'required|boolean',
            'photo'            => 'nullable|image|max:2048',
            'zone_province_id' => 'required|exists:zone,id',
            'zone_city_id'     => 'required|exists:zone,id',
            'appointment_fee'  => 'nullable|numeric|min:0|max:10000000',
            'visit_fee'        => 'nullable|numeric|min:0|max:10000000',
        ], [
            'first_name.required'       => 'لطفاً نام را وارد کنید.',
            'first_name.string'         => 'نام باید متن باشد.',
            'first_name.max'            => 'نام نباید بیشتر از ۵۰ حرف باشد.',
            'last_name.required'        => 'لطفاً نام خانوادگی را وارد کنید.',
            'last_name.string'          => 'نام خانوادگی باید متن باشد.',
            'last_name.max'             => 'نام خانوادگی نباید بیشتر از ۵۰ حرف باشد.',
            'email.email'               => 'ایمیل واردشده معتبر نیست.',
            'email.unique'              => 'این ایمیل قبلاً ثبت شده است.',
            'email.max'                 => 'ایمیل نباید بیشتر از ۲۵۵ کاراکتر باشد.',
            'email.nullable'            => 'ایمیل باید خالی یا معتبر باشد.',
            'mobile.required'           => 'لطفاً شماره موبایل را وارد کنید.',
            'mobile.string'             => 'شماره موبایل باید متن باشد.',
            'mobile.regex'              => 'شماره موبایل باید با ۰۹ شروع شود و ۱۱ رقم باشد.',
            'mobile.unique'             => 'این شماره موبایل قبلاً ثبت شده است.',
            'password.string'           => 'رمز عبور باید متن باشد.',
            'password.min'              => 'رمز عبور باید حداقل ۸ حرف باشد.',
            'password.max'              => 'رمز عبور نباید بیشتر از ۵۰ حرف باشد.',
            'password.nullable'         => 'رمز عبور باید خالی یا معتبر باشد.',
            'national_code.string'      => 'کد ملی باید متن باشد.',
            'national_code.digits'      => 'کد ملی باید دقیقاً ۱۰ رقم باشد.',
            'national_code.unique'      => 'این کد ملی قبلاً ثبت شده است.',
            'national_code.nullable'    => 'کد ملی باید خالی یا ۱۰ رقم باشد.',
            'date_of_birth.string'      => 'تاریخ تولد باید متن باشد.',
            'date_of_birth.max'         => 'تاریخ تولد نباید بیشتر از ۱۰ کاراکتر باشد.',
            'date_of_birth.nullable'    => 'تاریخ تولد باید خالی یا معتبر باشد.',
            'sex.required'              => 'لطفاً جنسیت را انتخاب کنید.',
            'sex.in'                    => 'جنسیت باید "مرد" یا "زن" باشد.',
            'status.required'           => 'لطفاً وضعیت را مشخص کنید.',
            'status.boolean'            => 'وضعیت باید فعال یا غیرفعال باشد.',
            'photo.image'               => 'فایل باید عکس باشد.',
            'photo.max'                 => 'حجم عکس نباید بیشتر از ۲ مگابایت باشد.',
            'photo.nullable'            => 'عکس باید خالی یا یک فایل معتبر باشد.',
            'zone_province_id.required' => 'لطفاً استان را انتخاب کنید.',
            'zone_province_id.exists'   => 'استان انتخاب‌شده معتبر نیست.',
            'zone_city_id.required'     => 'لطفاً شهر را انتخاب کنید.',
            'zone_city_id.exists'       => 'شهر انتخاب‌شده معتبر نیست.',
            'appointment_fee.numeric'   => 'تعرفه نوبت باید عدد باشد.',
            'appointment_fee.min'       => 'تعرفه نوبت نمی‌تواند منفی باشد.',
            'appointment_fee.max'       => 'تعرفه نوبت نباید بیشتر از ۱۰ میلیون تومان باشد.',
            'appointment_fee.nullable'  => 'تعرفه نوبت باید خالی یا عدد باشد.',
            'visit_fee.numeric'         => 'تعرفه ویزیت باید عدد باشد.',
            'visit_fee.min'             => 'تعرفه ویزیت نمی‌تواند منفی باشد.',
            'visit_fee.max'             => 'تعرفه ویزیت نباید بیشتر از ۱۰ میلیون تومان باشد.',
            'visit_fee.nullable'        => 'تعرفه ویزیت باید خالی یا عدد باشد.',
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
            'status'           => $this->status,
            'zone_province_id' => $this->zone_province_id,
            'zone_city_id'     => $this->zone_city_id,
            'appointment_fee'  => $this->appointment_fee,
            'visit_fee'        => $this->visit_fee,
        ];

        if ($this->password) {
            $data['password'] = bcrypt($this->password);
        }

        if ($this->photo) {
            if ($this->doctor->profile_photo_path) {
                Storage::disk('public')->delete($this->doctor->profile_photo_path);
            }
            $data['profile_photo_path'] = $this->photo->store('doctor-photos', 'public');
            Log::info('Photo Path (DoctorEdit): ' . $data['profile_photo_path']);
        }

        $this->doctor->update($data);

        $this->dispatch('show-alert', type: 'success', message: 'پزشک با موفقیت به‌روزرسانی شد!');
        return redirect()->route('admin.panel.doctors.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.doctors.doctor-edit');
    }
}

<?php


namespace App\Livewire\Admin\Panel\Hospitals;

use App\Models\Hospital;
use App\Models\Zone;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class HospitalCreate extends Component
{
    public $name;
    public $address;
    public $secretary_phone;
    public $phone_number;
    public $postal_code;
    public $province_id;
    public $city_id;
    public $is_main_center = false;
    public $start_time;
    public $end_time;
    public $description;
    public $latitude;
    public $longitude;
    public $consultation_fee;
    public $payment_methods;
    public $is_active = true;
    public $working_days = [];
    public $phone_numbers = [];
    public $location_confirmed = false;

    public $provinces = [];
    public $cities = [];

    public function mount()
    {
        $this->provinces = Zone::where('level', 1)->get();
        $this->cities = [];
    }

    public function updatedProvinceId($value)
    {
        $this->cities = Zone::where('level', 2)->where('parent_id', $value)->get();
        $this->city_id = null;
        $this->dispatch('refresh-select2', cities: $this->cities->toArray());
    }

    public function store()
    {
        $validator = Validator::make($this->all(), [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'secretary_phone' => 'nullable|string|regex:/^09[0-9]{9}$/',
            'phone_number' => 'nullable|string|regex:/^09[0-9]{9}$/',
            'postal_code' => 'nullable|string|digits:10',
            'province_id' => 'required|exists:zone,id',
            'city_id' => 'required|exists:zone,id',
            'is_main_center' => 'boolean',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'description' => 'nullable|string|max:1000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'consultation_fee' => 'nullable|numeric|min:0',
            'payment_methods' => 'nullable|in:cash,card,online',
            'is_active' => 'boolean',
            'working_days' => 'nullable|array',
            'phone_numbers' => 'nullable|array',
            'location_confirmed' => 'boolean',
        ], [
            'name.required' => 'لطفاً نام بیمارستان را وارد کنید.',
            'name.string' => 'نام بیمارستان باید متن باشد.',
            'name.max' => 'نام بیمارستان نباید بیشتر از ۲۵۵ حرف باشد.',
            'address.string' => 'آدرس باید متن باشد.',
            'address.max' => 'آدرس نباید بیشتر از ۵۰۰ حرف باشد.',
            'secretary_phone.regex' => 'شماره منشی باید با ۰۹ شروع شود و ۱۱ رقم باشد.',
            'phone_number.regex' => 'شماره تماس باید با ۰۹ شروع شود و ۱۱ رقم باشد.',
            'postal_code.digits' => 'کد پستی باید دقیقاً ۱۰ رقم باشد.',
            'province_id.required' => 'لطفاً استان را انتخاب کنید.',
            'province_id.exists' => 'استان انتخاب‌شده معتبر نیست.',
            'city_id.required' => 'لطفاً شهر را انتخاب کنید.',
            'city_id.exists' => 'شهر انتخاب‌شده معتبر نیست.',
            'start_time.date_format' => 'ساعت شروع باید به فرمت HH:MM باشد.',
            'end_time.date_format' => 'ساعت پایان باید به فرمت HH:MM باشد.',
            'description.max' => 'توضیحات نباید بیشتر از ۱۰۰۰ حرف باشد.',
            'latitude.between' => 'عرض جغرافیایی باید بین -۹۰ و ۹۰ باشد.',
            'longitude.between' => 'طول جغرافیایی باید بین -۱۸۰ و ۱۸۰ باشد.',
            'consultation_fee.numeric' => 'هزینه مشاوره باید عدد باشد.',
            'consultation_fee.min' => 'هزینه مشاوره نمی‌تواند منفی باشد.',
            'payment_methods.in' => 'روش پرداخت باید یکی از گزینه‌های نقدی، کارت یا آنلاین باشد.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        Hospital::create($validator->validated());

        $this->dispatch('show-alert', type: 'success', message: 'بیمارستان با موفقیت ایجاد شد!');
        return redirect()->route('admin.panel.hospitals.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.hospitals.hospital-create');
    }
}


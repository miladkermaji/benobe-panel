<?php
namespace App\Livewire\Admin\Panel\TreatmentCenters;

use App\Models\Doctor;
use App\Models\TreatmentCenter;
use App\Models\Zone;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class TreatmentCenterCreate extends Component
{
    public $doctor_id, $name, $address, $phone_number;
    public $province_id, $city_id, $is_main_center = false, $start_time, $end_time;
    public $description, $consultation_fee, $payment_methods;
    public $is_active = true;

    public $doctors = [], $provinces = [], $cities = [];

    public function mount()
    {
        $this->doctors   = Doctor::all();
        $this->provinces = Zone::where('level', 1)->get();
        $this->cities    = [];
    }

    public function updatedProvinceId($value)
    {
        $this->cities  = Zone::where('level', 2)->where('parent_id', $value)->get();
        $this->city_id = null;
        $this->dispatch('refresh-select2', cities: $this->cities->toArray());
    }

    public function store()
    {
        $validator = Validator::make($this->all(), [
            'doctor_id'        => 'required|exists:doctors,id',
            'name'             => 'required|string|max:255',
            'address'          => 'nullable|string|max:500',
            'phone_number'     => 'nullable|string|regex:/^09[0-9]{9}$/',
            'province_id'      => 'required|exists:zone,id',
            'city_id'          => 'required|exists:zone,id',
            'is_main_center'   => 'boolean',
            'start_time'       => 'nullable|date_format:H:i',
            'end_time'         => 'nullable|date_format:H:i',
            'description'      => 'nullable|string|max:1000',
            'consultation_fee' => 'nullable|numeric|min:0',
            'payment_methods'  => 'nullable|in:cash,card,online',
            'is_active'        => 'boolean',
        ], [
            'doctor_id.required'       => 'لطفاً پزشک را انتخاب کنید.',
            'doctor_id.exists'         => 'پزشک انتخاب‌شده معتبر نیست.',
            'name.required'            => 'لطفاً نام درمانگاه را وارد کنید.',
            'name.string'              => 'نام درمانگاه باید متن باشد.',
            'name.max'                 => 'نام درمانگاه نباید بیشتر از ۲۵۵ حرف باشد.',
            'address.string'           => 'آدرس باید متن باشد.',
            'address.max'              => 'آدرس نباید بیشتر از ۵۰۰ حرف باشد.',
            'phone_number.regex'       => 'شماره تماس باید با ۰۹ شروع شود و ۱۱ رقم باشد.',
            'province_id.required'     => 'لطفاً استان را انتخاب کنید.',
            'province_id.exists'       => 'استان انتخاب‌شده معتبر نیست.',
            'city_id.required'         => 'لطفاً شهر را انتخاب کنید.',
            'city_id.exists'           => 'شهر انتخاب‌شده معتبر نیست.',
            'start_time.date_format'   => 'ساعت شروع باید به فرمت HH:MM باشد.',
            'end_time.date_format'     => 'ساعت پایان باید به فرمت HH:MM باشد.',
            'description.max'          => 'توضیحات نباید بیشتر از ۱۰۰۰ حرف باشد.',
            'consultation_fee.numeric' => 'هزینه خدمات باید عدد باشد.',
            'consultation_fee.min'     => 'هزینه خدمات نمی‌تواند منفی باشد.',
            'payment_methods.in'       => 'روش پرداخت باید یکی از گزینه‌های نقدی، کارت یا آنلاین باشد.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        TreatmentCenter::create($validator->validated());

        $this->dispatch('show-alert', type: 'success', message: 'درمانگاه با موفقیت ایجاد شد!');
        return redirect()->route('admin.panel.treatmentcenters.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.treatmentcenters.treatmentcenters-create');
    }
}

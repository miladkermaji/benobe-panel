<?php

namespace App\Livewire\Admin\Panel\ImagingCenters;

use App\Models\Zone;
use App\Models\Doctor;
use Livewire\Component;
use App\Models\Insurance;
use App\Models\Specialty;
use App\Models\ImagingCenter;
use App\Models\MedicalCenter;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ImagingCenterEdit extends Component
{
    use WithFileUploads;

    public $imagingCenter;
    public $doctor_ids = [];
    public $specialty_ids = [];
    public $insurance_ids = [];
    public $name;
    public $title;
    public $address;
    public $phone_number;
    public $secretary_phone;
    public $postal_code;
    public $province_id;
    public $city_id;
    public $is_main_center;
    public $start_time;
    public $end_time;
    public $description;
    public $latitude;
    public $longitude;
    public $consultation_fee;
    public $payment_methods;
    public $is_active;
    public $working_days = [];
    public $avatar;
    public $documents = [];
    public $phone_numbers = [''];
    public $location_confirmed;
    public $type = 'imagingCenter';

    public $doctors = [];
    public $specialties = [];
    public $provinces = [];
    public $insurances = [];
    public $cities = [];
    public $service_ids = [];
    public $services;
    public $Center_tariff_type;
    public $Daycare_centers;
    public function mount($id)
    {
        $this->imagingCenter = MedicalCenter::findOrFail($id);
        $this->fill($this->imagingCenter->toArray());
        $this->doctor_ids = $this->imagingCenter->doctors->pluck('id')->map('strval')->toArray();
        $this->phone_numbers = $this->imagingCenter->phone_numbers ?: [''];
        $this->specialty_ids = $this->imagingCenter->specialty_ids ? array_map('strval', $this->imagingCenter->specialty_ids) : [];
        $this->insurance_ids = $this->imagingCenter->insurance_ids ? array_map('strval', $this->imagingCenter->insurance_ids) : [];
        $this->Center_tariff_type = $this->hospital->Center_tariff_type;
        $this->Daycare_centers = $this->hospital->Daycare_centers;

        $this->services = \App\Models\Service::all();

        $this->service_ids = $this->hospital->service_ids ? array_map('strval', $this->hospital->service_ids) : [];

        // تنظیم روزهای کاری
        $workingDays = $this->imagingCenter->working_days ?? [];
        $this->working_days = array_fill_keys(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], false);
        foreach ($workingDays as $day) {
            $this->working_days[$day] = true;
        }

        $this->doctors = Doctor::all();
        $this->specialties = Specialty::all();
        $this->insurances = Insurance::all();
        $this->provinces = Zone::where('level', 1)->get();
        $this->cities = $this->province_id ? Zone::where('level', 2)->where('parent_id', $this->province_id)->get() : [];

        $this->dispatch('set-select2-initial', [
            'doctor_ids' => $this->doctor_ids,
            'specialty_ids' => $this->specialty_ids,
            'insurance_ids' => $this->insurance_ids,
            'province_id' => $this->province_id ? strval($this->province_id) : null,
            'city_id' => $this->city_id ? strval($this->city_id) : null,
            'payment_methods' => $this->payment_methods,
            'Center_tariff_type' => $this->Center_tariff_type,
'Daycare_centers' => $this->Daycare_centers,
'service_ids' => $this->service_ids,
        ]);
    }

    public function updatedProvinceId($value)
    {
        $this->cities = Zone::where('level', 2)->where('parent_id', $value)->get();
        $this->city_id = null;
        $this->dispatch('refresh-select2', cities: $this->cities->toArray());
    }

    public function addPhoneNumber()
    {
        $this->phone_numbers[] = '';
    }

    public function removePhoneNumber($index)
    {
        unset($this->phone_numbers[$index]);
        $this->phone_numbers = array_values($this->phone_numbers);
    }

    public function update()
    {
        $validator = Validator::make($this->all(), [
            'doctor_ids' => 'required|array',
            'doctor_ids.*' => 'exists:doctors,id',
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone_number' => 'nullable|string|regex:/^09[0-9]{9}$/',
            'secretary_phone' => 'nullable|string|regex:/^09[0-9]{9}$/',
            'postal_code' => 'nullable|string|size:10',
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
            'avatar' => 'nullable|image',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx|max:10240',
            'phone_numbers' => 'nullable|array',
            'phone_numbers.*' => 'string|regex:/^09[0-9]{9}$/',
            'location_confirmed' => 'boolean',
            'type' => 'required|in:imagingCenter,treatment_centers,clinic,imaging_center,pharmacy',
            'specialty_ids' => 'nullable|array',
            'specialty_ids.*' => 'exists:specialties,id',
            'insurance_ids' => 'nullable|array',
            'insurance_ids.*' => 'exists:insurances,id',
            'Center_tariff_type' => 'nullable|in:governmental,special,else',
'Daycare_centers' => 'nullable|in:yes,no',
'service_ids' => 'nullable|array',
'service_ids.*' => 'exists:services,id',
        ], [
            'doctor_ids.required' => 'لطفاً حداقل یک پزشک را انتخاب کنید.',
            'doctor_ids.*.exists' => 'پزشک انتخاب‌شده معتبر نیست.',
            'name.required' => 'لطفاً نام مرکز تصویر برداری  را وارد کنید.',
            'name.max' => 'نام مرکز تصویر برداری  نباید بیشتر از ۲۵۵ حرف باشد.',
            'title.max' => 'عنوان نباید بیشتر از ۲۵۵ حرف باشد.',
            'address.max' => 'آدرس نباید بیشتر از ۵۰۰ حرف باشد.',
            'phone_number.regex' => 'شماره تماس باید با ۰۹ شروع شود و ۱۱ رقم باشد.',
            'secretary_phone.regex' => 'شماره منشی باید با ۰۹ شروع شود و ۱۱ رقم باشد.',
            'postal_code.size' => 'کد پستی باید ۱۰ رقم باشد.',
            'province_id.required' => 'لطفاً استان را انتخاب کنید.',
            'city_id.required' => 'لطفاً شهر را انتخاب کنید.',
            'start_time.date_format' => 'ساعت شروع باید به فرمت HH:MM باشد.',
            'end_time.date_format' => 'ساعت پایان باید به فرمت HH:MM باشد.',
            'description.max' => 'توضیحات نباید بیشتر از ۱۰۰۰ حرف باشد.',
            'latitude.between' => 'عرض جغرافیایی باید بین -۹۰ و ۹۰ باشد.',
            'longitude.between' => 'طول جغرافیایی باید بین -۱۸۰ و ۱۸۰ باشد.',
            'consultation_fee.numeric' => 'هزینه خدمات باید عدد باشد.',
            'consultation_fee.min' => 'هزینه خدمات نمی‌تواند منفی باشد.',
            'payment_methods.in' => 'روش پرداخت باید یکی از گزینه‌های نقدی، کارت یا آنلاین باشد.',
            'working_days.*.in' => 'روزهای کاری باید از بین روزهای هفته باشد.',
            'avatar.image' => 'تصویر اصلی باید یک فایل تصویری باشد.',
            'avatar.max' => 'تصویر اصلی نباید بزرگ‌تر از ۲ مگابایت باشد.',
            'documents.*.mimes' => 'مدارک باید از نوع PDF، DOC یا DOCX باشند.',
            'documents.*.max' => 'هر مدرک نباید بزرگ‌تر از ۱۰ مگابایت باشد.',
            'phone_numbers.*.regex' => 'شماره‌های تماس باید با ۰۹ شروع شوند و ۱۱ رقم باشند.',
            'specialty_ids.*.exists' => 'تخصص انتخاب‌شده معتبر نیست.',
            'insurance_ids.*.exists' => 'بیمه انتخاب‌شده معتبر نیست.',
            'Center_tariff_type.in' => 'نوع تعرفه مرکز باید یکی از گزینه‌های دولتی، ویژه یا سایر باشد.',
'Daycare_centers.in' => 'وضعیت مرکز شبانه‌روزی باید بله یا خیر باشد.',
'service_ids.*.exists' => 'خدمت انتخاب‌شده معتبر نیست.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        $data = $validator->validated();

        if ($this->avatar) {
            if ($this->imagingCenter->avatar) {
                Storage::disk('public')->delete($this->imagingCenter->avatar);
            }
            $data['avatar'] = $this->avatar->store('avatars', 'public');
        }

        if ($this->documents) {
            if ($this->imagingCenter->documents) {
                foreach ($this->imagingCenter->documents as $document) {
                    Storage::disk('public')->delete($document);
                }
            }
            $documentPaths = [];
            foreach ($this->documents as $document) {
                $documentPaths[] = $document->store('documents', 'public');
            }
            $data['documents'] = $documentPaths;
        }

        $data['phone_numbers'] = array_filter($this->phone_numbers, fn ($phone) => !empty($phone));
        $data['working_days'] = array_keys(array_filter($this->working_days, fn ($value) => $value));

        // حذف doctor_ids از $data چون در جدول medical_centers ذخیره نمی‌شود
        unset($data['doctor_ids']);
        $data['service_ids'] = $this->service_ids;
        $this->imagingCenter->update($data);
        $this->imagingCenter->doctors()->sync($this->doctor_ids); // به‌روزرسانی رابطه چند به چند

        $this->dispatch('show-alert', type: 'success', message: 'مرکز تصویر برداری  با موفقیت به‌روزرسانی شد!');
        return redirect()->route('admin.panel.imaging-centers.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.imaging-centers.imaging-center-edit');
    }
}

<?php

namespace App\Livewire\Dr\Panel\DoctorsClinic;

use App\Models\MedicalCenter;
use App\Models\Zone;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Models\Doctor; // add

class DoctorClinicCreate extends Component
{
    public $name;
    public $title;
    public $phone_numbers = [''];
    public $secretary_phone;
    public $phone_number;
    public $province_id;
    public $city_id;
    public $postal_code;
    public $address;
    public $description;
    public $provinces;
    public $cities;
    public $prescription_tariff = null;
    public $type = 'policlinic';

    // متغیرهای جدید برای مدیریت تخصیص ساعات کاری
    public $showWorkHoursAssignment = false;
    public $createdClinicId = null;
    public $hasWorkHoursWithoutClinic = false;

    public function mount()
    {
        $zones = Cache::remember('zones', 86400, function () {
            return Zone::where('status', 1)
                ->orderBy('sort')
                ->get(['id', 'name', 'parent_id', 'level']);
        });
        $this->provinces = $zones->where('level', 1)->values();
        $this->cities = collect(); // مقداردهی اولیه صحیح

        // بررسی وجود ساعات کاری بدون مطب
        $this->checkWorkHoursWithoutClinic();
    }

    public function checkWorkHoursWithoutClinic()
    {
        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;

        if ($doctorId) {
            $this->hasWorkHoursWithoutClinic = \App\Models\DoctorWorkSchedule::where('doctor_id', $doctorId)
                ->whereNull('medical_center_id')
                ->where('is_working', true)
                ->whereNotNull('work_hours')
                ->exists();
        }
    }

    public function updatedProvinceId($value)
    {
        $this->cities = \App\Models\Zone::where('level', 2)->where('parent_id', $value)->get();
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

    public function store()
    {
        $validator = Validator::make($this->all(), [
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'phone_numbers' => 'required|array',
            'phone_numbers.*' => 'required|string|regex:/^09[0-9]{9}$/',
            'secretary_phone' => 'nullable|string|regex:/^09[0-9]{9}$/',
            'phone_number' => 'nullable|string|regex:/^09[0-9]{9}$/',
            'province_id' => 'required|exists:zone,id',
            'city_id' => 'required|exists:zone,id',
            'postal_code' => 'nullable|string|size:10',
            'address' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:1000',
            'prescription_tariff' => 'nullable|numeric|min:0',
            'type' => 'required|in:hospital,treatment_centers,clinic,imaging_center,laboratory,pharmacy,policlinic',
        ], [
            'name.required' => 'وارد کردن نام مطب الزامی است.',
            'phone_numbers.required' => 'وارد کردن حداقل یک شماره موبایل الزامی است.',
            'phone_numbers.*.required' => 'وارد کردن شماره موبایل الزامی است.',
            'province_id.required' => 'انتخاب استان الزامی است.',
            'city_id.required' => 'انتخاب شهر الزامی است.',
            'prescription_tariff.numeric' => 'تعرفه نسخه باید عددی باشد.',
            'prescription_tariff.min' => 'تعرفه نسخه نمی‌تواند منفی باشد.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-toastr', type: 'error', message: $validator->errors()->first());
            return;
        }

        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;

        // بررسی اینکه آیا مطب با همین نام در همین شهر قبلاً وجود دارد یا نه
        $existingClinic = MedicalCenter::where('name', $this->name)
            ->where('type', $this->type)
            ->where('province_id', $this->province_id)
            ->where('city_id', $this->city_id)
            ->first();

        if ($existingClinic) {
            $this->dispatch('show-toastr', type: 'error', message: 'مطب با این نام در این شهر قبلاً وجود دارد!');
            return;
        }

        // ایجاد مطب جدید
        $medicalCenter = MedicalCenter::create([
            'name' => $this->name,
            'title' => $this->title,
            'phone_numbers' => $this->phone_numbers,
            'secretary_phone' => $this->secretary_phone,
            'phone_number' => $this->phone_number,
            'province_id' => $this->province_id,
            'city_id' => $this->city_id,
            'postal_code' => $this->postal_code,
            'address' => $this->address,
            'description' => $this->description,
            'prescription_tariff' => $this->prescription_tariff,
            'type' => $this->type,
            'is_active' => false,
        ]);

        // Attach the doctor to the medical center
        $medicalCenter->doctors()->attach($doctorId);
        // Set selected clinic so WorkHours loads this clinic's schedules
        if ($doctorId) {
            Doctor::find($doctorId)?->setSelectedMedicalCenter($medicalCenter->id);
        }

        // Dispatch event for clinic creation
        $this->dispatch('clinicCreated', clinicId: $medicalCenter->id);

        // اگر ساعات کاری بدون مطب وجود دارد، نمایش کامپوننت تخصیص
        if ($this->hasWorkHoursWithoutClinic) {
            $this->createdClinicId = $medicalCenter->id;
            $this->showWorkHoursAssignment = true;
            $this->dispatch('show-toastr', type: 'success', message: 'مطب با موفقیت ایجاد شد! حالا می‌توانید ساعات کاری را تخصیص دهید.');
            // ارسال event برای اسکرول خودکار به قسمت تخصیص ساعات کاری
            $this->dispatch('clinic-created-successfully');
        } else {
            $this->dispatch('show-toastr', type: 'success', message: 'مطب با موفقیت ایجاد شد!');
            return redirect()->route('dr-clinic-management');
        }
    }

    /**
     * به‌روزرسانی مطب موجود
     */
    private function updateExistingClinic()
    {
        $medicalCenter = MedicalCenter::find($this->createdClinicId);
        if ($medicalCenter) {
            $medicalCenter->update([
                'name' => $this->name,
                'title' => $this->title,
                'phone_numbers' => $this->phone_numbers,
                'secretary_phone' => $this->secretary_phone,
                'phone_number' => $this->phone_number,
                'province_id' => $this->province_id,
                'city_id' => $this->city_id,
                'postal_code' => $this->postal_code,
                'address' => $this->address,
                'description' => $this->description,
                'prescription_tariff' => $this->prescription_tariff,
                'type' => $this->type,
            ]);
            // ensure selected clinic remains this one
            $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
            if ($doctorId) {
                Doctor::find($doctorId)?->setSelectedMedicalCenter($medicalCenter->id);
            }

            $this->dispatch('show-toastr', type: 'success', message: 'اطلاعات مطب با موفقیت به‌روزرسانی شد!');
            return redirect()->route('dr-clinic-management');
        }
    }

    /**
     * دریافت تعداد ساعات کاری بدون مطب
     */
    public function getWorkHoursWithoutClinicCount()
    {
        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;

        if ($doctorId) {
            return \App\Models\DoctorWorkSchedule::where('doctor_id', $doctorId)
                ->whereNull('medical_center_id')
                ->where('is_working', true)
                ->whereNotNull('work_hours')
                ->count();
        }

        return 0;
    }

    /**
     * تخصیص ساعات کاری به مطب جدید
     */
    public function assignWorkHours()
    {
        if (!$this->createdClinicId) {
            $this->dispatch('show-toastr', type: 'error', message: 'مطب ایجاد نشده است!');
            return;
        }

        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;

        if (!$doctorId) {
            $this->dispatch('show-toastr', type: 'error', message: 'پزشک شناسایی نشد!');
            return;
        }

        try {
            // به‌روزرسانی تمام ساعات کاری این پزشک که medical_center_id آنها null است
            $updatedRows = \App\Models\DoctorWorkSchedule::where('doctor_id', $doctorId)
                ->whereNull('medical_center_id')
                ->where('is_working', true)
                ->whereNotNull('work_hours')
                ->update(['medical_center_id' => $this->createdClinicId]);

            // set selected clinic to created clinic
            Doctor::find($doctorId)?->setSelectedMedicalCenter($this->createdClinicId);

            if ($updatedRows > 0) {
                $this->dispatch('show-toastr', type: 'success', message: "تعداد {$updatedRows} ساعات کاری با موفقیت به مطب تخصیص داده شد!");
            } else {
                $this->dispatch('show-toastr', type: 'info', message: 'هیچ ساعات کاری بدون مطب یافت نشد!');
            }

            // انتقال به صفحه مدیریت مطب
            return redirect()->route('dr-clinic-management');

        } catch (\Exception $e) {
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در تخصیص ساعات کاری: ' . $e->getMessage());
        }
    }

    public function skipWorkHoursAssignment()
    {
        $this->dispatch('show-toastr', type: 'info', message: 'تخصیص ساعات کاری رد شد. می‌توانید بعداً از صفحه مدیریت مطب انجام دهید.');
        return redirect()->route('dr-clinic-management');
    }

    /**
     * پاک کردن فرم و ایجاد مطب جدید
     */
    public function resetForm()
    {
        $this->reset([
            'name', 'title', 'phone_numbers', 'secretary_phone', 'phone_number',
            'province_id', 'city_id', 'postal_code', 'address', 'description',
            'prescription_tariff', 'showWorkHoursAssignment', 'createdClinicId'
        ]);
        $this->phone_numbers = [''];
        $this->type = 'policlinic';
        $this->cities = collect();
    }

    public function render()
    {
        return view('livewire.dr.panel.doctors-clinic.doctor-clinic-create');
    }
}

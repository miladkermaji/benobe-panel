<?php

namespace App\Livewire\Admin\Panel\Doctorservices;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorService;
use Livewire\Component;

class DoctorServiceCreate extends Component
{
    public $doctor_id = null;
    public $clinic_id;
    public $name;
    public $description;
    public $duration;
    public $price;
    public $discount;
    public $status = true;
    public $parent_id;

    public $doctors;
    public $clinics;
    public $parentServices;

    public function mount()
    {
        $this->doctors        = Doctor::all();
        $this->clinics        = Clinic::all();
        $this->parentServices = DoctorService::whereNull('parent_id')->get();
    }

    protected function rules()
    {
        return [
            'doctor_id'   => 'required|exists:doctors,id',
            'clinic_id'   => 'nullable|exists:clinics,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration'    => 'integer|min:0',
            'price'       => 'numeric|min:0',
            'discount'    => 'nullable|numeric|min:0|max:100',
            'status'      => 'boolean',
            'parent_id'   => 'nullable|exists:doctor_services,id',
        ];
    }

    protected $messages = [
        'doctor_id.required' => 'لطفاً یک پزشک انتخاب کنید.',
        'doctor_id.exists'   => 'پزشک انتخاب‌شده معتبر نیست.',
        'clinic_id.exists'   => 'کلینیک انتخاب‌شده معتبر نیست.',
        'name.required'      => 'لطفاً نام خدمت را وارد کنید.',
        'name.string'        => 'نام خدمت باید یک متن باشد.',
        'name.max'           => 'نام خدمت نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
        'description.string' => 'توضیحات باید یک متن باشد.',
        'duration.integer'   => 'مدت زمان باید یک عدد صحیح باشد.',
        'duration.min'       => 'مدت زمان نمی‌تواند منفی باشد.',
        'price.numeric'      => 'قیمت باید یک عدد باشد.',
        'price.min'          => 'قیمت نمی‌تواند منفی باشد.',
        'discount.numeric'   => 'تخفیف باید یک عدد باشد.',
        'discount.min'       => 'تخفیف نمی‌تواند منفی باشد.',
        'discount.max'       => 'تخفیف نمی‌تواند بیشتر از ۱۰۰ درصد باشد.',
        'parent_id.exists'   => 'خدمت مادر انتخاب‌شده معتبر نیست.',
    ];

    public function store()
    {
        // دیباگ دستی برای چک کردن مقدار
        if (empty($this->doctor_id) || $this->doctor_id === '' || $this->doctor_id === null) {
            $this->addError('doctor_id', 'لطفاً یک پزشک انتخاب کنید.');
            $this->dispatch('show-alert', type: 'error', message: 'لطفاً یک پزشک انتخاب کنید!');
            return;
        }

        $this->validate();

        DoctorService::create([
            'doctor_id'   => $this->doctor_id,
            'clinic_id'   => $this->clinic_id,
            'name'        => $this->name,
            'description' => $this->description,
            'duration'    => $this->duration,
            'price'       => $this->price,
            'discount'    => $this->discount,
            'status'      => $this->status,
            'parent_id'   => $this->parent_id,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'خدمت پزشک با موفقیت اضافه شد!');
        $this->reset(['doctor_id', 'clinic_id', 'name', 'description', 'duration', 'price', 'discount', 'status', 'parent_id']);
    }

    public function render()
    {
        return view('livewire.admin.panel.doctorservices.doctorservice-create');
    }
}

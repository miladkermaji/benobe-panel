<?php

namespace App\Livewire\Dr\Panel\Insurance;

use App\Models\Insurance;
use App\Models\DoctorInsurance;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class InsuranceComponent extends Component
{
    public $insurances;
    public $insurance_id;
    public $name;
    public $calculation_method = "0";
    public $appointment_price;
    public $insurance_percent;
    public $final_price;
    public $selectedClinicId;

    protected $rules = [
        'name'               => 'required|string|max:255',
        'calculation_method' => 'required|in:0,1,2,3,4',
        'appointment_price'  => 'nullable|integer',
        'insurance_percent'  => 'nullable|integer|min:0|max:100',
        'final_price'        => 'nullable|integer',
    ];

    protected $messages = [
        'name.required'              => 'نام بیمه الزامی است.',
        'name.string'                => 'نام بیمه باید یک رشته متنی باشد.',
        'name.max'                   => 'نام بیمه نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
        'calculation_method.required' => 'روش محاسبه الزامی است.',
        'calculation_method.in'       => 'روش محاسبه انتخاب‌شده معتبر نیست.',
        'appointment_price.integer'   => 'مبلغ نوبت باید یک عدد صحیح باشد.',
        'insurance_percent.integer'   => 'درصد سهم بیمه باید یک عدد صحیح باشد.',
        'insurance_percent.min'       => 'درصد سهم بیمه نمی‌تواند منفی باشد.',
        'insurance_percent.max'       => 'درصد سهم بیمه نمی‌تواند بیشتر از ۱۰۰ باشد.',
        'final_price.integer'         => 'مبلغ نهایی باید یک عدد صحیح باشد.',
    ];

    public function mount()
    {
        $this->selectedClinicId = request()->query('selectedClinicId', session('selectedClinicId', 'default'));
        session(['selectedClinicId' => $this->selectedClinicId]);
    }

    public function render()
    {
        $query = Insurance::query();

        if (Auth::guard('doctor')->check()) {
            $doctorId = Auth::guard('doctor')->user()->id;
            $query->whereHas('doctors', function ($q) use ($doctorId) {
                $q->where('doctor_id', $doctorId);
            });
        } elseif (Auth::guard('secretary')->check()) {
            $secretary = Auth::guard('secretary')->user();
            if ($secretary && $secretary->doctor) {
                $doctorId = $secretary->doctor->id;
                $query->whereHas('doctors', function ($q) use ($doctorId) {
                    $q->where('doctor_id', $doctorId);
                });
            } else {
                $this->dispatch('toast', message: 'شما به یک دکتر متصل نیستید.', type: 'error');
                $this->insurances = collect();
                return view('livewire.dr.panel.insurance.insurance-component');
            }
        } elseif (Auth::guard('manager')->check()) {
            $segments = request()->segments();
            $doctorId = end($segments);
            if (!is_numeric($doctorId)) {
                $this->dispatch('toast', message: 'لطفاً ابتدا یک دکتر را انتخاب کنید.', type: 'error');
                $this->insurances = collect();
                return view('livewire.dr.panel.insurance.insurance-component');
            }
            $query->whereHas('doctors', function ($q) use ($doctorId) {
                $q->where('doctor_id', $doctorId);
            });
        } else {
            $this->dispatch('toast', message: 'لطفاً ابتدا وارد سیستم شوید.', type: 'error');
            $this->insurances = collect();
            return view('livewire.dr.panel.insurance.insurance-component');
        }

        if ($this->selectedClinicId === 'default') {
            $query->whereNull('clinic_id');
        } else {
            $query->where('clinic_id', $this->selectedClinicId);
        }

        $query->where('calculation_method', $this->calculation_method);
        $this->insurances = $query->get();

        return view('livewire.dr.panel.insurance.insurance-component');
    }

    public function store()
    {
        $data = $this->validate();

        // تنظیم و محاسبه بر اساس روش انتخاب‌شده
        switch ($data['calculation_method']) {
            case '0': // مبلغ ثابت
                if (!$data['final_price']) {
                    $this->addError('final_price', 'مبلغ نهایی برای این روش الزامی است.');
                    return;
                }
                $data['appointment_price'] = null;
                $data['insurance_percent'] = null;
                break;

            case '1': // درصد از مبلغ نوبت
                if (!$data['appointment_price']) {
                    $this->addError('appointment_price', 'مبلغ نوبت برای این روش الزامی است.');
                    return;
                }
                if (!$data['insurance_percent']) {
                    $this->addError('insurance_percent', 'درصد سهم بیمه برای این روش الزامی است.');
                    return;
                }
                $data['final_price'] = $data['appointment_price'] * (1 - $data['insurance_percent'] / 100);
                break;

            case '2': // مبلغ ثابت + درصد
                if (!$data['final_price']) {
                    $this->addError('final_price', 'مبلغ نهایی برای این روش الزامی است.');
                    return;
                }
                if (!$data['insurance_percent']) {
                    $this->addError('insurance_percent', 'درصد سهم بیمه برای این روش الزامی است.');
                    return;
                }
                $data['appointment_price'] = $data['appointment_price'] ?? null; // اختیاری
                break;

            case '3': // فقط برای آمارگیری
                $data['appointment_price'] = $data['appointment_price'] ?? null;
                $data['insurance_percent'] = $data['insurance_percent'] ?? null;
                $data['final_price'] = $data['final_price'] ?? null;
                break;

            case '4': // پویا (مثال: درصد از نوبت + حداقل مبلغ)
                $data['appointment_price'] = $data['appointment_price'] ?? null;
                $data['insurance_percent'] = $data['insurance_percent'] ?? null;
                $data['final_price'] = $data['final_price'] ?? null;
                if ($data['appointment_price'] && $data['insurance_percent']) {
                    $calculated = $data['appointment_price'] * (1 - $data['insurance_percent'] / 100);
                    $data['final_price'] = $data['final_price'] ? max($data['final_price'], $calculated) : $calculated;
                }
                break;
        }

        if (Auth::guard('doctor')->check()) {
            $data['doctor_id'] = Auth::guard('doctor')->user()->id;
        } elseif (Auth::guard('secretary')->check()) {
            $secretary = Auth::guard('secretary')->user();
            if ($secretary && $secretary->doctor) {
                $data['doctor_id'] = $secretary->doctor->id;
            } else {
                $this->dispatch('toast', message: 'شما به یک دکتر متصل نیستید.', type: 'error');
                return;
            }
        } elseif (Auth::guard('manager')->check()) {
            $segments = request()->segments();
            $doctorId = end($segments);
            if (!is_numeric($doctorId)) {
                $this->dispatch('toast', message: 'لطفاً ابتدا یک دکتر را انتخاب کنید.', type: 'error');
                return;
            }
            $data['doctor_id'] = $doctorId;
        } else {
            $this->dispatch('toast', message: 'لطفاً ابتدا وارد سیستم شوید.', type: 'error');
            return;
        }

        $data['clinic_id'] = $this->selectedClinicId === 'default' ? null : $this->selectedClinicId;

        $insurance = Insurance::create([
            'clinic_id' => $data['clinic_id'],
            'name' => $data['name'],
            'calculation_method' => $data['calculation_method'],
            'appointment_price' => $data['appointment_price'],
            'insurance_percent' => $data['insurance_percent'],
            'final_price' => $data['final_price'],
        ]);

        DoctorInsurance::create([
            'doctor_id' => $data['doctor_id'],
            'insurance_id' => $insurance->id,
        ]);

        $this->dispatch('toast', message: 'بیمه جدید با موفقیت اضافه شد.');
        $this->resetFields();
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirmDelete', id: $id);
    }

    #[On('delete')]
    public function delete($id)
    {
        $insuranceId = is_array($id) ? $id['id'] : $id;
        $query = Insurance::where('id', $insuranceId);

        if (Auth::guard('doctor')->check()) {
            $doctorId = Auth::guard('doctor')->user()->id;
            $query->whereHas('doctors', function ($q) use ($doctorId) {
                $q->where('doctor_id', $doctorId);
            });
        } elseif (Auth::guard('secretary')->check()) {
            $secretary = Auth::guard('secretary')->user();
            if ($secretary && $secretary->doctor) {
                $doctorId = $secretary->doctor->id;
                $query->whereHas('doctors', function ($q) use ($doctorId) {
                    $q->where('doctor_id', $doctorId);
                });
            } else {
                $this->dispatch('toast', message: 'شما به یک دکتر متصل نیستید.', type: 'error');
                return;
            }
        } elseif (Auth::guard('manager')->check()) {
            $segments = request()->segments();
            $doctorId = end($segments);
            if (!is_numeric($doctorId)) {
                $this->dispatch('toast', message: 'لطفاً ابتدا یک دکتر را انتخاب کنید.', type: 'error');
                return;
            }
            $query->whereHas('doctors', function ($q) use ($doctorId) {
                $q->where('doctor_id', $doctorId);
            });
        } else {
            $this->dispatch('toast', message: 'لطفاً ابتدا وارد سیستم شوید.', type: 'error');
            return;
        }

        if ($this->selectedClinicId === 'default') {
            $query->whereNull('clinic_id');
        } else {
            $query->where('clinic_id', $this->selectedClinicId);
        }

        $insurance = $query->firstOrFail();
        DoctorInsurance::where('insurance_id', $insurance->id)->delete();
        $insurance->delete();

        $this->dispatch('toast', message: 'بیمه با موفقیت حذف شد.');
    }

    private function resetFields()
    {
        $this->insurance_id = null;
        $this->name = '';
        $this->calculation_method = "0";
        $this->appointment_price = null;
        $this->insurance_percent = null;
        $this->final_price = null;
    }
}

<?php

namespace App\Livewire\Dr\Panel\DoctorServices;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Dr\DoctorService;

class DoctorServices extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedRows = [];
    public $selectAll = false;

    public $selectedClinicId;
    public $dummy = 0; // property کمکی برای رفرش شدن کامپوننت

    public $editingService = null;
    public $name;
    public $doctor_id;
    public $duration;
    public $price;
    public $discount;
    public $status;
    public $parent_id;
    public $description;

    protected $paginationTheme = 'bootstrap';
    protected $listeners = [
        'updateDeleteButton' => 'refreshDeleteButton',
        'doDelete' => 'delete'
    ];

    public function mount()
    {
        // دریافت مقدار کلینیک از درخواست، مقدار پیش‌فرض "default" است.
        $this->selectedClinicId = request()->get('selectedClinicId', 'default');
    }

    public function toggleStatus($id)
    {
        $service = DoctorService::find($id);
        if ($service) {
            // استفاده از property ثابت برای selectedClinicId
            if ($this->selectedClinicId !== 'default' && $service->clinic_id != $this->selectedClinicId) {
                return;
            }
            $service->status = $service->status == 1 ? 0 : 1;
            $service->save();
            $this->dummy++; // تغییر dummy برای رفرش کامپوننت
            $this->dispatch('show-toastr', 
                type : 'success',
                message: 'وضعیت خدمت با موفقیت تغییر کرد.'
            );
        }
    }

    public function edit($id)
    {
        $service = DoctorService::find($id);
        if ($service) {
            if ($this->selectedClinicId !== 'default' && $service->clinic_id != $this->selectedClinicId) {
                return abort(403, 'Access denied');
            }
            $this->editingService = $service->id;
            $this->name = $service->name;
            $this->doctor_id = $service->doctor_id;
            $this->duration = $service->duration;
            $this->price = $service->price;
            $this->discount = $service->discount;
            $this->status = $service->status;
            $this->parent_id = $service->parent_id;
            $this->description = $service->description;
            $this->dispatch('show-edit-modal');
        }
    }

    public function updateService()
    {
        $this->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'name' => 'required|string|max:255',
            'duration' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|lte:price',
            'parent_id' => 'nullable|exists:doctor_services,id',
            'description' => 'nullable|string',
        ], [
            'doctor_id.required' => 'شناسه دکتر الزامی است.',
            'doctor_id.exists' => 'شناسه دکتر معتبر نمی‌باشد.',
            'name.required' => 'نام خدمت الزامی است.',
            'name.max' => 'نام خدمت نمی‌تواند بیش از 255 کاراکتر باشد.',
            'duration.required' => 'مدت زمان خدمت الزامی است.',
            'duration.integer' => 'مدت زمان خدمت باید به صورت عددی وارد شود.',
            'duration.min' => 'مدت زمان خدمت باید حداقل 1 دقیقه باشد.',
            'price.required' => 'قیمت خدمت الزامی است.',
            'price.numeric' => 'قیمت خدمت باید به صورت عددی وارد شود.',
            'price.min' => 'قیمت خدمت نمی‌تواند منفی باشد.',
            'discount.numeric' => 'تخفیف باید به صورت عددی وارد شود.',
            'discount.min' => 'تخفیف نمی‌تواند منفی باشد.',
            'discount.lte' => 'تخفیف نمی‌تواند از قیمت بیشتر باشد.',
            'parent_id.exists' => 'شناسه خدمت مادر نامعتبر است.',
        ]);

        $service = DoctorService::find($this->editingService);
        if ($service) {
            if ($this->selectedClinicId !== 'default' && $service->clinic_id != $this->selectedClinicId) {
                return abort(403, 'Access denied');
            }
            $service->update([
                'doctor_id' => $this->doctor_id,
                'name' => $this->name,
                'duration' => $this->duration,
                'price' => $this->price,
                'discount' => $this->discount,
                'status' => $this->status,
                'parent_id' => $this->parent_id,
                'description' => $this->description,
            ]);
            $this->dummy++; // برای رفرش شدن کامپوننت
            $this->dispatch('show-toastr', [
                'type' => 'success',
                'message' => 'خدمت با موفقیت به‌روزرسانی شد.'
            ]);
            $this->resetInputFields();
            $this->dispatch('hide-edit-modal');
        }
    }

    public function destroy(DoctorService $service)
    {
        if ($this->selectedClinicId !== 'default' && $service->clinic_id != $this->selectedClinicId) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $service->delete();
            return response()->json(['success' => 'خدمت با موفقیت حذف شد.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'خطا در حذف خدمت!', 'message' => $e->getMessage()], 500);
        }
    }

    public function resetInputFields()
    {
        $this->editingService = null;
        $this->name = '';
        $this->doctor_id = '';
        $this->duration = '';
        $this->price = '';
        $this->discount = '';
        $this->status = '';
        $this->parent_id = '';
        $this->description = '';
    }


    public function render()
    {
        // از property $this->selectedClinicId استفاده می‌کنیم
        $selectedClinicId = $this->selectedClinicId ?: 'default';

        if ($selectedClinicId !== 'default') {
            // فقط خدمت‌هایی که به آن کلینیک تعلق دارند
            $services = DoctorService::where('clinic_id', $selectedClinicId)
                ->whereNull('parent_id')
                ->with('children')
                ->get();
        } else {
            // وقتی کلینیک پیش‌فرض است، فقط خدمت‌هایی که clinic_id = null هستند نمایش داده شوند
            $services = DoctorService::whereNull('clinic_id')
                ->whereNull('parent_id')
                ->with('children')
                ->get();
        }

        return view('livewire.dr.panel.doctor-services.doctor-services', compact('services'));
    }






}

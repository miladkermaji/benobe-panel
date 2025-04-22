<?php

namespace App\Livewire\Dr\Panel\DoctorServices;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\DoctorService;
use App\Models\Insurance;
use App\Models\Clinic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DoctorServiceList extends Component
{
    use WithPagination;
    public $openServices = [];
    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'deleteDoctorServiceConfirmed' => 'deleteDoctorService',
        'clinicSelected',
        'setSelectedClinicId' => 'setSelectedClinicId',
        'refreshDoctorServiceList' => 'loadDoctorServices',
    ];

    public $perPage = 10;
    public $search = '';
    public $readyToLoad = false;
    public $selectedDoctorServices = [];
    public $selectAll = false;
    public $selectedClinicId = 'default';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
        Log::info('Mount called, waiting for selectedClinicId');
        // لود خدمات به setSelectedClinicId موکول می‌شود
    }

    public function setSelectedClinicId($clinicId)
    {
        $this->selectedClinicId = $clinicId ?? 'default';
        Log::info('setSelectedClinicId called with clinicId: ' . $this->selectedClinicId);

        // اعتبارسنجی clinic_id
        $doctorId = Auth::guard('doctor')->user()->id;
        if ($this->selectedClinicId !== 'default') {
            $clinic = Clinic::where('id', $this->selectedClinicId)
                ->where('doctor_id', $doctorId)
                ->first();
            if (!$clinic) {
                Log::warning('کلینیک انتخاب‌شده معتبر نیست: ' . $this->selectedClinicId);
                $this->selectedClinicId = 'default';
            }
        }

        $this->createDefaultVisitService();
        $this->readyToLoad = true; // اطمینان از تنظیم readyToLoad
        $this->resetPage();
        $this->dispatch('refreshList'); // event برای رفرش ویو
    }

    public function createDefaultVisitService()
    {
        $doctorId = Auth::guard('doctor')->user()->id;
        $clinicId = $this->selectedClinicId === 'default' ? null : $this->selectedClinicId;

        // بررسی وجود بیمه آزاد
        $freeInsurance = Insurance::where('id', 72)->first();
        if (!$freeInsurance) {
            Log::warning('بیمه آزاد با ID 72 یافت نشد.');
            return;
        }

        // بررسی وجود سرویس ویزیت پیش‌فرض
        $existingService = DoctorService::where('doctor_id', $doctorId)
            ->where('name', 'ویزیت')
            ->where('insurance_id', 72)
            ->where('clinic_id', $clinicId)
            ->first();

        if (!$existingService) {
            DoctorService::create([
                'doctor_id' => $doctorId,
                'clinic_id' => $clinicId,
                'insurance_id' => 72,
                'name' => 'ویزیت',
                'description' => 'ویزیت پیش‌فرض',
                'duration' => 15,
                'price' => 100000,
                'discount' => 0,
                'status' => 1,
                'parent_id' => null,
            ]);
            Log::info('سرویس ویزیت پیش‌فرض برای پزشک با ID ' . $doctorId . ' ایجاد شد.');
        }
    }

    public function loadDoctorServices()
    {
        $this->readyToLoad = true;
        Log::info('loadDoctorServices called with selectedClinicId: ' . $this->selectedClinicId);
        $this->dispatch('refreshList'); // event برای رفرش ویو
    }

    public function clinicSelected($clinicId = 'default')
    {
        $this->selectedClinicId = $clinicId;
        Log::info('clinicSelected called with clinicId: ' . $this->selectedClinicId);

        // اعتبارسنجی clinic_id
        $doctorId = Auth::guard('doctor')->user()->id;
        if ($this->selectedClinicId !== 'default') {
            $clinic = Clinic::where('id', $this->selectedClinicId)
                ->where('doctor_id', $doctorId)
                ->first();
            if (!$clinic) {
                Log::warning('کلینیک انتخاب‌شده معتبر نیست: ' . $this->selectedClinicId);
                $this->selectedClinicId = 'default';
            }
        }

        $this->createDefaultVisitService();
        $this->readyToLoad = true;
        $this->resetPage();
        $this->dispatch('refreshList'); // event برای رفرش ویو
    }

private function getDoctorServicesQuery()
{
    $doctorId = Auth::guard('doctor')->user()->id;

    // کوئری اصلی برای دریافت بیمه‌ها و خدمات مرتبط
    $query = Insurance::with(['doctorServices' => function ($query) use ($doctorId) {
        $query->where('doctor_id', $doctorId)
              ->where(function ($q) {
                  $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
              });
        // اعمال فیلتر clinic_id در بخش with
        if ($this->selectedClinicId === 'default') {
            $query->whereNull('clinic_id');
        } else {
            $query->where('clinic_id', $this->selectedClinicId);
        }
        $query->with('children');
    }])
    ->whereHas('doctorServices', function ($query) use ($doctorId) {
        $query->where('doctor_id', $doctorId)
              ->where(function ($q) {
                  $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
              });
        // اعمال فیلتر clinic_id در بخش whereHas
        if ($this->selectedClinicId === 'default') {
            $query->whereNull('clinic_id');
        } else {
            $query->where('clinic_id', $this->selectedClinicId);
        }
    });

    // دریافت نتایج با صفحه‌بندی
    $paginated = $query->paginate($this->perPage);

    // لاگ برای دیباگ
    Log::info('Selected Clinic ID: ' . $this->selectedClinicId);
    Log::info('Doctor ID: ' . $doctorId);
    Log::info('Query Results: ', $paginated->toArray());

    // تنظیم پراپرتی isOpen برای خدمات
    $paginated->getCollection()->each(function ($insurance) {
        $insurance->doctorServices->each(function ($service) {
            $service->isOpen = in_array($service->id, $this->openServices);
        });
    });

    return $paginated;
}

    public function toggleStatus($id)
    {
        $item = DoctorService::findOrFail($id);
        $item->update(['status' => !$item->status]);
        $this->dispatch('show-alert', type: $item->status ? 'success' : 'info', message: $item->status ? 'سرویس فعال شد!' : 'سرویس غیرفعال شد!');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteDoctorService($id)
    {
        $item = DoctorService::findOrFail($id);
        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'سرویس با موفقیت حذف شد!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = $this->getDoctorServicesQuery()->pluck('doctorServices')->flatten()->pluck('id')->toArray();
        $this->selectedDoctorServices = $value ? $currentPageIds : [];
    }

    public function updatedSelectedDoctorServices()
    {
        $currentPageIds = $this->getDoctorServicesQuery()->pluck('doctorServices')->flatten()->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedDoctorServices) && count(array_diff($currentPageIds, $this->selectedDoctorServices)) === 0;
    }

    public function toggleChildren($id)
    {
        if (in_array($id, $this->openServices)) {
            $this->openServices = array_diff($this->openServices, [$id]);
        } else {
            $this->openServices[] = $id;
        }
    }

    public function deleteSelected()
    {
        if (empty($this->selectedDoctorServices)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ سرویسی انتخاب نشده است.');
            return;
        }

        DoctorService::whereIn('id', $this->selectedDoctorServices)->delete();
        $this->selectedDoctorServices = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'سرویس‌های انتخاب‌شده با موفقیت حذف شدند!');
    }

    public function render()
    {
        $items = $this->readyToLoad ? $this->getDoctorServicesQuery() : null;
        Log::info('Render called, readyToLoad: ' . ($this->readyToLoad ? 'true' : 'false'));

        return view('livewire.dr.panel.doctor-services.doctor-service-list', [
            'insurances' => $items,
        ]);
    }
}

<?php

namespace App\Livewire\Dr\Panel\DoctorServices;

use App\Models\Clinic;
use App\Models\Service;
use Livewire\Component;
use App\Models\Insurance;
use Livewire\WithPagination;
use App\Models\DoctorService;
use App\Traits\HasSelectedClinic;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class DoctorServiceList extends Component
{
    use WithPagination,HasSelectedClinic;
    public $openServices = [];
    public $openInsurances = [];
    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'deleteDoctorServiceConfirmed' => 'deleteDoctorService',
        'clinicSelected',
        'setSelectedClinicId' => 'setSelectedClinicId',
        'refreshDoctorServiceList' => 'loadDoctorServices',
    ];

    public $perPage = 100;
    public $search = '';
    public $readyToLoad = false;
    public $selectedDoctorServices = [];
    public $selectAll = false;
    public $selectedClinicId =  'default';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);

        $this->selectedClinicId = $this->getSelectedClinicId();

        // لود خدمات به setSelectedClinicId موکول می‌شود
    }

    public function setSelectedClinicId($clinicId)
    {
        $this->selectedClinicId =
$this->getSelectedClinicId()
 ?? 'default';

        // اعتبارسنجی clinic_id
        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
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
        $this->dispatch('refreshList');
    }

    public function createDefaultVisitService()
    {
        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
        $clinicId = $this->selectedClinicId === 'default' ? null : $this->selectedClinicId;

        // بررسی وجود هرگونه خدمت برای پزشک
        $hasServices = DoctorService::where('doctor_id', $doctorId)->exists();

        if (!$hasServices) {
            // بررسی وجود بیمه آزاد
            $freeInsurance = Insurance::where('id', 72)->first();
            if (!$freeInsurance) {
                Log::warning('بیمه آزاد با ID 72 یافت نشد.');
                return;
            }

            // پیدا کردن یا ایجاد خدمت "ویزیت" در جدول Service
            $visitService = Service::where('name', 'ویزیت')->first();
            if (!$visitService) {
                $visitService = Service::create([
                    'name' => 'ویزیت',
                    'description' => 'ویزیت عمومی پزشک',
                    'status' => true,
                ]);
                Log::info('خدمت ویزیت در جدول Service ایجاد شد.');
            }

            // ایجاد سرویس ویزیت پیش‌فرض
            DoctorService::create([
                'doctor_id' => $doctorId,
                'clinic_id' => $clinicId,
                'insurance_id' => 72,
                'service_id' => $visitService->id,
                'name' => 'ویزیت',
                'description' => 'ویزیت پیش‌فرض',
                'duration' => 15,
                'price' => 100000,
                'discount' => 0,
                'status' => 1,
                'parent_id' => null,
            ]);
            Log::info('سرویس ویزیت پیش‌فرض برای پزشک با ID ' . $doctorId . ' ایجاد شد.');
        } else {
            Log::info('ویزیت پیش‌فرض ایجاد نشد چون حداقل یک خدمت برای پزشک با ID ' . $doctorId . ' وجود دارد.');
        }
    }

    public function loadDoctorServices()
    {
        $this->readyToLoad = true;
        Log::info('loadDoctorServices called with selectedClinicId: ' . $this->selectedClinicId);
        $this->dispatch('refreshList');
    }

    public function clinicSelected($clinicId = 'default')
    {
        $this->selectedClinicId = $clinicId;
        Log::info('clinicSelected called with clinicId: ' . $this->selectedClinicId);

        // اعتبارسنجی clinic_id
        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
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
        $this->dispatch('refreshList');
    }

    private function getDoctorServicesQuery()
    {
        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;

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

    public function toggleInsurance($insuranceId)
    {
        if (in_array($insuranceId, $this->openInsurances)) {
            $this->openInsurances = array_diff($this->openInsurances, [$insuranceId]);
        } else {
            $this->openInsurances[] = $insuranceId;
        }
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

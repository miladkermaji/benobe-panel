<?php

namespace App\Livewire\Dr\Panel\DoctorServices;

use App\Models\MedicalCenter;
use App\Models\Service;
use App\Models\Insurance;
use Livewire\Component;
use App\Models\DoctorService;
use App\Traits\HasSelectedClinic;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class DoctorServiceList extends Component
{
    use WithPagination;
    use HasSelectedClinic;

    public $openServices = [];
    protected $paginationTheme = 'bootstrap';
    protected $listeners = [
        'deleteDoctorServiceConfirmed' => 'deleteDoctorService',
        'clinicSelected',
        'setSelectedClinicId' => 'setSelectedClinicId',
        'refreshDoctorServiceList' => 'loadDoctorServices',
        'deleteSelectedConfirmed' => 'deleteSelected',
    ];
    public $perPage = 100;
    public $search = '';
    public $readyToLoad = false;
    public $selectedDoctorServices = [];
    public $selectAll = false;
    public $selectedClinicId = 'default';
    public $groupAction = '';
    protected $queryString = ['search' => ['except' => '']];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
        $this->selectedClinicId = $this->getSelectedMedicalCenterId();
        // باز بودن پیش‌فرض در دسکتاپ
        if ($this->isDesktop()) {
            $this->openServices = Service::whereHas('doctorServices', function ($query) {
                $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
                $query->where('doctor_id', $doctorId);
            })->pluck('id')->toArray();
        }
    }

    private function isDesktop()
    {
        // اگر نیاز به تشخیص دقیق‌تر داشتی، می‌توانی از user-agent یا کوکی استفاده کنی
        // اینجا فرض می‌کنیم همیشه دسکتاپ است (برای تست)
        return true;
    }

    public function setSelectedClinicId($clinicId)
    {
        $this->selectedClinicId = $this->getSelectedMedicalCenterId() ?? 'default';
        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
        if ($this->selectedClinicId !== 'default') {
            $clinic = MedicalCenter::where('id', $this->selectedClinicId)
                ->whereHas('doctors', function ($query) use ($doctorId) {
                    $query->where('doctor_id', $doctorId);
                })
                ->first();
            if (!$clinic) {
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
        $hasServices = DoctorService::where('doctor_id', $doctorId)->exists();
        if (!$hasServices) {
            $freeInsurance = Insurance::where('id', 72)->first();
            if (!$freeInsurance) {
                return;
            }
            $visitService = Service::where('name', 'ویزیت')->first();
            if (!$visitService) {
                $visitService = Service::create([
                    'name' => 'ویزیت',
                    'description' => 'ویزیت عمومی پزشک',
                    'status' => true,
                ]);
            }
            DoctorService::create([
                'doctor_id' => $doctorId,
                'medical_center_id' => $clinicId,
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
        } else {

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
        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
        if ($this->selectedClinicId !== 'default') {
            $clinic = MedicalCenter::where('id', $this->selectedClinicId)
                ->whereHas('doctors', function ($query) use ($doctorId) {
                    $query->where('doctor_id', $doctorId);
                })->first();
            if (!$clinic) {
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
        $query = Service::with(['doctorServices' => function ($query) use ($doctorId) {
            $query->where('doctor_id', $doctorId)
                  ->where(function ($q) {
                      $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                  })
                  ->with('insurance');
            if ($this->selectedClinicId === 'default') {
                $query->whereNull('medical_center_id');
            } else {
                $query->where('medical_center_id', $this->selectedClinicId);
            }
        }])
        ->whereHas('doctorServices', function ($query) use ($doctorId) {
            $query->where('doctor_id', $doctorId)
                  ->where(function ($q) {
                      $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                  });
            if ($this->selectedClinicId === 'default') {
                $query->whereNull('medical_center_id');
            } else {
                $query->where('medical_center_id', $this->selectedClinicId);
            }
        })
        ->where('status', true);

        $paginated = $query->paginate($this->perPage);


        $paginated->getCollection()->each(function ($service) {
            $service->isOpen = in_array($service->id, $this->openServices);
            $service->doctorServices->each(function ($doctorService) {
                $doctorService->isOpen = in_array($doctorService->id, $this->openServices);
            });
        });

        return $paginated;
    }

    public function toggleStatus($id)
    {
        $item = DoctorService::findOrFail($id);
        $item->update(['status' => !$item->status]);
        $this->dispatch('show-alert', type: $item->status ? 'success' : 'info', message: $item->status ? 'خدمت فعال شد!' : 'خدمت غیرفعال شد!');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteDoctorService($id)
    {
        $item = DoctorService::findOrFail($id);
        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'خدمت با موفقیت حذف شد!');
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
        $services = $this->getDoctorServicesQuery();
        foreach ($services as $service) {
            $doctorServiceIds = collect($service->doctorServices)->pluck('id')->toArray();
            $parentKey = 'service-' . $service->id;
            // اگر همه بیمه‌های این خدمت انتخاب شده باشند، پدر را هم انتخاب کن
            if (!array_diff($doctorServiceIds, $this->selectedDoctorServices) && count($doctorServiceIds)) {
                if (!in_array($parentKey, $this->selectedDoctorServices)) {
                    $this->selectedDoctorServices[] = $parentKey;
                }
            } else {
                // اگر حتی یکی انتخاب نشده بود، پدر را بردار
                if (($key = array_search($parentKey, $this->selectedDoctorServices)) !== false) {
                    unset($this->selectedDoctorServices[$key]);
                }
            }
        }
        // منطق انتخاب همه
        $currentPageIds = $services->pluck('doctorServices')->flatten()->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedDoctorServices) && count(array_diff($currentPageIds, $this->selectedDoctorServices)) === 0;
    }

    public function toggleParentCheckbox($serviceId)
    {
        $parentKey = 'service-' . $serviceId;
        $services = $this->getDoctorServicesQuery();
        $service = $services->where('id', $serviceId)->first();
        if (!$service) {
            return;
        }
        $doctorServiceIds = collect($service->doctorServices)->pluck('id')->toArray();
        if (count($doctorServiceIds) === 0) {
            return;
        }

        if (count(array_intersect($doctorServiceIds, $this->selectedDoctorServices)) === count($doctorServiceIds)) {
            // اگر همه انتخاب بودن، یعنی کاربر می‌خواهد همه را بردارد
            $this->selectedDoctorServices = array_diff($this->selectedDoctorServices, $doctorServiceIds);
        } else {
            // اگر حتی یکی انتخاب نبود، همه را انتخاب کن
            $this->selectedDoctorServices = array_unique(array_merge($this->selectedDoctorServices, $doctorServiceIds));
        }
        // آرایه را ری‌ست کن تا Livewire متوجه تغییر شود
        $this->selectedDoctorServices = array_values($this->selectedDoctorServices);
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
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ خدمتی انتخاب نشده است.');
            return;
        }

        // فیلتر کردن مقادیر برای اطمینان از اینکه فقط اعداد صحیح هستند
        $validIds = array_filter($this->selectedDoctorServices, function ($id) {
            return is_numeric($id) && is_int((int)$id);
        });

        if (empty($validIds)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ شناسه معتبری برای حذف یافت نشد.');
            return;
        }

        DoctorService::whereIn('id', $validIds)->delete();
        $this->selectedDoctorServices = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'خدمات انتخاب‌شده با موفقیت حذف شدند!');
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedDoctorServices)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ خدمتی انتخاب نشده است.');
            return;
        }

        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفا یک عملیات را انتخاب کنید.');
            return;
        }

        switch ($this->groupAction) {
            case 'delete':
                $this->deleteSelected();
                break;
            case 'status_active':
                $this->updateStatus(true);
                break;
            case 'status_inactive':
                $this->updateStatus(false);
                break;
        }

        $this->groupAction = '';
        $this->selectedDoctorServices = [];
        $this->selectAll = false;
    }

    private function updateStatus($status)
    {
        // فیلتر کردن مقادیر برای اطمینان از اینکه فقط اعداد صحیح هستند
        $validIds = array_filter($this->selectedDoctorServices, function ($id) {
            return is_numeric($id) && is_int((int)$id);
        });

        if (empty($validIds)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ شناسه معتبری برای به‌روزرسانی یافت نشد.');
            return;
        }

        DoctorService::whereIn('id', $validIds)
            ->update(['status' => $status]);

        $this->selectedDoctorServices = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت خدمات انتخاب‌شده با موفقیت تغییر کرد.');
    }

    public function render()
    {
        $services = $this->readyToLoad ? $this->getDoctorServicesQuery() : null;
        return view('livewire.dr.panel.doctor-services.doctor-service-list', ['services' => $services]);
    }
}

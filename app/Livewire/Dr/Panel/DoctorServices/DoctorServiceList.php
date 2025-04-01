<?php

namespace App\Livewire\Dr\Panel\DoctorServices;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\DoctorService;

class DoctorServiceList extends Component
{
    use WithPagination;
    public $openServices = [];
    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteDoctorServiceConfirmed' => 'deleteDoctorService'];

    public $perPage = 10;
    public $search = '';
    public $readyToLoad = false;
    public $selectedDoctorServices = [];
    public $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadDoctorServices()
    {
        $this->readyToLoad = true;
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
        $currentPageIds = $this->getDoctorServicesQuery()->pluck('id')->toArray();
        $this->selectedDoctorServices = $value ? $currentPageIds : [];
    }

    public function updatedSelectedDoctorServices()
    {
        $currentPageIds = $this->getDoctorServicesQuery()->pluck('id')->toArray();
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

   private function getDoctorServicesQuery()
{
    // پیدا کردن تمام سرویس‌هایی که با جستجو مطابقت دارند (والد یا زیرمجموعه)
    $matchingServiceIds = DoctorService::where('name', 'like', '%' . $this->search . '%')
        ->orWhere('description', 'like', '%' . $this->search . '%')
        ->pluck('id')
        ->toArray();

    // کوئری اصلی برای سرویس‌های سطح بالا با صفحه‌بندی
    $query = DoctorService::with('children')
        ->whereNull('parent_id')
        ->where(function ($query) use ($matchingServiceIds) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhereHas('children', function ($subQuery) use ($matchingServiceIds) {
                      $subQuery->whereIn('id', $matchingServiceIds)
                               ->orWhere('name', 'like', '%' . $this->search . '%')
                               ->orWhere('description', 'like', '%' . $this->search . '%');
                  });
        });

    // اعمال صفحه‌بندی
    $paginated = $query->paginate($this->perPage);

    // اضافه کردن ویژگی isOpen به هر خدمت در نتایج صفحه‌بندی‌شده
    $paginated->getCollection()->each(function ($service) {
        $service->isOpen = in_array($service->id, $this->openServices);
    });

    return $paginated;
}

    public function render()
    {
        $items = $this->readyToLoad ? $this->getDoctorServicesQuery() : null;

        return view('livewire.dr.panel.doctor-services.doctor-service-list', [
            'doctorServices' => $items,
        ]);
    }
}

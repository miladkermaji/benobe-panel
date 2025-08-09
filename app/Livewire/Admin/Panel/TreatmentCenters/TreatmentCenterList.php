<?php

namespace App\Livewire\Admin\Panel\TreatmentCenters;

use Livewire\Component;
use App\Models\Insurance;
use App\Models\Specialty;
use Livewire\WithPagination;
use App\Models\MedicalCenter;
use App\Models\TreatmentCenter;
use Illuminate\Support\Facades\Storage;

class TreatmentCenterList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteTreatmentCenterConfirmed' => 'deleteTreatmentCenter', 'toggleStatusConfirmed' => 'toggleStatusConfirmed'];

    public $perPage = 50;
    public $search = '';
    public $readyToLoad = false;
    public $selectedTreatmentCenters = [];
    public $selectAll = false;
    public $groupAction = '';
    public $statusFilter = '';
    public $applyToAllFiltered = false;
    public $totalFilteredCount = 0;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadTreatmentCenters()
    {
        $this->readyToLoad = true;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = $this->getTreatmentCentersQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        $this->selectedTreatmentCenters = $value ? $currentPageIds : [];
    }

    public function updatedSelectedTreatmentCenters()
    {
        $currentPageIds = $this->getTreatmentCentersQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedTreatmentCenters) && count(array_diff($currentPageIds, $this->selectedTreatmentCenters)) === 0;
    }

    public function confirmToggleStatus($id)
    {
        $item = MedicalCenter::find($id);
        if (!$item) {
            $this->dispatch('show-alert', type: 'error', message: 'درمانگاه یافت نشد.');
            return;
        }
        $treatmentCenterName = $item->name;
        $action = $item->is_active ? 'غیرفعال کردن' : 'فعال کردن';

        $this->dispatch('confirm-toggle-status', id: $id, name: $treatmentCenterName, action: $action);
    }

    public function toggleStatusConfirmed($id)
    {
        $item = MedicalCenter::find($id);
        if (!$item) {
            $this->dispatch('show-alert', type: 'error', message: 'درمانگاه یافت نشد.');
            return;
        }

        $newStatus = !$item->is_active;
        $item->update(['is_active' => $newStatus]);

        if ($newStatus) {
            // ارسال پیامک فعال‌سازی
            if ($item->phone_number) {
                $message = "مرکز درمانی گرامی، حساب کاربری شما در سیستم فعال شد. می‌توانید از طریق لینک زیر وارد پنل خود شوید: " . route('dr.auth.login-register-form', $item->id);
                $activeGateway = \Modules\SendOtp\App\Models\SmsGateway::where('is_active', true)->first();
                $gatewayName = $activeGateway ? $activeGateway->name : 'pishgamrayan';
                $templateId = ($gatewayName === 'pishgamrayan') ? 100254 : null;

                \App\Jobs\SendSmsNotificationJob::dispatch(
                    $message,
                    [$item->phone_number],
                    $templateId,
                    [$item->name]
                )->delay(now()->addSeconds(5));
            }
            $this->dispatch('show-alert', type: 'success', message: 'درمانگاه فعال شد و پیامک فعال‌سازی ارسال شد!');
        } else {
            // ارسال پیامک غیرفعال‌سازی
            if ($item->phone_number) {
                $message = "مرکز درمانی گرامی، حساب کاربری شما در سیستم غیرفعال شد. برای اطلاعات بیشتر تماس بگیرید.";
                \App\Jobs\SendSmsNotificationJob::dispatch(
                    $message,
                    [$item->phone_number]
                )->delay(now()->addSeconds(5));
            }
            $this->dispatch('show-alert', type: 'info', message: 'درمانگاه غیرفعال شد!');
        }
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteTreatmentCenter($id)
    {
        $item = MedicalCenter::findOrFail($id);
        if ($item->avatar) {
            Storage::disk('public')->delete($item->avatar);
        }
        if ($item->documents) {
            foreach ($item->documents as $document) {
                Storage::disk('public')->delete($document);
            }
        }
        if ($item->galleries) {
            foreach ($item->galleries as $gallery) {
                Storage::disk('public')->delete($gallery['image_path']);
            }
        }
        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'درمانگاه حذف شد!');
    }

    public function deleteSelected($allFiltered = null)
    {
        if ($allFiltered === 'allFiltered') {
            $query = $this->getTreatmentCentersQuery();
            $query->delete();
            $this->selectedTreatmentCenters = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            $this->dispatch('show-alert', type: 'success', message: 'همه درمانگاه‌های فیلترشده حذف شدند!');
            return;
        }
        if (empty($this->selectedTreatmentCenters)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ درمانگاهی انتخاب نشده است.');
            return;
        }
        MedicalCenter::whereIn('id', $this->selectedTreatmentCenters)->delete();
        $this->selectedTreatmentCenters = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'درمانگاه‌های انتخاب شده حذف شدند!');
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedTreatmentCenters) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ درمانگاهی انتخاب نشده است.');
            return;
        }
        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفا یک عملیات را انتخاب کنید.');
            return;
        }
        if ($this->applyToAllFiltered) {
            $query = $this->getTreatmentCentersQuery();
            switch ($this->groupAction) {
                case 'delete':
                    $this->dispatch('confirm-delete-selected', ['allFiltered' => true]);
                    return;
                case 'status_active':
                    $items = $query->get();
                    $query->update(['is_active' => true]);
                    // ارسال پیامک برای همه آیتم‌های فعال شده
                    foreach ($items as $item) {
                        if ($item->phone_number) {
                            $message = "مرکز درمانی گرامی، حساب کاربری شما در سیستم فعال شد. می‌توانید از طریق لینک زیر وارد پنل خود شوید: " . route('dr.auth.login-register-form', $item->id);
                            $activeGateway = \Modules\SendOtp\App\Models\SmsGateway::where('is_active', true)->first();
                            $gatewayName = $activeGateway ? $activeGateway->name : 'pishgamrayan';
                            $templateId = ($gatewayName === 'pishgamrayan') ? 100254 : null;

                            \App\Jobs\SendSmsNotificationJob::dispatch(
                                $message,
                                [$item->phone_number],
                                $templateId,
                                [$item->name]
                            )->delay(now()->addSeconds(5));
                        }
                    }
                    $this->dispatch('show-alert', type: 'success', message: 'همه درمانگاه‌های فیلترشده فعال شدند و پیامک ارسال شد!');
                    break;
                case 'status_inactive':
                    $items = $query->get();
                    $query->update(['is_active' => false]);
                    // ارسال پیامک برای همه آیتم‌های غیرفعال شده
                    foreach ($items as $item) {
                        if ($item->phone_number) {
                            $message = "مرکز درمانی گرامی، حساب کاربری شما در سیستم غیرفعال شد. برای اطلاعات بیشتر تماس بگیرید.";
                            \App\Jobs\SendSmsNotificationJob::dispatch(
                                $message,
                                [$item->phone_number]
                            )->delay(now()->addSeconds(5));
                        }
                    }
                    $this->dispatch('show-alert', type: 'success', message: 'همه درمانگاه‌های فیلترشده غیرفعال شدند و پیامک ارسال شد!');
                    break;
            }
            $this->selectedTreatmentCenters = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            return;
        }
        switch ($this->groupAction) {
            case 'delete':
                $this->dispatch('confirm-delete-selected', ['allFiltered' => false]);
                break;
            case 'status_active':
                $this->updateStatus(true);
                break;
            case 'status_inactive':
                $this->updateStatus(false);
                break;
        }
        $this->groupAction = '';
    }

    private function updateStatus($status)
    {
        $items = MedicalCenter::whereIn('id', $this->selectedTreatmentCenters)->get();
        foreach ($items as $item) {
            $oldStatus = $item->is_active;
            $item->update(['is_active' => $status]);

            if ($status && !$oldStatus) {
                // ارسال پیامک فعال‌سازی
                if ($item->phone_number) {
                    $message = "مرکز درمانی گرامی، حساب کاربری شما در سیستم فعال شد. می‌توانید از طریق لینک زیر وارد پنل خود شوید: " . route('dr.auth.login-register-form', $item->id);
                    $activeGateway = \Modules\SendOtp\App\Models\SmsGateway::where('is_active', true)->first();
                    $gatewayName = $activeGateway ? $activeGateway->name : 'pishgamrayan';
                    $templateId = ($gatewayName === 'pishgamrayan') ? 100254 : null;

                    \App\Jobs\SendSmsNotificationJob::dispatch(
                        $message,
                        [$item->phone_number],
                        $templateId,
                        [$item->name]
                    )->delay(now()->addSeconds(5));
                }
            } elseif (!$status && $oldStatus) {
                // ارسال پیامک غیرفعال‌سازی
                if ($item->phone_number) {
                    $message = "مرکز درمانی گرامی، حساب کاربری شما در سیستم غیرفعال شد. برای اطلاعات بیشتر تماس بگیرید.";
                    \App\Jobs\SendSmsNotificationJob::dispatch(
                        $message,
                        [$item->phone_number]
                    )->delay(now()->addSeconds(5));
                }
            }
        }
        $this->selectedTreatmentCenters = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت درمانگاه‌های انتخاب‌شده با موفقیت تغییر کرد.');
    }

    protected function getTreatmentCentersQuery()
    {
        return MedicalCenter::where('type', 'treatment_centers')
            ->when($this->search, function ($query) {
                $search = trim($this->search);
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('description', 'like', "%$search%")
                      ->orWhere('title', 'like', "%$search%")
                      ->orWhereHas('doctors', function ($qq) use ($search) {
                          $qq->where('first_name', 'like', "%$search%")
                             ->orWhere('last_name', 'like', "%$search%") ;
                      })
                      ->orWhereHas('province', function ($qq) use ($search) {
                          $qq->where('name', 'like', "%$search%") ;
                      })
                      ->orWhereHas('city', function ($qq) use ($search) {
                          $qq->where('name', 'like', "%$search%") ;
                      });
                });
            })
            ->when($this->statusFilter, function ($query) {
                if ($this->statusFilter === 'active') {
                    $query->where('is_active', true);
                } elseif ($this->statusFilter === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->orderBy('created_at', 'desc');
    }

    public function render()
    {
        $this->totalFilteredCount = $this->readyToLoad ? $this->getTreatmentCentersQuery()->count() : 0;
        $items = $this->readyToLoad ? $this->getTreatmentCentersQuery()->paginate($this->perPage) : null;
        $specialties = Specialty::pluck('name', 'id');
        $insurances = Insurance::pluck('name', 'id');
        $services = \App\Models\Service::pluck('name', 'id');
        return view('livewire.admin.panel.treatment-centers.treatment-centers-list', [
            'treatmentCenters' => $items,
            'specialties' => $specialties,
            'insurances' => $insurances,
            'services' => $services,
            'totalFilteredCount' => $this->totalFilteredCount,
        ]);
    }
}

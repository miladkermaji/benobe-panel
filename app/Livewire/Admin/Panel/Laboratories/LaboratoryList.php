<?php

namespace App\Livewire\Admin\Panel\Laboratories;

use Livewire\Component;
use App\Models\Insurance;
use App\Models\Specialty;
use App\Models\Laboratory;
use Livewire\WithPagination;
use App\Models\MedicalCenter;
use Illuminate\Support\Facades\Storage;

class LaboratoryList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteLaboratoryConfirmed' => 'deleteLaboratory', 'toggleStatusConfirmed' => 'toggleStatusConfirmed'];

    public $perPage = 50;
    public $search = '';
    public $readyToLoad = false;
    public $selectedLaboratories = [];
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

    public function loadLaboratories()
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
        $currentPageIds = $this->getLaboratoriesQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        $this->selectedLaboratories = $value ? $currentPageIds : [];
    }

    public function updatedSelectedLaboratories()
    {
        $currentPageIds = $this->getLaboratoriesQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedLaboratories) && count(array_diff($currentPageIds, $this->selectedLaboratories)) === 0;
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteLaboratory($id)
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
        $this->dispatch('show-alert', type: 'success', message: 'آزمایشگاه حذف شد!');
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedLaboratories) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ آزمایشگاهی انتخاب نشده است.');
            return;
        }
        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفا یک عملیات را انتخاب کنید.');
            return;
        }
        if ($this->applyToAllFiltered) {
            $query = $this->getLaboratoriesQuery();
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
                    $this->dispatch('show-alert', type: 'success', message: 'همه آزمایشگاه‌های فیلترشده فعال شدند و پیامک ارسال شد!');
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
                    $this->dispatch('show-alert', type: 'success', message: 'همه آزمایشگاه‌های فیلترشده غیرفعال شدند و پیامک ارسال شد!');
                    break;
            }
            $this->selectedLaboratories = [];
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
        $items = MedicalCenter::whereIn('id', $this->selectedLaboratories)->get();
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
        $this->selectedLaboratories = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت آزمایشگاه‌های انتخاب‌شده با موفقیت تغییر کرد.');
    }

    public function deleteSelected($allFiltered = null)
    {
        if ($allFiltered === 'allFiltered') {
            $query = $this->getLaboratoriesQuery();
            $query->delete();
            $this->selectedLaboratories = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            $this->dispatch('show-alert', type: 'success', message: 'همه آزمایشگاه‌های فیلترشده حذف شدند!');
            return;
        }
        if (empty($this->selectedLaboratories)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ آزمایشگاهی انتخاب نشده است.');
            return;
        }
        MedicalCenter::whereIn('id', $this->selectedLaboratories)->delete();
        $this->selectedLaboratories = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'آزمایشگاه‌های انتخاب شده حذف شدند!');
    }

    public function toggleStatus($id)
    {
        $item = MedicalCenter::findOrFail($id);
        $item->is_active = !$item->is_active;
        $item->save();
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت کلینیک با موفقیت تغییر کرد.');
    }

    public function confirmToggleStatus($id)
    {
        $item = MedicalCenter::find($id);
        if (!$item) {
            $this->dispatch('show-alert', type: 'error', message: 'آزمایشگاه یافت نشد.');
            return;
        }
        $laboratoryName = $item->name;
        $action = $item->is_active ? 'غیرفعال کردن' : 'فعال کردن';

        $this->dispatch('confirm-toggle-status', id: $id, name: $laboratoryName, action: $action);
    }

    public function toggleStatusConfirmed($id)
    {
        $item = MedicalCenter::find($id);
        if (!$item) {
            $this->dispatch('show-alert', type: 'error', message: 'آزمایشگاه یافت نشد.');
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
            $this->dispatch('show-alert', type: 'success', message: 'آزمایشگاه فعال شد و پیامک فعال‌سازی ارسال شد!');
        } else {
            // ارسال پیامک غیرفعال‌سازی
            if ($item->phone_number) {
                $message = "مرکز درمانی گرامی، حساب کاربری شما در سیستم غیرفعال شد. برای اطلاعات بیشتر تماس بگیرید.";
                \App\Jobs\SendSmsNotificationJob::dispatch(
                    $message,
                    [$item->phone_number]
                )->delay(now()->addSeconds(5));
            }
            $this->dispatch('show-alert', type: 'info', message: 'آزمایشگاه غیرفعال شد!');
        }
    }

    protected function getLaboratoriesQuery()
    {
        return MedicalCenter::where('type', 'laboratory')
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
        $this->totalFilteredCount = $this->readyToLoad ? $this->getLaboratoriesQuery()->count() : 0;
        $items = $this->readyToLoad ? $this->getLaboratoriesQuery()->paginate($this->perPage) : null;
        $specialties = Specialty::pluck('name', 'id');
        $insurances = Insurance::pluck('name', 'id');
        $services = \App\Models\Service::pluck('name', 'id');
        return view('livewire.admin.panel.laboratories.laboratory-list', [
            'laboratories' => $items,
            'specialties' => $specialties,
            'insurances' => $insurances,
            'services' => $services,
            'totalFilteredCount' => $this->totalFilteredCount,
        ]);
    }
}

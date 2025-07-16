<?php

namespace App\Livewire\Admin\Panel\Doctors;

use App\Models\Doctor;
use Livewire\Component;
use Livewire\WithPagination;
use App\Jobs\SendSmsNotificationJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Morilog\Jalali\Jalalian;

class DoctorList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'deleteDoctorConfirmed' => 'deleteDoctor',
        'deleteSelectedConfirmed' => 'deleteSelected',
        'toggleStatusConfirmed' => 'toggleStatusConfirmed',
    ];

    public $perPage = 50;
    public $search = '';
    public $readyToLoad = false;
    public $selectedDoctors = [];
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

    public function loadDoctors()
    {
        $this->readyToLoad = true;
    }

    public function confirmToggleStatus($id)
    {
        $item = Doctor::find($id);
        if (!$item) {
            $this->dispatch('show-alert', type: 'error', message: 'پزشک یافت نشد.');
            return;
        }
        $doctorName = $item->first_name . ' ' . $item->last_name;
        $action = $item->status ? 'غیرفعال کردن' : 'فعال کردن';
       
        $this->dispatch('confirm-toggle-status', id: $id, name: $doctorName, action: $action);
    }

    public function toggleStatusConfirmed($id)
    {
        $item = Doctor::find($id);
        if (!$item) {
            $this->dispatch('show-alert', type: 'error', message: 'پزشک یافت نشد.');
            return;
        }

        $newStatus = !$item->status;
        $item->update(['status' => $newStatus]);

        if ($newStatus) {
            $message = "دکتر گرامی، حساب کاربری شما در سیستم فعال شد. می‌توانید از طریق لینک زیر وارد پنل خود شوید: " . route('dr.auth.login-register-form', $item->id);
            $activeGateway = \Modules\SendOtp\App\Models\SmsGateway::where('is_active', true)->first();
            $gatewayName = $activeGateway ? $activeGateway->name : 'pishgamrayan';
            $templateId = ($gatewayName === 'pishgamrayan') ? 100254 : null;

            \App\Jobs\SendSmsNotificationJob::dispatch(
                $message,
                [$item->mobile],
                $templateId,
                [$item->first_name . ' ' . $item->last_name]
            )->delay(now()->addSeconds(5));

            $this->dispatch('show-alert', type: 'success', message: 'پزشک فعال شد و پیامک فعال‌سازی ارسال شد!');
        } else {
            $message = "دکتر گرامی، حساب کاربری شما در سیستم غیرفعال شد. برای اطلاعات بیشتر تماس بگیرید.";
            \App\Jobs\SendSmsNotificationJob::dispatch(
                $message,
                [$item->mobile]
            )->delay(now()->addSeconds(5));

            $this->dispatch('show-alert', type: 'info', message: 'پزشک غیرفعال شد!');
        }

        Cache::forget('doctors_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteDoctor($id)
    {
        $item = Doctor::find($id);
        if (!$item) {
            $this->dispatch('show-alert', type: 'error', message: 'پزشک یافت نشد.');
            return;
        }
        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'پزشک حذف شد!');
        Cache::forget('doctors_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
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
        $currentPageIds = Cache::remember('doctors_ids_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage(), now()->addMinutes(5), function () {
            return $this->getDoctorsQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        });
        $this->selectedDoctors = $value ? $currentPageIds : [];
    }

    public function updatedSelectedDoctors()
    {
        $currentPageIds = Cache::remember('doctors_ids_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage(), now()->addMinutes(5), function () {
            return $this->getDoctorsQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        });
        $this->selectAll = !empty($this->selectedDoctors) && count(array_diff($currentPageIds, $this->selectedDoctors)) === 0;
    }

    public function deleteSelected($allFiltered = null)
    {
        if ($allFiltered === 'allFiltered') {
            $query = $this->getDoctorsQuery();
            $query->delete();
            $this->selectedDoctors = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            $this->dispatch('show-alert', type: 'success', message: 'همه پزشکان فیلترشده حذف شدند!');
            Cache::forget('doctors_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
            return;
        }
        if (empty($this->selectedDoctors)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ پزشکی انتخاب نشده است.');
            return;
        }
        Doctor::whereIn('id', $this->selectedDoctors)->delete();
        $this->selectedDoctors = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'پزشکان انتخاب‌شده حذف شدند!');
        Cache::forget('doctors_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedDoctors) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ پزشکی انتخاب نشده است.');
            return;
        }

        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفا یک عملیات را انتخاب کنید.');
            return;
        }

        if ($this->applyToAllFiltered) {
            $query = $this->getDoctorsQuery();
            switch ($this->groupAction) {
                case 'delete':
                    $this->dispatch('confirm-delete-selected', ['allFiltered' => true]);
                    return;
                case 'status_active':
                    $query->update(['status' => true]);
                    $this->sendBulkActivationSms($query->get());
                    $this->dispatch('show-alert', type: 'success', message: 'همه پزشکان فیلترشده فعال شدند!');
                    break;
                case 'status_inactive':
                    $query->update(['status' => false]);
                    $this->sendBulkDeactivationSms($query->get());
                    $this->dispatch('show-alert', type: 'success', message: 'همه پزشکان فیلترشده غیرفعال شدند!');
                    break;
            }
            $this->selectedDoctors = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            Cache::forget('doctors_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
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
        $items = Doctor::whereIn('id', $this->selectedDoctors)->get();
        foreach ($items as $item) {
            $oldStatus = $item->status;
            $item->update(['status' => $status]);

            if ($status && !$oldStatus) {
                $message = "دکتر گرامی، حساب کاربری شما در سیستم فعال شد. می‌توانید از طریق لینک زیر وارد پنل خود شوید: " . route('dr.auth.login-register-form', $item->id);
                $activeGateway = \Modules\SendOtp\App\Models\SmsGateway::where('is_active', true)->first();
                $gatewayName = $activeGateway ? $activeGateway->name : 'pishgamrayan';
                $templateId = ($gatewayName === 'pishgamrayan') ? 100254 : null;

                \App\Jobs\SendSmsNotificationJob::dispatch(
                    $message,
                    [$item->mobile],
                    $templateId,
                    [$item->first_name . ' ' . $item->last_name]
                )->delay(now()->addSeconds(5));
            } elseif (!$status && $oldStatus) {
                $message = "دکتر گرامی، حساب کاربری شما در سیستم غیرفعال شد. برای اطلاعات بیشتر تماس بگیرید.";
                \App\Jobs\SendSmsNotificationJob::dispatch(
                    $message,
                    [$item->mobile]
                )->delay(now()->addSeconds(5));
            }
        }

        $this->selectedDoctors = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت پزشکان انتخاب‌شده با موفقیت تغییر کرد.');
        Cache::forget('doctors_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
    }

    private function sendBulkActivationSms($doctors)
    {
        foreach ($doctors as $item) {
            $message = "دکتر گرامی، حساب کاربری شما در سیستم فعال شد. می‌توانید از طریق لینک زیر وارد پنل خود شوید: " . route('dr.auth.login-register-form', $item->id);
            $activeGateway = \Modules\SendOtp\App\Models\SmsGateway::where('is_active', true)->first();
            $gatewayName = $activeGateway ? $activeGateway->name : 'pishgamrayan';
            $templateId = ($gatewayName === 'pishgamrayan') ? 100254 : null;

            \App\Jobs\SendSmsNotificationJob::dispatch(
                $message,
                [$item->mobile],
                $templateId,
                [$item->first_name . ' ' . $item->last_name]
            )->delay(now()->addSeconds(5));
        }
    }

    private function sendBulkDeactivationSms($doctors)
    {
        foreach ($doctors as $item) {
            $message = "دکتر گرامی، حساب کاربری شما در سیستم غیرفعال شد. برای اطلاعات بیشتر تماس بگیرید.";
            \App\Jobs\SendSmsNotificationJob::dispatch(
                $message,
                [$item->mobile]
            )->delay(now()->addSeconds(5));
        }
    }

    private function getDoctorsQuery()
    {
        return Doctor::query()
            ->with(['province' => fn($q) => $q->select('id', 'name'), 'city' => fn($q) => $q->select('id', 'name')])
            ->when($this->search, function ($query) {
                $search = trim($this->search);
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%$search%")
                      ->orWhere('last_name', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%")
                      ->orWhere('mobile', 'like', "%$search%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
                });
            })
            ->when($this->statusFilter, function ($query) {
                if ($this->statusFilter === 'active') {
                    $query->where('status', true);
                } elseif ($this->statusFilter === 'inactive') {
                    $query->where('status', false);
                }
            })
            ->orderBy('created_at', 'desc');
    }

    public function render()
    {
        $cacheKey = 'doctors_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage();
        $doctors = $this->readyToLoad ? Cache::remember($cacheKey, now()->addMinutes(5), function () {
            return $this->getDoctorsQuery()->paginate($this->perPage);
        }) : [];
        $this->totalFilteredCount = $this->readyToLoad ? $this->getDoctorsQuery()->count() : 0;

        return view('livewire.admin.panel.doctors.doctor-list', [
            'doctors' => $doctors,
            'totalFilteredCount' => $this->totalFilteredCount,
        ]);
    }
}
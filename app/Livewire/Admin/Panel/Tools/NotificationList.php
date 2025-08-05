<?php

namespace App\Livewire\Admin\Panel\Tools;

use App\Models\Notification;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteNotificationConfirmed' => 'deleteNotification'];

    public $search = '';
    public $selectAll = false;
    public $selectedNotifications = [];
    public $readyToLoad = false;
    public $groupAction = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->readyToLoad = false;
    }

    public function loadNotifications()
    {
        $this->readyToLoad = true;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = $this->getNotificationsQuery()->pluck('id')->toArray();
        $this->selectedNotifications = $value ? $currentPageIds : [];
    }

    public function updatedSelectedNotifications()
    {
        $currentPageIds = $this->getNotificationsQuery()->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedNotifications) && count(array_diff($currentPageIds, $this->selectedNotifications)) === 0;
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedNotifications)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ اعلانی انتخاب نشده است.');
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
    }

    private function updateStatus($status)
    {
        Notification::whereIn('id', $this->selectedNotifications)
            ->update(['is_active' => $status]);

        $this->selectedNotifications = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت اعلان‌های انتخاب‌شده با موفقیت تغییر کرد.');
    }

    public function toggleStatus($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->is_active = !$notification->is_active;
            $notification->save();
            $this->dispatch('show-alert', type: 'success', message: 'وضعیت اعلان با موفقیت تغییر کرد.');
        } catch (\Exception $e) {
            $this->dispatch('show-alert', type: 'error', message: 'خطا در تغییر وضعیت: ' . $e->getMessage());
        }
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteNotification($id)
    {
        try {
            $notification = Notification::findOrFail($id);
            $notification->delete();
            $this->selectedNotifications = array_diff($this->selectedNotifications, [$id]);
            $this->dispatch('show-alert', type: 'success', message: 'اعلان با موفقیت حذف شد.');
        } catch (\Exception $e) {
            $this->dispatch('show-alert', type: 'error', message: 'خطا در حذف اعلان: ' . $e->getMessage());
        }
    }

    public function deleteSelected()
    {
        if (empty($this->selectedNotifications)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ اعلانی انتخاب نشده است.');
            return;
        }

        Notification::whereIn('id', $this->selectedNotifications)->delete();
        $this->selectedNotifications = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'اعلان‌های انتخاب‌شده با موفقیت حذف شدند.');
    }

    public function getTypeLabel($type)
    {
        return match ($type) {
            'info' => ['label' => 'اطلاع‌رسانی', 'class' => 'bg-info'],
            'success' => ['label' => 'موفقیت', 'class' => 'bg-success'],
            'warning' => ['label' => 'هشدار', 'class' => 'bg-warning'],
            'error' => ['label' => 'خطا', 'class' => 'bg-danger'],
            default => ['label' => 'نامشخص', 'class' => 'bg-secondary'],
        };
    }

    public function getTargetLabel($notification)
    {
        if ($notification->target_group) {
            return match ($notification->target_group) {
                'all' => 'همه',
                'doctors' => 'پزشکان',
                'secretaries' => 'منشی‌ها',
                'patients' => 'بیماران',
                default => 'گروه نامشخص',
            };
        } elseif ($notification->recipients->count() === 1 && $notification->recipients->first()->phone_number) {
            return 'تکی: ' . $notification->recipients->first()->phone_number;
        } elseif ($notification->recipients->count() > 0) {
            return 'چندانتخابی (' . $notification->recipients->count() . ' گیرنده)';
        }
        return 'نامشخص';
    }

    private function getNotificationsQuery()
    {
        return Notification::query()
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%')
                    ->orWhere('message', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);
    }

    public function render()
    {
        $notifications = $this->readyToLoad ? $this->getNotificationsQuery() : null;

        return view('livewire.admin.panel.tools.notification-list', [
            'notifications' => $notifications,
        ])->layout('layouts.admin');
    }
}

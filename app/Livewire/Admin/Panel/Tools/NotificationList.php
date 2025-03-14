<?php
namespace App\Livewire\Admin\Panel\Tools;

use App\Models\Notification;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationList extends Component
{
    use WithPagination;

    public $search                = '';
    public $selectAll             = false;
    public $selectedNotifications = [];
    public $readyToLoad           = false;

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->readyToLoad = false; // حالت اولیه
    }

    public function loadNotifications()
    {
        $this->readyToLoad = true;
    }

    public function updatedSearch()
    {
        $this->resetPage(); // ریست صفحه‌بندی موقع سرچ
    }

    public function updatedSelectAll($value)
    {
        $notifications = $this->notifications;
        if ($value && $notifications) {
            $this->selectedNotifications = $notifications->pluck('id')->toArray();
        } else {
            $this->selectedNotifications = [];
        }
    }

    public function updatedSelectedNotifications()
    {
        $notifications   = $this->notifications;
        $this->selectAll = $notifications && count($this->selectedNotifications) === $notifications->count();
    }

    public function toggleStatus($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['is_active' => ! $notification->is_active]);
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت اعلان با موفقیت تغییر کرد.');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteNotificationConfirmed($id)
    {
        Notification::findOrFail($id)->delete();
        $this->dispatch('show-alert', type: 'success', message: 'اعلان با موفقیت حذف شد.');
    }

    public function deleteSelected()
    {
        Notification::whereIn('id', $this->selectedNotifications)->delete();
        $this->selectedNotifications = [];
        $this->selectAll             = false;
        $this->dispatch('show-alert', type: 'success', message: 'اعلان‌های انتخاب‌شده با موفقیت حذف شدند.');
    }

    public function getTypeLabel($type)
    {
        return match ($type) {
            'info' => ['label' => 'اطلاع‌رسانی', 'class' => 'bg-label-info'],
            'success' => ['label' => 'موفقیت', 'class' => 'bg-label-success'],
            'warning' => ['label' => 'هشدار', 'class' => 'bg-label-warning'],
            'error' => ['label' => 'خطا', 'class' => 'bg-label-danger'],
            default => ['label' => 'نامشخص', 'class' => 'bg-label-secondary'],
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

    public function getNotificationsProperty()
    {
        if (! $this->readyToLoad) {
            return Notification::whereRaw('0=1')->paginate(10); // مجموعه خالی برای حالت اولیه
        }

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
        return view('livewire.admin.panel.tools.notification-list', [
            'notifications' => $this->notifications, // صراحتاً پاس دادن به ویو
        ])->layout('layouts.admin');
    }
}

<?php

namespace App\Livewire\Admin\Panel\UserBlockings;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\UserBlocking;
use App\Jobs\SendSmsNotificationJob;
use Illuminate\Support\Facades\Cache;
use Morilog\Jalali\Jalalian;

class UserBlockingList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'deleteUserBlockingConfirmed' => 'deleteUserBlocking',
        'deleteSelectedConfirmed' => 'deleteSelected',
        'toggleStatusConfirmed' => 'toggleStatusConfirmed',
    ];

    public $perPage = 50;
    public $search = '';
    public $readyToLoad = false;
    public $selectedUserBlockings = [];
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

    public function loadUserBlockings()
    {
        $this->readyToLoad = true;
    }

    public function confirmToggleStatus($id)
    {
        $item = UserBlocking::with([
            'user' => fn ($q) => $q->select('id', 'first_name', 'last_name'),
            'doctor' => fn ($q) => $q->select('id', 'first_name', 'last_name')
        ])->find($id);

        if (!$item) {
            $this->dispatch('show-alert', type: 'error', message: 'رکورد مسدودیت یافت نشد.');
            return;
        }

        $name = $item->user ? $item->user->first_name . ' ' . $item->user->last_name : ($item->doctor ? $item->doctor->first_name . ' ' . $item->doctor->last_name : 'نامشخص');
        $action = $item->status ? 'رفع مسدودیت' : 'مسدود کردن';
        $this->dispatch('confirm-toggle-status', id: $id, name: $name, action: $action);
    }

    public function toggleStatusConfirmed($id)
    {
        $item = UserBlocking::find($id);
        if (!$item) {
            $this->dispatch('show-alert', type: 'error', message: 'رکورد مسدودیت یافت نشد.');
            return;
        }

        $oldStatus = $item->status;
        $item->update(['status' => !$item->status]);
        Cache::forget('user_blockings_' . $this->search . '_page_' . $this->getPage());

        if ($item->status && !$oldStatus) {
            if ($item->user_id) {
                $user = $item->user()->select('mobile')->first();
                if ($user) {
                    $message = "کاربر گرامی، شما توسط مدیر سیستم مسدود شده‌اید. جهت اطلاعات بیشتر تماس بگیرید.";
                    SendSmsNotificationJob::dispatch($message, [$user->mobile])->delay(now()->addSeconds(5));
                }
            } elseif ($item->doctor_id) {
                $doctor = $item->doctor()->select('mobile')->first();
                if ($doctor) {
                    $message = "دکتر گرامی، شما توسط مدیر سیستم مسدود شده‌اید. جهت اطلاعات بیشتر تماس بگیرید.";
                    SendSmsNotificationJob::dispatch($message, [$doctor->mobile])->delay(now()->addSeconds(5));
                }
            }
            $item->update(['is_notified' => true]);
            $this->dispatch('show-alert', type: 'success', message: 'مسدودیت فعال شد!');
        } elseif (!$item->status && $oldStatus) {
            if ($item->user_id) {
                $user = $item->user()->select('mobile')->first();
                if ($user) {
                    $message = "کاربر گرامی، مسدودیت شما توسط مدیر سیستم رفع شد.";
                    SendSmsNotificationJob::dispatch($message, [$user->mobile])->delay(now()->addSeconds(5));
                }
            } elseif ($item->doctor_id) {
                $doctor = $item->doctor()->select('mobile')->first();
                if ($doctor) {
                    $message = "دکتر گرامی، مسدودیت شما توسط مدیر سیستم رفع شد.";
                    SendSmsNotificationJob::dispatch($message, [$doctor->mobile])->delay(now()->addSeconds(5));
                }
            }
            $this->dispatch('show-alert', type: 'info', message: 'مسدودیت غیرفعال شد!');
        }
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteUserBlocking($id)
    {
        $item = UserBlocking::find($id);
        if (!$item) {
            $this->dispatch('show-alert', type: 'error', message: 'رکورد مسدودیت یافت نشد.');
            return;
        }
        $item->delete();
        Cache::forget('user_blockings_' . $this->search . '_page_' . $this->getPage());
        $this->dispatch('show-alert', type: 'success', message: 'رکورد مسدودیت با موفقیت حذف شد!');
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
        $currentPageIds = $this->getUserBlockingsQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        $this->selectedUserBlockings = $value ? $currentPageIds : [];
    }

    public function updatedSelectedUserBlockings()
    {
        $currentPageIds = $this->getUserBlockingsQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedUserBlockings) && count(array_diff($currentPageIds, $this->selectedUserBlockings)) === 0;
    }

    public function deleteSelected($allFiltered = null)
    {
        if ($allFiltered === 'allFiltered') {
            $query = $this->getUserBlockingsQuery();
            $query->delete();
            $this->selectedUserBlockings = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            $this->dispatch('show-alert', type: 'success', message: 'همه رکوردهای مسدودیت فیلترشده حذف شدند!');
            Cache::forget('user_blockings_' . $this->search . '_page_' . $this->getPage());
            return;
        }
        if (empty($this->selectedUserBlockings)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ رکوردی انتخاب نشده است.');
            return;
        }
        UserBlocking::whereIn('id', $this->selectedUserBlockings)->delete();
        Cache::forget('user_blockings_' . $this->search . '_page_' . $this->getPage());
        $this->selectedUserBlockings = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'رکوردهای مسدودیت انتخاب‌شده با موفقیت حذف شدند!');
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedUserBlockings) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ رکوردی انتخاب نشده است.');
            return;
        }

        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفا یک عملیات را انتخاب کنید.');
            return;
        }

        if ($this->applyToAllFiltered) {
            $query = $this->getUserBlockingsQuery();
            switch ($this->groupAction) {
                case 'delete':
                    $this->dispatch('confirm-delete-selected', ['allFiltered' => true]);
                    return;
                case 'status_active':
                    $query->update(['status' => true]);
                    $this->dispatch('show-alert', type: 'success', message: 'همه رکوردهای مسدودیت فیلترشده فعال شدند!');
                    break;
                case 'status_inactive':
                    $query->update(['status' => false]);
                    $this->dispatch('show-alert', type: 'success', message: 'همه رکوردهای مسدودیت فیلترشده غیرفعال شدند!');
                    break;
            }
            $this->selectedUserBlockings = [];
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
        $items = UserBlocking::whereIn('id', $this->selectedUserBlockings)->get();
        foreach ($items as $item) {
            $oldStatus = $item->status;
            $item->update(['status' => $status]);

            if ($status && !$oldStatus) {
                if ($item->user_id) {
                    $user = $item->user()->select('mobile')->first();
                    if ($user) {
                        $message = "کاربر گرامی، شما توسط مدیر سیستم مسدود شده‌اید. جهت اطلاعات بیشتر تماس بگیرید.";
                        SendSmsNotificationJob::dispatch($message, [$user->mobile])->delay(now()->addSeconds(5));
                    }
                } elseif ($item->doctor_id) {
                    $doctor = $item->doctor()->select('mobile')->first();
                    if ($doctor) {
                        $message = "دکتر گرامی، شما توسط مدیر سیستم مسدود شده‌اید. جهت اطلاعات بیشتر تماس بگیرید.";
                        SendSmsNotificationJob::dispatch($message, [$doctor->mobile])->delay(now()->addSeconds(5));
                    }
                }
                $item->update(['is_notified' => true]);
            } elseif (!$status && $oldStatus) {
                if ($item->user_id) {
                    $user = $item->user()->select('mobile')->first();
                    if ($user) {
                        $message = "کاربر گرامی، مسدودیت شما توسط مدیر سیستم رفع شد.";
                        SendSmsNotificationJob::dispatch($message, [$user->mobile])->delay(now()->addSeconds(5));
                    }
                } elseif ($item->doctor_id) {
                    $doctor = $item->doctor()->select('mobile')->first();
                    if ($doctor) {
                        $message = "دکتر گرامی، مسدودیت شما توسط مدیر سیستم رفع شد.";
                        SendSmsNotificationJob::dispatch($message, [$doctor->mobile])->delay(now()->addSeconds(5));
                    }
                }
            }
        }

        $this->selectedUserBlockings = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت رکوردهای مسدودیت انتخاب‌شده با موفقیت تغییر کرد.');
    }

    private function getUserBlockingsQuery()
    {
        return UserBlocking::query()
            ->with([
                'user' => fn ($q) => $q->select('id', 'first_name', 'last_name', 'mobile'),
                'doctor' => fn ($q) => $q->select('id', 'first_name', 'last_name', 'mobile'),
                'manager' => fn ($q) => $q->select('id', 'first_name', 'last_name')
            ])
            ->when($this->search, function ($query) {
                $search = trim($this->search);
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($q) use ($search) {
                        $q->where('mobile', 'like', "%$search%")
                          ->orWhere('first_name', 'like', "%$search%")
                          ->orWhere('last_name', 'like', "%$search%")
                          ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
                    })->orWhereHas('doctor', function ($q) use ($search) {
                        $q->where('mobile', 'like', "%$search%")
                          ->orWhere('first_name', 'like', "%$search%")
                          ->orWhere('last_name', 'like', "%$search%")
                          ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
                    })->orWhere('reason', 'like', "%$search%");
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
        $userBlockings = $this->readyToLoad ? $this->getUserBlockingsQuery()->paginate($this->perPage) : [];
        $this->totalFilteredCount = $this->readyToLoad ? $this->getUserBlockingsQuery()->count() : 0;

        return view('livewire.admin.panel.user-blockings.user-blocking-list', [
            'userBlockings' => $userBlockings,
            'totalFilteredCount' => $this->totalFilteredCount,
        ]);
    }
}

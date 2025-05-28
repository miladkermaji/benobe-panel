<?php

namespace App\Livewire\Admin\Panel\UserSubscriptions;

use App\Models\UserSubscription;
use Livewire\Component;
use Livewire\WithPagination;

class SubscriptionList extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    protected $listeners = ['refreshList' => '$refresh'];

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function delete(UserSubscription $subscription)
    {
        $subscription->delete();
        $this->dispatch('refreshList');
        session()->flash('success', 'اشتراک با موفقیت حذف شد.');
    }

    public function render()
    {
        $subscriptions = UserSubscription::query()
            ->with(['user', 'membershipPlan'])
            ->when($this->search, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('mobile', 'like', '%' . $this->search . '%');
                });
            })
            ->where('user_id', auth('admin')->id())
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.admin.panel.user-subscriptions.subscription-list', [
            'subscriptions' => $subscriptions
        ]);
    }
}

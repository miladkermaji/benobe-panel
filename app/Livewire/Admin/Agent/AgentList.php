<?php

namespace App\Livewire\Admin\Agent;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admin\Agent\Agent;
use Illuminate\Support\Facades\Log;

class AgentList extends Component
{
    use WithPagination;

    public $search = ''; // رشته جستجو
    public $selectedAgents = []; // آرایه IDهای انتخاب‌شده برای حذف
    public $selectAll = false; // وضعیت چک‌باکس "انتخاب همه"
    public $perPage = 100; // تعداد ردیف‌ها در هر صفحه
    public $agentStatuses = []; // برای ذخیره وضعیت‌ها

    protected $paginationTheme = 'bootstrap'; // تم صفحه‌بندی بوتسترپ

    public function mount()
    {
        $this->loadAgentStatuses(); // لود اولیه وضعیت‌ها
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->loadAgentStatuses();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedAgents = Agent::where(function ($query) {
                $query->where('full_name', 'like', '%' . $this->search . '%')
                    ->orWhere('city', 'like', '%' . $this->search . '%');
            })->pluck('id')->toArray();
        } else {
            $this->selectedAgents = [];
        }
    }

    public function updatedSelectedAgents()
    {
        $total = Agent::where(function ($query) {
            $query->where('full_name', 'like', '%' . $this->search . '%')
                ->orWhere('city', 'like', '%' . $this->search . '%');
        })->count();
        $this->selectAll = count($this->selectedAgents) === $total && $total > 0;
    }

    public function toggleStatus($id)
    {
        $agent = Agent::find($id);
        if ($agent) {
            $agent->status = !$agent->status;
            $agent->save();
            $this->agentStatuses[$id] = $agent->status;
            $this->dispatch('toast', 'وضعیت نماینده با موفقیت تغییر کرد.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function deleteSelected()
    {
        if (empty($this->selectedAgents)) {
            Log::info('No agents selected for deletion');
            $this->dispatch('toast', 'هیچ نماینده‌ای انتخاب نشده است.', [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
            return;
        }

        try {
            Agent::whereIn('id', $this->selectedAgents)->delete();
            $this->selectedAgents = [];
            $this->selectAll = false;
            $this->dispatch('toast', 'نمایندگان انتخاب‌شده با موفقیت حذف شدند.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
            $this->loadAgentStatuses(); // آپدیت وضعیت‌ها
        } catch (\Exception $e) {
            $this->dispatch('toast', 'خطا در حذف نمایندگان: ' . $e->getMessage(), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function loadAgentStatuses()
    {
        $agents = Agent::where('full_name', 'like', '%' . $this->search . '%')
            ->orWhere('city', 'like', '%' . $this->search . '%')
            ->get();
        foreach ($agents as $agent) {
            $this->agentStatuses[$agent->id] = $agent->status;
        }
    }

    public function render()
    {
        $agents = Agent::where('full_name', 'like', '%' . $this->search . '%')
            ->orWhere('city', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage);

        return view('livewire.admin.agent.agent-list', [
            'agents' => $agents,
        ]);
    }
}

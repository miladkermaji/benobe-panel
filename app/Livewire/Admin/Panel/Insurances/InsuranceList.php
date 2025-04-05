<?php

namespace App\Livewire\Admin\Panel\Insurances;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Insurance;

class InsuranceList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteInsuranceConfirmed' => 'deleteInsurance'];

    public $perPage = 10;
    public $search = '';
    public $readyToLoad = false;
    public $selectedInsurances = [];
    public $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadInsurances()
    {
        $this->readyToLoad = true;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = $this->getInsurancesQuery()->pluck('id')->toArray();
        $this->selectedInsurances = $value ? $currentPageIds : [];
    }

    public function updatedSelectedInsurances()
    {
        $currentPageIds = $this->getInsurancesQuery()->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedInsurances) && count(array_diff($currentPageIds, $this->selectedInsurances)) === 0;
    }

    public function deleteSelected()
    {
        if (empty($this->selectedInsurances)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ بیمه‌ای انتخاب نشده است.');
            return;
        }

        Insurance::whereIn('id', $this->selectedInsurances)->delete();
        $this->selectedInsurances = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'بیمه‌های انتخاب‌شده حذف شدند!');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteInsurance($id)
    {
        $item = Insurance::findOrFail($id);
        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'بیمه حذف شد!');
    }

    private function getInsurancesQuery()
    {
        return Insurance::where('name', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage);
    }

    public function render()
    {
        $items = $this->readyToLoad ? $this->getInsurancesQuery() : null;

        return view('livewire.admin.panel.insurances.insurance-list', [
            'insurances' => $items,
        ]);
    }
}

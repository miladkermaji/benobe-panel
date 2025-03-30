<?php

namespace App\Livewire\Admin\Panel\Bestdoctors;

use App\Models\BestDoctor;
use Livewire\Component;
use Livewire\WithPagination;

class BestDoctorList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteBestDoctorConfirmed' => 'deleteBestDoctor'];

    public $perPage             = 10;
    public $search              = '';
    public $readyToLoad         = false;
    public $selectedBestDoctors = [];
    public $selectAll           = false;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadBestDoctors()
    {
        $this->readyToLoad = true;
    }

    public function toggleStatus($id)
    {
        $item = BestDoctor::findOrFail($id);
        $item->update(['status' => ! $item->status]);
        $this->dispatch('show-alert', type: $item->status ? 'success' : 'info', message: $item->status ? 'فعال شد!' : 'غیرفعال شد!');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteBestDoctor($id)
    {
        $item = BestDoctor::findOrFail($id);
        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'بهترین پزشک حذف شد!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds            = $this->getBestDoctors()->pluck('id')->toArray();
        $this->selectedBestDoctors = $value ? $currentPageIds : [];
    }

    public function updatedSelectedBestDoctors()
    {
        $currentPageIds  = $this->getBestDoctors()->pluck('id')->toArray();
        $this->selectAll = ! empty($this->selectedBestDoctors) && count(array_diff($currentPageIds, $this->selectedBestDoctors)) === 0;
    }

    public function deleteSelected()
    {
        if (empty($this->selectedBestDoctors)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ پزشکی انتخاب نشده است.');
            return;
        }

        BestDoctor::whereIn('id', $this->selectedBestDoctors)->delete();
        $this->selectedBestDoctors = [];
        $this->selectAll           = false;
        $this->dispatch('show-alert', type: 'success', message: 'پزشکان انتخاب‌شده حذف شدند!');
    }

    private function getBestDoctors()
    {
        return BestDoctor::whereHas('doctor', function ($query) {
            $query->where('first_name', 'like', '%' . $this->search . '%')
                ->orWhere('last_name', 'like', '%' . $this->search . '%')
                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $this->search . '%']);
        })

            ->orderBy('id', 'desc')
            ->paginate($this->perPage);
    }

    public function render()
    {
        $bestdoctors = $this->readyToLoad ? $this->getBestDoctors() : null;
        return view('livewire.admin.panel.bestdoctors.bestdoctor-list', [
            'bestdoctors' => $bestdoctors,
        ]);
    }
}

<?php

namespace App\Livewire\Mc\Panel\DoctorFaqs;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\DoctorFaq;
use Illuminate\Support\Facades\Auth;
use App\Traits\HasSelectedDoctor;

class DoctorFaqsList extends Component
{
    use WithPagination;
    use HasSelectedDoctor;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteFaqConfirmed' => 'deleteFaq'];

    public $perPage = 100;
    public $search = '';
    public $readyToLoad = false;
    public $selectedFaqs = [];
    public $selectAll = false;
    public $groupAction = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadFaqs()
    {
        $this->readyToLoad = true;
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteFaq($id)
    {
        $doctor = $this->getSelectedDoctor();
        if (!$doctor) {
            $this->dispatch('show-alert', type: 'error', message: 'هیچ پزشکی انتخاب نشده است.');
            return;
        }

        $faq = DoctorFaq::where('doctor_id', $doctor->id)->findOrFail($id);
        $faq->delete();
        $this->dispatch('show-alert', type: 'success', message: 'سوال متداول با موفقیت حذف شد!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = $this->getFaqsQuery()->pluck('id')->toArray();
        $this->selectedFaqs = $value ? $currentPageIds : [];
    }

    public function updatedSelectedFaqs()
    {
        $currentPageIds = $this->getFaqsQuery()->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedFaqs) && count(array_diff($currentPageIds, $this->selectedFaqs)) === 0;
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedFaqs)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ سوالی انتخاب نشده است.');
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
        $doctor = $this->getSelectedDoctor();
        if (!$doctor) {
            $this->dispatch('show-alert', type: 'error', message: 'هیچ پزشکی انتخاب نشده است.');
            return;
        }

        DoctorFaq::where('doctor_id', $doctor->id)
            ->whereIn('id', $this->selectedFaqs)
            ->update(['is_active' => $status]);

        $this->selectedFaqs = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت سوالات انتخاب‌شده با موفقیت تغییر کرد.');
    }

    public function deleteSelected()
    {
        if (empty($this->selectedFaqs)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ سوالی انتخاب نشده است.');
            return;
        }

        $doctor = $this->getSelectedDoctor();
        if (!$doctor) {
            $this->dispatch('show-alert', type: 'error', message: 'هیچ پزشکی انتخاب نشده است.');
            return;
        }

        DoctorFaq::where('doctor_id', $doctor->id)
            ->whereIn('id', $this->selectedFaqs)
            ->delete();
        $this->selectedFaqs = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'سوالات انتخاب‌شده حذف شدند!');
    }

    public function toggleStatus($id)
    {
        $doctor = $this->getSelectedDoctor();
        if (!$doctor) {
            $this->dispatch('show-alert', type: 'error', message: 'هیچ پزشکی انتخاب نشده است.');
            return;
        }

        $faq = DoctorFaq::where('doctor_id', $doctor->id)->findOrFail($id);
        $faq->update(['is_active' => !$faq->is_active]);
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت سوال متداول تغییر کرد!');
    }

    private function getFaqsQuery()
    {
        $doctor = $this->getSelectedDoctor();
        if (!$doctor) {
            return collect()->paginate($this->perPage);
        }

        return DoctorFaq::where('doctor_id', $doctor->id)
            ->where(function ($query) {
                $query->where('question', 'like', '%' . $this->search . '%')
                    ->orWhere('answer', 'like', '%' . $this->search . '%');
            })
            ->orderBy('order', 'asc')
            ->paginate($this->perPage);
    }

    public function render()
    {
        $faqs = $this->readyToLoad ? $this->getFaqsQuery() : null;

        return view('livewire.mc.panel.doctor-faqs.doctor-faqs-list', [
            'faqs' => $faqs,
        ]);
    }
}

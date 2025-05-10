<?php

namespace App\Livewire\Dr\Panel\DoctorFaqs;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\DoctorFaq;
use Illuminate\Support\Facades\Auth;

class DoctorFaqsList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $readyToLoad = false;
    public $perPage = 10;
    public $selectedFaqs = [];
    public $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    protected $listeners = ['deleteFaqConfirmed' => 'deleteFaq'];

    public function mount()
    {
        $this->readyToLoad = true;
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedFaqs = $this->getFaqsQuery()->pluck('id')->toArray();
        } else {
            $this->selectedFaqs = [];
        }
    }

    public function updatedSelectedFaqs()
    {
        if (count($this->selectedFaqs) === $this->getFaqsQuery()->count()) {
            $this->selectAll = true;
        } else {
            $this->selectAll = false;
        }
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteFaq($id)
    {
        $faq = DoctorFaq::where('doctor_id', Auth::guard('doctor')->user()->id)->findOrFail($id);
        $faq->delete();
        $this->dispatch('show-alert', type: 'success', message: 'سوال متداول با موفقیت حذف شد!');
    }

    public function deleteSelected()
    {
        DoctorFaq::where('doctor_id', Auth::guard('doctor')->user()->id)->whereIn('id', $this->selectedFaqs)->delete();
        $this->selectedFaqs = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'سوالات متداول انتخاب‌شده حذف شدند!');
    }

    public function toggleStatus($id)
    {
        $faq = DoctorFaq::where('doctor_id', Auth::guard('doctor')->user()->id)->findOrFail($id);
        $faq->update(['is_active' => !$faq->is_active]);
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت سوال متداول تغییر کرد!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    private function getFaqsQuery()
    {
        return DoctorFaq::where('doctor_id', Auth::guard('doctor')->user()->id)
            ->where(function ($query) {
                $query->where('question', 'like', '%' . $this->search . '%')
                    ->orWhere('answer', 'like', '%' . $this->search . '%');
            })
            ->ordered()
            ->paginate($this->perPage);
    }

    public function render()
    {
        $faqs = $this->readyToLoad ? $this->getFaqsQuery() : collect();

        return view('livewire.dr.panel.doctor-faqs.doctor-faqs-list', [
            'faqs' => $faqs,
        ]);
    }
}

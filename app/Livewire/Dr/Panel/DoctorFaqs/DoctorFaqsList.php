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
    public $perPage = 100;
    public $selectedFaqs = [];
    public $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    protected $listeners = ['deleteFaqConfirmed' => 'deleteFaq'];

    /**
     * مقداردهی اولیه
     */
    public function mount()
    {
        // بررسی احراز هویت پزشک
        if (!Auth::guard('doctor')->check()) {
            return redirect()->route('dr.auth.login-register-form');
        }
        $this->readyToLoad = true;
    }

    /**
     * به‌روزرسانی انتخاب همه
     */
    public function updatedSelectAll($value)
    {
        if (!Auth::guard('doctor')->check()) {
            return redirect()->route('dr.auth.login-register-form');
        }

        if ($value) {
            $this->selectedFaqs = $this->getFaqsQuery()->pluck('id')->toArray();
        } else {
            $this->selectedFaqs = [];
        }
    }

    /**
     * به‌روزرسانی سوالات انتخاب‌شده
     */
    public function updatedSelectedFaqs()
    {
        if (!Auth::guard('doctor')->check()) {
            return redirect()->route('dr.auth.login-register-form');
        }

        if (count($this->selectedFaqs) === $this->getFaqsQuery()->count()) {
            $this->selectAll = true;
        } else {
            $this->selectAll = false;
        }
    }

    /**
     * تأیید حذف سوال متداول
     */
    public function confirmDelete($id)
    {
        if (!Auth::guard('doctor')->check()) {
            return redirect()->route('dr.auth.login-register-form');
        }
        $this->dispatch('confirm-delete', id: $id);
    }

    /**
     * حذف سوال متداول
     */
    public function deleteFaq($id)
    {
        if (!Auth::guard('doctor')->check()) {
            return redirect()->route('dr.auth.login-register-form');
        }

        $faq = DoctorFaq::where('doctor_id', Auth::guard('doctor')->user()->id)->findOrFail($id);
        $faq->delete();
        $this->dispatch('show-alert', type: 'success', message: 'سوال متداول با موفقیت حذف شد!');
    }

    /**
     * حذف سوالات انتخاب‌شده
     */
    public function deleteSelected()
    {
        if (!Auth::guard('doctor')->check()) {
            return redirect()->route('dr.auth.login-register-form');
        }

        DoctorFaq::where('doctor_id', Auth::guard('doctor')->user()->id)
            ->whereIn('id', $this->selectedFaqs)
            ->delete();
        $this->selectedFaqs = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'سوالات متداول انتخاب‌شده حذف شدند!');
    }

    /**
     * تغییر وضعیت سوال متداول
     */
    public function toggleStatus($id)
    {
        if (!Auth::guard('doctor')->check()) {
            return redirect()->route('dr.auth.login-register-form');
        }

        $faq = DoctorFaq::where('doctor_id', Auth::guard('doctor')->user()->id)->findOrFail($id);
        $faq->update(['is_active' => !$faq->is_active]);
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت سوال متداول تغییر کرد!');
    }

    /**
     * به‌روزرسانی جستجو
     */
    public function updatedSearch()
    {
        $this->resetPage();
    }

    /**
     * دریافت کوئری سوالات متداول
     */
    private function getFaqsQuery()
    {
        if (!Auth::guard('doctor')->check()) {
            return collect();
        }

        return DoctorFaq::where('doctor_id', Auth::guard('doctor')->user()->id)
            ->where(function ($query) {
                $query->where('question', 'like', '%' . $this->search . '%')
                    ->orWhere('answer', 'like', '%' . $this->search . '%');
            })
            ->orderBy('order', 'asc') // جایگزین ordered()
            ->paginate($this->perPage);
    }

    /**
     * رندر صفحه
     */
    public function render()
    {
        $faqs = $this->readyToLoad ? $this->getFaqsQuery() : collect();

        return view('livewire.dr.panel.doctor-faqs.doctor-faqs-list', [
            'faqs' => $faqs,
        ]);
    }
}

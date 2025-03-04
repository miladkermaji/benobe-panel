<?php

namespace App\Livewire\Admin\Tools;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use App\Models\Admin\Tools\Redirect\Redirect;

class Redirects extends Component
{
    use WithPagination;

    public $search = '';
    public $newSourceUrl = '';
    public $newDestinationUrl = '';
    public $editId = null;
    public $editSourceUrl = '';
    public $editDestinationUrl = '';
    public $perPage = 10;
    public $selectedRedirects = [];
    public $selectAll = false;

    protected $rules = [
        'newSourceUrl' => 'required|url|unique:redirects,source_url',
        'newDestinationUrl' => 'required|url',
        'editSourceUrl' => 'required|url',
        'editDestinationUrl' => 'required|url',
    ];

    // پیام‌های سفارشی فارسی
    protected $messages = [
        'newSourceUrl.required' => 'لطفاً آدرس مسیر ریدایرکت را وارد کنید.',
        'newSourceUrl.url' => 'آدرس مسیر ریدایرکت باید یک URL معتبر باشد.',
        'newSourceUrl.unique' => 'این آدرس مسیر قبلاً ثبت شده است.',
        'newDestinationUrl.required' => 'لطفاً آدرس هدف ریدایرکت را وارد کنید.',
        'newDestinationUrl.url' => 'آدرس هدف ریدایرکت باید یک URL معتبر باشد.',
        'editSourceUrl.required' => 'لطفاً آدرس مسیر ریدایرکت را وارد کنید.',
        'editSourceUrl.url' => 'آدرس مسیر ریدایرکت باید یک URL معتبر باشد.',
        'editSourceUrl.unique' => 'این آدرس مسیر قبلاً ثبت شده است.',
        'editDestinationUrl.required' => 'لطفاً آدرس هدف ریدایرکت را وارد کنید.',
        'editDestinationUrl.url' => 'آدرس هدف ریدایرکت باید یک URL معتبر باشد.',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = Redirect::where('source_url', 'like', '%' . $this->search . '%')
            ->orWhere('destination_url', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage)
            ->pluck('id')
            ->toArray();

        $this->selectedRedirects = $value ? $currentPageIds : [];
    }

    public function updatedSelectedRedirects()
    {
        $currentPageIds = Redirect::where('source_url', 'like', '%' . $this->search . '%')
            ->orWhere('destination_url', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage)
            ->pluck('id')
            ->toArray();

        $this->selectAll = !empty($this->selectedRedirects) && count(array_diff($currentPageIds, $this->selectedRedirects)) === 0;
    }

    public function addRedirect()
    {
        $this->validate();

        try {
            Redirect::create([
                'source_url' => $this->newSourceUrl,
                'destination_url' => $this->newDestinationUrl,
                'status_code' => 301,
                'is_active' => true,
            ]);
            $this->newSourceUrl = '';
            $this->newDestinationUrl = '';
            $this->dispatch('toast', 'ریدایرکت جدید با موفقیت اضافه شد.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error adding redirect: ' . $e->getMessage());
            $this->dispatch('toast', 'خطا در اضافه کردن ریدایرکت: ' . $e->getMessage(), ['type' => 'error']);
        }
    }

    public function startEdit($id)
    {
        $redirect = Redirect::findOrFail($id);
        $this->editId = $id;
        $this->editSourceUrl = $redirect->source_url;
        $this->editDestinationUrl = $redirect->destination_url;
        $this->resetValidation();
    }

    public function updateRedirect()
    {
        $this->rules['editSourceUrl'] = "required|url|unique:redirects,source_url,{$this->editId}";
        $this->validate();

        try {
            $redirect = Redirect::findOrFail($this->editId);
            $redirect->update([
                'source_url' => $this->editSourceUrl,
                'destination_url' => $this->editDestinationUrl,
            ]);
            $this->editId = null;
            $this->editSourceUrl = '';
            $this->editDestinationUrl = '';
            $this->resetValidation();
            $this->dispatch('toast', 'ریدایرکت با موفقیت ویرایش شد.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error updating redirect: ' . $e->getMessage());
            $this->dispatch('toast', 'خطا در ویرایش ریدایرکت: ' . $e->getMessage(), ['type' => 'error']);
        }
    }

    public function cancelEdit()
    {
        $this->editId = null;
        $this->editSourceUrl = '';
        $this->editDestinationUrl = '';
        $this->resetValidation();
    }

    public function toggleStatus($id)
    {
        try {
            $redirect = Redirect::findOrFail($id);
            $redirect->is_active = !$redirect->is_active;
            $redirect->save();
            $this->dispatch('toast', 'وضعیت ریدایرکت با موفقیت تغییر کرد.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error toggling redirect status: ' . $e->getMessage());
            $this->dispatch('toast', 'خطا در تغییر وضعیت: ' . $e->getMessage(), ['type' => 'error']);
        }
    }

    public function deleteRedirect($id)
    {
        try {
            $redirect = Redirect::findOrFail($id);
            $redirect->delete();
            $this->selectedRedirects = array_diff($this->selectedRedirects, [$id]);
            $this->dispatch('toast', 'ریدایرکت با موفقیت حذف شد.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error deleting redirect: ' . $e->getMessage());
            $this->dispatch('toast', 'خطا در حذف ریدایرکت: ' . $e->getMessage(), ['type' => 'error']);
        }
    }

    public function deleteSelected()
    {
        if (empty($this->selectedRedirects)) {
            $this->dispatch('toast', 'هیچ ریدایرکتی انتخاب نشده است.', ['type' => 'warning']);
            return;
        }

        $this->dispatch('confirmDeleteSelected');
    }

    public function confirmDeleteSelected()
    {
        try {
            Redirect::whereIn('id', $this->selectedRedirects)->delete();
            $this->selectedRedirects = [];
            $this->selectAll = false;
            $this->dispatch('toast', 'ریدایرکت‌های انتخاب‌شده با موفقیت حذف شدند.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error deleting selected redirects: ' . $e->getMessage());
            $this->dispatch('toast', 'خطا در حذف ریدایرکت‌ها: ' . $e->getMessage(), ['type' => 'error']);
        }
    }

    public function export()
    {
        $redirects = Redirect::all();
        $csv = "آدرس مبدا,آدرس مقصد,وضعیت\n";
        foreach ($redirects as $redirect) {
            $csv .= "{$redirect->source_url},{$redirect->destination_url}," . ($redirect->is_active ? 'فعال' : 'غیرفعال') . "\n";
        }
        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, 'redirects.csv');
    }

    public function render()
    {
        $redirects = Redirect::where('source_url', 'like', '%' . $this->search . '%')
            ->orWhere('destination_url', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage);

        return view('livewire.admin.tools.redirects', [
            'redirects' => $redirects,
        ])->layout('admin.content.layouts.layoutMaster');
    }
}
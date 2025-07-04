<?php

namespace App\Livewire\Admin\Panel\Tools;

use App\Models\MailTemplate;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class MailTemplates extends Component
{
    use WithPagination;

    public $search             = '';
    public $newSubject         = '';
    public $newTemplate        = ''; // مقدار اولیه رشته خالی
    public $editId             = null;
    public $selectedTemplateId = null;
    public $perPage            = 10;
    public $selectedTemplates  = [];
    public $selectAll          = false;

    protected $rules = [
        'newSubject'  => 'required|string|max:255|unique:mail_templates,subject',
        'newTemplate' => 'required|string',
    ];

    protected $messages = [
        'newSubject.required'  => 'لطفاً عنوان قالب را وارد کنید.',
        'newSubject.string'    => 'عنوان قالب باید یک متن باشد.',
        'newSubject.max'       => 'عنوان قالب نباید بیشتر از 255 کاراکتر باشد.',
        'newSubject.unique'    => 'این عنوان قبلاً ثبت شده است.',
        'newTemplate.required' => 'لطفاً محتوای قالب را وارد کنید.',
        'newTemplate.string'   => 'محتوای قالب باید یک متن باشد.',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedTemplateId($value)
    {
        if ($value) {
            $template = MailTemplate::find($value);
            if ($template) {
                $this->newSubject  = $template->subject ?? '';
                $this->newTemplate = $template->template ?? '';
                $this->editId      = $template->id;
                $this->dispatch('updateEditor', $this->newTemplate);
            } else {
                $this->resetInputFields();
            }
        } else {
            $this->resetInputFields();
        }
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = MailTemplate::where('subject', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage)
            ->pluck('id')
            ->toArray();

        $this->selectedTemplates = $value ? $currentPageIds : [];
    }

    public function updatedSelectedTemplates()
    {
        $currentPageIds = MailTemplate::where('subject', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage)
            ->pluck('id')
            ->toArray();

        $this->selectAll = ! empty($this->selectedTemplates) && count(array_diff($currentPageIds, $this->selectedTemplates)) === 0;
    }

    public function addTemplate()
    {
        $this->validate();

        try {
            MailTemplate::create([
                'subject'   => $this->newSubject,
                'template'  => $this->newTemplate,
                'is_active' => true,
            ]);
            $this->resetInputFields();
            $this->dispatch('toast', 'قالب جدید با موفقیت اضافه شد.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error adding mail template: ' . $e->getMessage());
            $this->dispatch('toast', 'خطا در اضافه کردن قالب: ' . $e->getMessage(), ['type' => 'error']);
        }
    }

    public function startEdit($id)
    {
        $template          = MailTemplate::findOrFail($id);
        $this->editId      = $id;
        $this->newSubject  = $template->subject ?? '';
        $this->newTemplate = $template->template ?? '';
        $this->dispatch('updateEditor', $this->newTemplate);
        $this->resetValidation();
    }

    public function updateTemplate()
    {
        $this->rules['newSubject'] = "required|string|max:255|unique:mail_templates,subject,{$this->editId}";
        $this->validate();

        try {
            $template = MailTemplate::findOrFail($this->editId);
            $template->update([
                'subject'  => $this->newSubject,
                'template' => $this->newTemplate,
            ]);
            $this->resetInputFields();
            $this->dispatch('toast', 'قالب با موفقیت ویرایش شد.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error updating mail template: ' . $e->getMessage());
            $this->dispatch('toast', 'خطا در ویرایش قالب: ' . $e->getMessage(), ['type' => 'error']);
        }
    }

    public function cancelEdit()
    {
        $this->resetInputFields();
        $this->resetValidation();
    }

    public function toggleStatus($id)
    {
        try {
            $template            = MailTemplate::findOrFail($id);
            $template->is_active = ! $template->is_active;
            $template->save();
            $this->dispatch('toast', 'وضعیت قالب با موفقیت تغییر کرد.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error toggling mail template status: ' . $e->getMessage());
            $this->dispatch('toast', 'خطا در تغییر وضعیت: ' . $e->getMessage(), ['type' => 'error']);
        }
    }

    public function deleteTemplate($id)
    {
        try {
            $template = MailTemplate::findOrFail($id);
            $template->delete();
            $this->selectedTemplates = array_diff($this->selectedTemplates, [$id]);
            $this->dispatch('toast', 'قالب با موفقیت حذف شد.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error deleting mail template: ' . $e->getMessage());
            $this->dispatch('toast', 'خطا در حذف قالب: ' . $e->getMessage(), ['type' => 'error']);
        }
    }

    public function deleteSelected()
    {
        if (empty($this->selectedTemplates)) {
            $this->dispatch('toast', 'هیچ قالبی انتخاب نشده است.', ['type' => 'warning']);
            return;
        }

        $this->dispatch('confirmDeleteSelected');
    }

    public function confirmDeleteSelected()
    {
        try {
            MailTemplate::whereIn('id', $this->selectedTemplates)->delete();
            $this->selectedTemplates = [];
            $this->selectAll         = false;
            $this->dispatch('toast', 'قالب‌های انتخاب‌شده با موفقیت حذف شدند.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error deleting selected mail templates: ' . $e->getMessage());
            $this->dispatch('toast', 'خطا در حذف قالب‌ها: ' . $e->getMessage(), ['type' => 'error']);
        }
    }

    public function export()
    {
        $templates = MailTemplate::all();
        $csv       = "عنوان,محتوا,وضعیت\n";
        foreach ($templates as $template) {
            $csv .= "{$template->subject},\"" . str_replace('"', '""', $template->template ?? '') . "\"," . ($template->is_active ? 'فعال' : 'غیرفعال') . "\n";
        }
        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, 'mail-templates.csv');
    }

    public function previewTemplate($id)
    {
        $template        = MailTemplate::findOrFail($id);
        $templateContent = $template->template ?? '<p>محتوای قالب خالی است.</p>';

        // لاگ برای دیباگ

        $this->dispatch('openPreview', $templateContent);
    }

    private function resetInputFields()
    {
        $this->editId             = null;
        $this->newSubject         = '';
        $this->newTemplate        = '';
        $this->selectedTemplateId = null;
        $this->dispatch('updateEditor', '');
    }

    public function render()
    {
        $templates = MailTemplate::where('subject', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage);

        return view('livewire.admin.panel.tools.mail-templates', [
            'templates'    => $templates,
            'allTemplates' => MailTemplate::all(),
        ])->layout('admin.content.layouts.layoutMaster');
    }
}

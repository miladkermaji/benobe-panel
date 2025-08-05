<?php

namespace App\Livewire\Admin\Panel\Tools;

use App\Models\MailTemplate;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class MailTemplates extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteTemplateConfirmed' => 'deleteTemplate'];

    public $search = '';
    public $newSubject = '';
    public $newTemplate = '';
    public $editId = null;
    public $selectedTemplateId = null;
    public $perPage = 100;
    public $selectedTemplates = [];
    public $selectAll = false;
    public $readyToLoad = false;
    public $groupAction = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

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

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadMailTemplates()
    {
        $this->readyToLoad = true;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = $this->getMailTemplatesQuery()->pluck('id')->toArray();
        $this->selectedTemplates = $value ? $currentPageIds : [];
    }

    public function updatedSelectedTemplates()
    {
        $currentPageIds = $this->getMailTemplatesQuery()->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedTemplates) && count(array_diff($currentPageIds, $this->selectedTemplates)) === 0;
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedTemplates)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ قالبی انتخاب نشده است.');
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
        MailTemplate::whereIn('id', $this->selectedTemplates)
            ->update(['is_active' => $status]);

        $this->selectedTemplates = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت قالب‌های انتخاب‌شده با موفقیت تغییر کرد.');
    }

    public function updatedSelectedTemplateId($value)
    {
        if ($value) {
            $template = MailTemplate::find($value);
            if ($template) {
                $this->newSubject  = $template->subject ?? '';
                $this->newTemplate = $template->template ?? '';
                $this->editId      = $template->id;
            } else {
                $this->resetInputFields();
            }
        } else {
            $this->resetInputFields();
        }
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
            $this->dispatch('show-alert', type: 'success', message: 'قالب جدید با موفقیت اضافه شد.');
            $this->dispatch('close-modal', modalId: 'addTemplateModal');
        } catch (\Exception $e) {
            Log::error('Error adding mail template: ' . $e->getMessage());
            $this->dispatch('show-alert', type: 'error', message: 'خطا در اضافه کردن قالب: ' . $e->getMessage());
        }
    }

    public function startEdit($id)
    {
        $template          = MailTemplate::findOrFail($id);
        $this->editId      = $id;
        $this->newSubject  = $template->subject ?? '';
        $this->newTemplate = $template->template ?? '';
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
            $this->dispatch('show-alert', type: 'success', message: 'قالب با موفقیت ویرایش شد.');
            $this->dispatch('close-modal', modalId: 'addTemplateModal');
        } catch (\Exception $e) {
            Log::error('Error updating mail template: ' . $e->getMessage());
            $this->dispatch('show-alert', type: 'error', message: 'خطا در ویرایش قالب: ' . $e->getMessage());
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
            $template->is_active = !$template->is_active;
            $template->save();
            $this->dispatch('show-alert', type: 'success', message: 'وضعیت قالب با موفقیت تغییر کرد.');
        } catch (\Exception $e) {
            Log::error('Error toggling mail template status: ' . $e->getMessage());
            $this->dispatch('show-alert', type: 'error', message: 'خطا در تغییر وضعیت: ' . $e->getMessage());
        }
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteTemplate($id)
    {
        try {
            $template = MailTemplate::findOrFail($id);
            $template->delete();
            $this->selectedTemplates = array_diff($this->selectedTemplates, [$id]);
            $this->dispatch('show-alert', type: 'success', message: 'قالب با موفقیت حذف شد.');
        } catch (\Exception $e) {
            Log::error('Error deleting mail template: ' . $e->getMessage());
            $this->dispatch('show-alert', type: 'error', message: 'خطا در حذف قالب: ' . $e->getMessage());
        }
    }

    public function deleteSelected()
    {
        if (empty($this->selectedTemplates)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ قالبی انتخاب نشده است.');
            return;
        }

        MailTemplate::whereIn('id', $this->selectedTemplates)->delete();
        $this->selectedTemplates = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'قالب‌های انتخاب‌شده با موفقیت حذف شدند.');
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

        $this->dispatch('openPreview', $templateContent);
    }

    private function resetInputFields()
    {
        $this->editId             = null;
        $this->newSubject         = '';
        $this->newTemplate        = '';
        $this->selectedTemplateId = null;
    }

    private function getMailTemplatesQuery()
    {
        return MailTemplate::where('subject', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage);
    }

    public function render()
    {
        $templates = $this->readyToLoad ? $this->getMailTemplatesQuery() : null;

        return view('livewire.admin.panel.tools.mail-templates', [
            'templates'    => $templates,
            'allTemplates' => $this->readyToLoad ? MailTemplate::all() : collect(),
        ]);
    }
}

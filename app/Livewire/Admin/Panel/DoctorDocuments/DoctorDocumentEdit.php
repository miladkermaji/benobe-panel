<?php

namespace App\Livewire\Admin\Panel\DoctorDocuments;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\DoctorDocument;
use App\Models\Doctor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class DoctorDocumentEdit extends Component
{
    use WithFileUploads;

    public $document;
    public $doctor_id;
    public $file;
    public $title;
    public $is_verified;
    public $filePreview;
    public $doctors;

    public function mount($id)
    {
        $this->document = DoctorDocument::findOrFail($id);
        $this->doctor_id = $this->document->doctor_id;
        $this->title = $this->document->title;
        $this->is_verified = $this->document->is_verified;
        $this->filePreview = Storage::url($this->document->file_path);
        $this->doctors = Doctor::all();
    }

    public function updatedFile()
    {
        $this->filePreview = $this->file->temporaryUrl();
    }

    public function update()
    {
        $validated = $this->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'file' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx',
            'title' => 'nullable|string|max:255',
            'is_verified' => 'boolean',
        ], [
            'doctor_id.required' => 'لطفاً پزشک را انتخاب کنید.',
            'doctor_id.exists' => 'پزشک انتخاب‌شده معتبر نیست.',
            'file.file' => 'فایل انتخاب‌شده نامعتبر است.',
            'file.max' => 'حجم فایل نباید بیشتر از 10 مگابایت باشد.',
            'file.mimes' => 'فرمت فایل باید یکی از موارد jpg, png, pdf, doc, docx باشد.',
            'title.max' => 'عنوان نمی‌تواند بیشتر از 255 کاراکتر باشد.',
        ]);

        $data = [
            'doctor_id' => $this->doctor_id,
            'title' => $this->title,
            'is_verified' => $this->is_verified,
        ];

        if ($this->file) {
            if ($this->document->file_path && Storage::disk('public')->exists($this->document->file_path)) {
                Storage::disk('public')->delete($this->document->file_path);
            }
            $data['file_path'] = $this->file->store('doctor_documents', 'public');
            $data['file_type'] = $this->file->getClientOriginalExtension();
            $this->filePreview = Storage::url($data['file_path']); // به‌روزرسانی پیش‌نمایش بعد از آپلود
        }

        $this->document->update($data);
        Cache::forget('doctor_documents_' . ($this->search ?? '') . '_status__page_1');

        $this->dispatch('show-alert', type: 'success', message: 'مدرک با موفقیت به‌روزرسانی شد!');
        return redirect()->route('admin.panel.doctor-documents.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.doctor-documents.doctor-document-edit');
    }
}

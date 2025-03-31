<?php

namespace App\Livewire\Admin\Panel\DoctorDocuments;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Doctor;
use App\Models\DoctorDocument;

class DoctorDocumentCreate extends Component
{
    use WithFileUploads;

    public $doctor_id;
    public $file;
    public $title;
    public $is_verified = false;
    public $doctors;

    public function mount()
    {
        $this->doctors = Doctor::all();
    }

    public function store()
    {
        $validated = $this->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx', // حداکثر 10MB
            'title' => 'nullable|string|max:255',
            'is_verified' => 'boolean',
        ], [
            'doctor_id.required' => 'لطفاً پزشک را انتخاب کنید.',
            'doctor_id.exists' => 'پزشک انتخاب‌شده معتبر نیست.',
            'file.required' => 'لطفاً یک فایل انتخاب کنید.',
            'file.file' => 'فایل انتخاب‌شده نامعتبر است.',
            'file.max' => 'حجم فایل نباید بیشتر از 10 مگابایت باشد.',
            'file.mimes' => 'فرمت فایل باید یکی از موارد jpg, png, pdf, doc, docx باشد.',
            'title.max' => 'عنوان نمی‌تواند بیشتر از 255 کاراکتر باشد.',
        ]);

        $filePath = $this->file->store('doctor_documents', 'public');
        $fileType = $this->file->getClientOriginalExtension();

        DoctorDocument::create([
            'doctor_id' => $this->doctor_id,
            'file_path' => $filePath,
            'file_type' => $fileType,
            'title' => $this->title,
            'is_verified' => $this->is_verified,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'مدرک با موفقیت ثبت شد!');
        return redirect()->route('admin.panel.doctordocuments.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.doctordocuments.doctordocument-create');
    }
}

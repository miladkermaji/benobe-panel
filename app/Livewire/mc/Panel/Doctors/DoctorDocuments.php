<?php

namespace App\Livewire\Mc\Panel\Doctors;

use App\Models\DoctorDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Traits\HasSelectedDoctor;

class DoctorDocuments extends Component
{
    use WithFileUploads;
    use HasSelectedDoctor;

    public $doctorId;
    public $files = [];
    public $titles = [];

    public function mount($doctorId = null)
    {
        if (Auth::guard('medical_center')->check()) {
            $this->doctorId = $this->getSelectedDoctorId();
        } else {
            $this->doctorId = $doctorId;
        }
    }

    public function uploadFiles()
    {
        $this->validate([
            'files.*' => 'file|max:5120', // حداکثر 5 مگابایت برای هر فایل
            'titles.*' => 'nullable|string|max:255',
        ]);

        if (!$this->doctorId) {
            $this->dispatch('show-alert', type: 'error', message: 'پزشک انتخاب نشده است.');
            return;
        }

        foreach ($this->files as $index => $file) {
            $path = $file->store('doctor_documents', 'public');
            $fileType = $file->getMimeType();

            DoctorDocument::create([
                'doctor_id' => $this->doctorId,
                'file_path' => $path,
                'file_type' => $fileType,
                'title' => $this->titles[$index] ?? null,
                'is_verified' => false, // صراحتاً false تنظیم می‌شه
            ]);
        }

        $this->files = [];
        $this->titles = [];
        $this->dispatch('show-alert', type: 'success', message: 'مدارک با موفقیت آپلود شدند!');
    }

    public function deleteFile($id)
    {
        $document = DoctorDocument::findOrFail($id);

        // بررسی اینکه مدرک متعلق به پزشک انتخاب‌شده باشد
        if ($document->doctor_id != $this->doctorId) {
            $this->dispatch('show-alert', type: 'error', message: 'شما اجازه حذف این مدرک را ندارید.');
            return;
        }

        Storage::disk('public')->delete($document->file_path);
        $document->delete();
        $this->dispatch('show-alert', type: 'success', message: 'مدرک حذف شد!');
    }

    public function render()
    {
        if (!$this->doctorId) {
            return view('livewire.mc.panel.doctors.doctor-documents', ['documents' => collect()]);
        }

        $documents = DoctorDocument::where('doctor_id', $this->doctorId)->get();
        return view('livewire.mc.panel.doctors.doctor-documents', compact('documents'));
    }
}

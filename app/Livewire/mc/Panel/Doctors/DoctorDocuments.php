<?php

namespace App\Livewire\Dr\Panel\Doctors;

use App\Models\DoctorDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class DoctorDocuments extends Component
{
    use WithFileUploads;

    public $doctorId;
    public $files = [];
    public $titles = [];

    public function mount($doctorId)
    {
        $this->doctorId = $doctorId;
    }

    public function uploadFiles()
    {
        $this->validate([
            'files.*' => 'file|max:5120', // حداکثر 5 مگابایت برای هر فایل
            'titles.*' => 'nullable|string|max:255',
        ]);

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
        Storage::disk('public')->delete($document->file_path);
        $document->delete();
        $this->dispatch('show-alert', type: 'success', message: 'مدرک حذف شد!');
    }

    public function render()
    {
        $documents = DoctorDocument::where('doctor_id', $this->doctorId)->get();
        return view('livewire.dr.panel.doctors.doctor-documents', compact('documents'));
    }
}

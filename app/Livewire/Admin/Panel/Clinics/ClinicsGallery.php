<?php

namespace App\Livewire\Admin\Panel\Clinics;

use App\Models\Clinic;
use App\Models\ClinicGallery;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ClinicsGallery extends Component
{
    use WithFileUploads;

    public $clinic;
    public $images   = [];
    public $captions = [];

    public function mount($id)
    {
        $this->clinic = Clinic::with('galleries')->findOrFail($id);
    }

    public function uploadImages()
    {
        $this->validate([
            'images.*'   => 'image|max:2048',
            'captions.*' => 'nullable|string|max:255',
        ]);

        foreach ($this->images as $index => $image) {
            $path = $image->store('clinic_galleries', 'public');
            ClinicGallery::create([
                'clinic_id'  => $this->clinic->id,
                'image_path' => $path,
                'caption'    => $this->captions[$index] ?? null,
            ]);
        }

        $this->images   = [];
        $this->captions = [];
        $this->dispatch('show-alert', type: 'success', message: 'تصاویر با موفقیت آپلود شدند!');
    }

    public function deleteImage($id)
    {
        $gallery = ClinicGallery::findOrFail($id);
        Storage::disk('public')->delete($gallery->image_path);
        $gallery->delete();
        $this->dispatch('show-alert', type: 'success', message: 'تصویر حذف شد!');
    }

    public function setPrimary($id)
    {
        $this->clinic->galleries()->update(['is_primary' => false]);
        $gallery = ClinicGallery::findOrFail($id);
        $gallery->update(['is_primary' => true]);
        $this->dispatch('show-alert', type: 'success', message: 'تصویر اصلی تنظیم شد!');
    }

    public function render()
    {
        $galleries = $this->clinic->galleries ?? collect();
        return view('livewire.admin.panel.clinics.clinics-gallery', compact('galleries'));
    }
}

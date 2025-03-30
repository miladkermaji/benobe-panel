<?php

namespace App\Livewire\Admin\Panel\Hospitals;

use App\Models\Hospital;
use App\Models\HospitalGallery;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class HospitalsGallery extends Component
{
    use WithFileUploads;

    public $hospital;
    public $images   = [];
    public $captions = [];

    public function mount($id)
    {
        $this->hospital = Hospital::findOrFail($id);
    }

    public function uploadImages()
    {
        $this->validate([
            'images.*'   => 'image|max:2048', // حداکثر 2MB برای هر تصویر
            'captions.*' => 'nullable|string|max:255',
        ]);

        foreach ($this->images as $index => $image) {
            $path = $image->store('hospital_galleries', 'public');
            HospitalGallery::create([
                'hospital_id' => $this->hospital->id,
                'image_path'  => $path,
                'caption'     => $this->captions[$index] ?? null,
            ]);
        }

        $this->images   = [];
        $this->captions = [];
        $this->dispatch('show-alert', type: 'success', message: 'تصاویر با موفقیت آپلود شدند!');
    }

    public function deleteImage($id)
    {
        $gallery = HospitalGallery::findOrFail($id);
        Storage::disk('public')->delete($gallery->image_path);
        $gallery->delete();
        $this->dispatch('show-alert', type: 'success', message: 'تصویر حذف شد!');
    }

    public function setPrimary($id)
    {
        $this->hospital->galleries()->update(['is_primary' => false]);
        $gallery = HospitalGallery::findOrFail($id);
        $gallery->update(['is_primary' => true]);
        $this->dispatch('show-alert', type: 'success', message: 'تصویر اصلی تنظیم شد!');
    }

    public function render()
    {
        $galleries = $this->hospital->galleries;
        return view('livewire.admin.panel.hospitals.hospitals-gallery', compact('galleries'));
    }
}

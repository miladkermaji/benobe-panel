<?php

namespace App\Livewire\Admin\Panel\ImagingCenters;

use Livewire\Component;
use App\Models\ImagingCenter;
use App\Models\MedicalCenter;
use Livewire\WithFileUploads;
use App\Models\ImagingCenterGallery;
use Illuminate\Support\Facades\Storage;

class ImagingCentersGallery extends Component
{
     use WithFileUploads;

    public $imagingCenter;
    public $images = [];
    public $captions = [];

    public function mount($id)
    {
        $this->imagingCenter = MedicalCenter::findOrFail($id);
    }

    public function uploadImages()
    {
        $this->validate([
            'images.*' => 'image|max:2048',
            'captions.*' => 'nullable|string|max:255',
        ]);

        $galleries = $this->imagingCenter->galleries ?? [];
        foreach ($this->images as $index => $image) {
            $path = $image->store('hospital_galleries', 'public');
            $galleries[] = [
                'image_path' => $path,
                'caption' => $this->captions[$index] ?? null,
                'is_primary' => count($galleries) === 0, // اولین تصویر به‌طور پیش‌فرض اصلی باشد
            ];
        }

        $this->imagingCenter->update(['galleries' => $galleries]);
        $this->reset(['images', 'captions']); // پاک‌سازی متغیرها
        $this->dispatch('refresh-gallery'); // ارسال رویداد برای رفرش گالری
        $this->dispatch('show-alert', type: 'success', message: 'تصاویر با موفقیت اضافه شدند!');
    }

    public function deleteImage($index)
    {
        $galleries = $this->imagingCenter->galleries ?? [];
        if (isset($galleries[$index])) {
            Storage::disk('public')->delete($galleries[$index]['image_path']);
            unset($galleries[$index]);
            $this->imagingCenter->update(['galleries' => array_values($galleries)]);
            $this->dispatch('refresh-gallery');
            $this->dispatch('show-alert', type: 'success', message: 'تصویر حذف شد!');
        }
    }

    public function setPrimary($index)
    {
        $galleries = $this->imagingCenter->galleries ?? [];
        foreach ($galleries as &$gallery) {
            $gallery['is_primary'] = false;
        }
        if (isset($galleries[$index])) {
            $galleries[$index]['is_primary'] = true;
            $this->imagingCenter->update(['galleries' => $galleries]);
            $this->dispatch('refresh-gallery');
            $this->dispatch('show-alert', type: 'success', message: 'تصویر اصلی تنظیم شد!');
        }
    }

    public function render()
    {
        $galleries = collect($this->imagingCenter->galleries ?? []);
        return view('livewire.admin.panel.imaging-centers.imaging-centers-gallery', compact('galleries'));
    }
}

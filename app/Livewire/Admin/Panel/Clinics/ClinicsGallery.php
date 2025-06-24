<?php

namespace App\Livewire\Admin\Panel\Clinics;

use App\Models\MedicalCenter;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class ClinicsGallery extends Component
{
    use WithFileUploads;

    public $clinic;
    public $images = [];
    public $captions = [];

    public function mount($id)
    {
        $this->clinic = MedicalCenter::findOrFail($id);
    }

    public function uploadImages()
    {
        $this->validate([
            'images.*' => 'image|max:2048',
            'captions.*' => 'nullable|string|max:255',
        ]);

        $galleries = $this->clinic->galleries ?? [];
        foreach ($this->images as $index => $image) {
            $path = $image->store('clinic_galleries', 'public');
            $galleries[] = [
                'image_path' => $path,
                'caption' => $this->captions[$index] ?? null,
                'is_primary' => count($galleries) === 0, // اولین تصویر به‌طور پیش‌فرض اصلی باشد
            ];
        }

        $this->clinic->update(['galleries' => $galleries]);
        $this->reset(['images', 'captions']); // پاک‌سازی متغیرها
        $this->dispatch('refresh-gallery'); // ارسال رویداد برای رفرش گالری
        $this->dispatch('show-alert', type: 'success', message: 'تصاویر با موفقیت اضافه شدند!');
    }

    public function deleteImage($index)
    {
        $galleries = $this->clinic->galleries ?? [];
        if (isset($galleries[$index])) {
            Storage::disk('public')->delete($galleries[$index]['image_path']);
            unset($galleries[$index]);
            $this->clinic->update(['galleries' => array_values($galleries)]);
            $this->dispatch('refresh-gallery');
            $this->dispatch('show-alert', type: 'success', message: 'تصویر حذف شد!');
        }
    }

    public function setPrimary($index)
    {
        $galleries = $this->clinic->galleries ?? [];
        foreach ($galleries as &$gallery) {
            $gallery['is_primary'] = false;
        }
        if (isset($galleries[$index])) {
            $galleries[$index]['is_primary'] = true;
            $this->clinic->update(['galleries' => $galleries]);
            $this->dispatch('refresh-gallery');
            $this->dispatch('show-alert', type: 'success', message: 'تصویر اصلی تنظیم شد!');
        }
    }

    public function render()
    {
        $galleries = collect($this->clinic->galleries ?? []);
        return view('livewire.admin.panel.clinics.clinics-gallery', compact('galleries'));
    }
}

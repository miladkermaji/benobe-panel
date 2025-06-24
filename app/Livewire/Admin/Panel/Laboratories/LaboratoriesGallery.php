<?php

namespace App\Livewire\Admin\Panel\Laboratories;

use Livewire\Component;
use App\Models\Laboratory;
use App\Models\MedicalCenter;
use Livewire\WithFileUploads;
use App\Models\LaboratoryGallery;
use Illuminate\Support\Facades\Storage;

class LaboratoriesGallery extends Component
{
    use WithFileUploads;

    public $laboratory;
    public $images = [];
    public $captions = [];

    public function mount($id)
    {
        $this->laboratory = MedicalCenter::findOrFail($id);
    }

    public function uploadImages()
    {
        $this->validate([
            'images.*' => 'image|max:2048',
            'captions.*' => 'nullable|string|max:255',
        ]);

        $galleries = $this->laboratory->galleries ?? [];
        foreach ($this->images as $index => $image) {
            $path = $image->store('hospital_galleries', 'public');
            $galleries[] = [
                'image_path' => $path,
                'caption' => $this->captions[$index] ?? null,
                'is_primary' => count($galleries) === 0, // اولین تصویر به‌طور پیش‌فرض اصلی باشد
            ];
        }

        $this->laboratory->update(['galleries' => $galleries]);
        $this->reset(['images', 'captions']); // پاک‌سازی متغیرها
        $this->dispatch('refresh-gallery'); // ارسال رویداد برای رفرش گالری
        $this->dispatch('show-alert', type: 'success', message: 'تصاویر با موفقیت اضافه شدند!');
    }

    public function deleteImage($index)
    {
        $galleries = $this->laboratory->galleries ?? [];
        if (isset($galleries[$index])) {
            Storage::disk('public')->delete($galleries[$index]['image_path']);
            unset($galleries[$index]);
            $this->laboratory->update(['galleries' => array_values($galleries)]);
            $this->dispatch('refresh-gallery');
            $this->dispatch('show-alert', type: 'success', message: 'تصویر حذف شد!');
        }
    }

    public function setPrimary($index)
    {
        $galleries = $this->laboratory->galleries ?? [];
        foreach ($galleries as &$gallery) {
            $gallery['is_primary'] = false;
        }
        if (isset($galleries[$index])) {
            $galleries[$index]['is_primary'] = true;
            $this->laboratory->update(['galleries' => $galleries]);
            $this->dispatch('refresh-gallery');
            $this->dispatch('show-alert', type: 'success', message: 'تصویر اصلی تنظیم شد!');
        }
    }

    public function render()
    {
        $galleries = collect($this->laboratory->galleries ?? []);
        return view('livewire.admin.panel.laboratories.laboratories-gallery', compact('galleries'));
    }
}

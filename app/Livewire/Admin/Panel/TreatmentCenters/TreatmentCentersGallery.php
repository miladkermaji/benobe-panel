<?php

namespace App\Livewire\Admin\Panel\TreatmentCenters;

use App\Models\TreatmentCenter;
use App\Models\TreatmentCenterGallery;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class TreatmentCentersGallery extends Component
{
    use WithFileUploads;

    public $treatmentCenter;
    public $images   = [];
    public $captions = [];

    public function mount($id)
    {
        $this->treatmentCenter = TreatmentCenter::with('galleries')->findOrFail($id);
    }

    public function uploadImages()
    {
        $this->validate([
            'images.*'   => 'image|max:2048', // حداکثر 2 مگابایت
            'captions.*' => 'nullable|string|max:255',
        ], [
            'images.*.image' => 'فایل باید تصویر باشد.',
            'images.*.max'   => 'حجم تصویر نباید بیشتر از 2 مگابایت باشد.',
            'captions.*.max' => 'توضیحات نباید بیشتر از 255 کاراکتر باشد.',
        ]);

        foreach ($this->images as $index => $image) {
            $path = $image->store('treatment_center_galleries', 'public');
            TreatmentCenterGallery::create([
                'treatment_center_id' => $this->treatmentCenter->id,
                'image_path'          => $path,
                'caption'             => $this->captions[$index] ?? null,
            ]);
        }

        $this->images   = [];
        $this->captions = [];
        $this->dispatch('show-alert', type: 'success', message: 'تصاویر با موفقیت آپلود شدند!');
    }

    public function deleteImage($id)
    {
        $gallery = TreatmentCenterGallery::findOrFail($id);
        Storage::disk('public')->delete($gallery->image_path);
        $gallery->delete();
        $this->dispatch('show-alert', type: 'success', message: 'تصویر حذف شد!');
    }

    public function setPrimary($id)
    {
        $this->treatmentCenter->galleries()->update(['is_primary' => false]);
        $gallery = TreatmentCenterGallery::findOrFail($id);
        $gallery->update(['is_primary' => true]);
        $this->dispatch('show-alert', type: 'success', message: 'تصویر اصلی تنظیم شد!');
    }

    public function render()
    {
        $galleries = $this->treatmentCenter->galleries ?? collect();
        return view('livewire.admin.panel.treatment-centers.treatment-centers-gallery', compact('galleries'));
    }
}

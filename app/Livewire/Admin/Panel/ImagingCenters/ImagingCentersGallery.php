<?php

namespace App\Livewire\Admin\Panel\ImagingCenters;

use App\Models\ImagingCenter;
use App\Models\ImagingCenterGallery;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImagingCentersGallery extends Component
{
    use WithFileUploads;

    public $imaging_center;
    public $images   = [];
    public $captions = [];

    public function mount($id)
    {
        $this->imaging_center = ImagingCenter::with('galleries')->findOrFail($id);
    }

    public function uploadImages()
    {
        $this->validate([
            'images.*'   => 'image|max:2048',
            'captions.*' => 'nullable|string|max:255',
        ], [
            'images.*.image'    => 'فایل باید یک تصویر باشد.',
            'images.*.max'      => 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد.',
            'captions.*.string' => 'توضیحات باید متن باشد.',
            'captions.*.max'    => 'توضیحات نباید بیشتر از ۲۵۵ حرف باشد.',
        ]);

        foreach ($this->images as $index => $image) {
            $path = $image->store('imaging_center_galleries', 'public');
            ImagingCenterGallery::create([
                'imaging_center_id' => $this->imaging_center->id,
                'image_path'        => $path,
                'caption'           => $this->captions[$index] ?? null,
            ]);
        }

        $this->images   = [];
        $this->captions = [];
        $this->dispatch('show-alert', type: 'success', message: 'تصاویر با موفقیت آپلود شدند!');
    }

    public function deleteImage($id)
    {
        $gallery = ImagingCenterGallery::findOrFail($id);
        Storage::disk('public')->delete($gallery->image_path);
        $gallery->delete();
        $this->dispatch('show-alert', type: 'success', message: 'تصویر حذف شد!');
    }

    public function setPrimary($id)
    {
        $this->imaging_center->galleries()->update(['is_primary' => false]);
        $gallery = ImagingCenterGallery::findOrFail($id);
        $gallery->update(['is_primary' => true]);
        $this->dispatch('show-alert', type: 'success', message: 'تصویر اصلی تنظیم شد!');
    }

    public function render()
    {
        $galleries = $this->imaging_center->galleries ?? collect();
        return view('livewire.admin.panel.imaging-centers.imaging-centers-gallery', compact('galleries'));
    }
}

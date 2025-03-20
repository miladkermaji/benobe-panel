<?php
namespace App\Livewire\Admin\Panel\Laboratories;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Laboratory;
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
        // Eager-load the galleries relationship
        $this->laboratory = Laboratory::with('galleries')->findOrFail($id);
    }

    public function uploadImages()
    {
        $this->validate([
            'images.*' => 'image|max:2048',
            'captions.*' => 'nullable|string|max:255',
        ]);

        foreach ($this->images as $index => $image) {
            $path = $image->store('laboratory_galleries', 'public');
            LaboratoryGallery::create([
                'laboratory_id' => $this->laboratory->id,
                'image_path' => $path,
                'caption' => $this->captions[$index] ?? null,
            ]);
        }

        $this->images = [];
        $this->captions = [];
        $this->dispatch('show-alert', type: 'success', message: 'تصاویر با موفقیت آپلود شدند!');
    }

    public function deleteImage($id)
    {
        $gallery = LaboratoryGallery::findOrFail($id);
        Storage::disk('public')->delete($gallery->image_path);
        $gallery->delete();
        $this->dispatch('show-alert', type: 'success', message: 'تصویر حذف شد!');
    }

    public function setPrimary($id)
    {
        $this->laboratory->galleries()->update(['is_primary' => false]);
        $gallery = LaboratoryGallery::findOrFail($id);
        $gallery->update(['is_primary' => true]);
        $this->dispatch('show-alert', type: 'success', message: 'تصویر اصلی تنظیم شد!');
    }

    public function render()
    {
        // Ensure $galleries is always an array or collection
        $galleries = $this->laboratory->galleries ?? collect();
        return view('livewire.admin.panel.laboratories.laboratories-gallery', compact('galleries'));
    }
}
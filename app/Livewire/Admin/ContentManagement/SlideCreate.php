<?php

namespace App\Livewire\Admin\ContentManagement;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin\ContentManagement\Slider\Slide;

class SlideCreate extends Component
{
    use WithFileUploads;

    public $title;
    public $image; // حالا فایل هست
    public $link;
    public $description;
    public $display = 'site';
    public $status = true;

    protected $rules = [
        'title' => 'required|string|max:255',
        'image' => 'required|image|max:2048', // حداکثر 2MB
        'link' => 'nullable|string|max:255',
        'description' => 'nullable|string',
        'display' => 'required|in:site,mobile',
        'status' => 'boolean',
    ];

    public function save()
    {
        $this->validate();

        try {
            // آپلود تصویر
            $imagePath = $this->image->store('slides', 'public');

            Slide::create([
                'title' => $this->title,
                'image' => $imagePath,
                'link' => $this->link,
                'description' => $this->description,
                'display' => $this->display,
                'status' => $this->status,
            ]);

            Log::info('New slide created', ['title' => $this->title]);
            $this->dispatch('toast', 'اسلایدر با موفقیت اضافه شد.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);

            $this->reset();
            return redirect()->route('admin.content-management.slide.index');
        } catch (\Exception $e) {
            Log::error('Error creating slide:', ['message' => $e->getMessage()]);
            $this->dispatch('toast', 'خطا در افزودن اسلایدر: ' . $e->getMessage(), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.content-management.slide-create');
    }
}
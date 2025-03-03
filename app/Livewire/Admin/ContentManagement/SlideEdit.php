<?php

namespace App\Livewire\Admin\ContentManagement;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin\ContentManagement\Slider\Slide;

class SlideEdit extends Component
{
    use WithFileUploads;

    public $slideId;
    public $title;
    public $image; // فایل جدید
    public $currentImage; // تصویر فعلی
    public $link;
    public $description;
    public $display;
    public $status;

    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048', // آپلود اختیاری
            'link' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'display' => 'required|in:site,mobile',
            'status' => 'boolean',
        ];
    }

    public function mount($id)
    {
        $this->slideId = $id;
        $slide = Slide::findOrFail($id);
        $this->title = $slide->title;
        $this->currentImage = $slide->image; // تصویر فعلی
        $this->link = $slide->link;
        $this->description = $slide->description;
        $this->display = $slide->display;
        $this->status = $slide->status;
    }

    public function update()
    {
        $this->validate();

        try {
            $slide = Slide::findOrFail($this->slideId);

            $data = [
                'title' => $this->title,
                'link' => $this->link,
                'description' => $this->description,
                'display' => $this->display,
                'status' => $this->status,
            ];

            // اگه تصویر جدید آپلود شده، تصویر قبلی رو پاک کن و جدید رو ذخیره کن
            if ($this->image) {
                if ($slide->image && Storage::disk('public')->exists($slide->image)) {
                    Storage::disk('public')->delete($slide->image);
                }
                $data['image'] = $this->image->store('slides', 'public');
            }

            $slide->update($data);

            Log::info('Slide updated', ['id' => $this->slideId]);
            $this->dispatch('toast', 'اسلایدر با موفقیت ویرایش شد.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);

            return redirect()->route('admin.content-management.slide.index');
        } catch (\Exception $e) {
            Log::error('Error updating slide:', ['message' => $e->getMessage()]);
            $this->dispatch('toast', 'خطا در ویرایش اسلایدر: ' . $e->getMessage(), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.content-management.slide-edit');
    }
}
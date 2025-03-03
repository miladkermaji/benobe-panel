<?php

namespace App\Livewire\Admin\ContentManagement;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin\ContentManagement\HomeVideo\HomeVideo;

class HomeVideoEdit extends Component
{
    use WithFileUploads;

    public $videoId;
    public $title;
    public $image;
    public $existingImage;
    public $video;
    public $existingVideo;
    public $description;
    public $approve;

    protected $rules = [
        'title' => 'required|string|max:255',
        'image' => 'nullable|image|max:2048', // حداکثر 2 مگابایت
        'video' => 'nullable|mimetypes:video/mp4|max:102400', // حداکثر 100 مگابایت
        'description' => 'nullable|string',
        'approve' => 'boolean',
    ];

    public function mount($id)
    {
        $this->videoId = $id;
        $video = HomeVideo::findOrFail($id);
        $this->title = $video->title;
        $this->existingImage = $video->image;
        $this->existingVideo = $video->video;
        $this->description = $video->description;
        $this->approve = $video->approve;
    }

    public function save()
    {
        $this->validate();

        try {
            $video = HomeVideo::findOrFail($this->videoId);

            $data = [
                'title' => $this->title,
                'description' => $this->description,
                'approve' => $this->approve,
            ];

            // آپدیت تصویر (اگه جدید انتخاب شده، قبلی رو حذف کن)
            if ($this->image) {
                if ($video->image && Storage::disk('public')->exists($video->image)) {
                    Storage::disk('public')->delete($video->image);
                }
                $data['image'] = $this->image->store('home_videos', 'public');
            } else {
                $data['image'] = $this->existingImage;
            }

            // آپدیت ویدئو (اگه جدید انتخاب شده، قبلی رو حذف کن)
            if ($this->video) {
                if ($video->video && Storage::disk('public')->exists($video->video)) {
                    Storage::disk('public')->delete($video->video);
                }
                $data['video'] = $this->video->store('home_videos', 'public');
            } else {
                $data['video'] = $this->existingVideo;
            }

            $video->update($data);

            $this->dispatch('toast', 'ویدئو با موفقیت ویرایش شد.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);

            return redirect()->route('admin.content-management.home-video.index');
        } catch (\Exception $e) {
            Log::error('Error updating home video:', ['message' => $e->getMessage()]);
            $this->dispatch('toast', 'خطا در ویرایش ویدئو: ' . $e->getMessage(), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.content-management.home-video-edit');
    }
}
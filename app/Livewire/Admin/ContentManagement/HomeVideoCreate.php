<?php

namespace App\Livewire\Admin\ContentManagement;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin\ContentManagement\HomeVideo\HomeVideo;

class HomeVideoCreate extends Component
{
    use WithFileUploads;

    public $title;
    public $image;
    public $video;
    public $description;
    public $approve = true;

    protected $rules = [
        'title' => 'required|string|max:255',
        'image' => 'nullable|image|max:2048', // حداکثر 2 مگابایت
        'video' => 'nullable|mimetypes:video/mp4|max:102400', // حداکثر 100 مگابایت
        'description' => 'nullable|string',
        'approve' => 'boolean',
    ];

    public function save()
    {
        $this->validate();

        try {
            $data = [
                'title' => $this->title,
                'description' => $this->description,
                'approve' => $this->approve,
            ];

            if ($this->image) {
                $data['image'] = $this->image->store('home_videos', 'public');
            }

            if ($this->video) {
                $data['video'] = $this->video->store('home_videos', 'public');
            }

            HomeVideo::create($data);

            $this->dispatch('toast', 'ویدئو با موفقیت اضافه شد.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);

            return redirect()->route('admin.content-management.home-video.index');
        } catch (\Exception $e) {
            Log::error('Error creating home video:', ['message' => $e->getMessage()]);
            $this->dispatch('toast', 'خطا در افزودن ویدئو: ' . $e->getMessage(), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.content-management.home-video-create');
    }
}
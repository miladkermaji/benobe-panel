<?php

namespace App\Livewire\Admin\ContentManagement;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin\ContentManagement\FrontPages\FrontPage;

class FrontPageCreate extends Component
{
    use WithFileUploads;

    public $page_url;
    public $title;
    public $image;
    public $lead;
    public $description;
    public $approve = true;
    public $tplname;

    protected $rules = [
        'page_url' => 'required|string|max:255|unique:front_pages,page_url',
        'title' => 'required|string|max:255',
        'image' => 'nullable|image|max:2048', // حداکثر 2 مگابایت
        'lead' => 'nullable|string',
        'description' => 'nullable|string',
        'approve' => 'boolean',
        'tplname' => 'nullable|string|max:255',
    ];

    public function save()
    {
        $this->validate();

        try {
            $data = [
                'page_url' => $this->page_url,
                'title' => $this->title,
                'lead' => $this->lead,
                'description' => $this->description,
                'approve' => $this->approve,
                'tplname' => $this->tplname,
            ];

            if ($this->image) {
                $data['image'] = $this->image->store('front_pages', 'public');
            }

            FrontPage::create($data);

            $this->dispatch('toast', 'صفحه با موفقیت اضافه شد.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);

            return redirect()->route('admin.content-management.front-pages.index');
        } catch (\Exception $e) {
            Log::error('Error creating front page:', ['message' => $e->getMessage()]);
            $this->dispatch('toast', 'خطا در افزودن صفحه: ' . $e->getMessage(), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.content-management.front-page-create');
    }
}
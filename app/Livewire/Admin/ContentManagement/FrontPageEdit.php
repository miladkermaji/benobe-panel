<?php

namespace App\Livewire\Admin\ContentManagement;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin\ContentManagement\FrontPages\FrontPage;

class FrontPageEdit extends Component
{
    use WithFileUploads;

    public $pageId;
    public $page_url;
    public $title;
    public $image;
    public $existingImage;
    public $lead;
    public $description;
    public $approve;
    public $tplname;

    protected $rules = [
        'page_url' => 'required|string|max:255|unique:front_pages,page_url,{{ $this->pageId }}',
        'title' => 'required|string|max:255',
        'image' => 'nullable|image|max:2048',
        'lead' => 'nullable|string',
        'description' => 'nullable|string',
        'approve' => 'boolean',
        'tplname' => 'nullable|string|max:255',
    ];

    public function mount($id)
    {
        $this->pageId = $id;
        $page = FrontPage::findOrFail($id);
        $this->page_url = $page->page_url;
        $this->title = $page->title;
        $this->existingImage = $page->image;
        $this->lead = $page->lead;
        $this->description = $page->description;
        $this->approve = $page->approve;
        $this->tplname = $page->tplname;
    }

    public function save()
    {
        $this->validate();

        try {
            $page = FrontPage::findOrFail($this->pageId);

            $data = [
                'page_url' => $this->page_url,
                'title' => $this->title,
                'lead' => $this->lead,
                'description' => $this->description,
                'approve' => $this->approve,
                'tplname' => $this->tplname,
            ];

            if ($this->image) {
                if ($page->image && Storage::disk('public')->exists($page->image)) {
                    Storage::disk('public')->delete($page->image);
                }
                $data['image'] = $this->image->store('front_pages', 'public');
            } else {
                $data['image'] = $this->existingImage;
            }

            $page->update($data);

            $this->dispatch('toast', 'صفحه با موفقیت ویرایش شد.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);

            return redirect()->route('admin.content-management.front-pages.index');
        } catch (\Exception $e) {
            Log::error('Error updating front page:', ['message' => $e->getMessage()]);
            $this->dispatch('toast', 'خطا در ویرایش صفحه: ' . $e->getMessage(), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.content-management.front-page-edit');
    }
}
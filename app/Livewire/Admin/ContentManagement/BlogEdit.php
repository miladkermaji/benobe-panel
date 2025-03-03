<?php

namespace App\Livewire\Admin\ContentManagement;

use Livewire\Component;
use Livewire\WithFileUploads;
use Morilog\Jalali\CalendarUtils;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin\ContentManagement\Blog\Blog;
use App\Models\Admin\ContentManagement\Blog\CategoryBlog;

class BlogEdit extends Component
{
    use WithFileUploads;

    public $blogId;
    public $title;
    public $category_id;
    public $selectedDate; // تاریخ جلالی برای نمایش
    public $short_description;
    public $content;
    public $image;
    public $currentImage;
    public $is_index;
    public $status;
    public $page_title;
    public $url_seo;
    public $meta_description;

    public $categories = [];

    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:category_blogs,id',
            'selectedDate' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'content' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'is_index' => 'boolean',
            'status' => 'boolean',
            'page_title' => 'nullable|string|max:255',
            'url_seo' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:160',
        ];
    }

    public function mount($id)
    {
        $this->blogId = $id;
        $blog = Blog::findOrFail($id);
        $this->title = $blog->title;
        $this->category_id = $blog->category_id;
        $this->selectedDate = CalendarUtils::strftime('Y/m/d', $blog->date); // تاریخ جلالی
        $this->short_description = $blog->short_description;
        $this->content = $blog->content;
        $this->currentImage = $blog->image;
        $this->is_index = $blog->is_index;
        $this->status = $blog->status;
        $this->page_title = $blog->page_title;
        $this->url_seo = $blog->url_seo;
        $this->meta_description = $blog->meta_description;

        $this->categories = CategoryBlog::pluck('name', 'id')->toArray();
    }

    public function update()
    {
        $this->validate();

        try {
            $blog = Blog::findOrFail($this->blogId);

            // تبدیل تاریخ جلالی به میلادی
            $gregorianDate = CalendarUtils::createDatetimeFromFormat('Y/m/d', $this->selectedDate)->format('Y-m-d H:i:s');

            $data = [
                'title' => $this->title,
                'category_id' => $this->category_id,
                'date' => $gregorianDate, // تاریخ میلادی
                'short_description' => $this->short_description,
                'content' => $this->content,
                'is_index' => $this->is_index,
                'status' => $this->status,
                'page_title' => $this->page_title,
                'url_seo' => $this->url_seo,
                'meta_description' => $this->meta_description,
            ];

            if ($this->image) {
                if ($blog->image && Storage::disk('public')->exists($blog->image)) {
                    Storage::disk('public')->delete($blog->image);
                }
                $data['image'] = $this->image->store('blogs', 'public');
            }

            $blog->update($data);

            Log::info('Blog updated', ['id' => $this->blogId]);
            $this->dispatch('toast', 'خبر با موفقیت ویرایش شد.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);

            return redirect()->route('admin.content-management.blog.index');
        } catch (\Exception $e) {
            Log::error('Error updating blog:', ['message' => $e->getMessage()]);
            $this->dispatch('toast', 'خطا در ویرایش خبر: ' . $e->getMessage(), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.content-management.blog-edit');
    }
}
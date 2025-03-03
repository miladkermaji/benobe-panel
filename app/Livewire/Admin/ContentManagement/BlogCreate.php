<?php

namespace App\Livewire\Admin\ContentManagement;

use Livewire\Component;
use App\Models\Admin\ContentManagement\Blog\CategoryBlog;
use Livewire\WithFileUploads;
use Morilog\Jalali\CalendarUtils;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin\ContentManagement\Blog\Blog;

class BlogCreate extends Component
{
    use WithFileUploads;

    public $title;
    public $category_id;
    public $selectedDate; // تاریخ جلالی انتخاب‌شده توسط کاربر
    public $short_description;
    public $content;
    public $image;
    public $is_index = false;
    public $status = true;
    public $page_title;
    public $url_seo;
    public $meta_description;

    public $categories = [];

    protected $rules = [
        'title' => 'required|string|max:255',
        'category_id' => 'required|exists:category_blogs,id',
        'selectedDate' => 'required|string', // تاریخ جلالی
        'short_description' => 'nullable|string|max:500',
        'content' => 'nullable|string',
        'image' => 'nullable|image|max:2048',
        'is_index' => 'boolean',
        'status' => 'boolean',
        'page_title' => 'nullable|string|max:255',
        'url_seo' => 'nullable|string|max:255',
        'meta_description' => 'nullable|string|max:160',
    ];

    public function mount()
    {
        $this->categories = CategoryBlog::pluck('name', 'id')->toArray();
        $this->selectedDate = CalendarUtils::strftime('Y/m/d'); // تاریخ جلالی پیش‌فرض
    }

    public function save()
    {
        $this->validate();

        try {
            // تبدیل تاریخ جلالی به میلادی
            $gregorianDate = CalendarUtils::createDatetimeFromFormat('Y/m/d', $this->selectedDate)->format('Y-m-d H:i:s');

            $data = [
                'title' => $this->title,
                'category_id' => $this->category_id,
                'date' => $gregorianDate, // تاریخ میلادی برای دیتابیس
                'short_description' => $this->short_description,
                'content' => $this->content,
                'is_index' => $this->is_index,
                'status' => $this->status,
                'page_title' => $this->page_title,
                'url_seo' => $this->url_seo,
                'meta_description' => $this->meta_description,
            ];

            if ($this->image) {
                $data['image'] = $this->image->store('blogs', 'public');
            }

            Blog::create($data);

            Log::info('New blog created', ['title' => $this->title]);
            $this->dispatch('toast', 'خبر با موفقیت اضافه شد.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);

            $this->reset();
            $this->selectedDate = CalendarUtils::strftime('Y/m/d');
            return redirect()->route('admin.content-management.blog.index');
        } catch (\Exception $e) {
            Log::error('Error creating blog:', ['message' => $e->getMessage()]);
            $this->dispatch('toast', 'خطا در افزودن خبر: ' . $e->getMessage(), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    // متد برای برگرداندن تاریخ اولیه
    public function getInitialDateProperty()
    {
        return $this->selectedDate;
    }

    public function render()
    {
        return view('livewire.admin.content-management.blog-create');
    }
}
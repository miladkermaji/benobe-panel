<?php

namespace App\Livewire\Admin\ContentManagement;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin\ContentManagement\Blog\Blog;

class BlogList extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedBlogs = [];
    public $selectAll = false;
    public $perPage = 100;
    public $blogStatuses = [];

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->loadBlogStatuses();
    }

    public function updatedSearch()
    {
        Log::info('Search Query Updated:', ['search' => $this->search]);
        $this->resetPage();
        $this->loadBlogStatuses();
    }

    public function updatedSelectAll($value)
    {
        Log::info('Select All Updated:', ['value' => $value]);
        if ($value) {
            $this->selectedBlogs = Blog::where('title', 'like', '%' . $this->search . '%')
                ->orWhere('short_description', 'like', '%' . $this->search . '%')
                ->pluck('id')
                ->toArray();
        } else {
            $this->selectedBlogs = [];
        }
        Log::info('Selected Blogs:', ['selectedBlogs' => $this->selectedBlogs]);
    }

    public function updatedSelectedBlogs()
    {
        Log::info('Selected Blogs Updated:', ['selectedBlogs' => $this->selectedBlogs]);
        $total = Blog::where('title', 'like', '%' . $this->search . '%')
            ->orWhere('short_description', 'like', '%' . $this->search . '%')
            ->count();
        $this->selectAll = count($this->selectedBlogs) === $total && $total > 0;
    }

    public function toggleStatus($id)
    {
        $blog = Blog::find($id);
        if ($blog) {
            $blog->status = !$blog->status;
            $blog->save();
            $this->blogStatuses[$id] = $blog->status;
            Log::info('Blog status toggled', ['id' => $id, 'status' => $blog->status]);
            $this->dispatch('toast', 'وضعیت خبر با موفقیت تغییر کرد.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function deleteSelected()
    {
        if (empty($this->selectedBlogs)) {
            Log::info('No blogs selected for deletion');
            $this->dispatch('toast', 'هیچ خبری انتخاب نشده است.', [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
            return;
        }

        try {
            $blogs = Blog::whereIn('id', $this->selectedBlogs)->get();
            foreach ($blogs as $blog) {
                if ($blog->image && Storage::disk('public')->exists($blog->image)) {
                    Storage::disk('public')->delete($blog->image);
                }
            }
            Blog::whereIn('id', $this->selectedBlogs)->delete();

            $this->selectedBlogs = [];
            $this->selectAll = false;
            Log::info('Blogs deleted', ['ids' => $this->selectedBlogs]);
            $this->dispatch('toast', 'اخبار انتخاب‌شده با موفقیت حذف شدند.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
            $this->loadBlogStatuses();
        } catch (\Exception $e) {
            Log::error('Error deleting blogs:', ['message' => $e->getMessage()]);
            $this->dispatch('toast', 'خطا در حذف اخبار: ' . $e->getMessage(), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function loadBlogStatuses()
    {
        $blogs = Blog::where('title', 'like', '%' . $this->search . '%')
            ->orWhere('short_description', 'like', '%' . $this->search . '%')
            ->get();
        foreach ($blogs as $blog) {
            $this->blogStatuses[$blog->id] = $blog->status;
        }
    }

    public function render()
    {
        $blogs = Blog::with('category')
            ->where('title', 'like', '%' . $this->search . '%')
            ->orWhere('short_description', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage);

        // تبدیل تاریخ به فارسی
        foreach ($blogs as $blog) {
            $blog->persian_date = $this->toPersianDate($blog->date);
        }

        return view('livewire.admin.content-management.blog-list', [
            'blogs' => $blogs,
        ]);
    }

    // متد تبدیل تاریخ به فارسی
    public function toPersianDate($date)
    {
        if (class_exists(\Morilog\Jalali\Jalalian::class)) {
            return \Morilog\Jalali\Jalalian::fromDateTime($date)->format('Y/m/d');
        }

        $gregorian = strtotime($date);
        $gYear = (int) date('Y', $gregorian);
        $gMonth = (int) date('m', $gregorian);
        $gDay = (int) date('d', $gregorian);

        $jd = gregoriantojd($gMonth, $gDay, $gYear);
        $jYear = $gYear - 621;
        $jDays = $jd - gregoriantojd(3, 21, $gYear);
        if ($jDays < 0) {
            $jYear--;
            $jDays += 186 + 179;
        }

        $jMonth = $jDays < 186 ? ceil($jDays / 31) : ceil(($jDays - 186) / 30) + 6;
        $jDay = $jDays < 186 ? ($jDays % 31) : (($jDays - 186) % 30);
        if ($jDay == 0) {
            $jDay = $jMonth <= 6 ? 31 : 30;
            $jMonth--;
        }

        return sprintf('%04d/%02d/%02d', $jYear, $jMonth, $jDay);
    }
}

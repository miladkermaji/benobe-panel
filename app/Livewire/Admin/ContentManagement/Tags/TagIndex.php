<?php

namespace App\Livewire\Admin\ContentManagement\Tags;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin\ContentManagement\Tags\Tag;

class TagIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedTags = [];
    public $selectAll = false;
    public $perPage = 10;
    public $tagStatuses = [];

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->loadtagStatuses();
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->loadtagStatuses();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedTags = Tag::where('name', 'like', '%' . $this->search . '%')
                ->pluck('id')
                ->toArray();
        } else {
            $this->selectedTags = [];
        }
    }

    public function updatedselectedTags()
    {
        $total = Tag::where('name', 'like', '%' . $this->search . '%')
            ->count();
        $this->selectAll = count($this->selectedTags) === $total && $total > 0;
    }

    public function toggleStatus($id)
    {
        $tag = Tag::find($id);
        if ($tag) {
            $tag->status = !$tag->status;
            $tag->save();
            $this->tagStatuses[$id] = $tag->status;
            Log::info('Tag status toggled', ['id' => $id, 'status' => $tag->status]);
            $this->dispatch('toast', 'وضعیت تگ با موفقیت تغییر کرد.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function deleteSelected()
    {
        if (empty($this->selectedTags)) {
            Log::info('No Tags selected for deletion');
            $this->dispatch('toast', 'هیچ تگی انتخاب نشده است.', [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
            return;
        }

        try {
            $tags = Tag::whereIn('id', $this->selectedTags)->get();
         
            Tag::whereIn('id', $this->selectedTags)->delete();

            $this->selectedTags = [];
            $this->selectAll = false;
            $this->dispatch('toast', 'تگ انتخاب‌شده با موفقیت حذف شدند.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
            $this->loadtagStatuses();
        } catch (\Exception $e) {
            $this->dispatch('toast', 'خطا در حذف تگ: ' . $e->getMessage(), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function loadtagStatuses()
    {
        $tags = Tag::where('name', 'like', '%' . $this->search . '%')
            ->get();
        foreach ($tags as $tag) {
            $this->tagStatuses[$tag->id] = $tag->status;
        }
    }

    public function render()
    {
        $tags = Tag::where('name', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage);

        // تبدیل تاریخ به فارسی

        return view('livewire.admin.content-management.tags.tag-index', [
            'tags' => $tags,
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
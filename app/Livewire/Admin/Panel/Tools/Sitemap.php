<?php

namespace App\Livewire\Admin\Tools;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use App\Models\Admin\Tools\SitemapLink;
use Illuminate\Support\Facades\Storage;

class Sitemap extends Component
{
    use WithPagination;

    public $search = '';
    public $newUrl = '';
    public $newPriority = 0.5;
    public $newChangefreq = 'monthly';
    public $newLastmod = '';
    public $editId = null;
    public $editUrl = '';
    public $editPriority = 0.5;
    public $editChangefreq = 'monthly';
    public $editLastmod = '';
    public $perPage = 10;
    public $selectedLinks = [];
    public $selectAll = false;
    public $defaultPriority = 0.5;
    public $defaultChangefreq = 'monthly';

    protected $rules = [
        'newUrl' => 'required|url|unique:sitemap_links,url',
        'newPriority' => 'required|numeric|min:0|max:1',
        'newChangefreq' => 'required|in:always,hourly,daily,weekly,monthly,yearly,never',
        'newLastmod' => 'nullable|date',
        'editUrl' => 'required|url',
        'editPriority' => 'required|numeric|min:0|max:1',
        'editChangefreq' => 'required|in:always,hourly,daily,weekly,monthly,yearly,never',
        'editLastmod' => 'nullable|date',
        'defaultPriority' => 'required|numeric|min:0|max:1',
        'defaultChangefreq' => 'required|in:always,hourly,daily,weekly,monthly,yearly,never',
    ];

    protected $messages = [
        'newUrl.required' => 'لطفاً URL را وارد کنید.',
        'newUrl.url' => 'URL باید معتبر باشد.',
        'newUrl.unique' => 'این URL قبلاً ثبت شده است.',
        'newPriority.required' => 'لطفاً اولویت را وارد کنید.',
        'newPriority.numeric' => 'اولویت باید عدد باشد.',
        'newPriority.min' => 'اولویت باید حداقل 0 باشد.',
        'newPriority.max' => 'اولویت باید حداکثر 1 باشد.',
        'newChangefreq.required' => 'لطفاً فرکانس تغییر را انتخاب کنید.',
        'newChangefreq.in' => 'فرکانس تغییر نامعتبر است.',
        'newLastmod.date' => 'تاریخ آخرین تغییر باید معتبر باشد.',
        'editUrl.required' => 'لطفاً URL را وارد کنید.',
        'editUrl.url' => 'URL باید معتبر باشد.',
        'editUrl.unique' => 'این URL قبلاً ثبت شده است.',
        'editPriority.required' => 'لطفاً اولویت را وارد کنید.',
        'editPriority.numeric' => 'اولویت باید عدد باشد.',
        'editPriority.min' => 'اولویت باید حداقل 0 باشد.',
        'editPriority.max' => 'اولویت باید حداکثر 1 باشد.',
        'editChangefreq.required' => 'لطفاً فرکانس تغییر را انتخاب کنید.',
        'editChangefreq.in' => 'فرکانس تغییر نامعتبر است.',
        'editLastmod.date' => 'تاریخ آخرین تغییر باید معتبر باشد.',
        'defaultPriority.required' => 'لطفاً اولویت پیش‌فرض را وارد کنید.',
        'defaultPriority.numeric' => 'اولویت پیش‌فرض باید عدد باشد.',
        'defaultPriority.min' => 'اولویت پیش‌فرض باید حداقل 0 باشد.',
        'defaultPriority.max' => 'اولویت پیش‌فرض باید حداکثر 1 باشد.',
        'defaultChangefreq.required' => 'لطفاً فرکانس تغییر پیش‌فرض را انتخاب کنید.',
        'defaultChangefreq.in' => 'فرکانس تغییر پیش‌فرض نامعتبر است.',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = SitemapLink::where('url', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage)
            ->pluck('id')
            ->toArray();

        $this->selectedLinks = $value ? $currentPageIds : [];
    }

    public function updatedSelectedLinks()
    {
        $currentPageIds = SitemapLink::where('url', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage)
            ->pluck('id')
            ->toArray();

        $this->selectAll = !empty($this->selectedLinks) && count(array_diff($currentPageIds, $this->selectedLinks)) === 0;
    }

    public function addLink()
    {
        $this->validate([
            'newUrl' => 'required|url|unique:sitemap_links,url',
            'newPriority' => 'required|numeric|min:0|max:1',
            'newChangefreq' => 'required|in:always,hourly,daily,weekly,monthly,yearly,never',
            'newLastmod' => 'nullable|date',
        ]);

        try {
            SitemapLink::create([
                'url' => $this->newUrl,
                'priority' => $this->newPriority,
                'changefreq' => $this->newChangefreq,
                'lastmod' => $this->newLastmod ? Carbon::parse($this->newLastmod) : now(),
                'is_active' => true,
            ]);
            $this->newUrl = '';
            $this->newPriority = 0.5;
            $this->newChangefreq = 'monthly';
            $this->newLastmod = '';
            $this->generateSitemap();
            $this->dispatch('toast', 'لینک جدید با موفقیت اضافه شد.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error adding sitemap link: ' . $e->getMessage());
            $this->dispatch('toast', 'خطا در اضافه کردن لینک: ' . $e->getMessage(), ['type' => 'error']);
        }
    }

    public function startEdit($id)
    {
        $link = SitemapLink::findOrFail($id);
        $this->editId = $id;
        $this->editUrl = $link->url;
        $this->editPriority = $link->priority;
        $this->editChangefreq = $link->changefreq;
        $this->editLastmod = $link->lastmod ? Carbon::parse($link->lastmod)->toDateString() : '';
        $this->resetValidation();
    }

    public function updateLink()
    {
        $this->rules['editUrl'] = "required|url|unique:sitemap_links,url,{$this->editId}";
        $this->validate([
            'editUrl' => "required|url|unique:sitemap_links,url,{$this->editId}",
            'editPriority' => 'required|numeric|min:0|max:1',
            'editChangefreq' => 'required|in:always,hourly,daily,weekly,monthly,yearly,never',
            'editLastmod' => 'nullable|date',
        ]);

        try {
            $link = SitemapLink::findOrFail($this->editId);
            $link->update([
                'url' => $this->editUrl,
                'priority' => $this->editPriority,
                'changefreq' => $this->editChangefreq,
                'lastmod' => $this->editLastmod ? Carbon::parse($this->editLastmod) : now(),
            ]);
            $this->editId = null;
            $this->editUrl = '';
            $this->editPriority = 0.5;
            $this->editChangefreq = 'monthly';
            $this->editLastmod = '';
            $this->generateSitemap();
            $this->dispatch('toast', 'لینک با موفقیت ویرایش شد.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error updating sitemap link: ' . $e->getMessage());
            $this->dispatch('toast', 'خطا در ویرایش لینک: ' . $e->getMessage(), ['type' => 'error']);
        }
    }

    public function cancelEdit()
    {
        $this->editId = null;
        $this->editUrl = '';
        $this->editPriority = 0.5;
        $this->editChangefreq = 'monthly';
        $this->editLastmod = '';
        $this->resetValidation();
    }

    public function toggleStatus($id)
    {
        try {
            $link = SitemapLink::findOrFail($id);
            $link->is_active = !$link->is_active;
            $link->save();
            $this->generateSitemap();
            $this->dispatch('toast', 'وضعیت لینک با موفقیت تغییر کرد.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error toggling sitemap link status: ' . $e->getMessage());
            $this->dispatch('toast', 'خطا در تغییر وضعیت: ' . $e->getMessage(), ['type' => 'error']);
        }
    }

    public function deleteLink($id)
    {
        try {
            $link = SitemapLink::findOrFail($id);
            $link->delete();
            $this->selectedLinks = array_diff($this->selectedLinks, [$id]);
            $this->generateSitemap();
            $this->dispatch('toast', 'لینک با موفقیت حذف شد.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error deleting sitemap link: ' . $e->getMessage());
            $this->dispatch('toast', 'خطا در حذف لینک: ' . $e->getMessage(), ['type' => 'error']);
        }
    }

    public function deleteSelected()
    {
        if (empty($this->selectedLinks)) {
            $this->dispatch('toast', 'هیچ لینکی انتخاب نشده است.', ['type' => 'warning']);
            return;
        }

        $this->dispatch('confirmDeleteSelected');
    }

    public function confirmDeleteSelected()
    {
        try {
            SitemapLink::whereIn('id', $this->selectedLinks)->delete();
            $this->selectedLinks = [];
            $this->selectAll = false;
            $this->generateSitemap();
            $this->dispatch('toast', 'لینک‌های انتخاب‌شده با موفقیت حذف شدند.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error deleting selected sitemap links: ' . $e->getMessage());
            $this->dispatch('toast', 'خطا در حذف لینک‌ها: ' . $e->getMessage(), ['type' => 'error']);
        }
    }

    public function updateSitemap()
    {
        try {
            // اینجا می‌تونی منطق بروزرسانی خودکار رو بذاری (مثلاً لینک‌ها از صفحات سایت)
            $this->generateSitemap();
            $this->dispatch('toast', 'نقشه سایت با موفقیت بروزرسانی شد.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error updating sitemap: ' . $e->getMessage());
            $this->dispatch('toast', 'خطا در بروزرسانی نقشه سایت: ' . $e->getMessage(), ['type' => 'error']);
        }
    }

    public function generateSitemap()
    {
        $links = SitemapLink::where('is_active', true)->get();
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($links as $link) {
            $lastmod = $link->lastmod instanceof Carbon ? $link->lastmod->toAtomString() : Carbon::parse($link->lastmod)->toAtomString();
            $xml .= '<url>' . PHP_EOL;
            $xml .= '<loc>' . htmlspecialchars($link->url) . '</loc>' . PHP_EOL;
            $xml .= '<lastmod>' . ($link->lastmod ? $lastmod : now()->toAtomString()) . '</lastmod>' . PHP_EOL;
            $xml .= '<changefreq>' . $link->changefreq . '</changefreq>' . PHP_EOL;
            $xml .= '<priority>' . number_format($link->priority, 1) . '</priority>' . PHP_EOL;
            $xml .= '</url>' . PHP_EOL;
        }

        $xml .= '</urlset>';
        Storage::disk('public')->put('sitemap.xml', $xml);
    }

    public function export()
    {
        $links = SitemapLink::all();
        $csv = "URL,اولویت,فرکانس تغییر,آخرین تغییر,وضعیت\n";
        foreach ($links as $link) {
            $csv .= "{$link->url},{$link->priority},{$link->changefreq}," . ($link->lastmod ? Carbon::parse($link->lastmod)->toDateString() : '') . "," . ($link->is_active ? 'فعال' : 'غیرفعال') . "\n";
        }
        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, 'sitemap-links.csv');
    }

    public function render()
    {
        $links = SitemapLink::where('url', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage);

        $lastUpdated = SitemapLink::max('updated_at');
        $lastUpdated = $lastUpdated ? Carbon::parse($lastUpdated) : null;

        return view('livewire.admin.tools.sitemap', [
            'links' => $links,
            'totalLinks' => SitemapLink::count(),
            'lastUpdated' => $lastUpdated,
        ])->layout('admin.content.layouts.layoutMaster');
    }
}
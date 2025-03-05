<?php

namespace App\Livewire\Admin\Panel\Tools\SiteMap;

use Livewire\Component;
use Spatie\Sitemap\Sitemap;
use App\Models\Admin\Panel\Tools\CrawlLog;
use App\Models\Admin\Panel\Tools\SitemapUrl;
use Spatie\Sitemap\Tags\Url as SitemapUrlTag;
use App\Jobs\Admin\Panel\Tools\CrawlSiteForSitemap;

class SitemapManager extends Component
{
    public $urls = [];
    public $newUrl = '';
    public $newPriority = 0.8;
    public $newFrequency = 'weekly';
    public $newType = 'page';
    public $isGenerated = false;
    public $previewXml = '';
    public $isCrawling = false;
    public $crawlProgress = 0;
    public $crawlLogs = [];
    public $selectAll = false;
    public $selectedRows = [];

    public function loadInitialData()
    {
        $this->loadUrls();
        $this->isGenerated = file_exists(public_path('sitemap.xml'));
        $this->updateCrawlProgress();
    }

    public function loadUrls()
    {
        $this->urls = SitemapUrl::all()->toArray();
    }

    public function addUrl()
    {
        if (!filter_var($this->newUrl, FILTER_VALIDATE_URL)) {
            $this->dispatch('show-alert', type: 'error', message: 'URL معتبر نیست.');
            return;
        }

        SitemapUrl::updateOrCreate(
            ['url' => $this->newUrl],
            [
                'priority' => $this->newPriority,
                'frequency' => $this->newFrequency,
                'type' => $this->newType,
                'is_active' => true,
            ]
        );

        $this->reset(['newUrl', 'newPriority', 'newFrequency', 'newType']);
        $this->loadUrls();
        $this->dispatch('show-alert', type: 'success', message: 'URL با موفقیت اضافه شد.');
    }

    public function toggleStatus($id)
    {
        $url = SitemapUrl::findOrFail($id);
        $url->update(['is_active' => !$url->is_active]);
        $this->loadUrls();
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت URL تغییر کرد.');
    }

    public function removeUrl($id)
    {
        $this->dispatch('confirm-delete', ['id' => $id, 'type' => 'single']);
    }

    public function deleteSingleConfirmed($id)
    {
        SitemapUrl::findOrFail($id)->delete();
        $this->loadUrls();
        $this->dispatch('show-alert', type: 'success', message: 'URL حذف شد.');
    }

    public function crawlSite()
    {
        $this->isCrawling = true;
        $this->crawlProgress = 0;
        CrawlLog::truncate();
        CrawlSiteForSitemap::dispatch();
        $this->dispatch('show-alert', type: 'info', message: 'کراول سایت در پس‌زمینه شروع شد.');
    }

    public function stopCrawl()
    {
        $this->isCrawling = false;
        CrawlLog::truncate();
        $this->crawlProgress = 0;
        $this->crawlLogs = [];
        $this->dispatch('show-alert', type: 'warning', message: 'کراول سایت متوقف شد.');
    }

    public function updatedSelectAll()
    {
        $this->selectedRows = $this->selectAll ? SitemapUrl::pluck('id')->toArray() : [];
    }

    public function deleteSelected()
    {
        $this->dispatch('confirm-delete', ['type' => 'multiple']);
    }

    public function deleteMultipleConfirmed()
    {
        SitemapUrl::whereIn('id', $this->selectedRows)->delete();
        $this->selectedRows = [];
        $this->selectAll = false;
        $this->loadUrls();
        $this->dispatch('show-alert', type: 'success', message: 'URLهای انتخاب‌شده حذف شدند.');
    }

    public function updateCrawlProgress()
    {
        $total = 100; // setTotalCrawlLimit(100)
        $processed = CrawlLog::count(); // همه ردیف‌ها (crawled + failed + pending)
        $crawled = CrawlLog::whereIn('status', ['crawled', 'failed'])->count();
        $this->crawlProgress = $total > 0 ? ($processed / $total) * 100 : 0;
        $this->crawlLogs = CrawlLog::orderBy('created_at', 'desc')->get()->toArray();

        \Log::info('Crawl Progress Update - Total: ' . $total . ' - Processed: ' . $processed . ' - Crawled: ' . $crawled . ' - Pending: ' . ($total - $processed) . ' - Progress: ' . $this->crawlProgress);

        if ($this->crawlProgress >= 100 && $this->isCrawling) {
            $this->isCrawling = false;
            $this->loadUrls();
            $this->dispatch('show-alert', type: 'success', message: 'کراول سایت با موفقیت به پایان رسید.');
        }
    }

    public function generateSitemap()
    {
        $sitemap = Sitemap::create();
        $urls = SitemapUrl::where('is_active', true)->get();

        foreach ($urls as $url) {
            $sitemap->add(
                SitemapUrlTag::create($url->url)
                    ->setPriority($url->priority ?? 0.8)
                    ->setChangeFrequency($url->frequency ?? 'weekly')
            );
        }

        $sitemap->writeToFile(public_path('sitemap.xml'));
        $this->previewXml = file_exists(public_path('sitemap.xml')) ? file_get_contents(public_path('sitemap.xml')) : '';
        $this->isGenerated = true;
        $this->dispatch('show-alert', type: 'success', message: 'نقشه سایت با موفقیت تولید شد!');
    }

    public function render()
    {
        return view('livewire.admin.panel.tools.site-map.sitemap-manager');
    }
}
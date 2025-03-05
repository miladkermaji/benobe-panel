<?php

namespace App\Jobs\Admin\Panel\Tools;

use Spatie\Crawler\Crawler;
use Illuminate\Bus\Queueable;
use App\CrawlObservers\SitemapCrawler;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Spatie\Crawler\CrawlProfiles\CrawlAllUrls;
use App\Models\Admin\Panel\Tools\SitemapSetting;

class CrawlSiteForSitemap implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $settings = SitemapSetting::first() ?? new SitemapSetting();

        $crawler = Crawler::create()
            ->setCrawlObserver(new SitemapCrawler())
            ->setMaximumDepth($settings->maximum_depth)
            ->setTotalCrawlLimit($settings->total_crawl_limit)
            ->setDelayBetweenRequests($settings->delay_between_requests);

        if ($settings->ignore_robots) {
            $crawler->ignoreRobots();
        }

        $crawler->setCrawlProfile(new CrawlAllUrls())
            ->startCrawling($settings->base_url);
    }
}
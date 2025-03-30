<?php

namespace App\CrawlObservers;

use Psr\Http\Message\UriInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Psr\Http\Message\ResponseInterface;
use App\Models\Admin\Panel\Tools\CrawlLog;
use GuzzleHttp\Exception\RequestException;
use App\Models\Admin\Panel\Tools\SitemapUrl;
use Spatie\Crawler\CrawlObservers\CrawlObserver;

class SitemapCrawler extends CrawlObserver
{
    public function willCrawl(UriInterface $url, ?string $linkText = null): void
    {
        $urlString = (string) $url;
        CrawlLog::create([
            'url' => $urlString,
            'status' => 'pending',
        ]);
        Log::info('Will crawl: ' . $urlString . ' - Link text: ' . ($linkText ?? 'No link text'));
    }

    public function crawled(UriInterface $url, ResponseInterface $response, ?UriInterface $foundOnUrl = null, ?string $linkText = null): void
    {
        $urlString = (string) $url;
        $log = CrawlLog::where('url', $urlString)->firstOrFail();
        Log::info('Crawling: ' . $urlString . ' - Status: ' . $response->getStatusCode() . ' - Found on: ' . ($foundOnUrl ? (string) $foundOnUrl : 'None'));
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 400) { // فقط کدهای موفق (200-399)
            SitemapUrl::updateOrCreate(
                ['url' => $urlString],
                [
                    'priority' => $this->guessPriority($urlString),
                    'frequency' => $this->guessFrequency($urlString),
                    'type' => 'page',
                    'is_active' => true,
                ]
            );
            $log->update([
                'status' => 'crawled',
                'message' => 'Successfully crawled with status ' . $response->getStatusCode(),
            ]);
        } else {
            $log->update([
                'status' => 'failed',
                'message' => 'Failed with status ' . $response->getStatusCode(),
            ]);
        }
    }

    public function crawlFailed(UriInterface $url, RequestException $requestException, ?UriInterface $foundOnUrl = null, ?string $linkText = null): void
    {
        $urlString = (string) $url;
        $log = CrawlLog::where('url', $urlString)->firstOrFail();
        Log::error('Crawl failed for: ' . $urlString . ' - Error: ' . $requestException->getMessage() . ' - Found on: ' . ($foundOnUrl ? (string) $foundOnUrl : 'None'));
        $log->update([
            'status' => 'failed',
            'message' => $requestException->getMessage(),
        ]);
    }

    private function guessPriority(string $url): float
    {
        if ($url === URL::to('/'))
            return 1.0;
        return str_contains($url, '/blog') || str_contains($url, '/product') ? 0.9 : 0.8;
    }

    private function guessFrequency(string $url): string
    {
        if ($url === URL::to('/'))
            return 'daily';
        return str_contains($url, '/blog') ? 'weekly' : 'monthly';
    }
}
<?php

namespace App\Http\Controllers\Admin\Panel\Tools\SiteMap;

use App\Http\Controllers\Admin\Controller;
use App\Jobs\Admin\Panel\Tools\CrawlSiteForSitemap;

class SitemapController extends Controller
{
  public function index()
  {
    return view('admin.panel.tools.site-map.index');
  }

  public function download()
  {
    if (file_exists(public_path('sitemap.xml'))) {
      return response()->download(public_path('sitemap.xml'));
    }
    return redirect()->back()->with('error', 'فایل نقشه سایت یافت نشد!');
  }

  public static function crawlSite()
  {
    CrawlSiteForSitemap::dispatch();
  }
}
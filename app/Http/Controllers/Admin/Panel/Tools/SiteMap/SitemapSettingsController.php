<?php

namespace App\Http\Controllers\Admin\Panel\Tools\Sitemap;

use Illuminate\Http\Request;
use App\Http\Controllers\Admin\Controller;
use App\Models\Admin\Panel\Tools\SitemapSetting;

class SitemapSettingsController extends Controller
{
 public function index()
 {
  $settings = SitemapSetting::firstOrCreate([
   'base_url' => 'http://127.0.0.1:8000/admin/tools/sitemap',
   'maximum_depth' => 10,
   'total_crawl_limit' => 100,
   'delay_between_requests' => 1000,
   'ignore_robots' => true,
  ]);
  return view('admin.panel.tools.site-map.settings', compact('settings'));
 }

 public function update(Request $request)
 {
  $settings = SitemapSetting::firstOrCreate([
   'base_url' => 'http://127.0.0.1:8000/admin/tools/sitemap',
   'maximum_depth' => 10,
   'total_crawl_limit' => 100,
   'delay_between_requests' => 1000,
   'ignore_robots' => true,
  ]);
  $settings->update([
   'base_url' => $request->base_url,
   'maximum_depth' => $request->maximum_depth,
   'total_crawl_limit' => $request->total_crawl_limit,
   'delay_between_requests' => $request->delay_between_requests,
   'ignore_robots' => $request->boolean('ignore_robots'),
  ]);
  return redirect()->back()->with('success', 'تنظیمات با موفقیت به‌روزرسانی شد.');
 }
}
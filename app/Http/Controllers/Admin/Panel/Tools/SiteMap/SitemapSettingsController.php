<?php
namespace App\Http\Controllers\Admin\Panel\Tools\SiteMap;

use App\Http\Controllers\Admin\Controller;
use App\Models\Admin\Panel\Tools\SitemapSetting;
use Illuminate\Http\Request;

class SitemapSettingsController extends Controller
{
    public function index()
    {
        // اگه ردیفی نبود، تنظیمات پیش‌فرض رو بساز
        $settings = SitemapSetting::first();
        if (! $settings) {
            $settings = SitemapSetting::create([
                'base_url'               => 'https://emr-benobe.ir', // پیش‌فرض دلخواه
                'maximum_depth'          => 10,
                'total_crawl_limit'      => 100,
                'delay_between_requests' => 1000,
                'ignore_robots'          => true,
            ]);
        }

        return view('admin.panel.tools.site-map.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        // پیدا کردن ردیف موجود یا ساخت اگه هیچی نبود
        $settings = SitemapSetting::first();
        if (! $settings) {
            $settings = SitemapSetting::create([
                'base_url'               => 'https://benobe.ir',
                'maximum_depth'          => 10,
                'total_crawl_limit'      => 100,
                'delay_between_requests' => 1000,
                'ignore_robots'          => true,
            ]);
        }

        // آپدیت ردیف موجود با داده‌های فرم
        $settings->update([
            'base_url'               => $request->base_url,
            'maximum_depth'          => $request->maximum_depth,
            'total_crawl_limit'      => $request->total_crawl_limit,
            'delay_between_requests' => $request->delay_between_requests,
            'ignore_robots'          => $request->boolean('ignore_robots'),
        ]);

        return redirect()->back()->with('success', 'تنظیمات با موفقیت به‌روزرسانی شد.');
    }
}

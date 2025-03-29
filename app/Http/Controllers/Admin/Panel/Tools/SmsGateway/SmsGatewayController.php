<?php

namespace App\Http\Controllers\Admin\Panel\Tools\SmsGateway;

use App\Http\Controllers\Admin\Controller;
use Modules\SendOtp\App\Models\SmsGateway;

class SmsGatewayController extends Controller
{
    public function index()
    {
        // برگرداندن ویو برای نمایش لیست پنل‌های پیامکی
        return view('admin.panel.tools.sms-gateway.index');
    }

    public function edit($name)
    {
        $gateway = SmsGateway::where('name', $name)->firstOrFail();
        return view('admin.panel.tools.sms-gateway.edit', compact('gateway'));
    }
    public function create()
    {

        return view('admin.panel.tools.sms-gateway.create');

    }
}

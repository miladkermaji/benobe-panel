<?php

namespace App\Http\Controllers\Mc\Panel\Payment\Setting;

use App\Http\Controllers\Mc\Controller;

class DrPaymentSettingController extends Controller
{
    public function index()
    {
        return view('mc.panel.payment.setting');

    }
    public function wallet()
    {
        return view("mc.panel.payment.wallet.index");
    }

}

<?php

namespace App\Http\Controllers\Dr\Panel\Payment\Setting;

class DrPaymentSettingController
{
    public function index()
    {
        return view('dr.panel.payment.setting');

    }
    public function wallet()
    {
        return view("dr.panel.payment.wallet.index");
    }

}

<?php

namespace App\Http\Controllers\Dr\Panel\Payment\Setting;

use App\Http\Controllers\Dr\Controller;


class DrPaymentSettingController extends Controller
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

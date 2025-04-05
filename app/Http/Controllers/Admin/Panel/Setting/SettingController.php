<?php

namespace App\Http\Controllers\Admin\Panel\Setting;

use App\Http\Controllers\Admin\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return view('admin.panel.setting.index');
    }

    public function change_logo()
    {
        return view('admin.panel.setting.change-logo');
    }
}

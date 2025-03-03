<?php

namespace App\Http\Controllers\Admin\Dashboard\Setting;

use App\Http\Controllers\Admin\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return view('admin.content.dashboard.setting.index');
    }

    public function change_logo()
    {
        return view('admin.content.dashboard.setting.change-logo');
    }
}
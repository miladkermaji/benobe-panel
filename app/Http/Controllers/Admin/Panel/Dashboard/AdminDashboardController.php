<?php

namespace App\Http\Controllers\Admin\Panel\Dashboard;

use App\Http\Controllers\Admin\Controller;

class AdminDashboardController extends Controller
{
    public function index()
    {

        return view('admin.panel.dashboard.index');

    }
}

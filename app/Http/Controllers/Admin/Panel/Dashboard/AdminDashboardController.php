<?php

namespace App\Http\Controllers\Admin\Panel\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\Controller;

class AdminDashboardController extends Controller
{
  public function index()
  {
    
    return view('admin.panel.dashboard.index');

  }
}

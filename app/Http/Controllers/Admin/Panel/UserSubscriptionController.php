<?php

namespace App\Http\Controllers\Admin\Panel;

use App\Http\Controllers\Controller;
use App\Models\UserSubscription;
use Illuminate\Http\Request;

class UserSubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = UserSubscription::latest()->paginate(10);
        return view('admin.panel.user-subscriptions.index', compact('subscriptions'));
    }

    public function create()
    {
        return view('admin.panel.user-subscriptions.create');
    }



    public function edit(UserSubscription $userSubscription)
    {
        return view('admin.panel.user-subscriptions.edit', compact('userSubscription'));
    }

   
}

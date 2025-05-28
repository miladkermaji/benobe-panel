<?php

namespace App\Http\Controllers\Admin\Panel;

use App\Http\Controllers\Controller;
use App\Models\UserMembershipPlan;
use Illuminate\Http\Request;

class UserMembershipPlanController extends Controller
{
    public function index()
    {
        $plans = UserMembershipPlan::latest()->paginate(10);
        return view('admin.panel.user-membership-plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.panel.user-membership-plans.create');
    }


    public function edit(UserMembershipPlan $userMembershipPlan)
    {
        return view('admin.panel.user-membership-plans.edit', compact('userMembershipPlan'));
    }

}

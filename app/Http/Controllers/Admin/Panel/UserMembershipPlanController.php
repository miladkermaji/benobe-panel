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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'duration_unit' => 'required|in:day,week,month,year',
            'features' => 'nullable|array',
            'status' => 'required|in:active,inactive',
        ]);

        UserMembershipPlan::create($validated);

        return redirect()->route('admin.panel.user-membership-plans.index')
            ->with('success', 'طرح عضویت با موفقیت ایجاد شد.');
    }

    public function edit(UserMembershipPlan $userMembershipPlan)
    {
        return view('admin.panel.user-membership-plans.edit', compact('userMembershipPlan'));
    }

    public function update(Request $request, UserMembershipPlan $userMembershipPlan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'duration_unit' => 'required|in:day,week,month,year',
            'features' => 'nullable|array',
            'status' => 'required|in:active,inactive',
        ]);

        $userMembershipPlan->update($validated);

        return redirect()->route('admin.panel.user-membership-plans.index')
            ->with('success', 'طرح عضویت با موفقیت بروزرسانی شد.');
    }

    public function destroy(UserMembershipPlan $userMembershipPlan)
    {
        $userMembershipPlan->delete();

        return redirect()->route('admin.panel.user-membership-plans.index')
            ->with('success', 'طرح عضویت با موفقیت حذف شد.');
    }
}

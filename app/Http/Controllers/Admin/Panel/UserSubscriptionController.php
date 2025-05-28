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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'plan_id' => 'required|exists:user_membership_plans,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:active,inactive,expired',
        ]);

        UserSubscription::create($validated);

        return redirect()->route('admin.panel.user-subscriptions.index')
            ->with('success', 'اشتراک با موفقیت ایجاد شد.');
    }

    public function edit(UserSubscription $userSubscription)
    {
        return view('admin.panel.user-subscriptions.edit', compact('userSubscription'));
    }

    public function update(Request $request, UserSubscription $userSubscription)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'plan_id' => 'required|exists:user_membership_plans,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:active,inactive,expired',
        ]);

        $userSubscription->update($validated);

        return redirect()->route('admin.panel.user-subscriptions.index')
            ->with('success', 'اشتراک با موفقیت بروزرسانی شد.');
    }

    public function destroy(UserSubscription $userSubscription)
    {
        $userSubscription->delete();

        return redirect()->route('admin.panel.user-subscriptions.index')
            ->with('success', 'اشتراک با موفقیت حذف شد.');
    }
}

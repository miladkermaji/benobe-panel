<?php

namespace App\Http\Controllers\Admin\Panel;

use App\Http\Controllers\Controller;
use App\Models\UserAppointmentFee;
use Illuminate\Http\Request;

class UserAppointmentFeeController extends Controller
{
    public function index()
    {
        $fees = UserAppointmentFee::latest()->paginate(10);
        return view('admin.panel.user-appointment-fees.index', compact('fees'));
    }

    public function create()
    {
        return view('admin.panel.user-appointment-fees.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'fee' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        UserAppointmentFee::create($validated);

        return redirect()->route('admin.panel.user-appointment-fees.index')
            ->with('success', 'هزینه نوبت‌دهی با موفقیت ایجاد شد.');
    }

    public function edit(UserAppointmentFee $userAppointmentFee)
    {
        return view('admin.panel.user-appointment-fees.edit', compact('userAppointmentFee'));
    }

    public function update(Request $request, UserAppointmentFee $userAppointmentFee)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'fee' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        $userAppointmentFee->update($validated);

        return redirect()->route('admin.panel.user-appointment-fees.index')
            ->with('success', 'هزینه نوبت‌دهی با موفقیت بروزرسانی شد.');
    }

    public function destroy(UserAppointmentFee $userAppointmentFee)
    {
        $userAppointmentFee->delete();

        return redirect()->route('admin.panel.user-appointment-fees.index')
            ->with('success', 'هزینه نوبت‌دهی با موفقیت حذف شد.');
    }
} 
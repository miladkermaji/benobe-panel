<?php

namespace App\Http\Controllers\Admin\Panel\Tools\Notification;

class NotificationController
{
    public function index()
    {
        return view('admin.panel.tools.notifications.index');
    }

    public function create()
    {
        return view('admin.panel.tools.notifications.create');
    }

    public function edit($id)
    {
        return view('admin.panel.tools.notifications.edit', compact('id'));
    }

    public function recipientsSearch()
    {
        $q = request('q', '');
        $users = \App\Models\User::query()
            ->where('first_name', 'like', "%$q%")
            ->orWhere('last_name', 'like', "%$q%")
            ->orWhere('mobile', 'like', "%$q%")
            ->limit(10)
            ->get()
            ->map(fn ($u) => [
                'id' => "App\\Models\\User:{$u->id}",
                'text' => $u->first_name . ' ' . $u->last_name . ' (بیمار)'
            ]);
        $doctors = \App\Models\Doctor::query()
            ->where('first_name', 'like', "%$q%")
            ->orWhere('last_name', 'like', "%$q%")
            ->orWhere('mobile', 'like', "%$q%")
            ->limit(10)
            ->get()
            ->map(fn ($d) => [
                'id' => "App\\Models\\Doctor:{$d->id}",
                'text' => $d->first_name . ' ' . $d->last_name . ' (پزشک)'
            ]);
        $secretaries = \App\Models\Secretary::query()
            ->where('first_name', 'like', "%$q%")
            ->orWhere('last_name', 'like', "%$q%")
            ->orWhere('mobile', 'like', "%$q%")
            ->limit(10)
            ->get()
            ->map(fn ($s) => [
                'id' => "App\\Models\\Secretary:{$s->id}",
                'text' => $s->first_name . ' ' . $s->last_name . ' (منشی)'
            ]);
        return response()->json($users->concat($doctors)->concat($secretaries)->values());
    }
}

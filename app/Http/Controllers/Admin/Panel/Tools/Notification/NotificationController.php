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
}

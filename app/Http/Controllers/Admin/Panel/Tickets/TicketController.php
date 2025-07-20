<?php

namespace App\Http\Controllers\Admin\Panel\Tickets;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TicketController extends Controller
{
    public function index()
    {
        return view('admin.panel.tickets.index');
    }

    public function create()
    {
        return view('admin.panel.tickets.create');
    }

    public function show($id)
    {
        return view('admin.panel.tickets.show', compact('id'));
    }
}

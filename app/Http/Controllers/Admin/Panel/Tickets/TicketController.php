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
}

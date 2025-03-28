<?php

namespace App\Http\Controllers\Dr\Panel\Tickets;

use App\Http\Controllers\Dr\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketsController extends Controller
{
    public function index(Request $request)
    {
        $tickets = Ticket::latest()->paginate(2);

        if ($request->ajax()) {
            return view('dr.panel.tickets.index', compact('tickets'))->render();
        }

        return view('dr.panel.tickets.index', compact('tickets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ], [
            'title.required' => 'لطفاً عنوان تیکت را وارد کنید.',
            'title.string' => 'عنوان تیکت باید یک متن باشد.',
            'title.max' => 'عنوان تیکت نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
            'description.required' => 'لطفاً توضیحات تیکت را وارد کنید.',
            'description.string' => 'توضیحات تیکت باید یک متن باشد.',
        ]);

        $ticket = Ticket::create([
            'doctor_id' => Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id,
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'open',
        ]);

        return response()->json([
            'message' => 'تیکت با موفقیت اضافه شد!',
            'tickets' => Ticket::latest()->get()
        ]);
    }

    public function show(string $id)
    {
        $ticket = Ticket::with('responses.doctor')->findOrFail($id);
        return view('dr.panel.tickets.show', compact('ticket'));
    }

    public function destroy($id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->delete();

        $tickets = Ticket::all();

        return response()->json([
            'message' => 'تیکت با موفقیت حذف شد!',
            'tickets' => $tickets
        ]);
    }

    public function create()
    {
    }
    public function edit(string $id)
    {
    }
    public function update(Request $request, string $id)
    {
    }
}

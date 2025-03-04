<?php

namespace App\Http\Controllers\Dr\Panel\Tickets;

use App\Http\Controllers\Dr\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tickets = Ticket::latest()->paginate(2);

        if ($request->ajax()) {
            return view('dr.panel.tickets.index', compact('tickets'))->render();
        }

        return view('dr.panel.tickets.index', compact('tickets'));
    }




    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $ticket = Ticket::create([
            'doctor_id' => Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id,
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'open', // ✅ تیکت‌های جدید باز می‌شوند
        ]);

        return response()->json([
            'message' => 'تیکت با موفقیت اضافه شد!',
            'tickets' => Ticket::latest()->get()
        ]);
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $ticket = Ticket::with('responses.doctor')->findOrFail($id);
        return view('dr.panel.tickets.show', compact('ticket'));
    }


    public function destroy($id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->delete();

        // ارسال لیست جدید تیکت‌ها
        $tickets = Ticket::all();

        return response()->json([
            'message' => 'تیکت با موفقیت حذف شد!',
            'tickets' => $tickets
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
  
}

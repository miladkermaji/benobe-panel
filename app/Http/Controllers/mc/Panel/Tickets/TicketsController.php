<?php

namespace App\Http\Controllers\Mc\Panel\Tickets;

use App\Http\Controllers\Mc\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketsController extends Controller
{
    public function index(Request $request)
    {
        $tickets = Ticket::latest()->paginate(2);

        if ($request->ajax()) {
            return view('mc.panel.tickets.index', compact('tickets'))->render();
        }

        return view('mc.panel.tickets.index', compact('tickets'));
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

        // بررسی تعداد تیکت‌های باز یا پاسخ‌نشده
        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
        $openOrAnsweredTickets = Ticket::where('doctor_id', $doctorId)
            ->whereIn('status', ['open', 'answered'])
            ->count();

        if ($openOrAnsweredTickets >= 2) {
            return response()->json([
                'message' => 'شما بیش از 2 تیکت باز یا پاسخ‌نشده دارید. لطفاً ابتدا تیکت‌های موجود را تکمیل کنید.',
                'errors' => ['limit' => 'محدودیت تعداد تیکت‌ها']
            ], 422);
        }

        // ایجاد تیکت جدید
        $ticket = Ticket::create([
            'doctor_id' => $doctorId,
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'open',
        ]);

        return response()->json([
            'message' => 'تیکت با موفقیت اضافه شد!',
            'tickets' => Ticket::where('doctor_id', $doctorId)->latest()->get()
        ]);
    }

    public function show(string $id)
    {
        $ticket = Ticket::with('responses.doctor')->findOrFail($id);
        return view('mc.panel.tickets.show', compact('ticket'));
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

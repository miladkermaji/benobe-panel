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
        $doctor = $this->getAuthenticatedDoctor();
        if (!$doctor) {
            return redirect()->route('mc.panel.my-performance.index')->with('error', 'هیچ پزشکی انتخاب نشده است.');
        }

        $tickets = Ticket::where('doctor_id', $doctor->id)->latest()->paginate(2);

        if ($request->ajax()) {
            return view('mc.panel.tickets.index', compact('tickets'))->render();
        }

        return view('mc.panel.tickets.index', compact('tickets'));
    }

    public function store(Request $request)
    {
        $doctor = $this->getAuthenticatedDoctor();
        if (!$doctor) {
            return response()->json([
                'message' => 'هیچ پزشکی انتخاب نشده است.',
                'errors' => ['doctor' => 'هیچ پزشکی انتخاب نشده است.']
            ], 422);
        }

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
        $openOrAnsweredTickets = Ticket::where('doctor_id', $doctor->id)
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
            'doctor_id' => $doctor->id,
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'open',
        ]);

        return response()->json([
            'message' => 'تیکت با موفقیت اضافه شد!',
            'tickets' => Ticket::where('doctor_id', $doctor->id)->latest()->get()
        ]);
    }

    public function show(string $id)
    {
        $doctor = $this->getAuthenticatedDoctor();
        if (!$doctor) {
            return redirect()->route('mc.panel.my-performance.index')->with('error', 'هیچ پزشکی انتخاب نشده است.');
        }

        $ticket = Ticket::where('doctor_id', $doctor->id)
            ->with('responses.doctor')
            ->findOrFail($id);

        return view('mc.panel.tickets.show', compact('ticket'));
    }

    public function destroy($id)
    {
        $doctor = $this->getAuthenticatedDoctor();
        if (!$doctor) {
            return response()->json([
                'message' => 'هیچ پزشکی انتخاب نشده است.',
                'errors' => ['doctor' => 'هیچ پزشکی انتخاب نشده است.']
            ], 422);
        }

        $ticket = Ticket::where('doctor_id', $doctor->id)->findOrFail($id);
        $ticket->delete();

        $tickets = Ticket::where('doctor_id', $doctor->id)->latest()->get();

        return response()->json([
            'message' => 'تیکت با موفقیت حذف شد!',
            'tickets' => $tickets
        ]);
    }

    public function create()
    {
        $doctor = $this->getAuthenticatedDoctor();
        if (!$doctor) {
            return redirect()->route('mc.panel.my-performance.index')->with('error', 'هیچ پزشکی انتخاب نشده است.');
        }

        return view('mc.panel.tickets.create');
    }

    public function edit(string $id)
    {
        $doctor = $this->getAuthenticatedDoctor();
        if (!$doctor) {
            return redirect()->route('mc.panel.my-performance.index')->with('error', 'هیچ پزشکی انتخاب نشده است.');
        }

        $ticket = Ticket::where('doctor_id', $doctor->id)->findOrFail($id);
        return view('mc.panel.tickets.edit', compact('ticket'));
    }

    public function update(Request $request, string $id)
    {
        $doctor = $this->getAuthenticatedDoctor();
        if (!$doctor) {
            return response()->json([
                'message' => 'هیچ پزشکی انتخاب نشده است.',
                'errors' => ['doctor' => 'هیچ پزشکی انتخاب نشده است.']
            ], 422);
        }

        $ticket = Ticket::where('doctor_id', $doctor->id)->findOrFail($id);

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

        $ticket->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'تیکت با موفقیت به‌روزرسانی شد!',
            'tickets' => Ticket::where('doctor_id', $doctor->id)->latest()->get()
        ]);
    }
}

<?php

namespace App\Http\Controllers\Mc\Panel\Tickets;

use App\Http\Controllers\Mc\Controller;
use App\Models\Ticket;
use App\Models\TicketResponse;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\Auth;

class TicketResponseController extends Controller
{
    public function store(Request $request, $ticket_id)
    {
        $doctor = $this->getAuthenticatedDoctor();
        if (!$doctor) {
            return response()->json([
                'message' => 'هیچ پزشکی انتخاب نشده است.',
                'errors' => ['doctor' => 'هیچ پزشکی انتخاب نشده است.']
            ], 422);
        }

        $request->validate([
            'message' => 'required|string',
        ], [
            'message.required' => 'لطفاً متن پاسخ را وارد کنید.',
            'message.string' => 'پاسخ باید یک متن باشد.',
        ]);

        // Check if the ticket belongs to the authenticated doctor
        $ticket = Ticket::where('doctor_id', $doctor->id)->findOrFail($ticket_id);

        $response = TicketResponse::create([
            'ticket_id' => $ticket->id,
            'doctor_id' => $doctor->id,
            'message' => $request->message,
        ]);

        $doctor = $response->doctor;

        return response()->json([
            'user' => $doctor ? 'دکتر ' . $doctor->first_name . ' ' . $doctor->last_name : 'نامشخص',
            'message' => $response->message,
            'created_at' => Jalalian::forge($response->created_at)->ago(),
        ]);
    }
}

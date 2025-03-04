<?php
namespace App\Http\Controllers\Dr\Panel\Tickets;

use App\Http\Controllers\Dr\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;
use App\Models\TicketResponse;
use Illuminate\Support\Facades\Auth;


class TicketResponseController extends Controller
{
 public function store(Request $request, $ticket_id)
 {
  $request->validate([
   'message' => 'required|string',
  ]);

  $ticket = Ticket::findOrFail($ticket_id);

  $response = TicketResponse::create([
   'ticket_id' => $ticket->id,
   'doctor_id' => auth()->guard('doctor')->user()->id, // مقدار دکتر را حتماً ست کن
   'message' => $request->message,
  ]);

  // دریافت اطلاعات دکتر برای جلوگیری از undefined
  $doctor = $response->doctor;

  return response()->json([
   'user' => $doctor ? 'دکتر ' . $doctor->first_name . ' ' . $doctor->last_name : 'نامشخص',
   'message' => $response->message,
   'created_at' =>Jalalian::forge($response->created_at)->ago(),
  ]);
 }



}
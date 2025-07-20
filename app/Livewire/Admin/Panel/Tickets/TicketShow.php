<?php

namespace App\Livewire\Admin\Panel\Tickets;

use App\Models\Ticket;
use App\Models\TicketResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Morilog\Jalali\Jalalian;

class TicketShow extends Component
{
    public $ticketId;
    public $ticket;
    public $responses = [];
    public $responseMessage = '';

    public function mount($ticketId)
    {
        $this->ticketId = $ticketId;
        $this->ticket = Ticket::with(['user', 'doctor', 'manager', 'responses.manager', 'responses.doctor', 'responses.secretary'])->findOrFail($ticketId);
        $this->responses = $this->ticket->responses;
    }

    public function storeResponse()
    {
        $this->validate([
            'responseMessage' => 'required|string',
        ], [
            'responseMessage.required' => 'لطفاً متن پاسخ را وارد کنید.',
            'responseMessage.string' => 'متن پاسخ باید یک رشته باشد.',
        ]);

        $response = TicketResponse::create([
            'ticket_id' => $this->ticket->id,
            'manager_id' => Auth::guard('manager')->id(),
            'message' => $this->responseMessage,
        ]);
        $response->load('manager');
        $this->responses->push($response);
        $this->responseMessage = '';
        $this->dispatch('show-alert', type: 'success', message: 'پاسخ با موفقیت ثبت شد!');
        $this->ticket->refresh();
    }

    public function render()
    {
        return view('livewire.admin.panel.tickets.ticket-show');
    }
}

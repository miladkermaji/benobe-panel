<?php

namespace App\Livewire\Admin\Panel\Tickets;

use App\Models\Ticket;
use Livewire\Component;

class TicketShow extends Component
{
    public $ticketId;
    public $ticket;

    public function mount($ticketId)
    {
        $this->ticketId = $ticketId;
        $this->ticket = Ticket::with(['user', 'doctor', 'manager'])->findOrFail($ticketId);
    }

    public function render()
    {
        return view('livewire.admin.panel.tickets.ticket-show');
    }
}

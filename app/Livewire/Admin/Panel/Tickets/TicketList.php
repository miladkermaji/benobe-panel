<?php

namespace App\Livewire\Admin\Panel\Tickets;

use App\Models\Ticket;
use App\Models\Doctor;
use App\Models\User;
use App\Models\Manager;
use Livewire\Component;
use Livewire\WithPagination;

class TicketList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $perPage = 20;
    public $search = '';
    public $statusFilter = '';
    public $readyToLoad = false;
    public $selectedTickets = [];
    public $selectAll = false;
    public $groupAction = '';
    public $applyToAllFiltered = false;
    public $totalFilteredCount = 0;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    protected $listeners = [
        'deleteTicketConfirmed' => 'deleteTicket',
        'deleteSelectedConfirmed' => 'deleteSelected',
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadTickets()
    {
        $this->readyToLoad = true;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = $this->getTicketsQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        $this->selectedTickets = $value ? $currentPageIds : [];
    }

    public function updatedSelectedTickets()
    {
        $currentPageIds = $this->getTicketsQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedTickets) && count(array_diff($currentPageIds, $this->selectedTickets)) === 0;
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedTickets) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ تیکتی انتخاب نشده است.');
            return;
        }
        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفا یک عملیات را انتخاب کنید.');
            return;
        }
        $query = $this->applyToAllFiltered ? $this->getTicketsQuery() : Ticket::whereIn('id', $this->selectedTickets);
        switch ($this->groupAction) {
            case 'delete':
                $this->dispatch('confirm-delete-selected', ['allFiltered' => $this->applyToAllFiltered]);
                return;
            case 'close':
                $query->update(['status' => 'closed']);
                $this->dispatch('show-alert', type: 'success', message: 'تیکت‌ها بسته شدند!');
                break;
            case 'open':
                $query->update(['status' => 'open']);
                $this->dispatch('show-alert', type: 'success', message: 'تیکت‌ها باز شدند!');
                break;
        }
        $this->selectedTickets = [];
        $this->selectAll = false;
        $this->applyToAllFiltered = false;
        $this->groupAction = '';
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteTicket($id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->delete();
        $this->dispatch('show-alert', type: 'success', message: 'تیکت با موفقیت حذف شد!');
    }

    public function deleteSelected($allFiltered = null)
    {
        if ($allFiltered === 'allFiltered') {
            $this->getTicketsQuery()->delete();
        } elseif (!empty($this->selectedTickets)) {
            Ticket::whereIn('id', $this->selectedTickets)->delete();
        }
        $this->selectedTickets = [];
        $this->selectAll = false;
        $this->applyToAllFiltered = false;
        $this->groupAction = '';
        $this->resetPage();
        $this->dispatch('show-alert', type: 'success', message: 'تیکت‌های انتخاب شده حذف شدند!');
    }

    protected function getTicketsQuery()
    {
        return Ticket::with(['doctor', 'user', 'manager'])
            ->when($this->search, function ($query) {
                $search = trim($this->search);
                $query->where('title', 'like', "%$search%")
                      ->orWhereHas('user', function ($q) use ($search) {
                          $q->where('first_name', 'like', "%$search%")
                            ->orWhere('last_name', 'like', "%$search%")
                            ->orWhere('mobile', 'like', "%$search%") ;
                      })
                      ->orWhereHas('doctor', function ($q) use ($search) {
                          $q->where('first_name', 'like', "%$search%")
                            ->orWhere('last_name', 'like', "%$search%") ;
                      });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderByDesc('created_at');
    }

    public function render()
    {
        $tickets = $this->readyToLoad
            ? $this->getTicketsQuery()->paginate($this->perPage)
            : collect([]);
        $this->totalFilteredCount = $this->readyToLoad ? $tickets->total() : 0;
        return view('livewire.admin.panel.tickets.ticket-list', [
            'tickets' => $tickets,
            'totalFilteredCount' => $this->totalFilteredCount,
        ]);
    }
}

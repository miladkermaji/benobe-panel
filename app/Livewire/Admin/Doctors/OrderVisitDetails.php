<?php

namespace App\Livewire\Admin\Doctors;

use Livewire\Component;
use Illuminate\Support\Facades\Log;
use App\Models\Admin\Doctors\OrderVisit;

class OrderVisitDetails extends Component
{
    public $orderVisitId;

    public function mount($orderVisitId)
    {
        $this->orderVisitId = $orderVisitId;
    }

    public function render()
    {
        $orderVisit = OrderVisit::with(['user', 'doctor', 'clinic'])->findOrFail($this->orderVisitId);
        return view('livewire.admin.doctors.order-visit-details', [
            'orderVisit' => $orderVisit,
        ]);
    }
}
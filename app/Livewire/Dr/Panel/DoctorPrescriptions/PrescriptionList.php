<?php

namespace App\Livewire\Dr\Panel\DoctorPrescriptions;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PrescriptionRequest;
use Illuminate\Support\Facades\Auth;

class PrescriptionList extends Component
{
    use WithPagination;

    public $search = '';
    public $type = '';
    public $insurance = '';
    public $status = 'pending'; // مقدار پیش‌فرض فقط در انتظار
    public $payment_status = '';
    public $date_from = '';
    public $date_to = '';
    public $tracking_code = '';
    public $editId = null;
    public $doctor_description = '';
    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['refreshList' => '$refresh'];

    public function updating($field)
    {
        if (in_array($field, ['search', 'type', 'insurance', 'status', 'payment_status', 'date_from', 'date_to'])) {
            $this->resetPage();
        }
    }

    public function editTrackingCode($id)
    {
        $this->editId = $id;
        $this->tracking_code = '';
        $this->doctor_description = '';
        $presc = PrescriptionRequest::find($id);
        if ($presc) {
            $this->tracking_code = $presc->tracking_code;
            $this->doctor_description = $presc->doctor_description;
        }
        $this->dispatch('showTrackingModal');
    }

    public function updateTrackingCode()
    {
        $presc = PrescriptionRequest::find($this->editId);
        if ($presc) {
            $presc->tracking_code = $this->tracking_code;
            $presc->doctor_description = $this->doctor_description;
            $presc->status = 'completed';
            $presc->save();
            $this->dispatch('show-alert', type: 'success', message: 'کد رهگیری و توضیحات پزشک با موفقیت ثبت شد.');
            $this->editId = null;
            $this->tracking_code = '';
            $this->doctor_description = '';
            $this->dispatch('hideTrackingModal');
            $this->dispatch('refreshList');
        }
    }

    public function render()
    {
        $doctor = Auth::guard('doctor')->user();
        $query = PrescriptionRequest::with(['patient', 'prescriptionInsurance', 'clinic', 'insulins'])
            ->where('doctor_id', $doctor->id);
        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('patient', function ($qq) {
                    $qq->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%")
                        ->orWhere('national_code', 'like', "%{$this->search}%");
                })
                ->orWhere('tracking_code', 'like', "%{$this->search}%");
            });
        }
        if ($this->type) {
            $query->where('type', $this->type);
        }
        if ($this->insurance) {
            $query->where('prescription_insurance_id', $this->insurance);
        }
        if ($this->status) {
            $query->where('status', $this->status);
        }
        if ($this->payment_status) {
            $query->where('payment_status', $this->payment_status);
        }
        if ($this->date_from) {
            $query->whereDate('created_at', '>=', $this->date_from);
        }
        if ($this->date_to) {
            $query->whereDate('created_at', '<=', $this->date_to);
        }
        $prescriptions = $query->orderByDesc('created_at')->paginate(30);
        $insurances = \App\Models\PrescriptionInsurance::all();
        return view('livewire.dr.panel.doctor-prescriptions.prescription-list', compact('prescriptions', 'insurances'));
    }
}

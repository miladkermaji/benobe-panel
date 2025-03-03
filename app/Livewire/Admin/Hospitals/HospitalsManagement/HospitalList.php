<?php

namespace App\Livewire\Admin\Hospitals\HospitalsManagement;

use Livewire\Component;
use App\Models\Dr\Clinic;
use Livewire\WithPagination;

class HospitalList extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $selectedHospitals = [];
    public $selectAll = false;

    public function updated($propertyName)
    {
        if ($propertyName === 'search') {
            $this->resetPage();
        }
    }

    public function updatedSelectAll($value)
    {
        $hospitals = $this->getHospitalsQuery()->paginate($this->perPage);
        $this->selectedHospitals = $value ? $hospitals->pluck('id')->toArray() : [];
    }

    public function updatedSelectedHospitals()
    {
        $hospitals = $this->getHospitalsQuery()->paginate($this->perPage);
        $currentPageIds = $hospitals->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedHospitals) && !array_diff($currentPageIds, $this->selectedHospitals);
    }

    public function deleteSelected()
    {
        if (empty($this->selectedHospitals)) {
            $this->dispatch('toast', ['message' => 'هیچ بیمارستانی انتخاب نشده است.', 'type' => 'warning']);
            return;
        }

        Clinic::whereIn('id', $this->selectedHospitals)->delete();
        $this->selectedHospitals = [];
        $this->selectAll = false;
        $this->dispatch('toast', ['message' => 'بیمارستان‌های انتخاب‌شده با موفقیت حذف شدند.', 'type' => 'success']);
    }

    public function toggleStatus($hospitalId)
    {
        $hospital = Clinic::findOrFail($hospitalId);
        $newStatus = !$hospital->is_active;

        $hospital->update(['is_active' => $newStatus]);

        // ارسال پیامک (اختیاری، بر اساس نیاز)
        $doctor = $hospital->doctor;
        $doctorName = $doctor->first_name . ' ' . $doctor->last_name;
        $message = "وضعیت بیمارستان {$hospital->name} به " . ($newStatus ? 'فعال' : 'غیرفعال') . " تغییر کرد.";
        // فرض می‌کنیم سرویس پیامک مشابه قبلی دارید
        // $smsService = new MessageService(SmsService::create(100258, $doctor->mobile, [$hospital->name, $newStatus ? 'فعال' : 'غیرفعال']));
        // $smsService->send();

        $this->dispatch('toast', ['message' => "وضعیت بیمارستان با موفقیت به " . ($newStatus ? 'فعال' : 'غیرفعال') . " تغییر کرد.", 'type' => 'success']);
    }

    public function deleteHospital($id)
    {
        Clinic::findOrFail($id)->delete();
        $this->dispatch('toast', ['message' => 'بیمارستان با موفقیت حذف شد.', 'type' => 'success']);
    }

    private function getHospitalsQuery()
    {
        return Clinic::with('doctor')
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%')
                ->orWhereHas('doctor', fn($q2) => $q2->where('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('last_name', 'like', '%' . $this->search . '%')
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $this->search . '%'])));
    }

    public function render()
    {
        $hospitals = $this->getHospitalsQuery()->paginate($this->perPage);
        return view('livewire.admin.hospitals.hospitals-management.hospital-list', [
            'hospitals' => $hospitals,
        ]);
    }
}
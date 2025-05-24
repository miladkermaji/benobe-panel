<?php

namespace App\Livewire\Admin\Panel\doctors;

use App\Models\Doctor;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;

class DoctorList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteDoctorConfirmed' => 'deleteDoctor'];

    public $perPage         = 10;
    public $search          = '';
    public $readyToLoad     = false;
    public $selecteddoctors = [];
    public $selectAll       = false;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loaddoctors()
    {
        $this->readyToLoad = true;
    }

    public function toggleStatus($id)
    {
        $item = Doctor::findOrFail($id);
        $newStatus = !$item->status;

        if ($newStatus) {
            // اگر می‌خواهد فعال شود، تأیید بگیر
            $doctorName = $item->first_name . ' ' . $item->last_name;
            Log::info('Dispatching confirm-status-change event', [
                'id' => $id,
                'name' => $doctorName,
                'newStatus' => $newStatus
            ]);
            $this->dispatch('confirm-status-change', [
                'id' => $id,
                'name' => $doctorName,
                'newStatus' => $newStatus
            ]);
        } else {
            // اگر می‌خواهد غیرفعال شود، مستقیماً انجام شود
            $item->update(['status' => $newStatus]);
            $this->dispatch('show-alert', type: 'info', message: 'پزشک غیرفعال شد!');
        }
    }

    public function confirmStatusChange($data)
    {
        Log::info('confirmStatusChange called', $data);
        $id = $data['id'];
        $newStatus = $data['newStatus'];

        $item = Doctor::findOrFail($id);
        $item->update(['status' => $newStatus]);

        // ارسال پیامک فعال‌سازی
        $message = "دکتر گرامی، حساب کاربری شما در سیستم فعال شد. می‌توانید از طریق لینک زیر وارد پنل خود شوید: " . route('dr.auth.login-register-form', $item->id);

        $activeGateway = \Modules\SendOtp\App\Models\SmsGateway::where('is_active', true)->first();
        $gatewayName = $activeGateway ? $activeGateway->name : 'pishgamrayan';
        $templateId = ($gatewayName === 'pishgamrayan') ? 100254 : null;

        \App\Jobs\SendSmsNotificationJob::dispatch(
            $message,
            [$item->mobile],
            $templateId,
            [$item->first_name . ' ' . $item->last_name]
        )->delay(now()->addSeconds(5));

        $this->dispatch('show-alert', type: 'success', message: 'پزشک فعال شد و پیامک فعال‌سازی ارسال شد!');
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteDoctor($id)
    {
        $item = Doctor::findOrFail($id);
        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'doctor حذف شد!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds        = $this->getdoctorsQuery()->pluck('id')->toArray();
        $this->selecteddoctors = $value ? $currentPageIds : [];
    }

    public function updatedSelecteddoctors()
    {
        $currentPageIds  = $this->getdoctorsQuery()->pluck('id')->toArray();
        $this->selectAll = ! empty($this->selecteddoctors) && count(array_diff($currentPageIds, $this->selecteddoctors)) === 0;
    }

    public function deleteSelected()
    {
        if (empty($this->selecteddoctors)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ doctor انتخاب نشده است.');
            return;
        }

        Doctor::whereIn('id', $this->selecteddoctors)->delete();
        $this->selecteddoctors = [];
        $this->selectAll       = false;
        $this->dispatch('show-alert', type: 'success', message: 'doctors انتخاب‌شده حذف شدند!');
    }

    private function getdoctorsQuery()
    {
        return Doctor::where('first_name', 'like', '%' . $this->search . '%')
            ->orWhere('last_name', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->orWhere('mobile', 'like', '%' . $this->search . '%')
            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $this->search . '%'])
            ->with(['province', 'city'])
            ->paginate($this->perPage);

    }

    public function render()
    {
        $items = $this->readyToLoad ? $this->getdoctorsQuery() : null;

        return view('livewire.admin.panel.doctors.doctor-list', [
            'doctors' => $items,
        ]);
    }
}

<?php
namespace App\Livewire\Admin\Doctors;

use App\Models\Doctor;
use Livewire\Component;
use Livewire\WithPagination;

class DoctorsManagementList extends Component
{
    use WithPagination;

    public $searchMobile = '';
    public $searchName   = '';
    public $status       = '0'; // پیش‌فرض: همه
    public $perPage      = 10;

    protected $paginationTheme = 'bootstrap';

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['searchMobile', 'searchName', 'status'])) {
            $this->resetPage();
        }
    }

    public function resetFilters()
    {
        $this->searchMobile = '';
        $this->searchName   = '';
        $this->status       = '0';
        $this->resetPage();
    }

    public function render()
    {
        $doctors = Doctor::with(['province', 'city', 'tariff'])
            ->when($this->searchMobile, fn($q) => $q->where('mobile', 'like', '%' . $this->searchMobile . '%'))
            ->when($this->searchName, fn($q) => $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $this->searchName . '%']))
            ->when($this->status !== '0', function ($q) {
                switch ($this->status) {
                    case 'level_0':
                        return $q->where('status', 0); // مرحله اول ثبت‌نام
                    case 'level_special':
                        return $q->where('status', 1); // انتخاب تخصص
                    case 'level_nobatdehi':
                        return $q->where('status', 2); // تنظیم نوبت‌دهی
                    case 'level_moshavere':
                        return $q->where('status', 3); // تنظیم مشاوره
                    case 'level_finish':
                    case 'finish':
                        return $q->where('status', 4); // نهایی شده
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.doctors.doctors-management-list', [
            'doctors' => $doctors,
        ]);
    }
}

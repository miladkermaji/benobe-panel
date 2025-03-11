<?php
namespace App\Livewire\Admin\Dashboard\HomePage;

use App\Models\Admin\Dashboard\HomePage\BestDoctor;
use App\Models\Doctor;
use App\Models\Hospital;
use Livewire\Component;

class CreateBestDoctor extends Component
{
    public $doctor_id;
    public $hospital_id;
    public $best_doctor     = false;
    public $best_consultant = false;

    public function save()
    {
        $this->validate([
            'doctor_id'       => 'required|exists:doctors,id',
            'hospital_id'     => 'nullable|exists:hospitals,id',
            'best_doctor'     => 'boolean',
            'best_consultant' => 'boolean',
        ]);

        BestDoctor::create([
            'doctor_id'       => $this->doctor_id,
            'hospital_id'     => $this->hospital_id,
            'best_doctor'     => $this->best_doctor,
            'best_consultant' => $this->best_consultant,
        ]);

        $this->dispatch('show-toastr', type: 'success', message: 'پزشک برتر با موفقیت اضافه شد.');

        return redirect()->route('admin.Dashboard.home_page.index');
    }

    public function render()
    {
        return view('livewire.admin.dashboard.home_page.create-best-doctor', [
            'doctors'   => Doctor::all(),
            'hospitals' => Hospital::all(),
        ]);
    }
}

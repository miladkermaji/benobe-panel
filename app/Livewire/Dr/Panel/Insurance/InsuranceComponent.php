<?php
namespace App\Livewire\Dr\Panel\Insurance;

use GuzzleHttp\Psr7\Request;
use Livewire\Component;
use App\Models\Dr\Insurance;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use function Laravel\Prompts\alert;

class InsuranceComponent extends Component
{
    public $insurances, $insurance_id;
    public $name, $calculation_method = "0", $appointment_price, $insurance_percent, $final_price;
    public $selectedClinicId; // متغیر جدید برای کلینیک انتخاب‌شده

    protected $rules = [
        'name' => 'required|string|max:255',
        'calculation_method' => 'required|in:0,1,2',
        'appointment_price' => 'nullable|integer',
        'insurance_percent' => 'nullable|integer',
        'final_price' => 'nullable|integer',
    ];

    public function mount()
    {
        // گرفتن selectedClinicId از سشن یا URL
        $this->selectedClinicId = request()->query('selectedClinicId', session('selectedClinicId', 'default'));
        // ذخیره توی سشن برای استفاده بعدی
        session(['selectedClinicId' => $this->selectedClinicId]);
    }

    public function render()
    {
        $query = Insurance::where('doctor_id', Auth::guard('doctor')->user()->id)
            ->where('calculation_method', $this->calculation_method);

        // شرط برای clinic_id
        if ($this->selectedClinicId === 'default') {
            $query->whereNull('clinic_id');
        } else if (Auth::guard('manager')->check()) {
            // Get the last segment of the current URL
            $id = last(request()->segments());
            $query->where('doctor_id', $id);

        } else {
            $query->where('clinic_id', $this->selectedClinicId);
        }

        $this->insurances = $query->get();
        return view('livewire.dr.panel.insurance.insurance-component');
    }

    public function store()
    {
        // Validate the incoming data
        $data = $this->validate();

        // Handle calculation method logic
        switch ($data['calculation_method']) {
            case '0':
                $data['insurance_percent'] = null;
                $data['appointment_price'] = null;
                break;
            case '1':
                $data['final_price'] = null;
                $data['appointment_price'] = null;
                break;
            case '2':
                $data['final_price'] = null;
                break;
        }

        // Set the doctor_id based on the authentication context
        if (Auth::guard('manager')->check()) {
            // If a manager is logged in, get the ID from the URL
            $CurrentUrl = $_SERVER['HTTP_REFERER'];
            $doctorId = explode("/",$CurrentUrl)[7];
            $data['doctor_id'] = $doctorId; // Ensure this is the correct segment
        } else {
            // If a doctor is logged in, use their ID
            $data['doctor_id'] = Auth::guard('doctor')->user()->id;
        }

        // Set clinic_id based on selectedClinicId
        $data['clinic_id'] = $this->selectedClinicId === 'default' ? null : $this->selectedClinicId;

        // Create the Insurance record
        Insurance::create($data);

        // Dispatch a success message
        $this->dispatch('toast', message: 'بیمه جدید با موفقیت اضافه شد.');

        // Reset fields after submission
        $this->resetFields();
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirmDelete', id: $id);
    }

    #[On('delete')]
    public function delete($id)
    {
        $insuranceId = is_array($id) ? $id['id'] : $id;
        $insurance = Insurance::where('doctor_id', Auth::guard('doctor')->user()->id)
            ->where('id', $insuranceId);

        // شرط برای clinic_id موقع حذف
        if ($this->selectedClinicId === 'default') {
            $insurance->whereNull('clinic_id');
        }else if (Auth::guard('manager')->check()) {
            // If a manager is logged in, get the ID from the URL
            $CurrentUrl = $_SERVER['HTTP_REFERER'];
            $doctorId = explode("/", $CurrentUrl)[7];
            $insurance->where('doctor_id', $doctorId);
            // Ensure this is the correct segment
        } else {
            $insurance->where('clinic_id', $this->selectedClinicId);
        }

        $insurance->firstOrFail()->delete();
        $this->dispatch('toast', message: 'بیمه با موفقیت حذف شد.');
    }

    private function resetFields()
    {
        $this->insurance_id = null;
        $this->name = '';
        $this->calculation_method = "0";
        $this->appointment_price = null;
        $this->insurance_percent = null;
        $this->final_price = null;
    }
}
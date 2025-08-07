<?php

namespace App\Livewire\Admin\Panel\Bestdoctors;

use App\Models\BestDoctor;
use App\Models\Doctor;
use App\Models\MedicalCenter;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class BestDoctorCreate extends Component
{
    public $doctor_id;
    public $medical_center_id;
    public $best_doctor     = false;
    public $best_consultant = false;
    public $star_rating     = 0.0;
    public $status          = true;

    public $doctors;
    public $clinics = [];

    public function mount()
    {
        $this->doctors = Doctor::all();
    }

    public function loadClinics()
    {
        Log::info('Loading clinics for doctor_id: ' . $this->doctor_id);

        if ($this->doctor_id) {
            $clinics = MedicalCenter::whereHas('doctors', function ($query) {
                $query->where('doctor_id', $this->doctor_id);
            })->where('type', 'policlinic')->get();
            Log::info('Found clinics:', ['count' => $clinics->count(), 'clinics' => $clinics->toArray()]);
            $this->clinics = $clinics;

            // ارسال داده‌ها با فرمت صحیح برای Select2
            $this->dispatch('clinics-updated', clinics: $clinics->map(function ($clinic) {
                return [
                    'id' => $clinic->id,
                    'text' => $clinic->name
                ];
            })->toArray());
        } else {
            Log::info('No doctor_id provided, returning empty collection');
            $this->clinics = collect();
            $this->dispatch('clinics-updated', clinics: []);
        }
    }

    public function updatedDoctorId($value)
    {
        Log::info('Doctor ID updated:', ['old_value' => $this->doctor_id, 'new_value' => $value]);
        $this->doctor_id = $value;
        $this->medical_center_id = null;
        $this->loadClinics();
    }

    protected function rules()
    {
        return [
            'doctor_id'       => [
                'required',
                'exists:doctors,id',
                "unique:best_doctors,doctor_id,NULL,id,medical_center_id," . ($this->medical_center_id ?? 'NULL'),
            ],
            'medical_center_id' => 'nullable|exists:medical_centers,id',
            'best_doctor'     => 'boolean',
            'best_consultant' => 'boolean',
            'star_rating'     => 'numeric|min:0|max:5',
            'status'          => 'boolean',
        ];
    }

    protected $messages = [
        'doctor_id.required' => 'لطفاً یک پزشک انتخاب کنید.',
        'doctor_id.exists' => 'پزشک انتخاب شده معتبر نیست.',
        'medical_center_id.exists' => 'کلینیک انتخاب شده معتبر نیست.',
    ];

    public function store()
    {
        try {
            Log::info('Starting store method', [
                'doctor_id' => $this->doctor_id,
                'medical_center_id' => $this->medical_center_id,
                'best_doctor' => $this->best_doctor,
                'best_consultant' => $this->best_consultant,
                'status' => $this->status
            ]);

            $this->validate();

            Log::info('Validation passed');

            $bestDoctor = BestDoctor::create([
                'doctor_id'       => $this->doctor_id,
                'medical_center_id' => $this->medical_center_id,
                'best_doctor'     => $this->best_doctor,
                'best_consultant' => $this->best_consultant,
                'star_rating'     => $this->star_rating,
                'status'          => $this->status,
            ]);
            Cache::forget('best_doctors__status__page_1');

            Log::info('BestDoctor created successfully', ['id' => $bestDoctor->id]);

            $this->dispatch('show-alert', type: 'success', message: 'بهترین پزشک با موفقیت اضافه شد!');

            Log::info('Redirecting to index page');
            return redirect()->route('admin.panel.best-doctors.index');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error', ['errors' => $e->validator->errors()->all()]);
            $errors = $e->validator->errors()->all();
            $this->dispatch('show-alert', type: 'error', message: $errors[0]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error', [
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

            if ($e->getCode() == 23000) {
                $this->dispatch('show-alert', type: 'error', message: 'خطا در ذخیره اطلاعات: کلینیک انتخاب شده معتبر نیست.');
            } else {
                $this->dispatch('show-alert', type: 'error', message: 'خطا در ذخیره اطلاعات: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            Log::error('Error in store method', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->dispatch('show-alert', type: 'error', message: 'خطا در ذخیره اطلاعات: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.panel.best-doctors.best-doctor-create');
    }
}

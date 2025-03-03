<?php

namespace App\Livewire\Admin\Agent;

use Livewire\Component;
use App\Models\Admin\Agent\Agent;
use Illuminate\Support\Facades\Log;
use App\Models\Admin\Dashboard\Cities\Zone;

class CreateAgent extends Component
{
    public $full_name;
    public $mobile;
    public $national_code;
    public $province_id;
    public $city_id;
    public $status = true;

    public $provinces = [];
    public $cities = [];

    protected $rules = [
        'full_name' => 'required|string|max:255',
        'mobile' => 'required|string|size:11|unique:agents,mobile',
        'national_code' => 'required|string|size:10|unique:agents,national_code',
        'province_id' => 'required|exists:zone,id',
        'city_id' => 'required|exists:zone,id',
        'status' => 'boolean',
    ];

    public function mount()
    {
        $this->loadProvinces();
    }

    public function updatedProvinceId($value)
    {
        $this->loadCities($value);
    }

    public function loadProvinces()
    {
        $this->provinces = Zone::where('level', 1)->get(['id', 'name'])->toArray();
    }

    public function loadCities($provinceId)
    {
        $this->cities = Zone::where('level', 2)
            ->where('parent_id', $provinceId)
            ->get(['id', 'name'])
            ->toArray();
        $this->city_id = null; // ریست شهر
    }

    public function save()
    {
        $this->validate();

        try {
            $province = Zone::find($this->province_id)->name;
            $city = Zone::find($this->city_id)->name;

            Agent::create([
                'full_name' => $this->full_name,
                'mobile' => $this->mobile,
                'national_code' => $this->national_code,
                'province' => $province,
                'city' => $city,
                'status' => $this->status,
            ]);

            Log::info('New agent created', ['full_name' => $this->full_name]);
            $this->dispatch('toast', 'نماینده با موفقیت اضافه شد.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);

            $this->reset();
            $this->loadProvinces();
            return redirect()->route('admin.agent.agent');
        } catch (\Exception $e) {
            Log::error('Error creating agent:', ['message' => $e->getMessage()]);
            $this->dispatch('toast', 'خطا در افزودن نماینده: ' . $e->getMessage(), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.agent.create-agent');
    }
}
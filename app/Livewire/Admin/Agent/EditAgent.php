<?php

namespace App\Livewire\Admin\Agent;

use Livewire\Component;
use App\Models\Admin\Agent\Agent;
use Illuminate\Support\Facades\Log;
use App\Models\Admin\Dashboard\Cities\Zone;

class EditAgent extends Component
{
    public $agentId;
    public $full_name;
    public $mobile;
    public $national_code;
    public $province_id;
    public $city_id;
    public $status;

    public $provinces = [];
    public $cities = [];

    protected function rules()
    {
        return [
            'full_name' => 'required|string|max:255',
            'mobile' => 'required|string|size:11|unique:agents,mobile,' . $this->agentId,
            'national_code' => 'required|string|size:10|unique:agents,national_code,' . $this->agentId,
            'province_id' => 'required|exists:zone,id',
            'city_id' => 'required|exists:zone,id',
            'status' => 'boolean',
        ];
    }

    public function mount($agentId)
    {
        $this->agentId = $agentId;
        $agent = Agent::findOrFail($agentId);
        $this->full_name = $agent->full_name;
        $this->mobile = $agent->mobile;
        $this->national_code = $agent->national_code;

        $province = Zone::where('name', $agent->province)->where('level', 1)->first();
        $this->province_id = $province ? $province->id : null;
        $city = Zone::where('name', $agent->city)->where('level', 2)->where('parent_id', $this->province_id)->first();
        $this->city_id = $city ? $city->id : null;

        $this->status = $agent->status;

        $this->loadProvinces();
        if ($this->province_id) {
            $this->loadCities($this->province_id);
        }
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

    public function update()
    {
        $this->validate();

        try {
            $agent = Agent::findOrFail($this->agentId);
            $province = Zone::find($this->province_id)->name;
            $city = Zone::find($this->city_id)->name;

            $agent->update([
                'full_name' => $this->full_name,
                'mobile' => $this->mobile,
                'national_code' => $this->national_code,
                'province' => $province,
                'city' => $city,
                'status' => $this->status,
            ]);

            Log::info('Agent updated', ['id' => $this->agentId]);
            $this->dispatch('toast', 'نماینده با موفقیت ویرایش شد.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);

            return redirect()->route('admin.agent.agent');
        } catch (\Exception $e) {
            Log::error('Error updating agent:', ['message' => $e->getMessage()]);
            $this->dispatch('toast', 'خطا در ویرایش نماینده: ' . $e->getMessage(), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.agent.edit-agent');
    }
}
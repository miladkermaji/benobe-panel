<?php

namespace App\Livewire\Admin\Panel\Zones;

use App\Models\Zone;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class ZoneCreate extends Component
{
    public $name;
    public $sort = 1;
    public $latitude;
    public $longitude;
    public $population;
    public $area;
    public $postal_code;
    public $price_shipping;
    public $status = 1;

    public function store()
    {
        $validator = Validator::make([
            'name'           => $this->name,
            'sort'           => $this->sort,
            'latitude'       => $this->latitude,
            'longitude'      => $this->longitude,
            'population'     => $this->population,
            'area'           => $this->area,
            'postal_code'    => $this->postal_code,
            'price_shipping' => $this->price_shipping,
            'status'         => $this->status,
        ], [
            'name'           => 'required|string|max:255',
            'sort'           => 'required|integer|min:1',
            'latitude'       => 'nullable|numeric',
            'longitude'      => 'nullable|numeric',
            'population'     => 'nullable|integer|min:0',
            'area'           => 'nullable|numeric|min:0',
            'postal_code'    => 'nullable|string|max:20',
            'price_shipping' => 'nullable|integer|min:0',
            'status'         => 'required|boolean',
        ], [
            'name.required'          => 'نام منطقه الزامی است.',
            'name.string'            => 'نام باید رشته باشد.',
            'name.max'               => 'نام نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
            'sort.required'          => 'ترتیب الزامی است.',
            'sort.integer'           => 'ترتیب باید عدد صحیح باشد.',
            'sort.min'               => 'ترتیب باید حداقل ۱ باشد.',
            'latitude.numeric'       => 'عرض جغرافیایی باید عدد باشد.',
            'longitude.numeric'      => 'طول جغرافیایی باید عدد باشد.',
            'population.integer'     => 'جمعیت باید عدد صحیح باشد.',
            'population.min'         => 'جمعیت نمی‌تواند منفی باشد.',
            'area.numeric'           => 'مساحت باید عدد باشد.',
            'area.min'               => 'مساحت نمی‌تواند منفی باشد.',
            'postal_code.string'     => 'کد پستی باید رشته باشد.',
            'postal_code.max'        => 'کد پستی نمی‌تواند بیشتر از ۲۰ کاراکتر باشد.',
            'price_shipping.integer' => 'هزینه ارسال باید عدد صحیح باشد.',
            'price_shipping.min'     => 'هزینه ارسال نمی‌تواند منفی باشد.',
            'status.required'        => 'وضعیت الزامی است.',
            'status.boolean'         => 'وضعیت باید فعال یا غیرفعال باشد.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        Zone::create([
            'name'           => $this->name,
            'level'          => 1, // به طور پیش‌فرض استان
            'sort'           => $this->sort,
            'latitude'       => $this->latitude,
            'longitude'      => $this->longitude,
            'population'     => $this->population,
            'area'           => $this->area,
            'postal_code'    => $this->postal_code,
            'price_shipping' => $this->price_shipping,
            'status'         => $this->status,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'استان با موفقیت ایجاد شد!');
        return redirect()->route('admin.panel.zones.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.zones.zone-create');
    }
}

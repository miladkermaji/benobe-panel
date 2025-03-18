<?php
namespace App\Livewire\Admin\Panel\Cities;

use App\Models\Zone;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class CityEdit extends Component
{
    public $city;
    public $name;
    public $parent_id;
    public $sort;
    public $latitude;
    public $longitude;
    public $population;
    public $area;
    public $postal_code;
    public $price_shipping;
    public $status;

    public function mount($id)
    {
        $this->city           = Zone::findOrFail($id);
        $this->name           = $this->city->name;
        $this->parent_id      = $this->city->parent_id;
        $this->sort           = $this->city->sort;
        $this->latitude       = $this->city->latitude;
        $this->longitude      = $this->city->longitude;
        $this->population     = $this->city->population;
        $this->area           = $this->city->area;
        $this->postal_code    = $this->city->postal_code;
        $this->price_shipping = $this->city->price_shipping;
        $this->status         = $this->city->status;
    }

    public function update()
    {
        $validator = Validator::make([
            'name'           => $this->name,
            'parent_id'      => $this->parent_id,
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
            'parent_id'      => 'required|exists:zone,id',
            'sort'           => 'required|integer|min:1',
            'latitude'       => 'nullable|numeric',
            'longitude'      => 'nullable|numeric',
            'population'     => 'nullable|integer|min:0',
            'area'           => 'nullable|numeric|min:0',
            'postal_code'    => 'nullable|string|max:20',
            'price_shipping' => 'nullable|integer|min:0',
            'status'         => 'required|boolean',
        ], [
            'name.required'          => 'نام شهر الزامی است.',
            'name.string'            => 'نام باید رشته باشد.',
            'name.max'               => 'نام نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
            'parent_id.required'     => 'انتخاب استان الزامی است.',
            'parent_id.exists'       => 'استان انتخاب‌شده معتبر نیست.',
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

        $this->city->update([
            'name'           => $this->name,
            'parent_id'      => $this->parent_id,
            'sort'           => $this->sort,
            'latitude'       => $this->latitude,
            'longitude'      => $this->longitude,
            'population'     => $this->population,
            'area'           => $this->area,
            'postal_code'    => $this->postal_code,
            'price_shipping' => $this->price_shipping,
            'status'         => $this->status,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'شهر با موفقیت به‌روزرسانی شد!');
        return redirect()->route('admin.panel.cities.index', ['province_id' => $this->parent_id]);
    }

    public function render()
    {
        $provinces = Zone::provinces()->orderBy('sort')->get();
        return view('livewire.admin.panel.cities.city-edit', compact('provinces'));
    }
}

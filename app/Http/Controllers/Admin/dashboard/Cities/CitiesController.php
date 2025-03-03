<?php

namespace App\Http\Controllers\Admin\Dashboard\Cities;

use App\Http\Controllers\Admin\Controller;
use App\Http\Requests\Admin\Dashboard\Cities\ZoneRequest;
use App\Models\Admin\Dashboard\Cities\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class CitiesController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $cities = Zone::where('level', '1')->paginate(10);

        return view('admin.content.dashboard.cities.index', compact('cities'));
    }

    /**
     * Show the form for creating a new resource.
     */

    public function create()
    {

        $cities = Zone::where('level', '1')->get();

        return view('admin.content.dashboard.cities.create', compact('cities'));

    }

    public function createCity()
    {
        $cities = Zone::where('level', '1')->get();

        return view('admin.content.dashboard.cities.create-city', compact('cities'));

    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(ZoneRequest $request)
    {
        $zone = new Zone;
        $zone->name = $request->input('name');
        $zone->parent_id = $request->input('parent_id');
        $zone->price_shipping = $request->input('price_shipping');
        $zone->save();
        if ($zone) {
            return redirect()->route('admin.Dashboard.cities.index')->with('swal-success', ' استان  جدید شما با موفقیت اضافه شد');

        } else {
            return redirect()->back();
        }

    }

    public function storeCity(ZoneRequest $request)
    {
        $zone = new Zone;
        $zone->name = $request->input('name');
        $zone->parent_id = $request->input('parent_id');
        $zone->price_shipping = $request->input('price_shipping');
        $zone->level = 2;
        $zone->save();
        if ($zone) {
            return redirect()->route('admin.Dashboard.cities.show', $zone->parent_id)->with('swal-success', ' شهر  جدید شما با موفقیت اضافه شد');

        } else {
            return redirect()->back();
        }

    }

    /**
     * Display the specified resource.
     */

    public function show(Request $request, string $id)
    {

        //    dd( $citiesSearch );
        $citiesSearch = Zone::where('name', 'LIKE', '%' . $request->search . '%')->get();


        $cityName = Zone::where('id', $id)->get();

        $cities = Zone::where('parent_id', $id)->paginate(10);

        return view('admin.content.dashboard.cities.show', compact(['cities', 'cityName']));

    }

    /**
     * Show the form for editing the specified resource.
     */
    // public function edit( string $id )

    public function edit(string $id)
    {

        $city = Zone::where('id', $id)->first();
        return view('admin.content.dashboard.cities.edit', compact('city'));

    }

    public function editCity(string $id)
    {
        $cities = Zone::where('level', '1')->get();

        $cityy = Zone::where('id', $id)->first();
        return view('admin.content.dashboard.cities.edit-city', compact(['cityy', 'cities']));

    }

    /**
     * Update the specified resource in storage.
     */

    public function update(ZoneRequest $request, string $id)
    {

        $item = Zone::find($id);
        $item->name = $request->input('name');
        $item->parent_id = $request->input('level');
        $item->price_shipping = $request->input('price_shipping');
        $item->save();
        return redirect()->route('admin.Dashboard.cities.index')->with('swal-success', 'ویرایش استان با موفقیت انجام شد');

    }

    public function updateCity(ZoneRequest $request, string $id)
    {

        $item = Zone::find($id);
        $item->name = $request->input('name');
        $item->parent_id = $request->input('level');
        $item->price_shipping = $request->input('price_shipping');
        $item->save();
        return redirect()->route('admin.Dashboard.cities.show', $item->parent_id)->with('swal-success', 'ویرایش شهر با موفقیت انجام شد');

    }

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(string $id)
    {
        $item = Zone::find($id);
        $item->delete();
        return redirect()->route('admin.Dashboard.cities.index')->with('swal-success', 'حذف استان با موفقیت انجام شد');

    }

    public function destroyCity(string $id)
    {
        $item = Zone::find($id);
        $item->delete();
        return redirect()->route('admin.Dashboard.cities.show', $item->parent_id)->with('swal-success', 'حذف شهر با موفقیت انجام شد');

    }
}





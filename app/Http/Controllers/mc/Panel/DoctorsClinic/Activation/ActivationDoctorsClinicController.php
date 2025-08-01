<?php

namespace App\Http\Controllers\Dr\Panel\DoctorsClinic\Activation;

use App\Http\Controllers\Dr\Controller;
use App\Models\Clinic;
use Illuminate\Http\Request;

class ActivationDoctorsClinicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id)
    {
        $clinic = Clinic::where('id', $id)->first();
        return view("dr.panel.doctors-clinic.activation.index", compact('clinic'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateAddress(Request $request, $id)
    {
        $clinic            = Clinic::findOrFail($id);
        $clinic->address   = $request->input('address');
        $clinic->latitude  = $request->input('latitude');
        $clinic->longitude = $request->input('longitude');
        $clinic->save();

        return response()->json(['message' => 'اطلاعات با موفقیت به‌روزرسانی شد']);
    }

    public function getPhones($id)
    {
        $clinic         = Clinic::findOrFail($id);
        $phones         = $clinic->phone_numbers ? json_decode($clinic->phone_numbers, true) : [];
        $secretaryPhone = $clinic->secretary_phone;

        return response()->json(['phones' => $phones, 'secretary_phone' => $secretaryPhone]);
    }

    public function updatePhones(Request $request, $id)
    {

        $clinic = Clinic::findOrFail($id);

        // ذخیره شماره‌های موبایل
        $clinic->phone_numbers   = json_encode($request->input('phones', []));
        $clinic->secretary_phone = $request->secretary_phone;
        $clinic->save();

        return response()->json(['success' => true, 'phones' => $clinic->phone_numbers]);
    }
    public function getSecretaryPhone($id)
    {
        // پیدا کردن کلینیک بر اساس ID
        $clinic = Clinic::find($id);

        if (! $clinic) {
            return response()->json(['error' => 'کلینیک پیدا نشد.'], 404);
        }

        // بررسی شماره موبایل منشی
        $secretaryPhone = $clinic->secretary_phone;

        return response()->json(['secretary_phone' => $secretaryPhone]);
    }

    public function deletePhone(Request $request, $id)
    {
        $clinic     = Clinic::findOrFail($id);
        $phoneIndex = $request->input('phone_index');

        if ($clinic->phone_numbers) {
            $phones = json_decode($clinic->phone_numbers, true);
            if (isset($phones[$phoneIndex])) {
                unset($phones[$phoneIndex]);                                 // حذف شماره تماس از آرایه
                $clinic->phone_numbers = json_encode(array_values($phones)); // مرتب‌سازی مجدد آرایه
                $clinic->save();
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

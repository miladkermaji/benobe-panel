<?php

namespace App\Http\Controllers\Admin\Doctors\DoctorsManagement;

use App\Models\Dr\Doctor;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\Controller;
use App\Models\Admin\Dashboard\Cities\Zone;
use App\Models\Admin\Doctors\DoctorManagement\DoctorTariff;

class DoctorsManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $doctors = Doctor::with(['city', 'province'])->get();

        return view("admin.content.doctors.doctors-management.index", compact('doctors'));
    }
    public function status(Doctor $doctor)
    {

        $doctor->status = $doctor->status == 0 ? 1 : 0;
        $result = $doctor->save();
        if ($result) {
            if ($doctor->status == 0) {
                return response()->json(['status' => true, 'active' => false]);
            } else {
                return response()->json(['status' => true, 'active' => true]);
            }
        } else {
            return response()->json(['status' => false]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view("admin.content.doctors.doctors-management.create");
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
    public function edit(Doctor $doctor)
    {
        return view('admin.content.doctors.doctors-management.edit', compact('doctor'));
    }

    public function update(Request $request, Doctor $doctor)
    {
        $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:11|unique:doctors,mobile,' . $doctor->id,
            'status' => 'required|in:0,1,2,3,4',
            'visit_fee' => 'nullable|integer|min:0',
            'site_fee' => 'nullable|integer|min:0',
        ]);

        // آپدیت اطلاعات دکتر
        $doctor->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'mobile' => $request->mobile,
            'status' => $request->status,
        ]);

        // آپدیت یا ایجاد تعرفه
        $tariff = $doctor->tariff ?? new DoctorTariff(['doctor_id' => $doctor->id]);
        $tariff->visit_fee = $request->visit_fee ?? 0;
        $tariff->site_fee = $request->site_fee ?? 0;
        $tariff->save();

        return redirect()->route('admin.doctors.doctors-management.index')->with('success', 'اطلاعات پزشک با موفقیت به‌روزرسانی شد.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

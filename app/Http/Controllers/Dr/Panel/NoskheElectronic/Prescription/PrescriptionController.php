<?php

namespace App\Http\Controllers\Dr\Panel\NoskheElectronic\Prescription;

use Illuminate\Http\Request;
use App\Http\Controllers\Dr\Controller;

class PrescriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('dr.panel.noskhe-electronic.prescription.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dr.panel.noskhe-electronic.prescription.create');
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function myPrescriptions()
    {
        $doctor = auth('doctor')->user();
        $prescriptions = \App\Models\PrescriptionRequest::where('doctor_id', $doctor->id)
            ->orderByDesc('created_at')
            ->paginate(20);
        return view('dr.panel.noskhe-electronic.prescription.my-prescriptions', compact('prescriptions'));
    }
}

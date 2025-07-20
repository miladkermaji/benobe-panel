<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorSearchController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->input('q');
        $doctors = Doctor::query()
            ->where(function ($query) use ($q) {
                $query->where('first_name', 'like', "%$q%")
                      ->orWhere('last_name', 'like', "%$q%")
                      ->orWhere('mobile', 'like', "%$q%")
                      ->orWhere('id', $q);
            })
            ->select('id', 'first_name', 'last_name')
            ->limit(20)
            ->get()
            ->map(function ($doctor) {
                return [
                    'id' => $doctor->id,
                    'text' => $doctor->first_name . ' ' . $doctor->last_name
                ];
            });

        return response()->json(['results' => $doctors]);
    }
}

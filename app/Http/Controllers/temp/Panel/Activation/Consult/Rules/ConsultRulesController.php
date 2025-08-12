<?php

namespace App\Http\Controllers\Mc\Panel\Activation\Consult\Rules;

use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\AcademicDegree;
use App\Models\DoctorSpecialty;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Mc\Controller;

class ConsultRulesController extends Controller
{
    public function index()
    {
        return view('mc.panel.activation.consult.rules.index');
    }
    public function help()
    {
        return view('mc.panel.activation.consult.help.index');
    }
    public function messengers()
    {
        try {
            $doctor = $this->getAuthenticatedDoctor();
        } catch (\Exception $e) {
            // If no doctor is selected, redirect with error message
            return redirect()->back()->with('error', 'لطفاً ابتدا یک پزشک انتخاب کنید.');
        }

        $currentSpecialty         = DoctorSpecialty::where('doctor_id', $doctor->id)->first();
        $specialtyName            = $currentSpecialty->specialty_title ?? 'نامشخص';
        $doctor_specialties       = DoctorSpecialty::where('doctor_id', $doctor->id)->get();
        $doctorSpecialties        = DoctorSpecialty::where('doctor_id', $doctor->id)->get();
        $existingSpecialtiesCount = DoctorSpecialty::where('doctor_id', $doctor->id)->count();
        $doctorSpecialtyId        = DoctorSpecialty::where('doctor_id', $doctor->id)->first();
        $academic_degrees         = AcademicDegree::active()
            ->orderBy('sort_order')
            ->get();
        $messengers         = $doctor->messengers;
        $specialties        = Specialty::getOptimizedList();
        $incompleteSections = $doctor->getIncompleteProfileSections();

        return view("mc.panel.activation.consult.messengers.index", compact([
            'specialtyName',
            'academic_degrees',
            'specialties',
            'currentSpecialty',
            'doctor_specialties',
            'doctorSpecialtyId',
            'existingSpecialtiesCount',
            'messengers',
            'doctor',
            'incompleteSections',
        ]));
    }
}

<?php

namespace App\Http\Controllers\Mc\Panel\Activation\Consult\Rules;

use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\AcademicDegree;
use App\Models\DoctorSpecialty;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Mc\Controller;

class ConsultRulesController extends Controller
{
    protected function getAuthenticatedDoctor(): Doctor
    {
        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        if (! $doctor instanceof Doctor) {
            throw new \Exception('کاربر احراز هویت شده از نوع Doctor نیست یا وجود ندارد.');
        }
        return $doctor;
    }
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

        $doctor                   = $this->getAuthenticatedDoctor();
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

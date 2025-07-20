<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\MedicalCenter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class RegisterDoctorMedicalCenterController extends Controller
{
    // ثبت‌نام پزشک
    public function registerDoctor(Request $request)
    {
        $messages = [
            'first_name.required' => 'وارد کردن نام الزامی است.',
            'last_name.required' => 'وارد کردن نام خانوادگی الزامی است.',
            'national_code.required' => 'کد ملی الزامی است.',
            'national_code.max' => 'کد ملی باید ۱۰ رقم باشد.',
            'sex.required' => 'انتخاب جنسیت الزامی است.',
            'sex.in' => 'جنسیت انتخاب شده معتبر نیست.',
            'medical_system_code_type_id.required' => 'انتخاب نوع کد نظام پزشکی الزامی است.',
            'license_number.required' => 'کد نظام پزشکی الزامی است.',
            'specialty_id.required' => 'انتخاب تخصص الزامی است.',
            'province_id.required' => 'انتخاب استان الزامی است.',
            'city_id.required' => 'انتخاب شهر الزامی است.',
        ];
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'national_code' => 'required|string|max:10',
            'sex' => 'required|in:male,female',
            'medical_system_code_type_id' => 'required|integer',
            'license_number' => 'required|string',
            'specialty_id' => 'required|integer',
            'province_id' => 'required|integer',
            'city_id' => 'required|integer',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
        ], $messages);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (Doctor::where('national_code', $request->national_code)->exists()) {
            return response()->json(['errors' => ['national_code' => ['پزشکی با این کد ملی قبلاً ثبت شده است.']]], 422);
        }

        $doctor = Doctor::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'national_code' => $request->national_code,
            'sex' => $request->sex,
            'medical_system_code_type_id' => $request->medical_system_code_type_id,
            'license_number' => $request->license_number,
            'specialty_id' => $request->specialty_id,
            'province_id' => $request->province_id,
            'city_id' => $request->city_id,
            'address' => $request->address,
            'description' => $request->description,
            'is_active' => false,
            'is_verified' => false,
        ]);

        return response()->json(['message' => 'کاربر گرامی، اطلاعات شما با موفقیت ثبت شد. ظرف چند روز آینده حساب شما بررسی و در صورت تایید فعال خواهد شد.', 'doctor' => $doctor], 201);
    }

    // ثبت‌نام مرکز درمانی
    public function registerMedicalCenter(Request $request)
    {
        $messages = [
            'title.required' => 'وارد کردن نام مرکز درمانی الزامی است.',
            'type.required' => 'انتخاب نوع مرکز درمانی الزامی است.',
            'type.in' => 'نوع مرکز درمانی باید یکی از گزینه‌های مجاز باشد: بیمارستان، مرکز درمانی، کلینیک، مرکز تصویربرداری، آزمایشگاه، داروخانه یا پلی‌کلینیک.',
            'province_id.required' => 'انتخاب استان الزامی است.',
            'city_id.required' => 'انتخاب شهر الزامی است.',
        ];
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:hospital,treatment_centers,clinic,imaging_center,laboratory,pharmacy,policlinic',
            'siam_code' => 'nullable|string',
            'province_id' => 'required|integer',
            'city_id' => 'required|integer',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
        ], $messages);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->siam_code && MedicalCenter::where('siam_code', $request->siam_code)->exists()) {
            return response()->json(['errors' => ['siam_code' => ['مرکزی با این کد سیام قبلاً ثبت شده است.']]], 422);
        }

        $medicalCenter = MedicalCenter::create([
            'title' => $request->title,
            'type' => $request->type,
            'siam_code' => $request->siam_code,
            'province_id' => $request->province_id,
            'city_id' => $request->city_id,
            'address' => $request->address,
            'description' => $request->description,
            'is_active' => false,
        ]);

        return response()->json(['message' => 'کاربر گرامی، اطلاعات شما با موفقیت ثبت شد. ظرف چند روز آینده حساب شما بررسی و در صورت تایید فعال خواهد شد.', 'medical_center' => $medicalCenter->fresh()], 201);
    }
}

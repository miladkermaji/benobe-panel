<?php

namespace App\Http\Controllers\Dr\Panel\Secretary;

use App\Http\Controllers\Dr\Controller;
use App\Models\Secretary;
use App\Models\Doctor; // اضافه کردن مدل Doctor
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SecretaryManagementController extends Controller
{
    public function index(Request $request)
    {
        $doctorId         = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
        $selectedClinicId = $request->input('selectedClinicId') ?? 'default';


        $secretaries = Secretary::where('doctor_id', $doctorId)
            ->when($selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                $query->where('clinic_id', $selectedClinicId);
            }, function ($query) {
                $query->whereNull('clinic_id');
            })
            ->get();
        if ($request->ajax()) {
            return response()->json(['secretaries' => $secretaries]);
        }

        return view('dr.panel.secretary.index', compact('secretaries'));
    }

    public function store(Request $request)
    {
        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
        $clinicId = $request->selectedClinicId === 'default' ? null : $request->selectedClinicId;

        // تبدیل اعداد فارسی به انگلیسی
        $request->merge([
            'mobile' => \App\Helpers\PersianNumber::convertToEnglish($request->mobile),
            'national_code' => \App\Helpers\PersianNumber::convertToEnglish($request->national_code)
        ]);

        // اضافه کردن لاگ برای بررسی مقادیر ورودی
 

        // اعتبارسنجی داده‌های ورودی
        $request->validate([
            'first_name'    => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'mobile'        => [
                'required',
                'regex:/^09[0-9]{9}$/', // فرمت شماره موبایل ایرانی
                function ($attribute, $value, $fail) use ($doctorId, $clinicId) {
                    // بررسی تکراری بودن در جدول secretaries
                    $existsInSecretaries = Secretary::where('mobile', $value)
                        ->where('doctor_id', $doctorId)
                        ->where(function ($query) use ($clinicId) {
                            if ($clinicId) {
                                $query->where('clinic_id', $clinicId);
                            } else {
                                $query->whereNull('clinic_id');
                            }
                        })->exists();
                    if ($existsInSecretaries) {
                        $fail('این شماره موبایل قبلاً برای این دکتر یا کلینیک ثبت شده است.');
                    }

                    // بررسی وجود شماره در جدول doctors
                    $existsInDoctors = Doctor::where('mobile', $value)->exists();
                    if ($existsInDoctors) {
                        $fail('این شماره موبایل متعلق به یک دکتر است و نمی‌تواند برای منشی استفاده شود.');
                    }
                },
            ],
            'national_code' => [
                'required',
                'digits:10',
                function ($attribute, $value, $fail) use ($doctorId, $clinicId) {
                    $exists = Secretary::where('national_code', $value)
                        ->where('doctor_id', $doctorId)
                        ->where(function ($query) use ($clinicId) {
                            if ($clinicId) {
                                $query->where('clinic_id', $clinicId);
                            } else {
                                $query->whereNull('clinic_id');
                            }
                        })->exists();
                    if ($exists) {
                        $fail('این کد ملی قبلاً برای این دکتر یا کلینیک ثبت شده است.');
                    }
                },
            ],
            'gender'        => 'required|string|in:male,female',
            'password'      => 'nullable|min:6',
        ], [
            'first_name.required'    => 'لطفاً نام را وارد کنید.',
            'first_name.string'      => 'نام باید یک رشته متنی باشد.',
            'first_name.max'         => 'نام نمی‌تواند بیشتر از 255 کاراکتر باشد.',
            'last_name.required'     => 'لطفاً نام خانوادگی را وارد کنید.',
            'last_name.string'       => 'نام خانوادگی باید یک رشته متنی باشد.',
            'last_name.max'          => 'نام خانوادگی نمی‌تواند بیشتر از 255 کاراکتر باشد.',
            'mobile.required'        => 'لطفاً شماره موبایل را وارد کنید.',
            'mobile.regex'           => 'شماره موبایل باید با 09 شروع شود و 11 رقم باشد.',
            'national_code.required' => 'لطفاً کد ملی را وارد کنید.',
            'national_code.digits'   => 'کد ملی باید دقیقاً 10 رقم باشد.',
            'gender.required'        => 'لطفاً جنسیت را انتخاب کنید.',
            'gender.in'              => 'جنسیت باید یکی از گزینه‌های "مرد" یا "زن" باشد.',
            'password.min'           => 'کلمه عبور باید حداقل 6 کاراکتر باشد.',
        ]);

        try {
            $secretary = Secretary::create([
                'doctor_id'     => $doctorId,
                'clinic_id'     => $clinicId,
                'first_name'    => $request->first_name,
                'last_name'     => $request->last_name,
                'mobile'        => $request->mobile,
                'national_code' => $request->national_code,
                'gender'        => $request->gender,
                'password'      => $request->password ? Hash::make($request->password) : null,
            ]);

          

            \App\Models\SecretaryPermission::create([
                'doctor_id'    => $doctorId,
                'secretary_id' => $secretary->id,
                'clinic_id'    => $clinicId,
                'permissions'  => json_encode([
                    "dashboard",
                    "0",
                    "appointments",
                    "dr-appointments",
                    "dr-workhours",
                    "dr-mySpecialDays",
                    "dr-manual_nobat_setting",
                    "dr-manual_nobat",
                    "dr-scheduleSetting",
                    "consult",
                    "dr-moshavere_setting",
                    "dr-moshavere_waiting",
                    "consult-term.index",
                    "dr-mySpecialDays-counseling",
                    "prescription",
                    "prescription.index",
                    "providers.index",
                    "favorite.templates.index",
                    "templates.favorite.service.index",
                    "patient_records",
                    "dr-patient-records",
                    "clinic_management",
                    "dr-clinic-management",
                    "dr-office-gallery",
                    "dr-office-medicalDoc",
                    "insurance",
                    "0",
                    "messages",
                    "dr-panel-tickets",
                ]),
                'has_access'   => true,
            ]);

            $secretaries = Secretary::where('doctor_id', $doctorId)
                ->where(function ($query) use ($clinicId) {
                    if ($clinicId) {
                        $query->where('clinic_id', $clinicId);
                    } else {
                        $query->whereNull('clinic_id');
                    }
                })->get();

            return response()->json([
                'message'     => 'منشی با موفقیت ثبت شد و دسترسی‌های پیش‌فرض اضافه شدند.',
                'secretary'   => $secretary,
                'secretaries' => $secretaries,
            ]);
        } catch (\Exception $e) {
          
            return response()->json([
                'message' => 'خطا در ثبت منشی یا دسترسی‌ها!',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function edit(Request $request, $id)
    {
        $selectedClinicId = $request->input('selectedClinicId') ?? 'default';

        $secretary = Secretary::where('id', $id)
            ->when($selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                $query->where('clinic_id', $selectedClinicId);
            })
            ->firstOrFail();

        return response()->json($secretary);
    }


    public function update(Request $request, $id)
    {
        $selectedClinicId = $request->input('selectedClinicId') ?? 'default';

        // تبدیل اعداد فارسی به انگلیسی
        $request->merge([
            'mobile' => \App\Helpers\PersianNumber::convertToEnglish($request->mobile),
            'national_code' => \App\Helpers\PersianNumber::convertToEnglish($request->national_code)
        ]);

        $request->validate([
            'first_name'    => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'mobile'        => [
                'required',
                'regex:/^09[0-9]{9}$/',
                function ($attribute, $value, $fail) use ($id, $selectedClinicId) {
                    // بررسی تکراری بودن در جدول secretaries
                    $existsInSecretaries = Secretary::where('mobile', $value)
                        ->where('id', '!=', $id)
                        ->where(function ($query) use ($selectedClinicId) {
                            if ($selectedClinicId !== 'default') {
                                $query->where('clinic_id', $selectedClinicId);
                            } else {
                                $query->whereNull('clinic_id');
                            }
                        })->exists();
                    if ($existsInSecretaries) {
                        $fail('این شماره موبایل قبلاً برای این کلینیک یا دکتر ثبت شده است.');
                    }

                    // بررسی وجود شماره در جدول doctors
                    $existsInDoctors = Doctor::where('mobile', $value)->exists();
                    if ($existsInDoctors) {
                        $fail('این شماره موبایل متعلق به یک دکتر است و نمی‌تواند برای منشی استفاده شود.');
                    }
                },
            ],
            'national_code' => [
                'required',
                'digits:10',
                function ($attribute, $value, $fail) use ($id, $selectedClinicId) {
                    $exists = Secretary::where('national_code', $value)
                        ->where('id', '!=', $id)
                        ->where(function ($query) use ($selectedClinicId) {
                            if ($selectedClinicId !== 'default') {
                                $query->where('clinic_id', $selectedClinicId);
                            } else {
                                $query->whereNull('clinic_id');
                            }
                        })->exists();
                    if ($exists) {
                        $fail('این کد ملی قبلاً برای این کلینیک یا دکتر ثبت شده است.');
                    }
                },
            ],
            'gender'        => 'required|string|in:male,female',
            'password'      => 'nullable|min:6',
        ], [
            'first_name.required'    => 'لطفاً نام را وارد کنید.',
            'first_name.string'      => 'نام باید یک رشته متنی باشد.',
            'first_name.max'         => 'نام نمی‌تواند بیشتر از 255 کاراکتر باشد.',
            'last_name.required'     => 'لطفاً نام خانوادگی را وارد کنید.',
            'last_name.string'       => 'نام خانوادگی باید یک رشته متنی باشد.',
            'last_name.max'          => 'نام خانوادگی نمی‌تواند بیشتر از 255 کاراکتر باشد.',
            'mobile.required'        => 'لطفاً شماره موبایل را وارد کنید.',
            'mobile.regex'           => 'شماره موبایل باید با 09 شروع شود و 11 رقم باشد.',
            'national_code.required' => 'لطفاً کد ملی را وارد کنید.',
            'national_code.digits'   => 'کد ملی باید دقیقاً 10 رقم باشد.',
            'gender.required'        => 'لطفاً جنسیت را انتخاب کنید.',
            'gender.in'              => 'جنسیت باید یکی از گزینه‌های "مرد" یا "زن" باشد.',
            'password.min'           => 'کلمه عبور باید حداقل 6 کاراکتر باشد.',
        ]);

        $secretary = Secretary::findOrFail($id);

        $secretary->update([
            'first_name'    => $request->first_name,
            'last_name'     => $request->last_name,
            'mobile'        => $request->mobile,
            'national_code' => $request->national_code,
            'gender'        => $request->gender,
            'password'      => $request->password ? Hash::make($request->password) : $secretary->password,
        ]);

        $secretaries = Secretary::where('doctor_id', $secretary->doctor_id)
            ->where(function ($query) use ($selectedClinicId) {
                if ($selectedClinicId !== 'default') {
                    $query->where('clinic_id', $selectedClinicId);
                } else {
                    $query->whereNull('clinic_id');
                }
            })
            ->get();

        return response()->json([
            'message'     => 'منشی با موفقیت ویرایش شد.',
            'secretaries' => $secretaries,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $selectedClinicId = $request->input('selectedClinicId') ?? 'default';

        $secretary = Secretary::where('id', $id)
            ->when($selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                $query->where('clinic_id', $selectedClinicId);
            })
            ->firstOrFail();

        $secretary->delete();

        $secretaries = Secretary::where('doctor_id', $secretary->doctor_id)
            ->when($selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                $query->where('clinic_id', $selectedClinicId);
            })
            ->get();

        return response()->json(['message' => 'منشی با موفقیت حذف شد', 'secretaries' => $secretaries]);
    }

}

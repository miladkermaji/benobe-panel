<?php

namespace App\Http\Controllers\Dr\Panel\Secretary;

use App\Http\Controllers\Dr\Controller;
use App\Models\Secretary;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SecretaryManagementController extends Controller
{
    public function index(Request $request)
    {
        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
        $selectedClinicId = $this->getSelectedClinicId();

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
        $clinicId = $this->getSelectedClinicId() === 'default' ? null : $this->getSelectedClinicId();

        $request->merge([
            'mobile' => \App\Helpers\PersianNumber::convertToEnglish($request->mobile),
            'national_code' => \App\Helpers\PersianNumber::convertToEnglish($request->national_code)
        ]);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'mobile' => [
                'required',
                'regex:/^09[0-9]{9}$/',
                function ($attribute, $value, $fail) use ($doctorId, $clinicId) {
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
            'gender' => 'required|string|in:male,female',
            'password' => 'nullable|min:6',
        ], [
            'first_name.required' => 'لطفاً نام را وارد کنید.',
            'first_name.string' => 'نام باید یک رشته متنی باشد.',
            'first_name.max' => 'نام نمی‌تواند بیشتر از 255 کاراکتر باشد.',
            'last_name.required' => 'لطفاً نام خانوادگی را وارد کنید.',
            'last_name.string' => 'نام خانوادگی باید یک رشته متنی باشد.',
            'last_name.max' => 'نام خانوادگی نمی‌تواند بیشتر از 255 کاراکتر باشد.',
            'mobile.required' => 'لطفاً شماره موبایل را وارد کنید.',
            'mobile.regex' => 'شماره موبایل باید با 09 شروع شود و 11 رقم باشد.',
            'national_code.required' => 'لطفاً کد ملی را وارد کنید.',
            'national_code.digits' => 'کد ملی باید دقیقاً 10 رقم باشد.',
            'gender.required' => 'لطفاً جنسیت را انتخاب کنید.',
            'gender.in' => 'جنسیت باید یکی از گزینه‌های "مرد" یا "زن" باشد.',
            'password.min' => 'کلمه عبور باید حداقل 6 کاراکتر باشد.',
        ]);

        try {
            $secretary = Secretary::create([
                'doctor_id' => $doctorId,
                'clinic_id' => $clinicId,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'mobile' => $request->mobile,
                'national_code' => $request->national_code,
                'gender' => $request->gender,
                'password' => $request->password ? Hash::make($request->password) : null,
                'status' => 1, // پیش‌فرض: فعال
            ]);

            \App\Models\SecretaryPermission::create([
                'doctor_id' => $doctorId,
                'secretary_id' => $secretary->id,
                'clinic_id' => $clinicId,
                'permissions' => json_encode([
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
                'has_access' => true,
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
                'message' => 'منشی با موفقیت ثبت شد و دسترسی‌های پیش‌فرض اضافه شدند.',
                'secretary' => $secretary,
                'secretaries' => $secretaries,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'خطا در ثبت منشی یا دسترسی‌ها!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit(Request $request, $id)
    {
        $selectedClinicId = $this->getSelectedClinicId();

        $secretary = Secretary::where('id', $id)
            ->where('doctor_id', Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id)
            ->when($selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                $query->where('clinic_id', $selectedClinicId);
            })
            ->firstOrFail();

        return response()->json($secretary);
    }

    public function update(Request $request, $id)
    {
        $selectedClinicId = $this->getSelectedClinicId();

        $request->merge([
            'mobile' => \App\Helpers\PersianNumber::convertToEnglish($request->mobile),
            'national_code' => \App\Helpers\PersianNumber::convertToEnglish($request->national_code)
        ]);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'mobile' => [
                'required',
                'regex:/^09[0-9]{9}$/',
                function ($attribute, $value, $fail) use ($id, $selectedClinicId) {
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
            'gender' => 'required|string|in:male,female',
            'password' => 'nullable|min:6',
        ], [
            'first_name.required' => 'لطفاً نام را وارد کنید.',
            'first_name.string' => 'نام باید یک رشته متنی باشد.',
            'first_name.max' => 'نام نمی‌تواند بیشتر از 255 کاراکتر باشد.',
            'last_name.required' => 'لطفاً نام خانوادگی را وارد کنید.',
            'last_name.string' => 'نام خانوادگی باید یک رشته متنی باشد.',
            'last_name.max' => 'نام خانوادگی نمی‌تواند بیشتر از 255 کاراکتر باشد.',
            'mobile.required' => 'لطفاً شماره موبایل را وارد کنید.',
            'mobile.regex' => 'شماره موبایل باید با 09 شروع شود و 11 رقم باشد.',
            'national_code.required' => 'لطفاً کد ملی را وارد کنید.',
            'national_code.digits' => 'کد ملی باید دقیقاً 10 رقم باشد.',
            'gender.required' => 'لطفاً جنسیت را انتخاب کنید.',
            'gender.in' => 'جنسیت باید یکی از گزینه‌های "مرد" یا "زن" باشد.',
            'password.min' => 'کلمه عبور باید حداقل 6 کاراکتر باشد.',
        ]);

        $secretary = Secretary::where('id', $id)
            ->where('doctor_id', Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id)
            ->firstOrFail();

        $secretary->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'mobile' => $request->mobile,
            'national_code' => $request->national_code,
            'gender' => $request->gender,
            'password' => $request->password ? Hash::make($request->password) : $secretary->password,
        ]);

        $secretaries = Secretary::where('doctor_id', $secretary->doctor_id)
            ->where(function ($query) use ($selectedClinicId) {
                if ($selectedClinicId !== 'default') {
                    $query->where('clinic_id', $selectedClinicId);
                } else {
                    $query->whereNull('clinic_id');
                }
            })->get();

        return response()->json([
            'message' => 'منشی با موفقیت ویرایش شد.',
            'secretaries' => $secretaries,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $selectedClinicId = $this->getSelectedClinicId();

        $secretary = Secretary::where('id', $id)
            ->where('doctor_id', Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id)
            ->when($selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                $query->where('clinic_id', $selectedClinicId);
            })
            ->firstOrFail();

        $secretary->delete();

        $secretaries = Secretary::where('doctor_id', $secretary->doctor_id)
            ->where(function ($query) use ($selectedClinicId) {
                if ($selectedClinicId !== 'default') {
                    $query->where('clinic_id', $selectedClinicId);
                } else {
                    $query->whereNull('clinic_id');
                }
            })->get();

        return response()->json(['message' => 'منشی با موفقیت حذف شد', 'secretaries' => $secretaries]);
    }

    public function updateStatus(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:secretaries,id',
                'status' => 'required|in:0,1',
                'selectedClinicId' => 'nullable|string',
            ]);

            $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
            $clinicId = $this->getSelectedClinicId() === 'default' ? null : $this->getSelectedClinicId();

            $secretary = Secretary::where('id', $request->id)
                ->where('doctor_id', $doctorId)
                ->where(function ($query) use ($clinicId) {
                    if ($clinicId) {
                        $query->where('clinic_id', $clinicId);
                    } else {
                        $query->whereNull('clinic_id');
                    }
                })
                ->firstOrFail();

            $secretary->status = $request->status;
            $secretary->save();

            $secretaries = Secretary::where('doctor_id', $doctorId)
                ->where(function ($query) use ($clinicId) {
                    if ($clinicId) {
                        $query->where('clinic_id', $clinicId);
                    } else {
                        $query->whereNull('clinic_id');
                    }
                })->get();

            return response()->json([
                'success' => true,
                'message' => 'وضعیت منشی با موفقیت تغییر کرد.',
                'secretaries' => $secretaries,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در اطلاعات ارسالی!',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در تغییر وضعیت منشی!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function groupAction(Request $request)
    {
        try {
            $request->validate([
                'secretary_ids' => 'required|array',
                'secretary_ids.*' => 'exists:secretaries,id',
                'action' => 'required|in:delete,status_active,status_inactive',
                'selectedClinicId' => 'nullable|string',
            ]);

            $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
            $clinicId = $request->input('selectedClinicId') === 'default' ? null : $request->input('selectedClinicId');
            $secretaryIds = $request->input('secretary_ids');
            $action = $request->input('action');

            $secretaries = Secretary::whereIn('id', $secretaryIds)
                ->where('doctor_id', $doctorId)
                ->where(function ($query) use ($clinicId) {
                    if ($clinicId) {
                        $query->where('clinic_id', $clinicId);
                    } else {
                        $query->whereNull('clinic_id');
                    }
                })
                ->get();

            if ($secretaries->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'هیچ منشی‌ای برای انجام عملیات یافت نشد.',
                ], 422);
            }

            foreach ($secretaries as $secretary) {
                if ($action === 'delete') {
                    $secretary->delete();
                } elseif ($action === 'status_active') {
                    $secretary->status = 1;
                    $secretary->save();
                } elseif ($action === 'status_inactive') {
                    $secretary->status = 0;
                    $secretary->save();
                }
            }

            $updatedSecretaries = Secretary::where('doctor_id', $doctorId)
                ->where(function ($query) use ($clinicId) {
                    if ($clinicId) {
                        $query->where('clinic_id', $clinicId);
                    } else {
                        $query->whereNull('clinic_id');
                    }
                })->get();

            $message = '';
            switch ($action) {
                case 'delete':
                    $message = 'منشی‌های انتخاب‌شده با موفقیت حذف شدند.';
                    break;
                case 'status_active':
                    $message = 'وضعیت منشی‌های انتخاب‌شده به فعال تغییر کرد.';
                    break;
                case 'status_inactive':
                    $message = 'وضعیت منشی‌های انتخاب‌شده به غیرفعال تغییر کرد.';
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'secretaries' => $updatedSecretaries,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در اطلاعات ارسالی!',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در اجرای عملیات گروهی!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

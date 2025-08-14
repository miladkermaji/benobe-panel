<?php

namespace App\Http\Controllers\Dr\Panel\DoctorsClinic;

use App\Models\Zone;
use App\Models\MedicalCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Dr\Controller;
use App\Traits\HasSelectedClinic;
use App\Helpers\PersianNumber;
use App\Models\MedicalCenterDepositSetting;

class DoctorsClinicManagementController extends Controller
{
    use HasSelectedClinic;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {


        // ارسال داده‌ها به ویو
        return view('dr.panel.doctors-clinic.index');
    }

    public function getProvincesAndCities()
    {
        // کش کردن لیست استان‌ها و شهرها برای یک روز
        $zones = Cache::remember('zones', 86400, function () {
            return Zone::where('status', 1)
                ->orderBy('sort')
                ->get(['id', 'name', 'parent_id', 'level']);
        });

        // دسته‌بندی داده‌ها به استان‌ها و شهرها
        $provinces = $zones->where('level', 1)->values();             // سطح 1 => استان‌ها
        $cities    = $zones->where('level', 2)->groupBy('parent_id'); // سطح 2 => شهرها

        return view('dr.panel.doctors-clinic.index', compact('provinces', 'cities'));
    }
    public function create()
    {
        return view('dr.panel.doctors-clinic.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'phone_numbers'   => 'required|array|min:1',
            'phone_numbers.*' => 'required|string|max:15',
            'address'         => 'nullable|string',
            'province_id'     => 'required|exists:zone,id',
            'city_id'         => 'required|exists:zone,id',
            'postal_code'     => 'nullable|string',
            'description'     => 'nullable|string',
        ], [
            'name.required'            => 'وارد کردن نام مطب الزامی است.',
            'name.string'              => 'نام مطب باید یک رشته معتبر باشد.',
            'name.max'                 => 'نام مطب نباید بیشتر از 255 کاراکتر باشد.',
            'phone_numbers.required'   => 'وارد کردن حداقل یک شماره موبایل الزامی است.',
            'phone_numbers.*.required' => 'وارد کردن شماره موبایل الزامی است.',
            'phone_numbers.*.string'   => 'شماره موبایل باید یک رشته معتبر باشد.',
            'phone_numbers.*.max'      => 'شماره موبایل نباید بیشتر از 15 کاراکتر باشد.',
            'address.string'           => 'آدرس باید یک رشته معتبر باشد.',
            'province_id.required'     => 'انتخاب استان الزامی است.',
            'province_id.exists'       => 'استان انتخاب‌شده معتبر نیست.',
            'city_id.required'         => 'انتخاب شهر الزامی است.',
            'city_id.exists'           => 'شهر انتخاب‌شده معتبر نیست.',
            'postal_code.string'       => 'کد پستی باید یک رشته معتبر باشد.',
            'description.string'       => 'توضیحات باید یک رشته معتبر باشد.',
        ]);

        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;

        $medicalCenter = MedicalCenter::create([
            'name' => $request->name,
            'phone_numbers' => $request->phone_numbers,
            'province_id' => $request->province_id,
            'city_id' => $request->city_id,
            'postal_code' => $request->postal_code,
            'address' => $request->address,
            'description' => $request->description,
            'type' => 'policlinic',
            'is_active' => true,
        ]);

        // Attach the doctor to the medical center
        $medicalCenter->doctors()->attach($doctorId);

        return response()->json([
            'message' => 'مطب با موفقیت اضافه شد',
            'clinic_id' => $medicalCenter->id,
            'success' => true
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'phone_numbers'   => 'required|array|min:1',
            'phone_numbers.*' => 'required|string|max:15',
            'address'         => 'nullable|string',
            'province_id'     => 'required|exists:zone,id',
            'city_id'         => 'required|exists:zone,id',
            'postal_code'     => 'nullable|string',
            'description'     => 'nullable|string',
        ], [
            'name.required'            => 'وارد کردن نام مطب الزامی است.',
            'name.string'              => 'نام مطب باید یک رشته معتبر باشد.',
            'name.max'                 => 'نام مطب نباید بیشتر از 255 کاراکتر باشد.',
            'phone_numbers.required'   => 'وارد کردن حداقل یک شماره موبایل الزامی است.',
            'phone_numbers.*.required' => 'وارد کردن شماره موبایل الزامی است.',
            'phone_numbers.*.string'   => 'شماره موبایل باید یک رشته معتبر باشد.',
            'phone_numbers.*.max'      => 'شماره موبایل نباید بیشتر از 15 کاراکتر باشد.',
            'address.string'           => 'آدرس باید یک رشته معتبر باشد.',
            'province_id.required'     => 'انتخاب استان الزامی است.',
            'province_id.exists'       => 'استان انتخاب‌شده معتبر نیست.',
            'city_id.required'         => 'انتخاب شهر الزامی است.',
            'city_id.exists'           => 'شهر انتخاب‌شده معتبر نیست.',
            'postal_code.string'       => 'کد پستی باید یک رشته معتبر باشد.',
            'description.string'       => 'توضیحات باید یک رشته معتبر باشد.',
        ]);

        $clinic = MedicalCenter::findOrFail($id);
        $clinic->update([
            'name'          => $request->name,
            'phone_numbers' => json_encode($request->phone_numbers),
            'address'       => $request->address,
            'province_id'   => $request->province_id,
            'city_id'       => $request->city_id,
            'postal_code'   => $request->postal_code,
            'description'   => $request->description,
        ]);

        return response()->json(['message' => 'مطب با موفقیت ویرایش شد']);
    }

    public function edit($id)
    {
        $clinic = MedicalCenter::findOrFail($id);
        return view('dr.panel.doctors-clinic.edit', compact('clinic'));
    }

    public function getCitiesByProvince($provinceId)
    {
        $cities = Zone::where('parent_id', $provinceId)->get(['id', 'name']);
        return response()->json($cities);
    }

    public function destroy($id)
    {
        $clinic = MedicalCenter::findOrFail($id);
        $clinic->delete();

        return response()->json(['message' => 'مطب با موفقیت حذف شد']);
    }

    public function gallery($id)
    {
        return view("dr.panel.doctors-clinic.gallery", compact('id'));
    }


    public function medicalDoc()
    {
        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
        return view("dr.panel.doctors-clinic.medicalDoc", compact('doctorId'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function deposit(Request $request)
    {
        try {
            $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
            $selectedClinicId = $this->getSelectedMedicalCenterId();

            // Get clinics with error handling
            try {
                $clinics = MedicalCenter::whereHas('doctors', function ($query) use ($doctorId) {
                    $query->where('doctor_id', $doctorId);
                })->where('type', 'policlinic')->get();
            } catch (\Exception $e) {
                $clinics = collect([]);
            }

            // Get deposits with error handling
            try {
                $deposits = MedicalCenterDepositSetting::where('doctor_id', $doctorId)
                    ->when($selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                        $query->where('medical_center_id', $selectedClinicId);
                    }, function ($query) {
                        $query->whereNull('medical_center_id');
                    })
                    ->get();
            } catch (\Exception $e) {
                $deposits = collect([]);
            }

            return view('dr.panel.doctors-clinic.deposit', compact('clinics', 'deposits', 'selectedClinicId', 'doctorId'));
        } catch (\Exception $e) {
            return back()->with('error', 'خطا در بارگذاری اطلاعات: ' . $e->getMessage());
        }
    }

    public function storeDeposit(Request $request)
    {
        try {
            $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
            $selectedClinicId = $this->getSelectedMedicalCenterId();

            // تبدیل اعداد فارسی به انگلیسی
            if ($request->has('custom_price') && $request->custom_price) {
                $request->merge(['custom_price' => PersianNumber::convertToEnglish($request->custom_price)]);
            }
            if ($request->has('deposit_amount') && $request->deposit_amount) {
                $request->merge(['deposit_amount' => PersianNumber::convertToEnglish($request->deposit_amount)]);
            }

            // قوانین اعتبارسنجی پویا
            $rules = [
                'is_custom_price' => 'required|boolean',
                'no_deposit' => 'nullable|boolean',
            ];

            if ($request->is_custom_price && !$request->no_deposit) {
                $rules['custom_price'] = 'required|numeric|min:0';
            } elseif (!$request->no_deposit) {
                $rules['deposit_amount'] = 'required|numeric|min:0';
            }

            $messages = [
                'deposit_amount.required' => 'مبلغ بیعانه الزامی است.',
                'deposit_amount.numeric' => 'مبلغ بیعانه باید یک عدد معتبر باشد.',
                'deposit_amount.min' => 'مبلغ بیعانه نمی‌تواند منفی باشد.',
                'custom_price.required' => 'مبلغ دلخواه الزامی است.',
                'custom_price.numeric' => 'مبلغ دلخواه باید یک عدد معتبر باشد.',
                'custom_price.min' => 'مبلغ دلخواه نمی‌تواند منفی باشد.',
                'is_custom_price.required' => 'نوع قیمت (دلخواه یا پیش‌فرض) الزامی است.',
            ];

            $validated = $request->validate($rules, $messages);

            $clinicId = $selectedClinicId === 'default' ? null : $selectedClinicId;
            if ($clinicId && !MedicalCenter::where('id', $clinicId)->whereHas('doctors', function ($query) use ($doctorId) {
                $query->where('doctor_id', $doctorId);
            })->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'مطب انتخاب‌شده معتبر نیست یا متعلق به شما نیست.'
                ], 422);
            }

            // بررسی وجود بیعانه قبلی
            $existingDeposit = MedicalCenterDepositSetting::where('doctor_id', $doctorId)
                ->where(function ($query) use ($clinicId) {
                    if ($clinicId) {
                        $query->where('medical_center_id', $clinicId);
                    } else {
                        $query->whereNull('medical_center_id');
                    }
                })
                ->exists();

            if ($existingDeposit) {
                return response()->json([
                    'success' => false,
                    'message' => 'برای این مطب و پزشک قبلاً بیعانه ثبت شده است. لطفاً بیعانه موجود را ویرایش کنید.'
                ], 422);
            }

            $noDeposit = $validated['no_deposit'] ?? false;
            $depositAmount = $noDeposit ? 0 : // اگر بدون بیعانه باشد، مقدار 0 ذخیره شود
                ($validated['is_custom_price'] ? $validated['custom_price'] : $validated['deposit_amount']);

            if (!$noDeposit && is_null($depositAmount)) {
                return response()->json([
                    'success' => false,
                    'message' => 'لطفاً مبلغ بیعانه یا قیمت دلخواه را وارد کنید.'
                ], 422);
            }

            $deposit = MedicalCenterDepositSetting::create([
                'medical_center_id' => $clinicId,
                'doctor_id' => $doctorId,
                'deposit_amount' => $depositAmount,
                'is_custom_price' => $validated['is_custom_price'],
                'refundable' => true,
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'بیعانه با موفقیت ثبت شد.',
                'deposit' => $deposit
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در اطلاعات ورودی.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ثبت بیعانه: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateDeposit(Request $request, $id)
    {
        try {
            $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
            $selectedClinicId = $this->getSelectedMedicalCenterId();

            // تبدیل اعداد فارسی به انگلیسی
            if ($request->has('custom_price') && $request->custom_price) {
                $request->merge(['custom_price' => PersianNumber::convertToEnglish($request->custom_price)]);
            }
            if ($request->has('deposit_amount') && $request->deposit_amount) {
                $request->merge(['deposit_amount' => PersianNumber::convertToEnglish($request->deposit_amount)]);
            }

            // قوانین اعتبارسنجی پویا
            $rules = [
                'is_custom_price' => 'required|boolean',
                'no_deposit' => 'nullable|boolean',
            ];

            if ($request->is_custom_price && !$request->no_deposit) {
                $rules['custom_price'] = 'required|numeric|min:0';
            } elseif (!$request->no_deposit) {
                $rules['deposit_amount'] = 'required|numeric|min:0';
            }

            $messages = [
                'deposit_amount.required' => 'مبلغ بیعانه الزامی است.',
                'deposit_amount.numeric' => 'مبلغ بیعانه باید یک عدد معتبر باشد.',
                'deposit_amount.min' => 'مبلغ بیعانه نمی‌تواند منفی باشد.',
                'custom_price.required' => 'مبلغ دلخواه الزامی است.',
                'custom_price.numeric' => 'مبلغ دلخواه باید یک عدد معتبر باشد.',
                'custom_price.min' => 'مبلغ دلخواه نمی‌تواند منفی باشد.',
                'is_custom_price.required' => 'نوع قیمت (دلخواه یا پیش‌فرض) الزامی است.',
            ];

            $validated = $request->validate($rules, $messages);

            $deposit = MedicalCenterDepositSetting::where('id', $id)
                ->where('doctor_id', $doctorId)
                ->when($selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                    $query->where('medical_center_id', $selectedClinicId);
                }, function ($query) {
                    $query->whereNull('medical_center_id');
                })
                ->first();

            if (!$deposit) {
                return response()->json([
                    'success' => false,
                    'message' => 'بیعانه موردنظر یافت نشد یا متعلق به شما نیست.'
                ], 404);
            }

            $noDeposit = $validated['no_deposit'] ?? false;
            $depositAmount = $noDeposit ? 0 : // اگر بدون بیعانه باشد، مقدار 0 ذخیره شود
                ($validated['is_custom_price'] ? $validated['custom_price'] : $validated['deposit_amount']);

            if (!$noDeposit && is_null($depositAmount)) {
                return response()->json([
                    'success' => false,
                    'message' => 'لطفاً مبلغ بیعانه یا قیمت دلخواه را وارد کنید.'
                ], 422);
            }

            $deposit->update([
                'deposit_amount' => $depositAmount,
                'is_custom_price' => $validated['is_custom_price'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'بیعانه با موفقیت ویرایش شد.',
                'deposit' => $deposit
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در اطلاعات ورودی.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ویرایش بیعانه: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyDeposit(Request $request, $id)
    {
        try {
            $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
            $selectedClinicId = $this->getSelectedMedicalCenterId();

            $deposit = MedicalCenterDepositSetting::where('id', $id)
                ->where('doctor_id', $doctorId)
                ->when($selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                    $query->where('medical_center_id', $selectedClinicId);
                }, function ($query) {
                    $query->whereNull('medical_center_id');
                })
                ->first();

            if (!$deposit) {
                return response()->json([
                    'success' => false,
                    'message' => 'بیعانه موردنظر یافت نشد یا متعلق به شما نیست.'
                ], 404);
            }

            $deposit->delete();

            return response()->json([
                'success' => true,
                'message' => 'بیعانه با موفقیت حذف شد.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف بیعانه: ' . $e->getMessage()
            ], 500);
        }
    }
}

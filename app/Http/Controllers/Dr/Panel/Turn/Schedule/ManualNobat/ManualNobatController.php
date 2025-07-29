<?php

namespace App\Http\Controllers\Dr\Panel\Turn\Schedule\ManualNobat;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\DoctorService;
use App\Models\ManualAppointment;
use Morilog\Jalali\CalendarUtils;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Dr\Controller;
use App\Models\ManualAppointmentSetting;
use Illuminate\Support\Facades\Validator;

class ManualNobatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        try {
            $selectedClinicId = $this->getSelectedMedicalCenterId();
            ;

            $appointments = ManualAppointment::with('user')
                ->when($selectedClinicId === 'default', function ($query) {
                    // نوبت‌هایی که کلینیک ندارند (clinic_id = NULL)
                    $query->whereNull('clinic_id');
                })
                ->when($selectedClinicId && $selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                    // نوبت‌های مربوط به کلینیک مشخص‌شده
                    $query->where('clinic_id', $selectedClinicId);
                })
                ->get();

            // بررسی نوع درخواست (AJAX یا عادی)
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data'    => $appointments,
                ]);
            }

            return view('dr.panel.turn.schedule.manual_nobat.index', compact('appointments'));
        } catch (\Exception $e) {
            Log::error('Error in fetching appointments: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'خطا در بازیابی نوبت‌ها!',
                ], 500);
            }

            return abort(500, 'خطا در بازیابی اطلاعات!');
        }
    }

    public function showSettings(Request $request)
    {
        $doctorId         = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
        $selectedClinicId = $this->getSelectedMedicalCenterId();

        // جستجوی تنظیمات با در نظر گرفتن کلینیک
        $settings = ManualAppointmentSetting::where('doctor_id', $doctorId)
            ->when($selectedClinicId === 'default', function ($query) {
                $query->whereNull('clinic_id');
            })
            ->when($selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                $query->where('clinic_id', $selectedClinicId);
            })
            ->first();

        return view('dr.panel.turn.schedule.manual_nobat.manual-nobat-setting', compact('settings'));
    }

    public function saveSettings(Request $request)
    {
        // اعتبارسنجی ورودی‌ها با پیام‌های فارسی
        $validator = Validator::make($request->all(), [
            'status'                => 'required|boolean',
            'duration_send_link'    => 'required|integer|min:1',
            'duration_confirm_link' => 'required|integer|min:1',
            'selectedClinicId'      => 'nullable|string',
        ], [
            'status.required'                => 'وضعیت فعال یا غیرفعال بودن باید مشخص شود.',
            'status.boolean'                 => 'وضعیت باید "بلی" یا "خیر" باشد.',
            'duration_send_link.required'    => 'زمان ارسال لینک تأیید الزامی است.',
            'duration_send_link.integer'     => 'زمان ارسال لینک باید یک عدد صحیح باشد.',
            'duration_send_link.min'         => 'زمان ارسال لینک باید حداقل ۱ ساعت باشد.',
            'duration_confirm_link.required' => 'مدت زمان اعتبار لینک الزامی است.',
            'duration_confirm_link.integer'  => 'مدت زمان اعتبار لینک باید یک عدد صحیح باشد.',
            'duration_confirm_link.min'      => 'مدت زمان اعتبار لینک باید حداقل ۱ ساعت باشد.',
            'selectedClinicId.string'        => 'شناسه کلینیک باید معتبر باشد.',
        ]);

        // اگر اعتبارسنجی ناموفق بود
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در اعتبارسنجی اطلاعات واردشده.',
                'errors'  => $validator->errors()->all(), // همه خطاها به صورت آرایه
            ], 422); // کد وضعیت 422 برای خطاهای اعتبارسنجی
        }

        try {
            // گرفتن آیدی پزشک یا منشی
            $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
            $selectedClinicId = $this->getSelectedMedicalCenterId();

            // ذخیره یا به‌روزرسانی تنظیمات نوبت‌دهی دستی
            $settings = ManualAppointmentSetting::updateOrCreate(
                [
                    'doctor_id' => $doctorId,
                    'clinic_id' => $selectedClinicId === 'default' ? null : $selectedClinicId,
                ],
                [
                    'is_active'             => $request->status,
                    'duration_send_link'    => $request->duration_send_link,
                    'duration_confirm_link' => $request->duration_confirm_link,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'تنظیمات با موفقیت ذخیره شد.',
                'data'    => [
                    'is_active'             => $settings->is_active ? 'بلی' : 'خیر',
                    'duration_send_link'    => $settings->duration_send_link . ' ساعت',
                    'duration_confirm_link' => $settings->duration_confirm_link . ' ساعت',
                    'clinic_id'             => $settings->clinic_id ?? 'پیش‌فرض',
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ذخیره تنظیمات رخ داد.',
                'error'   => [
                    'details' => $e->getMessage(),
                    'code'    => $e->getCode(),
                ],
            ], 500);
        }
    }

    public function searchUsers(Request $request)
    {
        try {
            $query = $request->get('query');

            // جستجو در جدول کاربران بر اساس نام، نام خانوادگی، شماره موبایل و کد ملی
            $users = User::where('first_name', 'LIKE', "%{$query}%")
                ->orWhere('last_name', 'LIKE', "%{$query}%")
                ->orWhere('mobile', 'LIKE', "%{$query}%")
                ->orWhere('national_code', 'LIKE', "%{$query}%")
                ->get();

            return response()->json($users);
        } catch (\Exception $e) {
            // ثبت خطا در لاگ لاراول
            Log::error('Error in searchUsers: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
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
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date_format:Y/m/d',
            'appointment_time' => 'required|date_format:H:i',
            'description' => 'nullable|string|max:1000',
            'selectedClinicId' => 'nullable|string',
        ], [
            'user_id.required' => 'شناسه کاربر الزامی است.',
            'user_id.exists' => 'کاربر موردنظر وجود ندارد.',
            'doctor_id.required' => 'شناسه پزشک الزامی است.',
            'doctor_id.exists' => 'پزشک موردنظر وجود ندارد.',
            'appointment_date.required' => 'تاریخ نوبت الزامی است.',
            'appointment_date.date_format' => 'فرمت تاریخ باید به صورت Y/m/d باشد.',
            'appointment_time.required' => 'ساعت نوبت الزامی است.',
            'appointment_time.date_format' => 'فرمت ساعت باید H:i باشد.',
            'description.max' => 'توضیحات نمی‌تواند بیش از ۱۰۰۰ کاراکتر باشد.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در اطلاعات ورودی!',
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        try {
            $data = $validator->validated();
            $data['appointment_date'] = CalendarUtils::createDatetimeFromFormat('Y/m/d', $request->appointment_date)->format('Y-m-d');
            $data['clinic_id'] = $this->getSelectedMedicalCenterId() === 'default' ? null : $this->getSelectedMedicalCenterId();

            if (ManualAppointment::where('user_id', $data['user_id'])
                ->where('appointment_date', $data['appointment_date'])
                ->where('appointment_time', $data['appointment_time'])
                ->where('clinic_id', $data['clinic_id'])
                ->exists()) {
                return response()->json(['success' => false, 'message' => 'این نوبت قبلاً ثبت شده است!'], 400);
            }

            ManualAppointment::create($data);

            return response()->json(['success' => true, 'message' => 'نوبت با موفقیت ثبت شد!']);
        } catch (\Exception $e) {
            Log::error('خطا در ثبت نوبت: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'خطا در ثبت نوبت!'], 500);
        }
    }

    /**
     * ثبت نوبت همراه با کاربر جدید
     */
    public function storeWithUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'mobile' => 'required|digits:11|unique:users,mobile',
            'national_code' => 'required|digits:10|unique:users,national_code',
            'appointment_date' => 'required|date_format:Y/m/d',
            'appointment_time' => 'required|date_format:H:i',
            'description' => 'nullable|string|max:1000',
            'selectedClinicId' => 'nullable|string',
        ], [
            'first_name.required' => 'نام بیمار الزامی است.',
            'first_name.max' => 'نام بیمار نمی‌تواند بیش از ۲۵۵ کاراکتر باشد.',
            'last_name.required' => 'نام خانوادگی بیمار الزامی است.',
            'last_name.max' => 'نام خانوادگی نمی‌تواند بیش از ۲۵۵ کاراکتر باشد.',
            'mobile.required' => 'شماره موبایل الزامی است.',
            'mobile.digits' => 'شماره موبایل باید ۱۱ رقمی باشد.',
            'mobile.unique' => 'این شماره موبایل قبلاً ثبت شده است.',
            'national_code.required' => 'کد ملی الزامی است.',
            'national_code.digits' => 'کد ملی باید ۱۰ رقمی باشد.',
            'national_code.unique' => 'این کد ملی قبلاً ثبت شده است.',
            'appointment_date.required' => 'تاریخ نوبت الزامی است.',
            'appointment_date.date_format' => 'فرمت تاریخ باید Y/m/d باشد.',
            'appointment_time.required' => 'ساعت نوبت الزامی است.',
            'appointment_time.date_format' => 'فرمت ساعت باید H:i باشد.',
            'description.max' => 'توضیحات نمی‌تواند بیش از ۱۰۰۰ کاراکتر باشد.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در اطلاعات ورودی!',
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        try {
            $data = $validator->validated();
            $data['appointment_date'] = CalendarUtils::createDatetimeFromFormat('Y/m/d', $request->appointment_date)->format('Y-m-d');

            DB::beginTransaction();

            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'mobile' => $data['mobile'],
                'national_code' => $data['national_code'],
            ]);

            $appointment = ManualAppointment::create([
                'user_id' => $user->id,
                'doctor_id' => Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id,
                'clinic_id' => $data['selectedClinicId'] === 'default' ? null : $data['selectedClinicId'],
                'appointment_date' => $data['appointment_date'],
                'appointment_time' => $data['appointment_time'],
                'description' => $data['description'],
            ]);

            DB::commit();

            $appointment->load('user');
            return response()->json(['success' => true, 'data' => $appointment], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('خطا در ثبت نوبت با کاربر جدید: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'خطا در ثبت اطلاعات!'], 500);
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id, Request $request)
    {
        try {
            $selectedClinicId = $this->getSelectedMedicalCenterId();
            ;

            $appointment = ManualAppointment::with('user')
                ->when($selectedClinicId === 'default', function ($query) {
                    $query->whereNull('clinic_id');
                })
                ->when($selectedClinicId && $selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                    $query->where('clinic_id', $selectedClinicId);
                })
                ->findOrFail($id);

            return response()->json(['success' => true, 'data' => $appointment]);
        } catch (\Exception $e) {
            Log::error('Error in edit: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'خطا در دریافت اطلاعات!'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'mobile' => 'required|digits:11',
            'national_code' => 'required|digits:10',
            'appointment_date' => 'required|date_format:Y/m/d',
            'appointment_time' => 'required|date_format:H:i',
            'description' => 'nullable|string|max:1000',
            'selectedClinicId' => 'nullable|string',
        ], [
            'first_name.required' => 'نام بیمار الزامی است.',
            'last_name.required' => 'نام خانوادگی بیمار الزامی است.',
            'mobile.required' => 'شماره موبایل الزامی است.',
            'mobile.digits' => 'شماره موبایل باید ۱۱ رقمی باشد.',
            'national_code.required' => 'کد ملی الزامی است.',
            'national_code.digits' => 'کد ملی باید ۱۰ رقمی باشد.',
            'appointment_date.required' => 'تاریخ نوبت الزامی است.',
            'appointment_date.date_format' => 'فرمت تاریخ باید Y/m/d باشد.',
            'appointment_time.required' => 'ساعت نوبت الزامی است.',
            'appointment_time.date_format' => 'فرمت ساعت باید H:i باشد.',
            'description.max' => 'توضیحات نمی‌تواند بیش از ۱۰۰۰ کاراکتر باشد.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در اطلاعات ورودی!',
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        try {
            $data = $validator->validated();
            $data['appointment_date'] = CalendarUtils::createDatetimeFromFormat('Y/m/d', $request->appointment_date)->format('Y-m-d');
            $appointment = ManualAppointment::when(
                $data['selectedClinicId'] === 'default',
                fn ($query) => $query->whereNull('clinic_id'),
                fn ($query) => $query->where('clinic_id', $data['selectedClinicId'])
            )->findOrFail($id);

            $appointment->user->update([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'mobile' => $data['mobile'],
                'national_code' => $data['national_code'],
            ]);

            $appointment->update([
                'clinic_id' => $data['selectedClinicId'] === 'default' ? null : $data['selectedClinicId'],
                'appointment_date' => $data['appointment_date'],
                'appointment_time' => $data['appointment_time'],
                'description' => $data['description'],
            ]);

            return response()->json(['success' => true, 'message' => 'نوبت با موفقیت ویرایش شد!']);
        } catch (\Exception $e) {
            Log::error('خطا در ویرایش نوبت: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'خطا در ویرایش نوبت!'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id, Request $request)
    {
        try {
            $selectedClinicId = $this->getSelectedMedicalCenterId();
            ;

            // جستجوی نوبت بر اساس کلینیک
            $appointment = ManualAppointment::when(
                $selectedClinicId === 'default',
                fn ($query) => $query->whereNull('clinic_id'),
                fn ($query) => $query->where('clinic_id', $selectedClinicId)
            )
                ->findOrFail($id);

            $appointment->delete();

            return response()->json(['success' => true, 'message' => 'نوبت با موفقیت حذف شد!']);
        } catch (\Exception $e) {
            Log::error('Error in destroy: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'خطا در حذف نوبت!'], 500);
        }
    }
    public function getInsurances(Request $request)
    {
        try {
            $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
            $selectedClinicId = $this->getSelectedMedicalCenterId();

            $insurances = DoctorService::where('doctor_services.doctor_id', $doctorId)
                ->where('doctor_services.status', true)
                ->when($selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                    $query->where('doctor_services.clinic_id', $selectedClinicId);
                })
                ->when($selectedClinicId === 'default', function ($query) {
                    $query->whereNull('doctor_services.clinic_id');
                })
                ->distinct()
                ->join('insurances', 'doctor_services.insurance_id', '=', 'insurances.id')
                ->select('insurances.id', 'insurances.name')
                ->get();

            Log::info('Insurances fetched successfully', ['insurances' => $insurances]);

            return response()->json([
                'success' => true,
                'data' => $insurances,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching insurances: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در بارگذاری بیمه‌ها!',
            ], 500);
        }
    }

    public function getServices(Request $request, $insuranceId)
    {
        try {
            $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
            $selectedClinicId = $this->getSelectedMedicalCenterId();

            $services = DoctorService::where('doctor_id', $doctorId)
                ->where('insurance_id', $insuranceId)
                ->where('status', true)
                ->when($selectedClinicId !== 'default', function ($query) use ($selectedClinicId) {
                    $query->where('clinic_id', $selectedClinicId);
                })
                ->when($selectedClinicId === 'default', function ($query) {
                    $query->whereNull('clinic_id');
                })
                ->select('id', 'name', 'price')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $services,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching services: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'خطا در بارگذاری خدمات!',
            ], 500);
        }
    }

    public function calculateFinalPrice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'exists:doctor_services,id',
            'is_free' => 'nullable|boolean',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'selectedClinicId' => 'nullable|string',
        ], [
            'service_ids.required' => 'لطفاً حداقل یک خدمت انتخاب کنید.',
            'service_ids.array' => 'خدمات باید به‌صورت آرایه باشند.',
            'service_ids.min' => 'لطفاً حداقل یک خدمت انتخاب کنید.',
            'service_ids.*.exists' => 'یکی از خدمات انتخاب‌شده معتبر نیست.',
            'is_free.boolean' => 'مقدار ویزیت رایگان باید بولین باشد.',
            'discount_percentage.min' => 'درصد تخفیف نمی‌تواند منفی باشد.',
            'discount_percentage.max' => 'درصد تخفیف نمی‌تواند بیشتر از ۱۰۰ باشد.',
            'discount_amount.min' => 'مبلغ تخفیف نمی‌تواند منفی باشد.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در اعتبارسنجی!',
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        try {
            $isFree = $request->input('is_free', false);
            $serviceIds = $request->input('service_ids', []);
            $discountPercentage = $request->input('discount_percentage', 0);
            $discountAmount = $request->input('discount_amount', 0);

            Log::info('Calculating final price', [
                'service_ids' => $serviceIds,
                'is_free' => $isFree,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discountAmount,
            ]);

            if ($isFree) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'final_price' => 0,
                        'discount_percentage' => 0,
                        'discount_amount' => 0,
                    ],
                ]);
            }

            $basePrice = DoctorService::whereIn('id', $serviceIds)
                ->where('status', true)
                ->sum('price');

            if (empty($serviceIds) || $basePrice == 0) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'final_price' => 0,
                        'discount_percentage' => 0,
                        'discount_amount' => 0,
                    ],
                ]);
            }

            if ($discountPercentage > 0) {
                $discountAmount = round(($basePrice * $discountPercentage) / 100, 2);
            } elseif ($discountAmount > 0) {
                $discountPercentage = round(($discountAmount / $basePrice) * 100, 2);
            } else {
                $discountPercentage = 0;
                $discountAmount = 0;
            }

            $finalPrice = round(max(0, $basePrice - $discountAmount), 0);

            Log::info('Final price calculated', [
                'base_price' => $basePrice,
                'final_price' => $finalPrice,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discountAmount,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'final_price' => $finalPrice,
                    'discount_percentage' => round($discountPercentage, 2),
                    'discount_amount' => round($discountAmount, 2),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error calculating final price: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در محاسبه قیمت نهایی!',
            ], 500);
        }
    }
    public function endVisit(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'insurance_id' => 'required|exists:insurances,id',
            'service_ids' => 'required_if:is_free,0|array|min:1',
            'service_ids.*' => 'exists:doctor_services,id',
            'payment_method' => 'required_unless:is_free,1|in:online,cash,card_to_card,pos',
            'is_free' => 'nullable|boolean',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'final_price' => [
                'required',
                'numeric',
                \Illuminate\Validation\Rule::when(!$request->input('is_free', false), 'gt:0', 'gte:0')
            ],
            'description' => 'nullable|string|max:1000',
            'selectedClinicId' => 'nullable|string',
        ], [
            'insurance_id.required' => 'لطفاً یک بیمه انتخاب کنید.',
            'insurance_id.exists' => 'بیمه انتخاب‌شده معتبر نیست.',
            'service_ids.required_if' => 'لطفاً حداقل یک خدمت انتخاب کنید.',
            'service_ids.array' => 'خدمات باید به‌صورت آرایه باشند.',
            'service_ids.min' => 'لطفاً حداقل یک خدمت انتخاب کنید.',
            'service_ids.*.exists' => 'یکی از خدمات انتخاب‌شده معتبر نیست.',
            'payment_method.required_unless' => 'لطفاً نوع پرداخت را انتخاب کنید.',
            'payment_method.in' => 'نوع پرداخت انتخاب‌شده معتبر نیست.',
            'discount_percentage.min' => 'درصد تخفیف نمی‌تواند منفی باشد.',
            'discount_percentage.max' => 'درصد تخفیف نمی‌تواند بیشتر از ۱۰۰ باشد.',
            'discount_amount.min' => 'مبلغ تخفیف نمی‌تواند منفی باشد.',
            'final_price.required' => 'قیمت نهایی الزامی است.',
            'final_price.gt' => 'قیمت نهایی باید بیشتر از صفر باشد، مگر اینکه ویزیت رایگان باشد.',
            'final_price.gte' => 'قیمت نهایی نمی‌تواند منفی باشد.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در اعتبارسنجی!',
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        try {
            $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
            $selectedClinicId = $this->getSelectedMedicalCenterId();

            Log::info('Attempting to find appointment', [
                'appointment_id' => $id,
                'doctor_id' => $doctorId,
                'selectedClinicId' => $selectedClinicId,
            ]);

            $appointment = ManualAppointment::find($id);

            if (!$appointment) {
                Log::warning('Appointment not found', ['appointment_id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'نوبت یافت نشد!',
                ], 404);
            }

            if ($appointment->doctor_id != $doctorId) {
                Log::warning('Doctor ID mismatch', [
                    'appointment_doctor_id' => $appointment->doctor_id,
                    'auth_doctor_id' => $doctorId,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'دسترسی غیرمجاز: این نوبت متعلق به پزشک دیگری است!',
                ], 403);
            }

            if ($selectedClinicId !== 'default' && $appointment->clinic_id != $selectedClinicId) {
                Log::warning('Clinic ID mismatch', [
                    'appointment_clinic_id' => $appointment->clinic_id,
                    'selectedClinicId' => $selectedClinicId,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'کلینیک انتخاب‌شده با نوبت مطابقت ندارد!',
                ], 422);
            }

            if ($selectedClinicId === 'default' && !is_null($appointment->clinic_id)) {
                Log::warning('Clinic ID expected to be null', [
                    'appointment_clinic_id' => $appointment->clinic_id,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'کلینیک انتخاب‌شده با نوبت مطابقت ندارد!',
                ], 422);
            }

            $data = $validator->validated();

            Log::info('End visit data', [
                'appointment_id' => $id,
                'data' => $data,
                'doctor_id' => $doctorId,
                'selectedClinicId' => $selectedClinicId,
            ]);

            $appointment->update([
                'insurance_id' => $data['insurance_id'],
                'final_price' => $data['final_price'],
                'status' => 'attended',
                'payment_status' => $data['is_free'] ? 'unpaid' : 'paid',
                'payment_method' => $data['is_free'] ? null : $data['payment_method'],
                'description' => $data['description'],
            ]);

            $cacheKey = "appointments_doctor_{$doctorId}_clinic_{$selectedClinicId}";
            Cache::forget($cacheKey);

            return response()->json([
                'success' => true,
                'message' => 'ویزیت با موفقیت ثبت شد!',
            ]);
        } catch (\Exception $e) {
            Log::error('Error ending visit: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در ثبت ویزیت!',
                'error_details' => $e->getMessage(),
            ], 500);
        }
    }
}

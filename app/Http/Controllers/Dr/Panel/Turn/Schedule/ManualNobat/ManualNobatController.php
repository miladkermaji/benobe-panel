<?php

namespace App\Http\Controllers\Dr\Panel\Turn\Schedule\ManualNobat;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\ManualAppointment;
use Morilog\Jalali\CalendarUtils;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            $selectedClinicId = $request->input('selectedClinicId');

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
        $doctorId         = auth('doctor')->id() ?? auth('secretary')->id();
        $selectedClinicId = $request->input('selectedClinicId', 'default');

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
            $doctorId = auth('doctor')->id() ?? auth('secretary')->id();
            $selectedClinicId = $request->input('selectedClinicId', 'default');

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
            $data['clinic_id'] = $request->selectedClinicId === 'default' ? null : $request->selectedClinicId;

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
                'doctor_id' => auth('doctor')->id() ?? auth('secretary')->id(),
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
            $selectedClinicId = $request->input('selectedClinicId');

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
            $selectedClinicId = $request->input('selectedClinicId');

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

}

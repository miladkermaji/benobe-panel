<?php

namespace App\Http\Controllers\Dr\Panel\Turn\Schedule\ManualNobat;

use App\Http\Controllers\Dr\Controller;
use Log;
use App\Models\User;
use Illuminate\Http\Request;
use Morilog\Jalali\CalendarUtils;
use Illuminate\Support\Facades\DB;
use App\Models\Dr\ManualAppointment;
use App\Models\Dr\ManualAppointmentSetting;

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
          'data' => $appointments,
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
    $doctorId = auth('doctor')->id() ?? auth('secretary')->id();
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
    // اعتبارسنجی ورودی‌ها
    $request->validate([
      'status' => 'required|boolean',
      'duration_send_link' => 'required|integer|min:1',
      'duration_confirm_link' => 'required|integer|min:1',
      'selectedClinicId' => 'nullable|string', // اضافه کردن کلینیک آیدی
    ]);

    try {
      // گرفتن آیدی پزشک یا منشی
      $doctorId = auth('doctor')->id() ?? auth('secretary')->id();
      $selectedClinicId = $request->input('selectedClinicId');

      // ذخیره یا به‌روزرسانی تنظیمات نوبت‌دهی دستی
      ManualAppointmentSetting::updateOrCreate(
        [
          'doctor_id' => $doctorId,
          'clinic_id' => $selectedClinicId === 'default' ? null : $selectedClinicId,
        ],
        [
          'is_active' => $request->status,
          'duration_send_link' => $request->duration_send_link,
          'duration_confirm_link' => $request->duration_confirm_link,
        ]
      );

      return response()->json(['success' => true, 'message' => 'تنظیمات با موفقیت ذخیره شد.']);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'خطا در ذخیره تنظیمات.',
        'error' => $e->getMessage(),
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
    $validatedData = $request->validate([
      'user_id' => 'required|exists:users,id',
      'doctor_id' => 'required|exists:doctors,id',
      'appointment_date' => 'required|date',
      'appointment_time' => 'required|date_format:H:i',
      'description' => 'nullable|string|max:1000',
      'selectedClinicId' => 'nullable', // اعتبارسنجی کلینیک آیدی
    ]);

    try {
      // تبدیل تاریخ شمسی به میلادی
      $gregorianDate = CalendarUtils::createDatetimeFromFormat('Y/m/d', $request->appointment_date)->format('Y-m-d');
      $validatedData['appointment_date'] = $gregorianDate;

      // افزودن کلینیک آیدی به داده‌ها اگر موجود باشد
      if ($request->has('selectedClinicId') && $request->selectedClinicId !== 'default') {
        $validatedData['clinic_id'] = $request->selectedClinicId;
      } else {
        $validatedData['clinic_id'] = null; // اگر دیفالت باشد، مقدار نال بماند
      }

      // بررسی نوبت تکراری برای هر کلینیک به صورت جداگانه
      $existingAppointment = ManualAppointment::where('user_id', $validatedData['user_id'])
        ->where('appointment_date', $gregorianDate)
        ->where('appointment_time', $validatedData['appointment_time'])
        ->where(function ($query) use ($validatedData) {
          // اگر کلینیک خاص ارسال شده باشد، برای همان کلینیک بررسی شود
          if (!empty($validatedData['clinic_id'])) {
            $query->where('clinic_id', $validatedData['clinic_id']);
          } else {
            // اگر selectedClinicId برابر با 'default' باشد، کلینیک نال باشد
            $query->whereNull('clinic_id');
          }
        })
        ->exists();

      // لاگ برای دیباگ
      Log::info('Existing Appointment Check:', ['exists' => $existingAppointment]);

      if ($existingAppointment) {
        return response()->json(['success' => false, 'message' => 'نوبت تکراری است.'], 400);
      }

      // ثبت نوبت جدید
      ManualAppointment::create($validatedData);

      return response()->json(['success' => true, 'message' => 'نوبت با موفقیت ثبت شد.']);
    } catch (\Exception $e) {
      Log::error('Error in ManualNobatController@store: ' . $e->getMessage());
      return response()->json(['success' => false, 'message' => 'خطا در ثبت نوبت.'], 500);
    }
  }




  public function storeWithUser(Request $request)
  {
    $validatedData = $request->validate([
      'first_name' => 'required|string|max:255',
      'last_name' => 'required|string|max:255',
      'mobile' => 'required|digits:11|unique:users,mobile',
      'national_code' => 'required|digits:10|unique:users,national_code',
      'appointment_date' => 'required|date',
      'appointment_time' => 'required|date_format:H:i',
      'description' => 'nullable|string|max:1000',
      'selectedClinicId' => 'nullable|string', // اضافه کردن فیلد کلینیک
    ]);

    try {
      // تبدیل تاریخ جلالی به میلادی
      $gregorianDate = CalendarUtils::createDatetimeFromFormat('Y/m/d', $request->appointment_date)->format('Y-m-d');
      $validatedData['appointment_date'] = $gregorianDate;

      DB::beginTransaction();

      // ایجاد کاربر
      $user = User::create([
        'first_name' => $validatedData['first_name'],
        'last_name' => $validatedData['last_name'],
        'mobile' => $validatedData['mobile'],
        'national_code' => $validatedData['national_code'],
      ]);

      // ایجاد نوبت با کلینیک انتخابی
      $appointment = ManualAppointment::create([
        'user_id' => $user->id,
        'doctor_id' => auth('doctor')->id() ?? auth('secretary')->id(),
        'clinic_id' => $validatedData['selectedClinicId'] === 'default' ? null : $validatedData['selectedClinicId'],
        'appointment_date' => $validatedData['appointment_date'],
        'appointment_time' => $validatedData['appointment_time'],
        'description' => $validatedData['description'],
      ]);

      DB::commit();

      // بارگذاری اطلاعات مرتبط با کاربر
      $appointment->load('user');

      return response()->json(['data' => $appointment], 201);
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Error in storeWithUser: ' . $e->getMessage());
      return response()->json(['error' => 'خطا در ذخیره اطلاعات!'], 500);
    }
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
    $validatedData = $request->validate([
      'first_name' => 'required|string|max:255',
      'last_name' => 'required|string|max:255',
      'mobile' => 'required|digits:11',
      'national_code' => 'required|digits:10',
      'appointment_date' => 'required|date',
      'appointment_time' => 'required|date_format:H:i',
      'description' => 'nullable|string|max:1000',
      'selectedClinicId' => 'nullable|string', // افزودن کلینیک آیدی
    ]);

    try {
      $appointment = ManualAppointment::when(
        $validatedData['selectedClinicId'] === 'default',
        fn($query) => $query->whereNull('clinic_id'),
        fn($query) => $query->where('clinic_id', $validatedData['selectedClinicId'])
      )->findOrFail($id);

      // به‌روزرسانی اطلاعات کاربر مرتبط
      $appointment->user->update([
        'first_name' => $validatedData['first_name'],
        'last_name' => $validatedData['last_name'],
        'mobile' => $validatedData['mobile'],
        'national_code' => $validatedData['national_code'],
      ]);

      // به‌روزرسانی اطلاعات نوبت
      $appointment->update([
        'clinic_id' => $validatedData['selectedClinicId'] === 'default' ? null : $validatedData['selectedClinicId'],
        'appointment_date' => CalendarUtils::createDatetimeFromFormat('Y/m/d', $request->appointment_date)->format('Y-m-d'),
        'appointment_time' => $validatedData['appointment_time'],
        'description' => $validatedData['description'],
      ]);

      return response()->json(['success' => true, 'message' => 'نوبت با موفقیت ویرایش شد.']);
    } catch (\Exception $e) {
      Log::error('Error in update: ' . $e->getMessage());
      return response()->json(['success' => false, 'message' => 'خطا در ویرایش نوبت.'], 500);
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
        fn($query) => $query->whereNull('clinic_id'),
        fn($query) => $query->where('clinic_id', $selectedClinicId)
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

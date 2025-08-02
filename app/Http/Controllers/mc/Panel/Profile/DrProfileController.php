<?php

namespace App\Http\Controllers\Mc\Panel\Profile;

use Carbon\Carbon;
use App\Models\Otp;
use App\Models\Zone;
use App\Models\Doctor;
use App\Models\DoctorFaq;
use App\Models\Secretary;
use App\Models\Specialty;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\AcademicDegree;
use App\Models\DoctorSpecialty;
use Illuminate\Support\Facades\DB;
use App\Traits\HandlesRateLimiting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Mc\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\DoctorSpecialtyRequest;
use Modules\SendOtp\App\Http\Services\MessageService;
use Modules\SendOtp\App\Http\Services\SMS\SmsService;

class DrProfileController extends Controller
{
    use HandlesRateLimiting;

    protected function getAuthenticatedDoctor(): Doctor
    {
        $user = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();

        if ($user instanceof Doctor) {
            return $user;
        }

        if ($user instanceof Secretary) {
            return $user->doctor;
        }

        throw new \Exception('کاربر احراز هویت شده از نوع Doctor نیست یا وجود ندارد.');
    }

    public function uploadPhoto(Request $request)
    {
        if (! $request->hasFile('photo')) {
            return response()->json(['success' => false, 'message' => 'لطفاً یک عکس انتخاب کنید!'], 400);
        }

        $request->validate([
            'photo' => 'image',
        ], [
            'photo.image' => 'فایل انتخاب شده باید یک تصویر باشد.',
        ]);

        try {
            $doctor = $this->getAuthenticatedDoctor();
            $path   = $request->file('photo')->store('profile-photos', 'public');
            $doctor->update(['profile_photo_path' => $path]);

            return response()->json([
                'success' => true,
                'message' => 'عکس پروفایل با موفقیت آپدیت شد.',
                'path'    => Storage::url($path),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در آپلود عکس: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function index()
    {
        //
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        //
    }

    public function edit()
    {
        $doctor                   = $this->getAuthenticatedDoctor();
        $currentSpecialty         = DoctorSpecialty::where('doctor_id', $doctor->id ?? $doctor->doctor_id)->first();
        $specialtyName            = $currentSpecialty->specialty_title ?? 'نامشخص';
        $doctor_specialties       = DoctorSpecialty::where('doctor_id', $doctor->id ?? $doctor->doctor_id)->get();
        $doctorSpecialties        = DoctorSpecialty::where('doctor_id', $doctor->id ?? $doctor->doctor_id)->get();
        $existingSpecialtiesCount = DoctorSpecialty::where('doctor_id', $doctor->id ?? $doctor->doctor_id)->count();
        $doctorSpecialtyId        = DoctorSpecialty::where('doctor_id', $doctor->id ?? $doctor->doctor_id)->first();
        $academic_degrees         = AcademicDegree::active()
            ->orderBy('sort_order')
            ->get();
        $messengers         = $doctor->messengers;
        $specialties        = Specialty::getOptimizedList();
        $incompleteSections = $doctor->getIncompleteProfileSections();
        $doctorFaqs         = $doctor->faqs()->orderBy('order', 'asc')->get();

        // دریافت درجه علمی و اولین تخصص برای نمایش در هدر پروفایل
        $academicDegreeTitle = '';
        $firstSpecialtyName = '';

        if ($currentSpecialty) {
            // دریافت عنوان درجه علمی
            if ($currentSpecialty->academic_degree_id) {
                $academicDegree = AcademicDegree::find($currentSpecialty->academic_degree_id);
                $academicDegreeTitle = $academicDegree ? $academicDegree->title : '';
            }

            // دریافت نام اولین تخصص
            if ($currentSpecialty->specialty_id) {
                $specialty = Specialty::find($currentSpecialty->specialty_id);
                $firstSpecialtyName = $specialty ? $specialty->name : '';
            }
        }

        return view("mc.panel.profile.edit-profile", compact([
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
            'academicDegreeTitle',
            'firstSpecialtyName',
            'doctorFaqs',
        ]));
    }

    public function DrSpecialtyUpdate(DoctorSpecialtyRequest $request)
    {
        $key      = 'update_static_password_' . $request->ip();
        $response = $this->checkRateLimit($key);
        if ($response) {
            return $response;
        }

        $doctor    = $this->getAuthenticatedDoctor();

        // حالت auto_save (ویرایش تکی)
        if ($request->has('auto_save') && $request->has('specialty_id')) {
            $specialtyId = $request->input('specialty_id');
            $specialtyValue = $request->input('specialty_id_value');
            // جلوگیری از ثبت تخصص تکراری (به جز خودش)
            $duplicate = \App\Models\DoctorSpecialty::where('doctor_id', $doctor->id ?? $doctor->doctor_id)
                ->where('specialty_id', $specialtyValue)
                ->where('id', '!=', $specialtyId)
                ->exists();
            if ($duplicate) {
                return response()->json([
                    'success' => false,
                    'message' => 'این تخصص قبلاً ثبت شده است و امکان تکرار وجود ندارد.'
                ], 422);
            }
            $specialty = \App\Models\DoctorSpecialty::find($specialtyId);
            if (!$specialty) {
                return response()->json([
                    'success' => false,
                    'message' => 'تخصص مورد نظر یافت نشد.'
                ], 404);
            }
            $specialty->academic_degree_id = $request->input('academic_degree_id');
            $specialty->specialty_id = $specialtyValue;
            $specialty->specialty_title = $request->input('specialty_title');
            $specialty->save();

            return response()->json([
                'success' => true,
                'message' => 'تخصص با موفقیت ذخیره شد.',
                'update_sidebar' => true, // فلگ برای بروزرسانی سایدبار
                'specialty_title' => $request->input('specialty_title'),
                'academic_degree_id' => $request->input('academic_degree_id'),
                'specialty_id' => $specialtyValue,
                'specialty_name' => \App\Models\Specialty::find($specialtyValue)->name ?? '',
            ]);
        }

        $validator = Validator::make($request->all(), $request->rules());
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();
            $mainSpecialty = DoctorSpecialty::updateOrCreate(
                ['doctor_id' => $doctor->id ?? $doctor->doctor_id, 'is_main' => true],
                [
                    'academic_degree_id' => $request->academic_degree_id,
                    'specialty_id'       => $request->specialty_id,
                    'specialty_title'    => $request->specialty_title,
                ]
            );

            if ($request->has('degrees') && $request->has('specialties')) {
                $additionalSpecialtiesCount = 0;
                foreach ($request->degrees as $index => $degreeId) {
                    if (! empty($degreeId) && ! empty($request->specialties[$index])) {
                        $duplicateSpecialty = DoctorSpecialty::where('doctor_id', $doctor->id ?? $doctor->doctor_id)
                            ->where('specialty_id', $request->specialties[$index])
                            ->exists();
                        if ($duplicateSpecialty) {
                            continue;
                        }
                        $additionalSpecialtiesCount++;
                        if ($additionalSpecialtiesCount > 2) {
                            break;
                        }
                        DoctorSpecialty::create([
                            'doctor_id'          => $doctor->id ?? $doctor->doctor_id,
                            'academic_degree_id' => $degreeId,
                            'specialty_id'       => $request->specialties[$index],
                            'specialty_title'    => $request->titles[$index] ?? null,
                            'is_main'            => false,
                        ]);
                    }
                }
            }

            DB::commit();
            $this->updateProfileCompletion($doctor);
            $updatedSpecialties = DoctorSpecialty::where('doctor_id', $doctor->id ?? $doctor->doctor_id)->where('is_main', 0)->get();

            return response()->json([
                'success'     => true,
                'message'     => 'اطلاعات تخصص با موفقیت به‌روزرسانی شد.',
                'specialties' => $updatedSpecialties,
                'update_sidebar' => true, // فلگ برای بروزرسانی سایدبار
                'specialty_title' => $request->specialty_title,
                'academic_degree_id' => $request->academic_degree_id,
                'specialty_id' => $request->specialty_id,
                'specialty_name' => \App\Models\Specialty::find($request->specialty_id)->name ?? '',
                'additional_specialties' => $updatedSpecialties->map(function ($specialty) {
                    return [
                        'id' => $specialty->id,
                        'academic_degree_id' => $specialty->academic_degree_id,
                        'specialty_id' => $specialty->specialty_id,
                        'specialty_title' => $specialty->specialty_title,
                        'is_main' => $specialty->is_main,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'خطایی در به‌روزرسانی اطلاعات تخصص رخ داد.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function DrUUIDUpdate(Request $request)
    {
        $key      = 'DrSpecialtyUpdate_' . $request->ip();
        $response = $this->checkRateLimit($key);
        if ($response) {
            return $response;
        }

        $doctor    = $this->getAuthenticatedDoctor();
        $validator = Validator::make($request->all(), [
            'uuid' => [
                'string',
                'nullable',
                'unique:doctors,uuid,' . $doctor->id ?? $doctor->doctor_id,
                'regex:/^[a-zA-Z0-9۰-۹_-]+$/u',
                function ($attribute, $value, $fail) use ($doctor) {
                    $existingDoctor = Doctor::where('uuid', $value)
                        ->where('id', '!=', $doctor->id ?? $doctor->doctor_id)
                        ->first();
                    if ($existingDoctor) {
                        $fail('این UUID قبلاً توسط پزشک دیگری ثبت شده است');
                    }
                },
            ],
        ], [
            'uuid.string'    => 'شناسه باید یک رشته باشد.',
            'uuid.unique'    => 'این شناسه قبلاً استفاده شده است.',
            'uuid.regex'     => 'شناسه فقط می‌تواند شامل حروف، اعداد (فارسی و انگلیسی)، خط تیره و زیرخط باشد.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $doctor->uuid = $request->uuid;
        if ($doctor->save()) {
            $this->updateProfileCompletion($doctor);
            return response()->json([
                'success' => true,
                'message' => 'آیدی شما با موفقیت تغییر کرد',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'خطایی رخ داده',
            ]);
        }
    }

    public function deleteSpecialty($id)
    {
        try {
            $specialty = DoctorSpecialty::findOrFail($id);
            $specialty->delete();
            return response()->json([
                'success' => true,
                'message' => 'تخصص با موفقیت حذف شد.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف تخصص.',
            ], 500);
        }
    }

    public function updateMessengers(Request $request)
    {
        $doctor   = $this->getAuthenticatedDoctor();
        $key      = "updateMessengers_" . $request->ip();
        $response = $this->checkRateLimit($key);
        if ($response) {
            return $response;
        }

        // تبدیل اعداد فارسی به انگلیسی قبل از اعتبارسنجی
        if (class_exists('App\\Helpers\\PersianNumber')) {
            $request->merge([
                'ita_phone'      => \App\Helpers\PersianNumber::convertToEnglish($request->ita_phone),
                'telegram_phone' => \App\Helpers\PersianNumber::convertToEnglish($request->telegram_phone),
            ]);
        }

        $request->validate([
            'ita_phone'      => [
                'nullable',
                'string',
                'max:20',
                'regex:/^(?!09{1}([0-9۰-۹])\1{8}$)09(?:01|02|03|12|13|14|15|16|18|19|20|21|22|30|33|35|36|38|39|90|91|92|93|94)[0-9۰-۹]{7}$/u',
            ],
            'ita_username'   => 'nullable|string|max:100',
            'telegram_phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^(?!09{1}([0-9۰-۹])\1{8}$)09(?:01|02|03|12|13|14|15|16|18|19|20|21|22|30|33|35|36|38|39|90|91|92|93|94)[0-9۰-۹]{7}$/u',
            ],
            'telegram_username' => 'nullable|string|max:100',
            'instagram_username' => 'nullable|string|max:100',
            'secure_call'    => 'nullable|boolean',
        ], [
            'ita_phone.string'      => 'شماره موبایل ایتا باید یک رشته باشد.',
            'ita_phone.max'         => 'شماره موبایل ایتا نمی‌تواند بیشتر از ۲۰ کاراکتر باشد.',
            'ita_phone.regex'       => 'شماره موبایل ایتا باید با اعداد فارسی یا انگلیسی و فرمت صحیح وارد شود.',
            'ita_username.string'   => 'نام کاربری ایتا باید یک رشته باشد.',
            'ita_username.max'      => 'نام کاربری ایتا نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',
            'telegram_phone.string' => 'شماره موبایل تلگرام باید یک رشته باشد.',
            'telegram_phone.max'    => 'شماره موبایل تلگرام نمی‌تواند بیشتر از ۲۰ کاراکتر باشد.',
            'telegram_phone.regex'  => 'شماره موبایل تلگرام باید با اعداد فارسی یا انگلیسی و فرمت صحیح وارد شود.',
            'telegram_username.string' => 'نام کاربری تلگرام باید یک رشته باشد.',
            'telegram_username.max'    => 'نام کاربری تلگرام نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',
            'instagram_username.string' => 'نام کاربری اینستاگرام باید یک رشته باشد.',
            'instagram_username.max'    => 'نام کاربری اینستاگرام نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',
            'secure_call.boolean'   => 'وضعیت تماس امن باید یک مقدار بولین باشد.',
        ]);

        $doctor->messengers()->updateOrCreate(
            ['messenger_type' => 'ita'],
            [
                'phone_number'   => $request->ita_phone,
                'username'       => $request->ita_username,
                'is_secure_call' => $request->secure_call,
            ]
        );

        $doctor->messengers()->updateOrCreate(
            ['messenger_type' => 'telegram'],
            [
                'phone_number'   => $request->telegram_phone,
                'username'       => $request->telegram_username,
                'is_secure_call' => $request->secure_call,
            ]
        );

        $doctor->messengers()->updateOrCreate(
            ['messenger_type' => 'instagram'],
            [
                'username'       => $request->instagram_username,
                'is_secure_call' => $request->secure_call,
            ]
        );

        $this->updateProfileCompletion($doctor);

        return response()->json([
            'success' => true,
            'message' => 'اطلاعات پیام‌رسان‌ها با موفقیت به‌روزرسانی شد.',
        ]);
    }

    public function updateStaticPassword(Request $request)
    {
        $key = 'update_static_password_' . $request->ip();
        $response = $this->checkRateLimit($key);
        if ($response) {
            return $response;
        }

        $validator = Validator::make($request->all(), [
            'static_password_enabled' => 'required|boolean',
            'password' => 'required_if:static_password_enabled,true|string|min:6|confirmed',
        ], [
            'static_password_enabled.required' => 'وضعیت رمز عبور ثابت الزامی است.',
            'static_password_enabled.boolean' => 'وضعیت رمز عبور ثابت باید یکمقدار بولین باشد.',
            'password.required_if' => 'رمز عبور الزامی است.',
            'password.string' => 'رمز عبور باید یک رشته باشد.',
            'password.min' => 'رمز عبور باید حداقل ۶ کاراکتر باشد.',
            'password.confirmed' => 'تکرار رمز عبور با رمز عبور اصلی مطابقت ندارد.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'لطفاً خطاهای فرم را بررسی کنید.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $doctor = $this->getAuthenticatedDoctor();
            $doctor->static_password_enabled = $request->static_password_enabled;
            if ($request->static_password_enabled) {
                $doctor->password = Hash::make($request->password);
            } else {
                $doctor->password = null;
            }
            $doctor->save();

            return response()->json([
                'success' => true,
                'message' => 'تنظیمات رمز عبور ثابت با موفقیت به‌روزرسانی شد.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطای سرور در به‌روزرسانی رمز عبور ثابت.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function getCities(Request $request)
    {
        $provinceId = $request->query('province_id');
        if (!$provinceId) {
            return response()->json(['success' => false, 'message' => 'شناسه استان الزامی است'], 400);
        }

        $cities = Zone::where('parent_id', $provinceId)->get(['id', 'name']);
        return response()->json([
            'success' => true,
            'cities' => $cities
        ]);
    }
    public function updateTwoFactorAuth(Request $request)
    {
        $key = 'update_two_factor_auth_' . $request->ip();
        $response = $this->checkRateLimit($key);
        if ($response) {
            return $response;
        }

        $request->validate([
            'two_factor_secret_enabled' => 'required|in:0,1,true,false',
        ], [
            'two_factor_secret_enabled.required' => 'وضعیت احراز هویت دو مرحله‌ای الزامی است.',
            'two_factor_secret_enabled.in' => 'وضعیت احراز هویت دو مرحله‌ای باید یکی از مقادیر ۰، ۱، true یا false باشد.',
        ]);


        try {
            $doctor = $this->getAuthenticatedDoctor();
            $doctor->two_factor_secret_enabled = $request->two_factor_secret_enabled;
            $doctor->two_factor_secret = null; // اطمینان از خالی بودن فیلد رمز
            $doctor->save();

            return response()->json([
                'success' => true,
                'message' => 'تنظیمات گذرواژه دو مرحله‌ای با موفقیت به‌روزرسانی شد.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در به‌روزرسانی تنظیمات گذرواژه دو مرحله‌ای',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function niceId()
    {
        return view("mc.panel.profile.edit-niceId");
    }

    public function update_profile(UpdateProfileRequest $request)
    {
        $key = 'update_profile_' . $request->ip();
        $response = $this->checkRateLimit($key);
        if ($response) {
            return $response;
        }

        try {
            $doctor = $this->getAuthenticatedDoctor();
            $doctor->update([
                'first_name'     => $request->first_name,
                'last_name'      => $request->last_name,
                'national_code'  => $request->national_code,
                'license_number' => $request->license_number,
                'bio'            => $request->description,
                'province_id'    => $request->province_id,
                'city_id'        => $request->city_id,
            ]);

            $this->updateProfileCompletion($doctor);

            return response()->json([
                'success' => true,
                'message' => 'پروفایل با موفقیت به‌روزرسانی شد.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطای سرور در به‌روزرسانی پروفایل',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function checkProfileCompleteness()
    {
        try {
            $doctor             = $this->getAuthenticatedDoctor();
            $incompleteSections = $doctor->getIncompleteProfileSections();
            return response()->json([
                'success'             => true,
                'profile_completed'   => $doctor->profile_completed,
                'incomplete_sections' => $incompleteSections,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در بررسی وضعیت پروفایل',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function debugProfileCompletion()
    {
        try {
            $doctor = $this->getAuthenticatedDoctor();

            // بررسی پیام‌رسان‌های موجود
            $messengers = $doctor->messengers;
            $messengerInfo = [];

            foreach ($messengers as $messenger) {
                $messengerInfo[] = [
                    'id' => $messenger->id,
                    'type' => $messenger->messenger_type,
                    'phone' => $messenger->phone_number,
                    'username' => $messenger->username,
                    'has_data' => !empty($messenger->phone_number) || !empty($messenger->username)
                ];
            }

            // بررسی منطق تکمیل پروفایل
            $isComplete = $doctor->isProfileComplete();
            $incompleteSections = $doctor->getIncompleteProfileSections();

            return response()->json([
                'success' => true,
                'doctor_id' => $doctor->id,
                'profile_completed' => $doctor->profile_completed,
                'is_complete_logic' => $isComplete,
                'messengers' => $messengerInfo,
                'incomplete_sections' => $incompleteSections,
                'messengers_exists' => $doctor->messengers()->exists(),
                'messengers_contains_data' => $doctor->messengers->contains(function ($messenger) {
                    return $messenger->phone_number || $messenger->username;
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در بررسی وضعیت پروفایل',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function updateProfileCompletion(Doctor $doctor)
    {
        $doctor->profile_completed = $doctor->isProfileComplete();
        $doctor->save();
    }

    public function sendMobileOtp(Request $request)
    {
        $key      = 'sendMobileOtp_' . $request->ip();
        $response = $this->checkRateLimit($key, '3', '1');
        if ($response) {
            return $response;
        }

        // تبدیل اعداد فارسی به انگلیسی
        $mobile = $request->mobile;
        if (class_exists('App\\Helpers\\PersianNumber')) {
            $mobile = \App\Helpers\PersianNumber::convertToEnglish($mobile);
        } else {
            // تبدیل دستی اعداد فارسی به انگلیسی
            $persianNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            $englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            $mobile = str_replace($persianNumbers, $englishNumbers, $mobile);
        }

        $request->merge(['mobile' => $mobile]);

        $request->validate([
            'mobile' => [
                'required',
                'regex:/^(?!09{1}([0-9۰-۹])\1{8}$)09(?:01|02|03|12|13|14|15|16|18|19|20|21|22|30|33|35|36|38|39|90|91|92|93|94)[0-9۰-۹]{7}$/u',
                function ($attribute, $value, $fail) {
                    $existingDoctor = Doctor::where('mobile', $value)->first();
                    if ($existingDoctor) {
                        $fail('این شماره موبایل قبلاً ثبت شده است');
                    }
                },
            ],
        ], [
            'mobile.required' => 'شماره موبایل الزامی است',
            'mobile.regex'    => 'شماره موبایل نامعتبر است',
        ]);

        $doctor = $this->getAuthenticatedDoctor();
        return $this->sendOtp($doctor, $mobile);
    }

    private function sendOtp(Doctor $doctor, $newMobile)
    {
        $otpCode = rand(1000, 9999);
        $token   = Str::random(60);
        Otp::create([
            'token'     => $token,
            'doctor_id' => $doctor->id ?? $doctor->doctor_id,
            'otp_code'  => $otpCode,
            'login_id'  => $newMobile,
            'type'      => 0,
        ]);

        $messagesService = new MessageService(
            SmsService::create(100286, $newMobile, [$otpCode])
        );
        $messagesService->send();

        return response()->json(['token' => $token, 'otp_code' => $otpCode]);
    }

    public function mobileConfirm(Request $request, $token)
    {
        $key      = 'mobileConfirm' . $request->ip();
        $response = $this->checkRateLimit($key);
        if ($response) {
            return $response;
        }

        // Debug: نمایش داده‌های دریافتی
        Log::info('Mobile Confirm Request Data:', [
            'all_data' => $request->all(),
            'otp' => $request->otp,
            'mobile' => $request->mobile,
            'token' => $token
        ]);

        // تبدیل اعداد فارسی به انگلیسی
        $mobile = $request->mobile;
        if (class_exists('App\\Helpers\\PersianNumber')) {
            $mobile = \App\Helpers\PersianNumber::convertToEnglish($mobile);
        } else {
            // تبدیل دستی اعداد فارسی به انگلیسی
            $persianNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            $englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            $mobile = str_replace($persianNumbers, $englishNumbers, $mobile);
        }

        $request->merge(['mobile' => $mobile]);

        // تبدیل OTP آرایه به اعداد صحیح و تبدیل اعداد فارسی
        $otpArray = $request->otp;
        Log::info('Mobile Confirm Request Data:', [
            'all_data' => $request->all(),
            'otp' => $request->otp,
            'mobile' => $request->mobile,
            'token' => $token
        ]);

        if (!is_array($otpArray)) {
            Log::error('OTP is not an array:', ['otp' => $otpArray]);
            return response()->json([
                'success' => false,
                'message' => 'کد تأیید باید به صورت آرایه باشد.',
            ], 422);
        }

        if (count($otpArray) !== 4) {
            return response()->json([
                'success' => false,
                'message' => 'کد تأیید باید دقیقاً ۴ رقم باشد.',
            ], 422);
        }

        $convertedOtp = [];
        $persianNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        foreach ($otpArray as $index => $otpDigit) {
            // بررسی اینکه مقدار null یا خالی نباشد
            if ($otpDigit === null || $otpDigit === '') {
                Log::error('Empty or null OTP digit found:', ['index' => $index, 'digit' => $otpDigit]);
                return response()->json([
                    'success' => false,
                    'message' => 'تمام ارقام کد تأیید باید وارد شوند.',
                ], 422);
            }

            // تبدیل اعداد فارسی به انگلیسی
            $convertedDigit = str_replace($persianNumbers, $englishNumbers, (string)$otpDigit);

            // بررسی اینکه فقط اعداد باشد
            if (!is_numeric($convertedDigit)) {
                Log::error('Non-numeric OTP digit found:', ['index' => $index, 'original' => $otpDigit, 'converted' => $convertedDigit]);
                return response()->json([
                    'success' => false,
                    'message' => 'کد تأیید باید فقط شامل اعداد باشد.',
                ], 422);
            }

            // بررسی محدوده عدد (0-9)
            $digitValue = (int)$convertedDigit;
            if ($digitValue < 0 || $digitValue > 9) {
                Log::error('OTP digit out of range:', ['index' => $index, 'value' => $digitValue]);
                return response()->json([
                    'success' => false,
                    'message' => 'هر رقم کد تأیید باید بین ۰ تا ۹ باشد.',
                ], 422);
            }

            $convertedOtp[] = $digitValue;
        }

        $request->merge(['otp' => $convertedOtp]);
        Log::info('OTP Array after conversion:', ['converted_otp' => $convertedOtp]);

        $validator = Validator::make($request->all(), [
            'mobile' => [
                'required',
                'regex:/^(?!09{1}([0-9۰-۹])\1{8}$)09(?:01|02|03|12|13|14|15|16|18|19|20|21|22|30|33|35|36|38|39|90|91|92|93|94)[0-9۰-۹]{7}$/u',
                function ($attribute, $value, $fail) {
                    $existingDoctor = Doctor::where('mobile', $value)
                        ->where('id', '!=', Auth::guard('doctor')->id())
                        ->first();
                    if ($existingDoctor) {
                        $fail('این شماره موبایل قبلاً توسط پزشک دیگری ثبت شده است');
                    }
                },
            ],
        ], [
            'mobile.required'   => 'شماره موبایل الزامی است.',
            'mobile.regex'      => 'شماره موبایل نامعتبر است.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $otp = Otp::where('token', $token)
            ->where('used', 0)
            ->where('created_at', '>=', Carbon::now()->subMinutes(2))
            ->first();

        if (! $otp) {
            return response()->json([
                'success' => false,
                'message' => 'کد تأیید منقضی شده است',
            ], 422);
        }

        $otpCode = implode('', $request->otp);
        if ($otp->otp_code !== $otpCode) {
            return response()->json([
                'success' => false,
                'message' => 'کد تأیید صحیح نمی‌باشد',
            ], 422);
        }

        $currentDoctor = $this->getAuthenticatedDoctor();
        $updateResult  = $currentDoctor->update([
            'mobile' => $mobile,
        ]);

        if (! $updateResult) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در به‌روزرسانی شماره موبایل',
            ], 500);
        }

        $otp->update(['used' => 1]);

        return response()->json([
            'success' => true,
            'message' => 'شماره موبایل با موفقیت تغییر یافت',
            'mobile'  => $mobile,
        ]);
    }

    public function getCurrentSpecialtyName()
    {
        try {
            $doctor = $this->getAuthenticatedDoctor();

            // دریافت تخصص اصلی از جدول doctor_specialty
            $mainSpecialty = DoctorSpecialty::where('doctor_id', $doctor->id)->where('is_main', true)->first();

            if ($mainSpecialty) {
                // دریافت نام تخصص
                $specialty = Specialty::find($mainSpecialty->specialty_id);
                $specialtyName = $specialty ? $specialty->name : 'نامشخص';

                return response()->json([
                    'success' => true,
                    'specialty_name' => $specialtyName,
                    'specialty_title' => $mainSpecialty->specialty_title,
                    'academic_degree_id' => $mainSpecialty->academic_degree_id,
                    'specialty_id' => $mainSpecialty->specialty_id,
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'specialty_name' => 'نامشخص',
                    'specialty_title' => '',
                    'academic_degree_id' => null,
                    'specialty_id' => null,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت نام تخصص',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        //
    }

    // متدهای مربوط به سوالات متداول
    public function storeFaq(Request $request)
    {
        $doctor = $this->getAuthenticatedDoctor();

        $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'is_active' => 'boolean',
            'order' => 'integer|min:0',
        ], [
            'question.required' => 'فیلد سوال الزامی است.',
            'question.string' => 'سوال باید یک رشته متنی باشد.',
            'question.max' => 'سوال نمی‌تواند بیش از ۲۵۵ کاراکتر باشد.',
            'answer.required' => 'فیلد پاسخ الزامی است.',
            'answer.string' => 'پاسخ باید یک رشته متنی باشد.',
            'is_active.boolean' => 'وضعیت باید فعال یا غیرفعال باشد.',
            'order.integer' => 'ترتیب باید یک عدد صحیح باشد.',
            'order.min' => 'ترتیب نمی‌تواند کمتر از ۰ باشد.',
        ]);

        $faq = DoctorFaq::create([
            'doctor_id' => $doctor->id,
            'question' => $request->question,
            'answer' => $request->answer,
            'is_active' => $request->is_active ?? true,
            'order' => $request->order ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'سوال متداول با موفقیت ایجاد شد.',
            'faq' => $faq,
        ]);
    }

    public function updateFaq(Request $request, $id)
    {
        $doctor = $this->getAuthenticatedDoctor();

        $faq = DoctorFaq::where('doctor_id', $doctor->id)->findOrFail($id);

        $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'is_active' => 'boolean',
            'order' => 'integer|min:0',
        ], [
            'question.required' => 'فیلد سوال الزامی است.',
            'question.string' => 'سوال باید یک رشته متنی باشد.',
            'question.max' => 'سوال نمی‌تواند بیش از ۲۵۵ کاراکتر باشد.',
            'answer.required' => 'فیلد پاسخ الزامی است.',
            'answer.string' => 'پاسخ باید یک رشته متنی باشد.',
            'is_active.boolean' => 'وضعیت باید فعال یا غیرفعال باشد.',
            'order.integer' => 'ترتیب باید یک عدد صحیح باشد.',
            'order.min' => 'ترتیب نمی‌تواند کمتر از ۰ باشد.',
        ]);

        $faq->update([
            'question' => $request->question,
            'answer' => $request->answer,
            'is_active' => $request->is_active ?? $faq->is_active,
            'order' => $request->order ?? $faq->order,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'سوال متداول با موفقیت به‌روزرسانی شد.',
            'faq' => $faq->fresh(),
        ]);
    }

    public function deleteFaq($id)
    {
        $doctor = $this->getAuthenticatedDoctor();

        $faq = DoctorFaq::where('doctor_id', $doctor->id)->findOrFail($id);
        $faq->delete();

        return response()->json([
            'success' => true,
            'message' => 'سوال متداول با موفقیت حذف شد.',
        ]);
    }

    public function getFaq($id)
    {
        $doctor = $this->getAuthenticatedDoctor();

        $faq = DoctorFaq::where('doctor_id', $doctor->id)->findOrFail($id);

        return response()->json([
            'success' => true,
            'faq' => $faq,
        ]);
    }

    public function indexFaqs()
    {
        $doctor = $this->getAuthenticatedDoctor();

        $faqs = DoctorFaq::where('doctor_id', $doctor->id)
            ->orderBy('order', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'faqs' => $faqs,
        ]);
    }

    public function debugOtpValidation(Request $request)
    {
        // تست تبدیل OTP
        $testOtp = $request->input('otp', []);
        Log::info('Debug OTP Test:', [
            'original_otp' => $testOtp,
            'is_array' => is_array($testOtp),
            'count' => is_array($testOtp) ? count($testOtp) : 'not array'
        ]);

        if (is_array($testOtp)) {
            $convertedOtp = [];
            $persianNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
            $englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

            foreach ($testOtp as $index => $otpDigit) {
                $original = $otpDigit;
                $convertedDigit = str_replace($persianNumbers, $englishNumbers, (string)$otpDigit);
                $isNumeric = is_numeric($convertedDigit);
                $finalValue = $isNumeric ? (int)$convertedDigit : null;

                $convertedOtp[] = [
                    'index' => $index,
                    'original' => $original,
                    'converted' => $convertedDigit,
                    'is_numeric' => $isNumeric,
                    'final_value' => $finalValue
                ];
            }

            return response()->json([
                'success' => true,
                'debug_info' => [
                    'original_otp' => $testOtp,
                    'conversion_details' => $convertedOtp,
                    'all_numeric' => collect($convertedOtp)->every('is_numeric')
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'OTP باید آرایه باشد'
        ]);
    }
}

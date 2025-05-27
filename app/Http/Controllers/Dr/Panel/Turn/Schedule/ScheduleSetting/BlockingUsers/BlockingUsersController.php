<?php

namespace App\Http\Controllers\Dr\Panel\Turn\Schedule\ScheduleSetting\BlockingUsers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Doctor;
use App\Models\SmsTemplate;
use Illuminate\Support\Str;
use App\Models\UserBlocking;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendSmsNotificationJob;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Dr\Controller;
use Modules\SendOtp\App\Http\Services\MessageService;
use Modules\SendOtp\App\Http\Services\SMS\SmsService;

class BlockingUsersController extends Controller
{
    public function index(Request $request)
    {
        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        if (!$doctor) {
            return redirect()->route('dr.auth.login-register-form')->with('error', 'ابتدا وارد شوید.');
        }
        $doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : $doctor->doctor_id;
        $clinicId = ($request->input('selectedClinicId') === 'default') ? null : $request->input('selectedClinicId');
        $search = $request->input('search');

        $query = UserBlocking::with('user')
            ->where('doctor_id', $doctorId)
            ->where('clinic_id', $clinicId);

        // اعمال جستجو
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"])
                        ->orWhere('mobile', 'LIKE', "%$search%");
                })->orWhere('reason', 'LIKE', "%$search%");
            });
        }

        $blockedUsers = $query->get();
        $messages = SmsTemplate::with('user')->latest()->get();

        if ($request->ajax()) {
            return response()->json(['blockedUsers' => $blockedUsers]);
        }

        return view('dr.panel.turn.schedule.scheduleSetting.blocking_users.index', compact('blockedUsers', 'messages'));
    }

    private function processDate($date, $fieldName)
    {
        if (empty($date)) {
            return null;
        }

        // چک کردن فرمت میلادی (YYYY-MM-DD)
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            try {
                return Carbon::createFromFormat('Y-m-d', $date);
            } catch (\Exception $e) {
                throw new \Exception("فرمت تاریخ $fieldName معتبر نیست: $date");
            }
        }

        // چک کردن فرمت جلالی (YYYY/MM/DD یا YYYY-MM-DD)
        if (preg_match('/^14\d{2}[-\/]\d{2}[-\/]\d{2}$/', $date)) {
            try {
                $date = str_replace('/', '-', $date); // تبدیل جداکننده‌ها
                return Jalalian::fromFormat('Y-m-d', $date)->toCarbon();
            } catch (\Exception $e) {
                throw new \Exception("فرمت تاریخ جلالی $fieldName معتبر نیست: $date");
            }
        }

        throw new \Exception("فرمت تاریخ $fieldName ناشناخته است: $date");
    }

    public function store(Request $request)
    {
        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;

        $messages = [
            'mobile.required' => 'لطفاً شماره موبایل را وارد کنید.',
            'mobile.exists' => 'شماره موبایل واردشده در سیستم ثبت نشده است.',
            'blocked_at.required' => 'لطفاً تاریخ شروع مسدودیت را وارد کنید.',
            'blocked_at.regex' => 'تاریخ شروع مسدودیت باید به فرمت YYYY-MM-DD یا YYYY/MM/DD باشد.',
            'unblocked_at.regex' => 'تاریخ پایان مسدودیت باید به فرمت YYYY-MM-DD یا YYYY/MM/DD باشد.',
            'unblocked_at.after' => 'تاریخ پایان مسدودیت باید بعد از تاریخ شروع باشد.',
            'reason.max' => 'دلیل مسدودیت نمی‌تواند بیشتر از 255 کاراکتر باشد.',
        ];

        try {
            $validated = $request->validate([
                'mobile' => 'required|exists:users,mobile',
                'blocked_at' => ['required', 'regex:/^(\d{4}-\d{2}-\d{2}|14\d{2}[-\/]\d{2}[-\/]\d{2})$/'],
                'unblocked_at' => ['nullable', 'regex:/^(\d{4}-\d{2}-\d{2}|14\d{2}[-\/]\d{2}[-\/]\d{2})$/', 'after:blocked_at'],
                'reason' => 'nullable|string|max:255',
                'selectedClinicId' => 'nullable|string',
            ], $messages);

            $clinicId = $request->input('selectedClinicId') === 'default' ? null : $request->input('selectedClinicId');
            $user = User::where('mobile', $validated['mobile'])->first();

            // پردازش تاریخ‌ها
            $blockedAt = $this->processDate($validated['blocked_at'], 'شروع مسدودیت');
            $unblockedAt = $this->processDate($validated['unblocked_at'], 'پایان مسدودیت');

            $isBlocked = UserBlocking::where('user_id', $user->id)
                ->where('doctor_id', $doctorId)
                ->where('clinic_id', $clinicId)
                ->where('status', 1)
                ->exists();

            if ($isBlocked) {
                return response()->json(['success' => false, 'message' => 'این کاربر قبلاً در این کلینیک مسدود شده است.'], 422);
            }

            $blockingUser = UserBlocking::create([
                'user_id' => $user->id,
                'doctor_id' => $doctorId,
                'clinic_id' => $clinicId,
                'blocked_at' => $blockedAt,
                'unblocked_at' => $unblockedAt,
                'reason' => $validated['reason'] ?? null,
                'status' => 1,
            ]);

            $doctor = Doctor::find($doctorId);
            $doctorName = $doctor->first_name . ' ' . $doctor->last_name;
            $message = "کاربر گرامی، شما توسط پزشک {$doctorName} در کلینیک انتخابی مسدود شده‌اید. جهت اطلاعات بیشتر تماس بگیرید.";

            $activeGateway = \Modules\SendOtp\App\Models\SmsGateway::where('is_active', true)->first();
            $gatewayName = $activeGateway ? $activeGateway->name : 'pishgamrayan';
            $templateId = ($gatewayName === 'pishgamrayan') ? 100254 : null;

            SendSmsNotificationJob::dispatch(
                $message,
                [$user->mobile],
                $templateId,
                [$doctorName]
            )->delay(now()->addSeconds(5));

            return response()->json([
                'success' => true,
                'message' => 'کاربر با موفقیت مسدود شد',
                'blocking_user' => $blockingUser->load('user'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ثبت کاربر مسدود.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function storeMultiple(Request $request)
    {
        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
        $clinicId = ($request->input('selectedClinicId') === 'default') ? null : $request->input('selectedClinicId');

        $messages = [
            'mobiles.required' => 'لطفاً حداقل یک شماره موبایل وارد کنید.',
            'mobiles.*.exists' => 'یکی از شماره‌های موبایل واردشده در سیستم ثبت نشده است.',
            'blocked_at.required' => 'لطفاً تاریخ شروع مسدودیت را وارد کنید.',
            'blocked_at.regex' => 'تاریخ شروع مسدودیت باید به فرمت YYYY-MM-DD یا YYYY/MM/DD باشد.',
            'unblocked_at.regex' => 'تاریخ پایان مسدودیت باید به فرمت YYYY-MM-DD یا YYYY/MM/DD باشد.',
            'unblocked_at.after' => 'تاریخ پایان مسدودیت باید بعد از تاریخ شروع باشد.',
            'reason.max' => 'دلیل مسدودیت نمی‌تواند بیشتر از 255 کاراکتر باشد.',
        ];

        try {
            $validated = $request->validate([
                'mobiles' => 'required|array',
                'mobiles.*' => 'exists:users,mobile',
                'blocked_at' => ['required', 'regex:/^(\d{4}-\d{2}-\d{2}|14\d{2}[-\/]\d{2}[-\/]\d{2})$/'],
                'unblocked_at' => ['nullable', 'regex:/^(\d{4}-\d{2}-\d{2}|14\d{2}[-\/]\d{2}[-\/]\d{2})$/', 'after:blocked_at'],
                'reason' => 'nullable|string|max:255',
            ], $messages);

            // پردازش تاریخ‌ها
            $blockedAt = $this->processDate($validated['blocked_at'], 'شروع مسدودیت');
            // بررسی Eager Loading برای بهبود عملکرد
            $unblockedAt = isset($validated['unblocked_at']) && !empty($validated['unblocked_at'])
                ? $this->processDate($validated['unblocked_at'], 'پایان مسدودیت')
                : null;

            $blockedUsers = [];
            $alreadyBlocked = [];
            $recipients = [];

            foreach ($validated['mobiles'] as $mobile) {
                $user = User::where('mobile', $mobile)->first();
                if (!$user) {
                    continue;
                }

                $isBlocked = UserBlocking::where('user_id', $user->id)
                    ->where('doctor_id', $doctorId)
                    ->where('clinic_id', $clinicId)
                    ->where('status', 1)
                    ->exists();

                if ($isBlocked) {
                    $alreadyBlocked[] = $mobile;
                    continue;
                }

                $blockingUser = UserBlocking::create([
                    'user_id' => $user->id,
                    'doctor_id' => $doctorId,
                    'clinic_id' => $clinicId,
                    'blocked_at' => $blockedAt,
                    'unblocked_at' => $unblockedAt,
                    'reason' => $validated['reason'] ?? null,
                    'status' => 1,
                ]);

                $blockedUsers[] = $blockingUser;
                $recipients[] = $mobile;
            }

            if (empty($blockedUsers) && !empty($alreadyBlocked)) {
                return response()->json(['success' => false, 'message' => 'کاربران انتخاب‌شده قبلاً مسدود شده‌اند.'], 422);
            }

            if (empty($blockedUsers)) {
                return response()->json(['success' => false, 'message' => 'هیچ کاربری برای مسدود کردن پیدا نشد.'], 422);
            }

            if (!empty($recipients)) {
                $doctor = Doctor::find($doctorId);
                $doctorName = $doctor->first_name . ' ' . $doctor->last_name;
                $message = "کاربر گرامی، شما توسط پزشک {$doctorName} در کلینیک انتخابی مسدود شده‌اید. جهت اطلاعات بیشتر تماس بگیرید.";

                $activeGateway = \Modules\SendOtp\App\Models\SmsGateway::where('is_active', true)->first();
                $gatewayName = $activeGateway ? $activeGateway->name : 'pishgamrayan';
                $templateId = ($gatewayName === 'pishgamrayan') ? 100254 : null;

                SendSmsNotificationJob::dispatch(
                    $message,
                    $recipients,
                    $templateId,
                    [$doctorName]
                )->delay(now()->addSeconds(5));
            }

            return response()->json([
                'success' => true,
                'message' => 'کاربران با موفقیت مسدود شدند.',
                'blocked_users' => $blockedUsers,
                'already_blocked' => $alreadyBlocked,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ثبت کاربران مسدود.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateStatus(Request $request)
    {
        try {
            $clinicId = ($request->input('selectedClinicId') === 'default') ? null : $request->input('selectedClinicId');

            $userBlocking = UserBlocking::where('id', $request->id)
                ->where('clinic_id', $clinicId)
                ->firstOrFail();

            $userBlocking->status = $request->status;
            $userBlocking->save();

            $user = $userBlocking->user;
            $doctor = $userBlocking->doctor;
            $doctorName = $doctor->first_name . ' ' . $doctor->last_name;

            if ($request->status == 1) {
                $message = "کاربر گرامی، شما توسط پزشک {$doctorName} در کلینیک انتخابی مسدود شده‌اید. جهت اطلاعات بیشتر تماس بگیرید.";
                $defaultTemplateId = 100254;
            } else {
                $message = "کاربر گرامی، شما توسط پزشک {$doctorName} از حالت مسدودی خارج شدید. اکنون دسترسی شما فعال است.";
                $defaultTemplateId = 100255;
            }

            $activeGateway = \Modules\SendOtp\App\Models\SmsGateway::where('is_active', true)->first();
            $gatewayName = $activeGateway ? $activeGateway->name : 'pishgamrayan';
            $templateId = ($gatewayName === 'pishgamrayan') ? $defaultTemplateId : null;

            SendSmsNotificationJob::dispatch(
                $message,
                [$user->mobile],
                $templateId,
                [$doctorName]
            )->delay(now()->addSeconds(5));

            SmsTemplate::create([
                'doctor_id' => $doctor->id,
                'clinic_id' => $clinicId,
                'user_id' => $user->id,
                'identifier' => Str::random(11),
                'title' => $request->status == 1 ? 'مسدودی کاربر' : 'رفع مسدودی',
                'content' => $message,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'وضعیت با موفقیت به‌روزرسانی شد .',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در به‌روزرسانی وضعیت کاربر.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function sendMessage(Request $request)
    {
        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
        $clinicId = ($request->input('selectedClinicId') === 'default') ? null : $request->input('selectedClinicId');

        // تعریف پیام‌های خطای فارسی
        $messages = [
            'title.required' => 'لطفاً عنوان پیام را وارد کنید.',
            'title.string' => 'عنوان پیام باید یک متن معتبر باشد.',
            'title.max' => 'عنوان پیام نمی‌تواند بیش از ۲۵۵ کاراکتر باشد.',
            'content.required' => 'لطفاً متن پیام را وارد کنید.',
            'content.string' => 'متن پیام باید یک متن معتبر باشد.',
            'content.max' => 'متن پیام نمی‌تواند بیش از ۱۰۰۰ کاراکتر باشد.',
            'recipient_type.required' => 'لطفاً نوع گیرنده را انتخاب کنید.',
            'recipient_type.in' => 'نوع گیرنده انتخاب‌شده معتبر نیست.',
            'specific_recipient.required_if' => 'لطفاً شماره موبایل گیرنده را وارد کنید.',
            'specific_recipient.exists' => 'شماره موبایل وارد‌شده در سیستم ثبت نشده یا نوبت فعالی با شما ندارد.',
        ];

        try {
            // تعریف قوانین اعتبارسنجی
            $rules = [
                'title' => 'required|string|max:255',
                'content' => 'required|string|max:1000',
                'recipient_type' => 'required|in:all,blocked,specific',
            ];

            // اضافه کردن قانون برای specific_recipient در صورت انتخاب نوع گیرنده 'specific'
            if ($request->input('recipient_type') === 'specific') {
                $rules['specific_recipient'] = [
                    'required',
                    Rule::exists('users', 'mobile')->where(function ($query) use ($doctorId) {
                        $query->whereIn('id', DB::table('appointments')->where('doctor_id', $doctorId)->pluck('patient_id'));
                    }),
                ];
            }

            // انجام اعتبارسنجی
            $validated = $request->validate($rules, $messages);

            // ادامه منطق برنامه...
            $recipients = [];
            $userId = null;

            if ($validated['recipient_type'] === 'all') {
                $recipients = DB::table('appointments')
                    ->where('doctor_id', $doctorId)
                    ->join('users', 'appointments.patient_id', '=', 'users.id')
                    ->distinct()
                    ->pluck('users.mobile')
                    ->toArray();
                if (empty($recipients)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'هیچ کاربری با شما نوبت ثبت نکرده است.',
                    ], 422);
                }
            } elseif ($validated['recipient_type'] === 'blocked') {
                $recipients = UserBlocking::where('user_blockings.doctor_id', $doctorId)
                    ->where('user_blockings.clinic_id', $clinicId)
                    ->where('user_blockings.status', 1)
                    ->join('users', 'user_blockings.user_id', '=', 'users.id')
                    ->pluck('users.mobile')
                    ->toArray();
                if (empty($recipients)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'هیچ کاربر مسدودی یافت نشد.',
                    ], 422);
                }
            } elseif ($validated['recipient_type'] === 'specific') {
                $user = User::where('mobile', $validated['specific_recipient'])->first();
                $recipients[] = $validated['specific_recipient'];
                $userId = $user->id;
            }

            // ذخیره پیام در SmsTemplate
            $smsTemplate = SmsTemplate::create([
                'doctor_id' => $doctorId,
                'user_id' => $userId,
                'title' => $validated['title'],
                'content' => $validated['content'],
                'type' => 'manual',
                'recipient_type' => $validated['recipient_type'],
                'identifier' => uniqid(),
            ]);

            // ارسال پیامک
            $doctor = Doctor::find($doctorId);
            $doctorName = $doctor->first_name . ' ' . $doctor->last_name;

            $activeGateway = \Modules\SendOtp\App\Models\SmsGateway::where('is_active', true)->first();
            $gatewayName = $activeGateway ? $activeGateway->name : 'pishgamrayan';
            $templateId = ($gatewayName === 'pishgamrayan') ? null : null;

            SendSmsNotificationJob::dispatch(
                $validated['content'],
                $recipients,
                $templateId,
                [$doctorName]
            )->delay(now()->addSeconds(5));

            return response()->json([
                'success' => true,
                'message' => 'پیام با موفقیت در صف ارسال قرار گرفت.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('خطا در اعتبارسنجی هنگام ارسال پیام', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'خطا در اطلاعات ارسالی!',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('خطا در ارسال پیام', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'خطایی در ارسال پیام رخ داد. لطفاً دوباره تلاش کنید.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getMessages()
    {
        $messages = SmsTemplate::with('user')->latest()->get();
        return response()->json($messages);
    }

    public function deleteMessage(Request $request)
    {
        try {
            $messageIds = $request->input('message_ids', []);
            if (empty($messageIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'هیچ پیامی برای حذف انتخاب نشده است.',
                ], 422);
            }

            SmsTemplate::whereIn('id', $messageIds)->delete();

            return response()->json([
                'success' => true,
                'message' => 'پیام‌ها با موفقیت حذف شدند.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف پیام‌ها!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id, Request $request)
    {
        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
        $clinicId = ($request->input('selectedClinicId') === 'default') ? null : $request->input('selectedClinicId');

        try {
            $userBlocking = UserBlocking::where('id', $id)
                ->where('doctor_id', $doctorId)
                ->where('clinic_id', $clinicId)
                ->firstOrFail();

            // بررسی درخواست برای تغییر وضعیت (فقط برای کاربران مسدود)
            if ($request->input('update_status') && $userBlocking->status == 1) {
                $userBlocking->status = 0; // تغییر به آزاد
                $userBlocking->save();

                // ارسال پیامک به کاربر
                $user = $userBlocking->user;
                $doctor = $userBlocking->doctor;
                $doctorName = $doctor->first_name . ' ' . $doctor->last_name;

                $message = "کاربر گرامی، شما توسط پزشک {$doctorName} از حالت مسدودی خارج شدید. اکنون دسترسی شما فعال است.";
                $defaultTemplateId = 100255;

                $activeGateway = \Modules\SendOtp\App\Models\SmsGateway::where('is_active', true)->first();
                $gatewayName = $activeGateway ? $activeGateway->name : 'pishgamrayan';
                $templateId = ($gatewayName === 'pishgamrayan') ? $defaultTemplateId : null;

                SendSmsNotificationJob::dispatch(
                    $message,
                    [$user->mobile],
                    $templateId,
                    [$doctorName]
                )->delay(now()->addSeconds(5));

                // ثبت پیام در SmsTemplate
                SmsTemplate::create([
                    'doctor_id' => $doctor->id,
                    'clinic_id' => $clinicId,
                    'user_id' => $user->id,
                    'identifier' => Str::random(11),
                    'title' => 'رفع مسدودی',
                    'content' => $message,
                ]);
            }

            // حذف رکورد
            $userBlocking->delete();

            return response()->json([
                'success' => true,
                'message' => 'کاربر با موفقیت از لیست مسدودی حذف شد' . ($request->input('update_status') ? ' و وضعیت به آزاد تغییر کرد.' : '.'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف کاربر از لیست مسدودی!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

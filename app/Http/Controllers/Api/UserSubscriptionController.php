<?php

namespace App\Http\Controllers\Api;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\UserSubscription;
use Illuminate\Http\JsonResponse;
use App\Models\UserAppointmentFee;
use App\Models\UserMembershipPlan;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Payment\Services\PaymentService;

class UserSubscriptionController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Get user subscription status and payment details.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubscriptionDetails(): JsonResponse
    {
        $user = Auth::user();

        $activeSubscription = UserSubscription::with('plan')
            ->where('subscribable_id', $user->id)
            ->where('subscribable_type', get_class($user))
            ->where('status', true)
            ->where('end_date', '>=', now()->toDateString())
            ->where('remaining_appointments', '>', 0)
            ->first();

        $appointmentFee = UserAppointmentFee::where('status', true)->first();

        if ($activeSubscription) {
            return response()->json([
                'has_subscription' => true,
                'subscription' => [
                    'plan_name' => $activeSubscription->plan->name,
                    'remaining_appointments' => $activeSubscription->remaining_appointments,
                    'end_date' => $activeSubscription->end_date,
                ],
                'appointment_fee' => $appointmentFee,
            ]);
        }

        $membershipPlans = UserMembershipPlan::where('status', true)->get();

        return response()->json([
            'has_subscription' => false,
            'available_plans' => $membershipPlans,
            'appointment_fee' => $appointmentFee,
        ]);
    }

    /**
     * Purchase a subscription plan.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function purchaseSubscription(Request $request): JsonResponse
    {
        $request->validate([
            'plan_id' => 'required|exists:user_membership_plans,id',
        ]);

        $user = Auth::user();

        // جلوگیری از خرید مجدد اشتراک فعال
        $activeSubscription = UserSubscription::where('subscribable_id', $user->id)
            ->where('subscribable_type', get_class($user))
            ->where('status', true)
            ->where('end_date', '>=', now()->toDateString())
            ->where('remaining_appointments', '>', 0)
            ->first();
        if ($activeSubscription) {
            return response()->json([
                'success' => false,
                'message' => 'شما هم‌اکنون یک اشتراک فعال دارید و امکان خرید مجدد وجود ندارد.',
                'subscription' => [
                    'plan_name' => $activeSubscription->plan->name,
                    'remaining_appointments' => $activeSubscription->remaining_appointments,
                    'end_date' => $activeSubscription->end_date,
                ],
            ], 400);
        }

        $plan = UserMembershipPlan::find($request->plan_id);

        if (!$plan || !$plan->status) {
            return response()->json(['message' => 'طرح اشتراک معتبر نیست.'], 404);
        }

        $amount = $plan->final_price ?? $plan->price;

        $meta = [
            'type' => 'subscription_purchase',
            'plan_id' => $plan->id,
        ];
        if ($user instanceof \App\Models\Doctor) {
            $meta['doctor_id'] = $user->id;
        } elseif ($user instanceof \App\Models\Secretary) {
            $meta['secretary_id'] = $user->id;
        } else {
            $meta['user_id'] = $user->id;
        }

        $successRedirect = route('api.v2.subscriptions.payment.callback');
        $errorRedirect = route('api.v2.subscriptions.payment.callback');

        try {
            $paymentResponse = $this->paymentService->pay($amount, null, $meta, $successRedirect, $errorRedirect);

            if ($paymentResponse instanceof \Shetabit\Multipay\RedirectionForm) {
                return response()->json([
                    'payment_url' => $paymentResponse->getAction(),
                ]);
            }

            if ($paymentResponse instanceof \Illuminate\Http\RedirectResponse) {
                return response()->json([
                    'payment_url' => $paymentResponse->getTargetUrl(),
                ]);
            }

            if (is_array($paymentResponse) && isset($paymentResponse['payment_url'])) {
                return response()->json([
                    'payment_url' => $paymentResponse['payment_url'],
                ]);
            }

            return response()->json(['message' => 'خطا در ایجاد لینک پرداخت.'], 500);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error($e);
            return response()->json(['message' => 'خطای سرور: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handle payment callback.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function paymentCallback(Request $request)
    {
        $authority = $request->input('Authority');
        $status = $request->input('Status');

        if ($status !== 'OK' || !$authority) {
            return redirect()->away(config('app.frontend_url') . '/payment/error?message=' . urlencode('تراکنش ناموفق بود یا توسط شما لغو شد.'));
        }

        // مرحله مهم: verify
        $verifiedTransaction = app(\Modules\Payment\Services\PaymentService::class)->verify();
        if (!$verifiedTransaction || $verifiedTransaction->status !== 'paid') {
            Log::warning('Payment callback received for a non-successful transaction after verify.', ['transaction_id' => $authority]);
            return redirect()->away(config('app.frontend_url') . '/payment/error?message=' . urlencode('تراکنش یافت نشد یا موفقیت آمیز نبود.'));
        }
        $transaction = $verifiedTransaction;

        try {
            // Check if subscription was already created for this transaction to prevent duplicates
            $existingSubscription = UserSubscription::where('transaction_id', $transaction->id)->first();
            if ($existingSubscription) {
                return redirect()->away(config('app.frontend_url') . '/payment/success?message=' . urlencode('اشتراک شما قبلا با موفقیت فعال شده است.'));
            }

            $meta = json_decode($transaction->meta, true);
            $plan = UserMembershipPlan::find($meta['plan_id']);

            // تشخیص نوع کاربر برای polymorphic
            $mobile = null;
            $subscribable = null;
            if (isset($meta['user_id'])) {
                $subscribable = \App\Models\User::find($meta['user_id']);
            } elseif (isset($meta['doctor_id'])) {
                $subscribable = \App\Models\Doctor::find($meta['doctor_id']);
            } elseif (isset($meta['secretary_id'])) {
                $subscribable = \App\Models\Secretary::find($meta['secretary_id']);
            }
            if (!$subscribable) {
                Log::error('Subscribable not found for subscription', ['meta' => $meta]);
                return redirect()->away(config('app.frontend_url') . '/payment/error?message=' . urlencode('کاربر یا پزشک یا منشی یافت نشد.'));
            }

            Log::info('Before create subscription', [
                'subscribable_id' => $subscribable->id,
                'subscribable_type' => get_class($subscribable),
                'plan_id' => $meta['plan_id'],
                'transaction_id' => $transaction->id,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays($plan->duration_days)->toDateString(),
                'remaining_appointments' => $plan->appointment_count,
                'status' => true,
                'description' => 'transaction_id from gateway: ' . ($transaction->transaction_id ?? 'null'),
            ]);

            try {
                $subscription = UserSubscription::create([
                    'subscribable_id' => $subscribable->id,
                    'subscribable_type' => get_class($subscribable),
                    'plan_id' => $meta['plan_id'],
                    'transaction_id' => $transaction->id,
                    'start_date' => now()->toDateString(),
                    'end_date' => now()->addDays($plan->duration_days)->toDateString(),
                    'remaining_appointments' => $plan->appointment_count,
                    'status' => true,
                    'description' => 'transaction_id from gateway: ' . ($transaction->transaction_id ?? 'null'),
                ]);
                Log::info('Subscription created successfully', ['subscription' => $subscription]);
            } catch (\Exception $e) {
                Log::error('Failed to create subscription', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'data' => [
                        'subscribable_id' => $subscribable->id,
                        'subscribable_type' => get_class($subscribable),
                        'plan_id' => $meta['plan_id'],
                        'transaction_id' => $transaction->id,
                        'start_date' => now()->toDateString(),
                        'end_date' => now()->addDays($plan->duration_days)->toDateString(),
                        'remaining_appointments' => $plan->appointment_count,
                        'status' => true,
                        'description' => 'transaction_id from gateway: ' . ($transaction->transaction_id ?? 'null'),
                    ]
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'خطا در ثبت اشتراک. لطفا با پشتیبانی تماس بگیرید.',
                    'authority' => $transaction->transaction_id,
                ], 500);
            }

            // خروجی جیسون با authority و فورس json
            return response()->json([
                'success' => true,
                'message' => 'اشتراک شما با موفقیت فعال شد.',
                'authority' => $transaction->transaction_id,
                'subscription' => $subscription,
            ], 200, ['Content-Type' => 'application/json']);

        } catch (\Exception $e) {
            Log::error('Could not create subscription after payment.', [
                'authority' => $authority,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->away(config('app.frontend_url') . '/payment/error?message=' . urlencode('خطا در فعال‌سازی اشتراک. لطفا با پشتیبانی تماس بگیرید.'));
        }
    }
}

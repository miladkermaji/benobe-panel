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
            ->where('user_id', $user->id)
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
        $plan = UserMembershipPlan::find($request->plan_id);

        if (!$plan || !$plan->status) {
            return response()->json(['message' => 'طرح اشتراک معتبر نیست.'], 404);
        }

        $amount = $plan->final_price ?? $plan->price;

        $meta = [
            'type' => 'subscription_purchase',
            'user_id' => $user->id,
            'plan_id' => $plan->id,
        ];

        $successRedirect = config('app.frontend_url') . '/payment/success';
        $errorRedirect = config('app.frontend_url') . '/payment/error';

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
        $transactionId = $request->input('transaction_id') ?? $request->input('Authority');
        $status = $request->input('Status');

        if (!$transactionId) {
            return response()->json([
                'success' => false,
                'message' => 'پارامتر transaction_id یا Authority در درخواست وجود ندارد.',
                'data' => null,
            ], 400);
        }

        try {
            $transaction = Transaction::where('transaction_id', $transactionId)->first();
            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'تراکنش با این شناسه یافت نشد.',
                    'data' => null,
                ], 404);
            }

            $isPaymentSuccessful = $transaction->status === 'successful';
            $subscription = UserSubscription::where('transaction_id', $transaction->id)->first();

            if ($isPaymentSuccessful && !$subscription) {
                $meta = json_decode($transaction->meta, true);
                $plan = UserMembershipPlan::find($meta['plan_id'] ?? null);
                if ($plan) {
                    $subscription = UserSubscription::create([
                        'user_id' => $meta['user_id'],
                        'plan_id' => $meta['plan_id'],
                        'transaction_id' => $transaction->id,
                        'start_date' => now()->toDateString(),
                        'end_date' => now()->addDays($plan->duration_days)->toDateString(),
                        'remaining_appointments' => $plan->appointment_count,
                        'status' => true,
                    ]);
                }
            }

            $responseData = [
                'success' => $isPaymentSuccessful,
                'message' => $isPaymentSuccessful ? 'پرداخت و فعال‌سازی اشتراک با موفقیت انجام شد.' : 'پرداخت ناموفق بود.',
                'transaction' => [
                    'id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'gateway' => $transaction->gateway,
                    'status' => $transaction->status,
                    'transaction_id' => $transaction->transaction_id,
                    'meta' => json_decode($transaction->meta, true),
                    'created_at' => $transaction->created_at->toDateTimeString(),
                    'updated_at' => $transaction->updated_at->toDateTimeString(),
                ],
                'subscription' => $subscription,
            ];

            if ($request->wantsJson() || $request->isJson() || $request->ajax()) {
                return response()->json($responseData, $isPaymentSuccessful ? 200 : 400);
            }

            if ($isPaymentSuccessful) {
                return redirect()->away(config('app.frontend_url') . '/payment/success?transaction_id=' . $transactionId);
            } else {
                return redirect()->away(config('app.frontend_url') . '/payment/error?message=' . urlencode($responseData['message']));
            }
        } catch (\Exception $e) {
            Log::error('Subscription paymentCallback error', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطای سرور در پردازش نتیجه پرداخت',
                'data' => null,
            ], 500);
        }
    }
}

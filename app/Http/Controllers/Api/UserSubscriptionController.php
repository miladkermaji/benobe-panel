<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserAppointmentFee;
use App\Models\UserMembershipPlan;
use App\Models\UserSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        $successRedirect = route('api.v2.subscriptions.payment.callback');
        $errorRedirect = route('api.v2.subscriptions.payment.callback');

        try {
            $paymentResponse = $this->paymentService->pay($amount, null, $meta, $successRedirect, $errorRedirect);

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
        // For simplicity, we redirect to a frontend route with transaction status
        // The frontend will then display the appropriate message to the user.
        // A more robust implementation would verify the payment here and update the subscription.
        if ($request->has('transaction_id')) {
            // You should verify the transaction status with your payment service here
            // and then activate the subscription.
            return redirect()->away(config('app.frontend_url') . '/payment/success?transaction_id=' . $request->transaction_id);
        }

        return redirect()->away(config('app.frontend_url') . '/payment/error?message=' . $request->input('message', 'تراکنش ناموفق بود.'));
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Models\UserWallet;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\UserWalletTransaction;

class WalletController extends Controller
{
    /**
     * گرفتن اطلاعات کیف پول کاربر
     *
     * @response 200 {
     *   "status": "success",
     *   "data": {
     *     "balance": 1000000.50
     *   }
     * }
     * @response 401 {
     *   "status": "error",
     *   "message": "توکن نامعتبر است",
     *   "data": null
     * }
     * @response 404 {
     *   "status": "error",
     *   "message": "کیف پول یافت نشد",
     *   "data": null
     * }
     * @response 500 {
     *   "status": "error",
     *   "message": "خطای سرور",
     *   "data": null
     * }
     */
    public function getWallet(Request $request)
    {
        try {
            // گرفتن توکن از هدر یا کوکی
            $token = $request->bearerToken() ?: $request->cookie('auth_token');
            if (! $token) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'توکن ارائه نشده است',
                    'data'    => null,
                ], 401);
            }

            // احراز هویت کاربر
            try {

                $user = Auth::user();

                if (! $user) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'کاربر یافت نشد',
                        'data'    => null,
                    ], 401);
                }
            } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
                Log::error('GetWallet - JWT Error: ' . $e->getMessage());
                return response()->json([
                    'status'  => 'error',
                    'message' => 'توکن نامعتبر است: ' . $e->getMessage(),
                    'data'    => null,
                ], 401);
            }

            // گرفتن کیف پول کاربر
            $wallet = UserWallet::where('walletable_id', $user->id)
                ->where('walletable_type', 'App\\Models\\User')
                ->first();
            if (! $wallet) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'کیف پول یافت نشد',
                    'data'    => null,
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'balance' => $wallet->balance,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('GetWallet - Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور',
                'data'    => null,
            ], 500);
        }
    }

    /**
     * گرفتن لیست تراکنش‌های کیف پول کاربر
     *
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "amount": 500000,
     *       "status": "paid",
     *       "type": "deposit",
     *       "description": "شارژ کیف پول",
     *       "registered_at": "2025-03-16T10:00:00Z",
     *       "paid_at": "2025-03-16T10:05:00Z"
     *     },
     *     {
     *       "id": 2,
     *       "amount": 250000,
     *       "status": "paid",
     *       "type": "payment",
     *       "description": "پرداخت سفارش ORD-123456",
     *       "registered_at": "2025-03-16T11:00:00Z",
     *       "paid_at": "2025-03-16T11:05:00Z"
     *     }
     *   ]
     * }
     * @response 401 {
     *   "status": "error",
     *   "message": "توکن نامعتبر است",
     *   "data": null
     * }
     * @response 500 {
     *   "status": "error",
     *   "message": "خطای سرور",
     *   "data": null
     * }
     */
    public function getTransactions(Request $request)
    {
        try {
            // گرفتن توکن از هدر یا کوکی
            $token = $request->bearerToken() ?: $request->cookie('auth_token');
            if (! $token) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'توکن ارائه نشده است',
                    'data'    => null,
                ], 401);
            }

            // احراز هویت کاربر
            try {

                $user = Auth::user();

                if (! $user) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'کاربر یافت نشد',
                        'data'    => null,
                    ], 401);
                }
            } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
                Log::error('GetTransactions - JWT Error: ' . $e->getMessage());
                return response()->json([
                    'status'  => 'error',
                    'message' => 'توکن نامعتبر است: ' . $e->getMessage(),
                    'data'    => null,
                ], 401);
            }

            // گرفتن تراکنش‌های کاربر
            $transactions = UserWalletTransaction::where('walletable_id', $user->id)
                ->where('walletable_type', 'App\\Models\\User')
                ->select('id', 'walletable_id', 'amount', 'status', 'type', 'description', 'registered_at', 'paid_at')
                ->get();

            // فرمت کردن داده‌ها
            $formattedTransactions = $transactions->map(function ($transaction) {
                return [
                    'id'            => $transaction->id,
                    'amount'        => $transaction->amount,
                    'status'        => $transaction->status,
                    'type'          => $transaction->type,
                    'description'   => $transaction->description,
                    'registered_at' => $transaction->registered_at ? $transaction->registered_at->toIso8601String() : null,
                    'paid_at'       => $transaction->paid_at ? $transaction->paid_at->toIso8601String() : null,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data'   => $formattedTransactions,
            ], 200);

        } catch (\Exception $e) {
            Log::error('GetTransactions - Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور',
                'data'    => null,
            ], 500);
        }
    }
}

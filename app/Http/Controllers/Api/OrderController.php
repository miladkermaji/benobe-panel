<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * گرفتن لیست سفارشات کاربر
     *
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "order_code": "ORD-123456",
     *       "total_amount": 750000,
     *       "status": "completed",
     *       "order_date": "2025-03-16",
     *       "notes": "سفارش شامل تجهیزات پزشکی است.",
     *       "user": {
     *         "mobile": "09121234567",
     *         "name": "زهرا احمدی"
     *       }
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
    public function getOrders(Request $request)
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
                return response()->json([
                    'status'  => 'error',
                    'message' => 'توکن نامعتبر است: ' . $e->getMessage(),
                    'data'    => null,
                ], 401);
            }

            // گرفتن سفارشات کاربر
            $orders = Order::where('orderable_id', $user->id)
                ->where('orderable_type', 'App\\Models\\User')
                ->with([
                    'orderable' // morph relation
                ])
                ->select('id', 'orderable_id', 'orderable_type', 'order_code', 'total_amount', 'status', 'order_date', 'notes')
                ->get();

            // فرمت کردن داده‌ها
            $formattedOrders = $orders->map(function ($order) {
                $orderable = $order->orderable;
                $orderableData = null;
                if ($orderable) {
                    if ($order->orderable_type === 'App\\Models\\User') {
                        $orderableData = [
                            'mobile' => $orderable->mobile,
                            'name'   => trim(($orderable->first_name ?? '') . ' ' . ($orderable->last_name ?? '')),
                        ];
                    } elseif ($order->orderable_type === 'App\\Models\\Doctor') {
                        $orderableData = [
                            'mobile' => $orderable->mobile ?? null,
                            'name'   => trim(($orderable->first_name ?? '') . ' ' . ($orderable->last_name ?? '')),
                        ];
                    } // add more types if needed
                }
                return [
                    'id'           => $order->id,
                    'order_code'   => $order->order_code,
                    'total_amount' => $order->total_amount,
                    'status'       => $order->status,
                    'order_date'   => $order->order_date ? $order->order_date->format('Y-m-d') : null,
                    'notes'        => $order->notes,
                    'orderable'    => $orderableData,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data'   => $formattedOrders,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور',
                'data'    => null,
            ], 500);
        }
    }
}

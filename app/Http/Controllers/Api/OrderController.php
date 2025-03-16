<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

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
                $user = JWTAuth::setToken($token)->authenticate();
                if (! $user) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'کاربر یافت نشد',
                        'data'    => null,
                    ], 401);
                }
            } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
                Log::error('GetOrders - JWT Error: ' . $e->getMessage());
                return response()->json([
                    'status'  => 'error',
                    'message' => 'توکن نامعتبر است: ' . $e->getMessage(),
                    'data'    => null,
                ], 401);
            }

            // گرفتن سفارشات کاربر
            $orders = Order::where('user_id', $user->id)
                ->with([
                    'user' => function ($query) {
                        $query->select('id', 'mobile', 'first_name', 'last_name');
                    },
                ])
                ->select('id', 'user_id', 'order_code', 'total_amount', 'status', 'order_date', 'notes')
                ->get();

            // فرمت کردن داده‌ها
            $formattedOrders = $orders->map(function ($order) {
                return [
                    'id'           => $order->id,
                    'order_code'   => $order->order_code,
                    'total_amount' => $order->total_amount,
                    'status'       => $order->status,
                    'order_date'   => $order->order_date ? $order->order_date->format('Y-m-d') : null,
                    'notes'        => $order->notes,
                    'user'         => $order->user ? [
                        'mobile' => $order->user->mobile,
                        'name'   => $order->user->first_name . ' ' . $order->user->last_name,
                    ] : null,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data'   => $formattedOrders,
            ], 200);

        } catch (\Exception $e) {
            Log::error('GetOrders - Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور',
                'data'    => null,
            ], 500);
        }
    }
}

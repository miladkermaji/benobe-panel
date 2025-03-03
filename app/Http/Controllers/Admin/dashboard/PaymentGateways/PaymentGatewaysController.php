<?php

namespace App\Http\Controllers\Admin\dashboard\PaymentGateways;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Admin\Controller;

class PaymentGatewaysController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $gateways = DB::table('payment_gateways')->get();
        return view('admin.content.dashboard.payment_gateways.index', compact('gateways'));
    }
    public function edit($name)
    {
        $gateway = DB::table('payment_gateways')->where('name', $name)->first();
        return view('admin.content.dashboard.payment_gateways.edit', compact('gateway'));


    }
    public function toggle(Request $request)
    {
        $gatewayId = $request->input('gateway_id');
        $isActive = $request->boolean('is_active');

        if ($isActive) {
            // غیرفعال کردن همه درگاه‌ها قبل از فعال کردن درگاه جدید
            DB::table('payment_gateways')->update(['is_active' => false]);
        }

        // آپدیت درگاه انتخاب‌شده
        DB::table('payment_gateways')->where('id', $gatewayId)->update([
            'is_active' => $isActive,
            'updated_at' => now(),
        ]);

        // چک کردن اینکه آیا هیچ درگاهی فعال نیست
        $activeCount = DB::table('payment_gateways')->where('is_active', true)->count();
        if ($activeCount == 0) {
            // فعال کردن زرین‌پال به‌صورت پیش‌فرض
            DB::table('payment_gateways')->where('name', 'zarinpal')->update([
                'is_active' => true,
                'updated_at' => now(),
            ]);
            return response()->json([
                'success' => true,
                'is_active' => false, // برای درگاه فعلی که غیرفعال شده
                'default_activated' => 'zarinpal' // نشون می‌ده زرین‌پال فعال شده
            ]);
        }

        return response()->json([
            'success' => true,
            'is_active' => $isActive,
        ]);
    }
    public function update(Request $request, $name)
    {
        $isActive = $request->boolean('is_active');

        if ($isActive) {
            // غیرفعال کردن همه درگاه‌های دیگه
            DB::table('payment_gateways')->update(['is_active' => false]);
        }

        DB::table('payment_gateways')->where('name', $name)->update([
            'is_active' => $isActive,
            'settings' => json_encode($request->input('settings', [])),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.Dashboard.payment_gateways.index')->with('success', 'درگاه با موفقیت به‌روزرسانی شد.');
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
        //
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


    /**
     * Update the specified resource in storage.
     */


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

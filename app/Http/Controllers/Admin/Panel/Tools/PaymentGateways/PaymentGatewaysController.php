<?php

namespace App\Http\Controllers\Admin\Panel\Tools\PaymentGateways;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Admin\Controller;
use Illuminate\Support\Facades\Validator;

class PaymentGatewaysController extends Controller
{
 /**
  * Display a listing of the resource.
  */
 public function index()
 {
  $gateways = DB::table('payment_gateways')->get();
  return view('admin.panel.tools.payment_gateways.index', compact('gateways'));
 }

 /**
  * Show the form for editing the specified resource.
  */
 public function edit($name)
 {
  $gateway = DB::table('payment_gateways')->where('name', $name)->first();
  if (!$gateway) {
   return redirect()->route('admin.panel.tools.payment_gateways.index')->with('error', 'درگاه مورد نظر یافت نشد.');
  }
  return view('admin.panel.tools.payment_gateways.edit', compact('gateway'));
 }

 /**
  * Toggle the active status of a gateway via AJAX
  */
 public function toggle(Request $request)
 {
  $validator = Validator::make($request->all(), [
   'gateway_id' => 'required|integer|exists:payment_gateways,id',
   'is_active' => 'required|boolean',
  ], [
   'gateway_id.required' => 'شناسه درگاه الزامی است.',
   'gateway_id.integer' => 'شناسه درگاه باید عدد باشد.',
   'gateway_id.exists' => 'درگاه مورد نظر وجود ندارد.',
   'is_active.required' => 'وضعیت درگاه الزامی است.',
   'is_active.boolean' => 'وضعیت درگاه باید بله/خیر باشد.',
  ]);

  if ($validator->fails()) {
   return response()->json([
    'success' => false,
    'message' => $validator->errors()->first(),
   ], 422);
  }

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
    'is_active' => false,
    'default_activated' => 'zarinpal',
   ]);
  }

  return response()->json([
   'success' => true,
   'is_active' => $isActive,
  ]);
 }

 /**
  * Update the specified resource in storage.
  */
 public function update(Request $request, $name)
 {
  $validator = Validator::make($request->all(), [
   'title' => 'required|string|max:255',
   'is_active' => 'required|boolean',
   'settings' => 'required|json',
  ], [
   'title.required' => 'عنوان درگاه الزامی است.',
   'title.string' => 'عنوان درگاه باید متنی باشد.',
   'title.max' => 'عنوان درگاه نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
   'is_active.required' => 'وضعیت درگاه الزامی است.',
   'is_active.boolean' => 'وضعیت درگاه باید فعال یا غیرفعال باشد.',
   'settings.required' => 'تنظیمات درگاه الزامی است.',
   'settings.json' => 'تنظیمات باید به فرمت JSON معتبر باشد.',
  ]);

  if ($validator->fails()) {
   return response()->json([
    'success' => false,
    'message' => $validator->errors()->first(),
   ], 422);
  }

  $isActive = $request->boolean('is_active');

  if ($isActive) {
   // غیرفعال کردن همه درگاه‌های دیگه
   DB::table('payment_gateways')->update(['is_active' => false]);
  }

  $updated = DB::table('payment_gateways')->where('name', $name)->update([
   'title' => $request->input('title'),
   'is_active' => $isActive,
   'settings' => $request->input('settings'),
   'updated_at' => now(),
  ]);

  if ($updated) {
   return response()->json([
    'success' => true,
    'message' => 'درگاه با موفقیت به‌روزرسانی شد.',
   ]);
  } else {
   return response()->json([
    'success' => false,
    'message' => 'خطا در به‌روزرسانی درگاه. ممکن است درگاه وجود نداشته باشد.',
   ], 500);
  }
 }

 /**
  * Remove the specified resource from storage.
  */
 public function destroy($name)
 {
  $gateway = DB::table('payment_gateways')->where('name', $name)->first();

  if (!$gateway) {
   return redirect()->route('admin.panel.tools.payment_gateways.index')->with('error', 'درگاه مورد نظر یافت نشد.');
  }

  if ($gateway->is_active) {
   return redirect()->route('admin.panel.tools.payment_gateways.index')->with('error', 'نمی‌توانید درگاه فعال را حذف کنید. ابتدا آن را غیرفعال کنید.');
  }

  DB::table('payment_gateways')->where('name', $name)->delete();

  return redirect()->route('admin.panel.tools.payment_gateways.index')->with('success', 'درگاه با موفقیت حذف شد.');
 }
}
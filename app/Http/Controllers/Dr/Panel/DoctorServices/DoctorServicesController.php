<?php

namespace App\Http\Controllers\Dr\Panel\DoctorServices;

use Illuminate\Http\Request;
use App\Models\Dr\DoctorService;
use App\Http\Controllers\Dr\Controller;

class DoctorServicesController extends Controller
{
 public function index()
 {
  return view('dr.panel.dr-services.index');
 }

 public function create()
 {
  // دریافت تمامی خدمت‌ها جهت انتخاب به عنوان خدمت مادر
  $parentServices = DoctorService::all();
  return view('dr.panel.dr-services.create', compact('parentServices'));
 }

 public function store(Request $request)
 {
  $data = $request->validate([
   'doctor_id' => 'required|integer|exists:doctors,id',
   'name' => 'required|string|max:255',
   'description' => 'nullable|string|max:1000',
   'duration' => 'required|integer|min:1',
   'price' => 'required|numeric|min:0',
   'discount' => 'nullable|numeric|min:0|lte:price',
   'parent_id' => 'nullable|integer|exists:doctor_services,id',
  ], [
   'doctor_id.required' => 'شناسه دکتر الزامی است.',
   'doctor_id.integer' => 'شناسه دکتر باید به صورت عددی وارد شود.',
   'doctor_id.exists' => 'شناسه دکتر معتبر نمی‌باشد.',
   'name.required' => 'نام خدمت الزامی است.',
   'name.max' => 'نام خدمت نمی‌تواند بیش از 255 کاراکتر باشد.',
   'duration.required' => 'مدت زمان خدمت الزامی است.',
   'duration.integer' => 'مدت زمان خدمت باید به صورت عددی وارد شود.',
   'duration.min' => 'مدت زمان خدمت باید حداقل 1 دقیقه باشد.',
   'price.required' => 'قیمت خدمت الزامی است.',
   'price.numeric' => 'قیمت خدمت باید به صورت عددی وارد شود.',
   'price.min' => 'قیمت خدمت نمی‌تواند منفی باشد.',
   'discount.numeric' => 'تخفیف باید به صورت عددی وارد شود.',
   'discount.min' => 'تخفیف نمی‌تواند منفی باشد.',
   'discount.lte' => 'تخفیف نمی‌تواند از قیمت بیشتر باشد.',
   'parent_id.integer' => 'شناسه خدمت مادر باید به صورت عددی وارد شود.',
   'parent_id.exists' => 'شناسه خدمت مادر نامعتبر است.',
  ]);

  // دریافت کلینیک انتخاب شده از درخواست (با مقدار پیش‌فرض "default")
  $selectedClinicId = $request->get('selectedClinicId', 'default');
  // اگر کلینیک انتخاب شده غیر از دیفالت بود، clinic_id رو ست می‌کنیم؛ در غیر این صورت null
  $data['clinic_id'] = $selectedClinicId !== 'default' ? $selectedClinicId : null;

  DoctorService::create($data);
  return redirect()->route('dr-services.index')->with('success', 'خدمت با موفقیت ایجاد شد.');
 }

 public function edit(DoctorService $service)
 {
  $selectedClinicId = request()->get('selectedClinicId', 'default');
  // اگر کلینیک انتخاب شده غیر از "default" باشد و خدمت متعلق به آن کلینیک نباشد، دسترسی رد شود
  if ($selectedClinicId !== 'default' && $service->clinic_id != $selectedClinicId) {
   return abort(403, 'Access denied');
  }
  $parentServices = DoctorService::where('id', '!=', $service->id)->get();
  return view('dr.panel.dr-services.edit', compact('service', 'parentServices'));
 }

 public function update(Request $request, DoctorService $service)
 {
  $data = $request->validate([
   'doctor_id' => 'required|integer|exists:doctors,id',
   'name' => 'required|string|max:255',
   'description' => 'nullable|string|max:1000',
   'duration' => 'required|integer|min:1',
   'price' => 'required|numeric|min:0',
   'discount' => 'nullable|numeric|min:0|lte:price',
   'parent_id' => 'nullable|integer|exists:doctor_services,id',
  ], [
   'doctor_id.required' => 'شناسه دکتر الزامی است.',
   'doctor_id.integer' => 'شناسه دکتر باید به صورت عددی وارد شود.',
   'doctor_id.exists' => 'شناسه دکتر معتبر نمی‌باشد.',
   'name.required' => 'نام خدمت الزامی است.',
   'name.max' => 'نام خدمت نمی‌تواند بیش از 255 کاراکتر باشد.',
   'duration.required' => 'مدت زمان خدمت الزامی است.',
   'duration.integer' => 'مدت زمان خدمت باید به صورت عددی وارد شود.',
   'duration.min' => 'مدت زمان خدمت باید حداقل 1 دقیقه باشد.',
   'price.required' => 'قیمت خدمت الزامی است.',
   'price.numeric' => 'قیمت خدمت باید به صورت عددی وارد شود.',
   'price.min' => 'قیمت خدمت نمی‌تواند منفی باشد.',
   'discount.numeric' => 'تخفیف باید به صورت عددی وارد شود.',
   'discount.min' => 'تخفیف نمی‌تواند منفی باشد.',
   'discount.lte' => 'تخفیف نمی‌تواند از قیمت بیشتر باشد.',
   'parent_id.integer' => 'شناسه خدمت مادر باید به صورت عددی وارد شود.',
   'parent_id.exists' => 'شناسه خدمت مادر نامعتبر است.',
  ]);

  $selectedClinicId = $request->get('selectedClinicId', 'default');
  if ($selectedClinicId !== 'default' && $service->clinic_id != $selectedClinicId) {
   return abort(403, 'Access denied');
  }

  $service->update($data);
  return redirect()->route('dr-services.index')->with('success', 'خدمت با موفقیت به‌روزرسانی شد.');
 }

 public function destroy(DoctorService $service)
 {
  $selectedClinicId = request()->get('selectedClinicId', 'default');
  if ($selectedClinicId !== 'default' && $service->clinic_id != $selectedClinicId) {
   return response()->json(['error' => 'Access denied'], 403);
  }

  try {
   $service->delete();
   return response()->json(['success' => 'خدمت با موفقیت حذف شد.'], 200);
  } catch (\Exception $e) {
   return response()->json(['error' => 'خطا در حذف خدمت!', 'message' => $e->getMessage()], 500);
  }
 }


}

<?php
namespace App\Http\Controllers\Admin\Panel\Profile;
use Illuminate\Http\Request;
use App\Traits\HandlesRateLimiting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdminProfileController
{
 use HandlesRateLimiting;
 protected $manager;
 public function __construct()
 {
  $this->manager = Auth::guard('manager')->user()->first();
 }
 protected function getAuthenticatedmanager()
 {
  return Auth::guard('manager')->user()->first();
 }
 public function uploadPhoto(Request $request)
 {
  if (!$request->hasFile('photo')) {
   return response()->json(['success' => false, 'message' => 'لطفاً یک عکس انتخاب کنید!'], 400);
  }
  $request->validate([
   'photo' => 'image', // حداکثر 2MB
  ]);
  try {
   $manager = Auth::guard('manager')->user();
   if (!$manager) {
    return response()->json(['success' => false, 'message' => 'خطا: کاربر یافت نشد!'], 401);
   }
   $path = $request->file('photo')->store('admin-profile-photos', 'public');
   $manager->update(['avatar' => $path]);
   return response()->json(['success' => true, 'message' => 'عکس پروفایل با موفقیت آپدیت شد.', 'path' => Storage::url($path)]);
  } catch (\Exception $e) {
   return response()->json(['success' => false, 'message' => 'خطا در آپلود عکس: ' . $e->getMessage()], 500);
  }
 }

 
}

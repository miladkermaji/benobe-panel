<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BannerText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
/**
 * @group بنر و گرفتن  امار
 */
class BannerController extends Controller
{
    /**
     * گرفتن متن بنر
     *
     * @response 200 {
     *   "status": "success",
     *   "data": {
     *     "main_text": "بنوب: سلامت جامعه چطور به دست {word} می‌رسد",
     *     "switch_words": ["پزشک", "بیمارستان", "کلینیک", "تخصص"],
     *     "switch_interval": 2000,
     *     "image_path": "/images/stethoscope-background.png"
     *   }
     * }
     * @response 404 {
     *   "status": "error",
     *   "message": "متن بنر یافت نشد",
     *   "data": null
     * }
     * @response 500 {
     *   "status": "error",
     *   "message": "خطای سرور",
     *   "data": null
     * }
     */
    public function getBannerText(Request $request)
    {
        try {
            $bannerText = BannerText::where('status', 1)
                ->select('main_text', 'switch_words', 'switch_interval', 'image_path')
                ->first();

            if (! $bannerText) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'متن بنر یافت نشد',
                    'data'    => null,
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'main_text'       => $bannerText->main_text,
                    'switch_words'    => $bannerText->switch_words,
                    'switch_interval' => $bannerText->switch_interval,
                    'image_path'      => $bannerText->image_path,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('GetBannerText - Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور',
                'data'    => null,
            ], 500);
        }
    }

   /**
 * گرفتن آمار (تعداد پزشکان، بیمارستان‌ها،  کاربران و آزمایشگاه‌ها)
 *
 * @response 200 {
 *   "status": "success",
 *   "data": {
 *     "doctors_count": 1200,
 *     "hospitals_count": 300,
 *     "clinics_count": 500,
 *     "specialties_count": 40,
 *     "laboratories_count": 200
 *   }
 * }
 * @response 500 {
 *   "status": "error",
 *   "message": "خطای سرور",
 *   "data": null
 * }
 */
public function getStats(Request $request)
{
    try {
        $doctorsCount = \App\Models\Doctor::count();
        $usersCount = \App\Models\User::count();
        $hospitalsCount = \App\Models\MedicalCenter::where('is_active', 1)->where('type','hospital')->count();
        $laboratoriesCount = \App\Models\MedicalCenter::where('is_active', 1)->where('type','laboratory')->count();


        return response()->json([
            'status' => 'success',
            'data' => [
                'doctors_count' => $doctorsCount,
                'users_count' => $usersCount,
                'hospitals_count' => $hospitalsCount,
                'laboratories_count' => $laboratoriesCount,

            ],
        ], 200);

    } catch (\Exception $e) {
        Log::error('GetStats - Error: ' . $e->getMessage());
        return response()->json([
            'status' => 'error',
            'message' => 'خطای سرور',
            'data' => null,
        ], 500);
    }
}
}

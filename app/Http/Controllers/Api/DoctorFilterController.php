<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Specialty; // مدل Service برای جدول services
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DoctorFilterController extends Controller
{
    /**
     * گرفتن لیست گزینه‌های فیلتر (استان‌ها، شهرها، تخصص‌ها، خدمات)
     *
     * @response 200 {
     *   "status": "success",
     *   "data": {
     *     "provinces": [
     *       {"id": 1, "name": "تهران"},
     *       {"id": 2, "name": "اصفهان"}
     *     ],
     *     "cities": [
     *       {"id": 1, "name": "تهران", "province_id": 1},
     *       {"id": 2, "name": "کرج", "province_id": 1}
     *     ],
     *     "specialties": [
     *       {"id": 1, "name": "فوق تخصص قلب و عروق"},
     *       {"id": 2, "name": "جراحی عمومی"}
     *     ],
     *     "services": [
     *       {"id": 1, "name": "نوبت‌دهی مطب"},
     *       {"id": 2, "name": "مشاوره تلفنی"}
     *     ]
     *   }
     * }
     * @response 500 {
     *   "status": "error",
     *   "message": "خطای سرور",
     *   "data": null
     * }
     */
    public function getFilterOptions(Request $request)
    {
        try {
            // کش کردن برای 24 ساعت (1440 دقیقه)
            $data = Cache::remember('doctor_filter_options', 1440, function () {
                // گرفتن استان‌ها (level = 1)
                $provinces = Zone::where('level', 1)
                    ->select('id', 'name')
                    ->orderBy('name')
                    ->get();

                // گرفتن شهرها (level = 2)
                $cities = Zone::where('level', 2)
                    ->select('id', 'name', 'parent_id as province_id')
                    ->orderBy('name')
                    ->get();

                // گرفتن تخصص‌ها از جدول specialties
                $specialties = Specialty::select('id', 'name')
                    ->orderBy('name')
                    ->get();

                // گرفتن خدمات از جدول services
                $services = Service::where('status', true)
                    ->select('id', 'name')
                    ->orderBy('name')
                    ->get();

                return [
                    'provinces'   => $provinces,
                    'cities'      => $cities,
                    'specialties' => $specialties,
                    'services'    => $services,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data'   => $data,
            ], 200);

        } catch (\Exception $e) {
            \Log::error('GetFilterOptions - Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور',
                'data'    => null,
            ], 500);
        }
    }
}

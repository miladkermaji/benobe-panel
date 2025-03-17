<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    /**
     * گرفتن لیست استان‌ها
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {"id": 1, "name": "تهران"},
     *     {"id": 2, "name": "مشهد"}
     *   ]
     * }
     */
    public function getProvinces()
    {
        $provinces = Zone::where('level', 1) // فقط استان‌ها
            ->where('status', 1)                 // فقط فعال‌ها
            ->select('id', 'name')
            ->orderBy('name') // مرتب‌سازی بر اساس نام
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $provinces,
        ], 200);
    }

    /**
     * گرفتن لیست شهرها بر اساس استان
     * @queryParam province_id integer required شناسه استان
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {"id": 1, "name": "تهران"},
     *     {"id": 3, "name": "ری"}
     *   ]
     * }
     * @response 400 {
     *   "status": "error",
     *   "message": "شناسه استان الزامی است",
     *   "data": null
     * }
     * @response 404 {
     *   "status": "error",
     *   "message": "استان یافت نشد",
     *   "data": null
     * }
     */
    public function getCities(Request $request)
    {
        $provinceId = $request->query('province_id');

        if (! $provinceId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'شناسه استان الزامی است',
                'data'    => null,
            ], 400);
        }

        // بررسی وجود استان
        $provinceExists = Zone::where('id', $provinceId)
            ->where('level', 1)
            ->where('status', 1)
            ->exists();

        if (! $provinceExists) {
            return response()->json([
                'status'  => 'error',
                'message' => 'استان یافت نشد',
                'data'    => null,
            ], 404);
        }

        $cities = Zone::where('level', 2) // فقط شهرها
            ->where('parent_id', $provinceId) // شهرهای مربوط به استان
            ->where('status', 1)              // فقط فعال‌ها
            ->select('id', 'name')
            ->orderBy('name') // مرتب‌سازی بر اساس نام
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $cities,
        ], 200);
    }
}

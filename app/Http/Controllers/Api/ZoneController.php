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
     *     {"id": 1, "name": "تهران", "slug": "تهران"},
     *     {"id": 2, "name": "مشهد", "slug": "مشهد"}
     *   ]
     * }
     * @response 500 {
     *   "status": "error",
     *   "message": "خطای سرور",
     *   "data": null
     * }
     */
    public function getProvinces()
    {
        try {
            $provinces = Zone::where('level', 1) // فقط استان‌ها
                ->where('status', true)
                ->select('id', 'name', 'slug')
                ->orderBy('name')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $provinces,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطای سرور',
                'data' => null,
            ], 500);
        }
    }

    /**
     * گرفتن لیست شهرها بر اساس استان
     * @queryParam province_slug string required اسلاگ استان
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {"id": 1, "name": "تهران", "slug": "تهران"},
     *     {"id": 3, "name": "ری", "slug": "ری"}
     *   ]
     * }
     * @response 400 {
     *   "status": "error",
     *   "message": "اسلاگ استان الزامی است",
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
        try {
            $provinceSlug = $request->query('province_slug');

            if (!$provinceSlug) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'اسلاگ استان الزامی است',
                    'data' => null,
                ], 400);
            }

            // بررسی وجود استان
            $province = Zone::where('slug', $provinceSlug)
                ->where('level', 1)
                ->where('status', true)
                ->first();

            if (!$province) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'استان یافت نشد',
                    'data' => null,
                ], 404);
            }

            $cities = Zone::where('level', 2) // فقط شهرها
                ->where('parent_id', $province->id)
                ->where('status', true)
                ->select('id', 'name', 'slug')
                ->orderBy('name')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $cities,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطای سرور',
                'data' => null,
            ], 500);
        }
    }
}

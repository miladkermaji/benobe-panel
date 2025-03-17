<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use Illuminate\Http\Request;

class SpecialtyController extends Controller
{
    /**
     * گرفتن لیست تخصص‌ها
     *
     * @queryParam limit integer تعداد آیتم‌ها (اختیاری، اگر نباشد همه برگردانده می‌شود)
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "قلب و عروق",
     *       "description": "تخصص در درمان قلب",
     *       "doctor_count": 50
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

    public function getSpecialties(Request $request)
    {
        // بررسی وجود پارامتر limit
        $limit = $request->has('limit') ? (int) $request->input('limit') : null;

// گرفتن تخصص‌ها
        $specialties = Specialty::where('status', 1)
            ->withCount('doctors') // تعداد پزشکان مرتبط
            ->select('id', 'name', 'description')
            ->when($limit !== null, function ($query) use ($limit) {
                return $query->limit($limit);
            })
            ->orderBy('id')
            ->get();

// فرمت کردن داده‌ها
        $formattedSpecialties = $specialties->map(function ($specialty) {
            return [
                'id'           => $specialty->id,
                'name'         => $specialty->name,
                'description'  => $specialty->description,
                'doctor_count' => $specialty->doctors_count,
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data'   => $formattedSpecialties,
        ], 200);

    }
}

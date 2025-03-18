<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Hospital;
use App\Models\ImagingCenter;
use App\Models\Laboratory;
use App\Models\TreatmentCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @group مراکز درمانی
 */
class MedicalCentersController extends Controller
{
    /**
     * گرفتن آمار تعداد مراکز درمانی
     *
     * این متد تعداد کلینیک‌ها، درمانگاه‌ها، مراکز تصویربرداری، بیمارستان‌ها و لابراتوارها را برمی‌گرداند.
     *
     * @response 200 {
     *   "status": "success",
     *   "data": {
     *     "clinics_count": 10,
     *     "treatment_centers_count": 5,
     *     "imaging_centers_count": 5,
     *     "hospitals_count": 8,
     *     "laboratories_count": 6
     *   }
     * }
     * @response 500 {
     *   "status": "error",
     *   "message": "خطای سرور",
     *   "data": null
     * }
     */
    public function getStats()
    {
        try {
            $clinicsCount          = Clinic::where('is_active', 1)->count();
            $treatmentCentersCount = TreatmentCenter::where('is_active', 1)->count();
            $imagingCentersCount   = ImagingCenter::where('is_active', 1)->count();
            $hospitalsCount        = Hospital::where('is_active', 1)->count();
            $laboratoriesCount     = Laboratory::where('is_active', 1)->count();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'clinics_count'           => $clinicsCount,
                    'treatment_centers_count' => $treatmentCentersCount,
                    'imaging_centers_count'   => $imagingCentersCount,
                    'hospitals_count'         => $hospitalsCount,
                    'laboratories_count'      => $laboratoriesCount,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('GetStats - Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور',
                'data'    => null,
            ], 500);
        }
    }

    /**
     * گرفتن لیست کلینیک‌ها
     *
     * این متد لیستی از کلینیک‌ها را برمی‌گرداند. پارامتر اختیاری `limit` برای محدود کردن تعداد نتایج وجود دارد (اگر مشخص نشود، همه را برمی‌گرداند).
     *
     * @queryParam limit integer تعداد آیتم‌ها (اختیاری، اگر نباشد همه برگردانده می‌شود)
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "کلینیک نمونه",
     *       "address": "تهران، خیابان اصلی",
     *       "doctor_count": 5
     *     }
     *   ]
     * }
     * @response 500 {
     *   "status": "error",
     *   "message": "خطای سرور",
     *   "data": null
     * }
     */
    public function getClinics(Request $request)
    {
        try {
            $limit = $request->has('limit') ? (int) $request->input('limit') : null;

            $clinics = Clinic::where('is_active', 1)
                ->withCount('doctor')
                ->select('id', 'name', 'address')
                ->when($limit !== null, function ($query) use ($limit) {
                    return $query->limit($limit);
                })
                ->orderBy('id')
                ->get();

            $formattedClinics = $clinics->map(function ($clinic) {
                return [
                    'id'           => $clinic->id,
                    'name'         => $clinic->name,
                    'address'      => $clinic->address,
                    'doctor_count' => $clinic->doctor_count,
                ];
            })->values();

            return response()->json([
                'status' => 'success',
                'data'   => $formattedClinics,
            ], 200);

        } catch (\Exception $e) {
            Log::error('GetClinics - Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور',
                'data'    => null,
            ], 500);
        }
    }

    /**
     * گرفتن لیست درمانگاه‌ها
     *
     * این متد لیستی از درمانگاه‌ها را برمی‌گرداند. پارامتر اختیاری `limit` برای محدود کردن تعداد نتایج وجود دارد (اگر مشخص نشود، همه را برمی‌گرداند).
     *
     * @queryParam limit integer تعداد آیتم‌ها (اختیاری، اگر نباشد همه برگردانده می‌شود)
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "درمانگاه نمونه",
     *       "address": "تهران، خیابان اصلی",
     *       "doctor_count": 3
     *     }
     *   ]
     * }
     * @response 500 {
     *   "status": "error",
     *   "message": "خطای سرور",
     *   "data": null
     * }
     */
    public function getTreatmentCenters(Request $request)
    {
        try {
            $limit = $request->has('limit') ? (int) $request->input('limit') : null;

            $treatmentCenters = TreatmentCenter::where('is_active', 1)
                ->withCount('doctor')
                ->select('id', 'name', 'address')
                ->when($limit !== null, function ($query) use ($limit) {
                    return $query->limit($limit);
                })
                ->orderBy('id')
                ->get();

            $formattedTreatmentCenters = $treatmentCenters->map(function ($treatmentCenter) {
                return [
                    'id'           => $treatmentCenter->id,
                    'name'         => $treatmentCenter->name,
                    'address'      => $treatmentCenter->address,
                    'doctor_count' => $treatmentCenter->doctor_count,
                ];
            })->values();

            return response()->json([
                'status' => 'success',
                'data'   => $formattedTreatmentCenters,
            ], 200);

        } catch (\Exception $e) {
            Log::error('GetTreatmentCenters - Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور',
                'data'    => null,
            ], 500);
        }
    }

    /**
     * گرفتن لیست مراکز تصویربرداری
     *
     * این متد لیستی از مراکز تصویربرداری را برمی‌گرداند. پارامتر اختیاری `limit` برای محدود کردن تعداد نتایج وجود دارد (اگر مشخص نشود، همه را برمی‌گرداند).
     *
     * @queryParam limit integer تعداد آیتم‌ها (اختیاری، اگر نباشد همه برگردانده می‌شود)
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "مرکز تصویربرداری نمونه",
     *       "address": "تهران، خیابان اصلی",
     *       "doctor_count": 2
     *     }
     *   ]
     * }
     * @response 500 {
     *   "status": "error",
     *   "message": "خطای سرور",
     *   "data": null
     * }
     */
    public function getImagingCenters(Request $request)
    {
        try {
            $limit = $request->has('limit') ? (int) $request->input('limit') : null;

            $imagingCenters = ImagingCenter::where('is_active', 1)
                ->withCount('doctor')
                ->select('id', 'name', 'address')
                ->when($limit !== null, function ($query) use ($limit) {
                    return $query->limit($limit);
                })
                ->orderBy('id')
                ->get();

            $formattedImagingCenters = $imagingCenters->map(function ($imagingCenter) {
                return [
                    'id'           => $imagingCenter->id,
                    'name'         => $imagingCenter->name,
                    'address'      => $imagingCenter->address,
                    'doctor_count' => $imagingCenter->doctor_count,
                ];
            })->values();

            return response()->json([
                'status' => 'success',
                'data'   => $formattedImagingCenters,
            ], 200);

        } catch (\Exception $e) {
            Log::error('GetImagingCenters - Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور',
                'data'    => null,
            ], 500);
        }
    }

    /**
     * گرفتن لیست بیمارستان‌ها
     *
     * این متد لیستی از بیمارستان‌ها را برمی‌گرداند. پارامتر اختیاری `limit` برای محدود کردن تعداد نتایج وجود دارد (اگر مشخص نشود، همه را برمی‌گرداند).
     *
     * @queryParam limit integer تعداد آیتم‌ها (اختیاری، اگر نباشد همه برگردانده می‌شود)
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "بیمارستان نمونه",
     *       "address": "تهران، خیابان اصلی",
     *       "doctor_count": 10
     *     }
     *   ]
     * }
     * @response 500 {
     *   "status": "error",
     *   "message": "خطای سرور",
     *   "data": null
     * }
     */
    public function getHospitals(Request $request)
    {
        try {
            $limit = $request->has('limit') ? (int) $request->input('limit') : null;

            $hospitals = Hospital::where('is_active', 1)
                ->withCount('doctor')
                ->select('id', 'name', 'address')
                ->when($limit !== null, function ($query) use ($limit) {
                    return $query->limit($limit);
                })
                ->orderBy('id')
                ->get();

            $formattedHospitals = $hospitals->map(function ($hospital) {
                return [
                    'id'           => $hospital->id,
                    'name'         => $hospital->name,
                    'address'      => $hospital->address,
                    'doctor_count' => $hospital->doctor_count,
                ];
            })->values();

            return response()->json([
                'status' => 'success',
                'data'   => $formattedHospitals,
            ], 200);

        } catch (\Exception $e) {
            Log::error('GetHospitals - Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور',
                'data'    => null,
            ], 500);
        }
    }

    /**
     * گرفتن لیست لابراتوارها
     *
     * این متد لیستی از لابراتوارها را برمی‌گرداند. پارامتر اختیاری `limit` برای محدود کردن تعداد نتایج وجود دارد (اگر مشخص نشود، همه را برمی‌گرداند).
     *
     * @queryParam limit integer تعداد آیتم‌ها (اختیاری، اگر نباشد همه برگردانده می‌شود)
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "لابراتوار نمونه",
     *       "address": "تهران، خیابان اصلی",
     *       "doctor_count": 4
     *     }
     *   ]
     * }
     * @response 500 {
     *   "status": "error",
     *   "message": "خطای سرور",
     *   "data": null
     * }
     */
    public function getLaboratories(Request $request)
    {
        try {
            $limit = $request->has('limit') ? (int) $request->input('limit') : null;

            $laboratories = Laboratory::where('is_active', 1)
                ->withCount('doctor')
                ->select('id', 'name', 'address')
                ->when($limit !== null, function ($query) use ($limit) {
                    return $query->limit($limit);
                })
                ->orderBy('id')
                ->get();

            $formattedLaboratories = $laboratories->map(function ($laboratory) {
                return [
                    'id'           => $laboratory->id,
                    'name'         => $laboratory->name,
                    'address'      => $laboratory->address,
                    'doctor_count' => $laboratory->doctor_count,
                ];
            })->values();

            return response()->json([
                'status' => 'success',
                'data'   => $formattedLaboratories,
            ], 200);

        } catch (\Exception $e) {
            Log::error('GetLaboratories - Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور',
                'data'    => null,
            ], 500);
        }
    }
}

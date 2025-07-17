<?php

namespace App\Http\Controllers\Api;

use App\Models\Zone;
use App\Models\Clinic;
use App\Models\Service;
use App\Models\Hospital;
use App\Models\Insurance;
use App\Models\Specialty;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use App\Models\ImagingCenter;
use App\Models\MedicalCenter;
use App\Models\TreatmentCenter;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\ZoneResource;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\ServiceResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\InsuranceResource;
use App\Http\Resources\SpecialtyResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\MedicalCenterResource;

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
            $clinicsCount          = MedicalCenter::where('is_active', 1)->where('type', 'clinic')->count();
            $treatmentCentersCount = MedicalCenter::where('is_active', 1)->where('type', 'treatment_centers')->count();
            $imagingCentersCount   = MedicalCenter::where('is_active', 1)->where('type', 'imaging_center')->count();
            $hospitalsCount        = MedicalCenter::where('is_active', 1)->where('type', 'hospital')->count();
            $laboratoriesCount     = MedicalCenter::where('is_active', 1)->where('type', 'laboratory')->count();

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
     *       "doctor_count": 5,
     *       "province": "تهران",
     *       "province_id": 1,
     *       "city": "تهران",
     *       "city_id": 2,
     *       "slug": "clinic-sample"
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

            $clinics = MedicalCenter::where('is_active', 1)
            ->where('type', 'clinic')
                ->withCount('doctor')
                ->with([
                    'province' => fn ($query) => $query->select('id', 'name'),
                    'city' => fn ($query) => $query->select('id', 'name')
                ])
                ->select('id', 'name', 'address', 'province_id', 'city_id', 'slug')
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
                    'province'     => $clinic->province ? $clinic->province->name : null,
                    'province_id'  => $clinic->province_id,
                    'city'         => $clinic->city ? $clinic->city->name : null,
                    'city_id'      => $clinic->city_id,
                    'slug'         => $clinic->slug,
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
     *       "doctor_count": 3,
     *       "province": "تهران",
     *       "province_id": 1,
     *       "city": "تهران",
     *       "city_id": 2,
     *       "slug": "treatment-center-sample"
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

            $treatmentCenters = MedicalCenter::where('is_active', 1)
            ->where('type', 'treatment_centers')
                ->withCount('doctor')
                ->with([
                    'province' => fn ($query) => $query->select('id', 'name'),
                    'city' => fn ($query) => $query->select('id', 'name')
                ])
                ->select('id', 'name', 'address', 'province_id', 'city_id', 'slug')
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
                    'province'     => $treatmentCenter->province ? $treatmentCenter->province->name : null,
                    'province_id'  => $treatmentCenter->province_id,
                    'city'         => $treatmentCenter->city ? $treatmentCenter->city->name : null,
                    'city_id'      => $treatmentCenter->city_id,
                    'slug'         => $treatmentCenter->slug,
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
     *       "doctor_count": 2,
     *       "province": "تهران",
     *       "province_id": 1,
     *       "city": "تهران",
     *       "city_id": 2,
     *       "slug": "imaging-center-sample"
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

            $imagingCenters = MedicalCenter::where('is_active', 1)
                ->withCount('doctor')
                ->where('type', 'imaging_center')
                ->with([
                    'province' => fn ($query) => $query->select('id', 'name'),
                    'city' => fn ($query) => $query->select('id', 'name')
                ])
                ->select('id', 'name', 'address', 'province_id', 'city_id', 'slug')
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
                    'province'     => $imagingCenter->province ? $imagingCenter->province->name : null,
                    'province_id'  => $imagingCenter->province_id,
                    'city'         => $imagingCenter->city ? $imagingCenter->city->name : null,
                    'city_id'      => $imagingCenter->city_id,
                    'slug'         => $imagingCenter->slug,
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
     *       "doctor_count": 10,
     *       "province": "تهران",
     *       "province_id": 1,
     *       "city": "تهران",
     *       "city_id": 2,
     *       "slug": "hospital-sample"
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

            $hospitals = MedicalCenter::where('is_active', 1)
                ->withCount('doctor')
                ->where('type', 'hospital')
                ->with([
                    'province' => fn ($query) => $query->select('id', 'name'),
                    'city' => fn ($query) => $query->select('id', 'name')
                ])
                ->select('id', 'name', 'address', 'province_id', 'city_id', 'slug')
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
                    'province'     => $hospital->province ? $hospital->province->name : null,
                    'province_id'  => $hospital->province_id,
                    'city'         => $hospital->city ? $hospital->city->name : null,
                    'city_id'      => $hospital->city_id,
                    'slug'         => $hospital->slug,
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
     *       "doctor_count": 4,
     *       "province": "تهران",
     *       "province_id": 1,
     *       "city": "تهران",
     *       "city_id": 2,
     *       "slug": "laboratory-sample"
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

            $laboratories = MedicalCenter::where('is_active', 1)
                ->withCount('doctor')
                ->where('type', 'laboratory')
                ->with([
                    'province' => fn ($query) => $query->select('id', 'name'),
                    'city' => fn ($query) => $query->select('id', 'name')
                ])
                ->select('id', 'name', 'address', 'province_id', 'city_id', 'slug')
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
                    'province'     => $laboratory->province ? $laboratory->province->name : null,
                    'province_id'  => $laboratory->province_id,
                    'city'         => $laboratory->city ? $laboratory->city->name : null,
                    'city_id'      => $laboratory->city_id,
                    'slug'         => $laboratory->slug,
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
    /**
     * گرفتن لیست استان‌های دارای مراکز درمانی
     *
     * @queryParam limit integer تعداد آیتم‌ها (اختیاری، پیش‌فرض 10)
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "province_id": 1,
     *       "province_name": "کردستان",
     *       "centers_count": 90
     *     }
     *   ]
     * }
     * @response 500 {
     *   "status": "error",
     *   "message": "خطای سرور",
     *   "data": null
     * }
     */
    public function getCitiesWithCenters(Request $request)
    {
        try {
            // اعتبارسنجی ورودی limit
            $limit = max(1, (int) $request->input('limit', 10));

            $provinces = Zone::where('level', 1)
                ->whereHas('medicalCenters', fn ($q) => $q->where('is_active', true))
                ->with([
                    'children' => fn ($query) => $query->withCount([
                        'medicalCenters as centers_count' => fn ($q) => $q->where('is_active', true)
                    ])->select('id', 'name')
                ])
                ->select('id', 'name')
                ->take($limit)
                ->get();

            $formattedProvinces = $provinces->map(function ($province) {
                $totalCenters = $province->children->sum('centers_count');
                return [
                    'province_id'   => $province->id,
                    'province_name' => $province->name,
                    'centers_count' => $totalCenters,
                ];
            })->values();

            return response()->json([
                'status' => 'success',
                'data'   => $formattedProvinces,
            ], 200);
        } catch (\Exception $e) {
            Log::channel('api')->error('GetCitiesWithCenters - Error: ', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور',
                'data'    => null,
            ], 500);
        }
    }

    /**
 * گرفتن لیست همه مراکز درمانی به‌صورت رندوم با نام استان
 *
 * @queryParam limit integer تعداد آیتم‌ها (اختیاری، پیش‌فرض 5، اگر 0 یا خالی باشد همه برگردانده می‌شود)
 * @response 200 {
 *   "status": "success",
 *   "data": [
 *     {
 *       "id": 1,
 *       "name": "درمانگاه سعدی",
 *       "type": "treatment_centers",
 *       "province": "کردستان",
 *       "avatar": "http://example.com/images/center-avatar.png",
 *       "slug": "treatment-center-saadi"
 *     }
 *   ]
 * }
 * @response 500 {
 *   "status": "error",
 *   "message": "خطای سرور",
 *   "data": null
 * }
 */
    public function getAllCenters(Request $request)
    {
        try {
            $limit = (int) $request->input('limit', 5);

            $query = MedicalCenter::where('is_active', true)
                ->with([
                    'province' => fn ($q) => $q->select('id', 'name')->where('level', 1)
                ])
                ->select('id', 'name', 'type', 'province_id', 'avatar', 'slug')
                ->inRandomOrder();

            $centers = $query->get()->map(function ($center) {
                return [
                    'id'       => $center->id,
                    'name'     => $center->name,
                    'type'     => $center->type,
                    'province' => $center->province ? $center->province->name : null,
                    'avatar'   => $center->avatar ? Storage::url($center->avatar) : url('/default-avatar.png'),
                    'slug'     => $center->slug,
                ];
            });

            $formattedCenters = $limit > 0 ? $centers->take($limit)->values() : $centers->values();

            return response()->json([
                'status' => 'success',
                'data'   => $formattedCenters,
            ], 200);
        } catch (\Exception $e) {
            Log::channel('api')->error('GetAllCenters - Error: ', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور',
                'data'    => null,
            ], 500);
        }
    }

    /**
     * لیست همه مراکز درمانی فعال
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        try {
            $centers = MedicalCenter::where('is_active', 1)->get();
            return response()->json([
                'status' => 'success',
                'data' => $centers,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('MedicalCentersController@list - Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'خطای سرور',
                'data' => null,
            ], 500);
        }
    }

    /**
     * گرفتن لیست تخصص‌های مراکز درمانی
     *
     * این متد لیستی از تخصص‌هایی که در مراکز درمانی فعال وجود دارند را برمی‌گرداند.
     * پارامتر اختیاری `limit` برای محدود کردن تعداد نتایج وجود دارد (اگر مشخص نشود، همه را برمی‌گرداند).
     * پارامتر اختیاری `center_type` برای فیلتر کردن بر اساس نوع مرکز درمانی.
     *
     * @queryParam limit integer تعداد آیتم‌ها (اختیاری، اگر نباشد همه برگردانده می‌شود)
     * @queryParam center_type string نوع مرکز درمانی (اختیاری، hospital, treatment_centers, clinic, imaging_center, laboratory, pharmacy, policlinic)
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "قلب و عروق",
     *       "description": "تخصص در درمان بیماری‌های قلب و عروق",
     *       "center_count": 15,
     *       "provinces": [
     *         {
     *           "id": 1,
     *           "name": "تهران"
     *         }
     *       ],
     *       "cities": [
     *         {
     *           "id": 2,
     *           "name": "تهران"
     *         }
     *       ]
     *     }
     *   ]
     * }
     * @response 422 {
     *   "status": "error",
     *   "message": "خطا در اعتبارسنجی",
     *   "errors": {
     *     "center_type": ["نوع مرکز معتبر نیست."]
     *   }
     * }
     * @response 500 {
     *   "status": "error",
     *   "message": "خطای سرور",
     *   "data": null
     * }
     */
    public function getCenterSpecialties(Request $request)
    {
        try {
            // اعتبارسنجی ورودی‌ها
            $validator = Validator::make($request->all(), [
                'limit' => 'nullable|integer|min:1|max:100',
                'center_type' => 'nullable|in:hospital,treatment_centers,clinic,imaging_center,laboratory,pharmacy,policlinic',
            ], [
                'limit.integer' => 'تعداد آیتم‌ها باید عدد باشد.',
                'limit.min' => 'تعداد آیتم‌ها باید حداقل ۱ باشد.',
                'limit.max' => 'تعداد آیتم‌ها نمی‌تواند بیشتر از ۱۰۰ باشد.',
                'center_type.in' => 'نوع مرکز معتبر نیست.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'خطا در اعتبارسنجی',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $limit = $request->input('limit');
            $centerType = $request->input('center_type');

            // گرفتن تمام تخصص‌های موجود در مراکز درمانی فعال
            $query = MedicalCenter::where('is_active', 1)
                ->whereNotNull('specialty_ids')
                ->where('specialty_ids', '!=', '[]')
                ->with([
                    'province' => fn ($query) => $query->select('id', 'name'),
                    'city' => fn ($query) => $query->select('id', 'name')
                ])
                ->select('id', 'name', 'specialty_ids', 'province_id', 'city_id', 'type');

            // فیلتر بر اساس نوع مرکز اگر مشخص شده باشد
            if ($centerType) {
                $query->where('type', $centerType);
            }

            $medicalCenters = $query->get();

            // استخراج تمام آیدی‌های تخصص‌ها
            $allSpecialtyIds = [];
            foreach ($medicalCenters as $center) {
                if (is_array($center->specialty_ids)) {
                    $allSpecialtyIds = array_merge($allSpecialtyIds, $center->specialty_ids);
                }
            }

            // حذف آیدی‌های تکراری
            $uniqueSpecialtyIds = array_unique($allSpecialtyIds);

            if (empty($uniqueSpecialtyIds)) {
                return response()->json([
                    'status' => 'success',
                    'data'   => [],
                    'filters' => [
                        'center_type' => $centerType,
                        'total_centers' => $medicalCenters->count(),
                    ]
                ], 200);
            }

            // گرفتن اطلاعات تخصص‌ها
            $specialties = Specialty::whereIn('id', $uniqueSpecialtyIds)
                ->where('status', 1)
                ->select('id', 'name', 'description')
                ->orderBy('name')
                ->get();

            // محاسبه تعداد مراکز برای هر تخصص
            $formattedSpecialties = $specialties->map(function ($specialty) use ($medicalCenters) {
                $centerCount = 0;
                $provinces = [];
                $cities = [];

                foreach ($medicalCenters as $center) {
                    if (is_array($center->specialty_ids) && in_array($specialty->id, $center->specialty_ids)) {
                        $centerCount++;

                        // جمع‌آوری استان‌ها و شهرها
                        if ($center->province) {
                            $provinces[$center->province_id] = [
                                'id' => $center->province_id,
                                'name' => $center->province->name
                            ];
                        }

                        if ($center->city) {
                            $cities[$center->city_id] = [
                                'id' => $center->city_id,
                                'name' => $center->city->name
                            ];
                        }
                    }
                }

                return [
                    'id'           => $specialty->id,
                    'name'         => $specialty->name,
                    'description'  => $specialty->description,
                    'center_count' => $centerCount,
                    'provinces'    => array_values($provinces),
                    'cities'       => array_values($cities),
                ];
            })->values();

            // اعمال محدودیت تعداد اگر مشخص شده باشد
            if ($limit !== null) {
                $formattedSpecialties = $formattedSpecialties->take($limit);
            }

            return response()->json([
                'status' => 'success',
                'data'   => $formattedSpecialties,
                'filters' => [
                    'center_type' => $centerType,
                    'total_centers' => $medicalCenters->count(),
                    'total_specialties' => $formattedSpecialties->count(),
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('GetCenterSpecialties - Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور',
                'data'    => null,
            ], 500);
        }
    }

    /**
     * گرفتن لیست انواع مراکز درمانی برای کشویی
     *
     * این متد لیستی از انواع مراکز درمانی موجود را برای استفاده در کشویی برمی‌گرداند.
     *
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "value": "hospital",
     *       "label": "بیمارستان"
     *     },
     *     {
     *       "value": "treatment_centers",
     *       "label": "مراکز درمانی"
     *     }
     *   ]
     * }
     * @response 500 {
     *   "status": "error",
     *   "message": "خطای سرور",
     *   "data": null
     * }
     */
    public function getCenterTypes()
    {
        try {
            $centerTypes = [
                ['value' => 'hospital', 'label' => 'بیمارستان'],
                ['value' => 'treatment_centers', 'label' => 'مراکز درمانی'],
                ['value' => 'clinic', 'label' => 'کلینیک'],
                ['value' => 'imaging_center', 'label' => 'مرکز تصویربرداری'],
                ['value' => 'laboratory', 'label' => 'آزمایشگاه'],
                ['value' => 'pharmacy', 'label' => 'داروخانه'],
                ['value' => 'policlinic', 'label' => 'پلی‌کلینیک'],
            ];

            return response()->json([
                'status' => 'success',
                'data'   => $centerTypes,
            ], 200);

        } catch (\Exception $e) {
            Log::error('GetCenterTypes - Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور',
                'data'    => null,
            ], 500);
        }
    }

    /**
     * گرفتن لیست تخصص‌های درمانگاه‌ها
     *
     * این متد لیستی از تخصص‌هایی که در درمانگاه‌های فعال وجود دارند را برمی‌گرداند.
     * پارامتر اختیاری `limit` برای محدود کردن تعداد نتایج وجود دارد (اگر مشخص نشود، همه را برمی‌گرداند).
     *
     * @queryParam limit integer تعداد آیتم‌ها (اختیاری، اگر نباشد همه برگردانده می‌شود)
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "قلب و عروق",
     *       "description": "تخصص در درمان بیماری‌های قلب و عروق",
     *       "center_count": 8,
     *       "provinces": [
     *         {
     *           "id": 1,
     *           "name": "تهران"
     *         }
     *       ],
     *       "cities": [
     *         {
     *           "id": 2,
     *           "name": "تهران"
     *         }
     *       ]
     *     }
     *   ]
     * }
     * @response 500 {
     *   "status": "error",
     *   "message": "خطای سرور",
     *   "data": null
     * }
     */
    public function getTreatmentCenterSpecialties(Request $request)
    {
        try {
            $limit = $request->has('limit') ? (int) $request->input('limit') : null;

            // گرفتن تمام تخصص‌های موجود در درمانگاه‌های فعال
            $query = MedicalCenter::where('is_active', 1)
                ->where('type', 'treatment_centers')
                ->whereNotNull('specialty_ids')
                ->where('specialty_ids', '!=', '[]')
                ->with([
                    'province' => fn ($query) => $query->select('id', 'name'),
                    'city' => fn ($query) => $query->select('id', 'name')
                ])
                ->select('id', 'name', 'specialty_ids', 'province_id', 'city_id');

            $treatmentCenters = $query->get();

            // استخراج تمام آیدی‌های تخصص‌ها
            $allSpecialtyIds = [];
            foreach ($treatmentCenters as $center) {
                if (is_array($center->specialty_ids)) {
                    $allSpecialtyIds = array_merge($allSpecialtyIds, $center->specialty_ids);
                }
            }

            // حذف آیدی‌های تکراری
            $uniqueSpecialtyIds = array_unique($allSpecialtyIds);

            if (empty($uniqueSpecialtyIds)) {
                return response()->json([
                    'status' => 'success',
                    'data'   => [],
                ], 200);
            }

            // گرفتن اطلاعات تخصص‌ها
            $specialties = Specialty::whereIn('id', $uniqueSpecialtyIds)
                ->where('status', 1)
                ->select('id', 'name', 'description')
                ->orderBy('name')
                ->get();

            // محاسبه تعداد مراکز برای هر تخصص
            $formattedSpecialties = $specialties->map(function ($specialty) use ($treatmentCenters) {
                $centerCount = 0;
                $provinces = [];
                $cities = [];

                foreach ($treatmentCenters as $center) {
                    if (is_array($center->specialty_ids) && in_array($specialty->id, $center->specialty_ids)) {
                        $centerCount++;

                        // جمع‌آوری استان‌ها و شهرها
                        if ($center->province) {
                            $provinces[$center->province_id] = [
                                'id' => $center->province_id,
                                'name' => $center->province->name
                            ];
                        }

                        if ($center->city) {
                            $cities[$center->city_id] = [
                                'id' => $center->city_id,
                                'name' => $center->city->name
                            ];
                        }
                    }
                }

                return [
                    'id'           => $specialty->id,
                    'name'         => $specialty->name,
                    'description'  => $specialty->description,
                    'center_count' => $centerCount,
                    'provinces'    => array_values($provinces),
                    'cities'       => array_values($cities),
                ];
            })->values();

            // اعمال محدودیت تعداد اگر مشخص شده باشد
            if ($limit !== null) {
                $formattedSpecialties = $formattedSpecialties->take($limit);
            }

            return response()->json([
                'status' => 'success',
                'data'   => $formattedSpecialties,
            ], 200);

        } catch (\Exception $e) {
            Log::error('GetTreatmentCenterSpecialties - Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور',
                'data'    => null,
            ], 500);
        }
    }
}

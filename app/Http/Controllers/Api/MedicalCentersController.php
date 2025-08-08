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
            $clinicsCount          = MedicalCenter::where('is_active', 1)->where('type', 'clinic')->whereNotIn('type', ['policlinic'])->count();
            $treatmentCentersCount = MedicalCenter::where('is_active', 1)->where('type', 'treatment_centers')->whereNotIn('type', ['policlinic'])->count();
            $imagingCentersCount   = MedicalCenter::where('is_active', 1)->where('type', 'imaging_center')->whereNotIn('type', ['policlinic'])->count();
            $hospitalsCount        = MedicalCenter::where('is_active', 1)->where('type', 'hospital')->whereNotIn('type', ['policlinic'])->count();
            $laboratoriesCount     = MedicalCenter::where('is_active', 1)->where('type', 'laboratory')->whereNotIn('type', ['policlinic'])->count();

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
     *       "province": { "id": 1, "name": "تهران", "slug": "تهران" },
     *       "province_id": 1,
     *       "city": { "id": 2, "name": "تهران", "slug": "تهران" },
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

            $clinics = MedicalCenter::where('is_active', true)
                ->where('type', 'clinic')
                ->whereNotIn('type', ['policlinic'])
                ->withCount('doctor')
                ->with([
                    'province' => fn ($query) => $query->select('id', 'name', 'slug'),
                    'city' => fn ($query) => $query->select('id', 'name', 'slug')
                ])
                ->select('id', 'name', 'address', 'province_id', 'city_id', 'slug')
                ->when($limit !== null, function ($query) use ($limit) {
                    return $query->limit($limit);
                })
                ->orderBy('id')
                ->get();

            $formattedClinics = $clinics->map(function ($clinic) {
                return [
                    'id' => $clinic->id,
                    'name' => $clinic->name,
                    'address' => $clinic->address,
                    'doctor_count' => $clinic->doctor_count,
                    'province' => $clinic->province ? [
                        'id' => $clinic->province->id,
                        'name' => $clinic->province->name,
                        'slug' => $clinic->province->slug
                    ] : null,
                    'province_id' => $clinic->province_id,
                    'city' => $clinic->city ? [
                        'id' => $clinic->city->id,
                        'name' => $clinic->city->name,
                        'slug' => $clinic->city->slug
                    ] : null,
                    'city_id' => $clinic->city_id,
                    'slug' => $clinic->slug,
                ];
            })->values();

            return response()->json([
                'status' => 'success',
                'data' => $formattedClinics,
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
 *       "province": { "id": 1, "name": "تهران", "slug": "تهران" },
 *       "province_id": 1,
 *       "city": { "id": 2, "name": "تهران", "slug": "تهران" },
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

            $treatmentCenters = MedicalCenter::where('is_active', true)
                ->where('type', 'treatment_centers')
                ->whereNotIn('type', ['policlinic'])
                ->withCount('doctor')
                ->with([
                    'province' => fn ($query) => $query->select('id', 'name', 'slug'),
                    'city' => fn ($query) => $query->select('id', 'name', 'slug')
                ])
                ->select('id', 'name', 'address', 'province_id', 'city_id', 'slug')
                ->when($limit !== null, function ($query) use ($limit) {
                    return $query->limit($limit);
                })
                ->orderBy('id')
                ->get();

            $formattedTreatmentCenters = $treatmentCenters->map(function ($treatmentCenter) {
                return [
                    'id' => $treatmentCenter->id,
                    'name' => $treatmentCenter->name,
                    'address' => $treatmentCenter->address,
                    'doctor_count' => $treatmentCenter->doctor_count,
                    'province' => $treatmentCenter->province ? [
                        'id' => $treatmentCenter->province->id,
                        'name' => $treatmentCenter->province->name,
                        'slug' => $treatmentCenter->province->slug
                    ] : null,
                    'province_id' => $treatmentCenter->province_id,
                    'city' => $treatmentCenter->city ? [
                        'id' => $treatmentCenter->city->id,
                        'name' => $treatmentCenter->city->name,
                        'slug' => $treatmentCenter->city->slug
                    ] : null,
                    'city_id' => $treatmentCenter->city_id,
                    'slug' => $treatmentCenter->slug,
                ];
            })->values();

            return response()->json([
                'status' => 'success',
                'data' => $formattedTreatmentCenters,
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
     *       "province": { "id": 1, "name": "تهران", "slug": "تهران" },
     *       "province_id": 1,
     *       "city": { "id": 2, "name": "تهران", "slug": "تهران" },
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

            $imagingCenters = MedicalCenter::where('is_active', true)
                ->where('type', 'imaging_center')
                ->whereNotIn('type', ['policlinic'])
                ->withCount('doctor')
                ->with([
                    'province' => fn ($query) => $query->select('id', 'name', 'slug'),
                    'city' => fn ($query) => $query->select('id', 'name', 'slug')
                ])
                ->select('id', 'name', 'address', 'province_id', 'city_id', 'slug')
                ->when($limit !== null, function ($query) use ($limit) {
                    return $query->limit($limit);
                })
                ->orderBy('id')
                ->get();

            $formattedImagingCenters = $imagingCenters->map(function ($imagingCenter) {
                return [
                    'id' => $imagingCenter->id,
                    'name' => $imagingCenter->name,
                    'address' => $imagingCenter->address,
                    'doctor_count' => $imagingCenter->doctor_count,
                    'province' => $imagingCenter->province ? [
                        'id' => $imagingCenter->province->id,
                        'name' => $imagingCenter->province->name,
                        'slug' => $imagingCenter->province->slug
                    ] : null,
                    'province_id' => $imagingCenter->province_id,
                    'city' => $imagingCenter->city ? [
                        'id' => $imagingCenter->city->id,
                        'name' => $imagingCenter->city->name,
                        'slug' => $imagingCenter->city->slug
                    ] : null,
                    'city_id' => $imagingCenter->city_id,
                    'slug' => $imagingCenter->slug,
                ];
            })->values();

            return response()->json([
                'status' => 'success',
                'data' => $formattedImagingCenters,
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
     *       "province": { "id": 1, "name": "تهران", "slug": "تهران" },
     *       "province_id": 1,
     *       "city": { "id": 2, "name": "تهران", "slug": "تهران" },
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

            $hospitals = MedicalCenter::where('is_active', true)
                ->where('type', 'hospital')
                ->whereNotIn('type', ['policlinic'])
                ->withCount('doctor')
                ->with([
                    'province' => fn ($query) => $query->select('id', 'name', 'slug'),
                    'city' => fn ($query) => $query->select('id', 'name', 'slug')
                ])
                ->select('id', 'name', 'address', 'province_id', 'city_id', 'slug')
                ->when($limit !== null, function ($query) use ($limit) {
                    return $query->limit($limit);
                })
                ->orderBy('id')
                ->get();

            $formattedHospitals = $hospitals->map(function ($hospital) {
                return [
                    'id' => $hospital->id,
                    'name' => $hospital->name,
                    'address' => $hospital->address,
                    'doctor_count' => $hospital->doctor_count,
                    'province' => $hospital->province ? [
                        'id' => $hospital->province->id,
                        'name' => $hospital->province->name,
                        'slug' => $hospital->province->slug
                    ] : null,
                    'province_id' => $hospital->province_id,
                    'city' => $hospital->city ? [
                        'id' => $hospital->city->id,
                        'name' => $hospital->city->name,
                        'slug' => $hospital->city->slug
                    ] : null,
                    'city_id' => $hospital->city_id,
                    'slug' => $hospital->slug,
                ];
            })->values();

            return response()->json([
                'status' => 'success',
                'data' => $formattedHospitals,
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
     *       "province": { "id": 1, "name": "تهران", "slug": "تهران" },
     *       "province_id": 1,
     *       "city": { "id": 2, "name": "تهران", "slug": "تهران" },
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

            $laboratories = MedicalCenter::where('is_active', true)
                ->where('type', 'laboratory')
                ->whereNotIn('type', ['policlinic'])
                ->withCount('doctor')
                ->with([
                    'province' => fn ($query) => $query->select('id', 'name', 'slug'),
                    'city' => fn ($query) => $query->select('id', 'name', 'slug')
                ])
                ->select('id', 'name', 'address', 'province_id', 'city_id', 'slug')
                ->when($limit !== null, function ($query) use ($limit) {
                    return $query->limit($limit);
                })
                ->orderBy('id')
                ->get();

            $formattedLaboratories = $laboratories->map(function ($laboratory) {
                return [
                    'id' => $laboratory->id,
                    'name' => $laboratory->name,
                    'address' => $laboratory->address,
                    'doctor_count' => $laboratory->doctor_count,
                    'province' => $laboratory->province ? [
                        'id' => $laboratory->province->id,
                        'name' => $laboratory->province->name,
                        'slug' => $laboratory->province->slug
                    ] : null,
                    'province_id' => $laboratory->province_id,
                    'city' => $laboratory->city ? [
                        'id' => $laboratory->city->id,
                        'name' => $laboratory->city->name,
                        'slug' => $laboratory->city->slug
                    ] : null,
                    'city_id' => $laboratory->city_id,
                    'slug' => $laboratory->slug,
                ];
            })->values();

            return response()->json([
                'status' => 'success',
                'data' => $formattedLaboratories,
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
     * گرفتن لیست استان‌های دارای مراکز درمانی
     *
     * @queryParam limit integer تعداد آیتم‌ها (اختیاری، پیش‌فرض 10)
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "province_id": 1,
     *       "province_name": "کردستان",
     *       "province_slug": "کردستان",
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
            $limit = max(1, (int) $request->input('limit', 10));

            $provinces = Zone::where('level', 1)
                ->whereHas('medicalCenters', fn ($q) => $q->where('is_active', true)->whereNotIn('type', ['policlinic']))
                ->with([
                    'children' => fn ($query) => $query->withCount([
                        'medicalCenters as centers_count' => fn ($q) => $q->where('is_active', true)->whereNotIn('type', ['policlinic'])
                    ])->select('id', 'name', 'slug')
                ])
                ->select('id', 'name', 'slug')
                ->take($limit)
                ->get();

            $formattedProvinces = $provinces->map(function ($province) {
                $totalCenters = $province->children->sum('centers_count');
                return [
                    'province_id' => $province->id,
                    'province_name' => $province->name,
                    'province_slug' => $province->slug,
                    'centers_count' => $totalCenters,
                ];
            })->values();

            return response()->json([
                'status' => 'success',
                'data' => $formattedProvinces,
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
     *       "province": { "id": 1, "name": "کردستان", "slug": "کردستان" },
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
                ->whereNotIn('type', ['policlinic'])
                ->with([
                    'province' => fn ($q) => $q->select('id', 'name', 'slug')->where('level', 1)
                ])
                ->select('id', 'name', 'type', 'province_id', 'avatar', 'slug')
                ->inRandomOrder();

            $centers = $query->get()->map(function ($center) {
                return [
                    'id' => $center->id,
                    'name' => $center->name,
                    'type' => $center->type,
                    'province' => $center->province ? [
                        'id' => $center->province->id,
                        'name' => $center->province->name,
                        'slug' => $center->province->slug
                    ] : null,
                    'avatar' => $center->avatar ? Storage::url($center->avatar) : url('/default-avatar.png'),
                    'slug' => $center->slug,
                ];
            });

            $formattedCenters = $limit > 0 ? $centers->take($limit)->values() : $centers->values();

            return response()->json([
                'status' => 'success',
                'data' => $formattedCenters,
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
 * لیست همه مراکز درمانی فعال
 *
 * @param Request $request
 * @return JsonResponse
 *
 * @queryParam province_slug string اسلاگ استان (اختیاری)
 * @queryParam city_slug string اسلاگ شهر (اختیاری)
 * @queryParam center_type string نوع مرکز درمانی (اختیاری)
 * @queryParam tariff_type string نوع تعرفه (اختیاری)
 * @queryParam specialty_slug string اسلاگ تخصص (اختیاری، می‌تواند از specialty_slugs هم استفاده شود)
 * @queryParam specialty_slugs array اسلاگ‌های تخصص (اختیاری، جایگزین specialty_slug)
 * @queryParam service_slug string اسلاگ خدمت (اختیاری، می‌تواند از service_slugs هم استفاده شود)
 * @queryParam service_slugs array اسلاگ‌های خدمت (اختیاری، جایگزین service_slug)
 * @queryParam insurance_slug string اسلاگ بیمه (اختیاری، می‌تواند از insurance_slugs هم استفاده شود)
 * @queryParam insurance_slugs array اسلاگ‌های بیمه (اختیاری، جایگزین insurance_slug)
 * @queryParam sort_by string فیلد مرتب‌سازی (اختیاری)
 * @queryParam sort_dir string جهت مرتب‌سازی (asc/desc، اختیاری)
 * @queryParam per_page integer تعداد آیتم در هر صفحه (پیش‌فرض: 20)
 * @response 200 {
 *   "status": "success",
 *   "message": "عملیات با موفقیت انجام شد",
 *   "data": {
 *     "medical_centers": [
 *       {
 *         "id": 1,
 *         "name": "کلینیک نمونه",
 *         "slug": "کلینیک-نمونه",
 *         "type": "clinic",
 *         "province_id": 1,
 *         "province": {
 *           "id": 1,
 *           "name": "تهران",
 *           "slug": "تهران"
 *         },
 *         "city_id": 1,
 *         "city": {
 *           "id": 1,
 *           "name": "تهران",
 *           "slug": "تهران"
 *         },
 *         "address": "خیابان نمونه"
 *       }
 *     ],
 *     "zones": {
 *       "provinces": [
 *         {
 *           "id": 1,
 *           "name": "تهران",
 *           "slug": "تهران",
 *           "province_id": 1,
 *           "cities": ""
 *         }
 *       ],
 *       "cities": [
 *         {
 *           "id": 1,
 *           "name": "تهران",
 *           "slug": "تهران",
 *           "province_id": 1,
 *           "cities": ""
 *         }
 *       ]
 *     },
 *     "specialties": [
 *       {
 *         "id": 1,
 *         "name": "متخصص قلب",
 *         "slug": "متخصص-قلب"
 *       }
 *     ],
 *     "insurances": [
 *       {
 *         "id": 1,
 *         "name": "بیمه تامین اجتماعی",
 *         "slug": "بیمه-تامین-اجتماعی"
 *       }
 *     ],
 *     "services": [
 *       {
 *         "id": 1,
 *         "name": "نوبت‌دهی مطب",
 *         "slug": "نوبت-دهی-مطب",
 *         "description": "توضیحات خدمت"
 *       }
 *     ],
 *     "center_types": {
 *       "clinic": "کلینیک",
 *       "hospital": "بیمارستان",
 *       "treatment_centers": "درمانگاه",
 *       "imaging_center": "تصویربرداری",
 *       "laboratory": "آزمایشگاه",
 *       "pharmacy": "داروخانه",
 *       "policlinic": "پلی‌کلینیک"
 *     },
 *     "tariff_types": {
 *       "public": "دولتی",
 *       "private": "خصوصی"
 *     }
 *   },
 *   "pagination": {
 *     "current_page": "1",
 *     "last_page": "2",
 *     "per_page": "20",
 *     "total": "30"
 *   }
 * }
 * @response 404 {
 *   "status": "error",
 *   "message": "استان، شهر، تخصص، خدمت یا بیمه یافت نشد.",
 *   "data": null
 * }
 * @response 422 {
 *   "status": "error",
 *   "message": "خطای اعتبارسنجی ورودی‌ها",
 *   "errors": {},
 *   "data": null
 * }
 * @response 500 {
 *   "status": "error",
 *   "message": "خطای سرور",
 *   "data": null
 * }
 */
public function list(Request $request)
{
    try {
        // اعتبارسنجی ورودی‌ها
        $validated = $request->validate([
            'province_slug'     => 'nullable|exists:zone,slug',
            'city_slug'         => 'nullable|exists:zone,slug',
            'center_type'       => 'nullable|string|in:clinic,hospital,treatment_centers,imaging_center,laboratory,pharmacy,policlinic',
            'tariff_type'       => 'nullable|string|in:public,private',
            'specialty_slug'    => 'nullable|exists:specialties,slug',
            'specialty_slugs'   => 'nullable|array',
            'specialty_slugs.*' => 'exists:specialties,slug',
            'service_slug'      => 'nullable|exists:services,slug',
            'service_slugs'     => 'nullable|array',
            'service_slugs.*'   => 'exists:services,slug',
            'insurance_slug'    => 'nullable|exists:insurances,slug',
            'insurance_slugs'   => 'nullable|array',
            'insurance_slugs.*' => 'exists:insurances,slug',
            'sort_by'           => 'nullable|string',
            'sort_dir'          => 'nullable|in:asc,desc',
            'per_page'          => 'nullable|integer|min:1|max:100',
        ], [
            'province_slug.exists'     => 'استان انتخاب‌شده وجود ندارد.',
            'city_slug.exists'         => 'شهر انتخاب‌شده وجود ندارد.',
            'center_type.in'           => 'نوع مرکز درمانی باید یکی از مقادیر مجاز باشد.',
            'tariff_type.in'           => 'نوع تعرفه باید یکی از مقادیر مجاز باشد.',
            'specialty_slug.exists'    => 'تخصص انتخاب‌شده وجود ندارد.',
            'specialty_slugs.*.exists' => 'یک یا چند تخصص انتخاب‌شده وجود ندارد.',
            'service_slug.exists'      => 'خدمت انتخاب‌شده وجود ندارد.',
            'service_slugs.*.exists'   => 'یک یا چند خدمت انتخاب‌شده وجود ندارد.',
            'insurance_slug.exists'    => 'بیمه انتخاب‌شده وجود ندارد.',
            'insurance_slugs.*.exists' => 'یک یا چند بیمه انتخاب‌شده وجود ندارد.',
            'sort_dir.in'              => 'جهت مرتب‌سازی باید asc یا desc باشد.',
            'per_page.integer'         => 'تعداد آیتم در هر صفحه باید یک عدد صحیح باشد.',
            'per_page.min'             => 'تعداد آیتم در هر صفحه باید حداقل 1 باشد.',
            'per_page.max'             => 'تعداد آیتم در هر صفحه نمی‌تواند بیشتر از 100 باشد.',
        ]);

        $perPage = (int) $request->input('per_page', 20);
        $query = MedicalCenter::where('is_active', true)
            ->whereNotIn('type', ['policlinic'])
            ->with([
                'province' => fn ($q) => $q->select('id', 'name', 'slug'),
                'city' => fn ($q) => $q->select('id', 'name', 'slug'),
            ]);

        // پیدا کردن province_id از province_slug
        $provinceId = null;
        if ($request->filled('province_slug')) {
            $province = Zone::where('level', 1)->where('slug', $request->input('province_slug'))->first();
            if (!$province) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'استان یافت نشد.',
                    'data'    => null,
                ], 404);
            }
            $provinceId = $province->id;
        }

        // پیدا کردن city_id از city_slug
        $cityId = null;
        if ($request->filled('city_slug')) {
            $city = Zone::where('level', 2)->where('slug', $request->input('city_slug'))->first();
            if (!$city) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'شهر یافت نشد.',
                    'data'    => null,
                ], 404);
            }
            $cityId = $city->id;
        }

        // پیدا کردن specialty_ids از specialty_slug یا specialty_slugs
        $specialtyIds = null;
        if ($request->filled('specialty_slug') || $request->filled('specialty_slugs')) {
            $specialtySlugs = $request->input('specialty_slugs', [$request->input('specialty_slug')]);
            $specialtySlugs = array_filter($specialtySlugs);
            if (!empty($specialtySlugs)) {
                $specialtyIds = Specialty::whereIn('slug', $specialtySlugs)->pluck('id')->toArray();
                if (empty($specialtyIds)) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'تخصص یافت نشد.',
                        'data'    => null,
                    ], 404);
                }
            }
        }

        // پیدا کردن service_ids از service_slug یا service_slugs
        $serviceIds = null;
        if ($request->filled('service_slug') || $request->filled('service_slugs')) {
            $serviceSlugs = $request->input('service_slugs', [$request->input('service_slug')]);
            $serviceSlugs = array_filter($serviceSlugs);
            if (!empty($serviceSlugs)) {
                $serviceIds = Service::whereIn('slug', $serviceSlugs)->pluck('id')->toArray();
                if (empty($serviceIds)) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'خدمت یافت نشد.',
                        'data'    => null,
                    ], 404);
                }
            }
        }

        // پیدا کردن insurance_ids از insurance_slug یا insurance_slugs
        $insuranceIds = null;
        if ($request->filled('insurance_slug') || $request->filled('insurance_slugs')) {
            $insuranceSlugs = $request->input('insurance_slugs', [$request->input('insurance_slug')]);
            $insuranceSlugs = array_filter($insuranceSlugs);
            if (!empty($insuranceSlugs)) {
                $insuranceIds = Insurance::whereIn('slug', $insuranceSlugs)->pluck('id')->toArray();
                if (empty($insuranceIds)) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'بیمه یافت نشد.',
                        'data'    => null,
                    ], 404);
                }
            }
        }

        // فیلترها
        $filters = [
            'province_id' => $provinceId,
            'city_id' => $cityId,
            'center_type' => $request->input('center_type'),
            'tariff_type' => $request->input('tariff_type'),
            'specialty_ids' => $specialtyIds,
            'service_ids' => $serviceIds,
            'insurance_ids' => $insuranceIds,
        ];

        // اعمال فیلترها با استفاده از scopeFilter
        $query->filter($filters);

        // مرتب‌سازی با استفاده از scopeSort
        if ($request->filled('sort_by')) {
            $sortBy = $request->input('sort_by');
            $sortDir = $request->input('sort_dir', 'desc');
            $query->sort($sortBy, $sortDir);
        }

        // دریافت مراکز درمانی
        $centers = $query->paginate($perPage);

        // Provinces (level 1)
        $provinces = Zone::where('level', 1)
            ->select('id', 'name', 'slug')
            ->get()
            ->map(function ($province) {
                return [
                    'id' => $province->id,
                    'name' => $province->name,
                    'slug' => $province->slug,
                    'province_id' => $province->id,
                    'cities' => '',
                ];
            });

        // Cities (level 2)
        $cities = Zone::where('level', 2)
            ->select('id', 'name', 'slug', 'parent_id as province_id')
            ->get()
            ->map(function ($city) {
                return [
                    'id' => $city->id,
                    'name' => $city->name,
                    'slug' => $city->slug,
                    'province_id' => $city->province_id,
                    'cities' => '',
                ];
            });

        // Specialties
        $specialties = Specialty::select('id', 'name', 'slug')->get();

        // Insurances
        $insurances = Insurance::select('id', 'name', 'slug')->get();

        // Services
        $services = Service::select('id', 'name', 'slug', 'description')->get();

        // Center types
        $center_types = [
            'clinic' => 'کلینیک',
            'hospital' => 'بیمارستان',
            'treatment_centers' => 'درمانگاه',
            'imaging_center' => 'تصویربرداری',
            'laboratory' => 'آزمایشگاه',
            'pharmacy' => 'داروخانه',
            'policlinic' => 'پلی‌کلینیک',
        ];

        // Tariff types
        $tariff_types = [
            'public' => 'دولتی',
            'private' => 'خصوصی',
        ];

        // ساختار خروجی
        return response()->json([
            'status' => 'success',
            'message' => 'عملیات با موفقیت انجام شد',
            'data' => [
                'medical_centers' => $centers->getCollection()->map(function ($center) {
                    return [
                        'id' => $center->id,
                        'name' => $center->name,
                        'slug' => $center->slug,
                        'type' => $center->type,
                        'province_id' => $center->province_id,
                        'province' => $center->province ? [
                            'id' => $center->province->id,
                            'name' => $center->province->name,
                            'slug' => $center->province->slug,
                        ] : null,
                        'city_id' => $center->city_id,
                        'city' => $center->city ? [
                            'id' => $center->city->id,
                            'name' => $center->city->name,
                            'slug' => $center->city->slug,
                        ] : null,
                        'address' => $center->address,
                    ];
                })->values(),
                'zones' => [
                    'provinces' => $provinces,
                    'cities' => $cities,
                ],
                'specialties' => $specialties->map(function ($specialty) {
                    return [
                        'id' => $specialty->id,
                        'name' => $specialty->name,
                        'slug' => $specialty->slug,
                    ];
                })->values(),
                'insurances' => $insurances->map(function ($insurance) {
                    return [
                        'id' => $insurance->id,
                        'name' => $insurance->name,
                        'slug' => $insurance->slug,
                    ];
                })->values(),
                'services' => $services->map(function ($service) {
                    return [
                        'id' => $service->id,
                        'name' => $service->name,
                        'slug' => $service->slug,
                        'description' => $service->description,
                    ];
                })->values(),
                'center_types' => $center_types,
                'tariff_types' => $tariff_types,
            ],
            'pagination' => [
                'current_page' => (string) $centers->currentPage(),
                'last_page' => (string) $centers->lastPage(),
                'per_page' => (string) $centers->perPage(),
                'total' => (string) $centers->total(),
            ],
        ], 200);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status'  => 'error',
            'message' => 'خطای اعتبارسنجی ورودی‌ها',
            'errors'  => $e->errors(),
            'data'    => null,
        ], 422);
    } catch (\Exception $e) {
        Log::error('Medical center listing error: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'status'  => 'error',
            'message' => 'خطای سرور: ' . $e->getMessage(),
            'data'    => null,
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
 *       "slug": "قلب-و-عروق",
 *       "description": "تخصص در درمان بیماری‌های قلب و عروق",
 *       "center_count": 15,
 *       "provinces": [
 *         {
 *           "id": 1,
 *           "name": "تهران",
 *           "slug": "تهران"
 *         }
 *       ],
 *       "cities": [
 *         {
 *           "id": 2,
 *           "name": "تهران",
 *           "slug": "تهران"
 *         }
 *       ]
 *     }
 *   ],
 *   "filters": {
 *     "center_type": null,
 *     "total_centers": 100,
 *     "total_specialties": 10
 *   }
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
            $query = MedicalCenter::where('is_active', true)
                ->whereNotIn('type', ['policlinic'])
                ->whereNotNull('specialty_ids')
                ->where('specialty_ids', '!=', '[]')
                ->with([
                    'province' => fn ($query) => $query->select('id', 'name', 'slug'),
                    'city' => fn ($query) => $query->select('id', 'name', 'slug')
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
                    'data' => [],
                    'filters' => [
                        'center_type' => $centerType,
                        'total_centers' => $medicalCenters->count(),
                    ]
                ], 200);
            }

            // گرفتن اطلاعات تخصص‌ها
            $specialties = Specialty::whereIn('id', $uniqueSpecialtyIds)
                ->where('status', true)
                ->select('id', 'name', 'description', 'slug')
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
                                'name' => $center->province->name,
                                'slug' => $center->province->slug
                            ];
                        }
                        if ($center->city) {
                            $cities[$center->city_id] = [
                                'id' => $center->city_id,
                                'name' => $center->city->name,
                                'slug' => $center->city->slug
                            ];
                        }
                    }
                }

                return [
                    'id' => $specialty->id,
                    'name' => $specialty->name,
                    'slug' => $specialty->slug,
                    'description' => $specialty->description,
                    'center_count' => $centerCount,
                    'provinces' => array_values($provinces),
                    'cities' => array_values($cities),
                ];
            })->values();

            // اعمال محدودیت تعداد اگر مشخص شده باشد
            if ($limit !== null) {
                $formattedSpecialties = $formattedSpecialties->take($limit);
            }

            return response()->json([
                'status' => 'success',
                'data' => $formattedSpecialties,
                'filters' => [
                    'center_type' => $centerType,
                    'total_centers' => $medicalCenters->count(),
                    'total_specialties' => $formattedSpecialties->count(),
                ]
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
 *       "slug": "قلب-و-عروق",
 *       "description": "تخصص در درمان بیماری‌های قلب و عروق",
 *       "center_count": 8,
 *       "provinces": [
 *         {
 *           "id": 1,
 *           "name": "تهران",
 *           "slug": "تهران"
 *         }
 *       ],
 *       "cities": [
 *         {
 *           "id": 2,
 *           "name": "تهران",
 *           "slug": "تهران"
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
            $query = MedicalCenter::where('is_active', true)
                ->where('type', 'treatment_centers')
                ->whereNotIn('type', ['policlinic'])
                ->whereNotNull('specialty_ids')
                ->where('specialty_ids', '!=', '[]')
                ->with([
                    'province' => fn ($query) => $query->select('id', 'name', 'slug'),
                    'city' => fn ($query) => $query->select('id', 'name', 'slug')
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
                    'data' => [],
                ], 200);
            }

            // گرفتن اطلاعات تخصص‌ها
            $specialties = Specialty::whereIn('id', $uniqueSpecialtyIds)
                ->where('status', true)
                ->select('id', 'name', 'description', 'slug')
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
                                'name' => $center->province->name,
                                'slug' => $center->province->slug
                            ];
                        }
                        if ($center->city) {
                            $cities[$center->city_id] = [
                                'id' => $center->city_id,
                                'name' => $center->city->name,
                                'slug' => $center->city->slug
                            ];
                        }
                    }
                }

                return [
                    'id' => $specialty->id,
                    'name' => $specialty->name,
                    'slug' => $specialty->slug,
                    'description' => $specialty->description,
                    'center_count' => $centerCount,
                    'provinces' => array_values($provinces),
                    'cities' => array_values($cities),
                ];
            })->values();

            // اعمال محدودیت تعداد اگر مشخص شده باشد
            if ($limit !== null) {
                $formattedSpecialties = $formattedSpecialties->take($limit);
            }

            return response()->json([
                'status' => 'success',
                'data' => $formattedSpecialties,
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

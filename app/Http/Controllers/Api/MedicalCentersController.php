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
     *       "province": "تهران"
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
                ->select('id', 'name', 'address', 'province_id', 'city_id')
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
     *       "province": "تهران"
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
                ->select('id', 'name', 'address', 'province_id', 'city_id')
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
     *       "province": "تهران"
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
                ->select('id', 'name', 'address', 'province_id', 'city_id')
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
     *       "province": "تهران"
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
                ->select('id', 'name', 'address', 'province_id', 'city_id')
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
     *       "province": "تهران"
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
                ->select('id', 'name', 'address', 'province_id', 'city_id')
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
 *       "avatar": "http://example.com/images/center-avatar.png"
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
                ->select('id', 'name', 'type', 'province_id', 'avatar')
                ->inRandomOrder();

            $centers = $query->get()->map(function ($center) {
                return [
                    'id'       => $center->id,
                    'name'     => $center->name,
                    'type'     => $center->type,
                    'province' => $center->province ? $center->province->name : null,
                    'avatar'   => $center->avatar ? Storage::url($center->avatar) : url('/default-avatar.png'),
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




    public function list(Request $request)
    {
        try {
            // لاگ درخواست
            Log::info('درخواست لیست مراکز درمانی', ['params' => $request->all()]);

            // اعتبارسنجی ورودی‌ها
            $validator = Validator::make($request->all(), [
                'province_id' => 'nullable|exists:zone,id',
                'city_id' => 'nullable|exists:zone,id',
                'center_type' => 'nullable|in:hospital,treatment_centers,clinic,imaging_center,laboratory,pharmacy,policlinic',
                'specialty_ids' => 'sometimes|nullable|string|exists:specialties,id',
                'insurance_ids' => 'sometimes|nullable|string|exists:insurances,id',
                'service_ids' => 'sometimes|nullable|string|exists:services,id',
                'tariff_type' => 'nullable|in:governmental,special,else',
                'sort_by' => 'nullable|in:average_rating,reviews_count',
                'sort_direction' => 'nullable|in:asc,desc',
                'per_page' => 'nullable|integer|min:1|max:100',
            ], [
                'province_id.exists' => 'استان انتخاب‌شده معتبر نیست.',
                'city_id.exists' => 'شهر انتخاب‌شده معتبر نیست.',
                'center_type.in' => 'نوع مرکز معتبر نیست.',
                'specialty_ids.exists' => 'تخصص انتخاب‌شده معتبر نیست.',
                'insurance_ids.exists' => 'بیمه انتخاب‌شده معتبر نیست.',
                'service_ids.exists' => 'خدمت انتخاب‌شده معتبر نیست.',
                'tariff_type.in' => 'نوع تعرفه معتبر نیست.',
                'sort_by.in' => 'معیار مرتب‌سازی معتبر نیست.',
                'sort_direction.in' => 'جهت مرتب‌سازی معتبر نیست.',
                'per_page.integer' => 'تعداد در هر صفحه باید عدد باشد.',
            ]);

            if ($validator->fails()) {
                Log::warning('خطا در اعتبارسنجی ورودی‌ها', ['errors' => $validator->errors()]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'خطا در اعتبارسنجی',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // فیلترها
            $filters = [
                'province_id' => $request->input('province_id'),
                'city_id' => $request->input('city_id'),
                'center_type' => $request->input('center_type'),
                'specialty_ids' => $request->has('specialty_ids') ? (is_array($request->input('specialty_ids')) ? $request->input('specialty_ids') : [$request->input('specialty_ids')]) : null,
                'insurance_ids' => $request->has('insurance_ids') ? (is_array($request->input('insurance_ids')) ? $request->input('insurance_ids') : [$request->input('insurance_ids')]) : null,
                'service_ids' => $request->has('service_ids') ? (is_array($request->input('service_ids')) ? $request->input('service_ids') : [$request->input('service_ids')]) : null,
                'tariff_type' => $request->input('tariff_type'),
            ];

            // لاگ فیلترها
            Log::info('فیلترهای اعمال‌شده', ['filters' => $filters]);

            // مرتب‌سازی
            $sortBy = $request->input('sort_by', 'average_rating');
            $sortDirection = $request->input('sort_direction', 'desc');
            $perPage = $request->input('per_page', 10);

            // کوئری برای مراکز درمانی
            $query = MedicalCenter::query()
                ->active()
                ->with(['province', 'city', 'doctors'])
                ->filter($filters)
                ->orderBy($sortBy, $sortDirection);

            // لاگ تعداد مراکز
            Log::info('تعداد مراکز درمانی قبل از صفحه‌بندی', ['count' => $query->count()]);

            // صفحه‌بندی
            $medicalCenters = $query->paginate($perPage);

            // دریافت لیست استان‌ها (با کش)
            $provinces = Cache::remember('medical_centers_provinces', 1440, function () {
                $provinces = Zone::where('level', 1)
                    ->where('status', 1)
                    ->select('id', 'name')
                    ->orderBy('name')
                    ->get();
                Log::info('تعداد استان‌ها', ['count' => $provinces->count()]);
                return $provinces;
            });

            // دریافت لیست شهرها (با کش)
            $cities = Cache::remember('medical_centers_cities', 1440, function () {
                $cities = Zone::where('level', 2)
                    ->where('status', 1)
                    ->select('id', 'name', 'parent_id as province_id')
                    ->orderBy('name')
                    ->get();
                Log::info('تعداد شهرها', ['count' => $cities->count()]);
                return $cities;
            });

            // دریافت لیست تخصص‌ها (با کش)
            $specialties = Cache::remember('medical_centers_specialties', 1440, function () {
                $specialties = Specialty::where('status', 1)
                    ->select('id', 'name')
                    ->orderBy('name')
                    ->get();
                Log::info('تعداد تخصص‌ها', ['count' => $specialties->count()]);
                return $specialties;
            });

            // دریافت لیست بیمه‌ها (با کش)
            $insurances = Cache::remember('medical_centers_insurances', 1440, function () {
                $insurances = Insurance::where('status', 1)
                    ->select('id', 'name')
                    ->orderBy('name')
                    ->get();
                Log::info('تعداد بیمه‌ها', ['count' => $insurances->count()]);
                return $insurances;
            });

            // دریافت لیست خدمات (با کش)
            $services = Cache::remember('medical_centers_services', 1440, function () {
                $services = Service::where('status', true)
                    ->select('id', 'name')
                    ->orderBy('name')
                    ->get();
                Log::info('تعداد خدمات', ['count' => $services->count()]);
                return $services;
            });

            // لیست انواع مراکز
            $centerTypes = [
                'hospital' => 'بیمارستان',
                'treatment_centers' => 'مراکز درمانی',
                'clinic' => 'کلینیک',
                'imaging_center' => 'مرکز تصویربرداری',
                'laboratory' => 'آزمایشگاه',
                'pharmacy' => 'داروخانه',
                'policlinic' => 'پلی‌کلینیک',
            ];

            // لیست انواع تعرفه‌ها
            $tariffTypes = [
                'governmental' => 'دولتی',
                'special' => 'ویژه',
                'else' => 'سایر',
            ];

            // پاسخ
            return response()->json([
                'status' => 'success',
                'message' => 'لیست مراکز درمانی و اطلاعات فیلترها با موفقیت دریافت شد.',
                'data' => [
                    'medical_centers' => MedicalCenterResource::collection($medicalCenters),
                    'zones' => [
                        'provinces' => ZoneResource::collection($provinces),
                        'cities' => ZoneResource::collection($cities),
                    ],
                    'specialties' => SpecialtyResource::collection($specialties),
                    'insurances' => InsuranceResource::collection($insurances),
                    'services' => ServiceResource::collection($services),
                    'center_types' => $centerTypes,
                    'tariff_types' => $tariffTypes,
                ],
                'pagination' => [
                    'current_page' => $medicalCenters->currentPage(),
                    'last_page' => $medicalCenters->lastPage(),
                    'per_page' => $medicalCenters->perPage(),
                    'total' => $medicalCenters->total(),
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('خطا در دریافت لیست مراکز درمانی و اطلاعات فیلترها: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'خطایی در سرور رخ داد. لطفاً دوباره تلاش کنید.',
            ], 500);
        }
    }

    public function getProfile($slug)
    {
        try {
            $medicalCenter = MedicalCenter::where('slug', $slug)
                ->active()
                ->with(['province', 'city', 'doctors'])
                ->firstOrFail();

            return response()->json([
                'status' => 'success',
                'message' => 'اطلاعات مرکز درمانی با موفقیت دریافت شد.',
                'data' => new MedicalCenterResource($medicalCenter),
            ], 200);
        } catch (\Exception $e) {
            Log::error('خطا در دریافت پروفایل مرکز درمانی: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'مرکز درمانی یافت نشد یا خطایی رخ داد.',
            ], 404);
        }
    }
}

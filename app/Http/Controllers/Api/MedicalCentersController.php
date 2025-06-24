<?php

namespace App\Http\Controllers\Api;

use App\Models\Zone;
use App\Models\Clinic;
use App\Models\Hospital;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use App\Models\ImagingCenter;
use App\Models\MedicalCenter;
use App\Models\TreatmentCenter;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\ZoneResource;
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

            $clinics = Clinic::where('is_active', 1)
                ->withCount('doctor')
                ->with(['province' => fn ($query) => $query->select('id', 'name')])
                ->select('id', 'name', 'address', 'province_id')
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

            $treatmentCenters = TreatmentCenter::where('is_active', 1)
                ->withCount('doctor')
                ->with(['province' => fn ($query) => $query->select('id', 'name')])
                ->select('id', 'name', 'address', 'province_id')
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

            $imagingCenters = ImagingCenter::where('is_active', 1)
                ->withCount('doctor')
                ->with(['province' => fn ($query) => $query->select('id', 'name')])
                ->select('id', 'name', 'address', 'province_id')
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

            $hospitals = Hospital::where('is_active', 1)
                ->withCount('doctor')
                ->with(['province' => fn ($query) => $query->select('id', 'name')])
                ->select('id', 'name', 'address', 'province_id')
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

            $laboratories = Laboratory::where('is_active', 1)
                ->withCount('doctor')
                ->with(['province' => fn ($query) => $query->select('id', 'name')])
                ->select('id', 'name', 'address', 'province_id')
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
     * این متد لیستی از استان‌ها را با تعداد کل مراکز درمانی فعال در هر استان برمی‌گرداند.
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
            $limit = $request->has('limit') ? (int) $request->input('limit') : 10;

            $provinces = Zone::where('level', 1) // فقط استان‌ها
                ->where(function ($query) {
                    $query->whereHas('children.clinics', fn ($q) => $q->where('is_active', 1))
                          ->orWhereHas('children.treatmentCenters', fn ($q) => $q->where('is_active', 1))
                          ->orWhereHas('children.imagingCenters', fn ($q) => $q->where('is_active', 1))
                          ->orWhereHas('children.hospitals', fn ($q) => $q->where('is_active', 1))
                          ->orWhereHas('children.laboratories', fn ($q) => $q->where('is_active', 1));
                })
                ->with(['children' => fn ($query) => $query->withCount([
                    'clinics as clinics_count' => fn ($q) => $q->where('is_active', 1),
                    'treatmentCenters as treatment_centers_count' => fn ($q) => $q->where('is_active', 1),
                    'imagingCenters as imaging_centers_count' => fn ($q) => $q->where('is_active', 1),
                    'hospitals as hospitals_count' => fn ($q) => $q->where('is_active', 1),
                    'laboratories as laboratories_count' => fn ($q) => $q->where('is_active', 1),
                ])])
                ->select('id', 'name')
                ->limit($limit)
                ->get();

            $formattedProvinces = $provinces->map(function ($province) {
                $totalCenters = $province->children->sum(function ($city) {
                    return $city->clinics_count +
                           $city->treatment_centers_count +
                           $city->imaging_centers_count +
                           $city->hospitals_count +
                           $city->laboratories_count;
                });

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
            Log::error('GetCitiesWithCenters - Error: ' . $e->getMessage());
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
 * این متد لیستی از همه مراکز درمانی (کلینیک، درمانگاه، بیمارستان، مراکز تصویربرداری، آزمایشگاه) را به‌صورت رندوم برمی‌گرداند.
 * پیش‌فرض ۵ مرکز رندوم، و با پارامتر limit می‌توان تعداد را مشخص کرد. اگر limit خالی باشد، همه را برمی‌گرداند.
 *
 * @queryParam limit integer تعداد آیتم‌ها (اختیاری، پیش‌فرض 5، اگر 0 یا خالی باشد همه برگردانده می‌شود)
 * @response 200 {
 *   "status": "success",
 *   "data": [
 *     {
 *       "id": 1,
 *       "name": "درمانگاه سعدی",
 *       "type": "treatment_center",
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
            $limit = $request->has('limit') ? (int) $request->input('limit') : 5;

            $centers = collect();

            $types = [
                ['model' => Clinic::class, 'type' => 'clinic'],
                ['model' => TreatmentCenter::class, 'type' => 'treatment_center'],
                ['model' => ImagingCenter::class, 'type' => 'imaging_center'],
                ['model' => Hospital::class, 'type' => 'hospital'],
                ['model' => Laboratory::class, 'type' => 'laboratory'],
            ];

            foreach ($types as $type) {
                $query = $type['model']::where('is_active', 1)
                    ->with(['province' => fn ($q) => $q->select('id', 'name')->where('level', 1)]) // استان‌ها
                    ->select('id', 'name', 'province_id', 'avatar') // province_id به‌جای city_id
                    ->inRandomOrder(); // رندوم کردن

                $centers = $centers->merge($query->get()->map(function ($center) use ($type) {
                    return [
                        'id'       => $center->id,
                        'name'     => $center->name,
                        'type'     => $type['type'],
                        'province' => $center->province ? $center->province->name : null, // اسم استان
                        'avatar'   => $center->avatar ? url($center->avatar) : url('/default-avatar.png'),
                    ];
                }));
            }

            // اعمال لیمیت یا برگرداندن همه
            if ($limit > 0) {
                $formattedCenters = $centers->take($limit)->values();
            } else {
                $formattedCenters = $centers->values(); // همه رو برگردون
            }

            return response()->json([
                'status' => 'success',
                'data'   => $formattedCenters,
            ], 200);
        } catch (\Exception $e) {
            Log::error('GetAllCenters - Error: ' . $e->getMessage());
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
            // اعتبارسنجی ورودی‌ها
            $validator = Validator::make($request->all(), [
                'province_id' => 'nullable|exists:zone,id',
                'city_id' => 'nullable|exists:zone,id',
                'center_type' => 'nullable|in:hospital,treatment_centers,clinic,imaging_center,laboratory,pharmacy,policlinic',
                'specialty_ids' => 'nullable|array',
                'specialty_ids.*' => 'exists:specialties,id',
                'insurance_ids' => 'nullable|array',
                'insurance_ids.*' => 'exists:insurances,id',
                'tariff_type' => 'nullable|in:governmental,special,else',
                'sort_by' => 'nullable|in:average_rating,reviews_count',
                'sort_direction' => 'nullable|in:asc,desc',
                'per_page' => 'nullable|integer|min:1|max:100',
            ], [
                'province_id.exists' => 'استان انتخاب‌شده معتبر نیست.',
                'city_id.exists' => 'شهر انتخاب‌شده معتبر نیست.',
                'center_type.in' => 'نوع مرکز معتبر نیست.',
                'specialty_ids.*.exists' => 'تخصص انتخاب‌شده معتبر نیست.',
                'insurance_ids.*.exists' => 'بیمه انتخاب‌شده معتبر نیست.',
                'tariff_type.in' => 'نوع تعرفه معتبر نیست.',
                'sort_by.in' => 'معیار مرتب‌سازی معتبر نیست.',
                'sort_direction.in' => 'جهت مرتب‌سازی معتبر نیست.',
                'per_page.integer' => 'تعداد در هر صفحه باید عدد باشد.',
            ]);

            if ($validator->fails()) {
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
                'specialty_ids' => $request->input('specialty_ids'),
                'insurance_ids' => $request->input('insurance_ids'),
                'tariff_type' => $request->input('tariff_type'),
            ];

            // مرتب‌سازی
            $sortBy = $request->input('sort_by', 'average_rating');
            $sortDirection = $request->input('sort_direction', 'desc');
            $perPage = $request->input('per_page', 10);

            // کوئری برای مراکز درمانی
            $query = MedicalCenter::query()
                ->active()
                ->with(['province', 'city', 'specialties', 'insurances', 'doctors'])
                ->filter($filters)
                ->sort($sortBy, $sortDirection);

            // صفحه‌بندی
            $medicalCenters = $query->paginate($perPage);

            // دریافت لیست استان‌ها و شهرها
            $provinces = Zone::provinces()
                ->with(['children' => function ($query) {
                    $query->cities()->whereHas('medicalCenters', function ($q) {
                        $q->active();
                    });
                }])
                ->whereHas('children.medicalCenters', function ($q) {
                    $q->active();
                })
                ->get();

            // پاسخ
            return response()->json([
                'status' => 'success',
                'message' => 'لیست مراکز درمانی و مناطق با موفقیت دریافت شد.',
                'data' => [
                    'medical_centers' => MedicalCenterResource::collection($medicalCenters),
                    'zones' => [
                        'provinces' => ZoneResource::collection($provinces),
                    ],
                ],
                'pagination' => [
                    'current_page' => $medicalCenters->currentPage(),
                    'last_page' => $medicalCenters->lastPage(),
                    'per_page' => $medicalCenters->perPage(),
                    'total' => $medicalCenters->total(),
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('خطا در دریافت لیست مراکز درمانی و مناطق: ' . $e->getMessage());
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
                ->with(['province', 'city', 'specialties', 'insurances', 'doctors'])
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

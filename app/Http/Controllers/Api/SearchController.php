<?php

namespace App\Http\Controllers\Api;

use App\Models\Doctor;
use App\Models\Specialty;
use Illuminate\Http\Request;
use App\Models\MedicalCenter;
use App\Models\FrequentSearch;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $searchText = trim($request->input('search_text', ''));
        $provinceId = $request->input('province_id');
        $cityId = $request->input('city_id');
        $limit = 15;

        $token = $request->bearerToken() ?: $request->cookie('auth_token');
        $userId = null;

        if ($token) {
            try {
                app(\App\Http\Middleware\JwtMiddleware::class)->handle($request, function () {});
                $user = \Illuminate\Support\Facades\Auth::user();
                // Validate that the user actually exists in the database
                if ($user && \App\Models\User::find($user->id)) {
                    $userId = $user->id;
                    \Illuminate\Support\Facades\Log::info("Search request from authenticated user ID: {$userId}");
                } else {
                    \Illuminate\Support\Facades\Log::warning("JWT token contains user ID that doesn't exist in database: " . ($user ? $user->id : 'null'));
                }
            } catch (\Exception $e) {
                // If JWT authentication fails, userId remains null
                \Illuminate\Support\Facades\Log::warning('JWT authentication failed in search: ' . $e->getMessage());
            }
        } else {
            \Illuminate\Support\Facades\Log::info("Search request from unauthenticated user");
        }

        // اگر طول کلمه جستجو کمتر یا مساوی 2 بود، خروجی خالی برگردان
        if (mb_strlen($searchText) > 0 && mb_strlen($searchText) <= 2) {
            return response()->json([
                'specialties' => [],
                'doctors' => [],
                'medical_centers' => [],
                'frequent_searches' => [],
                'services' => [],
            ]);
        }
        // حالت ۱: اگر search_text خالی بود فقط جستجوهای پرتکرار کاربر جاری
        if ($searchText === '') {
            $frequentSearches = FrequentSearch::with('specialty')
                ->when($userId, function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->orderByDesc('search_count')
                ->limit($limit)
                ->get();
            return response()->json([
                'specialties' => [],
                'doctors' => [],
                'medical_centers' => [],
                'frequent_searches' => $frequentSearches,
            ]);
        }

        // جستجوی چندکلمه‌ای
        $words = preg_split('/\s+/', $searchText, -1, PREG_SPLIT_NO_EMPTY);

        // تخصص‌ها
        $specialties = Specialty::where('status', 1)
            ->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $q->where(function ($qq) use ($word) {
                        $qq->where('name', 'like', "%$word%")
                           ->orWhere('description', 'like', "%$word%") ;
                    });
                }
            })
            // ->with(['doctors', 'medicalCenters']) // حذف با توجه به نبود جدول واسط
            ->limit($limit)
            ->get();

        // پزشکان
        $doctorsQuery = Doctor::where('is_active', 1)
            ->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $q->where(function ($qq) use ($word) {
                        $qq->where('first_name', 'like', "%$word%")
                           ->orWhere('last_name', 'like', "%$word%")
                           ->orWhereHas('Specialty', function ($q2) use ($word) {
                               $q2->where('name', 'like', "%$word%") ;
                           });
                    });
                }
            })
            ->with(['Specialty', 'medicalCenters', 'province', 'city']);
        if ($provinceId && $provinceId !== 'all') {
            $doctorsQuery->where('province_id', $provinceId);
        }
        if ($cityId && $cityId !== 'all') {
            $doctorsQuery->where('city_id', $cityId);
        }
        $doctors = $doctorsQuery->limit($limit)->get();

        // مراکز درمانی
        $medicalCentersQuery = MedicalCenter::where('is_active', 1)
            ->whereNotIn('type', ['policlinic'])
            ->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $q->where(function ($qq) use ($word) {
                        $qq->where('title', 'like', "%$word%")
                           ->orWhere('type', 'like', "%$word%")
                           ->orWhereJsonContains('specialty_ids', function ($query) use ($word) {
                               $ids = Specialty::where('name', 'like', "%$word%")
                                   ->pluck('id')->toArray();
                               return $ids;
                           });
                    });
                }
            })
            ->with(['province', 'city']);
        if ($provinceId && $provinceId !== 'all') {
            $medicalCentersQuery->where('province_id', $provinceId);
        }
        if ($cityId && $cityId !== 'all') {
            $medicalCentersQuery->where('city_id', $cityId);
        }
        $medicalCenters = $medicalCentersQuery->limit($limit)->get();

        // جستجوهای پرتکرار فقط برای کاربر جاری
        $frequentSearches = FrequentSearch::with('specialty')
            ->when($userId, function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $q->where('search_text', 'like', "%$word%") ;
                }
            })
            ->orderByDesc('search_count')
            ->limit($limit)
            ->get();

        // واکشی ۱۰ تخصص پرتکرار (بر اساس specialty_id)
        $frequentSearches = FrequentSearch::with('specialty')
            ->whereNotNull('specialty_id')
            ->orderByDesc('search_count')
            ->limit(10)
            ->get();

        $specialtyId = $request->input('specialty_id');

        // اگر specialty_id ارسال شده و جزو لیست نبود، آن را به ابتدای خروجی اضافه کن
        if ($specialtyId) {
            $alreadyInList = $frequentSearches->contains('specialty_id', $specialtyId);
            if (!$alreadyInList) {
                $specialty = \App\Models\Specialty::find($specialtyId);
                if ($specialty) {
                    $frequentSearches->prepend((object)[
                        'specialty_id' => $specialtyId,
                        'specialty' => $specialty,
                        'search_text' => $specialty->name,
                        'search_count' => 0,
                    ]);
                    $frequentSearches = $frequentSearches->take(10);
                }
            }
        }

        // خدمات (سرویس‌ها) با اطلاعات دکترها
        // اگر کاربر نام پزشک را وارد کند، خدمات مربوط به آن پزشک هم نمایش داده شود
        $doctorIds = Doctor::where('is_active', 1)
            ->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $q->where(function ($qq) use ($word) {
                        $qq->where('first_name', 'like', "%$word%")
                           ->orWhere('last_name', 'like', "%$word%") ;
                    });
                }
            })
            ->pluck('id')->toArray();

        $services = \App\Models\Service::where('status', 1)
            ->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $q->where(function ($qq) use ($word) {
                        $qq->where('name', 'like', "%$word%")
                           ->orWhere('description', 'like', "%$word%") ;
                    });
                }
            })
            ->orWhereHas('doctorServices', function ($q) use ($doctorIds) {
                if (!empty($doctorIds)) {
                    $q->whereIn('doctor_id', $doctorIds);
                }
            })
            ->with(['doctorServices.doctor'])
            ->limit($limit)
            ->get();

        $specialtyId = $request->input('specialty_id');
        // ذخیره جستجوی پرتکرار (نمونه پیاده‌سازی)
        if ($searchText && mb_strlen($searchText) > 2 && $userId) {
            // Double-check that the user exists before creating the record
            if (\App\Models\User::find($userId)) {
                $frequentSearch = FrequentSearch::where('search_text', $searchText)
                    ->where('user_id', $userId)
                    ->when($specialtyId, function ($q) use ($specialtyId) {
                        $q->where('specialty_id', $specialtyId);
                    })
                    ->first();
                if ($frequentSearch) {
                    $frequentSearch->increment('search_count');
                } else {
                    FrequentSearch::create([
                        'search_text' => $searchText,
                        'user_id' => $userId,
                        'specialty_id' => $specialtyId,
                        'search_count' => 1,
                    ]);
                }
            } else {
                \Illuminate\Support\Facades\Log::warning("Attempted to create frequent search for non-existent user ID: {$userId}");
            }
        }

        return response()->json([
            'specialties' => $specialties,
            'doctors' => $doctors,
            'medical_centers' => $medicalCenters,
            'frequent_searches' => $frequentSearches->values(),
            'services' => $services,
        ]);
    }
}

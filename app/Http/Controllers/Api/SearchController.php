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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

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
                // Use the JWT middleware to validate the token
                $jwtMiddleware = app(\App\Http\Middleware\JwtMiddleware::class);
                $response = $jwtMiddleware->handle($request, function ($request) {
                    return null; // Continue to next middleware
                });

                // If middleware returned a response, it means authentication failed
                if ($response && $response->getStatusCode() === 401) {
                    \Illuminate\Support\Facades\Log::warning('JWT authentication failed in search: ' . $response->getContent());
                } else {
                    // Authentication was successful, get the user
                    $user = \Illuminate\Support\Facades\Auth::user();

                    if ($user && $user->id) {
                        // Double-check that the user exists in the database
                        $existingUser = \App\Models\User::find($user->id);
                        if ($existingUser) {
                            $userId = $user->id;
                            \Illuminate\Support\Facades\Log::info("Search request from authenticated user ID: {$userId}");
                        } else {
                            \Illuminate\Support\Facades\Log::warning("JWT token contains user ID that doesn't exist in database: " . ($user ? $user->id : 'null'));
                        }
                    } else {
                        \Illuminate\Support\Facades\Log::warning("JWT token contains null user ID or user object is null");
                    }
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
            $cacheKey = "frequent_searches_user_{$userId}";
            $frequentSearches = Cache::remember($cacheKey, 300, function () use ($userId, $limit) {
                return FrequentSearch::with('specialty')
                    ->when($userId, function ($q) use ($userId) {
                        $q->where('user_id', $userId);
                    })
                    ->orderByDesc('search_count')
                    ->limit($limit)
                    ->get();
            });

            return response()->json([
                'specialties' => [],
                'doctors' => [],
                'medical_centers' => [],
                'frequent_searches' => $frequentSearches,
            ]);
        }

        // جستجوی چندکلمه‌ای - بهینه‌سازی شده
        $words = preg_split('/\s+/', $searchText, -1, PREG_SPLIT_NO_EMPTY);

        // Build search conditions more efficiently
        $searchConditions = $this->buildSearchConditions($words);

        // Use caching for expensive queries
        $cacheKey = "search_{$searchText}_{$provinceId}_{$cityId}";
        $results = Cache::remember($cacheKey, 180, function () use ($searchConditions, $provinceId, $cityId, $limit) {
            return $this->performSearch($searchConditions, $provinceId, $cityId, $limit);
        });

        // ذخیره جستجوی پرتکرار (نمونه پیاده‌سازی)
        if ($searchText && mb_strlen($searchText) > 2 && $userId) {
            // Double-check that the user exists before creating the record
            if (\App\Models\User::find($userId)) {
                $frequentSearch = FrequentSearch::where('search_text', $searchText)
                    ->where('user_id', $userId)
                    ->when($request->input('specialty_id'), function ($q) use ($request) {
                        $q->where('specialty_id', $request->input('specialty_id'));
                    })
                    ->first();
                if ($frequentSearch) {
                    $frequentSearch->increment('search_count');
                } else {
                    FrequentSearch::create([
                        'search_text' => $searchText,
                        'user_id' => $userId,
                        'specialty_id' => $request->input('specialty_id'),
                        'search_count' => 1,
                    ]);
                }
            } else {
                \Illuminate\Support\Facades\Log::warning("Attempted to create frequent search for non-existent user ID: {$userId}");
            }
        }

        return response()->json($results);
    }

    /**
     * Perform the actual search with optimized queries
     */
    private function performSearch(array $searchConditions, $provinceId, $cityId, int $limit)
    {
        // تخصص‌ها - بهینه‌سازی شده
        $specialties = Specialty::where('status', 1)
            ->where(function ($q) use ($searchConditions) {
                foreach ($searchConditions as $condition) {
                    $q->where(function ($qq) use ($condition) {
                        $qq->where('name', 'like', $condition)
                           ->orWhere('description', 'like', $condition);
                    });
                }
            })
            ->limit($limit)
            ->get();

        // پزشکان - بهینه‌سازی شده
        $doctorsQuery = Doctor::where('is_active', 1)
            ->where(function ($q) use ($searchConditions) {
                foreach ($searchConditions as $condition) {
                    $q->where(function ($qq) use ($condition) {
                        $qq->where('first_name', 'like', $condition)
                           ->orWhere('last_name', 'like', $condition)
                           ->orWhereHas('Specialty', function ($q2) use ($condition) {
                               $q2->where('name', 'like', $condition);
                           });
                    });
                }
            })
            ->with(['Specialty', 'medicalCenters', 'province', 'city']);

        // Apply location filters
        if ($provinceId && $provinceId !== 'all') {
            $doctorsQuery->where('province_id', $provinceId);
        }
        if ($cityId && $cityId !== 'all') {
            $doctorsQuery->where('city_id', $cityId);
        }

        $doctors = $doctorsQuery->limit($limit)->get();

        // مراکز درمانی - بهینه‌سازی شده
        $medicalCentersQuery = MedicalCenter::where('is_active', 1)
            ->whereNotIn('type', ['policlinic'])
            ->where(function ($q) use ($searchConditions) {
                foreach ($searchConditions as $condition) {
                    $q->where(function ($qq) use ($condition) {
                        $qq->where('title', 'like', $condition)
                           ->orWhere('type', 'like', $condition);
                    });
                }
            })
            ->with(['province', 'city']);

        // Apply location filters
        if ($provinceId && $provinceId !== 'all') {
            $medicalCentersQuery->where('province_id', $provinceId);
        }
        if ($cityId && $cityId !== 'all') {
            $medicalCentersQuery->where('city_id', $cityId);
        }

        $medicalCenters = $medicalCentersQuery->limit($limit)->get();

        // جستجوهای پرتکرار - بهینه‌سازی شده (یکبار اجرا)
        $frequentSearches = FrequentSearch::with('specialty')
            ->whereNotNull('specialty_id')
            ->orderByDesc('search_count')
            ->limit(10)
            ->get();

        // خدمات (سرویس‌ها) - بهینه‌سازی شده
        $services = $this->getOptimizedServices($searchConditions, $limit);

        return [
            'specialties' => $specialties,
            'doctors' => $doctors,
            'medical_centers' => $medicalCenters,
            'frequent_searches' => $frequentSearches->values(),
            'services' => $services,
        ];
    }

    /**
     * Build optimized search conditions
     */
    private function buildSearchConditions(array $words): array
    {
        $conditions = [];
        foreach ($words as $word) {
            $conditions[] = "%{$word}%";
        }
        return $conditions;
    }

    /**
     * Get optimized services with reduced queries
     */
    private function getOptimizedServices(array $searchConditions, int $limit)
    {
        // Get doctor IDs that match the search in one query
        $doctorIds = Doctor::where('is_active', 1)
            ->where(function ($q) use ($searchConditions) {
                foreach ($searchConditions as $condition) {
                    $q->where(function ($qq) use ($condition) {
                        $qq->where('first_name', 'like', $condition)
                           ->orWhere('last_name', 'like', $condition);
                    });
                }
            })
            ->pluck('id')
            ->toArray();

        // Get services with optimized query
        return \App\Models\Service::where('status', 1)
            ->where(function ($q) use ($searchConditions, $doctorIds) {
                // Search in service name and description
                foreach ($searchConditions as $condition) {
                    $q->where(function ($qq) use ($condition) {
                        $qq->where('name', 'like', $condition)
                           ->orWhere('description', 'like', $condition);
                    });
                }

                // Search in related doctor services if doctor IDs found
                if (!empty($doctorIds)) {
                    $q->orWhereHas('doctorServices', function ($q2) use ($doctorIds) {
                        $q2->whereIn('doctor_id', $doctorIds);
                    });
                }
            })
            ->with(['doctorServices.doctor'])
            ->limit($limit)
            ->get();
    }
}

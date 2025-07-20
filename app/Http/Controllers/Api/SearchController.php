<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Specialty;
use App\Models\Doctor;
use App\Models\MedicalCenter;
use App\Models\FrequentSearch;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $searchText = trim($request->input('search_text', ''));
        $provinceId = $request->input('province_id');
        $cityId = $request->input('city_id');
        $limit = 15;

        // حالت ۱: اگر search_text خالی بود فقط جستجوهای پرتکرار
        if ($searchText === '') {
            $frequentSearches = FrequentSearch::with('specialty')
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
            ->with(['doctors', 'medicalCenters'])
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

        // جستجوهای پرتکرار
        $frequentSearches = FrequentSearch::with('specialty')
            ->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $q->where('search_text', 'like', "%$word%") ;
                }
            })
            ->orderByDesc('search_count')
            ->limit($limit)
            ->get();

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

        return response()->json([
            'specialties' => $specialties,
            'doctors' => $doctors,
            'medical_centers' => $medicalCenters,
            'frequent_searches' => $frequentSearches,
            'services' => $services,
        ]);
    }
}

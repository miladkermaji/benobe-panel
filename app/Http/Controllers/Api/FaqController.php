<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * Get all active FAQs grouped by category
     */
    public function index(Request $request)
    {
        try {
            $category = $request->query('category');

            $query = Faq::active()->ordered();

            // Filter by category if provided
            if ($category && in_array($category, ['citizens', 'doctors'])) {
                $query->where('category', $category);
            }

            $faqs = $query->get();

            // Group FAQs by category
            $groupedFaqs = [
                'citizens' => $faqs->where('category', 'citizens')->values(),
                'doctors' => $faqs->where('category', 'doctors')->values(),
            ];

            // If specific category requested, return only that category
            if ($category && in_array($category, ['citizens', 'doctors'])) {
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'category' => $category,
                        'category_display_name' => $category === 'citizens' ? 'سؤالات متداول برای شهروندان' : 'سؤالات متداول برای پزشکان',
                        'faqs' => $groupedFaqs[$category]
                    ]
                ]);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'citizens' => [
                        'category' => 'citizens',
                        'category_display_name' => 'سؤالات متداول برای شهروندان',
                        'faqs' => $groupedFaqs['citizens']
                    ],
                    'doctors' => [
                        'category' => 'doctors',
                        'category_display_name' => 'سؤالات متداول برای پزشکان',
                        'faqs' => $groupedFaqs['doctors']
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطا در دریافت سؤالات متداول',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get FAQs for citizens
     */
    public function citizens()
    {
        try {
            $faqs = Faq::active()
                ->citizens()
                ->ordered()
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'category' => 'citizens',
                    'category_display_name' => 'سؤالات متداول برای شهروندان',
                    'faqs' => $faqs
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطا در دریافت سؤالات متداول شهروندان',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get FAQs for doctors
     */
    public function doctors()
    {
        try {
            $faqs = Faq::active()
                ->doctors()
                ->ordered()
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'category' => 'doctors',
                    'category_display_name' => 'سؤالات متداول برای پزشکان',
                    'faqs' => $faqs
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطا در دریافت سؤالات متداول پزشکان',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Search FAQs
     */
    public function search(Request $request)
    {
        try {
            $search = $request->query('q');
            $category = $request->query('category');

            if (!$search) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'عبارت جستجو الزامی است'
                ], 400);
            }

            $query = Faq::active()
                ->where(function ($q) use ($search) {
                    $q->where('question', 'like', '%' . $search . '%')
                      ->orWhere('answer', 'like', '%' . $search . '%');
                });

            // Filter by category if provided
            if ($category && in_array($category, ['citizens', 'doctors'])) {
                $query->where('category', $category);
            }

            $faqs = $query->ordered()->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'search_term' => $search,
                    'total_results' => $faqs->count(),
                    'faqs' => $faqs
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطا در جستجوی سؤالات متداول',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}

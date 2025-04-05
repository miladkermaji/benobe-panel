<?php

namespace App\Http\Controllers\Admin\Panel\Blogs;

use App\Http\Controllers\Admin\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BlogsController extends Controller
{
    public function index()
    {
        $page = request()->get('page', 1); // شماره صفحه از URL
        $perPage = 20; // تعداد پست در هر صفحه (کم کردم تا سریع‌تر باشه)
        $cacheKey = "blog_posts_page_{$page}";
        $cacheDuration = 1440; // 24 ساعت

        // گرفتن پست‌ها از کش یا API
        $posts = Cache::remember($cacheKey, $cacheDuration, function () use ($page, $perPage) {
            $response = Http::timeout(30)->get('https://benobe.ir/mag/wp-json/wp/v2/posts', [ // تایم‌اوت 30 ثانیه
                'per_page' => $perPage,
                'page'     => $page,
                'orderby'  => 'date',
                'order'    => 'desc',
            ]);

            if ($response->failed()) {
                Log::error('BlogsController - Error fetching posts: ' . $response->status());
                return [];
            }

            $posts = $response->json();
            $totalPosts = (int) $response->header('X-WP-Total') ?? 0;
            $totalPages = (int) $response->header('X-WP-TotalPages') ?? 1;

            // تبدیل داده‌ها
            $formattedPosts = array_map(function ($post) {
                $slug = $this->generateSlug($post['title']['rendered']);
                return [
                    'id'             => $post['id'],
                    'title'          => $post['title']['rendered'],
                    'featured_media' => $post['featured_media'] ? $this->getMediaUrl($post['featured_media']) : null,
                    'link'           => $post['link'],
                    'read_more_link' => "https://benobe.ir/mag/{$slug}/",
                    'all_posts_link' => 'https://benobe.ir/mag/',
                    'date'           => $post['date'],
                ];
            }, $posts);

            return [
                'data'        => $formattedPosts,
                'total'       => $totalPosts,
                'total_pages' => $totalPages,
                'current_page' => $page,
            ];
        });

        // اگه رفرش دستی بخوای
        if (request()->has('refresh') && request()->get('refresh') == 1) {
            Cache::forget($cacheKey);
            return redirect()->route('admin.panel.blogs.index', ['page' => $page]);
        }

        // ارسال داده‌ها به view
        return view('admin.panel.blogs.index', [
            'posts'       => $posts['data'],
            'total'       => $posts['total'],
            'total_pages' => $posts['total_pages'],
            'current_page' => $posts['current_page'],
        ]);
    }

    private function getMediaUrl($mediaId)
    {
        return Cache::remember("media_{$mediaId}", 1440, function () use ($mediaId) {
            try {
                $mediaResponse = Http::timeout(10)->get("https://benobe.ir/mag/wp-json/wp/v2/media/{$mediaId}");
                if ($mediaResponse->successful()) {
                    return $mediaResponse->json()['source_url'] ?? null;
                }
                return null;
            } catch (\Exception $e) {
                Log::error('BlogsController - GetMediaUrl Error: ' . $e->getMessage());
                return null;
            }
        });
    }

    private function generateSlug($title)
    {
        $slug = str_replace(' ', '-', $title);
        $slug = preg_replace('/[^\p{L}\p{N}-]+/u', '', $slug);
        return trim($slug, '-');
    }
}

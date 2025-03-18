<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MagController extends Controller
{
    /**
     * گرفتن 5 پست آخر از منبع (API وردپرس) با کش 24 ساعته
     *
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 4352,
     *       "title": "هر آنچه در مورد تزریق فیلر باید بدانید",
     *       "featured_media": "https://benobe.ir/mag/wp-content/uploads/2024/10/mdktgjer.jpg",
     *       "link": "https://benobe.ir/mag/%d9%87%d8%b1-%d8%a2%d9%86%da%86%d9%87-%d8%af%d8%b1-%d9%85%d9%88%d8%b1%d8%af-%d8%aa%d8%b2%d8%b1%db%8c%d9%82-%d9%81%db%8c%d9%84%d8%b1-%d8%a8%d8%a7%db%8c%d8%af-%d8%a8%d8%af%d8%a7%d9%86%db%8c%d8%af/",
     *       "read_more_link": "https://benobe.ir/mag/هر-آنچه-در-مورد-تزریق-فیلر-باید-بدانید/",
     *       "all_posts_link": "https://benobe.ir/mag/"
     *     },
     *     ...
     *   ]
     * }
     * @response 500 {
     *   "status": "error",
     *   "message": "خطای سرور",
     *   "data": null
     * }
     */
    public function getLatestPosts()
    {
        try {
            // کش کردن داده‌ها برای 24 ساعت (1440 دقیقه)
            $posts = Cache::remember('latest_posts', 1440, function () {
                $response = Http::get('https://benobe.ir/mag/wp-json/wp/v2/posts', [
                    'per_page' => 5,
                    'orderby'  => 'date',
                    'order'    => 'desc',
                ]);

                if ($response->failed()) {
                    throw new \Exception('خطا در دریافت داده‌ها از API');
                }

                $posts = $response->json();

                return array_map(function ($post) {
                    // تبدیل تایتل به اسلاگ ساده
                    $slug         = $this->generateSlug($post['title']['rendered']);
                    $readMoreLink = "https://benobe.ir/mag/{$slug}/";

                    return [
                        'id'             => $post['id'],
                        'title'          => $post['title']['rendered'],
                        'featured_media' => $post['featured_media'] ? $this->getMediaUrl($post['featured_media']) : null,
                        'link'           => $post['link'],
                        'read_more_link' => $readMoreLink,
                        'all_posts_link' => 'https://benobe.ir/mag/',
                    ];
                }, $posts);
            });

            return response()->json([
                'status' => 'success',
                'data'   => $posts,
            ], 200);

        } catch (\Exception $e) {
            Log::error('GetLatestPosts - Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور',
                'data'    => null,
            ], 500);
        }
    }

    /**
     * گرفتن URL تصویر از ID رسانه
     */
    private function getMediaUrl($mediaId)
    {
        try {
            $mediaResponse = Http::get("https://benobe.ir/mag/wp-json/wp/v2/media/{$mediaId}");
            if ($mediaResponse->successful()) {
                $media = $mediaResponse->json();
                return $media['source_url'] ?? null;
            }
            return null;
        } catch (\Exception $e) {
            Log::error('GetMediaUrl - Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * تولید اسلاگ ساده از تایتل (بدون نیاز به intl یا iconv)
     */
    private function generateSlug($title)
    {
        // جایگزینی فاصله‌ها با خط تیره و حذف کاراکترهای خاص
        $slug = str_replace(' ', '-', $title);
        // حذف کاراکترهای غیرمجاز (فقط حروف، اعداد و خط تیره نگه داشته می‌شن)
        $slug = preg_replace('/[^\p{L}\p{N}-]+/u', '', $slug);
        // حذف خط تیره‌های اضافی
        $slug = trim($slug, '-');
        return $slug;
    }
}

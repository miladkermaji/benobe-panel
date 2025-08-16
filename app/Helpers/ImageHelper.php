<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ImageHelper
{
    /**
     * Get a safe image URL with fallback
     *
     * @param string|null $path
     * @param string $fallback
     * @return string
     */
    public static function safeImageUrl($path, $fallback = null)
    {
        if (!$path) {
            return $fallback ?? asset('images/default-avatar.png');
        }

        try {
            // Check if the file exists in local storage first
            if (Storage::disk('public')->exists($path)) {
                return Storage::url($path);
            }

            // If not in local storage, try to construct CDN URL
            $cdnUrl = env('FILES_PUBLIC_URL');
            if ($cdnUrl) {
                $fullCdnUrl = rtrim($cdnUrl, '/') . '/' . ltrim($path, '/');

                // Check if CDN image is accessible (with caching)
                if (self::isCdnImageAccessible($fullCdnUrl)) {
                    return $fullCdnUrl;
                }
            }

            // Fallback to local storage URL (even if it might not exist)
            return Storage::url($path);
        } catch (\Exception $e) {
            Log::warning('ImageHelper: Error getting safe image URL', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            return $fallback ?? asset('images/default-avatar.png');
        }
    }

    /**
     * Get profile photo URL with fallback
     *
     * @param string|null $path
     * @param string $type
     * @return string
     */
    public static function profilePhotoUrl($path, $type = 'default')
    {
        $fallbacks = [
            'admin' => asset('admin-assets/panel/img/pro.jpg'),
            'doctor' => asset('dr-assets/panel/img/pro.jpg'),
            'medical_center' => asset('mc-assets/panel/img/pro.jpg'),
            'default' => asset('dr-assets/panel/img/pro.jpg') // Use existing image as default
        ];

        $fallback = $fallbacks[$type] ?? $fallbacks['default'];
        return self::safeImageUrl($path, $fallback);
    }

    /**
     * Check if image URL is accessible
     *
     * @param string $url
     * @return bool
     */
    public static function isImageAccessible($url)
    {
        try {
            $headers = get_headers($url, 1);
            return isset($headers['Content-Type']) &&
                   (strpos($headers['Content-Type'], 'image/') === 0);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get a smart image URL that shows fallback first, then tries CDN
     *
     * @param string|null $path
     * @param string $type
     * @param bool $preload
     * @return array
     */
    public static function smartImageUrl($path, $type = 'default', $preload = true)
    {
        $fallbacks = [
            'admin' => asset('admin-assets/panel/img/pro.jpg'),
            'doctor' => asset('dr-assets/panel/img/pro.jpg'),
            'medical_center' => asset('mc-assets/panel/img/pro.jpg'),
            'default' => asset('dr-assets/panel/img/pro.jpg')
        ];

        $fallback = $fallbacks[$type] ?? $fallbacks['default'];

        if (!$path) {
            return [
                'primary' => $fallback,
                'fallback' => $fallback,
                'cdn' => null,
                'strategy' => 'fallback-only'
            ];
        }

        // Try to construct CDN URL
        $cdnUrl = null;
        try {
            $cdnUrl = env('FILES_PUBLIC_URL');
            if ($cdnUrl) {
                $cdnUrl = rtrim($cdnUrl, '/') . '/' . ltrim($path, '/');
            }
        } catch (\Exception $e) {
            $cdnUrl = null;
        }

        return [
            'primary' => $fallback, // Show fallback first
            'fallback' => $fallback,
            'cdn' => $cdnUrl,
            'strategy' => 'fallback-first'
        ];
    }

    /**
     * Check if CDN image is accessible without showing errors
     *
     * @param string $url
     * @return bool
     */
    public static function isCdnImageAccessible($url)
    {
        if (!$url) {
            return false;
        }

        // Use cache to avoid repeated checks
        $cacheKey = 'cdn_image_check_' . md5($url);
        $cached = cache($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 2, // Very short timeout
                    'user_agent' => 'Mozilla/5.0 (compatible; ImageCheck/1.0)',
                    'ignore_errors' => true,
                    'method' => 'HEAD' // Use HEAD request for efficiency
                ]
            ]);

            $headers = @get_headers($url, 1, $context);
            if ($headers && isset($headers[0])) {
                $isAccessible = strpos($headers[0], '200') !== false;

                // Cache the result for 5 minutes
                cache([$cacheKey => $isAccessible], 300);

                return $isAccessible;
            }

            // Cache negative result for 1 minute
            cache([$cacheKey => false], 60);
            return false;
        } catch (\Exception $e) {
            // Cache negative result for 1 minute
            cache([$cacheKey => false], 60);
            return false;
        }
    }

    /**
     * Get image with progressive fallback strategy
     *
     * @param string|null $path
     * @param string $type
     * @return array
     */
    public static function getProgressiveImage($path, $type = 'default')
    {
        $fallbacks = [
            'admin' => asset('admin-assets/panel/img/pro.jpg'),
            'doctor' => asset('dr-assets/panel/img/pro.jpg'),
            'medical_center' => asset('mc-assets/panel/img/pro.jpg'),
            'default' => asset('dr-assets/panel/img/pro.jpg')
        ];

        $fallback = $fallbacks[$type] ?? $fallbacks['default'];

        if (!$path) {
            return [
                'url' => $fallback,
                'source' => 'fallback',
                'type' => $type
            ];
        }

        // Strategy 1: Try local storage first
        if (Storage::disk('public')->exists($path)) {
            return [
                'url' => Storage::url($path),
                'source' => 'local',
                'type' => $type
            ];
        }

        // Strategy 2: Try CDN
        $cdnUrl = env('FILES_PUBLIC_URL');
        if ($cdnUrl) {
            $fullCdnUrl = rtrim($cdnUrl, '/') . '/' . ltrim($path, '/');

            if (self::isCdnImageAccessible($fullCdnUrl)) {
                return [
                    'url' => $fullCdnUrl,
                    'source' => 'cdn',
                    'type' => $type
                ];
            }
        }

        // Strategy 3: Fallback to default image
        return [
            'url' => $fallback,
            'source' => 'fallback',
            'type' => $type
        ];
    }

    /**
     * Get optimized image URL with WebP support
     *
     * @param string|null $path
     * @param string $fallback
     * @param bool $preferWebP
     * @return string
     */
    public static function getOptimizedImageUrl($path, $fallback = null, $preferWebP = true)
    {
        if (!$path) {
            return $fallback ?? asset('images/default-avatar.png');
        }

        try {
            // Check if WebP is preferred and supported
            if ($preferWebP && self::supportsWebP()) {
                $webpPath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $path);

                // Try WebP version first
                if (Storage::disk('public')->exists($webpPath)) {
                    return Storage::url($webpPath);
                }
            }

            // Fall back to original format
            return self::safeImageUrl($path, $fallback);
        } catch (\Exception $e) {
            return $fallback ?? asset('images/default-avatar.png');
        }
    }

    /**
     * Check if browser supports WebP
     *
     * @return bool
     */
    private static function supportsWebP()
    {
        // This is a server-side check, so we'll assume modern browsers support WebP
        // In a real implementation, you might want to check user agent or use JavaScript
        return true;
    }

    /**
     * Clear CDN image cache
     *
     * @param string|null $url
     * @return void
     */
    public static function clearCdnCache($url = null)
    {
        if ($url) {
            $cacheKey = 'cdn_image_check_' . md5($url);
            cache()->forget($cacheKey);
        } else {
            // Clear all CDN image check caches
            $keys = cache()->get('cdn_image_check_keys', []);
            foreach ($keys as $key) {
                cache()->forget($key);
            }
        }
    }
}

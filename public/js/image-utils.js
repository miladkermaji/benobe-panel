/**
 * Smart Image Utilities for handling CDN images without console errors
 * Enhanced version to handle CORS issues, 404 errors, and opaque response blocking
 */
class SmartImageUtils {
    constructor() {
        this.defaultImages = {
            admin: "/admin-assets/panel/img/pro.jpg",
            doctor: "/dr-assets/panel/img/pro.jpg",
            medicalCenter: "/mc-assets/panel/img/pro.jpg",
            default: "/dr-assets/panel/img/pro.jpg",
        };

        this.imageCache = new Map();
        this.failedUrls = new Set();
        this.corsBlockedUrls = new Set();
        this.init();
    }

    init() {
        // Initialize smart image loading
        this.initializeSmartImages();

        // Add silent error handling for any remaining images
        this.addSilentErrorHandling();

        // Test fallback images
        this.testFallbackImages();

        // Add CORS error handling
        this.handleCorsErrors();
    }

    /**
     * Initialize smart image loading - show fallback first, then try CDN
     */
    initializeSmartImages() {
        const profileImages = document.querySelectorAll(
            'img[id*="profile-photo"], img[class*="avatar"], img[class*="profile"]'
        );

        profileImages.forEach((img) => {
            if (!img.hasAttribute("data-smart-loaded")) {
                this.setupSmartImage(img);
            }
        });
    }

    /**
     * Setup smart image loading for a specific image
     */
    setupSmartImage(img) {
        img.setAttribute("data-smart-loaded", "true");

        // Determine user type and fallback
        const userType = this.detectUserType(img);
        const fallbackSrc =
            this.defaultImages[userType] || this.defaultImages.default;

        // Store original source
        if (!img.dataset.originalSrc) {
            img.dataset.originalSrc = img.src;
        }

        // If image is already a fallback, don't change it
        if (img.src.includes("pro.jpg") || img.src.includes("default-avatar")) {
            return;
        }

        // Check if this URL has already failed or is CORS blocked
        if (
            this.failedUrls.has(img.dataset.originalSrc) ||
            this.corsBlockedUrls.has(img.dataset.originalSrc)
        ) {
            img.src = fallbackSrc;
            img.classList.add("image-fallback");
            img.setAttribute(
                "data-fallback-reason",
                this.corsBlockedUrls.has(img.dataset.originalSrc)
                    ? "cors-blocked"
                    : "failed"
            );
            return;
        }

        // Show fallback immediately for better UX
        img.src = fallbackSrc;
        img.classList.add("image-fallback");

        // Try to load CDN image in background (silently)
        this.silentlyTryCdnImage(img, img.dataset.originalSrc, fallbackSrc);
    }

    /**
     * Detect user type based on context
     */
    detectUserType(img) {
        const sidebar = img.closest(".sidebar__nav");
        if (!sidebar) return "default";

        if (sidebar.getAttribute("data-user-type")) {
            return sidebar.getAttribute("data-user-type");
        }

        // Fallback detection based on URL
        if (window.location.pathname.includes("/admin")) return "admin";
        if (window.location.pathname.includes("/dr")) return "doctor";
        if (window.location.pathname.includes("/mc")) return "medicalCenter";

        return "default";
    }

    /**
     * Silently try to load CDN image in background
     */
    async silentlyTryCdnImage(img, cdnUrl, fallbackSrc) {
        try {
            // Check if CDN image is accessible (silently)
            const result = await this.silentImageCheck(cdnUrl);

            if (result.success) {
                // CDN image is available, switch to it
                img.src = cdnUrl;
                img.classList.remove("image-fallback");
                img.classList.add("image-cdn");
                img.removeAttribute("data-fallback-reason");

                // Cache the success
                this.imageCache.set(cdnUrl, true);
            } else {
                // Handle different types of failures
                if (result.reason === "cors") {
                    this.corsBlockedUrls.add(cdnUrl);
                    img.setAttribute("data-fallback-reason", "cors-blocked");
                } else if (result.reason === "404") {
                    this.failedUrls.add(cdnUrl);
                    img.setAttribute("data-fallback-reason", "not-found");
                } else {
                    this.failedUrls.add(cdnUrl);
                    img.setAttribute("data-fallback-reason", "failed");
                }

                this.imageCache.set(cdnUrl, false);
            }
        } catch (error) {
            // Mark URL as failed and cache the failure
            this.failedUrls.add(cdnUrl);
            this.imageCache.set(cdnUrl, false);
            img.setAttribute("data-fallback-reason", "error");
        }
    }

    /**
     * Silent image accessibility check with improved CORS and 404 handling
     */
    async silentImageCheck(url) {
        return new Promise((resolve) => {
            // Skip if URL is already known to fail
            if (this.failedUrls.has(url) || this.corsBlockedUrls.has(url)) {
                resolve({
                    success: false,
                    reason: this.corsBlockedUrls.has(url) ? "cors" : "failed",
                });
                return;
            }

            const img = new Image();
            let resolved = false;

            // Set a short timeout
            const timeout = setTimeout(() => {
                if (!resolved) {
                    resolved = true;
                    resolve({ success: false, reason: "timeout" });
                }
            }, 2000); // Reduced timeout for better performance

            img.onload = () => {
                if (!resolved) {
                    clearTimeout(timeout);
                    resolved = true;
                    resolve({ success: true, reason: null });
                }
            };

            img.onerror = (error) => {
                if (!resolved) {
                    clearTimeout(timeout);
                    resolved = true;

                    // Try to determine the reason for failure
                    let reason = "failed";

                    // Check if it's a CORS issue by trying to access the image properties
                    try {
                        if (img.naturalWidth === 0 || img.naturalHeight === 0) {
                            reason = "cors";
                        }
                    } catch (e) {
                        reason = "cors";
                    }

                    resolve({ success: false, reason });
                }
            };

            // Add CORS handling
            img.crossOrigin = "anonymous";

            // Start loading
            img.src = url;
        });
    }

    /**
     * Handle CORS errors specifically
     */
    handleCorsErrors() {
        // Listen for CORS-related errors
        window.addEventListener(
            "error",
            (e) => {
                if (e.target && e.target.tagName === "IMG") {
                    const img = e.target;
                    const src = img.src;

                    // Check if this is a CORS error
                    if (
                        src &&
                        (src.includes("cloudydl.com") || src.includes("cdn"))
                    ) {
                        this.corsBlockedUrls.add(src);

                        // If this image hasn't been handled by smart loading, handle it now
                        if (!img.hasAttribute("data-smart-loaded")) {
                            this.handleSilentError(img);
                        }
                    }
                }
            },
            true
        );
    }

    /**
     * Add silent error handling for any remaining images
     */
    addSilentErrorHandling() {
        document.addEventListener(
            "error",
            (e) => {
                if (
                    e.target.tagName === "IMG" &&
                    !e.target.hasAttribute("data-smart-loaded")
                ) {
                    this.handleSilentError(e.target);
                }
            },
            true
        );

        // Handle load events to remove fallback styling when images load successfully
        document.addEventListener(
            "load",
            (e) => {
                if (
                    e.target.tagName === "IMG" &&
                    e.target.classList.contains("image-fallback")
                ) {
                    e.target.classList.remove("image-fallback");
                    e.target.classList.add("image-cdn");
                }
            },
            true
        );
    }

    /**
     * Handle image errors silently
     */
    handleSilentError(img) {
        // Don't log errors to console
        if (img.src.includes("pro.jpg") || img.src.includes("default-avatar")) {
            return;
        }

        // Determine failure reason
        let reason = "failed";
        if (img.src.includes("cloudydl.com") || img.src.includes("cdn")) {
            reason = "cors";
            this.corsBlockedUrls.add(img.src);
        } else {
            this.failedUrls.add(img.src);
        }

        const userType = this.detectUserType(img);
        const fallbackSrc =
            this.defaultImages[userType] || this.defaultImages.default;

        img.src = fallbackSrc;
        img.classList.add("image-fallback");
        img.setAttribute("data-fallback-reason", reason);
        img.setAttribute("data-smart-loaded", "true");
    }

    /**
     * Test fallback images silently
     */
    async testFallbackImages() {
        // Only log success, not failures
        for (const [type, path] of Object.entries(this.defaultImages)) {
            try {
                const result = await this.silentImageCheck(path);
                if (result.success) {
                    console.log(`✅ ${type} fallback image is ready`);
                }
            } catch (error) {
                // Silent - don't log errors
            }
        }
    }

    /**
     * Refresh images (useful for dynamic content)
     */
    refresh() {
        this.initializeSmartImages();
    }

    /**
     * Clear failed URL cache (useful for testing)
     */
    clearFailedCache() {
        this.failedUrls.clear();
        this.corsBlockedUrls.clear();
        this.imageCache.clear();
    }

    /**
     * Get image loading statistics
     */
    getStats() {
        return {
            totalCached: this.imageCache.size,
            failedUrls: this.failedUrls.size,
            corsBlockedUrls: this.corsBlockedUrls.size,
            successfulLoads: Array.from(this.imageCache.values()).filter(
                Boolean
            ).length,
        };
    }

    /**
     * Get detailed failure information for debugging
     */
    getFailureDetails() {
        const details = {
            failed: Array.from(this.failedUrls),
            corsBlocked: Array.from(this.corsBlockedUrls),
            cacheStatus: Object.fromEntries(this.imageCache),
        };
        return details;
    }
}

// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
    window.smartImageUtils = new SmartImageUtils();
});

// Initialize for dynamic content (Livewire, etc.)
document.addEventListener("livewire:load", () => {
    if (window.smartImageUtils) {
        window.smartImageUtils.refresh();
    }
});

// Initialize for Livewire 3
document.addEventListener("livewire:init", () => {
    if (window.smartImageUtils) {
        window.smartImageUtils.refresh();
    }
});

// Export for use in other scripts
if (typeof module !== "undefined" && module.exports) {
    module.exports = SmartImageUtils;
}

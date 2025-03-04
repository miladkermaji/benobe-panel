<div id="global-loader" class="global-loader">
    <div class="loader-backdrop"></div>
    <div class="loader-content">
        <div class="spinner">
            <svg class="spinner-svg" viewBox="0 0 50 50">
                <circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
            </svg>
        </div>
        <p class="loader-text">لطفاً صبر کنید...</p>
    </div>
</div>

@push('styles')
    <style>
        .global-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .loader-backdrop {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(10, 25, 47, 0.8); /* رنگ تیره و شیک */
            z-index: 1;
        }

        .loader-content {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            padding: 25px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .spinner-svg {
            width: 50px;
            height: 50px;
            animation: rotate 1.5s linear infinite;
        }

        .spinner-svg .path {
            stroke: #6366f1; /* رنگ بنفش شیک */
            stroke-linecap: round;
            animation: dash 1.5s ease-in-out infinite;
        }

        @keyframes rotate {
            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes dash {
            0% {
                stroke-dasharray: 1, 150;
                stroke-dashoffset: 0;
            }
            50% {
                stroke-dasharray: 90, 150;
                stroke-dashoffset: -35;
            }
            100% {
                stroke-dasharray: 90, 150;
                stroke-dashoffset: -124;
            }
        }

        .loader-text {
            margin: 0;
            font-size: 16px;
            color: #1f2937; /* خاکستری تیره */
            font-weight: 500;
            font-family: 'Vazir', sans-serif; /* فونت فارسی شیک */
        }

        .global-loader.hidden {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.4s ease;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            window.addEventListener("load", () => {
                const loader = document.getElementById("global-loader");
                setTimeout(() => loader.classList.add("hidden"), 500); // تأخیر برای زیبایی
            });
        });
    </script>
@endpush
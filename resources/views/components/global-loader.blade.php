    <!-- لودینگ ساده و شیک با تم پزشکی -->
    <div id="global-loader">
      <div class="loader-backdrop"></div>
      <div class="loader-content text-center">
        <x-svg-loading-icon size="40" color="#2196f3" />
        <p class="loader-text">در حال بارگذاری...</p>
      </div>
    </div>

    <style>
      .loader-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(30, 40, 60, 0.55);
        /* تیره‌تر شبیه مودال */
        z-index: 9998;
        backdrop-filter: blur(5px) saturate(120%);
        transition: background 0.3s;
      }

      .loader-content {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(255, 255, 255, 0.97);
        border: none;
        padding: 24px 32px 18px 32px;
        border-radius: 16px;
        text-align: center;
        box-shadow: 0 4px 24px 0 rgba(30, 40, 60, 0.13);
        z-index: 9999;
        min-width: 160px;
        animation: fadeIn 0.3s;
      }

      .loader-text {
        margin: 14px 0 0 0;
        font-size: 16px;
        color: #333 !important;
        font-weight: bold;
        letter-spacing: 0.2px;
        opacity: 1;
        white-space: nowrap;
      }

      @keyframes fadeIn {
        0% {
          opacity: 0;
        }

        100% {
          opacity: 1;
        }
      }

      .hidden {
        display: none !important;
      }
    </style>

    <script>
      document.addEventListener("DOMContentLoaded", () => {
        window.addEventListener("load", () => {
          const loader = document.getElementById("global-loader");
          setTimeout(() => loader.classList.add("hidden"), 400);
        });
      });
    </script>

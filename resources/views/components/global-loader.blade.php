    <!-- لودینگ ساده و شیک با تم پزشکی -->
    <div id="global-loader">
      <div class="loader-backdrop"></div>
      <div class="loader-content text-center">
        <div class="medical-spinner"></div>
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

      .medical-spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #e3f2fd;
        border-top: 4px solid #2196f3;
        border-radius: 50%;
        animation: spin 0.9s linear infinite;
        margin: 0 auto 14px;
      }

      .loader-text {
        margin: 0;
        font-size: 16px;
        color: #333 !important;
        font-weight: bold;
        letter-spacing: 0.2px;
        opacity: 1;
        white-space: nowrap;
      }

      @keyframes spin {
        0% {
          transform: rotate(0deg);
        }

        100% {
          transform: rotate(360deg);
        }
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
        });
      });
    </script>

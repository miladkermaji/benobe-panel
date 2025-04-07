    <!-- لودینگ کلی سایت -->
    <div id="global-loader">
      <div class="loader-backdrop"></div> <!-- بک‌دراپ -->
      <div class="loader-content text-center">
        <div class="spinner"></div> <!-- انیمیشن لودینگ -->
        <p>منتظر بمانید ....</p>
      </div>
    </div>

    @push('styles')
      <style>
        /* Backdrop with a modern semi-transparent look */
        .loader-backdrop {
          position: fixed;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background: rgba(20, 30, 40, 0.7);
          /* Darker, modern tone */
          z-index: 9998;
          backdrop-filter: blur(2px);
          /* Subtle blur for sleekness */
        }

        /* Smaller, centered, and modern loader content */
        .loader-content {
          position: fixed;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%);
          background: #1a1a1a;
          /* Dark background for a modern feel */
          padding: 20px;
          /* Reduced padding */
          border-radius: 12px;
          text-align: center;
          box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
          /* Softer shadow */
          z-index: 9999;
          width: 150px;
          /* Smaller width */
          animation: slideIn 0.4s ease-out;
          /* Quick, modern entrance */
        }

        /* Smaller, futuristic spinner */
        .spinner {
          width: 40px;
          /* Reduced size */
          height: 40px;
          border: 4px solid rgba(255, 255, 255, 0.1);
          /* Faint base */
          border-top: 4px solid #00ddeb;
          /* Neon cyan */
          border-left: 4px solid #ff007a;
          /* Neon pink */
          border-radius: 50%;
          animation: spin 1s cubic-bezier(0.68, -0.55, 0.27, 1.55) infinite;
          /* Bouncy spin */
          margin: 0 auto 10px;
          /* Less margin */
          position: relative;
        }

        /* Glowing effect for the spinner */
        .spinner::after {
          content: '';
          position: absolute;
          top: 50%;
          left: 50%;
          width: 30px;
          height: 30px;
          background: radial-gradient(circle, rgba(0, 221, 235, 0.3), transparent);
          border-radius: 50%;
          transform: translate(-50%, -50%);
          animation: glow 1.2s infinite ease-in-out;
        }

        /* Modern text styling */
        .loader-content p {
          margin: 0;
          font-size: 14px;
          /* Smaller text */
          color: #ffffff;
          /* White for contrast */
          font-weight: 400;
          letter-spacing: 0.5px;
          /* Slight spacing for elegance */
          opacity: 0.9;
        }

        /* Spin animation with a modern twist */
        @keyframes spin {
          0% {
            transform: rotate(0deg);
          }

          100% {
            transform: rotate(360deg);
          }
        }

        /* Glow animation */
        @keyframes glow {
          0% {
            transform: translate(-50%, -50%) scale(0.9);
            opacity: 0.8;
          }

          50% {
            transform: translate(-50%, -50%) scale(1.1);
            opacity: 0.4;
          }

          100% {
            transform: translate(-50%, -50%) scale(0.9);
            opacity: 0.8;
          }
        }

        /* Slide-in animation for the loader */
        @keyframes slideIn {
          0% {
            opacity: 0;
            transform: translate(-50%, -70%);
          }

          100% {
            opacity: 1;
            transform: translate(-50%, -50%);
          }
        }

        /* Hide loader when done */
        .hidden {
          display: none !important;
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

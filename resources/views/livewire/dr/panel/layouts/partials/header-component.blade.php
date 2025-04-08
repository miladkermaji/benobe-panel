<div class="header d-flex item-center bg-white width-100 custom-border-bottom padding-12-30">
  <div class="header__right d-flex flex-grow-1 item-center">
    <span class="bars"></span>
  </div>
  <div class="header__left d-flex flex-end item-center margin-top-2">
    <div class="d-flex notif-option">
      <div class="position-relative">
        <!-- آیکون زنگوله -->
        <span class="bell-red-badge" x-show="{{ $unreadCount }} > 0" x-text="{{ $unreadCount }}"></span>
        <svg xmlns="http://www.w3.org/2000/svg" class="cursor-pointer" fill="none" viewBox="0 0 24 24" height="24px"
          role="img" x-on:click="$refs.notificationBox.classList.toggle('d-none')">
          <path
            d="M12.02 2.91c-3.31 0-6 2.69-6 6v2.89c0 .61-.26 1.54-.57 2.06L4.3 15.77c-.71 1.18-.22 2.49 1.08 2.93 4.31 1.44 8.96 1.44 13.27 0 1.21-.4 1.74-1.83 1.08-2.93l-1.15-1.91c-.3-.52-.56-1.45-.56-2.06V8.91c0-3.3-2.7-6-6-6z"
            stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"></path>
          <path d="M13.87 3.2a6.754 6.754 0 00-3.7 0c.29-.74 1.01-1.26 1.85-1.26.84 0 1.56.52 1.85 1.26z"
            stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
            stroke-linejoin="round"></path>
          <path d="M15.02 19.06c0 1.65-1.35 3-3 3-.82 0-1.58-.34-2.12-.88a3.01 3.01 0 01-.88-2.12" stroke="currentColor"
            stroke-width="1.5" stroke-miterlimit="10"></path>
        </svg>

        <!-- باکس اعلان‌ها -->
        <div x-ref="notificationBox" class="notification-box d-none position-absolute bg-white shadow-lg rounded-3 p-3"
          style="width: 500px; top: 40px; left: 0; z-index: 1000;">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h6 class="mb-0 fw-bold text-gray-800" style="font-size: 18px;">اعلان‌ها</h6>
            <span class="badge bg-primary text-white">{{ $unreadCount }} جدید</span>
          </div>
          <div class="notification-list" style="max-height: 400px; overflow-y: auto;">
            @forelse ($notifications as $recipient)
              @if ($recipient->notification)
                <div class="notification-item d-flex justify-content-between align-items-start border-bottom py-3">
                  <div class="notification-content">
                    <p class="mb-1 fw-medium text-gray-800" style="font-size: 16px;">
                      {{ $recipient->notification->title }}</p>
                    <p class="mb-0 text-gray-600" style="font-size: 14px;">{{ $recipient->notification->message }}</p>
                  </div>
                  <button wire:click="markAsRead({{ $recipient->id }})" class="btn-read rounded-circle p-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none"
                      viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                      stroke-linejoin="round">
                      <path d="M20 6L9 17l-5-5"></path>
                    </svg>
                  </button>
                </div>
              @endif
            @empty
              <div class="text-center py-4">
                <p class="text-gray-500 mb-0" style="font-size: 16px;">اعلانی برای نمایش وجود ندارد.</p>
              </div>
            @endforelse
          </div>
        </div>
      </div>
      <div class="mx-4 cursor-pointer d-flex" onclick="location.href='{{ route('dr-wallet-charge') }}'">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="24px" stroke="currentColor" stroke-width="2"
          stroke-linecap="round" stroke-linejoin="round"
          class="plasmic-default__svg plasmic_all__FLoMj PlasmicQuickAccessWallet_svg__4uUbY lucide lucide-wallet"
          viewBox="0 0 24 24" role="img">
          <path d="M19 7V4a1 1 0 00-1-1H5a2 2 0 000 4h15a1 1 0 011 1v4h-3a2 2 0 000 4h3a1 1 0 001-1v-2a1 1 0 00-1-1">
          </path>
          <path d="M3 5v14a2 2 0 002 2h15a1 1 0 001-1v-4"></path>
        </svg>
        <span>{{ number_format($walletBalance) }} تومان</span>
      </div>
    </div>
    <!-- تغییر لینک لاگ‌اوت به نویگیشن Livewire -->
    <a href="#" wire:click.prevent="$dispatch('navigateTo', { url: '{{ route('dr.auth.logout') }}' })"
      class="logout" title="خروج"></a>
  </div>
  <style>
    /* استایل‌های مربوط به بج قرمز */
    .bell-red-badge {
      position: absolute;
      top: -5px;
      right: -5px;
      background-color: #ef4444;
      color: white;
      border-radius: 50%;
      padding: 4px 7px;
      font-size: 9px;
      font-weight: bold;
      line-height: 1;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* استایل‌های باکس اعلان‌ها */
    .notification-box {
      border: 1px solid #e2e8f0;
      box-shadow: 0 6px 24px rgba(0, 0, 0, 0.15);
      border-radius: 16px;
      background: linear-gradient(145deg, #ffffff, #f8fafc);
      transition: all 0.3s ease;
    }

    .notification-box:hover {
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
    }

    .notification-list::-webkit-scrollbar {
      width: 8px;
    }

    .notification-list::-webkit-scrollbar-thumb {
      background-color: #b0b0b0;
      border-radius: 12px;
      border: 2px solid #f8fafc;
    }

    .notification-list::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 12px;
    }

    .notification-item {
      transition: all 0.3s ease;
      padding: 12px 0;
    }

    .notification-item:hover {
      background-color: #f1f5f9;
      transform: translateY(-2px);
      padding: 3px;
    }

    .notification-content p {
      margin: 0;
      line-height: 1.5;
    }

    /* استایل دکمه "خوانده شد" */
    .btn-read {
      background: linear-gradient(90deg, #34d399, #10b981);
      color: white;
      font-weight: 600;
      font-size: 13px;
      border: none;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }

    .btn-read:hover {
      background: linear-gradient(90deg, #2dd4bf, #059669);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .badge.bg-primary {
      background-color: #374151 !important;
      font-size: 13px;
      padding: 5px 10px;
      border-radius: 12px;
    }
  </style>

  <script>
    document.addEventListener('livewire:init', function() {
      // بستن باکس اعلان‌ها با کلیک خارج از آن
      document.addEventListener('click', function(event) {
        const notificationBox = document.querySelector('.notification-box');
        const bellIcon = document.querySelector('.bell-red-badge').parentElement.querySelector('svg');
        if (!notificationBox || !bellIcon) return;

        if (!notificationBox.contains(event.target) && !bellIcon.contains(event.target)) {
          notificationBox.classList.add('d-none');
        }
      });
    });
  </script>
</div>

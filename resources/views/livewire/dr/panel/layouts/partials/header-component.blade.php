<div>
  <div>
    <div class="header d-flex item-center bg-white width-100 custom-border-bottom">
      <div class="w-100 d-flex align-items-center">
        <div class="header__right d-flex flex-grow-1 item-center">
          <span class="bars"></span>
          <div class="top-dr-panel d-flex justify-content-between w-100 align-items-start">
            <div class="p-3 bg-white stylish-breadcrumb" style="display: none">
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-white mb-0">
                  <li class="breadcrumb-item"><a href="#">پنل دکتر</a></li>
                  <li class="breadcrumb-item active" aria-current="page">@yield('bread-crumb-title')</li>
                </ol>
              </nav>
            </div>
          </div>
        </div>
        <div class="header__left d-flex flex-end item-center margin-top-2">
          <div class="myPanelOption p-3 p-md-3">
            <div class="d-flex align-items-center">
              <div class="my-tooltip mx-2">
                <x-custom-tooltip title="از این قسمت، مرکزی که در آن مشغول تجویز و طبابت هستید را انتخاب کنید"
                  placement="bottom">
                  <svg width="16" height="17" viewBox="0 0 16 17" fill="none"
                    xmlns="http://www.w3.org/2000/svg" class="lg:block svg-help" style="color: currentColor;"
                    data-tip="true" data-for="centerSelect" currentItem="false">
                    <path
                      d="M8.00006 9.9198V9.70984C8.00006 9.02984 8.42009 8.66982 8.84009 8.37982C9.25009 8.09982 9.66003 7.73983 9.66003 7.07983C9.66003 6.15983 8.92006 5.4198 8.00006 5.4198C7.08006 5.4198 6.34009 6.15983 6.34009 7.07983"
                      stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M7.9955 12.0692H8.0045" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                      stroke-linejoin="round"></path>
                    <circle cx="8" cy="8.99445" r="7.25" stroke="currentColor" stroke-width="1.5"></circle>
                  </svg>
                </x-custom-tooltip>
              </div>
              <div class="w-100">
                <div class="dropdown">
                  <div
                    class="dropdown-trigger btn h-40 w-md-300 bg-light-blue text-left d-flex justify-content-between align-items-center"
                    aria-haspopup="true" aria-expanded="false">
                    <div class="text-truncate">
                      <span class="dropdown-label">{{ $selectedMedicalCenterName }}</span>
                    </div>
                    <div class="flex-shrink-0">
                      <svg width="7" height="11" viewBox="0 0 7 11" fill="none"
                        xmlns="http://www.w3.org/2000/svg" style="transform: rotate(90deg)"
                        class="chevron_bottom__M8fF9">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                          d="M0.658146 0.39655C0.95104 0.103657 1.42591 0.103657 1.71881 0.39655L6.21881 4.89655C6.5117 5.18944 6.5117 5.66432 6.21881 5.95721L1.71881 10.4572C1.42591 10.7501 0.95104 10.7501 0.658146 10.4572C0.365253 10.1643 0.365253 9.68944 0.658146 9.39655L4.62782 5.42688L0.658146 1.45721C0.365253 1.16432 0.365253 0.689443 0.658146 0.39655Z"
                          fill="#758599"></path>
                      </svg>
                    </div>
                  </div>
                  <div class="my-dropdown-menu d-none">
                    <div class="" aria-hidden="true">
                      <div class="{{ Route::is('doctors.clinic.deposit') ? 'd-none' : '' }}" aria-hidden="true">
                        <div
                          class="d-flex align-items-center p-3 option-card {{ $selectedMedicalCenterId === null ? 'card-active' : '' }}"
                          wire:click="selectMedicalCenter()" data-id="default">
                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                              d="M17.62 10.7501C17.19 10.7501 16.85 10.4001 16.85 9.9801C16.85 9.6101 16.48 8.8401 15.86 8.1701C15.25 7.5201 14.58 7.1401 14.02 7.1401C13.59 7.1401 13.25 6.7901 13.25 6.3701C13.25 5.9501 13.6 5.6001 14.02 5.6001C15.02 5.6001 16.07 6.1401 16.99 7.1101C17.85 8.0201 18.4 9.1501 18.4 9.9701C18.4 10.4001 18.05 10.7501 17.62 10.7501Z"
                              fill="#3F3F79"></path>
                            <path
                              d="M21.23 10.75C20.8 10.75 20.46 10.4 20.46 9.98C20.46 6.43 17.57 3.55 14.03 3.55C13.6 3.55 13.26 3.2 13.26 2.78C13.26 2.36 13.6 2 14.02 2C18.42 2 22 5.58 22 9.98C22 10.4 21.65 10.75 21.23 10.75Z"
                              fill="#3F3F79"></path>
                            <path
                              d="M11.05 14.95L9.2 16.8C8.81 17.19 8.19 17.19 7.79 16.81C7.68 16.7 7.57 16.6 7.46 16.49C6.43 15.45 5.5 14.36 4.67 13.22C3.85 12.08 3.19 10.94 2.71 9.81C2.24 8.67 2 7.58 2 6.54C2 5.86 2.12 5.21 2.36 4.61C2.6 4 2.98 3.44 3.51 2.94C4.15 2.31 4.85 2 5.59 2C5.87 2 6.15 2.06 6.4 2.18C6.66 2.3 6.89 2.48 7.07 2.74L9.39 6.01C9.57 6.26 9.7 6.49 9.79 6.71C9.88 6.92 9.93 7.13 9.93 7.32C9.93 7.56 9.86 7.8 9.72 8.03C9.59 8.26 9.4 8.5 9.16 8.74L8.4 9.53C8.29 9.64 8.24 9.77 8.24 9.93C8.24 10.01 8.25 10.08 8.27 10.16C8.3 10.24 8.33 10.3 8.35 10.36C8.53 10.69 8.84 11.12 9.28 11.64C9.73 12.16 10.21 12.69 10.73 13.22C10.83 13.32 10.94 13.42 11.04 13.52C11.44 13.91 11.45 14.55 11.05 14.95Z"
                              fill="#3F3F79"></path>
                            <path
                              d="M21.97 18.33C21.97 18.61 21.92 18.9 21.82 19.18C21.79 19.26 21.76 19.34 21.72 19.42C21.55 19.78 21.33 20.12 21.04 20.44C20.55 20.98 20.01 21.37 19.4 21.62C19.39 21.62 19.38 21.63 19.37 21.63C18.78 21.87 18.14 22 17.45 22C16.43 22 15.34 21.76 14.19 21.27C13.04 20.78 11.89 20.12 10.75 19.29C7.4811 16.91 6.87003 15.81 6.50003 15.5L11 13.5C11.28 13.71 13.4 15.5 13.61 15.61C13.66 15.63 13.72 15.66 13.79 15.69C13.87 15.72 13.95 15.73 14.04 15.73C14.21 15.73 14.34 15.67 14.45 15.56L15.21 14.81C15.46 14.56 15.7 14.37 15.93 14.25C16.16 14.11 16.39 14.04 16.64 14.04C16.83 14.04 17.03 14.08 17.25 14.17C17.47 14.26 17.7 14.39 17.95 14.56L21.26 16.91C21.52 17.09 21.7 17.3 21.81 17.55C21.91 17.8 21.97 18.33 21.97 18.33Z"
                              fill="#3F3F79"></path>
                          </svg>
                          <div class="d-flex flex-column mx-3">
                            <span class="fw-bold d-block fs-15">مشاوره آنلاین به نوبه</span>
                            <span class="fw-bold fs-13">مرکز مشاوره آنلاین به نوبه</span>
                          </div>
                        </div>
                      </div>
                      @foreach ($medicalCenters as $medicalCenter)
                        <div
                          class="d-flex justify-content-between align-items-center option-card {{ $selectedMedicalCenterId == ($medicalCenter->id ?? null) ? 'card-active' : '' }} {{ !($medicalCenter->is_active ?? false) ? 'inactive-clinic' : '' }}"
                          wire:click="selectMedicalCenter({{ $medicalCenter->id ?? '' }})"
                          data-id="{{ $medicalCenter->id ?? '' }}"
                          data-active="{{ $medicalCenter->is_active ?? false ? '1' : '0' }}">
                          <div class="d-flex align-items-center p-3 position-relative">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                              xmlns="http://www.w3.org/2000/svg">
                              <path
                                d="M20.83 8.01002L14.28 2.77002C13 1.75002 11 1.74002 9.73002 2.76002L3.18002 8.01002C2.24002 8.76002 1.67002 10.26 1.87002 11.44L3.13002 18.98C3.42002 20.67 4.99002 22 6.70002 22H17.3C18.99 22 20.59 20.64 20.88 18.97L22.14 11.43C22.32 10.26 21.75 8.76002 20.83 8.01002Z"
                                fill="#3F3F79"></path>
                            </svg>
                            <div class="d-flex flex-column mx-3">
                              <span class="fw-bold d-block fs-15">{{ $medicalCenter->name ?? '' }}</span>
                              <span class="fw-bold d-block fs-13">{{ $medicalCenter->province->name ?? '' }}،
                                {{ $medicalCenter->city->name ?? '' }}</span>
                            </div>
                            @if (!($medicalCenter->is_active ?? false))
                              <div class="inactive-dot"
                                title="این مرکز درمانی هنوز فعال نشده است برای فعالسازی روی دکمه فعالسازی کلیک کنید">
                              </div>
                            @endif
                          </div>
                          <div class="mx-2">
                            @if (!($medicalCenter->is_active ?? false))
                              <button class="btn my-btn-primary fs-13 btn-sm h-35" tabindex="0" type="button"
                                onclick="window.location.href='{{ route('activation-doctor-clinic', $medicalCenter->id ?? '') }}'">فعال‌سازی</button>
                            @endif
                          </div>
                        </div>
                      @endforeach
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="d-flex notif-option px-2 px-md-3 align-items-center" >
            <!-- Dark Mode Toggle -->
           {{--  <div class="dark-mode-toggle me-3 d-flex align-items-center" >
              <button onclick="toggleDarkMode()" class="btn btn-link p-0 border-0 bg-transparent"
                title="تغییر به حالت تاریک"
                style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: all 0.3s ease;">
                <svg style="display: none" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                  fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                  stroke-linejoin="round" class="moon-icon">
                  <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                </svg>
              </button>
            </div> --}}

            <div class="position-relative d-flex align-items-center">
              <span
                class="absolute -top-3 -right-3 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white ring-2 ring-white shadow-lg"
                style="display: {{ $unreadCount > 0 ? 'flex' : 'none' }};">{{ $unreadCount }}</span>
              <svg xmlns="http://www.w3.org/2000/svg"
                class="cursor-pointer hover:text-gray-600 transition-colors duration-200 notification-bell"
                fill="none" viewBox="0 0 24 24" height="24px" role="img">
                <path
                  d="M12.02 2.91c-3.31 0-6 2.69-6 6v2.89c0 .61-.26 1.54-.57 2.06L4.3 15.77c-.71 1.18-.22 2.49 1.08 2.93 4.31 1.44 8.96 1.44 13.27 0 1.21-.4 1.74-1.83 1.08-2.93l-1.15-1.91c-.3-.52-.56-1.45-.56-2.06V8.91c0-3.3-2.7-6-6-6z"
                  stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"></path>
                <path d="M13.87 3.2a6.754 6.754 0 00-3.7 0c.29-.74 1.01-1.26 1.85-1.26.84 0 1.56.52 1.85 1.26z"
                  stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                  stroke-linejoin="round"></path>
                <path d="M15.02 19.06c0 1.65-1.35 3-3 3-.82 0-1.58-.34-2.12-.88a3.01 3.01 0 01-.88-2.12"
                  stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10"></path>
              </svg>
              <div id="notificationBox"
                class="notification-box d-none position-absolute bg-white shadow-lg rounded-3 p-3"
                style="width: 500px; top: 40px; left: 0; z-index: 1000;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                  <h6 class="mb-0 fw-bold text-gray-800" style="font-size: 18px;">اعلان‌ها</h6>
                  <span class="badge bg-primary text-white">{{ $unreadCount }} جدید</span>
                </div>
                <div class="notification-list" style="max-height: 400px; overflow-y: auto;">
                  @forelse ($notifications as $recipient)
                    @if ($recipient->notification)
                      <div
                        class="notification-item d-flex justify-content-between align-items-start border-bottom py-3">
                        <div class="notification-content">
                          <p class="mb-1 fw-medium text-gray-800" style="font-size: 16px;">
                            {{ $recipient->notification->title }}</p>
                          <p class="mb-0 text-gray-600" style="font-size: 14px;">
                            {{ $recipient->notification->message }}</p>
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
            <div style="display: none !important" class="mx-3 cursor-pointer d-flex"
              onclick="location.href='{{ route('dr-wallet-charge') }}'">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" width="24px" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="plasmic-default__svg plasmic_all__FLoMj PlasmicQuickAccessWallet_svg__4uUbY lucide lucide-wallet"
                viewBox="0 0 24 24" role="img">
                <path
                  d="M19 7V4a1 1 0 00-1-1H5a2 2 0 000 4h15a1 1 0 011 1v4h-3a2 2 0 000 4h3a1 1 0 001-1v-2a1 1 0 00-1-1">
                </path>
                <path d="M3 5v14a2 2 0 002 2h15a1 1 0 001-1v-4"></path>
              </svg>
              <span>{{ number_format($walletBalance) }} تومان</span>
            </div>
          </div>
          <a href="#" wire:click.prevent="$dispatch('navigateTo', { url: '{{ route('dr.auth.logout') }}' })"
            class="logout ms-2 d-flex align-items-center" title="خروج" style="height: 32px;"></a>
        </div>
      </div>
    </div>

    <script>
     /*  function applyDarkMode(isDark) {
        const html = document.documentElement;
        const body = document.body;
        if (isDark) {
          html.classList.add('dark');
          body.classList.add('dark-mode');
        } else {
          html.classList.remove('dark');
          body.classList.remove('dark-mode');
        }
      }

      window.toggleDarkMode = function() {
        const currentMode = localStorage.getItem('darkMode') === 'true';
        const newMode = !currentMode;
        localStorage.setItem('darkMode', newMode);
        applyDarkMode(newMode);
        updateDarkModeButton(newMode);
      };

      function updateDarkModeButton(isDark) {
        const button = document.querySelector('.dark-mode-toggle button');
        if (button) {
          const title = isDark ? 'تغییر به حالت روشن' : 'تغییر به حالت تاریک';
          button.setAttribute('title', title);
          if (isDark) {
            button.innerHTML =
              `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="sun-icon"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>`;
          } else {
            button.innerHTML =
              `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="moon-icon"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>`;
          }
        }

        // Update help icon color based on dark mode
        const helpIcon = document.querySelector('.svg-help');
        if (helpIcon) {
          if (isDark) {
            helpIcon.style.color = '#ffffff';
          } else {
            helpIcon.style.color = '#3f4079';
          }
        }
      } */

      document.addEventListener('livewire:init', function() {
        /* let isDarkMode = localStorage.getItem('darkMode') === 'true'; */
       /*  if (localStorage.getItem('darkMode') === null) {
          const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
          localStorage.setItem('darkMode', prefersDark);
          isDarkMode = prefersDark;
        }
        applyDarkMode(isDarkMode);
        updateDarkModeButton(isDarkMode); */

        // Initialize notification dropdown
        initializeNotificationDropdown();

        document.addEventListener('click', function(event) {
          const notificationBox = document.getElementById('notificationBox');
          const bellIcon = document.querySelector('.notification-bell');
          if (!notificationBox || !bellIcon) return;
          if (!notificationBox.contains(event.target) && !bellIcon.contains(event.target)) {
            notificationBox.classList.add('d-none');
          }
        });

        Livewire.on('reloadPageAfterDelay', (data) => {
          const delay = data.delay || 3000;
          const message = document.createElement('div');
          message.className = 'alert alert-info position-fixed';
          message.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
          message.innerHTML =
            `<div class="d-flex align-items-center"><div class="spinner-border spinner-border-sm me-2" role="status"><span class="visually-hidden">در حال بارگذاری...</span></div><span>مرکز درمانی تغییر کرد. صفحه در حال بروزرسانی...</span></div>`;
          document.body.appendChild(message);
          setTimeout(() => {
            window.location.reload();
          }, delay);
        });
      });

      function initializeNotificationDropdown() {
        const bellIcon = document.querySelector('.notification-bell');
        const notificationBox = document.getElementById('notificationBox');

        if (bellIcon && notificationBox) {
          bellIcon.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            notificationBox.classList.toggle('d-none');
          });
        }
      }

      document.addEventListener('DOMContentLoaded', function() {
        initializeDropdown();
        initializeNotificationDropdown();
      });

      document.addEventListener('livewire:navigated', function() {
        setTimeout(initializeDropdown, 100);
        setTimeout(initializeNotificationDropdown, 100);
      });

      function initializeDropdown() {
        const dropdownTrigger = document.querySelector('.dropdown-trigger');
        const dropdownMenu = document.querySelector('.my-dropdown-menu');
        if (dropdownTrigger && dropdownMenu) {
          dropdownTrigger.removeEventListener('click', handleDropdownClick);
          dropdownTrigger.addEventListener('click', handleDropdownClick);

          function handleDropdownClick(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
            if (isExpanded) {
              dropdownMenu.classList.add('d-none');
              dropdownMenu.style.display = 'none';
              dropdownMenu.style.visibility = 'hidden';
              dropdownMenu.style.opacity = '0';
            } else {
              dropdownMenu.classList.remove('d-none');
              dropdownMenu.style.display = 'block';
              dropdownMenu.style.visibility = 'visible';
              dropdownMenu.style.opacity = '1';
              dropdownMenu.style.position = 'fixed';
              dropdownMenu.style.top = '80px';
              dropdownMenu.style.left = '20px';
              dropdownMenu.style.right = 'auto';
              dropdownMenu.style.minHeight = '200px';
              dropdownMenu.style.height = 'auto';
              dropdownMenu.style.background = '#ffffff';
              dropdownMenu.style.border = '2px solid #e5e7eb';
              dropdownMenu.style.borderRadius = '12px';
              dropdownMenu.style.boxShadow = '0 20px 40px rgba(0, 0, 0, 0.2)';
              dropdownMenu.style.zIndex = '10000';
              dropdownMenu.style.padding = '1rem 0';
              dropdownMenu.style.overflow = 'visible';
              dropdownMenu.style.transform = 'none';
              dropdownMenu.style.maxHeight = 'none';
              setTimeout(() => {
                dropdownMenu.style.display = 'block';
                dropdownMenu.style.visibility = 'visible';
                dropdownMenu.style.opacity = '1';
                dropdownMenu.style.border = '2px solid #e5e7eb';
                dropdownMenu.style.background = '#ffffff';
              }, 10);
            }
          }

          document.removeEventListener('click', handleOutsideClick);
          setTimeout(() => {
            document.addEventListener('click', handleOutsideClick);
          }, 100);

          function handleOutsideClick(e) {
            if (dropdownTrigger.contains(e.target) || dropdownMenu.contains(e.target)) {
              return;
            }
            dropdownMenu.classList.add('d-none');
            dropdownMenu.style.display = 'none';
            dropdownMenu.style.visibility = 'hidden';
            dropdownMenu.style.opacity = '0';
            dropdownTrigger.setAttribute('aria-expanded', 'false');
          }

          document.removeEventListener('keydown', handleEscapeKey);
          document.addEventListener('keydown', handleEscapeKey);

          function handleEscapeKey(e) {
            if (e.key === 'Escape') {
              dropdownMenu.classList.add('d-none');
              dropdownMenu.style.display = 'none';
              dropdownMenu.style.visibility = 'hidden';
              dropdownMenu.style.opacity = '0';
              dropdownTrigger.setAttribute('aria-expanded', 'false');
            }
          }
        }
      }
    </script>
  </div>

</div>

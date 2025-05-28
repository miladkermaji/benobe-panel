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

        <div class="myPanelOption p-3 d-md-block d-none">
          <div class="d-flex align-items-center">
            <div class="my-tooltip mx-2">
              <x-custom-tooltip title="از این قسمت، مرکزی که در آن مشغول تجویز و طبابت هستید را انتخاب کنید"
                placement="bottom">
                <svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg"
                  class="lg:block svg-help" color="#3f4079" data-tip="true" data-for="centerSelect" currentItem="false">
                  <path
                    d="M8.00006 9.9198V9.70984C8.00006 9.02984 8.42009 8.66982 8.84009 8.37982C9.25009 8.09982 9.66003 7.73983 9.66003 7.07983C9.66003 6.15983 8.92006 5.4198 8.00006 5.4198C7.08006 5.4198 6.34009 6.15983 6.34009 7.07983"
                    stroke="#3f4079" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                  <path d="M7.9955 12.0692H8.0045" stroke="#3f4079" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round"></path>
                  <circle cx="8" cy="8.99445" r="7.25" stroke="#3f4079" stroke-width="1.5"></circle>
                </svg>
              </x-custom-tooltip>
            </div>
            <div class="">
              <div class="dropdown">
                <div
                  class="dropdown-trigger btn h-40 w-300 bg-light-blue text-left d-flex justify-content-between align-items-center"
                  aria-haspopup="true" aria-expanded="false">
                  <div class="">
                    <span class="dropdown-label">مشاوره آنلاین به نوبه</span>
                  </div>
                  <div>
                    <svg width="7" height="11" viewBox="0 0 7 11" fill="none"
                      xmlns="http://www.w3.org/2000/svg" style="transform: rotate(90deg)" class="chevron_bottom__M8fF9">
                      <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M0.658146 0.39655C0.95104 0.103657 1.42591 0.103657 1.71881 0.39655L6.21881 4.89655C6.5117 5.18944 6.5117 5.66432 6.21881 5.95721L1.71881 10.4572C1.42591 10.7501 0.95104 10.7501 0.658146 10.4572C0.365253 10.1643 0.365253 9.68944 0.658146 9.39655L4.62782 5.42688L0.658146 1.45721C0.365253 1.16432 0.365253 0.689443 0.658146 0.39655Z"
                        fill="#758599"></path>
                    </svg>
                  </div>
                </div>
                <div class="my-dropdown-menu d-none">
                  <div class="" aria-hidden="true">
                    <div class="{{ Request::routeIs('doctors.clinic.deposit') ? 'd-none' : '' }}" aria-hidden="true">
                      <div class="d-flex align-items-center p-3 option-card card-active" data-id="default">
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
                    @foreach ($clinics as $clinic)
                      <div
                        class="d-flex justify-content-between align-items-center option-card {{ !$clinic->is_active ? 'inactive-clinic' : '' }}"
                        aria-hidden="true" data-id="{{ $clinic->id }}"
                        data-active="{{ $clinic->is_active ? '1' : '0' }}">
                        <div class="d-flex align-items-center p-3 position-relative">
                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                              d="M20.83 8.01002L14.28 2.77002C13 1.75002 11 1.74002 9.73002 2.76002L3.18002 8.01002C2.24002 8.76002 1.67002 10.26 1.87002 11.44L3.13002 18.98C3.42002 20.67 4.99002 22 6.70002 22H17.3C18.99 22 20.59 20.64 20.88 18.97L22.14 11.43C22.32 10.26 21.75 8.76002 20.83 8.01002Z"
                              fill="#3F3F79"></path>
                          </svg>
                          <div class="d-flex flex-column mx-3">
                            <span class="fw-bold d-block fs-15"> {{ $clinic->name }}</span>
                            <span class="fw-bold d-block fs-13">{{ $clinic->province->name ?? '' }} ،
                              {{ $clinic->city->name ?? '' }}</span>
                          </div>
                          @if (!$clinic->is_active)
                            <div class="inactive-dot"
                              title="این مطب هنوز فعال نشده است برای فعالسازی روی دکمه فعالسازی کلیک کنید">
                            </div>
                          @endif
                        </div>
                        <div class="mx-2">
                          @if (!$clinic->is_active)
                            <button class="btn my-btn-primary fs-13 btn-sm h-35" tabindex="0" type="button"
                              onclick="window.location.href='{{ route('activation-doctor-clinic', $clinic) }}'">فعال‌سازی
                            </button>
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

        <!-- دکمه شناور و پنل بازشونده برای موبایل -->
        <div class="floating-panel-btn d-md-none">
          <x-custom-tooltip title="انتخاب مرکز" placement="top">
            <button class="floating-btn" aria-label="باز کردن پنل انتخاب مرکز">
              <svg fill="#fff" width="64px" height="64px" viewBox="-0.26 0 33.549 33.549"
                xmlns="http://www.w3.org/2000/svg">
                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                <g id="SVGRepo_iconCarrier">
                  <g transform="translate(-607.873 -577.167)">
                    <path
                      d="M638.546,610.716a1,1,0,0,1-.942-1.334c1.785-5.044,1.745-8.637-.12-10.679-3.26-3.568-11.186-1.6-11.266-1.574l-1.247.318V586l.016-.087a3.188,3.188,0,0,0-.274-2.085.7.7,0,0,0-.609-.226.774.774,0,0,0-.657.247,3.155,3.155,0,0,0-.346,2.033l.011.144v15.115l-1.155-.18c-1.766-.279-2.336.02-2.408.158-.459.9,2.05,4.66,5.264,7.888a1,1,0,0,1-1.418,1.412c-1.681-1.689-7.053-7.412-5.627-10.208.645-1.265,2.182-1.425,3.344-1.359V586.094a4.926,4.926,0,0,1,.822-3.55,2.768,2.768,0,0,1,2.17-.939,2.678,2.678,0,0,1,2.144.944,4.94,4.94,0,0,1,.723,3.624v8.757c2.643-.466,8.781-1.085,11.987,2.42,2.406,2.629,2.585,6.9.532,12.7A1,1,0,0,1,638.546,610.716Z">
                    </path>
                    <path
                      d="M612.733,586.792a2.2,2.2,0,0,1-1.562-.646l-3.005-3.005a1,1,0,0,1,1.414-1.414l3.006,3.005a.211.211,0,0,0,.3,0l6.522-6.521a1,1,0,0,1,1.414,1.414l-6.523,6.522A2.2,2.2,0,0,1,612.733,586.792Z">
                    </path>
                    <path
                      d="M639.392,587.543a1,1,0,0,1-.707-.293l-8.376-8.376a1,1,0,0,1,1.414-1.414l8.376,8.376a1,1,0,0,1-.707,1.707Z">
                    </path>
                    <path
                      d="M631.016,587.543a1,1,0,0,1-.707-1.707l8.376-8.376a1,1,0,0,1,1.414,1.414l-8.376,8.376A1,1,0,0,1,631.016,587.543Z">
                    </path>
                  </g>
                </g>
              </svg>
            </button>
          </x-custom-tooltip>

          <div class="floating-panel d-none">
            <div class="panel-header">
              <h6 class="fw-bold fs-15 mb-0">انتخاب مرکز</h6>
              <button class="panel-close-btn" aria-label="بستن پنل">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                  xmlns="http://www.w3.org/2000/svg">
                  <path d="M18 6L6 18" stroke="#4a5568" stroke-width="2" stroke-linecap="round" />
                  <path d="M6 6L18 18" stroke="#4a5568" stroke-width="2" stroke-linecap="round" />
                </svg>
              </button>
            </div>
            <div class="panel-content">
              <div class="{{ Request::routeIs('doctors.clinic.deposit') ? 'd-none' : '' }}">
                <div class="d-flex align-items-center p-3 option-card card-active" data-id="default">
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
              @foreach ($clinics as $clinic)
                <div
                  class="d-flex justify-content-between align-items-center option-card flex-wrap {{ !$clinic->is_active ? 'inactive-clinic' : '' }}"
                  data-id="{{ $clinic->id }}" data-active="{{ $clinic->is_active ? '1' : '0' }}">
                  <div class="d-flex align-items-center p-3 position-relative">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                      xmlns="http://www.w3.org/2000/svg">
                      <path
                        d="M20.83 8.01002L14.28 2.77002C13 1.75002 11 1.74002 9.73002 2.76002L3.18002 8.01002C2.24002 8.76002 1.67002 10.26 1.87002 11.44L3.13002 18.98C3.42002 20.67 4.99002 22 6.70002 22H17.3C18.99 22 20.59 20.64 20.88 18.97L22.14 11.43C22.32 10.26 21.75 8.76002 20.83 8.01002Z"
                        fill="#3F3F79"></path>
                    </svg>
                    <div class="d-flex flex-column mx-3">
                      <span class="fw-bold d-block fs-15"> {{ $clinic->name }}</span>
                      <span class="fw-bold d-block fs-13">{{ $clinic->province->name ?? '' }} ،
                        {{ $clinic->city->name ?? '' }}</span>
                    </div>
                    @if (!$clinic->is_active)
                      <div class="inactive-dot"
                        title="این مطب هنوز فعال نشده است برای فعالسازی روی دکمه فعالسازی کلیک کنید">
                      </div>
                    @endif
                  </div>
                  <div class="mx-2 w-100">
                    @if (!$clinic->is_active)
                      <button class="btn my-btn-primary fs-13 btn-sm h-35 w-100" tabindex="0" type="button"
                        onclick="window.location.href='{{ route('activation-doctor-clinic', $clinic) }}'">فعال‌سازی
                      </button>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>

        <!-- اسکریپت برای مدیریت باز و بسته شدن پنل -->


        <!-- اسکریپت برای مدیریت باز و بسته شدن پنل -->

        <div class="d-flex notif-option px-3">
          <div class="position-relative">
            <!-- آیکون زنگوله -->
            <span class="bell-red-badge" x-show="{{ $unreadCount }} > 0" x-text="{{ $unreadCount }}"></span>
            <svg xmlns="http://www.w3.org/2000/svg" class="cursor-pointer" fill="none" viewBox="0 0 24 24"
              height="24px" role="img" x-on:click="$refs.notificationBox.classList.toggle('d-none')">
              <path
                d="M12.02 2.91c-3.31 0-6 2.69-6 6v2.89c0 .61-.26 1.54-.57 2.06L4.3 15.77c-.71 1.18-.22 2.49 1.08 2.93 4.31 1.44 8.96 1.44 13.27 0 1.21-.4 1.74-1.83 1.08-2.93l-1.15-1.91c-.3-.52-.56-1.45-.56-2.06V8.91c0-3.3-2.7-6-6-6z"
                stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"></path>
              <path d="M13.87 3.2a6.754 6.754 0 00-3.7 0c.29-.74 1.01-1.26 1.85-1.26.84 0 1.56.52 1.85 1.26z"
                stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round"
                stroke-linejoin="round"></path>
              <path d="M15.02 19.06c0 1.65-1.35 3-3 3-.82 0-1.58-.34-2.12-.88a3.01 3.01 0 01-.88-2.12"
                stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10"></path>
            </svg>

            <!-- باکس اعلان‌ها -->
            <div x-ref="notificationBox"
              class="notification-box d-none position-absolute bg-white shadow-lg rounded-3 p-3"
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
                        <p class="mb-0 text-gray-600" style="font-size: 14px;">
                          {{ $recipient->notification->message }}
                        </p>
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
        <!-- تغییر لینک لاگ‌اوت به نویگیشن Livewire -->
        <a href="#" wire:click.prevent="$dispatch('navigateTo', { url: '{{ route('dr.auth.logout') }}' })"
          class="logout" title="خروج"></a>
      </div>
    </div>


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
      document.addEventListener('DOMContentLoaded', function() {
        const floatingBtn = document.querySelector('.floating-btn');
        const floatingPanel = document.querySelector('.floating-panel');
        const closeBtn = document.querySelector('.panel-close-btn');

        // باز کردن پنل با کلیک روی دکمه شناور
        floatingBtn.addEventListener('click', function() {
          floatingPanel.classList.toggle('d-none');
          floatingPanel.classList.toggle('panel-open');
        });

        // بستن پنل با کلیک روی دکمه بستن
        closeBtn.addEventListener('click', function() {
          floatingPanel.classList.add('d-none');
          floatingPanel.classList.remove('panel-open');
        });

        // بستن پنل با کلیک خارج از آن
        document.addEventListener('click', function(event) {
          if (!floatingPanel.contains(event.target) && !floatingBtn.contains(event.target)) {
            floatingPanel.classList.add('d-none');
            floatingPanel.classList.remove('panel-open');
          }
        });
      });
    </script>
  </div>
  <div class="quick-access">

  </div>
</div>

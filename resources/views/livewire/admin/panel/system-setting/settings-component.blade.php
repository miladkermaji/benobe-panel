<div class="container-fluid py-5">
  <!-- هدر اصلی -->
  <div class="bg-gradient-primary text-white p-4 rounded-top-3 shadow-sm">
    <div class="d-flex align-items-center gap-3 mb-2">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        class="animate-spin-slow">
        <path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8Z" />
        <path d="M12 6a1 1 0 0 0-1 1v5a1 1 0 0 0 .29.71l3 3a1 1 0 0 0 1.42-1.42L13 12.41V7a1 1 0 0 0-1-1Z" />
      </svg>
      <h5 class="mb-0 fw-semibold">تنظیمات سامانه</h5>
    </div>
  </div>
  <!-- بدنه اصلی -->
  <div class="bg-white p-5 rounded-bottom-3 shadow-sm">
    <!-- تب‌ها -->
    <div class="tab-wrapper mb-5">
      <ul class="nav nav-tabs-custom justify-content-start flex-wrap gap-2 d-md-flex d-none" role="tablist">
        <li class="nav-item">
          <button class="nav-link {{ $activeTab === 'general' ? 'active' : '' }} px-4 py-2"
            wire:click="switchTab('general')">تنظیمات عمومی</button>
        </li>
        <li class="nav-item">
          <button class="nav-link {{ $activeTab === 'seo' ? 'active' : '' }} px-4 py-2"
            wire:click="switchTab('seo')">سئو</button>
        </li>
        <li class="nav-item">
          <button class="nav-link {{ $activeTab === 'payment' ? 'active' : '' }} px-4 py-2"
            wire:click="switchTab('payment')">درگاه‌های پرداخت</button>
        </li>
        <li class="nav-item">
          <button class="nav-link {{ $activeTab === 'communication' ? 'active' : '' }} px-4 py-2"
            wire:click="switchTab('communication')">ارتباطات</button>
        </li>
        <li class="nav-item">
          <button class="nav-link {{ $activeTab === 'callmee' ? 'active' : '' }} px-4 py-2"
            wire:click="switchTab('callmee')">تنظیمات کال می</button>
        </li>
        <li class="nav-item">
          <button class="nav-link {{ $activeTab === 'program' ? 'active' : '' }} px-4 py-2"
            wire:click="switchTab('program')">تنظیمات برنامه</button>
        </li>
        <li class="nav-item">
          <button class="nav-link {{ $activeTab === 'security_users' ? 'active' : '' }} px-4 py-2"
            wire:click="switchTab('security_users')">امنیت و کاربران</button>
        </li>
        <li class="nav-item">
          <button class="nav-link {{ $activeTab === 'files' ? 'active' : '' }} px-4 py-2"
            wire:click="switchTab('files')">تنظیمات فایل‌ها</button>
        </li>
      </ul>
      <!-- تب‌ها برای موبایل -->
      <div class="d-md-none">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="nav-mobile d-flex flex-nowrap overflow-auto gap-2">
            <button class="nav-link {{ $activeTab === 'general' ? 'active' : '' }} px-4 py-2"
              wire:click="switchTab('general')">تنظیمات عمومی</button>
            <button class="nav-link {{ $activeTab === 'seo' ? 'active' : '' }} px-4 py-2"
              wire:click="switchTab('seo')">سئو</button>
            <button class="nav-link {{ $activeTab === 'payment' ? 'active' : '' }} px-4 py-2"
              wire:click="switchTab('payment')">درگاه‌های پرداخت</button>
          </div>
          <div class="dropdown">
            <button class="btn btn-outline-primary dropdown-toggle px-3 py-2 rounded-pill" type="button"
              data-bs-toggle="dropdown" aria-expanded="false">
              بیشتر
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><button class="dropdown-item {{ $activeTab === 'communication' ? 'active' : '' }}"
                  wire:click="switchTab('communication')">ارتباطات</button></li>
              <li><button class="dropdown-item {{ $activeTab === 'callmee' ? 'active' : '' }}"
                  wire:click="switchTab('callmee')">تنظیمات کال می</button></li>
              <li><button class="dropdown-item {{ $activeTab === 'program' ? 'active' : '' }}"
                  wire:click="switchTab('program')">تنظیمات برنامه</button></li>
              <li><button class="dropdown-item {{ $activeTab === 'security_users' ? 'active' : '' }}"
                  wire:click="switchTab('security_users')">امنیت و کاربران</button></li>
              <li><button class="dropdown-item {{ $activeTab === 'files' ? 'active' : '' }}"
                  wire:click="switchTab('files')">تنظیمات فایل‌ها</button></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <!-- محتوای تب‌ها -->
    <div class="tab-content">
      @if (empty($settings))
        <p class="text-danger text-center py-4">هیچ تنظیمی یافت نشد!</p>
      @else
        @foreach ($settings as $group => $groupSettings)
          <!-- تب‌های اصلی که تو $settings هستن -->
          @if (in_array($group, ['general', 'seo', 'payment', 'callmee', 'program', 'files']))
            <div class="{{ $activeTab === $group ? 'd-block' : 'd-none' }}" id="{{ $group }}-tab">
              <div class="row g-4">
                @if (empty($groupSettings))
                  <p class="text-warning text-center py-4">هیچ تنظیمی برای گروه {{ $group }} یافت نشد!</p>
                @else
                  @foreach ($groupSettings as $index => $setting)
                    <div class="col-md-6">
                      <div class="p-4 border rounded-3 bg-light shadow-sm">
                        <label class="form-label fw-semibold text-dark mb-2">{{ $setting['description'] }}</label>
                        @if ($setting['type'] === 'boolean')
                          <div class="toggle-switch">
                            <input type="checkbox" class="toggle-input"
                              wire:model.live="settings.{{ $group }}.{{ $index }}.value"
                              id="toggle-{{ $group }}-{{ $index }}" @checked($setting['value'] == 1)>
                            <label for="toggle-{{ $group }}-{{ $index }}" class="toggle-label">
                              <span class="toggle-text">{{ $setting['value'] == 1 ? 'فعال' : 'غیرفعال' }}</span>
                            </label>
                          </div>
                        @elseif (
                            $setting['type'] === 'string' &&
                                in_array($setting['key'], [
                                    'type_payment_system',
                                    'theme',
                                    'admintheme',
                                    'extra_login',
                                    'ip_control',
                                    'auth_metod',
                                    'log_threshold',
                                    'o_seite',
                                    'image_align',
                                    'mail_metod',
                                    'smtp_secure',
                                    'allow_cache',
                                ]))
                          <select class="form-select shadow-sm"
                            wire:model.live="settings.{{ $group }}.{{ $index }}.value">
                            @if ($setting['key'] === 'type_payment_system')
                              <option value="membershipfee">پرداخت حق عضویت</option>
                              <option value="onlinepayment">پرداخت آنلاین</option>
                            @elseif ($setting['key'] === 'theme')
                              <option value="portal-old">portal-old</option>
                              <option value="portal">portal</option>
                            @elseif ($setting['key'] === 'admintheme')
                              <option value="nopardaz">nopardaz</option>
                            @elseif ($setting['key'] === 'extra_login')
                              <option value="0">مداوم</option>
                              <option value="1">پایدار</option>
                            @elseif ($setting['key'] === 'ip_control')
                              <option value="0">عادی</option>
                              <option value="1">متوسط</option>
                              <option value="2">پیشرفته</option>
                            @elseif ($setting['key'] === 'auth_metod')
                              <option value="0">نام کاربری</option>
                              <option value="1">پست الکترونیکی</option>
                            @elseif ($setting['key'] === 'log_threshold')
                              <option value="0">غیرفعال</option>
                              <option value="1">Error</option>
                              <option value="2">Debug</option>
                              <option value="3">INFO</option>
                              <option value="4">All</option>
                            @elseif ($setting['key'] === 'o_seite')
                              <option value="0">به‌صورت کامل</option>
                              <option value="1">طول</option>
                              <option value="2">عرض</option>
                            @elseif ($setting['key'] === 'image_align')
                              <option value="">هیچ‌کدام</option>
                              <option value="left">سمت چپ</option>
                              <option value="center">وسط</option>
                              <option value="right">سمت راست</option>
                            @elseif ($setting['key'] === 'mail_metod')
                              <option value="php">PHP Mail()</option>
                              <option value="smtp">SMTP</option>
                            @elseif ($setting['key'] === 'smtp_secure')
                              <option value="">هیچ‌کدام</option>
                              <option value="ssl">SSL</option>
                              <option value="tls">TLS</option>
                            @elseif ($setting['key'] === 'allow_cache')
                              <option value="yes">بلی</option>
                              <option value="no">خیر</option>
                            @endif
                          </select>
                        @elseif ($setting['key'] === 'register_default_usergroup')
                          <select class="form-select shadow-sm"
                            wire:model.live="settings.{{ $group }}.{{ $index }}.value">
                            <option value="1">مدیران</option>
                            <option value="2">کاربران</option>
                            <option value="3">پزشکان</option>
                            <option value="4">بیمارستان</option>
                            <option value="5">منشی</option>
                            <option value="6">منشی درمانگاه</option>
                            <option value="7">نمایندگان</option>
                          </select>
                        @elseif ($setting['type'] === 'integer' || $setting['type'] === 'string')
                          <input type="text" class="form-control shadow-sm"
                            wire:model.live="settings.{{ $group }}.{{ $index }}.value"
                            style="direction: {{ in_array($setting['key'], ['smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_mail', 'recaptcha_site_key', 'recaptcha_secret_key']) ? 'ltr' : 'rtl' }};">
                        @endif
                        <small class="text-muted mt-2 d-block">{{ $setting['description'] }}</small>
                        <!-- اضافه کردن تغییر لوگو به تنظیمات عمومی -->
                        @if ($group === 'general' && $index === array_key_last($groupSettings))
                          <div class="mt-3">
                            <a href="{{ route('admin.panel.setting.change-logo') }}"
                              class="btn btn-outline-success px-4 py-2 rounded-pill d-flex align-items-center gap-2">
                              <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2">
                                </rect>
                                <line x1="3" y1="9" x2="21" y2="9"></line>
                                <line x1="9" y1="21" x2="9" y2="9"></line>
                              </svg>
                              تغییر لوگو
                            </a>
                          </div>
                        @endif
                      </div>
                    </div>
                  @endforeach
                @endif
              </div>
            </div>
          @endif
        @endforeach
        <!-- تب ارتباطات (ادغام sms و mail) -->
        <div class="{{ $activeTab === 'communication' ? 'd-block' : 'd-none' }}" id="communication-tab">
          <div class="row g-4">
            @if (!empty($settings['sms']))
              @foreach ($settings['sms'] as $index => $setting)
                <div class="col-md-6">
                  <div class="p-4 border rounded-3 bg-light shadow-sm">
                    <label class="form-label fw-semibold text-dark mb-2">{{ $setting['description'] }}</label>
                    @if ($setting['type'] === 'boolean')
                      <div class="toggle-switch">
                        <input type="checkbox" class="toggle-input"
                          wire:model.live="settings.sms.{{ $index }}.value"
                          id="toggle-sms-{{ $index }}" @checked($setting['value'] == 1)>
                        <label for="toggle-sms-{{ $index }}" class="toggle-label">
                          <span class="toggle-text">{{ $setting['value'] == 1 ? 'فعال' : 'غیرفعال' }}</span>
                        </label>
                      </div>
                    @elseif ($setting['type'] === 'string' || $setting['type'] === 'integer')
                      <input type="text" class="form-control shadow-sm"
                        wire:model.live="settings.sms.{{ $index }}.value"
                        style="direction: {{ in_array($setting['key'], ['smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_mail']) ? 'ltr' : 'rtl' }};">
                    @endif
                    <small class="text-muted mt-2 d-block">{{ $setting['description'] }}</small>
                  </div>
                </div>
              @endforeach
            @endif
            @if (!empty($settings['mail']))
              @foreach ($settings['mail'] as $index => $setting)
                <div class="col-md-6">
                  <div class="p-4 border rounded-3 bg-light shadow-sm">
                    <label class="form-label fw-semibold text-dark mb-2">{{ $setting['description'] }}</label>
                    @if ($setting['type'] === 'boolean')
                      <div class="toggle-switch">
                        <input type="checkbox" class="toggle-input"
                          wire:model.live="settings.mail.{{ $index }}.value"
                          id="toggle-mail-{{ $index }}" @checked($setting['value'] == 1)>
                        <label for="toggle-mail-{{ $index }}" class="toggle-label">
                          <span class="toggle-text">{{ $setting['value'] == 1 ? 'فعال' : 'غیرفعال' }}</span>
                        </label>
                      </div>
                    @elseif ($setting['type'] === 'string' && in_array($setting['key'], ['mail_metod', 'smtp_secure']))
                      <select class="form-select shadow-sm"
                        wire:model.live="settings.mail.{{ $index }}.value">
                        @if ($setting['key'] === 'mail_metod')
                          <option value="php">PHP Mail()</option>
                          <option value="smtp">SMTP</option>
                        @elseif ($setting['key'] === 'smtp_secure')
                          <option value="">هیچ‌کدام</option>
                          <option value="ssl">SSL</option>
                          <option value="tls">TLS</option>
                        @endif
                      </select>
                    @elseif ($setting['type'] === 'string' || $setting['type'] === 'integer')
                      <input type="text" class="form-control shadow-sm"
                        wire:model.live="settings.mail.{{ $index }}.value"
                        style="direction: {{ in_array($setting['key'], ['smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_mail']) ? 'ltr' : 'rtl' }};">
                    @endif
                    <small class="text-muted mt-2 d-block">{{ $setting['description'] }}</small>
                  </div>
                </div>
              @endforeach
            @endif
            @if (empty($settings['sms']) && empty($settings['mail']))
              <p class="text-warning text-center py-4">هیچ تنظیمی برای ارتباطات یافت نشد!</p>
            @endif
          </div>
        </div>
        <!-- تب امنیت و کاربران (ادغام security و user) -->
        <div class="{{ $activeTab === 'security_users' ? 'd-block' : 'd-none' }}" id="security_users-tab">
          <div class="row g-4">
            @if (!empty($settings['security']))
              @foreach ($settings['security'] as $index => $setting)
                <div class="col-md-6">
                  <div class="p-4 border rounded-3 bg-light shadow-sm">
                    <label class="form-label fw-semibold text-dark mb-2">{{ $setting['description'] }}</label>
                    @if ($setting['type'] === 'boolean')
                      <div class="toggle-switch">
                        <input type="checkbox" class="toggle-input"
                          wire:model.live="settings.security.{{ $index }}.value"
                          id="toggle-security-{{ $index }}" @checked($setting['value'] == 1)>
                        <label for="toggle-security-{{ $index }}" class="toggle-label">
                          <span class="toggle-text">{{ $setting['value'] == 1 ? 'فعال' : 'غیرفعال' }}</span>
                        </label>
                      </div>
                    @elseif ($setting['type'] === 'string' && in_array($setting['key'], ['ip_control', 'auth_metod', 'log_threshold']))
                      <select class="form-select shadow-sm"
                        wire:model.live="settings.security.{{ $index }}.value">
                        @if ($setting['key'] === 'ip_control')
                          <option value="0">عادی</option>
                          <option value="1">متوسط</option>
                          <option value="2">پیشرفته</option>
                        @elseif ($setting['key'] === 'auth_metod')
                          <option value="0">نام کاربری</option>
                          <option value="1">پست الکترونیکی</option>
                        @elseif ($setting['key'] === 'log_threshold')
                          <option value="0">غیرفعال</option>
                          <option value="1">Error</option>
                          <option value="2">Debug</option>
                          <option value="3">INFO</option>
                          <option value="4">All</option>
                        @endif
                      </select>
                    @elseif ($setting['type'] === 'string' || $setting['type'] === 'integer')
                      <input type="text" class="form-control shadow-sm"
                        wire:model.live="settings.security.{{ $index }}.value"
                        style="direction: {{ in_array($setting['key'], ['recaptcha_site_key', 'recaptcha_secret_key']) ? 'ltr' : 'rtl' }};">
                    @endif
                    <small class="text-muted mt-2 d-block">{{ $setting['description'] }}</small>
                  </div>
                </div>
              @endforeach
            @endif
            @if (!empty($settings['user']))
              @foreach ($settings['user'] as $index => $setting)
                <div class="col-md-6">
                  <div class="p-4 border rounded-3 bg-light shadow-sm">
                    <label class="form-label fw-semibold text-dark mb-2">{{ $setting['description'] }}</label>
                    @if ($setting['key'] === 'register_default_usergroup')
                      <select class="form-select shadow-sm"
                        wire:model.live="settings.user.{{ $index }}.value">
                        <option value="1">مدیران</option>
                        <option value="2">کاربران</option>
                        <option value="3">پزشکان</option>
                        <option value="4">بیمارستان</option>
                        <option value="5">منشی</option>
                        <option value="6">منشی درمانگاه</option>
                        <option value="7">نمایندگان</option>
                      </select>
                    @elseif ($setting['type'] === 'string' || $setting['type'] === 'integer')
                      <input type="text" class="form-control shadow-sm"
                        wire:model.live="settings.user.{{ $index }}.value" style="direction: rtl;">
                    @endif
                    <small class="text-muted mt-2 d-block">{{ $setting['description'] }}</small>
                  </div>
                </div>
              @endforeach
            @endif
            @if (empty($settings['security']) && empty($settings['user']))
              <p class="text-warning text-center py-4">هیچ تنظیمی برای امنیت و کاربران یافت نشد!</p>
            @endif
          </div>
        </div>
      @endif
    </div>
    <!-- دکمه ذخیره -->
    <div class="mt-5 text-end">
      <button wire:click="saveSettings"
        class="btn my-btn-primary px-4 py-2 rounded-pill d-flex align-items-center gap-2">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
          stroke-width="2">
          <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
          <polyline points="17 21 17 13 7 13 7 21"></polyline>
          <polyline points="7 3 7 8 15 8"></polyline>
        </svg>
        ذخیره تغییرات
      </button>
    </div>
  </div>
  <script>
    document.addEventListener('livewire:initialized', () => {
      Livewire.on('toast', (message, options = {}) => {
        if (typeof toastr === 'undefined') {
          console.error('Toastr is not loaded!');
          return;
        }
        const type = options.type || 'info';
        const toastOptions = {
          positionClass: options.position || 'toast-top-right',
          timeOut: options.timeOut || 3000,
          progressBar: options.progressBar || false,
        };
        if (type === 'success') toastr.success(message, '', toastOptions);
        else if (type === 'error') toastr.error(message, '', toastOptions);
        else if (type === 'warning') toastr.warning(message, '', toastOptions);
        else toastr.info(message, '', toastOptions);
      });
    });
  </script>
</div>

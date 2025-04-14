<div class="container-fluid py-5">
  <!-- هدر اصلی -->
  <div class="bg-gradient-primary text-white p-4 rounded-top-3 shadow-sm">
    <div class="d-flex align-items-center gap-3">
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

  <!-- استایل‌ها -->
  <style>
    /* کلیات */
    .container-fluid {
      padding: 1.5rem;
      background: #f4f6f9;
    }

    /* هدر شیشه‌ای */
    .glass-header {
      background: linear-gradient(135deg, rgba(31, 41, 55, 0.95), rgba(55, 65, 81, 0.85));
      backdrop-filter: blur(15px);
      border: 1px solid rgba(255, 255, 255, 0.1);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
      border-radius: 16px;
      padding: 1.5rem;
      transition: all 0.4s ease;
    }

    .glass-header:hover {
      box-shadow: 0 12px 35px rgba(0, 0, 0, 0.2);
      transform: translateY(-3px);
    }

    .glass-header h1 {
      color: #ffffff;
      font-size: 1.75rem;
      font-weight: 300;
      margin: 0;
      letter-spacing: -0.025em;
      text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }

    /* فیلد جستجو */
    .input-group {
      max-width: 400px;
      position: relative;
    }

    .form-control {
      background: #ffffff;
      border: 1px solid #d1d5db;
      border-radius: 12px;
      padding: 0.75rem 1rem 0.75rem 2.5rem;
      font-size: 0.95rem;
      box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.02);
      transition: all 0.3s ease;
      height: 48px;
    }

    .form-control:focus {
      border-color: #374151;
      box-shadow: 0 0 0 4px rgba(55, 65, 81, 0.15);
      background: #f9fafb;
      outline: none;
    }

    .form-control::placeholder {
      color: #6b7280;
    }

    .search-icon {
      position: absolute;
      top: 50%;
      right: 1rem;
      transform: translateY(-50%);
      z-index: 5;
    }

    /* دکمه‌ها */
    .btn-gradient-success {
      background: linear-gradient(90deg, #10b981, #34d399);
      border: none;
      color: #ffffff;
      border-radius: 9999px;
      padding: 0.65rem 1.5rem;
      font-size: 0.95rem;
      font-weight: 500;
      box-shadow: 0 3px 10px rgba(16, 185, 129, 0.2);
      transition: all 0.3s ease;
    }

    .btn-gradient-success:hover {
      background: linear-gradient(90deg, #059669, #10b981);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
    }

    .btn-gradient-danger {
      background: linear-gradient(90deg, #f43f5e, #fb7185);
      border: none;
      color: #ffffff;
      border-radius: 9999px;
      padding: 0.65rem 1.5rem;
      font-size: 0.95rem;
      font-weight: 500;
      box-shadow: 0 3px 10px rgba(244, 63, 94, 0.2);
      transition: all 0.3s ease;
    }

    .btn-gradient-danger:hover:not(:disabled) {
      background: linear-gradient(90deg, #e11d48, #f43f5e);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(244, 63, 94, 0.3);
    }

    .btn-gradient-danger:disabled {
      background: #e5e7eb;
      color: #9ca3af;
      cursor: not-allowed;
      box-shadow: none;
    }

    .buttons-container {
      display: flex;
      gap: 0.5rem;
      flex-wrap: wrap;
      justify-content: center;
    }

    /* کارت */
    .card {
      background: #ffffff;
      border-radius: 16px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
      transition: all 0.3s ease;
      border: none;
    }

    .card:hover {
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
      transform: translateY(-2px);
    }

    /* جدول */
    .table {
      border-collapse: separate;
      border-spacing: 0;
      margin: 0;
      width: 100% !important;
    }

    .table thead th {
      background: linear-gradient(135deg, rgba(31, 41, 55, 0.95), rgba(55, 65, 81, 0.85));
      color: #ffffff;
      font-weight: 600;
      padding: 1rem;
      border-bottom: none;
      text-wrap: nowrap !important;
    }

    .table td {
      border-bottom: 1px solid #e5e7eb;
      padding: 1rem;
      vertical-align: middle;
    }

    .table-hover tbody tr:hover {
      background: #f9fafb;
      transition: background 0.2s ease;
    }

    /* چک‌باکس */
    .form-check-input {
      width: 1rem;
      height: 1rem;
      border-radius: 4px;
      border: 1px solid #d1d5db;
      transition: all 0.3s ease;
      cursor: pointer;
      margin: 0;
      vertical-align: middle;
    }

    .form-check-input:checked {
      background-color: #374151;
      border-color: #374151;
    }

    /* بج‌ها */
    .badge {
      padding: 0.5rem 1rem;
      border-radius: 9999px;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .bg-label-success {
      background-color: #d1fae5;
      color: #10b981;
    }

    .bg-label-danger {
      background-color: #fee2e2;
      color: #f43f5e;
    }

    /* افکت‌ها */
    .cursor-pointer {
      cursor: pointer;
    }

    /* ریسپانسیو */
    @media (max-width: 991px) {
      .glass-header {
        padding: 1.25rem;
      }

      .input-group {
        max-width: 100%;
        margin-top: 1rem;
      }

      .buttons-container {
        margin-top: 1rem;
        width: 100%;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
      }

      .btn {
        width: 100%;
        max-width: 250px;
      }
    }

    @media (max-width: 767px) {
      .container-fluid {
        padding: 1rem;
      }

      .glass-header {
        padding: 1rem;
      }

      .glass-header h1 {
        font-size: 1.5rem;
        text-align: center;
        width: 100%;
      }

      .form-control {
        font-size: 0.9rem;
        padding: 0.65rem 1rem 0.65rem 2.5rem;
        height: 44px;
      }

      .btn-gradient-success,
      .btn-gradient-danger {
        font-size: 0.9rem;
        padding: 0.5rem 1.25rem;
      }

      .table td,
      .table th {
        padding: 0.75rem;
        font-size: 0.9rem;
      }
    }

    @media (max-width: 576px) {
      .glass-header h1 {
        font-size: 1.25rem;
      }

      .form-control {
        font-size: 0.85rem;
        padding: 0.5rem 1rem 0.5rem 2.5rem;
        height: 40px;
      }

      .btn-gradient-success,
      .btn-gradient-danger {
        font-size: 0.85rem;
        padding: 0.5rem 1rem;
      }

      .table-responsive {
        font-size: 0.85rem;
      }

      .table td,
      .table th {
        padding: 0.5rem;
      }
    }

    .bg-gradient-primary {
      background: linear-gradient(90deg, #4f46e5, #7c3aed);
    }

    .tab-wrapper {
      position: relative;
    }

    .nav-tabs-custom .nav-link {
      background: #f1f5f9;
      color: #374151;
      font-weight: 500;
      border-radius: 0.75rem;
      padding: 0.75rem 1.5rem;
      border: none;
      transition: all 0.3s ease;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .nav-tabs-custom .nav-link:hover {
      background: #e5e7eb;
      color: #1f2937;
    }

    .nav-tabs-custom .nav-link.active {
      background: #4f46e5;
      color: white;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .nav-mobile {
      white-space: nowrap;
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    .nav-mobile::-webkit-scrollbar {
      display: none;
    }

    .nav-mobile .nav-link {
      background: #f1f5f9;
      color: #374151;
      font-weight: 500;
      border-radius: 0.75rem;
      padding: 0.75rem 1.5rem;
      border: none;
      transition: all 0.3s ease;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .nav-mobile .nav-link:hover {
      background: #e5e7eb;
      color: #1f2937;
    }

    .nav-mobile .nav-link.active {
      background: #4f46e5;
      color: white;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .dropdown-menu {
      border-radius: 0.75rem;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .dropdown-item {
      padding: 0.5rem 1.5rem;
      color: #374151;
    }

    .dropdown-item:hover {
      background-color: #f3f4f6;
      color: #4f46e5;
    }

    .dropdown-item.active {
      background-color: #4f46e5;
      color: white;
    }

    .form-control,
    .form-select {
      border-radius: 0.5rem;
      padding: 0.75rem 1rem;
      border: 1px solid #d1d5db;
      transition: all 0.2s ease;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: #4f46e5;
      box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
    }

    /* استایل تاگل */
    .toggle-switch {
      position: relative;
      display: inline-flex;
      align-items: center;
      gap: 10px;
    }

    .toggle-input {
      display: none;
    }

    .toggle-label {
      position: relative;
      display: inline-block;
      width: 50px;
      height: 24px;
      background-color: #d1d5db;
      border-radius: 9999px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .toggle-label::before {
      content: '';
      position: absolute;
      width: 20px;
      height: 20px;
      left: 2px;
      top: 2px;
      background-color: white;
      border-radius: 50%;
      transition: transform 0.3s ease;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }

    .toggle-input:checked+.toggle-label {
      background-color: #4f46e5;
    }

    .toggle-input:checked+.toggle-label::before {
      transform: translateX(26px);
    }

    .toggle-text {
      font-size: 0.9rem;
      color: #6b7280;
      transition: color 0.3s ease;
    }

    .toggle-input:checked+.toggle-label+.toggle-text {
      color: #4f46e5;
    }

    .bg-light {
      background-color: #f9fafb !important;
    }

    .rounded-top-3 {
      border-top-left-radius: 1rem;
      border-top-right-radius: 1rem;
    }

    .rounded-bottom-3 {
      border-bottom-left-radius: 1rem;
      border-bottom-right-radius: 1rem;
    }

    .animate-spin-slow {
      animation: spin 3s linear infinite;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    @media (max-width: 767px) {
      .nav-mobile {
        flex-wrap: nowrap;
        justify-content: flex-start;
      }
    }
  </style>

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

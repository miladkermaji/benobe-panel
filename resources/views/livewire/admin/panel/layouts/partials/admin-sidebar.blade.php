<div class="sidebar__nav border-top border-left">
  <!-- بخش ثابت -->
  <div class="sidebar__fixed">
    <span class="bars d-none padding-0-18"></span>
    <div class="profile__info border cursor-pointer text-center">
      <div class="avatar__img cursor-pointer">
        <img id="profile-photo-img"
          src="{{ $user && $user->avatar ? Storage::url($user->avatar) : asset('admin-assets/panel/img/pro.jpg') }}"
          class="avatar___img cursor-pointer">
        <input type="file" accept="image/*" class="avatar-img__input" id="profile-photo-input">
        <div class="v-dialog__container" style="display: block;"></div>
        <div class="box__camera default__avatar"></div>
      </div>
      <span class="profile__name sidebar-full-name">
        {{ $user ? $user->first_name . ' ' . $user->last_name : 'کاربر ناشناس' }}
      </span>
      <span class="fs-11 fw-bold" id="takhasos-txt">{{ $userType }}</span>
    </div>
  </div>
  <!-- بخش اسکرول‌پذیر -->
  <div class="sidebar__scrollable">
    <ul class="" id="">
      @if (Auth::guard('manager')->check())
        <!-- داشبورد -->
        <li class="item-li i-dashboard {{ Request::routeIs('admin-panel') ? 'is-active' : '' }}">
          <a href="{{ route('admin-panel') }}">داشبورد</a>
        </li>
        <!-- ابزارها -->
        <li class="item-li i-courses">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            ابزارها
            <div class="d-flex justify-content-end w-100 align-items-center">
              <svg width="6" height="9" class="svg-caret-left" viewBox="0 0 7 11" fill="none"
                xmlns="http://www.w3.org/2000/svg" style="transition: transform 0.3s; transform: rotate(180deg);">
                <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M0.658146 0.39655C0.95104 0.103657 1.42591 0.103657 1.71881 0.39655L6.21881 4.89655C6.5117 5.18944 6.5117 5.66432 6.21881 5.95721L1.71881 10.4572C1.42591 10.7501 0.95104 10.7501 0.658146 10.4572C0.365253 10.1643 0.365253 9.68944 0.658146 9.39655L4.62782 5.42688L0.658146 1.45721C0.365253 1.16432 0.365253 0.689443 0.658146 0.39655Z"
                  fill="currentColor"></path>
              </svg>
            </div>
          </a>
          <ul class="drop-toggle d-none">
            <li class="item-li"><a href="{{ route('admin.panel.tools.file-manager') }}">مدیریت فایل</a></li>
            <li class="item-li"><a href="{{ route('admin.tools.data-migration.index') }}">انتقال داده‌ها</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.tools.payment_gateways.index') }}">درگاه پرداخت</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.tools.sms-gateways.index') }}">پیامک</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.tools.telescope') }}">تلسکوپ</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.tools.redirects.index') }}">ابزار ریدایرکت</a></li>
            <li class="item-li"><a href="{{ route('admin.tools.sitemap.index') }}">نقشه سایت</a></li>
            <li class="item-li"><a href="{{ route('admin.tools.page-builder.index') }}">صفحه‌ساز</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.tools.mail-template.index') }}">قالب ایمیل</a></li>
            <li class="item-li"><a href="{{ route('admin.tools.news-latter.index') }}">خبرنامه</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.tools.notifications.index') }}">مدیریت اعلان‌ها</a></li>
          </ul>
        </li>
        <!-- مدیریت کاربران -->
        <li class="item-li i-users">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            مدیریت کاربران
            <div class="d-flex justify-content-end w-100 align-items-center">
              <svg width="6" height="9" class="svg-caret-left" viewBox="0 0 7 11" fill="none"
                xmlns="http://www.w3.org/2000/svg" style="transition: transform 0.3s; transform: rotate(180deg);">
                <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M0.658146 0.39655C0.95104 0.103657 1.42591 0.103657 1.71881 0.39655L6.21881 4.89655C6.5117 5.18944 6.5117 5.66432 6.21881 5.95721L1.71881 10.4572C1.42591 10.7501 0.95104 10.7501 0.658146 10.4572C0.365253 10.1643 0.365253 9.68944 0.658146 9.39655L4.62782 5.42688L0.658146 1.45721C0.365253 1.16432 0.365253 0.689443 0.658146 0.39655Z"
                  fill="currentColor"></path>
              </svg>
            </div>
          </a>
          <ul class="drop-toggle d-none">
            <li class="item-li"><a href="{{ route('admin.panel.users.index') }}">لیست کاربران</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.user-groups.index') }}">گروه‌های کاربری</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.user-blockings.index') }}">مدیریت مسدودیت‌ها</a></li>
          </ul>
        </li>
        <!-- مدیریت پزشکان -->
        <li class="item-li i-users">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            مدیریت پزشکان
            <div class="d-flex justify-content-end w-100 align-items-center">
              <svg width="6" height="9" class="svg-caret-left" viewBox="0 0 7 11" fill="none"
                xmlns="http://www.w3.org/2000/svg" style="transition: transform 0.3s; transform: rotate(180deg);">
                <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M0.658146 0.39655C0.95104 0.103657 1.42591 0.103657 1.71881 0.39655L6.21881 4.89655C6.5117 5.18944 6.5117 5.66432 6.21881 5.95721L1.71881 10.4572C1.42591 10.7501 0.95104 10.7501 0.658146 10.4572C0.365253 10.1643 0.365253 9.68944 0.658146 9.39655L4.62782 5.42688L0.658146 1.45721C0.365253 1.16432 0.365253 0.689443 0.658146 0.39655Z"
                  fill="currentColor"></path>
              </svg>
            </div>
          </a>
          <ul class="drop-toggle d-none">
            <li class="item-li"><a href="{{ route('admin.panel.doctors.index') }}">لیست پزشکان</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.best-doctors.index') }}"> پزشک برتر</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.doctor-documents.index') }}">تأیید مدارک</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.doctor-specialties.index') }}">تخصص های پزشک</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.doctor-comments.index') }}">نظرات بیماران</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.doctors.permissions') }}">مدیریت دسترسی‌ها</a></li>
            <li class="item-li" style="opacity: 0.5; pointer-events: none;">
              <a href="javascript:void(0)"
                style="color: #6c757d; cursor: not-allowed; display: flex; align-items: center;">
                ارتقا حساب پزشکان
                <span class="badge bg-danger text-white ms-2" style="font-size: 10px; padding: 2px 6px;">به
                  زودی</span>
              </a>
            </li>
          </ul>
        </li>
        <!-- حق عضویت -->
        <li class="item-li i-courses">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            حق عضویت
            <div class="d-flex justify-content-end w-100 align-items-center">
              <svg width="6" height="9" class="svg-caret-left" viewBox="0 0 7 11" fill="none"
                xmlns="http://www.w3.org/2000/svg" style="transition: transform 0.3s; transform: rotate(180deg);">
                <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M0.658146 0.39655C0.95104 0.103657 1.42591 0.103657 1.71881 0.39655L6.21881 4.89655C6.5117 5.18944 6.5117 5.66432 6.21881 5.95721L1.71881 10.4572C1.42591 10.7501 0.95104 10.7501 0.658146 10.4572C0.365253 10.1643 0.365253 9.68944 0.658146 9.39655L4.62782 5.42688L0.658146 1.45721C0.365253 1.16432 0.365253 0.689443 0.658146 0.39655Z"
                  fill="currentColor"></path>
              </svg>
            </div>
          </a>
          <ul class="drop-toggle d-none">
            <li class="item-li"><a href="{{ route('admin.panel.user-subscriptions.index') }}">اشتراک‌ها</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.user-membership-plans.index') }}">طرح‌های عضویت</a>
            </li>
            <li class="item-li"><a href="{{ route('admin.panel.user-appointment-fees.index') }}">هزینه‌های
                نوبت‌دهی</a></li>
          </ul>
        </li>
        <!-- مدیریت منشی‌ها -->
        <li class="item-li i-users">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            مدیریت منشی‌ها
            <div class="d-flex justify-content-end w-100 align-items-center">
              <svg width="6" height="9" class="svg-caret-left" viewBox="0 0 7 11" fill="none"
                xmlns="http://www.w3.org/2000/svg" style="transition: transform 0.3s; transform: rotate(180deg);">
                <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M0.658146 0.39655C0.95104 0.103657 1.42591 0.103657 1.71881 0.39655L6.21881 4.89655C6.5117 5.18944 6.5117 5.66432 6.21881 5.95721L1.71881 10.4572C1.42591 10.7501 0.95104 10.7501 0.658146 10.4572C0.365253 10.1643 0.365253 9.68944 0.658146 9.39655L4.62782 5.42688L0.658146 1.45721C0.365253 1.16432 0.365253 0.689443 0.658146 0.39655Z"
                  fill="currentColor"></path>
              </svg>
            </div>
          </a>
          <ul class="drop-toggle d-none">
            <li class="item-li"><a href="{{ route('admin.panel.secretaries.index') }}">لیست منشی‌ها</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.secretaries.secreteries-permission') }}">دسترسی‌های
                منشی</a></li>
          </ul>
        </li>

        <!-- مدیریت بیماران -->
        <li class="item-li i-users">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            مدیریت بیماران
            <div class="d-flex justify-content-end w-100 align-items-center">
              <svg width="6" height="9" class="svg-caret-left" viewBox="0 0 7 11" fill="none"
                xmlns="http://www.w3.org/2000/svg" style="transition: transform 0.3s; transform: rotate(180deg);">
                <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M0.658146 0.39655C0.95104 0.103657 1.42591 0.103657 1.71881 0.39655L6.21881 4.89655C6.5117 5.18944 6.5117 5.66432 6.21881 5.95721L1.71881 10.4572C1.42591 10.7501 0.95104 10.7501 0.658146 10.4572C0.365253 10.1643 0.365253 9.68944 0.658146 9.39655L4.62782 5.42688L0.658146 1.45721C0.365253 1.16432 0.365253 0.689443 0.658146 0.39655Z"
                  fill="currentColor"></path>
              </svg>
            </div>
          </a>
          <ul class="drop-toggle d-none">
            <li class="item-li"><a href="{{ route('admin.panel.users.index') }}">لیست بیماران</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.sub-users.index') }}">کاربران زیرمجموعه</a></li>
            <li class="item-li" style="opacity: 0.5; pointer-events: none;">
              <a href="javascript:void(0)"
                style="color: #6c757d; cursor: not-allowed; display: flex; align-items: center;">
                پرونده پزشکی
                <span class="badge bg-danger text-white ms-2" style="font-size: 10px; padding: 2px 6px;">به
                  زودی</span>
              </a>
            </li>
          </ul>
        </li>
        <!-- مدیریت مراکز درمانی -->
        <li
          class="item-li i-courses {{ Request::routeIs('admin.panel.hospitals.index') || Request::routeIs('admin.panel.laboratories.index') || Request::routeIs('admin.panel.clinics.index') || Request::routeIs('admin.panel.treatment-centers.index') || Request::routeIs('admin.panel.imaging-centers.index') ? 'is-active' : '' }}">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            مراکز درمانی
            <div class="d-flex justify-content-end w-100 align-items-center">
              <svg width="6" height="9" class="svg-caret-left" viewBox="0 0 7 11" fill="none"
                xmlns="http://www.w3.org/2000/svg" style="transition: transform 0.3s; transform: rotate(180deg);">
                <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M0.658146 0.39655C0.95104 0.103657 1.42591 0.103657 1.71881 0.39655L6.21881 4.89655C6.5117 5.18944 6.5117 5.66432 6.21881 5.95721L1.71881 10.4572C1.42591 10.7501 0.95104 10.7501 0.658146 10.4572C0.365253 10.1643 0.365253 9.68944 0.658146 9.39655L4.62782 5.42688L0.658146 1.45721C0.365253 1.16432 0.365253 0.689443 0.658146 0.39655Z"
                  fill="currentColor"></path>
              </svg>
            </div>
          </a>
          <ul class="drop-toggle d-none">
            <li class="item-li {{ Request::routeIs('admin.panel.hospitals.index') ? 'is-active' : '' }}">
              <a href="{{ route('admin.panel.hospitals.index') }}">مدیریت بیمارستان</a>
            </li>
            <li class="item-li {{ Request::routeIs('admin.panel.laboratories.index') ? 'is-active' : '' }}">
              <a href="{{ route('admin.panel.laboratories.index') }}">مدیریت آزمایشگاه</a>
            </li>
            <li class="item-li {{ Request::routeIs('admin.panel.clinics.index') ? 'is-active' : '' }}">
              <a href="{{ route('admin.panel.clinics.index') }}">مدیریت کلینیک</a>
            </li>
            <li class="item-li {{ Request::routeIs('admin.panel.treatment-centers.index') ? 'is-active' : '' }}">
              <a href="{{ route('admin.panel.treatment-centers.index') }}">مدیریت درمانگاه</a>
            </li>
            <li class="item-li {{ Request::routeIs('admin.panel.imaging-centers.index') ? 'is-active' : '' }}">
              <a href="{{ route('admin.panel.imaging-centers.index') }}">مراکز تصویربرداری</a>
            </li>
            <li class="item-li {{ Request::routeIs('admin.panel.medical-centers.permissions') ? 'is-active' : '' }}">
              <a href="{{ route('admin.panel.medical-centers.permissions') }}">دسترسی‌های مراکز درمانی</a>
            </li>
          </ul>
        </li>
        <!-- مدیریت خدمات -->
        <li class="item-li i-courses">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            مدیریت خدمات
            <div class="d-flex justify-content-end w-100 align-items-center">
              <svg width="6" height="9" class="svg-caret-left" viewBox="0 0 7 11" fill="none"
                xmlns="http://www.w3.org/2000/svg" style="transition: transform 0.3s; transform: rotate(180deg);">
                <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M0.658146 0.39655C0.95104 0.103657 1.42591 0.103657 1.71881 0.39655L6.21881 4.89655C6.5117 5.18944 6.5117 5.66432 6.21881 5.95721L1.71881 10.4572C1.42591 10.7501 0.95104 10.7501 0.658146 10.4572C0.365253 10.1643 0.365253 9.68944 0.658146 9.39655L4.62782 5.42688L0.658146 1.45721C0.365253 1.16432 0.365253 0.689443 0.658146 0.39655Z"
                  fill="currentColor"></path>
              </svg>
            </div>
          </a>
          <ul class="drop-toggle d-none">
            <li class="item-li"><a href="{{ route('admin.panel.services.index') }}">لیست خدمات</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.doctor-services.index') }}">خدمات پزشکان</a></li>
          </ul>
        </li>

        <!-- مدیریت مالی -->
        <li class="item-li i-courses">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            مدیریت مالی
            <div class="d-flex justify-content-end w-100 align-items-center">
              <svg width="6" height="9" class="svg-caret-left" viewBox="0 0 7 11" fill="none"
                xmlns="http://www.w3.org/2000/svg" style="transition: transform 0.3s; transform: rotate(180deg);">
                <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M0.658146 0.39655C0.95104 0.103657 1.42591 0.103657 1.71881 0.39655L6.21881 4.89655C6.5117 5.18944 6.5117 5.66432 6.21881 5.95721L1.71881 10.4572C1.42591 10.7501 0.95104 10.7501 0.658146 10.4572C0.365253 10.1643 0.365253 9.68944 0.658146 9.39655L4.62782 5.42688L0.658146 1.45721C0.365253 1.16432 0.365253 0.689443 0.658146 0.39655Z"
                  fill="currentColor"></path>
              </svg>
            </div>
          </a>
          <ul class="drop-toggle d-none">
            <li class="item-li"><a href="{{ route('admin.panel.transactions.index') }}">تراکنش‌ها</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.doctor-wallets.index') }}">کیف‌پول</a></li>
          </ul>
        </li>
        <!-- مدیریت محتوا -->
        <li class="item-li i-courses">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            مدیریت محتوا
            <div class="d-flex justify-content-end w-100 align-items-center">
              <svg width="6" height="9" class="svg-caret-left" viewBox="0 0 7 11" fill="none"
                xmlns="http://www.w3.org/2000/svg" style="transition: transform 0.3s; transform: rotate(180deg);">
                <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M0.658146 0.39655C0.95104 0.103657 1.42591 0.103657 1.71881 0.39655L6.21881 4.89655C6.5117 5.18944 6.5117 5.66432 6.21881 5.95721L1.71881 10.4572C1.42591 10.7501 0.95104 10.7501 0.658146 10.4572C0.365253 10.1643 0.365253 9.68944 0.658146 9.39655L4.62782 5.42688L0.658146 1.45721C0.365253 1.16432 0.365253 0.689443 0.658146 0.39655Z"
                  fill="currentColor"></path>
              </svg>
            </div>
          </a>
          <ul class="drop-toggle d-none">
            <li class="item-li"><a href="{{ route('admin.panel.blogs.index') }}">مدیریت بلاگ</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.specialties.index') }}">مدیریت تخصص ها</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.zones.index') }}"> شهر و استان</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.reviews.index') }}">مدیریت نظرات</a></li>
            <li class="item-li" style="opacity: 0.5; pointer-events: none;">
              <a href="javascript:void(0)"
                style="color: #6c757d; cursor: not-allowed; display: flex; align-items: center;">
                مدیریت تبلیغات
                <span class="badge bg-danger text-white ms-2" style="font-size: 10px; padding: 2px 6px;">به
                  زودی</span>
              </a>
            </li>
          </ul>
        </li>
        <!-- تنظیمات سایت -->
        <li class="item-li i-courses">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            تنظیمات سایت
            <div class="d-flex justify-content-end w-100 align-items-center">
              <svg width="6" height="9" class="svg-caret-left" viewBox="0 0 7 11" fill="none"
                xmlns="http://www.w3.org/2000/svg" style="transition: transform 0.3s; transform: rotate(180deg);">
                <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M0.658146 0.39655C0.95104 0.103657 1.42591 0.103657 1.71881 0.39655L6.21881 4.89655C6.5117 5.18944 6.5117 5.66432 6.21881 5.95721L1.71881 10.4572C1.42591 10.7501 0.95104 10.7501 0.658146 10.4572C0.365253 10.1643 0.365253 9.68944 0.658146 9.39655L4.62782 5.42688L0.658146 1.45721C0.365253 1.16432 0.365253 0.689443 0.658146 0.39655Z"
                  fill="currentColor"></path>
              </svg>
            </div>
          </a>
          <ul class="drop-toggle d-none">
            <li class="item-li"><a href="{{ route('admin.panel.menus.index') }}">منوها</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.banner-texts.index') }}">بنر صفحه اصلی</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.footer-contents.index') }}">فوتر</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.setting.index') }}">تنظیمات عمومی</a></li>
            <li class="item-li" style="opacity: 0.5; pointer-events: none;">
              <a href="javascript:void(0)"
                style="color: #6c757d; cursor: not-allowed; display: flex; align-items: center;">
                تنظیمات اعلان‌ها
                <span class="badge bg-danger text-white ms-2" style="font-size: 10px; padding: 2px 6px;">به
                  زودی</span>
              </a>
            </li>
            <li class="item-li" style="opacity: 0.5; pointer-events: none;">
              <a href="javascript:void(0)"
                style="color: #6c757d; cursor: not-allowed; display: flex; align-items: center;">
                پشتیبان‌گیری
                <span class="badge bg-danger text-white ms-2" style="font-size: 10px; padding: 2px 6px;">به
                  زودی</span>
              </a>
            </li>
            <li class="item-li" style="opacity: 0.5; pointer-events: none;">
              <a href="javascript:void(0)"
                style="color: #6c757d; cursor: not-allowed; display: flex; align-items: center;">
                تنظیمات زبان
                <span class="badge bg-danger text-white ms-2" style="font-size: 10px; padding: 2px 6px;">به
                  زودی</span>
              </a>
            </li>
            <li class="item-li" style="opacity: 0.5; pointer-events: none;">
              <a href="javascript:void(0)"
                style="color: #6c757d; cursor: not-allowed; display: flex; align-items: center;">
                تنظیمات امنیتی
                <span class="badge bg-danger text-white ms-2" style="font-size: 10px; padding: 2px 6px;">به
                  زودی</span>
              </a>
            </li>
          </ul>
        </li>

        <!-- پشتیبانی -->
        <li class="item-li i-courses">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            پشتیبانی
            <div class="d-flex justify-content-end w-100 align-items-center">
              <svg width="6" height="9" class="svg-caret-left" viewBox="0 0 7 11" fill="none"
                xmlns="http://www.w3.org/2000/svg" style="transition: transform 0.3s; transform: rotate(180deg);">
                <path fill-rule="evenodd" clip-rule="evenodd"
                  d="M0.658146 0.39655C0.95104 0.103657 1.42591 0.103657 1.71881 0.39655L6.21881 4.89655C6.5117 5.18944 6.5117 5.66432 6.21881 5.95721L1.71881 10.4572C1.42591 10.7501 0.95104 10.7501 0.658146 10.4572C0.365253 10.1643 0.365253 9.68944 0.658146 9.39655L4.62782 5.42688L0.658146 1.45721C0.365253 1.16432 0.365253 0.689443 0.658146 0.39655Z"
                  fill="currentColor"></path>
              </svg>
            </div>
          </a>
          <ul class="drop-toggle d-none">
            <li class="item-li"><a href="{{ route('admin.panel.tickets.index') }}">تیکت‌های پشتیبانی</a></li>
          </ul>
        </li>
        <!-- خروج -->
        <li class="item-li i-exit">
          <a href="{{ route('admin.auth.logout') }}" class="logout-sidebar">خروج</a>
        </li>
      @endif
    </ul>
  </div>
  <!-- اسکریپت بدون تغییر -->
  <script>
    document.addEventListener('livewire:init', () => {
      toastr.options = {
        positionClass: 'toast-top-right',
        timeOut: 3000,
      };
      const photoInput = document.getElementById('profile-photo-input');
      const profileImg = document.getElementById('profile-photo-img');
      if (photoInput) {
        photoInput.addEventListener('change', function() {
          if (this.files && this.files[0]) {
            uploadPhoto(this.files[0]);
          }
        });
        photoInput.addEventListener('dblclick', function() {
          console.log('Double click on input');
          this.click();
        });
      }
      if (profileImg) {
        profileImg.addEventListener('dblclick', function() {
          photoInput.click();
        });
        profileImg.addEventListener('contextmenu', function(e) {
          e.preventDefault();
          photoInput.click();
        });
      }

      function uploadPhoto(file) {
        const formData = new FormData();
        formData.append('photo', file);
        formData.append('_token', '{{ csrf_token() }}');
        fetch("{{ route('admin.upload-photo') }}", {
            method: 'POST',
            body: formData,
            headers: {
              'Accept': 'application/json'
            }
          })
          .then(response => {
            if (!response.ok) {
              throw new Error('Server returned status: ' + response.status);
            }
            return response.text();
          })
          .then(text => {
            const data = JSON.parse(text);
            if (data.success) {
              toastr.success(data.message);
              profileImg.src = data.path;
              photoInput.value = '';
            } else {
              toastr.error(data.message);
            }
          })
          .catch(error => {
            toastr.error('خطا در آپلود عکس: ' + error.message);
          });
      }
    });
  </script>
</div>

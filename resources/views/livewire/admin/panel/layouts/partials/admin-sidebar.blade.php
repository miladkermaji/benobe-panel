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
    <ul class="mt-65" id="mt-65">
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
            <li class="item-li"><a href="{{ route('admin.panel.user-groups.index') }}">گروه‌های کاربری</a></li>
            <li class="item-li"><a href="#">مدیریت نقش‌ها و دسترسی‌ها</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.user-blockings.index') }}">کاربران مسدود</a></li>
           
            <!-- جدید -->
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
            <li class="item-li"><a href="#">ارتقا حساب پزشکان</a></li>
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
        <!-- مدیریت نوبت‌ها -->
        <li class="item-li i-courses">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            مدیریت نوبت‌ها
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
            <li class="item-li"><a href="{{ route('admin.panel.appointments.index') }}">لیست نوبت‌ها</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.manual-appointments.index') }}">نوبت‌دهی دستی</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.manual-appointment-settings.index') }}">تنظیمات
                نوبت‌دهی</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.doctor-holidays.index') }}">تعطیلات</a></li>
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
            <li class="item-li"><a href="{{ route('dr-patient-records') }}">پرونده پزشکی</a></li>
          </ul>
        </li>
        <!-- مدیریت fdlhvsjhk -->
        <li class="item-li i-courses">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            مدیریت بیمارستان
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
            <li class="item-li"><a href="{{ route('admin.panel.hospitals.index') }}"> مدیریت بیمارستان</a></li>
          </ul>
        </li>
        <!-- مدیریت آزمایشگاه -->
        <li class="item-li i-courses">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            مدیریت آزمایشگاه
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
            <li class="item-li"><a href="{{ route('admin.panel.laboratories.index') }}"> مدیریت آزمایشگاه</a></li>
          </ul>
        </li>
        <!-- مدیریت کلینیک‌ها -->
        <li class="item-li i-courses">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            مدیریت کلینیک‌ها
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
            <li class="item-li"><a href="{{ route('admin.panel.clinics.index') }}"> مدیریت کلینیک</a></li>
            <li class="item-li"><a href="#"> بیعانه</a></li>
          </ul>
        </li>
        <!-- مدیریت کلینیک‌ها -->
        <li class="item-li i-courses">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            مدیریت درمانگاه
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
            <li class="item-li"><a href="{{ route('admin.panel.treatment-centers.index') }}"> مدیریت درمانگاه</a>
            </li>
          </ul>
        </li>
        <li class="item-li i-courses">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            مراکزتصویربرداری
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
            <li class="item-li"><a href="{{ route('admin.panel.imaging-centers.index') }}"> مراکزتصویربرداری</a></li>
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
            <li class="item-li"><a href="{{ route('admin.panel.doctor-services.index') }}"> خدمات پزشکان</a></li>
          </ul>
        </li>
        <!-- نسخه الکترونیک -->
        <li class="item-li i-courses">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            نسخه الکترونیک
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
            <li class="item-li"><a href="{{ route('prescription.index') }}">لیست نسخه‌ها</a></li>
            <li class="item-li"><a href="{{ route('prescription.create') }}">ایجاد نسخه</a></li>
            <li class="item-li"><a href="{{ route('favorite.templates.index') }}">قالب‌های مورد علاقه</a></li>
            <li class="item-li"><a href="{{ route('templates.favorite.service.index') }}">خدمات مورد علاقه</a></li>
            <li class="item-li"><a href="{{ route('providers.index') }}">ارائه‌دهندگان</a></li>
          </ul>
        </li>
        <!-- مدیریت بیمه -->
        <li class="item-li i-courses">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            مدیریت بیمه
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
            <li class="item-li"><a href="{{ route('admin.panel.insurances.index') }}">لیست بیمه‌ها</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.doctor-insurances.index') }}">بیمه های دکتر</a></li>
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
            <li class="item-li"><a href="#">تراکنش‌ها</a></li>
            <li class="item-li"><a href="{{ route('dr-wallet') }}">کیف‌پول</a></li>
            <li class="item-li"><a href="{{ route('dr-wallet-charge') }}">شارژ کیف‌پول</a></li>
            <li class="item-li"><a href="#">تسویه‌حساب‌ها</a></li>
            <li class="item-li"><a href="{{ route('dr-payment-setting') }}">تنظیمات مالی</a></li>
            <li class="item-li"><a href="#">مدیریت تخفیف‌ها</a></li>
          </ul>
        </li>
        <!-- ارتباط با بیماران -->
        <li class="item-li i-courses">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            ارتباط با بیماران
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
            <li class="item-li"><a href="#">ارسال پیام</a></li>
            <li class="item-li"><a href="#">اعلان‌های گروهی</a></li>
            <li class="item-li"><a href="#">پیام‌های پیش‌فرض</a></li>
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
            <li class="item-li"><a href="#">مدیریت بلاگ</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.specialties.index') }}">مدیریت تخصص ها</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.zones.index') }}"> شهر و استان</a></li>
            <li class="item-li"><a href="#">اخبار پزشکی</a></li>
            <li class="item-li"><a href="{{ route('admin.panel.reviews.index') }}">مدیریت نظرات</a></li>
            <li class="item-li"><a href="#">گالری تصاویر</a></li>
            <li class="item-li"><a href="#">مدیریت تبلیغات</a></li>
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
            <li class="item-li"><a href="#">تنظیمات عمومی</a></li>
            <li class="item-li"><a href="#">مدیریت صفحات</a></li>
            <li class="item-li"><a href="#">تنظیمات SEO</a></li>
            <li class="item-li"><a href="#">تنظیمات اعلان‌ها</a></li>
            <li class="item-li"><a href="#">پشتیبان‌گیری</a></li>
            <li class="item-li"><a href="#">تنظیمات زبان</a></li>
            <li class="item-li"><a href="{{ route('dr-edit-profile') }}">پروفایل من</a></li>
            <li class="item-li"><a href="{{ route('dr-messengers-update') }}">تنظیمات پیام‌رسان‌ها</a></li>
            <li class="item-li"><a href="{{ route('dr-edit-profile-security') }}">تنظیمات امنیتی</a></li>
          </ul>
        </li>
        <!-- گزارش‌ها -->
        <li class="item-li i-courses">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            گزارش‌ها
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
            <li class="item-li"><a href="#">گزارش فعالیت کاربران</a></li>
            <li class="item-li"><a href="#">گزارش نوبت‌ها</a></li>
            <li class="item-li"><a href="#">گزارش مالی</a></li>
            <li class="item-li"><a href="{{ route('dr-my-performance') }}">عملکرد من</a></li>
            <li class="item-li"><a href="#">آمار بازدید سایت</a></li>
            <li class="item-li"><a href="#">تحلیل داده‌ها</a></li>
            <li class="item-li"><a href="#">نمودارهای آماری</a></li>
            <li class="item-li"><a href="#">گزارش خدمات پزشکان</a></li> <!-- جدید -->
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
            <li class="item-li"><a href="{{ route('dr-panel-tickets') }}">تیکت‌های پشتیبانی</a></li>
            <li class="item-li"><a href="#">ارسال تیکت جدید</a></li>
            <li class="item-li"><a href="#">سوالات متداول</a></li>
            <li class="item-li"><a href="#">راهنمای سیستم</a></li>
            <li class="item-li"><a href="#">چت آنلاین</a></li>
            <li class="item-li"><a href="#">تماس با ما</a></li>
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

<div class="sidebar__nav border-top border-left">
  <div class="sidebar__fixed">
    <span class="bars d-none padding-0-18"></span>
    <div class="profile__info border cursor-pointer text-center">
      <div class="avatar__img cursor-pointer">
        <img id="profile-photo-img"
          src="{{ $user->profile_photo_path ? Storage::url($user->profile_photo_path) : asset('dr-assets/panel/img/pro.jpg') }}"
          class="avatar___img cursor-pointer">
        <input type="file" accept="image/*" class="avatar-img__input" id="profile-photo-input">
        <div class="v-dialog__container" style="display: block;"></div>
        <div class="box__camera default__avatar"></div>
      </div>
      <span class="profile__name sidebar-full-name">
        {{ optional($user)->first_name }} {{ optional($user)->last_name }}
      </span>
      <span class="fs-11 fw-bold" id="takhasos-txt">{{ $specialtyName }}</span>
    </div>
  </div>
  <div class="sidebar__scrollable">
    <ul class="" id="">
      @if (Auth::guard('doctor')->check())
        <li class="item-li i-dashboard {{ Request::routeIs('dr-panel') ? 'is-active' : '' }}">
          <a href="{{ route('dr-panel') }}">داشبورد</a>
        </li>
        <li class="item-li i-courses">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            نوبت اینترنتی
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
            <li class="item-li {{ Request::routeIs('dr-appointments') ? 'is-active' : '' }}">
              <a href="{{ route('dr-appointments') }}">مراجعین من</a>
            </li>
            <li class="item-li {{ Request::routeIs('dr-workhours') ? 'is-active' : '' }}">
              <a href="{{ route('dr-workhours') }}">ساعت کاری</a>
            </li>
            <li class="item-li {{ Request::routeIs('dr.panel.doctornotes.index') ? 'is-active' : '' }}">
              <a href="{{ route('dr.panel.doctornotes.index') }}"> توضیحات نوبت</a>
            </li>
            <li class="item-li {{ Request::routeIs('dr-mySpecialDays') ? 'is-active' : '' }}">
              <a href="{{ route('dr-mySpecialDays') }}">روزهای خاص</a>
            </li>
            <li class="item-li {{ Request::routeIs('dr-manual_nobat_setting') ? 'is-active' : '' }}">
              <a href="{{ route('dr-manual_nobat_setting') }}">تنظیمات نوبت دستی</a>
            </li>
            <li class="item-li {{ Request::routeIs('dr-manual_nobat') ? 'is-active' : '' }}">
              <a href="{{ route('dr-manual_nobat') }}">ثبت نوبت دستی</a>
            </li>
            <li class="item-li {{ Request::routeIs('dr-scheduleSetting') ? 'is-active' : '' }}">
              <a href="{{ route('dr-scheduleSetting') }}">تنظیمات نوبت</a>
            </li>
            <li class="item-li {{ Request::routeIs('dr-vacation') ? 'is-active' : '' }}">
              <a href="{{ route('dr-vacation') }}">تعطیلات</a>
            </li>
            <li class="item-li {{ Request::routeIs('doctor-blocking-users.index') ? 'is-active' : '' }}">
              <a href="{{ route('doctor-blocking-users.index') }}">کاربران مسدود</a>
            </li>
          </ul>
        </li>
        <li
          class="item-li i-moshavere {{ Request::routeIs('dr-moshavere_setting') || Request::routeIs('dr-moshavere_waiting') || Request::routeIs('consult-term.index') ? 'is-active' : '' }}">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            مشاوره
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
            <li class="item-li i-courses {{ Request::routeIs('dr-moshavere_setting') ? 'is-active' : '' }}">
              <a href="{{ route('dr-moshavere_setting') }}">برنامه‌ریزی مشاوره</a>
            </li>
            <li class="item-li i-user__inforamtion {{ Request::routeIs('dr-moshavere_waiting') ? 'is-active' : '' }}">
              <a href="{{ route('dr-moshavere_waiting') }}">گزارش مشاوره</a>
            </li>
            <li class="item-li {{ Request::routeIs('dr.panel.doctornotes.index') ? 'is-active' : '' }}">
              <a href="{{ route('dr.panel.doctornotes.index') }}"> توضیحات نوبت</a>
            </li>
            <li
              class="item-li i-user__inforamtion {{ Request::routeIs('dr-mySpecialDays-counseling') ? 'is-active' : '' }}">
              <a href="{{ route('dr-mySpecialDays-counseling') }}">روزهای خاص</a>
            </li>
            <li class="item-li i-user__inforamtion {{ Request::routeIs('consult-term.index') ? 'is-active' : '' }}">
              <a href="{{ route('consult-term.index') }}">قوانین مشاوره</a>
            </li>
          </ul>
        </li>
        <li
          class="item-li i-checkout__request {{ Request::routeIs('dr.panel.doctor-services.index') ? 'is-active' : '' }}">
          <a href="{{ route('dr.panel.doctor-services.index') }}">خدمات</a>
        </li>
        <li
          class="item-li i-banners {{ Request::routeIs('prescription.index') || Request::routeIs('providers.index') || Request::routeIs('favorite.templates.index') || Request::routeIs('templates.favorite.service.index') ? 'is-active' : '' }}">
          <a href="#">
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
            <li class="item-li"><a href="{{ route('dr-patient-records') }}">پرونده پزشکی</a></li>

            <li class="item-li i-courses {{ Request::routeIs('prescription.index') ? 'is-active' : '' }}">
              <a href="{{ route('prescription.index') }}">نسخه‌های ثبت شده</a>
            </li>
            <li class="item-li i-courses {{ Request::routeIs('providers.index') ? 'is-active' : '' }}">
              <a href="{{ route('providers.index') }}">بیمه‌های من</a>
            </li>
            <li class="item-li i-courses {{ Request::routeIs('favorite.templates.index') ? 'is-active' : '' }}">
              <a href="{{ route('favorite.templates.index') }}">نسخه پراستفاده</a>
            </li>
            <li
              class="item-li i-courses {{ Request::routeIs('templates.favorite.service.index') ? 'is-active' : '' }}">
              <a href="{{ route('templates.favorite.service.index') }}">اقلام پراستفاده</a>
            </li>
          </ul>
        </li>
        <li
          class="item-li i-my__peyments {{ Request::routeIs('dr-wallet') || Request::routeIs('dr-payment-setting') ? 'is-active' : '' }} d-flex flex-column justify-content-center"
          id="gozaresh-mali">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            گزارش مالی
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
            <li class="item-li i-user__inforamtion {{ Request::routeIs('dr-payment-setting') ? 'is-active' : '' }}">
              <a href="{{ route('dr-payment-setting') }}">پرداخت</a>
            </li>
            <li class="item-li i-user__inforamtion {{ Request::routeIs('dr-wallet-charge') ? 'is-active' : '' }}">
              <a href="{{ route('dr-wallet-charge') }}">شارژ کیف‌پول</a>
            </li>
          </ul>
        </li>
        <!-- ارتباط با بیماران (جدید) -->
        <li class="item-li i-users">
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
            <li class="item-li"><a href="{{ route('dr.panel.send-message') }}">ارسال پیام</a></li>
          </ul>
        </li>
        <li
          class="item-li i-checkout__request {{ Request::routeIs('dr-patient-records') ? 'is-active' : '' }} d-flex flex-column justify-content-center"
          id="gozaresh-mali">
          <a href="{{ route('dr-patient-records') }}">پرونده الکترونیک</a>
        </li>
        <li
          class="item-li i-user__secratary {{ Request::routeIs('dr-secretary-management') ? 'is-active' : '' }} d-flex flex-column justify-content-center"
          id="gozaresh-mali">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            منشی
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
            <li
              class="item-li i-user__inforamtion {{ Request::routeIs('dr-secretary-management') ? 'is-active' : '' }}">
              <a href="{{ route('dr-secretary-management') }}">مدیریت منشی‌ها</a>
            </li>
          </ul>
        </li>
        <li
          class="item-li i-clinic {{ Request::routeIs('dr-clinic-management') || Request::routeIs('doctors.clinic.cost') || Request::routeIs('duration.index') || Request::routeIs('activation.workhours.index') ? 'is-active' : '' }} d-flex flex-column justify-content-center"
          id="gozaresh-mali">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            مطب
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
            <li class="item-li i-user__inforamtion {{ Request::routeIs('dr-clinic-management') ? 'is-active' : '' }}">
              <a href="{{ route('dr-clinic-management') }}">مدیریت مطب</a>
            </li>
            <li class="item-li i-user__inforamtion {{ Request::routeIs('doctors.clinic.cost') ? 'is-active' : '' }}">
              <a href="#">هزینه‌ها</a>
            </li>
            <li class="item-li i-user__inforamtion {{ Request::routeIs('duration.index') ? 'is-active' : '' }}">
              <a href="#">مدت زمان ویزیت</a>
            </li>
            <li
              class="item-li i-user__inforamtion {{ Request::routeIs('activation.workhours.index') ? 'is-active' : '' }}">
              <a href="#">ساعات کاری کلینیک</a>
            </li>
            <li class="item-li i-user__inforamtion {{ Request::routeIs('dr-office-gallery') ? 'is-active' : '' }}">
              <a href="#">گالری تصاویر</a>
            </li>
            <li class="item-li i-user__inforamtion {{ Request::routeIs('dr-office-medicalDoc') ? 'is-active' : '' }}">
              <a href="#">مدارک من</a>
            </li>
          </ul>
        </li>
        <li class="item-li i-checkout__request {{ Request::routeIs('dr-bime') ? 'is-active' : '' }}">
          <a href="{{ route('dr-bime') }}">بیمه‌ها</a>
        </li>
        <li class="item-li i-checkout__request {{ Request::routeIs('dr-secretary-permissions') ? 'is-active' : '' }}">
          <a href="{{ route('dr-secretary-permissions') }}">دسترسی‌ها</a>
        </li>
        <li
          class="item-li i-users {{ Request::routeIs('dr-edit-profile') || Request::routeIs('dr-edit-profile-security') || Request::routeIs('dr-edit-profile-upgrade') || Request::routeIs('dr-my-performance') || Request::routeIs('dr-subuser') || Request::routeIs('my-dr-appointments') ? 'is-active' : '' }} d-flex flex-column justify-content-center"
          id="hesab-karbari">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            حساب کاربری
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
            <li class="item-li {{ Request::routeIs('dr-edit-profile') ? 'is-active' : '' }}">
              <a href="{{ route('dr-edit-profile') }}">حساب کاربری</a>
            </li>
            <li class="item-li {{ Request::routeIs('my-dr-appointments') ? 'is-active' : '' }}">
              <a href="{{ route('my-dr-appointments') }}">نوبت‌های من</a>
            </li>
            <li class="item-li {{ Request::routeIs('dr-edit-profile-security') ? 'is-active' : '' }}">
              <a href="{{ route('dr-edit-profile-security') }}">امنیت</a>
            </li>
            <li class="item-li {{ Request::routeIs('dr-edit-profile-upgrade') ? 'is-active' : '' }}">
              <a href="{{ route('dr-edit-profile-upgrade') }}">ارتقا حساب</a>
            </li>
            <li class="item-li {{ Request::routeIs('dr-my-performance') ? 'is-active' : '' }}">
              <a href="{{ route('dr-my-performance') }}">عملکرد و رتبه من</a>
            </li>
            <li class="item-li {{ Request::routeIs('dr-subuser') ? 'is-active' : '' }}">
              <a href="{{ route('dr-subuser') }}">کاربران زیرمجموعه</a>
            </li>
          </ul>
        </li>
        <!-- تنظیمات پیشرفته (جدید) -->
        <li class="item-li i-users">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            تنظیمات پیشرفته
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
            <li class="item-li"><a href="#">تنظیمات پیام‌رسان‌ها</a></li>
            <li class="item-li"><a href="#">تنظیمات اعلان‌ها</a></li>
            <li class="item-li"><a href="#">پشتیبان‌گیری</a></li>
          </ul>
        </li>
        <li
          class="item-li i-comments {{ Request::routeIs('dr-panel-tickets') ? 'is-active' : '' }} d-flex flex-column justify-content-center"
          id="gozaresh-mali">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            پیام
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
            <li class="item-li i-tickets {{ Request::routeIs('dr-panel-tickets') ? 'is-active' : '' }}">
              <a href="{{ route('dr-panel-tickets') }}">تیکت‌ها</a>
            </li>
            <li class="item-li i-comments">
              <a href="#">صفحه گفتگو</a>
            </li>
          </ul>
        </li>
        <!-- گزارش‌ها و آمار (به‌روز شده) -->
        <li class="item-li i-transactions {{ Request::routeIs('dr-my-performance-chart') ? 'is-active' : '' }}">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            گزارش‌ها و آمار
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
            <li class="item-li {{ Request::routeIs('dr-my-performance-chart') ? 'is-active' : '' }}">
              <a href="{{ route('dr-my-performance-chart') }}">آمار و نمودار</a>
            </li>
            <li class="item-li"><a href="#">گزارش نوبت‌های لغو شده</a></li>
            <li class="item-li"><a href="#">تحلیل داده‌ها</a></li>
          </ul>
        </li>
        <!-- مدیریت محتوا (جدید) -->
        <li class="item-li i-users">
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
            <li class="item-li"><a href="#">نظرات بیماران</a></li>
          </ul>
        </li>
        <li class="item-li i-exit">
          <a href="{{ route('dr.auth.logout') }}" class="logout-sidebar">خروج</a>
        </li>
      @elseif(Auth::guard('secretary')->check())
        @php
          $permissions = is_array($permissions) ? $permissions : json_decode($permissions ?? '[]', true);
        @endphp
        @foreach (config('permissions') as $permissionKey => $permissionData)
          @if (in_array($permissionKey, $permissions))
            <li
              class="item-li {{ Request::routeIs($permissionData['routes'][0] ?? '') ? 'is-active' : '' }} {{ $permissionData['icon'] }}">
              <a href="{{ is_array($permissionData['routes']) && !empty($permissionData['routes'][0]) ? route($permissionData['routes'][0]) : (is_string($permissionData['routes']) ? route($permissionData['routes']) : '#') }}"
                class="d-flex justify-content-between w-100 align-items-center">
                {{ $permissionData['title'] }}
                @if (
                    $permissionKey !== 'dashboard' &&
                        $permissionKey !== 'insurance' &&
                        $permissionKey !== 'permissions' &&
                        $permissionKey !== 'statistics')
                  <div class="d-flex justify-content-end w-100 align-items-center">
                    <svg width="6" height="9" class="svg-caret-left" viewBox="0 0 7 11" fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                      style="transition: transform 0.3s; transform: rotate(180deg);">
                      <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M0.658146 0.39655C0.95104 0.103657 1.42591 0.103657 1.71881 0.39655L6.21881 4.89655C6.5117 5.18944 6.5117 5.66432 6.21881 5.95721L1.71881 10.4572C1.42591 10.7501 0.95104 10.7501 0.658146 10.4572C0.365253 10.1643 0.365253 9.68944 0.658146 9.39655L4.62782 5.42688L0.658146 1.45721C0.365253 1.16432 0.365253 0.689443 0.658146 0.39655Z"
                        fill="currentColor"></path>
                    </svg>
                  </div>
                @endif
              </a>
              @if (!empty($permissionData['routes']) && is_array($permissionData['routes']))
                <ul class="drop-toggle d-none">
                  @foreach ($permissionData['routes'] as $routeKey => $routeTitle)
                    @if (is_string($routeKey) && Route::has($routeKey))
                      <li class="item-li {{ Request::routeIs($routeKey) ? 'is-active' : '' }}">
                        <a href="{{ route($routeKey) }}">{{ $routeTitle }}</a>
                      </li>
                    @endif
                  @endforeach
                </ul>
              @endif
            </li>
          @endif
        @endforeach
        <li class="item-li i-exit">
          <a href="{{ route('dr.auth.logout') }}" class="logout-sidebar">خروج</a>
        </li>
      @endif
    </ul>
  </div>

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
            console.log('Photo selected:', this.files[0].name);
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
          console.log('Double click on image');
          photoInput.click();
        });

        profileImg.addEventListener('contextmenu', function(e) {
          e.preventDefault();
          console.log('Right click on image');
          photoInput.click();
        });
      }

      function uploadPhoto(file) {
        const formData = new FormData();
        formData.append('photo', file);
        formData.append('_token', '{{ csrf_token() }}');

        console.log('Uploading photo...');

        fetch("{{ route('dr.upload-photo') }}", {
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
            console.log('Raw server response:', text);
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
            console.error('Upload error:', error);
            toastr.error('خطا در آپلود عکس: ' + error.message);
          });
      }
    });
  </script>
</div>

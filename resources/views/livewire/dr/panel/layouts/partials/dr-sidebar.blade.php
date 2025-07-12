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
      @if ($this->hasPermission('dashboard'))
        <li class="item-li i-dashboard {{ Request::routeIs('dr-panel') ? 'is-active' : '' }}">
          <a href="{{ route('dr-panel') }}">داشبورد</a>
        </li>
      @endif
      @if ($this->hasPermission('dr-workhours'))
        <li class="item-li i-checkout__request {{ Request::routeIs('dr-workhours') ? 'is-active' : '' }}">
          <a href="{{ route('dr-workhours') }}">ساعت کاری</a>
        </li>
      @endif
      @if ($this->hasPermission('appointments'))
        <li
          class="item-li i-courses {{ Request::routeIs('dr-appointments') || Request::routeIs('dr.panel.doctornotes.index') || Request::routeIs('dr-mySpecialDays') || Request::routeIs('dr-manual_nobat_setting') || Request::routeIs('dr-scheduleSetting') || Request::routeIs('dr-vacation') || Request::routeIs('doctor-blocking-users.index') ? 'is-active' : '' }}">
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
            @if ($this->hasPermission('dr-appointments'))
              <li class="item-li {{ Request::routeIs('dr-appointments') ? 'is-active' : '' }}">
                <a href="{{ route('dr-appointments') }}"> لیست نوبت ها</a>
              </li>
            @endif

            @if ($this->hasPermission('dr.panel.doctornotes.index'))
              <li class="item-li {{ Request::routeIs('dr.panel.doctornotes.index') ? 'is-active' : '' }}">
                <a href="{{ route('dr.panel.doctornotes.index') }}"> توضیحات نوبت</a>
              </li>
            @endif
            @if ($this->hasPermission('dr-mySpecialDays'))
              <li class="item-li {{ Request::routeIs('dr-mySpecialDays') ? 'is-active' : '' }}">
                <a href="{{ route('dr-mySpecialDays') }}">روزهای خاص</a>
              </li>
            @endif
            {{--    @if ($this->hasPermission('dr-manual_nobat'))
              <li class="item-li {{ Request::routeIs('dr-manual_nobat') ? 'is-active' : '' }}">
                <a href="{{ route('dr-manual_nobat') }}">ثبت نوبت دستی</a>
              </li>
            @endif --}}
            @if ($this->hasPermission('dr-scheduleSetting'))
              <li class="item-li {{ Request::routeIs('dr-scheduleSetting') ? 'is-active' : '' }}">
                <a href="{{ route('dr-scheduleSetting') }}">تنظیمات نوبت</a>
              </li>
            @endif
            @if ($this->hasPermission('dr-vacation'))
              <li class="item-li {{ Request::routeIs('dr-vacation') ? 'is-active' : '' }}">
                <a href="{{ route('dr-vacation') }}">تعطیلات</a>
              </li>
            @endif
            @if ($this->hasPermission('doctor-blocking-users.index'))
              <li class="item-li {{ Request::routeIs('doctor-blocking-users.index') ? 'is-active' : '' }}">
                <a href="{{ route('doctor-blocking-users.index') }}">کاربران مسدود</a>
              </li>
            @endif
          </ul>
        </li>
      @endif

      @if ($this->hasPermission('consult'))
        <li
          class="item-li i-moshavere {{ Request::routeIs('dr-moshavere_setting') || Request::routeIs('dr-moshavere_waiting') || Request::routeIs('consult-term.index') || Request::routeIs('dr-mySpecialDays-counseling') ? 'is-active' : '' }}">
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
            @if ($this->hasPermission('dr-moshavere_setting'))
              <li class="item-li i-courses {{ Request::routeIs('dr-moshavere_setting') ? 'is-active' : '' }}">
                <a href="{{ route('dr-moshavere_setting') }}">برنامه‌ریزی مشاوره</a>
              </li>
            @endif
            @if ($this->hasPermission('dr-moshavere_waiting'))
              <li
                class="item-li i-user__inforamtion {{ Request::routeIs('dr-moshavere_waiting') ? 'is-active' : '' }}">
                <a href="{{ route('dr-moshavere_waiting') }}">گزارش مشاوره</a>
              </li>
            @endif
            @if ($this->hasPermission('dr-mySpecialDays-counseling'))
              <li
                class="item-li i-user__inforamtion {{ Request::routeIs('dr-mySpecialDays-counseling') ? 'is-active' : '' }}">
                <a href="{{ route('dr-mySpecialDays-counseling') }}">روزهای خاص</a>
              </li>
            @endif
            @if ($this->hasPermission('consult-term.index'))
              <li class="item-li i-user__inforamtion {{ Request::routeIs('consult-term.index') ? 'is-active' : '' }}">
                <a href="{{ route('consult-term.index') }}">قوانین مشاوره</a>
              </li>
            @endif
          </ul>
        </li>
      @endif

      @if ($this->hasPermission('insurance'))
        <li
          class="item-li i-checkout__request {{ Request::routeIs('dr.panel.doctor-services.index') ? 'is-active' : '' }}">
          <a href="{{ route('dr.panel.doctor-services.index') }}">خدمات و بیمه</a>
        </li>
      @endif

      @if ($this->hasPermission('prescription'))
        <li
          class="item-li i-banners {{ Request::routeIs('prescription.index') || Request::routeIs('providers.index') || Request::routeIs('favorite.templates.index') || Request::routeIs('templates.favorite.service.index') || Request::routeIs('dr-patient-records') ? 'is-active' : '' }}">
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
            @if ($this->hasPermission('dr-patient-records'))
              <li class="item-li"><a href="{{ route('dr-patient-records') }}">پرونده پزشکی</a></li>
            @endif
            @if ($this->hasPermission('prescription.index'))
              <li class="item-li i-courses {{ Request::routeIs('prescription.index') ? 'is-active' : '' }}">
                <a href="{{ route('prescription.index') }}">نسخه‌های ثبت شده</a>
              </li>
            @endif
            @if ($this->hasPermission('providers.index'))
              <li class="item-li i-courses {{ Request::routeIs('providers.index') ? 'is-active' : '' }}">
                <a href="{{ route('providers.index') }}">بیمه‌های من</a>
              </li>
            @endif
            @if ($this->hasPermission('favorite.templates.index'))
              <li class="item-li i-courses {{ Request::routeIs('favorite.templates.index') ? 'is-active' : '' }}">
                <a href="{{ route('favorite.templates.index') }}">نسخه پراستفاده</a>
              </li>
            @endif
            @if ($this->hasPermission('templates.favorite.service.index'))
              <li
                class="item-li i-courses {{ Request::routeIs('templates.favorite.service.index') ? 'is-active' : '' }}">
                <a href="{{ route('templates.favorite.service.index') }}">اقلام پراستفاده</a>
              </li>
            @endif
          </ul>
        </li>
      @endif

      @if ($this->hasPermission('financial_reports'))
        <li
          class="item-li i-my__peyments {{ Request::routeIs('dr-wallet') || Request::routeIs('dr-payment-setting') || Request::routeIs('dr.panel.financial-reports.index') || Request::routeIs('dr-wallet-charge') ? 'is-active' : '' }} d-flex flex-column justify-content-center"
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
            @if ($this->hasPermission('dr.panel.financial-reports.index'))
              <li
                class="item-li i-user__inforamtion {{ Request::routeIs('dr.panel.financial-reports.index') ? 'is-active' : '' }}">
                <a href="{{ route('dr.panel.financial-reports.index') }}">گزارش مالی</a>
              </li>
            @endif
            @if ($this->hasPermission('dr-payment-setting'))
              <li class="item-li i-user__inforamtion {{ Request::routeIs('dr-payment-setting') ? 'is-active' : '' }}">
                <a href="{{ route('dr-payment-setting') }}">پرداخت</a>
              </li>
            @endif
            @if ($this->hasPermission('dr-wallet-charge'))
              <li class="item-li i-user__inforamtion {{ Request::routeIs('dr-wallet-charge') ? 'is-active' : '' }}">
                <a href="{{ route('dr-wallet-charge') }}">شارژ کیف‌پول</a>
              </li>
            @endif
          </ul>
        </li>
      @endif

      @if ($this->hasPermission('patient_communication'))
        <li class="item-li i-users {{ Request::routeIs('dr.panel.send-message') ? 'is-active' : '' }}">
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
            @if ($this->hasPermission('dr.panel.send-message'))
              <li class="item-li"><a href="{{ route('dr.panel.send-message') }}">ارسال پیام</a></li>
            @endif
          </ul>
        </li>
      @endif

      @if ($this->hasPermission('patient_records'))
        <li
          class="item-li i-checkout__request {{ Request::routeIs('dr-patient-records') ? 'is-active' : '' }} d-flex flex-column justify-content-center"
          id="gozaresh-mali">
          <a href="{{ route('dr-patient-records') }}">پرونده الکترونیک</a>
        </li>
      @endif

      @if ($this->hasPermission('secretary_management'))
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
            @if ($this->hasPermission('secretary_management'))
              <li
                class="item-li i-user__inforamtion {{ Request::routeIs('dr-secretary-management') ? 'is-active' : '' }}">
                <a href="{{ route('dr-secretary-management') }}">مدیریت منشی‌ها</a>
              </li>
            @endif
            @if ($this->hasPermission('permissions'))
              <li
                class="item-li i-checkout__request {{ Request::routeIs('dr-secretary-permissions') ? 'is-active' : '' }}">
                <a href="{{ route('dr-secretary-permissions') }}">دسترسی‌ها</a>
              </li>
            @endif
          </ul>
        </li>
      @endif

      @if ($this->hasPermission('clinic_management'))
        <li
          class="item-li i-clinic {{ Request::routeIs('dr-clinic-management') || Request::routeIs('doctors.clinic.cost') || Request::routeIs('duration.index') || Request::routeIs('activation.workhours.index') || Request::routeIs('dr.panel.clinics.medical-documents') || Request::routeIs('doctors.clinic.deposit') ? 'is-active' : '' }} d-flex flex-column justify-content-center"
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
            @if ($this->hasPermission('dr-clinic-management'))
              <li
                class="item-li i-user__inforamtion {{ Request::routeIs('dr-clinic-management') ? 'is-active' : '' }}">
                <a href="{{ route('dr-clinic-management') }}">مدیریت مطب</a>
              </li>
            @endif
            @if ($this->hasPermission('dr.panel.clinics.medical-documents'))
              <li
                class="item-li i-user__inforamtion {{ Request::routeIs('dr.panel.clinics.medical-documents') ? 'is-active' : '' }}">
                <a href="{{ route('dr.panel.clinics.medical-documents') }}">مدارک من</a>
              </li>
            @endif
            @if ($this->hasPermission('doctors.clinic.deposit'))
              <li
                class="item-li i-checkout__request {{ Request::routeIs('doctors.clinic.deposit') ? 'is-active' : '' }}">
                <a href="{{ route('doctors.clinic.deposit') }}">بیعانه</a>
              </li>
            @endif
          </ul>
        </li>
      @endif



      @if ($this->hasPermission('profile'))
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
            @if ($this->hasPermission('dr-edit-profile'))
              <li class="item-li i-user__inforamtion {{ Request::routeIs('dr-edit-profile') ? 'is-active' : '' }}">
                <a href="{{ route('dr-edit-profile') }}">ویرایش پروفایل</a>
              </li>
            @endif
            @if ($this->hasPermission('dr-edit-profile-security'))
              <li
                class="item-li i-user__inforamtion {{ Request::routeIs('dr-edit-profile-security') ? 'is-active' : '' }}">
                <a href="{{ route('dr-edit-profile-security') }}">امنیت</a>
              </li>
            @endif
            @if ($this->hasPermission('dr-edit-profile-upgrade'))
              <li
                class="item-li i-user__inforamtion {{ Request::routeIs('dr-edit-profile-upgrade') ? 'is-active' : '' }}">
                <a href="{{ route('dr-edit-profile-upgrade') }}">ارتقا حساب</a>
              </li>
            @endif
            @if ($this->hasPermission('dr-my-performance'))
              <li class="item-li i-user__inforamtion {{ Request::routeIs('dr-my-performance') ? 'is-active' : '' }}">
                <a href="{{ route('dr-my-performance') }}">عملکرد من</a>
              </li>
            @endif
            @if ($this->hasPermission('dr-subuser'))
              <li class="item-li i-user__inforamtion {{ Request::routeIs('dr-subuser') ? 'is-active' : '' }}">
                <a href="{{ route('dr-subuser') }}">کاربران زیرمجموعه</a>
              </li>
            @endif
            @if ($this->hasPermission('my-dr-appointments'))
              <li class="item-li i-user__inforamtion {{ Request::routeIs('my-dr-appointments') ? 'is-active' : '' }}">
                <a href="{{ route('my-dr-appointments') }}">نوبت‌های من</a>
              </li>
            @endif
            @if ($this->hasPermission('dr.panel.doctor-faqs.index'))
              <li
                class="item-li i-user__inforamtion {{ Request::routeIs('dr.panel.doctor-faqs.index') ? 'is-active' : '' }}">
                <a href="{{ route('dr.panel.doctor-faqs.index') }}"> سوالات متداول</a>
              </li>
            @endif
          </ul>
        </li>
      @endif

      @if ($this->hasPermission('statistics'))
        <li class="item-li i-transactions {{ Request::routeIs('dr-my-performance-chart') ? 'is-active' : '' }}">
          <a href="{{ route('dr-my-performance-chart') }}">آمار و نمودار</a>
        </li>
      @endif

      @if ($this->hasPermission('messages'))
        <li class="item-li i-comments {{ Request::routeIs('dr-panel-tickets') ? 'is-active' : '' }}">
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
            @if ($this->hasPermission('dr-panel-tickets'))
              <li class="item-li i-user__inforamtion {{ Request::routeIs('dr-panel-tickets') ? 'is-active' : '' }}">
                <a href="{{ route('dr-panel-tickets') }}">تیکت‌ها</a>
              </li>
            @endif
            @if ($this->hasPermission('#'))
              <li class="item-li i-user__inforamtion">
                <a href="#">صفحه گفتگو</a>
              </li>
            @endif
          </ul>
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

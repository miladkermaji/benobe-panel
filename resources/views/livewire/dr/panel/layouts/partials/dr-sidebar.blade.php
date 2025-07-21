<div>
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

        @if ($this->hasPermission('my-prescriptions'))
          <li class="item-li i-banners {{ Request::routeIs('dr.panel.my-prescriptions') ? 'is-active' : '' }}">
            <a href="#" class="d-flex justify-content-between w-100 align-items-center">
              <div class="d-flex align-items-center">
                <span class="fw-bold">نسخه های من</span>
                <span class="badge bg-danger text-white ms-2" style="font-size: 10px; padding: 2px 6px;">جدید</span>
              </div>
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
              <li class="item-li i-courses {{ Request::routeIs('dr.panel.my-prescriptions') ? 'is-active' : '' }}">
                <a href="{{ route('dr.panel.my-prescriptions') }}">مدیریت نسخه ها</a>
              </li>
            </ul>
          </li>
        @endif

        @if ($this->hasPermission('consult'))
          <li
            class="item-li i-moshavere {{ Request::routeIs('dr-moshavere_setting') || Request::routeIs('dr-moshavere_waiting') || Request::routeIs('consult-term.index') || Request::routeIs('dr-mySpecialDays-counseling') ? 'is-active' : '' }}">
            <a href="#" class="d-flex justify-content-between w-100 align-items-center">
              <div class="d-flex align-items-center">
                <span class="fw-bold">مشاوره</span>
                <span class="badge bg-danger text-white ms-2" style="font-size: 10px; padding: 2px 6px;">به زودی</span>
              </div>
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
                <li class="item-li i-courses {{ Request::routeIs('dr-moshavere_setting') ? 'is-active' : '' }}"
                  style="opacity: 0.5; pointer-events: none;">
                  <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed;">برنامه‌ریزی مشاوره</a>
                </li>
              @endif
              @if ($this->hasPermission('dr-moshavere_waiting'))
                <li
                  class="item-li i-user__inforamtion {{ Request::routeIs('dr-moshavere_waiting') ? 'is-active' : '' }}"
                  style="opacity: 0.5; pointer-events: none;">
                  <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed;">گزارش مشاوره</a>
                </li>
              @endif
              @if ($this->hasPermission('dr-mySpecialDays-counseling'))
                <li
                  class="item-li i-user__inforamtion {{ Request::routeIs('dr-mySpecialDays-counseling') ? 'is-active' : '' }}"
                  style="opacity: 0.5; pointer-events: none;">
                  <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed;">روزهای خاص</a>
                </li>
              @endif
              @if ($this->hasPermission('consult-term.index'))
                <li
                  class="item-li i-user__inforamtion {{ Request::routeIs('consult-term.index') ? 'is-active' : '' }}"
                  style="opacity: 0.5; pointer-events: none;">
                  <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed;">قوانین مشاوره</a>
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
            <a href="#" class="d-flex justify-content-between w-100 align-items-center">
              <div class="d-flex align-items-center">
                <span class="fw-bold">
                  نسخه الکترونیک

                </span>
                <span class="badge bg-danger text-white ms-2" style="font-size: 10px; padding: 2px 6px;">به
                  زودی</span>
              </div>
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
                <li class="item-li" style="opacity: 0.5; pointer-events: none;">
                  <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed;">پرونده پزشکی</a>
                </li>
              @endif
              @if ($this->hasPermission('prescription.index'))
                <li class="item-li i-courses {{ Request::routeIs('prescription.index') ? 'is-active' : '' }}"
                  style="opacity: 0.5; pointer-events: none;">
                  <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed;">نسخه‌های ثبت شده</a>
                </li>
              @endif
              @if ($this->hasPermission('providers.index'))
                <li class="item-li i-courses {{ Request::routeIs('providers.index') ? 'is-active' : '' }}"
                  style="opacity: 0.5; pointer-events: none;">
                  <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed;">بیمه‌های من</a>
                </li>
              @endif
              @if ($this->hasPermission('favorite.templates.index'))
                <li class="item-li i-courses {{ Request::routeIs('favorite.templates.index') ? 'is-active' : '' }}"
                  style="opacity: 0.5; pointer-events: none;">
                  <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed;">نسخه پراستفاده</a>
                </li>
              @endif
              @if ($this->hasPermission('templates.favorite.service.index'))
                <li
                  class="item-li i-courses {{ Request::routeIs('templates.favorite.service.index') ? 'is-active' : '' }}"
                  style="opacity: 0.5; pointer-events: none;">
                  <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed;">اقلام پراستفاده</a>
                </li>
              @endif
              @if ($this->hasPermission('patient_records'))
                <li
                  class="item-li i-checkout__request {{ Request::routeIs('dr-patient-records') ? 'is-active' : '' }} d-flex flex-column justify-content-center"
                  id="gozaresh-mali" style="opacity: 0.5; pointer-events: none;">
                  <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed;"
                    class="d-flex align-items-center">
                    پرونده الکترونیک

                  </a>
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
                <li
                  class="item-li i-user__inforamtion {{ Request::routeIs('dr-payment-setting') ? 'is-active' : '' }}">
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
                  class="item-li i-user__inforamtion {{ Request::routeIs('dr-edit-profile-upgrade') ? 'is-active' : '' }}"
                  style="opacity: 0.5; pointer-events: none;">
                  <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed;"
                    class="d-flex align-items-center">
                    ارتقا حساب
                    <span class="badge bg-danger text-white ms-2" style="font-size: 10px; padding: 2px 6px;">به
                      زودی</span>
                  </a>
                </li>
              @endif
              @if ($this->hasPermission('dr-my-performance'))
                <li
                  class="item-li i-user__inforamtion {{ Request::routeIs('dr-my-performance') ? 'is-active' : '' }}">
                  <a href="{{ route('dr-my-performance') }}">عملکرد من</a>
                </li>
              @endif
              @if ($this->hasPermission('dr-subuser'))
                <li class="item-li i-user__inforamtion {{ Request::routeIs('dr-subuser') ? 'is-active' : '' }}">
                  <a href="{{ route('dr-subuser') }}">کاربران زیرمجموعه</a>
                </li>
              @endif
              @if ($this->hasPermission('my-dr-appointments'))
                <li
                  class="item-li i-user__inforamtion {{ Request::routeIs('my-dr-appointments') ? 'is-active' : '' }}">
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

    <!-- Bottom Navigation for Mobile -->
    <style>
      @media (max-width: 768px) {

        .sidebar__nav,
        .sidebar__fixed,
        .bars {
          display: none !important;
        }

        .mobile-bottom-nav {
          display: flex;
          position: fixed;
          bottom: 0;
          left: 0;
          width: 100vw;
          background: #fff;
          box-shadow: 0 -2px 16px rgba(0, 0, 0, 0.08);
          z-index: 15000;
          justify-content: space-around;
          align-items: center;
          padding: 0;
          height: 64px;
          border-top: 1px solid #eee;
          overflow-x: visible !important;
        }

        .mobile-bottom-nav__item {
          position: relative;
          flex: 1 0 60px;
          text-align: center;
          padding: 8px 0 0 0;
          cursor: pointer;
          /* برای موقعیت‌دهی درست dropdown */
          transition: background 0.2s;
          min-width: 60px;
        }

        .mobile-bottom-nav__item svg {
          display: block;
          margin: 0 auto 2px auto;
          width: 24px;
          height: 24px;
          fill: #666;
          transition: fill 0.2s;
        }

        .mobile-bottom-nav__item.active svg,
        .mobile-bottom-nav__item:active svg {
          fill: #007bff;
        }

        .mobile-bottom-nav__label {
          font-size: 11px;
          color: #444;
          font-weight: 500;
        }

        /* تست موقعیت و نمایش dropdown */
        .mobile-bottom-nav__dropdown {
          position: absolute;
          bottom: 64px;
          left: 50%;
          transform: translateX(-50%);
          min-width: 180px;
          background: #fff;
          box-shadow: 0 2px 16px rgba(0, 0, 0, 0.12);
          border-radius: 12px 12px 0 0;
          padding: 8px 0;
          z-index: 20000;
          animation: dropdownIn 0.25s;
        }

        .mobile-bottom-nav__dropdown {
          display: none;
        }

        .mobile-bottom-nav__item.open .mobile-bottom-nav__dropdown {
          display: block !important;
        }

        .mobile-bottom-nav__dropdown a {
          display: block;
          padding: 10px 20px;
          color: #333;
          text-decoration: none;
          font-size: 14px;
          border-bottom: 1px solid #f2f2f2;
          transition: background 0.2s;
        }

        .mobile-bottom-nav__dropdown a:last-child {
          border-bottom: none;
        }

        .mobile-bottom-nav__dropdown a:hover {
          background: #f7f7f7;
          color: #007bff;
        }

        @keyframes dropdownIn {
          from {
            opacity: 0;
            transform: translateX(-50%) translateY(20px);
          }

          to {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
          }
        }
      }

      @media (min-width: 769px) {
        .mobile-bottom-nav {
          display: none !important;
        }
      }
    </style>



    <script>
      (function() {
        let navItems;
        let lastOpen = null;

        function closeAllDropdowns(e) {
          if (!e.target.closest('.mobile-bottom-nav')) {
            navItems.forEach(i => i.classList.remove('open'));
            lastOpen = null;
          }
        }

        function setupMobileNavDropdowns() {
          navItems = document.querySelectorAll('.mobile-bottom-nav__item');
          navItems.forEach(item => {
            item.onclick = function(e) {
              console.log('کلیک روی آیتم منو:', item, 'کلاس open دارد؟', item.classList.contains('open'));
              if (e.target.closest('.mobile-bottom-nav__dropdown')) {
                console.log('کلیک روی dropdown داخلی.');
                return;
              }
              // اگر همین آیتم باز است، فقط ببند
              if (item.classList.contains('open')) {
                item.classList.remove('open');
                lastOpen = null;
                console.log('dropdown بسته شد');
              } else {
                navItems.forEach(i => i.classList.remove('open'));
                item.classList.add('open');
                lastOpen = item;
                console.log('dropdown باز شد برای آیتم:', item);
              }
            };
          });
        }

        document.addEventListener('DOMContentLoaded', function() {
          setupMobileNavDropdowns();
          document.addEventListener('click', closeAllDropdowns);
        });

        window.addEventListener('resize', function() {
          if (window.innerWidth <= 768) {
            setupMobileNavDropdowns();
          }
        });
        // اجرای مجدد setupMobileNavDropdowns بعد از هر آپدیت Livewire
        document.addEventListener('livewire:update', function() {
          setupMobileNavDropdowns();
        });
      })();
    </script>
  </div>
  <div class="mobile-bottom-nav" wire:ignore>
    <!-- داشبورد -->
    <div class="mobile-bottom-nav__item" data-group="dashboard">
      <svg viewBox="0 0 24 24">
        <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z" />
      </svg>
      <div class="mobile-bottom-nav__label">داشبورد</div>
      <div class="mobile-bottom-nav__dropdown">
        <a href="{{ route('dr-panel') }}">داشبورد</a>
      </div>
    </div>
    <!-- نوبت‌ها -->
    <div class="mobile-bottom-nav__item" data-group="appointments">
      <svg viewBox="0 0 24 24">
        <path
          d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zm0-13H5V5h14v1z" />
      </svg>
      <div class="mobile-bottom-nav__label">نوبت‌ها</div>
      <div class="mobile-bottom-nav__dropdown">
        <a href="{{ route('dr-appointments') }}">لیست نوبت‌ها</a>
        <a href="{{ route('dr.panel.doctornotes.index') }}">توضیحات نوبت</a>
        <a href="{{ route('dr-mySpecialDays') }}">روزهای خاص</a>
        <a href="{{ route('dr-scheduleSetting') }}">تنظیمات نوبت</a>
        <a href="{{ route('dr-vacation') }}">تعطیلات</a>
        <a href="{{ route('doctor-blocking-users.index') }}">کاربران مسدود</a>
        <a href="{{ route('dr.panel.my-prescriptions') }}">مدیریت نسخه‌ها</a>
      </div>
    </div>
    <!-- مشاوره -->
    <div class="mobile-bottom-nav__item" data-group="consult">
      <svg viewBox="0 0 24 24">
        <path
          d="M12 12c2.7 0 8 1.34 8 4v2H4v-2c0-2.66 5.3-4 8-4zm0-2c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3z" />
      </svg>
      <div class="mobile-bottom-nav__label">مشاوره</div>
      <div class="mobile-bottom-nav__dropdown">
        <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed; opacity: 0.5;">برنامه‌ریزی مشاوره</a>
        <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed; opacity: 0.5;">گزارش مشاوره</a>
        <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed; opacity: 0.5;">روزهای خاص</a>
        <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed; opacity: 0.5;">قوانین مشاوره</a>
      </div>
    </div>
    <!-- پروفایل -->
    <div class="mobile-bottom-nav__item" data-group="profile">
      <svg viewBox="0 0 24 24">
        <path
          d="M12 12c2.7 0 8 1.34 8 4v2H4v-2c0-2.66 5.3-4 8-4zm0-2c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3z" />
      </svg>
      <div class="mobile-bottom-nav__label">پروفایل</div>
      <div class="mobile-bottom-nav__dropdown">
        <a href="{{ route('dr-edit-profile') }}">ویرایش پروفایل</a>
        <a href="{{ route('dr-edit-profile-security') }}">امنیت</a>
        <a href="{{ route('dr-my-performance') }}">عملکرد من</a>
        <a href="{{ route('dr-subuser') }}">کاربران زیرمجموعه</a>
        <a href="{{ route('my-dr-appointments') }}">نوبت‌های من</a>
        <a href="{{ route('dr.panel.doctor-faqs.index') }}">سوالات متداول</a>
        <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed; opacity: 0.5;">ارتقا حساب</a>
      </div>
    </div>
    <!-- ساعت کاری -->
    <div class="mobile-bottom-nav__item" data-group="workhours">
      <svg viewBox="0 0 24 24">
        <path
          d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8-2V4c0-1.1-.9-2-2-2H6C4.9 2 4 2.9 4 4v2C2.9 6 2 6.9 2 8v10c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm0 12H4V8h16v10z" />
      </svg>
      <div class="mobile-bottom-nav__label">ساعت کاری</div>
      <div class="mobile-bottom-nav__dropdown">
        <a href="{{ route('dr-workhours') }}">ساعت کاری</a>
      </div>
    </div>
    <!-- سایر -->
    <div class="mobile-bottom-nav__item" data-group="other">
      <svg viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10" fill="#eee" />
        <text x="12" y="16" text-anchor="middle" font-size="16" fill="#888">...</text>
      </svg>
      <div class="mobile-bottom-nav__label">سایر</div>
      <div class="mobile-bottom-nav__dropdown">
        <a href="{{ route('dr-my-performance-chart') }}">آمار و نمودار</a>
        <a href="{{ route('dr-panel-tickets') }}">تیکت‌ها</a>
        <a href="{{ route('dr.panel.send-message') }}">ارسال پیام</a>
        <a href="#">صفحه گفتگو</a>
        <a href="{{ route('dr.panel.financial-reports.index') }}">گزارش مالی</a>
        <a href="{{ route('dr-payment-setting') }}">پرداخت</a>
        <a href="{{ route('dr-wallet-charge') }}">شارژ کیف پول</a>
        <a href="{{ route('dr.panel.doctor-services.index') }}">خدمات و بیمه</a>
        <div style="border-top:1px solid #eee; margin:4px 0;"></div>
        <div style="font-size:12px; color:#888; padding:2px 16px 2px 0;">نسخه الکترونیک</div>
        <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed; opacity: 0.5;">پرونده پزشکی</a>
        <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed; opacity: 0.5;">نسخه‌های ثبت شده</a>
        <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed; opacity: 0.5;">بیمه‌های من</a>
        <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed; opacity: 0.5;">نسخه پراستفاده</a>
        <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed; opacity: 0.5;">اقلام پراستفاده</a>
        <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed; opacity: 0.5;">پرونده الکترونیک</a>
        <div style="border-top:1px solid #eee; margin:4px 0;"></div>
        <a href="{{ route('dr-clinic-management') }}">مدیریت مطب</a>
        <a href="{{ route('dr.panel.clinics.medical-documents') }}">مدارک من</a>
        <a href="{{ route('doctors.clinic.deposit') }}">بیعانه</a>
        <a href="{{ route('dr-secretary-management') }}">مدیریت منشی‌ها</a>
        <a href="{{ route('dr-secretary-permissions') }}">دسترسی‌های منشی</a>
      </div>
    </div>
  </div>

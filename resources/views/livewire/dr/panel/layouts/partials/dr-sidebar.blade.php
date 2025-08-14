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
            class="item-li i-appointments {{ Request::routeIs('dr-appointments') || Request::routeIs('dr.panel.doctornotes.index') || Request::routeIs('dr-mySpecialDays') || Request::routeIs('dr-manual_nobat_setting') || Request::routeIs('dr-scheduleSetting') || Request::routeIs('dr-vacation') || Request::routeIs('doctor-blocking-users.index') ? 'is-active' : '' }}">
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
          <li class="item-li i-prescriptions {{ Request::routeIs('dr.panel.my-prescriptions') ? 'is-active' : '' }}">
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
              <li
                class="item-li i-courses {{ Request::routeIs('dr.panel.my-prescriptions.settings') ? 'is-active' : '' }}">
                <a href="{{ route('dr.panel.my-prescriptions.settings') }}">تنظیمات درخواست نسخه</a>
              </li>
            </ul>
          </li>
        @endif
        @if ($this->hasPermission('consult'))
          <li
            class="item-li i-consultation {{ Request::routeIs('dr-moshavere_setting') || Request::routeIs('dr-moshavere_waiting') || Request::routeIs('consult-term.index') || Request::routeIs('dr-mySpecialDays-counseling') ? 'is-active' : '' }}">
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
          <li class="item-li i-insurance {{ Request::routeIs('dr.panel.doctor-services.index') ? 'is-active' : '' }}">
            <a href="{{ route('dr.panel.doctor-services.index') }}">خدمات و بیمه</a>
          </li>
        @endif
        @if ($this->hasPermission('prescription'))
          <li
            class="item-li i-electronic-prescription {{ Request::routeIs('prescription.index') || Request::routeIs('providers.index') || Request::routeIs('favorite.templates.index') || Request::routeIs('templates.favorite.service.index') || Request::routeIs('dr-patient-records') ? 'is-active' : '' }}">
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
            class="item-li i-financial-reports {{ Request::routeIs('dr-wallet') || Request::routeIs('dr-payment-setting') || Request::routeIs('dr.panel.financial-reports.index') || Request::routeIs('dr-wallet-charge') ? 'is-active' : '' }} d-flex flex-column justify-content-center"
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
          <li
            class="item-li i-patient-communication {{ Request::routeIs('dr.panel.send-message') ? 'is-active' : '' }}">
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
            class="item-li i-secretary {{ Request::routeIs('dr-secretary-management') ? 'is-active' : '' }} d-flex flex-column justify-content-center"
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
            class="item-li i-profile {{ Request::routeIs('dr-edit-profile') || Request::routeIs('dr-edit-profile-security') || Request::routeIs('dr-edit-profile-upgrade') || Request::routeIs('dr-my-performance') || Request::routeIs('dr-subuser') || Request::routeIs('my-dr-appointments') ? 'is-active' : '' }} d-flex flex-column justify-content-center"
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
          <li class="item-li i-statistics {{ Request::routeIs('dr-my-performance-chart') ? 'is-active' : '' }}">
            <a href="{{ route('dr-my-performance-chart') }}">آمار و نمودار</a>
          </li>
        @endif
        @if ($this->hasPermission('messages'))
          <li class="item-li i-messages {{ Request::routeIs('dr-panel-tickets') ? 'is-active' : '' }}">
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
              uploadPhoto(this.files[0]);
            }
          });
          photoInput.addEventListener('dblclick', function() {
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

  <!-- Bottom Navigation for Mobile -->
  <style>
    @media (max-width: 425px) {
      .mobile-bottom-nav__label {
        font-size: 9px !important;
      }
    }

    @media (max-width: 768px) {

      .sidebar__nav,
      .sidebar__fixed,
      .bars {
        display: none !important;
      }

      /* New Modern Mobile Navigation */
      .mobile-bottom-nav {
        display: flex;
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100vw;
        background: rgba(255, 255, 255, 0.95);
        box-shadow: 0 -8px 32px rgba(0, 0, 0, 0.12);
        z-index: 25000;
        justify-content: space-around;
        align-items: center;
        padding: 12px 0 8px 0;
        height: 80px;
        border-top: 1px solid rgba(255, 255, 255, 0.2);
      }

      .mobile-bottom-nav__item {
        position: relative;
        flex: 1;
        text-align: center;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        min-width: 0;
        border-radius: 20px;
        margin: 0 4px;
        overflow: visible;
        border: 2px solid transparent;
      }

      .mobile-bottom-nav__item:active {
        transform: scale(0.95);
      }

      .mobile-bottom-nav__item.open {
        border: 2px solid #667eea;
        background: rgba(102, 126, 234, 0.05);
      }

      .mobile-bottom-nav__icon {
        position: relative;
        width: 48px;
        height: 48px;
        margin: 0 auto 6px auto;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
      }

      .mobile-bottom-nav__item.active .mobile-bottom-nav__icon {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        transform: translateY(-8px) scale(1.1);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
      }

      .mobile-bottom-nav__item.open .mobile-bottom-nav__icon {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        transform: translateY(-4px) scale(1.05);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.25);
      }

      .mobile-bottom-nav__item.active .mobile-bottom-nav__icon::before {
        content: '';
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 18px;
        z-index: -1;
        opacity: 0.3;
        filter: blur(8px);
      }

      .mobile-bottom-nav__icon svg {
        width: 24px;
        height: 24px;
        stroke: #6c757d;
        stroke-width: 2;
        fill: none;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      }

      .mobile-bottom-nav__item.active .mobile-bottom-nav__icon svg,
      .mobile-bottom-nav__item.open .mobile-bottom-nav__icon svg {
        stroke: #ffffff;
        transform: scale(1.1);
      }

      .mobile-bottom-nav__label {
        font-size: 11px;
        color: #6c757d;
        font-weight: 600;
        letter-spacing: 0.3px;
        margin-top: 2px;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        opacity: 0.8;
      }

      .mobile-bottom-nav__item.active .mobile-bottom-nav__label,
      .mobile-bottom-nav__item.open .mobile-bottom-nav__label {
        color: #667eea;
        font-weight: 700;
        opacity: 1;
        transform: translateY(-2px);
      }

      /* Improved Dropdown Styling - Full Width */
      .mobile-bottom-nav__dropdown {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(255, 255, 255, 0.98);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        border-radius: 0;
        padding: 0;
        z-index: 20001;
        opacity: 0;
        visibility: hidden;
        transform: translateY(100%);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: none;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        max-height: 100vh;
      }

      .mobile-bottom-nav__item.open .mobile-bottom-nav__dropdown {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
      }

      /* Close Button Styling */
      .mobile-dropdown-close {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 10;
        box-shadow: 0 3px 12px rgba(102, 126, 234, 0.3);
        transition: all 0.3s ease;
      }

      .mobile-dropdown-close:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
      }

      .mobile-dropdown-close svg {
        width: 18px;
        height: 18px;
        stroke: white;
        stroke-width: 2;
      }

      /* Header for full height dropdown */
      .mobile-dropdown-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 16px;
        text-align: center;
        font-size: 16px;
        font-weight: 700;
        position: relative;
        margin-bottom: 0;
        flex-shrink: 0;
      }

      /* Content container for full height with proper scrolling */
      .mobile-dropdown-content {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-height: 0;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: rgba(102, 126, 234, 0.4) rgba(0, 0, 0, 0.05);
      }

      /* Enhanced scrollbar styling for better visibility */
      .mobile-dropdown-content::-webkit-scrollbar {
        width: 6px;
      }

      .mobile-dropdown-content::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.05);
        border-radius: 3px;
        margin: 4px 0;
      }

      .mobile-dropdown-content::-webkit-scrollbar-thumb {
        background: rgba(102, 126, 234, 0.4);
        border-radius: 3px;
        border: 1px solid rgba(255, 255, 255, 0.2);
      }

      .mobile-dropdown-content::-webkit-scrollbar-thumb:hover {
        background: rgba(102, 126, 234, 0.6);
      }

      /* Ensure all content is visible */
      .mobile-dropdown-content>* {
        min-width: 0;
        flex-shrink: 0;
      }

      /* Fix for very long content */
      .mobile-dropdown-content {
        padding-bottom: 20px;
      }

      /* Ensure proper touch scrolling on mobile */
      .mobile-dropdown-content {
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
        scroll-behavior: smooth;
      }

      .mobile-bottom-nav__dropdown a {
        display: block;
        padding: 10px 14px;
        color: #495057;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        border: 1px solid rgba(102, 126, 234, 0.1);
        border-radius: 8px;
        transition: all 0.3s ease;
        margin: 0;
        position: relative;
        white-space: nowrap;
        text-align: center;
        background: rgba(255, 255, 255, 0.9);
      }

      .mobile-bottom-nav__dropdown a:last-child {
        border-bottom: 1px solid rgba(102, 126, 234, 0.1);
        margin-bottom: 0;
      }

      .mobile-bottom-nav__dropdown a::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
        transition: left 0.5s ease;
      }

      .mobile-bottom-nav__dropdown a:hover::before {
        left: 100%;
      }

      .mobile-bottom-nav__dropdown a:hover {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.08) 0%, rgba(118, 75, 162, 0.08) 100%);
        color: #667eea;
        transform: translateX(3px);
        box-shadow: 0 3px 10px rgba(102, 126, 234, 0.15);
        border-color: rgba(102, 126, 234, 0.25);
      }

      /* Enhanced Backdrop with Blur */
      .mobile-bottom-nav__item:not(.mobile-bottom-nav__item--other).open::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.4);
        backdrop-filter: blur(8px);
        z-index: 20000;
        animation: backdropIn 0.3s ease-out;
      }

      /* Special styling for "other" menu - full height version */
      .mobile-bottom-nav__item--other .mobile-bottom-nav__dropdown {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(255, 255, 255, 0.98);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        border-radius: 0;
        padding: 0;
        z-index: 20001;
        opacity: 0;
        visibility: hidden;
        transform: translateY(100%);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: none;
        overflow: hidden;
        display: flex;
        flex-direction: column;
      }

      .mobile-bottom-nav__item--other.open .mobile-bottom-nav__dropdown {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
      }

      /* Mobile Menu Sections Styling - Two Column Layout */
      .mobile-menu-section {
        margin-bottom: 12px;
        border-radius: 10px;
        overflow: visible;
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(102, 126, 234, 0.15);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        flex-shrink: 0;
        min-width: 0;
      }

      .mobile-menu-section:last-child {
        margin-bottom: 0;
      }

      .mobile-menu-section__header {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        border-bottom: 1px solid rgba(102, 126, 234, 0.15);
        font-weight: 600;
        font-size: 13px;
        color: #495057;
        position: relative;
        flex-shrink: 0;
      }

      .mobile-menu-section__header svg {
        width: 16px;
        height: 16px;
        stroke: #667eea;
        stroke-width: 2;
        flex-shrink: 0;
      }

      .mobile-menu-section__header .badge {
        margin-right: auto;
        font-size: 8px;
        padding: 2px 5px;
        border-radius: 8px;
        font-weight: 600;
        flex-shrink: 0;
      }

      /* Two Column Layout for Menu Items */
      .mobile-menu-section__items {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px;
        padding: 10px;
        overflow: visible;
        min-width: 0;
      }

      .mobile-menu-section a {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 6px 10px;
        color: #495057;
        text-decoration: none;
        font-size: 10px;
        font-weight: 500;
        border: 1px solid rgba(102, 126, 234, 0.1);
        border-radius: 6px;
        transition: all 0.3s ease;
        position: relative;
        background: rgba(255, 255, 255, 0.9);
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        min-height: 28px;
        line-height: 1.2;
        min-width: 0;
        flex-shrink: 0;
      }

      .mobile-menu-section a:hover {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.08) 0%, rgba(118, 75, 162, 0.08) 100%);
        color: #667eea;
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(102, 126, 234, 0.15);
        border-color: rgba(102, 126, 234, 0.25);
      }

      .mobile-menu-section a.disabled-link {
        color: #6c757d;
        cursor: not-allowed;
        opacity: 0.5;
        background: rgba(108, 117, 125, 0.05);
        border-color: rgba(108, 117, 125, 0.1);
      }

      .mobile-menu-section a.disabled-link:hover {
        transform: none;
        box-shadow: none;
        background: rgba(108, 117, 125, 0.05);
        color: #6c757d;
      }

      .mobile-menu-section a::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
        transition: left 0.5s ease;
      }

      .mobile-menu-section a:hover::before {
        left: 100%;
      }

      /* Responsive adjustments for two column layout */
      @media (max-width: 400px) {
        .mobile-menu-section {
          margin-bottom: 10px;
        }

        .mobile-menu-section__header {
          padding: 8px 12px;
          font-size: 12px;
        }

        .mobile-menu-section__header svg {
          width: 14px;
          height: 14px;
        }

        .mobile-menu-section__items {
          padding: 8px;
          gap: 5px;
        }

        .mobile-menu-section a {
          padding: 5px 8px;
          font-size: 9px;
          min-height: 26px;
        }

        .mobile-dropdown-header {
          padding: 14px;
          font-size: 15px;
        }

        .mobile-dropdown-content {
          padding: 14px;
        }
      }

      /* For very small screens, switch to single column */
      @media (max-width: 320px) {
        .mobile-menu-section__items {
          grid-template-columns: 1fr;
          gap: 5px;
        }
      }

      /* Animations */
      @keyframes backdropIn {
        from {
          opacity: 0;
          backdrop-filter: blur(0px);
        }

        to {
          opacity: 1;
          backdrop-filter: blur(8px);
        }
      }

      @keyframes dropdownSlideIn {
        0% {
          opacity: 0;
          transform: translateY(100%);
        }

        100% {
          opacity: 1;
          transform: translateY(0);
        }
      }

      /* Active state indicator */
      .mobile-bottom-nav__item.active::after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 50%;
        transform: translateX(-50%);
        width: 6px;
        height: 6px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.4);
      }

      /* Scrollbar styling for dropdown */
      .mobile-bottom-nav__dropdown::-webkit-scrollbar {
        width: 4px;
      }

      .mobile-bottom-nav__dropdown::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.05);
        border-radius: 2px;
      }

      .mobile-bottom-nav__dropdown::-webkit-scrollbar-thumb {
        background: rgba(102, 126, 234, 0.3);
        border-radius: 2px;
      }

      .mobile-bottom-nav__dropdown::-webkit-scrollbar-thumb:hover {
        background: rgba(102, 126, 234, 0.5);
      }

      /* Responsive adjustments */
      @media (max-width: 400px) {
        .mobile-bottom-nav__label {
          font-size: 9px;
        }

        .mobile-bottom-nav__icon {
          width: 42px;
          height: 42px;
        }

        .mobile-bottom-nav__icon svg {
          width: 20px;
          height: 20px;
        }

        .mobile-menu-section {
          margin-bottom: 8px;
        }

        .mobile-menu-section__header {
          padding: 7px 10px;
          font-size: 11px;
        }

        .mobile-menu-section__header svg {
          width: 13px;
          height: 13px;
        }

        .mobile-menu-section__items {
          padding: 7px;
          gap: 4px;
        }

        .mobile-menu-section a {
          padding: 4px 6px;
          font-size: 8px;
          min-height: 24px;
        }

        .mobile-dropdown-header {
          padding: 12px;
          font-size: 14px;
        }

        .mobile-dropdown-content {
          padding: 12px;
          gap: 4px;
        }

        .mobile-dropdown-close {
          top: 12px;
          right: 12px;
          width: 32px;
          height: 32px;
        }

        .mobile-dropdown-close svg {
          width: 16px;
          height: 16px;
        }
      }

      /* Responsive adjustments for sections */
      @media (max-width: 400px) {
        .mobile-menu-section__header {
          padding: 7px 10px;
          font-size: 11px;
        }

        .mobile-menu-section__header svg {
          width: 13px;
          height: 13px;
        }

        .mobile-menu-section a {
          padding: 4px 6px;
          font-size: 8px;
        }

        .mobile-dropdown-header {
          padding: 12px;
          font-size: 14px;
        }

        .mobile-dropdown-content {
          padding: 12px;
        }
      }

      /* Enhanced spacing for mobile menu */
      .mobile-bottom-nav__dropdown {
        padding: 0;
        max-height: none;
      }

      /* Responsive adjustments for two column layout */
    }

    @media (min-width: 769px) {
      .mobile-bottom-nav {
        display: none !important;
      }
    }

    .soon-label {
      font-size: 10px;
      color: #dc3545;
      margin-right: 4px;
      vertical-align: middle;
      background: rgba(220, 53, 69, 0.1);
      padding: 2px 6px;
      border-radius: 8px;
    }
  </style>

  <script>
    (function() {
      let navItems;
      let lastOpen = null;

      function closeAllDropdowns(e) {
        if (!e.target.closest('.mobile-bottom-nav')) {
          // بستن همه منوها وقتی روی بیرون کلیک می‌شه
          navItems.forEach(i => i.classList.remove('open'));
          lastOpen = null;
          document.body.style.overflow = '';
        }
      }

      function setupMobileNavDropdowns() {
        navItems = document.querySelectorAll('.mobile-bottom-nav__item');

        navItems.forEach(item => {
          item.onclick = function(e) {
            if (e.target.closest('.mobile-bottom-nav__dropdown')) {
              return;
            }

            if (item.classList.contains('open')) {
              // اگر همین آیتم باز است، فقط ببند
              item.classList.remove('open');
              lastOpen = null;
              document.body.style.overflow = '';
            } else {
              // بستن همه منوهای باز
              navItems.forEach(i => {
                i.classList.remove('open');
              });

              // باز کردن منوی جدید
              item.classList.add('open');
              lastOpen = item;

              // تنظیم overflow برای backdrop
              if (item.querySelector('.mobile-bottom-nav__dropdown')) {
                document.body.style.overflow = 'hidden';
              }
            }
          };
        });
      }

      // Function to close the "سایر" menu
      function closeOtherMenu() {
        const otherItem = document.querySelector('.mobile-bottom-nav__item--other');
        if (otherItem) {
          otherItem.classList.remove('open');
          lastOpen = null;
          document.body.style.overflow = '';
        }
      }

      function setActiveItem() {
        const currentPath = window.location.pathname;
        const currentUrl = window.location.href;

        navItems.forEach(item => {
          // حذف کلاس active از همه آیتم‌ها
          item.classList.remove('active');

          // بررسی لینک‌های مستقیم (بدون زیرمنو)
          const directLink = item.querySelector('a[href]');
          if (directLink && directLink.href && !item.hasAttribute('data-has-submenu')) {
            if (directLink.href === currentUrl || directLink.href.endsWith(currentPath)) {
              item.classList.add('active');
              return;
            }
          }

          // بررسی لینک‌های زیرمنو
          const submenuLinks = item.querySelectorAll('.mobile-bottom-nav__dropdown a[href]');
          let isActive = false;

          submenuLinks.forEach(link => {
            if (link.href && link.href !== 'javascript:void(0)') {
              if (link.href === currentUrl || link.href.endsWith(currentPath)) {
                isActive = true;
              }
            }
          });

          if (isActive) {
            item.classList.add('active');
          }
        });
      }

      // Function to ensure proper scrolling behavior
      function setupScrolling() {
        const dropdownContent = document.querySelector('.mobile-dropdown-content');
        if (dropdownContent) {
          // Reset scroll position when opening
          dropdownContent.scrollTop = 0;

          // Ensure smooth scrolling
          dropdownContent.style.scrollBehavior = 'smooth';

          // Add touch scrolling support
          let isScrolling = false;
          let startY = 0;
          let startScrollTop = 0;

          dropdownContent.addEventListener('touchstart', function(e) {
            startY = e.touches[0].clientY;
            startScrollTop = this.scrollTop;
            isScrolling = false;
          });

          dropdownContent.addEventListener('touchmove', function(e) {
            if (!isScrolling) {
              const deltaY = startY - e.touches[0].clientY;
              if (Math.abs(deltaY) > 10) {
                isScrolling = true;
              }
            }
          });
        }
      }

      document.addEventListener('DOMContentLoaded', function() {
        setupMobileNavDropdowns();
        setActiveItem();
        setupScrolling();
        document.addEventListener('click', closeAllDropdowns);
      });

      window.addEventListener('resize', function() {
        if (window.innerWidth <= 768) {
          setupMobileNavDropdowns();
          setupScrolling();
        }
      });

      document.addEventListener('livewire:update', function() {
        setupMobileNavDropdowns();
        setActiveItem();
        setupScrolling();
      });
    })();
  </script>

  <div class="mobile-bottom-nav" wire:ignore>
    <!-- داشبورد -->
    @if ($this->hasPermission('dashboard'))
      <div class="mobile-bottom-nav__item" data-group="dashboard">
        <a href="{{ route('dr-panel') }}"
          style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: inherit;">
          <div class="mobile-bottom-nav__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round">
              <rect x="3" y="3" width="7" height="7" />
              <rect x="14" y="3" width="7" height="7" />
              <rect x="14" y="14" width="7" height="7" />
              <rect x="3" y="14" width="7" height="7" />
            </svg>
          </div>
          <div class="mobile-bottom-nav__label">داشبورد</div>
        </a>
      </div>
    @endif

    <!-- نوبت‌ها -->
    @if ($this->hasPermission('appointments'))
      <div class="mobile-bottom-nav__item" data-group="appointments">
        <a href="{{ route('dr-appointments') }}"
          style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: inherit;">
          <div class="mobile-bottom-nav__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
              <line x1="16" y1="2" x2="16" y2="6" />
              <line x1="8" y1="2" x2="8" y2="6" />
              <line x1="3" y1="10" x2="21" y2="10" />
              <path d="M8 14h.01" />
              <path d="M12 14h.01" />
              <path d="M16 14h.01" />
              <path d="M8 18h.01" />
              <path d="M12 18h.01" />
              <path d="M16 18h.01" />
            </svg>
          </div>
          <div class="mobile-bottom-nav__label">نوبت‌ها</div>
        </a>
      </div>
    @endif

    <!-- ساعت کاری -->
    @if ($this->hasPermission('dr-workhours'))
      <div class="mobile-bottom-nav__item" data-group="workhours">
        <a href="{{ route('dr-workhours') }}"
          style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: inherit;">
          <div class="mobile-bottom-nav__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round">
              <circle cx="12" cy="12" r="10" />
              <polyline points="12,6 12,12 16,14" />
            </svg>
          </div>
          <div class="mobile-bottom-nav__label">ساعت کاری</div>
        </a>
      </div>
    @endif

    <!-- نسخه‌ها -->
    @if ($this->hasPermission('my-prescriptions'))
      <div class="mobile-bottom-nav__item" data-group="prescriptions">
        <a href="{{ route('dr.panel.my-prescriptions') }}"
          style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: inherit;">
          <div class="mobile-bottom-nav__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
              <polyline points="14,2 14,8 20,8"></polyline>
              <line x1="16" y1="13" x2="8" y2="13"></line>
              <line x1="16" y1="17" x2="8" y2="17"></line>
              <polyline points="10,9 9,9 8,9"></polyline>
            </svg>
          </div>
          <div class="mobile-bottom-nav__label">نسخه‌ها</div>
        </a>
      </div>
    @endif

    <!-- گزارش مالی -->
    @if ($this->hasPermission('financial_reports'))
      <div class="mobile-bottom-nav__item" data-group="financial">
        <a href="{{ route('dr.panel.financial-reports.index') }}"
          style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: inherit;">
          <div class="mobile-bottom-nav__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round">
              <line x1="12" y1="1" x2="12" y2="23"></line>
              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
          </div>
          <div class="mobile-bottom-nav__label">گزارش مالی</div>
        </a>
      </div>
    @endif

    <!-- سایر -->
    <div class="mobile-bottom-nav__item mobile-bottom-nav__item--other" data-group="other" data-has-submenu="true">
      <div class="mobile-bottom-nav__icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
          stroke-linejoin="round">
          <circle cx="12" cy="12" r="1" />
          <circle cx="19" cy="12" r="1" />
          <circle cx="5" cy="12" r="1" />
        </svg>
      </div>
      <div class="mobile-bottom-nav__label">سایر</div>
      <div class="mobile-bottom-nav__dropdown">
        <!-- Header with close button -->
        <div class="mobile-dropdown-header">
          <button class="mobile-dropdown-close" onclick="closeOtherMenu()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
          <span>منوی کامل</span>
        </div>

        <!-- Content container -->
        <div class="mobile-dropdown-content">
          <!-- مشاوره -->
          @if ($this->hasPermission('consult'))
            <div class="mobile-menu-section">
              <div class="mobile-menu-section__header">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <span>مشاوره</span>
                <span class="badge bg-warning text-dark">به زودی</span>
              </div>
              <div class="mobile-menu-section__items">
                @if ($this->hasPermission('dr-moshavere_setting'))
                  <a href="javascript:void(0)" class="disabled-link">برنامه‌ریزی مشاوره</a>
                @endif
                @if ($this->hasPermission('dr-moshavere_waiting'))
                  <a href="javascript:void(0)" class="disabled-link">گزارش مشاوره</a>
                @endif
                @if ($this->hasPermission('dr-mySpecialDays-counseling'))
                  <a href="javascript:void(0)" class="disabled-link">روزهای خاص</a>
                @endif
                @if ($this->hasPermission('consult-term.index'))
                  <a href="javascript:void(0)" class="disabled-link">قوانین مشاوره</a>
                @endif
              </div>
            </div>
          @endif

          <!-- نسخه الکترونیک -->
          @if ($this->hasPermission('prescription'))
            <div class="mobile-menu-section">
              <div class="mobile-menu-section__header">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round">
                  <path d="M9 12l2 2 4-4"></path>
                  <path d="M21 12c-1 0-2-1-2-2s1-2 2-2 2 1 2 2-1 2-2 2z"></path>
                  <path d="M3 12c1 0 2-1 2-2s-1-2-2-2-2 1-2 2 1 2 2 2z"></path>
                  <path d="M12 3c0 1-1 2-2 2s-2 1-2 2 1 2 2 2 2 1 2 2 1-2 2-2 2-1 2-2-1-2-2-2-2-1-2-2z"></path>
                </svg>
                <span>نسخه الکترونیک</span>
                <span class="badge bg-warning text-dark">به زودی</span>
              </div>
              <div class="mobile-menu-section__items">
                @if ($this->hasPermission('dr-patient-records'))
                  <a href="javascript:void(0)" class="disabled-link">پرونده پزشکی</a>
                @endif
                @if ($this->hasPermission('prescription.index'))
                  <a href="javascript:void(0)" class="disabled-link">نسخه‌های ثبت شده</a>
                @endif
                @if ($this->hasPermission('providers.index'))
                  <a href="javascript:void(0)" class="disabled-link">بیمه‌های من</a>
                @endif
                @if ($this->hasPermission('favorite.templates.index'))
                  <a href="javascript:void(0)" class="disabled-link">نسخه پراستفاده</a>
                @endif
                @if ($this->hasPermission('templates.favorite.service.index'))
                  <a href="javascript:void(0)" class="disabled-link">اقلام پراستفاده</a>
                @endif
                @if ($this->hasPermission('patient_records'))
                  <a href="javascript:void(0)" class="disabled-link">پرونده الکترونیک</a>
                @endif
              </div>
            </div>
          @endif

          <!-- ارتباط با بیماران -->
          @if ($this->hasPermission('patient_communication'))
            <div class="mobile-menu-section">
              <div class="mobile-menu-section__header">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round">
                  <path
                    d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z">
                  </path>
                </svg>
                <span>ارتباط با بیماران</span>
              </div>
              <div class="mobile-menu-section__items">
                @if ($this->hasPermission('dr.panel.send-message'))
                  <a href="{{ route('dr.panel.send-message') }}">ارسال پیام</a>
                @endif
              </div>
            </div>
          @endif

          <!-- منشی -->
          @if ($this->hasPermission('secretary_management'))
            <div class="mobile-menu-section">
              <div class="mobile-menu-section__header">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                  <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span>منشی</span>
              </div>
              <div class="mobile-menu-section__items">
                @if ($this->hasPermission('secretary_management'))
                  <a href="{{ route('dr-secretary-management') }}">مدیریت منشی‌ها</a>
                @endif
                @if ($this->hasPermission('permissions'))
                  <a href="{{ route('dr-secretary-permissions') }}">دسترسی‌ها</a>
                @endif
              </div>
            </div>
          @endif

          <!-- مطب -->
          @if ($this->hasPermission('clinic_management'))
            <div class="mobile-menu-section">
              <div class="mobile-menu-section__header">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round">
                  <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                  <polyline points="9,22 9,12 15,12 15,22"></polyline>
                </svg>
                <span>مطب</span>
              </div>
              <div class="mobile-menu-section__items">
                @if ($this->hasPermission('dr-clinic-management'))
                  <a href="{{ route('dr-clinic-management') }}">مدیریت مطب</a>
                @endif
                @if ($this->hasPermission('dr.panel.clinics.medical-documents'))
                  <a href="{{ route('dr.panel.clinics.medical-documents') }}">مدارک من</a>
                @endif
                @if ($this->hasPermission('doctors.clinic.deposit'))
                  <a href="{{ route('doctors.clinic.deposit') }}">بیعانه</a>
                @endif
              </div>
            </div>
          @endif

          <!-- حساب کاربری -->
          @if ($this->hasPermission('profile'))
            <div class="mobile-menu-section">
              <div class="mobile-menu-section__header">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                  <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span>حساب کاربری</span>
              </div>
              <div class="mobile-menu-section__items">
                @if ($this->hasPermission('dr-edit-profile'))
                  <a href="{{ route('dr-edit-profile') }}">ویرایش پروفایل</a>
                @endif
                @if ($this->hasPermission('dr-edit-profile-security'))
                  <a href="{{ route('dr-edit-profile-security') }}">امنیت</a>
                @endif
                @if ($this->hasPermission('dr-my-performance'))
                  <a href="{{ route('dr-my-performance') }}">عملکرد من</a>
                @endif
                @if ($this->hasPermission('dr-subuser'))
                  <a href="{{ route('dr-subuser') }}">کاربران زیرمجموعه</a>
                @endif
                @if ($this->hasPermission('my-dr-appointments'))
                  <a href="{{ route('my-dr-appointments') }}">نوبت‌های من</a>
                @endif
                @if ($this->hasPermission('dr.panel.doctor-faqs.index'))
                  <a href="{{ route('dr.panel.doctor-faqs.index') }}">سوالات متداول</a>
                @endif
                @if ($this->hasPermission('dr-edit-profile-upgrade'))
                  <a href="javascript:void(0)" class="disabled-link">
                    ارتقا حساب <span class="badge bg-warning text-dark">به زودی</span>
                  </a>
                @endif
              </div>
            </div>
          @endif

          <!-- آمار و نمودار -->
          @if ($this->hasPermission('statistics'))
            <div class="mobile-menu-section">
              <div class="mobile-menu-section__header">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round">
                  <line x1="18" y1="20" x2="18" y2="10"></line>
                  <line x1="12" y1="20" x2="12" y2="4"></line>
                  <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
                <span>آمار و نمودار</span>
              </div>
              <div class="mobile-menu-section__items">
                <a href="{{ route('dr-my-performance-chart') }}">آمار و نمودار</a>
              </div>
            </div>
          @endif

          <!-- پیام -->
          @if ($this->hasPermission('messages'))
            <div class="mobile-menu-section">
              <div class="mobile-menu-section__header">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <span>پیام</span>
              </div>
              <div class="mobile-menu-section__items">
                @if ($this->hasPermission('dr-panel-tickets'))
                  <a href="{{ route('dr-panel-tickets') }}">تیکت‌ها</a>
                @endif
              </div>
            </div>
          @endif

          <!-- خدمات و بیمه -->
          @if ($this->hasPermission('insurance'))
            <div class="mobile-menu-section">
              <div class="mobile-menu-section__header">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round">
                  <path d="M9 12l2 2 4-4"></path>
                  <path d="M21 12c-1 0-2-1-2-2s1-2 2-2 2 1 2 2-1 2-2 2z"></path>
                  <path d="M3 12c1 0 2-1 2-2s-1-2-2-2-2 1-2 2 1 2 2 2z"></path>
                  <path d="M12 3c0 1-1 2-2 2s-2 1-2 2 1 2 2 2 2 1 2 2 1-2 2-2 2-1 2-2-1-2-2-2-2-1-2-2z"></path>
                </svg>
                <span>خدمات و بیمه</span>
              </div>
              <div class="mobile-menu-section__items">
                <a href="{{ route('dr.panel.doctor-services.index') }}">خدمات و بیمه</a>
              </div>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

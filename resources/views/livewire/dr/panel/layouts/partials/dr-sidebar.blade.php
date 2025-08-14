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
        backdrop-filter: blur(20px);
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
      }

      .mobile-bottom-nav__item:active {
        transform: scale(0.95);
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

      .mobile-bottom-nav__item.active .mobile-bottom-nav__icon svg {
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

      .mobile-bottom-nav__item.active .mobile-bottom-nav__label {
        color: #667eea;
        font-weight: 700;
        opacity: 1;
        transform: translateY(-2px);
      }

      /* Improved Dropdown Styling */
      .mobile-bottom-nav__dropdown {
        position: absolute;
        bottom: 90px;
        left: 50%;
        transform: translateX(-50%);
        min-width: 180px;
        width: fit-content;
        max-width: 85vw;
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        border-radius: 20px;
        padding: 12px 0;
        z-index: 20001;
        opacity: 0;
        visibility: hidden;
        transform: translateX(-50%) translateY(20px) scale(0.8);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: 1px solid rgba(255, 255, 255, 0.2);
        overflow: hidden;
      }

      .mobile-bottom-nav__item.open .mobile-bottom-nav__dropdown {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(0) scale(1);
      }

      .mobile-bottom-nav__dropdown a {
        display: block;
        padding: 12px 20px;
        color: #495057;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        margin: 0 6px;
        border-radius: 12px;
        position: relative;
        white-space: nowrap;
      }

      .mobile-bottom-nav__dropdown a:last-child {
        border-bottom: none;
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
        transform: translateX(4px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
      }

      /* Backdrop - positioned above body but below dropdown */
      .mobile-bottom-nav__item:not(.mobile-bottom-nav__item--other).open::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: calc(100vh - 80px);
        z-index: 20000;
        animation: backdropIn 0.3s ease-out;
      }

      /* Special styling for "other" menu - compact version */
      .mobile-bottom-nav__item--other .mobile-bottom-nav__dropdown {
        position: absolute;
        bottom: 90px;
        left: 50%;
        transform: translateX(-50%);
        min-width: 200px;
        width: fit-content;
        max-width: 85vw;
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        border-radius: 20px;
        padding: 12px 0;
        z-index: 20001;
        opacity: 0;
        visibility: hidden;
        transform: translateX(-50%) translateY(20px) scale(0.8);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: 1px solid rgba(255, 255, 255, 0.2);
        overflow: hidden;
      }

      .mobile-bottom-nav__item--other.open .mobile-bottom-nav__dropdown {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(0) scale(1);
      }

      /* Fix for edge positioning - prevent dropdowns from going off-screen */
      .mobile-bottom-nav__item:first-child .mobile-bottom-nav__dropdown {
        left: 20px;
        transform: translateX(0) translateY(20px) scale(0.8);
      }

      .mobile-bottom-nav__item:first-child.open .mobile-bottom-nav__dropdown {
        transform: translateX(0) translateY(0) scale(1);
      }

      .mobile-bottom-nav__item:last-child .mobile-bottom-nav__dropdown,
      .mobile-bottom-nav__item--other .mobile-bottom-nav__dropdown {
        left: 20px !important;
        transform: translateX(0) translateY(20px) scale(0.8) !important;
      }

      .mobile-bottom-nav__item:last-child.open .mobile-bottom-nav__dropdown,
      .mobile-bottom-nav__item--other.open .mobile-bottom-nav__dropdown {
        transform: translateX(0) translateY(0) scale(1) !important;
      }

      /* Animations */
      @keyframes backdropIn {
        from {
          opacity: 0;
        }

        to {
          opacity: 1;
        }
      }

      @keyframes dropdownSlideIn {
        0% {
          opacity: 0;
          transform: translateX(-50%) translateY(30px) scale(0.8);
        }

        100% {
          opacity: 1;
          transform: translateX(-50%) translateY(0) scale(1);
        }
      }

      /* Responsive adjustments */
      @media (max-width: 400px) {
        .mobile-bottom-nav__label {
          font-size: 10px;
        }

        .mobile-bottom-nav__icon {
          width: 44px;
          height: 44px;
        }

        .mobile-bottom-nav__icon svg {
          width: 22px;
          height: 22px;
        }

        .mobile-bottom-nav__dropdown {
          min-width: 160px;
          max-width: 90vw;
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

      function setActiveItem() {
        const currentPath = window.location.pathname;
        navItems.forEach(item => {
          const links = item.querySelectorAll('a[href]');
          let isActive = false;

          links.forEach(link => {
            if (link.href.includes(currentPath)) {
              isActive = true;
            }
          });

          if (isActive) {
            item.classList.add('active');
          } else {
            item.classList.remove('active');
          }
        });
      }

      document.addEventListener('DOMContentLoaded', function() {
        setupMobileNavDropdowns();
        setActiveItem();
        document.addEventListener('click', closeAllDropdowns);
      });

      window.addEventListener('resize', function() {
        if (window.innerWidth <= 768) {
          setupMobileNavDropdowns();
        }
      });

      document.addEventListener('livewire:update', function() {
        setupMobileNavDropdowns();
        setActiveItem();
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
      <div class="mobile-bottom-nav__item" data-group="appointments" data-has-submenu="true">
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
        <div class="mobile-bottom-nav__dropdown">
          @if ($this->hasPermission('dr-appointments'))
            <a href="{{ route('dr-appointments') }}">لیست نوبت‌ها</a>
          @endif
          @if ($this->hasPermission('dr.panel.doctornotes.index'))
            <a href="{{ route('dr.panel.doctornotes.index') }}">توضیحات نوبت</a>
          @endif
          @if ($this->hasPermission('dr-mySpecialDays'))
            <a href="{{ route('dr-mySpecialDays') }}">روزهای خاص</a>
          @endif
          @if ($this->hasPermission('dr-scheduleSetting'))
            <a href="{{ route('dr-scheduleSetting') }}">تنظیمات نوبت</a>
          @endif
          @if ($this->hasPermission('dr-vacation'))
            <a href="{{ route('dr-vacation') }}">تعطیلات</a>
          @endif
          @if ($this->hasPermission('doctor-blocking-users.index'))
            <a href="{{ route('doctor-blocking-users.index') }}">کاربران مسدود</a>
          @endif
        </div>
      </div>
    @endif

    <!-- نسخه‌ها -->
    @if ($this->hasPermission('my-prescriptions'))
      <div class="mobile-bottom-nav__item" data-group="prescriptions" data-has-submenu="true">
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
        <div class="mobile-bottom-nav__dropdown">
          <a href="{{ route('dr.panel.my-prescriptions') }}">مدیریت نسخه ها</a>
          <a href="{{ route('dr.panel.my-prescriptions.settings') }}">تنظیمات درخواست نسخه</a>
        </div>
      </div>
    @endif

    <!-- پروفایل -->
    @if ($this->hasPermission('profile'))
      <div class="mobile-bottom-nav__item" data-group="profile" data-has-submenu="true">
        <div class="mobile-bottom-nav__icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
            <circle cx="12" cy="7" r="4" />
          </svg>
        </div>
        <div class="mobile-bottom-nav__label">پروفایل</div>
        <div class="mobile-bottom-nav__dropdown">
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
            <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed; opacity: 0.5;">
              ارتقا حساب <span class="soon-label">به زودی</span>
            </a>
          @endif
        </div>
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
        @if ($this->hasPermission('statistics'))
          <a href="{{ route('dr-my-performance-chart') }}">آمار و نمودار</a>
        @endif
        @if ($this->hasPermission('messages'))
          <a href="{{ route('dr-panel-tickets') }}">تیکت‌ها</a>
        @endif
        @if ($this->hasPermission('patient_communication'))
          <a href="{{ route('dr.panel.send-message') }}">ارسال پیام</a>
        @endif
        @if ($this->hasPermission('financial_reports'))
          <a href="{{ route('dr.panel.financial-reports.index') }}">گزارش مالی</a>
          <a href="{{ route('dr-payment-setting') }}">پرداخت</a>
          <a href="{{ route('dr-wallet-charge') }}">شارژ کیف پول</a>
        @endif
        @if ($this->hasPermission('insurance'))
          <a href="{{ route('dr.panel.doctor-services.index') }}">خدمات و بیمه</a>
        @endif
        @if ($this->hasPermission('clinic_management'))
          <a href="{{ route('dr-clinic-management') }}">مدیریت مطب</a>
          @if ($this->hasPermission('dr.panel.clinics.medical-documents'))
            <a href="{{ route('dr.panel.clinics.medical-documents') }}">مدارک من</a>
          @endif
          @if ($this->hasPermission('doctors.clinic.deposit'))
            <a href="{{ route('doctors.clinic.deposit') }}">بیعانه</a>
          @endif
        @endif
        @if ($this->hasPermission('secretary_management'))
          <a href="{{ route('dr-secretary-management') }}">مدیریت منشی‌ها</a>
          @if ($this->hasPermission('permissions'))
            <a href="{{ route('dr-secretary-permissions') }}">دسترسی‌های منشی</a>
          @endif
        @endif
      </div>
    </div>
  </div>
</div>

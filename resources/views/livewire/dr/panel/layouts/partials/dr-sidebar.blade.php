<div>
  <div class="sidebar__nav border-top border-left" data-user-type="doctor">
    <div class="sidebar__fixed">
      <span class="bars d-none padding-0-18"></span>
      <div class="profile__info border cursor-pointer text-center">
        <div class="avatar__img cursor-pointer">
          <img id="profile-photo-img"
            src="{{ $user->profile_photo_path ? Storage::url($user->profile_photo_path) : asset('dr-assets/panel/img/pro.jpg') }}"
            class="avatar___img cursor-pointer"
            onerror="this.src='{{ asset('dr-assets/panel/img/pro.jpg') }}'; this.onerror=null;">
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
  <div class="mobile-nav d-md-none">
    <div class="mobile-nav-container">
      <!-- Main Mobile Navigation Items -->
      <div class="mobile-nav-main">
        @if ($this->hasPermission('dashboard'))
          <a href="{{ route('dr-panel') }}"
            class="mobile-nav-item {{ Request::routeIs('dr-panel') ? 'active' : '' }}">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <path d="M3 13H11V3H3V13ZM3 21H11V15H3V21ZM13 21H21V11H13V21ZM13 3V9H21V3H13Z" fill="currentColor" />
            </svg>
            <span>داشبورد</span>
          </a>
        @endif

        @if ($this->hasPermission('appointments'))
          <a href="{{ route('dr-appointments') }}"
            class="mobile-nav-item {{ Request::routeIs('dr-appointments') ? 'active' : '' }}">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <path
                d="M19 3H5C3.89 3 3 3.89 3 5V19C3 20.11 3.89 21 5 21H19C20.11 21 21 20.11 21 19V5C21 3.89 20.11 3 19 3ZM19 19H5V9H19V19ZM19 7H5V5H19V7Z"
                fill="currentColor" />
              <path
                d="M7 11H9V13H7V11ZM11 11H13V13H11V11ZM15 11H17V13H15V11ZM7 15H9V17H7V15ZM11 15H13V17H11V15ZM15 15H17V17H15V15Z"
                fill="currentColor" />
            </svg>
            <span>نوبت</span>
          </a>
        @endif

        @if ($this->hasPermission('dr-workhours'))
          <a href="{{ route('dr-workhours') }}"
            class="mobile-nav-item {{ Request::routeIs('dr-workhours') ? 'active' : '' }}">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <path
                d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 20C7.59 20 4 16.41 4 12C4 7.59 7.59 4 12 4C16.41 4 20 7.59 20 12C20 16.41 16.41 20 12 20Z"
                fill="currentColor" />
              <path d="M12.5 7H11V13L16.25 16.15L17 14.92L12.5 12.25V7Z" fill="currentColor" />
            </svg>
            <span>ساعت کاری</span>
          </a>
        @endif

        @if ($this->hasPermission('my-prescriptions'))
          <a href="{{ route('dr.panel.my-prescriptions') }}"
            class="mobile-nav-item {{ Request::routeIs('dr.panel.my-prescriptions') ? 'active' : '' }}">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <path
                d="M19 3H5C3.89 3 3 3.89 3 5V19C3 20.11 3.89 21 5 21H19C20.11 21 21 20.11 21 19V5C21 3.89 20.11 3 19 3ZM19 19H5V5H19V19Z"
                fill="currentColor" />
              <path d="M7 7H17V9H7V7ZM7 11H17V13H7V11ZM7 15H14V17H7V15Z" fill="currentColor" />
            </svg>
            <span>نسخه</span>
          </a>
        @endif

        @if ($this->hasPermission('financial_reports'))
          <a href="{{ route('dr.panel.financial-reports.index') }}"
            class="mobile-nav-item {{ Request::routeIs('dr.panel.financial-reports.index') ? 'active' : '' }}">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
              xmlns="http://www.w3.org/2000/svg">
              <path d="M3 3V21H21V3H3ZM19 19H5V5H19V19Z" fill="currentColor" />
              <path
                d="M7 7H9V9H7V7ZM11 7H13V9H11V7ZM15 7H17V9H15V7ZM7 11H9V13H7V11ZM11 11H13V13H11V11ZM15 11H17V13H15V11ZM7 15H9V17H7V15ZM11 15H13V17H11V15ZM15 15H17V17H15V15Z"
                fill="currentColor" />
            </svg>
            <span>گزارش مالی</span>
          </a>
        @endif

        <!-- Others Menu Toggle -->
        <button class="mobile-nav-item mobile-nav-toggle" id="mobile-others-toggle">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM12 18C13.1 18 14 18.9 14 20C14 21.1 13.1 22 12 22C10.9 22 10 21.1 10 20C10 18.9 10.9 18 12 18ZM12 10C13.1 10 14 10.9 14 12C14 13.1 13.1 14 12 14C10.9 14 10 13.1 10 12C10 10.9 10.9 10 12 10Z"
              fill="currentColor" />
          </svg>
          <span>سایر</span>

        </button>
      </div>
    </div>

    <!-- Full Screen Others Menu -->
    <div class="mobile-others-menu" id="mobile-others-menu">
      <div class="mobile-others-header">
        <button class="mobile-close-btn" id="mobile-close-btn">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M19 6.41L17.59 5L12 10.59L6.41 5L5 6.41L10.59 12L5 17.59L6.41 19L12 13.41L17.59 19L19 17.59L13.41 12L19 6.41Z"
              fill="currentColor" />
          </svg>
        </button>
      </div>

      <div class="mobile-others-content">
        <!-- Appointments Section -->
        @if ($this->hasPermission('appointments'))
          <div class="mobile-section">
            <h4 class="mobile-section-title">نوبت اینترنتی</h4>
            <div class="mobile-section-items">
              @if ($this->hasPermission('dr-appointments'))
                <a href="{{ route('dr-appointments') }}"
                  class="mobile-section-item {{ Request::routeIs('dr-appointments') ? 'active' : '' }}">
                  لیست نوبت ها
                </a>
              @endif
              @if ($this->hasPermission('dr.panel.doctornotes.index'))
                <a href="{{ route('dr.panel.doctornotes.index') }}"
                  class="mobile-section-item {{ Request::routeIs('dr.panel.doctornotes.index') ? 'active' : '' }}">
                  توضیحات نوبت
                </a>
              @endif
              @if ($this->hasPermission('dr-mySpecialDays'))
                <a href="{{ route('dr-mySpecialDays') }}"
                  class="mobile-section-item {{ Request::routeIs('dr-mySpecialDays') ? 'active' : '' }}">
                  روزهای خاص
                </a>
              @endif
              @if ($this->hasPermission('dr-scheduleSetting'))
                <a href="{{ route('dr-scheduleSetting') }}"
                  class="mobile-section-item {{ Request::routeIs('dr-scheduleSetting') ? 'active' : '' }}">
                  تنظیمات نوبت
                </a>
              @endif
              @if ($this->hasPermission('dr-vacation'))
                <a href="{{ route('dr-vacation') }}"
                  class="mobile-section-item {{ Request::routeIs('dr-vacation') ? 'active' : '' }}">
                  تعطیلات
                </a>
              @endif
              @if ($this->hasPermission('doctor-blocking-users.index'))
                <a href="{{ route('doctor-blocking-users.index') }}"
                  class="mobile-section-item {{ Request::routeIs('doctor-blocking-users.index') ? 'active' : '' }}">
                  کاربران مسدود
                </a>
              @endif
            </div>
          </div>
        @endif

        <!-- Prescriptions Section -->
        @if ($this->hasPermission('my-prescriptions'))
          <div class="mobile-section">
            <h4 class="mobile-section-title">نسخه های من</h4>
            <div class="mobile-section-items">
              <a href="{{ route('dr.panel.my-prescriptions') }}"
                class="mobile-section-item {{ Request::routeIs('dr.panel.my-prescriptions') ? 'active' : '' }}">
                مدیریت نسخه ها
              </a>
              <a href="{{ route('dr.panel.my-prescriptions.settings') }}"
                class="mobile-section-item {{ Request::routeIs('dr.panel.my-prescriptions.settings') ? 'active' : '' }}">
                تنظیمات درخواست نسخه
              </a>
            </div>
          </div>
        @endif

        <!-- Consultation Section -->
        @if ($this->hasPermission('consult'))
          <div class="mobile-section">
            <h4 class="mobile-section-title">مشاوره</h4>
            <div class="mobile-section-items">
              @if ($this->hasPermission('dr-moshavere_setting'))
                <a href="javascript:void(0)" class="mobile-section-item disabled">
                  برنامه‌ریزی مشاوره
                </a>
              @endif
              @if ($this->hasPermission('dr-moshavere_waiting'))
                <a href="javascript:void(0)" class="mobile-section-item disabled">
                  گزارش مشاوره
                </a>
              @endif
              @if ($this->hasPermission('dr-mySpecialDays-counseling'))
                <a href="javascript:void(0)" class="mobile-section-item disabled">
                  روزهای خاص
                </a>
              @endif
              @if ($this->hasPermission('consult-term.index'))
                <a href="javascript:void(0)" class="mobile-section-item disabled">
                  قوانین مشاوره
                </a>
              @endif
            </div>
          </div>
        @endif

        <!-- Insurance Section -->
        @if ($this->hasPermission('insurance'))
          <div class="mobile-section">
            <h4 class="mobile-section-title">خدمات و بیمه</h4>
            <div class="mobile-section-items">
              <a href="{{ route('dr.panel.doctor-services.index') }}"
                class="mobile-section-item {{ Request::routeIs('dr.panel.doctor-services.index') ? 'active' : '' }}">
                خدمات و بیمه
              </a>
            </div>
          </div>
        @endif

        <!-- Electronic Prescription Section -->
        @if ($this->hasPermission('prescription'))
          <div class="mobile-section">
            <h4 class="mobile-section-title">نسخه الکترونیک</h4>
            <div class="mobile-section-items">
              @if ($this->hasPermission('dr-patient-records'))
                <a href="javascript:void(0)" class="mobile-section-item disabled">
                  پرونده پزشکی
                </a>
              @endif
              @if ($this->hasPermission('prescription.index'))
                <a href="javascript:void(0)" class="mobile-section-item disabled">
                  نسخه‌های ثبت شده
                </a>
              @endif
              @if ($this->hasPermission('providers.index'))
                <a href="javascript:void(0)" class="mobile-section-item disabled">
                  بیمه‌های من
                </a>
              @endif
              @if ($this->hasPermission('favorite.templates.index'))
                <a href="javascript:void(0)" class="mobile-section-item disabled">
                  نسخه پراستفاده
                </a>
              @endif
              @if ($this->hasPermission('templates.favorite.service.index'))
                <a href="javascript:void(0)" class="mobile-section-item disabled">
                  اقلام پراستفاده
                </a>
              @endif
              @if ($this->hasPermission('patient_records'))
                <a href="javascript:void(0)" class="mobile-section-item disabled">
                  پرونده الکترونیک
                </a>
              @endif
            </div>
          </div>
        @endif

        <!-- Financial Reports Section -->
        @if ($this->hasPermission('financial_reports'))
          <div class="mobile-section">
            <h4 class="mobile-section-title">گزارش مالی</h4>
            <div class="mobile-section-items">
              @if ($this->hasPermission('dr.panel.financial-reports.index'))
                <a href="{{ route('dr.panel.financial-reports.index') }}"
                  class="mobile-section-item {{ Request::routeIs('dr.panel.financial-reports.index') ? 'active' : '' }}">
                  گزارش مالی
                </a>
              @endif
              @if ($this->hasPermission('dr-payment-setting'))
                <a href="{{ route('dr-payment-setting') }}"
                  class="mobile-section-item {{ Request::routeIs('dr-payment-setting') ? 'active' : '' }}">
                  پرداخت
                </a>
              @endif
              @if ($this->hasPermission('dr-wallet-charge'))
                <a href="{{ route('dr-wallet-charge') }}"
                  class="mobile-section-item {{ Request::routeIs('dr-wallet-charge') ? 'active' : '' }}">
                  شارژ کیف‌پول
                </a>
              @endif
            </div>
          </div>
        @endif

        <!-- Patient Communication Section -->
        @if ($this->hasPermission('patient_communication'))
          <div class="mobile-section">
            <h4 class="mobile-section-title">ارتباط با بیماران</h4>
            <div class="mobile-section-items">
              @if ($this->hasPermission('dr.panel.send-message'))
                <a href="{{ route('dr.panel.send-message') }}"
                  class="mobile-section-item {{ Request::routeIs('dr.panel.send-message') ? 'active' : '' }}">
                  ارسال پیام
                </a>
              @endif
            </div>
          </div>
        @endif

        <!-- Secretary Management Section -->
        @if ($this->hasPermission('secretary_management'))
          <div class="mobile-section">
            <h4 class="mobile-section-title">منشی</h4>
            <div class="mobile-section-items">
              @if ($this->hasPermission('secretary_management'))
                <a href="{{ route('dr-secretary-management') }}"
                  class="mobile-section-item {{ Request::routeIs('dr-secretary-management') ? 'active' : '' }}">
                  مدیریت منشی‌ها
                </a>
              @endif
              @if ($this->hasPermission('permissions'))
                <a href="{{ route('dr-secretary-permissions') }}"
                  class="mobile-section-item {{ Request::routeIs('dr-secretary-permissions') ? 'active' : '' }}">
                  دسترسی‌ها
                </a>
              @endif
            </div>
          </div>
        @endif

        <!-- Clinic Management Section -->
        @if ($this->hasPermission('clinic_management'))
          <div class="mobile-section">
            <h4 class="mobile-section-title">مطب</h4>
            <div class="mobile-section-items">
              @if ($this->hasPermission('dr-clinic-management'))
                <a href="{{ route('dr-clinic-management') }}"
                  class="mobile-section-item {{ Request::routeIs('dr-clinic-management') ? 'active' : '' }}">
                  مدیریت مطب
                </a>
              @endif
              @if ($this->hasPermission('dr.panel.clinics.medical-documents'))
                <a href="{{ route('dr.panel.clinics.medical-documents') }}"
                  class="mobile-section-item {{ Request::routeIs('dr.panel.clinics.medical-documents') ? 'active' : '' }}">
                  مدارک من
                </a>
              @endif
              @if ($this->hasPermission('doctors.clinic.deposit'))
                <a href="{{ route('doctors.clinic.deposit') }}"
                  class="mobile-section-item {{ Request::routeIs('doctors.clinic.deposit') ? 'active' : '' }}">
                  بیعانه
                </a>
              @endif
            </div>
          </div>
        @endif

        <!-- Profile Section -->
        @if ($this->hasPermission('profile'))
          <div class="mobile-section">
            <h4 class="mobile-section-title">حساب کاربری</h4>
            <div class="mobile-section-items">
              @if ($this->hasPermission('dr-edit-profile'))
                <a href="{{ route('dr-edit-profile') }}"
                  class="mobile-section-item {{ Request::routeIs('dr-edit-profile') ? 'active' : '' }}">
                  ویرایش پروفایل
                </a>
              @endif
              @if ($this->hasPermission('dr-edit-profile-security'))
                <a href="{{ route('dr-edit-profile-security') }}"
                  class="mobile-section-item {{ Request::routeIs('dr-edit-profile-security') ? 'active' : '' }}">
                  امنیت
                </a>
              @endif
              @if ($this->hasPermission('dr-edit-profile-upgrade'))
                <a href="javascript:void(0)" class="mobile-section-item disabled">
                  ارتقا حساب
                </a>
              @endif
              @if ($this->hasPermission('dr-my-performance'))
                <a href="{{ route('dr-my-performance') }}"
                  class="mobile-section-item {{ Request::routeIs('dr-my-performance') ? 'active' : '' }}">
                  عملکرد من
                </a>
              @endif
              @if ($this->hasPermission('dr-subuser'))
                <a href="{{ route('dr-subuser') }}"
                  class="mobile-section-item {{ Request::routeIs('dr-subuser') ? 'active' : '' }}">
                  کاربران زیرمجموعه
                </a>
              @endif
              @if ($this->hasPermission('my-dr-appointments'))
                <a href="{{ route('my-dr-appointments') }}"
                  class="mobile-section-item {{ Request::routeIs('my-dr-appointments') ? 'active' : '' }}">
                  نوبت‌های من
                </a>
              @endif
              @if ($this->hasPermission('dr.panel.doctor-faqs.index'))
                <a href="{{ route('dr.panel.doctor-faqs.index') }}"
                  class="mobile-section-item {{ Request::routeIs('dr.panel.doctor-faqs.index') ? 'active' : '' }}">
                  سوالات متداول
                </a>
              @endif
            </div>
          </div>
        @endif

        <!-- Statistics Section -->
        @if ($this->hasPermission('statistics'))
          <div class="mobile-section">
            <h4 class="mobile-section-title">آمار و نمودار</h4>
            <div class="mobile-section-items">
              <a href="{{ route('dr-my-performance-chart') }}"
                class="mobile-section-item {{ Request::routeIs('dr-my-performance-chart') ? 'active' : '' }}">
                آمار و نمودار
              </a>
            </div>
          </div>
        @endif

        <!-- Messages Section -->
        @if ($this->hasPermission('messages'))
          <div class="mobile-section">
            <h4 class="mobile-section-title">پیام</h4>
            <div class="mobile-section-items">
              @if ($this->hasPermission('dr-panel-tickets'))
                <a href="{{ route('dr-panel-tickets') }}"
                  class="mobile-section-item {{ Request::routeIs('dr-panel-tickets') ? 'active' : '' }}">
                  تیکت‌ها
                </a>
              @endif
              @if ($this->hasPermission('#'))
                <a href="#" class="mobile-section-item">
                  صفحه گفتگو
                </a>
              @endif
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>

  <style>
    /* Mobile Navigation Styles */
    .mobile-nav {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      background: #fff;
      border-top: 1px solid #e0e0e0;
      z-index: 1000;
      box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
    }

    .mobile-nav-container {
      padding: 6px 12px;
    }

    .mobile-nav-main {
      display: flex;
      justify-content: space-around;
      align-items: center;
    }

    .mobile-nav-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-decoration: none;
      color: #666;
      font-size: 10px;
      padding: 6px 3px;
      border-radius: 6px;
      transition: all 0.3s ease;
      min-width: 55px;
      background: none;
      border: none;
      cursor: pointer;
    }

    .mobile-nav-item:hover,
    .mobile-nav-item.active {
      color: #007bff;
      background: rgba(0, 123, 255, 0.1);
    }

    .mobile-nav-item svg {
      margin-bottom: 3px;
      transition: transform 0.3s ease;
    }

    .mobile-nav-item span {
      text-align: center;
      line-height: 1.1;
      white-space: nowrap;
    }

    .mobile-nav-toggle {
      position: relative;
    }

    .toggle-arrow {
      margin-top: 2px;
      transition: transform 0.3s ease;
    }

    .mobile-nav-toggle.active .toggle-arrow {
      transform: rotate(180deg);
    }

    /* Full Screen Others Menu */
    .mobile-others-menu {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: #fff;
      z-index: 2000;
      transform: translateY(100%);
      transition: transform 0.3s ease;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
    }

    .mobile-others-menu.active {
      transform: translateY(0);
    }

    .mobile-others-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 16px 12px;
      border-bottom: 1px solid #e0e0e0;
      background: #f8f9fa;
      position: sticky;
      top: 0;
      z-index: 10;
    }

    .mobile-others-header h3 {
      margin: 0;
      font-size: 16px;
      font-weight: 600;
      color: #333;
    }

    .mobile-close-btn {
      background: none;
      border: none;
      padding: 6px;
      border-radius: 50%;
      cursor: pointer;
      color: #666;
      transition: all 0.3s ease;
    }

    .mobile-close-btn:hover {
      background: rgba(0, 0, 0, 0.1);
      color: #333;
    }

    .mobile-others-content {
      flex: 1;
      padding: 12px;
      overflow-y: auto;
    }

    .mobile-section {
      margin-bottom: 16px;
    }

    .mobile-section-title {
      font-size: 14px;
      font-weight: 600;
      color: #333;
      margin-bottom: 8px;
      padding-bottom: 6px;
      border-bottom: 2px solid #007bff;
    }

    .mobile-section-items {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
      gap: 6px;
    }

    .mobile-section-item {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 10px 8px;
      background: #f8f9fa;
      border-radius: 8px;
      text-decoration: none;
      color: #333;
      font-size: 12px;
      transition: all 0.3s ease;
      border: 1px solid transparent;
      text-align: center;
      min-height: 40px;
      line-height: 1.2;
    }

    .mobile-section-item:hover {
      background: #e9ecef;
      border-color: #007bff;
      color: #007bff;
      transform: translateY(-1px);
      box-shadow: 0 2px 8px rgba(0, 123, 255, 0.15);
    }

    .mobile-section-item.active {
      background: #007bff;
      color: #fff;
      border-color: #007bff;
      box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
    }

    .mobile-section-item.disabled {
      opacity: 0.5;
      color: #6c757d;
      cursor: not-allowed;
      background: #f8f9fa;
    }

    .mobile-section-item.disabled:hover {
      background: #f8f9fa;
      border-color: transparent;
      color: #6c757d;
      transform: none;
      box-shadow: none;
    }

    /* Responsive adjustments */
    @media (max-width: 480px) {
      .mobile-nav-item {
        font-size: 9px;
        min-width: 45px;
        padding: 4px 2px;
      }

      .mobile-nav-item svg {
        width: 18px;
        height: 18px;
      }

      .mobile-others-content {
        padding: 8px;
      }

      .mobile-section-items {
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 5px;
      }

      .mobile-section-item {
        padding: 8px 6px;
        font-size: 11px;
        min-height: 36px;
      }

      .mobile-section {
        margin-bottom: 12px;
      }

      .mobile-section-title {
        font-size: 13px;
        margin-bottom: 6px;
      }
    }

    @media (max-width: 360px) {
      .mobile-section-items {
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
        gap: 4px;
      }

      .mobile-section-item {
        padding: 6px 4px;
        font-size: 10px;
        min-height: 32px;
      }
    }

    /* Hide desktop sidebar on mobile */
    @media (max-width: 767px) {
      .sidebar__nav {
        display: none;
      }

      .bars {
        display: none !important;
      }
    }
  </style>

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

      // Mobile Navigation JavaScript
      const mobileOthersToggle = document.getElementById('mobile-others-toggle');
      const mobileOthersMenu = document.getElementById('mobile-others-menu');
      const mobileCloseBtn = document.getElementById('mobile-close-btn');

      if (mobileOthersToggle) {
        mobileOthersToggle.addEventListener('click', function() {
          mobileOthersMenu.classList.add('active');
          this.classList.add('active');
          document.body.style.overflow = 'hidden';
        });
      }

      if (mobileCloseBtn) {
        mobileCloseBtn.addEventListener('click', function() {
          mobileOthersMenu.classList.remove('active');
          mobileOthersToggle.classList.remove('active');
          document.body.style.overflow = '';
        });
      }

      // Close menu when clicking outside
      if (mobileOthersMenu) {
        mobileOthersMenu.addEventListener('click', function(e) {
          if (e.target === this) {
            this.classList.remove('active');
            mobileOthersToggle.classList.remove('active');
            document.body.style.overflow = '';
          }
        });
      }

      // Close menu on escape key
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && mobileOthersMenu.classList.contains('active')) {
          mobileOthersMenu.classList.remove('active');
          mobileOthersToggle.classList.remove('active');
          document.body.style.overflow = '';
        }
      });
    });
  </script>
</div>

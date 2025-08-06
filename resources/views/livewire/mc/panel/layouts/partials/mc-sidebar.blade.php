<div>
  <div class="sidebar__nav border-top border-left">
    <div class="sidebar__fixed">
      <span class="bars d-none padding-0-18"></span>
      <div class="profile__info border cursor-pointer text-center">
        <div class="avatar__img cursor-pointer">
          <img id="profile-photo-img"
            src="{{ optional($medical_center)->avatar ? Storage::url(optional($medical_center)->avatar) : asset('mc-assets/panel/img/pro.jpg') }}"
            class="avatar___img cursor-pointer">
          <input type="file" accept="image/*" class="avatar-img__input" id="profile-photo-input">
          <div class="v-dialog__container" style="display: block;"></div>
          <div class="box__camera default__avatar"></div>
        </div>
        <span class="profile__name sidebar-full-name">
          {{ optional($medical_center)->name }}
        </span>
        <span class="fs-11 fw-bold" id="takhasos-txt"></span>
      </div>
    </div>
    <div class="sidebar__scrollable">
      <ul class="" id="">
        @if($this->hasPermission('dashboard'))
        <li class="item-li i-dashboard {{ Request::routeIs('mc-panel') ? 'is-active' : '' }}">
          <a href="{{ route('mc-panel') }}">داشبورد</a>
        </li>
        @endif
        
        @if($this->hasPermission('medical_center_management'))
        <li
          class="item-li i-users {{ Request::routeIs('mc.panel.doctors.index') || Request::routeIs('mc.panel.doctors.create') || Request::routeIs('mc.panel.doctors.edit') || Request::routeIs('mc.panel.specialties.index') || Request::routeIs('mc.panel.specialties.create') || Request::routeIs('mc.panel.specialties.edit') || Request::routeIs('mc.panel.services.index') || Request::routeIs('mc.panel.services.create') || Request::routeIs('mc.panel.services.edit') || Request::routeIs('mc.panel.insurances.index') || Request::routeIs('mc.panel.insurances.create') || Request::routeIs('mc.panel.insurances.edit') || Request::routeIs('mc.panel.profile.edit') ? 'is-active' : '' }} d-flex flex-column justify-content-center"
          id="medical-center-management">
          <a href="#" class="d-flex justify-content-between w-100 align-items-center">
            مرکز درمانی من
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
            @if($this->hasPermission('mc.panel.doctors.index'))
            <li class="item-li i-user__inforamtion {{ Request::routeIs('mc.panel.doctors.index') ? 'is-active' : '' }}">
              <a href="{{ route('mc.panel.doctors.index') }}">مدیریت پزشکان</a>
            </li>
            @endif
            @if($this->hasPermission('mc.panel.profile.edit'))
            <li class="item-li i-user__inforamtion {{ Request::routeIs('mc.panel.profile.edit') ? 'is-active' : '' }}">
              <a href="{{ route('mc.panel.profile.edit') }}">ویرایش پروفایل</a>
            </li>
            @endif
            @if($this->hasPermission('mc.panel.specialties.index'))
            <li
              class="item-li i-user__inforamtion {{ Request::routeIs('mc.panel.specialties.index') ? 'is-active' : '' }}">
              <a href="{{ route('mc.panel.specialties.index') }}">مدیریت تخصص‌ها</a>
            </li>
            @endif
            @if($this->hasPermission('mc.panel.services.index'))
            <li
              class="item-li i-user__inforamtion {{ Request::routeIs('mc.panel.services.index') ? 'is-active' : '' }}">
              <a href="{{ route('mc.panel.services.index') }}">مدیریت خدمات</a>
            </li>
            @endif
            @if($this->hasPermission('mc.panel.insurances.index'))
            <li
              class="item-li i-user__inforamtion {{ Request::routeIs('mc.panel.insurances.index') ? 'is-active' : '' }}">
              <a href="{{ route('mc.panel.insurances.index') }}">مدیریت بیمه‌ها</a>
            </li>
            @endif
          </ul>
        </li>
        @endif
        
        @if($this->hasPermission('workhours'))
     
        <li class="item-li i-checkout__request {{ Request::routeIs('mc-workhours') ? 'is-active' : '' }}">
          <a href="{{ route('mc-workhours') }}">ساعت کاری</a>
        </li>
        @endif

        @if($this->hasPermission('appointments'))
        <li
          class="item-li i-courses {{ Request::routeIs('mc-appointments') || Request::routeIs('mc.panel.doctornotes.index') || Request::routeIs('mc-mySpecialDays') || Request::routeIs('mc-scheduleSetting') || Request::routeIs('mc-vacation') || Request::routeIs('mc-doctor-blocking-users.index') ? 'is-active' : '' }}">
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
            @if($this->hasPermission('mc-appointments'))
            <li class="item-li {{ Request::routeIs('mc-appointments') ? 'is-active' : '' }}">
              <a href="{{ route('mc-appointments') }}"> لیست نوبت ها</a>
            </li>
            @endif
            @if($this->hasPermission('mc.panel.doctornotes.index'))
            <li class="item-li {{ Request::routeIs('mc.panel.doctornotes.index') ? 'is-active' : '' }}">
              <a href="{{ route('mc.panel.doctornotes.index') }}"> توضیحات نوبت</a>
            </li>
            @endif
            @if($this->hasPermission('mc-mySpecialDays'))
            <li class="item-li {{ Request::routeIs('mc-mySpecialDays') ? 'is-active' : '' }}">
              <a href="{{ route('mc-mySpecialDays') }}">روزهای خاص</a>
            </li>
            @endif
            @if($this->hasPermission('mc-scheduleSetting'))
            <li class="item-li {{ Request::routeIs('mc-scheduleSetting') ? 'is-active' : '' }}">
              <a href="{{ route('mc-scheduleSetting') }}">تنظیمات نوبت</a>
            </li>
            @endif
            @if($this->hasPermission('mc-vacation'))
            <li class="item-li {{ Request::routeIs('mc-vacation') ? 'is-active' : '' }}">
              <a href="{{ route('mc-vacation') }}">تعطیلات</a>
            </li>
            @endif
            @if($this->hasPermission('mc-doctor-blocking-users.index'))
            <li class="item-li {{ Request::routeIs('mc-doctor-blocking-users.index') ? 'is-active' : '' }}">
              <a href="{{ route('mc-doctor-blocking-users.index') }}">کاربران مسدود</a>
            </li>
            @endif
          </ul>
        </li>
        @endif
        
        @if($this->hasPermission('prescriptions'))
        <li class="item-li i-banners {{ Request::routeIs('mc.panel.my-prescriptions') ? 'is-active' : '' }}">
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
            @if($this->hasPermission('mc.panel.my-prescriptions'))
            <li class="item-li i-courses {{ Request::routeIs('mc.panel.my-prescriptions') ? 'is-active' : '' }}">
              <a href="{{ route('mc.panel.my-prescriptions') }}">مدیریت نسخه ها</a>
            </li>
            @endif
            @if($this->hasPermission('mc.panel.my-prescriptions.settings'))
            <li
              class="item-li i-courses {{ Request::routeIs('mc.panel.my-prescriptions.settings') ? 'is-active' : '' }}">
              <a href="{{ route('mc.panel.my-prescriptions.settings') }}">تنظیمات درخواست نسخه</a>
            </li>
            @endif
          </ul>
        </li>
        @endif
        
        @if($this->hasPermission('consultation'))
        <li
          class="item-li i-moshavere {{ Request::routeIs('mc-moshavere_setting') || Request::routeIs('mc-moshavere_waiting') || Request::routeIs('consult-term.index') || Request::routeIs('mc-mySpecialDays-counseling') ? 'is-active' : '' }}">
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
            @if($this->hasPermission('mc-moshavere_setting'))
            <li class="item-li i-courses {{ Request::routeIs('mc-moshavere_setting') ? 'is-active' : '' }}"
              style="opacity: 0.5; pointer-events: none;">
              <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed;">برنامه‌ریزی مشاوره</a>
            </li>
            @endif
            @if($this->hasPermission('mc-moshavere_waiting'))
            <li class="item-li i-user__inforamtion {{ Request::routeIs('mc-moshavere_waiting') ? 'is-active' : '' }}"
              style="opacity: 0.5; pointer-events: none;">
              <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed;">گزارش مشاوره</a>
            </li>
            @endif
            @if($this->hasPermission('mc-mySpecialDays-counseling'))
            <li
              class="item-li i-user__inforamtion {{ Request::routeIs('mc-mySpecialDays-counseling') ? 'is-active' : '' }}"
              style="opacity: 0.5; pointer-events: none;">
              <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed;">روزهای خاص</a>
            </li>
            @endif
            @if($this->hasPermission('consult-term.index'))
            <li class="item-li i-user__inforamtion {{ Request::routeIs('consult-term.index') ? 'is-active' : '' }}"
              style="opacity: 0.5; pointer-events: none;">
              <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed;">قوانین مشاوره</a>
            </li>
            @endif
          </ul>
        </li>
        @endif
        
        @if($this->hasPermission('doctor_services'))
        <li
          class="item-li i-checkout__request {{ Request::routeIs('mc.panel.doctor-services.index') ? 'is-active' : '' }}">
          <a href="{{ route('mc.panel.doctor-services.index') }}">خدمات و بیمه</a>
        </li>
        @endif
        
        @if($this->hasPermission('electronic_prescriptions'))
        <li
          class="item-li i-banners {{ Request::routeIs('prescription.index') || Request::routeIs('providers.index') || Request::routeIs('favorite.templates.index') || Request::routeIs('templates.favorite.service.index') || Request::routeIs('mc-patient-records') ? 'is-active' : '' }}">
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
            @if($this->hasPermission('prescription.index'))
            <li class="item-li" style="opacity: 0.5; pointer-events: none;">
              <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed;">پرونده پزشکی</a>
            </li>
            @endif
            @if($this->hasPermission('prescription.index'))
            <li class="item-li i-courses {{ Request::routeIs('prescription.index') ? 'is-active' : '' }}"
              style="opacity: 0.5; pointer-events: none;">
              <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed;">نسخه‌های ثبت شده</a>
            </li>
            @endif
            @if($this->hasPermission('providers.index'))
            <li class="item-li i-courses {{ Request::routeIs('providers.index') ? 'is-active' : '' }}"
              style="opacity: 0.5; pointer-events: none;">
              <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed;">بیمه‌های من</a>
            </li>
            @endif
            @if($this->hasPermission('favorite.templates.index'))
            <li class="item-li i-courses {{ Request::routeIs('favorite.templates.index') ? 'is-active' : '' }}"
              style="opacity: 0.5; pointer-events: none;">
              <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed;">نسخه پراستفاده</a>
            </li>
            @endif
            @if($this->hasPermission('templates.favorite.service.index'))
            <li
              class="item-li i-courses {{ Request::routeIs('templates.favorite.service.index') ? 'is-active' : '' }}"
              style="opacity: 0.5; pointer-events: none;">
              <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed;">اقلام پراستفاده</a>
            </li>
            @endif
            @if($this->hasPermission('mc-patient-records'))
            <li
              class="item-li i-checkout__request {{ Request::routeIs('mc-patient-records') ? 'is-active' : '' }} d-flex flex-column justify-content-center"
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
        
        @if($this->hasPermission('financial_reports'))
        <li
          class="item-li i-my__peyments {{ Request::routeIs('mc-wallet') || Request::routeIs('mc-payment-setting') || Request::routeIs('mc.panel.financial-reports.index') || Request::routeIs('mc-wallet-charge') ? 'is-active' : '' }} d-flex flex-column justify-content-center"
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
            @if($this->hasPermission('mc.panel.financial-reports.index'))
            <li
              class="item-li i-user__inforamtion {{ Request::routeIs('mc.panel.financial-reports.index') ? 'is-active' : '' }}">
              <a href="{{ route('mc.panel.financial-reports.index') }}">گزارش مالی</a>
            </li>
            @endif
            @if($this->hasPermission('mc-payment-setting'))
            <li class="item-li i-user__inforamtion {{ Request::routeIs('mc-payment-setting') ? 'is-active' : '' }}">
              <a href="{{ route('mc-payment-setting') }}">پرداخت</a>
            </li>
            @endif
            @if($this->hasPermission('mc-wallet-charge'))
            <li class="item-li i-user__inforamtion {{ Request::routeIs('mc-wallet-charge') ? 'is-active' : '' }}">
              <a href="{{ route('mc-wallet-charge') }}">شارژ کیف‌پول</a>
            </li>
            @endif
          </ul>
        </li>
        @endif
        
        @if($this->hasPermission('send_messages'))
        <li class="item-li i-users {{ Request::routeIs('mc.panel.send-message') ? 'is-active' : '' }}">
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
            @if($this->hasPermission('mc.panel.send-message'))
            <li class="item-li"><a href="{{ route('mc.panel.send-message') }}">ارسال پیام</a></li>
            @endif
          </ul>
        </li>
        @endif
        
        @if($this->hasPermission('secretary_management'))
        <li
          class="item-li i-user__secratary {{ Request::routeIs('mc-secretary-management') ? 'is-active' : '' }} d-flex flex-column justify-content-center"
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
            @if($this->hasPermission('mc-secretary-management'))
            <li
              class="item-li i-user__inforamtion {{ Request::routeIs('mc-secretary-management') ? 'is-active' : '' }}">
              <a href="{{ route('mc-secretary-management') }}">مدیریت منشی‌ها</a>
            </li>
            @endif
            @if($this->hasPermission('mc-secretary-permissions'))
            <li
              class="item-li i-checkout__request {{ Request::routeIs('mc-secretary-permissions') ? 'is-active' : '' }}">
              <a href="{{ route('mc-secretary-permissions') }}">دسترسی‌ها</a>
            </li>
            @endif
          </ul>
        </li>
        @endif
        
        @if($this->hasPermission('clinic_management'))
        <li
          class="item-li i-clinic {{ Request::routeIs('mc-clinic-management') || Request::routeIs('doctors.clinic.cost') || Request::routeIs('duration.index') || Request::routeIs('activation.workhours.index') || Request::routeIs('mc.panel.clinics.medical-documents') || Request::routeIs('mc-doctors.clinic.deposit') ? 'is-active' : '' }} d-flex flex-column justify-content-center"
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
            @if($this->hasPermission('mc-clinic-management'))
            <li class="item-li i-user__inforamtion {{ Request::routeIs('mc-clinic-management') ? 'is-active' : '' }}">
              <a href="{{ route('mc-clinic-management') }}">مدیریت مطب</a>
            </li>
            @endif
            @if($this->hasPermission('mc.panel.clinics.medical-documents'))
            <li
              class="item-li i-user__inforamtion {{ Request::routeIs('mc.panel.clinics.medical-documents') ? 'is-active' : '' }}">
              <a href="{{ route('mc.panel.clinics.medical-documents') }}">مدارک من</a>
            </li>
            @endif
            @if($this->hasPermission('mc-doctors.clinic.deposit'))
            <li
              class="item-li i-checkout__request {{ Request::routeIs('mc-doctors.clinic.deposit') ? 'is-active' : '' }}">
              <a href="{{ route('mc-doctors.clinic.deposit') }}">بیعانه</a>
            </li>
            @endif
          </ul>
        </li>
        @endif
        
        @if($this->hasPermission('user_management'))
        <li
          class="item-li i-users {{ Request::routeIs('mc-edit-profile') || Request::routeIs('mc-edit-profile-security') || Request::routeIs('mc-edit-profile-upgrade') || Request::routeIs('mc-my-performance') || Request::routeIs('mc-subuser') || Request::routeIs('my-mc-appointments') || Request::routeIs('mc.panel.doctor-faqs.index') ? 'is-active' : '' }} d-flex flex-column justify-content-center"
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
            @if($this->hasPermission('mc-edit-profile'))
            <li class="item-li i-user__inforamtion {{ Request::routeIs('mc-edit-profile') ? 'is-active' : '' }}">
              <a href="{{ route('mc-edit-profile') }}">ویرایش پروفایل</a>
            </li>
            @endif
            @if($this->hasPermission('mc-edit-profile-security'))
            <li
              class="item-li i-user__inforamtion {{ Request::routeIs('mc-edit-profile-security') ? 'is-active' : '' }}">
              <a href="{{ route('mc-edit-profile-security') }}">امنیت</a>
            </li>
            @endif
            @if($this->hasPermission('mc-edit-profile-upgrade'))
            <li
              class="item-li i-user__inforamtion {{ Request::routeIs('mc-edit-profile-upgrade') ? 'is-active' : '' }}"
              style="opacity: 0.5; pointer-events: none;">
              <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed;"
                class="d-flex align-items-center">
                ارتقا حساب
                <span class="badge bg-danger text-white ms-2" style="font-size: 10px; padding: 2px 6px;">به
                  زودی</span>
              </a>
            </li>
            @endif
            @if($this->hasPermission('mc-my-performance'))
            <li class="item-li i-user__inforamtion {{ Request::routeIs('mc-my-performance') ? 'is-active' : '' }}">
              <a href="{{ route('mc-my-performance') }}">عملکرد من</a>
            </li>
            @endif
            @if($this->hasPermission('mc-subuser'))
            <li class="item-li i-user__inforamtion {{ Request::routeIs('mc-subuser') ? 'is-active' : '' }}">
              <a href="{{ route('mc-subuser') }}">کاربران زیرمجموعه</a>
            </li>
            @endif
            @if($this->hasPermission('my-mc-appointments'))
            <li class="item-li i-user__inforamtion {{ Request::routeIs('my-mc-appointments') ? 'is-active' : '' }}">
              <a href="{{ route('my-mc-appointments') }}">نوبت‌های من</a>
            </li>
            @endif
            @if($this->hasPermission('mc.panel.doctor-faqs.index'))
            <li
              class="item-li i-user__inforamtion {{ Request::routeIs('mc.panel.doctor-faqs.index') ? 'is-active' : '' }}">
              <a href="{{ route('mc.panel.doctor-faqs.index') }}"> سوالات متداول</a>
            </li>
            @endif
          </ul>
        </li>
        @endif
        
        @if($this->hasPermission('performance_charts'))
        <li class="item-li i-transactions {{ Request::routeIs('mc-my-performance-chart') ? 'is-active' : '' }}">
          <a href="{{ route('mc-my-performance-chart') }}">آمار و نمودار</a>
        </li>
        @endif
        
        @if($this->hasPermission('tickets'))
        <li class="item-li i-comments {{ Request::routeIs('mc-panel-tickets') ? 'is-active' : '' }}">
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
            @if($this->hasPermission('mc-panel-tickets'))
            <li class="item-li i-user__inforamtion {{ Request::routeIs('mc-panel-tickets') ? 'is-active' : '' }}">
              <a href="{{ route('mc-panel-tickets') }}">تیکت‌ها</a>
            </li>
            @endif
            <li class="item-li i-user__inforamtion">
              <a href="#">صفحه گفتگو</a>
            </li>
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
          fetch("{{ route('mc.upload-photo') }}", {
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

        .mobile-bottom-nav {
          display: flex;
          position: fixed;
          bottom: 0;
          overflow: hidden !important;
          left: 0;
          width: 100vw;
          background: rgba(255, 255, 255, 0.98);
          box-shadow: 0 -2px 20px rgba(0, 0, 0, 0.08);
          z-index: 25000;
          /* بالاتر از overlay */
          justify-content: space-around;
          align-items: center;
          padding: 0;
          height: 68px;
          border-top: 1px solid rgba(224, 224, 224, 0.8);
          backdrop-filter: blur(10px);
        }

        .mobile-bottom-nav__item {
          position: relative;
          flex: 1 1 0px;
          text-align: center;
          padding: 6px 0 0 0;
          cursor: pointer;
          transition: all 0.3s ease;
          min-width: 0;
          border-radius: 18px 18px 0 0;
          margin: 0 2px;
          overflow: hidden;
        }

        @media (max-width: 400px) {
          .mobile-bottom-nav__label {
            font-size: 10px;
          }

          .mobile-bottom-nav__item svg {
            width: 22px;
            height: 22px;
          }
        }

        .mobile-bottom-nav__item svg {
          display: block;
          margin: 0 auto 2px auto;
          width: 24px;
          height: 24px;
          fill: #888;
          transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
          filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.04));
        }

        .mobile-bottom-nav__item.active svg,
        .mobile-bottom-nav__item:active svg,
        .mobile-bottom-nav__item.open svg {
          fill: #1976d2;
          transform: scale(1.15);
        }

        .mobile-bottom-nav__label {
          font-size: 11px;
          color: #444;
          font-weight: 500;
          letter-spacing: 0.2px;
          margin-top: 4px;
          opacity: 0.92;
          transition: all 0.3s ease;
        }

        .mobile-bottom-nav__item:active,
        .mobile-bottom-nav__item.open {
          background: linear-gradient(135deg, #e3f2fd 0%, #fce4ec 100%);
          box-shadow: 0 2px 12px rgba(25, 118, 210, 0.08);
        }

        .mobile-bottom-nav__dropdown {
          position: absolute;
          bottom: 68px;
          left: 50%;
          transform: translateX(-50%);
          min-width: 180px;
          width: max-content;
          max-width: 95vw;
          background: #fff;
          box-shadow: 0 2px 16px rgba(25, 118, 210, 0.10);
          border-radius: 16px;
          padding: 8px 0;
          z-index: 20000;
          animation: dropdownIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
          display: none;
        }

        .mobile-bottom-nav__item.open .mobile-bottom-nav__dropdown {
          display: block !important;
        }

        .mobile-bottom-nav__dropdown a {
          display: block;
          padding: 12px 20px;
          color: #333;
          text-decoration: none;
          font-size: 14px;
          border-bottom: 1px solid rgba(242, 242, 242, 0.7);
          transition: all 0.25s ease;
          border-radius: 10px;
          margin: 4px 8px;
          font-weight: 500;
        }

        .mobile-bottom-nav__dropdown a:last-child {
          border-bottom: none;
        }

        .mobile-bottom-nav__dropdown a:hover {
          background: linear-gradient(135deg, rgba(227, 242, 253, 0.7) 0%, rgba(252, 228, 236, 0.7) 100%);
          color: #1976d2;
          transform: translateX(3px);
          box-shadow: 0 2px 8px rgba(25, 118, 210, 0.08);
        }

        @keyframes dropdownIn {
          0% {
            opacity: 0;
            transform: translateX(-50%) translateY(20px) scale(0.95);
          }

          70% {
            opacity: 1;
            transform: translateX(-50%) translateY(-5px) scale(1.02);
          }

          100% {
            opacity: 1;
            transform: translateX(-50%) translateY(0) scale(1);
          }
        }

        /* Fix for dashboard dropdown (first item, right edge) */
        .mobile-bottom-nav__item--dashboard .mobile-bottom-nav__dropdown {
          left: auto;
          right: 2.5vw;
          transform: none;
          animation: dropdownInRight 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes dropdownInRight {
          0% {
            opacity: 0;
            transform: translateY(20px) scale(0.95);
          }

          70% {
            opacity: 1;
            transform: translateY(-5px) scale(1.02);
          }

          100% {
            opacity: 1;
            transform: translateY(0) scale(1);
          }
        }

        /* Fix for other dropdown (last item, left edge) */
        .mobile-bottom-nav__item--other .mobile-bottom-nav__dropdown {
          left: 2.5vw;
          right: auto;
          transform: none;
          animation: dropdownInLeft 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes dropdownInLeft {
          0% {
            opacity: 0;
            transform: translateY(20px) scale(0.95);
          }

          70% {
            opacity: 1;
            transform: translateY(-5px) scale(1.02);
          }

          100% {
            opacity: 1;
            transform: translateY(0) scale(1);
          }
        }
      }

      @media (min-width: 769px) {
        .mobile-bottom-nav {
          display: none !important;
        }
      }

      .soon-label {
        font-size: 10px;
        color: #d32f2f;
        margin-right: 4px;
        vertical-align: middle;
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
              if (e.target.closest('.mobile-bottom-nav__dropdown')) {
                return;
              }
              // اگر همین آیتم باز است، فقط ببند
              if (item.classList.contains('open')) {
                item.classList.remove('open');
                lastOpen = null;
              } else {
                navItems.forEach(i => i.classList.remove('open'));
                item.classList.add('open');
                lastOpen = item;
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
    <div class="mobile-bottom-nav__item mobile-bottom-nav__item--dashboard" data-group="dashboard">
      <a href="{{ route('mc-panel') }}"
        style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: inherit;">
        <div class="mobile-bottom-nav__activebox">
          <div class="mobile-bottom-nav__icon">
            <svg viewBox="0 0 24 24">
              <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z" />
            </svg>
          </div>
          <div class="mobile-bottom-nav__label">داشبورد</div>
        </div>
      </a>
    </div>
    <!-- نوبت‌ها -->
    <div class="mobile-bottom-nav__item" data-group="appointments" data-has-submenu="true">
      <div class="mobile-bottom-nav__activebox">
        <div class="mobile-bottom-nav__icon">
          <svg viewBox="0 0 24 24">
            <path
              d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zm0-13H5V5h14v1z" />
          </svg>
        </div>
        <div class="mobile-bottom-nav__label">نوبت‌ها</div>
      </div>
      <div class="mobile-bottom-nav__dropdown" style="display:none">
        @if($this->hasPermission('mc-appointments'))
        <a href="{{ route('mc-appointments') }}">لیست نوبت‌ها</a>
        @endif
        @if($this->hasPermission('mc.panel.doctornotes.index'))
        <a href="{{ route('mc.panel.doctornotes.index') }}">توضیحات نوبت</a>
        @endif
        @if($this->hasPermission('mc-mySpecialDays'))
        <a href="{{ route('mc-mySpecialDays') }}">روزهای خاص</a>
        @endif
        @if($this->hasPermission('mc-scheduleSetting'))
        <a href="{{ route('mc-scheduleSetting') }}">تنظیمات نوبت</a>
        @endif
        @if($this->hasPermission('mc-vacation'))
        <a href="{{ route('mc-vacation') }}">تعطیلات</a>
        @endif
        @if($this->hasPermission('mc-doctor-blocking-users.index'))
        <a href="{{ route('mc-doctor-blocking-users.index') }}">کاربران مسدود</a>
        @endif
        @if($this->hasPermission('mc.panel.my-prescriptions'))
        <a href="{{ route('mc.panel.my-prescriptions') }}">مدیریت نسخه‌ها</a>
        @endif
      </div>
    </div>
    <!-- مشاوره -->
    <div class="mobile-bottom-nav__item" data-group="consult" data-has-submenu="true">
      <div class="mobile-bottom-nav__activebox">
        <div class="mobile-bottom-nav__icon">
          <svg viewBox="0 0 24 24">
            <path
              d="M12 12c2.7 0 8 1.34 8 4v2H4v-2c0-2.66 5.3-4 8-4zm0-2c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3z" />
          </svg>
        </div>
        <div class="mobile-bottom-nav__label">مشاوره</div>
      </div>
      <div class="mobile-bottom-nav__dropdown" style="display:none">
        @if($this->hasPermission('mc-moshavere_setting'))
        <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed; opacity: 0.5;">برنامه‌ریزی <span
            class="soon-label">به زودی</span></a>
        @endif
        @if($this->hasPermission('mc-moshavere_waiting'))
        <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed; opacity: 0.5;">گزارش <span
            class="soon-label">به زودی</span></a>
        @endif
        @if($this->hasPermission('mc-mySpecialDays-counseling'))
        <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed; opacity: 0.5;">روزهای خاص <span
            class="soon-label">به زودی</span></a>
        @endif
        @if($this->hasPermission('consult-term.index'))
        <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed; opacity: 0.5;">قوانین <span
            class="soon-label">به زودی</span></a>
        @endif
      </div>
    </div>
    <!-- پروفایل -->
    <div class="mobile-bottom-nav__item" data-group="profile" data-has-submenu="true">
      <div class="mobile-bottom-nav__activebox">
        <div class="mobile-bottom-nav__icon">
          <svg viewBox="0 0 24 24">
            <path
              d="M12 12c2.7 0 8 1.34 8 4v2H4v-2c0-2.66 5.3-4 8-4zm0-2c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3z" />
          </svg>
        </div>
        <div class="mobile-bottom-nav__label">پروفایل</div>
      </div>
      <div class="mobile-bottom-nav__dropdown" style="display:none">
        @if($this->hasPermission('mc-edit-profile'))
        <a href="{{ route('mc-edit-profile') }}">ویرایش پروفایل</a>
        @endif
        @if($this->hasPermission('mc-edit-profile-security'))
        <a href="{{ route('mc-edit-profile-security') }}">امنیت</a>
        @endif
        @if($this->hasPermission('mc-my-performance'))
        <a href="{{ route('mc-my-performance') }}">عملکرد من</a>
        @endif
        @if($this->hasPermission('mc-subuser'))
        <a href="{{ route('mc-subuser') }}">کاربران زیرمجموعه</a>
        @endif
        @if($this->hasPermission('my-mc-appointments'))
        <a href="{{ route('my-mc-appointments') }}">نوبت‌های من</a>
        @endif
        @if($this->hasPermission('mc.panel.doctor-faqs.index'))
        <a href="{{ route('mc.panel.doctor-faqs.index') }}">سوالات متداول</a>
        @endif
        @if($this->hasPermission('mc-edit-profile-upgrade'))
        <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed; opacity: 0.5;">ارتقا حساب <span
            class="soon-label">به زودی</span></a>
        @endif
      </div>
    </div>
    <!-- ساعت کاری -->
    <div class="mobile-bottom-nav__item" data-group="workhours">
      <a href="{{ route('mc-workhours') }}"
        style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: inherit;">
        <div class="mobile-bottom-nav__activebox">
          <div class="mobile-bottom-nav__icon">
            <svg viewBox="0 0 24 24">
              <path
                d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8-2V4c0-1.1-.9-2-2-2H6C4.9 2 4 2.9 4 4v2C2.9 6 2 6.9 2 8v10c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm0 12H4V8h16v10z" />
            </svg>
          </div>
          <div class="mobile-bottom-nav__label">ساعت کاری</div>
        </div>
      </a>
    </div>
    <!-- سایر -->
    <div class="mobile-bottom-nav__item mobile-bottom-nav__item--other" data-group="other" data-has-submenu="true">
      <div class="mobile-bottom-nav__activebox">
        <div class="mobile-bottom-nav__icon">
          <svg viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" fill="#eee" />
            <text x="12" y="16" text-anchor="middle" font-size="16" fill="#888">...</text>
          </svg>
        </div>
        <div class="mobile-bottom-nav__label">سایر</div>
      </div>
      <div class="mobile-bottom-nav__dropdown" style="display:none">
        @if($this->hasPermission('mc-my-performance-chart'))
        <a href="{{ route('mc-my-performance-chart') }}">آمار و نمودار</a>
        @endif
        @if($this->hasPermission('mc-panel-tickets'))
        <a href="{{ route('mc-panel-tickets') }}">تیکت‌ها</a>
        @endif
        @if($this->hasPermission('mc.panel.send-message'))
        <a href="{{ route('mc.panel.send-message') }}">ارسال پیام</a>
        @endif
        <a href="#">صفحه گفتگو</a>
        @if($this->hasPermission('mc.panel.financial-reports.index'))
        <a href="{{ route('mc.panel.financial-reports.index') }}">گزارش مالی</a>
        @endif
        @if($this->hasPermission('mc-payment-setting'))
        <a href="{{ route('mc-payment-setting') }}">پرداخت</a>
        @endif
        @if($this->hasPermission('mc-wallet-charge'))
        <a href="{{ route('mc-wallet-charge') }}">شارژ کیف پول</a>
        @endif
        @if($this->hasPermission('mc.panel.doctor-services.index'))
        <a href="{{ route('mc.panel.doctor-services.index') }}">خدمات و بیمه</a>
        @endif
        <div style="border-top:1px solid #eee; margin:4px 0;"></div>
        <div style="font-size:12px; color:#888; padding:2px 16px 2px 0;">نسخه الکترونیک</div>
        @if($this->hasPermission('prescription.index'))
        <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed; opacity: 0.5;">پرونده پزشکی <span
            class="soon-label">به زودی</span></a>
        @endif
        @if($this->hasPermission('prescription.index'))
        <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed; opacity: 0.5;">نسخه‌های ثبت شده
          <span class="soon-label">به زودی</span></a>
        @endif
        @if($this->hasPermission('providers.index'))
        <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed; opacity: 0.5;">بیمه‌های من <span
            class="soon-label">به زودی</span></a>
        @endif
        @if($this->hasPermission('favorite.templates.index'))
        <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed; opacity: 0.5;">نسخه پراستفاده <span
            class="soon-label">به زودی</span></a>
        @endif
        @if($this->hasPermission('templates.favorite.service.index'))
        <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed; opacity: 0.5;">اقلام پراستفاده
          <span class="soon-label">به زودی</span></a>
        @endif
        @if($this->hasPermission('mc-patient-records'))
        <a href="javascript:void(0)" style="color: #6c757d; cursor: not-allowed; opacity: 0.5;">پرونده الکترونیک
          <span class="soon-label">به زودی</span></a>
        @endif
        <div style="border-top:1px solid #eee; margin:4px 0;"></div>
        @if($this->hasPermission('mc.panel.clinics.medical-documents'))
        <a href="{{ route('mc.panel.clinics.medical-documents') }}">مدارک من</a>
        @endif
        @if($this->hasPermission('mc-doctors.clinic.deposit'))
        <a href="{{ route('mc-doctors.clinic.deposit') }}">بیعانه</a>
        @endif
        @if($this->hasPermission('mc-secretary-management'))
        <a href="{{ route('mc-secretary-management') }}">مدیریت منشی‌ها</a>
        @endif
        @if($this->hasPermission('mc-secretary-permissions'))
        <a href="{{ route('mc-secretary-permissions') }}">دسترسی‌های منشی</a>
        @endif
        <div style="border-top:1px solid #eee; margin:4px 0;"></div>
        <div style="font-size:12px; color:#888; padding:2px 16px 2px 0;">مرکز درمانی من</div>
        @if($this->hasPermission('mc.panel.profile.edit'))
        <a href="{{ route('mc.panel.profile.edit') }}">ویرایش پروفایل</a>
        @endif
        @if($this->hasPermission('mc.panel.doctors.index'))
        <a href="{{ route('mc.panel.doctors.index') }}">مدیریت پزشکان</a>
        @endif
        @if($this->hasPermission('mc.panel.specialties.index'))
        <a href="{{ route('mc.panel.specialties.index') }}">مدیریت تخصص‌ها</a>
        @endif
        @if($this->hasPermission('mc.panel.services.index'))
        <a href="{{ route('mc.panel.services.index') }}">مدیریت خدمات</a>
        @endif
        @if($this->hasPermission('mc.panel.insurances.index'))
        <a href="{{ route('mc.panel.insurances.index') }}">مدیریت بیمه‌ها</a>
        @endif
      </div>
    </div>
  </div>
  <!-- Overlay for mobile submenu -->
  <div id="mobile-submenu-overlay" class="mobile-submenu-overlay">
    <div class="mobile-submenu-header">
      <button id="close-mobile-submenu" class="close-mobile-submenu-btn" aria-label="بستن">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2"
          stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18" />
          <line x1="6" y1="6" x2="18" y2="18" />
        </svg>
      </button>
    </div>
    <div class="mobile-submenu-list" id="mobile-submenu-list">
      <!-- زیرمنوها اینجا قرار می‌گیرند -->
    </div>
  </div>
  <style>
    .mobile-submenu-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: calc(100vh - 68px);
      /* ارتفاع منهای منوی پایین */
      background: rgba(255, 255, 255, 0.98);
      z-index: 20001;
      flex-direction: column;
      justify-content: flex-start;
      align-items: stretch;
      overflow-y: auto;
      transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
      backdrop-filter: blur(10px);
    }

    .mobile-submenu-overlay.active {
      display: flex;
      opacity: 1;
      animation: overlayFadeIn 0.3s ease-out;
    }

    @keyframes overlayFadeIn {
      from {
        opacity: 0;
        transform: translateY(10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .mobile-submenu-header {
      display: flex;
      justify-content: flex-end;
      align-items: center;
      padding: 16px 16px 0 16px;
      background: transparent;
      position: sticky;
      top: 0;
      z-index: 1;
    }

    .close-mobile-submenu-btn {
      background: rgba(240, 240, 240, 0.8);
      border: none;
      cursor: pointer;
      padding: 8px;
      margin: 0;
      outline: none;
      border-radius: 50%;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .close-mobile-submenu-btn:hover {
      background: rgba(220, 220, 220, 0.9);
      transform: scale(1.05);
    }

    .mobile-submenu-list {
      width: 100%;
      margin-top: 20px;
      padding: 0 24px 24px 24px;
      display: flex;
      flex-direction: column;
      gap: 12px;
      overflow-y: auto;
      flex: 1 1 0;
      max-height: calc(100vh - 68px - 64px);
      /* 64px تقریبی هدر */
    }

    .mobile-submenu-list a {
      display: block;
      background: #f7f7fa;
      border-radius: 12px;
      padding: 16px 18px;
      font-size: 16px;
      color: #222;
      text-decoration: none;
      box-shadow: 0 2px 8px rgba(25, 118, 210, 0.04);
      border: 1px solid #e0e0e0;
      transition: background 0.2s, color 0.2s;
      font-weight: 500;
      text-align: right;
    }

    .mobile-submenu-list a:hover {
      background: #e3f2fd;
      color: #1976d2;
    }

    @media (min-width: 769px) {
      .mobile-submenu-overlay {
        display: none !important;
      }
    }

    .mobile-bottom-nav__activebox {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 0;
      transition: background 0.2s, box-shadow 0.2s;
    }

    .mobile-bottom-nav__item.active .mobile-bottom-nav__activebox {
      background: linear-gradient(90deg, #a7c7ff 0%, #fbc2eb 100%);
      box-shadow: 0 2px 12px rgba(25, 118, 210, 0.08);
      border-radius: 16px;
      padding: 6px 8px 4px 8px;
    }

    .mobile-bottom-nav__item.active .mobile-bottom-nav__icon svg {
      fill: #1976d2;
      transform: scale(1.12);
      transition: fill 0.2s, transform 0.2s;
    }

    .mobile-bottom-nav__item.active .mobile-bottom-nav__label {
      color: #1976d2;
      font-weight: bold;
    }
  </style>
  <script>
    (function() {
      let navItems;
      let lastOpen = null;
      let overlay = document.getElementById('mobile-submenu-overlay');
      let closeBtn = document.getElementById('close-mobile-submenu');
      let submenuList = document.getElementById('mobile-submenu-list');

      function closeOverlay() {
        overlay.classList.remove('active');
        submenuList.innerHTML = '';
        document.body.style.overflow = '';
        // حذف کلاس active از همه آیتم‌ها
        document.querySelectorAll('.mobile-bottom-nav__item.active').forEach(i => i.classList.remove('active'));
      }

      function openOverlayWithSubmenu(submenuHtml, parentItem) {
        submenuList.innerHTML = submenuHtml;
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        // حذف active از همه و اضافه کردن به آیتم فعلی
        document.querySelectorAll('.mobile-bottom-nav__item.active').forEach(i => i.classList.remove('active'));
        if (parentItem) parentItem.classList.add('active');
      }

      function setupMobileNavOverlay() {
        navItems = document.querySelectorAll('.mobile-bottom-nav__item[data-has-submenu="true"]');
        navItems.forEach(item => {
          item.onclick = function(e) {
            if (window.innerWidth > 768) return;
            let dropdown = item.querySelector('.mobile-bottom-nav__dropdown');
            if (dropdown) {
              let links = Array.from(dropdown.querySelectorAll('a')).filter(a => a.href && a.href !==
                'javascript:void(0)');
              if (links.length === 1) {
                window.location.href = links[0].href;
                return;
              }
              let html = dropdown.innerHTML;
              openOverlayWithSubmenu(html, item);
            }
          };
        });
        if (closeBtn) {
          closeBtn.onclick = function() {
            closeOverlay();
          };
        }
        // بستن با کلیک روی بیرون overlay
        overlay.addEventListener('click', function(e) {
          if (e.target === overlay) {
            closeOverlay();
          }
        });
      }
      document.addEventListener('DOMContentLoaded', function() {
        setupMobileNavOverlay();
      });
      window.addEventListener('resize', function() {
        if (window.innerWidth <= 768) {
          setupMobileNavOverlay();
        }
      });
      document.addEventListener('livewire:update', function() {
        setupMobileNavOverlay();
      });
    })();
  </script>
</div>

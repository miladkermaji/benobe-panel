<div class="sidebar__nav border-top border-left">
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

 <ul class="mt-65" id="mt-65">
  @if (Auth::guard('manager')->check())
       <!-- داشبورد (بدون تغییر) -->
       <li class="item-li i-dashboard {{ Request::routeIs('admin-panel') ? 'is-active' : '' }}">
        <a href="{{ route('admin-panel') }}">داشبورد</a>
       </li>

       <!-- ابزار (بدون تغییر) -->
       <li class="item-li i-courses">
        <a href="#" class="d-flex justify-content-between w-100 align-items-center">
         ابزار
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
         <li class="item-li {{ Request::routeIs('admin.panel.tools.file-manager') ? 'is-active' : '' }}">
          <a href="{{ route('admin.panel.tools.file-manager') }}">مدیریت فایل</a>
         </li>
         <li class="item-li {{ Request::routeIs('admin.tools.data-migration.index') ? 'is-active' : '' }}">
          <a href="{{ route('admin.tools.data-migration.index') }}">انتقال داده‌ها</a>
         </li>
         <li class="item-li {{ Request::routeIs('admin.panel.tools.payment_gateways.index') ? 'is-active' : '' }}">
          <a href="{{ route('admin.panel.tools.payment_gateways.index') }}">درگاه پرداخت</a>
         </li>
         <li class="item-li {{ Request::routeIs('admin.panel.tools.sms-gateways.index') ? 'is-active' : '' }}">
          <a href="{{ route('admin.panel.tools.sms-gateways.index') }}">پیامک</a>
         </li>
         <li class="item-li {{ Request::routeIs('admin.panel.tools.telescope') ? 'is-active' : '' }}">
          <a href="{{ route('admin.panel.tools.telescope') }}">تلسکوپ</a>
         </li>
         <li class="item-li {{ Request::routeIs('admin.panel.tools.redirects.index') ? 'is-active' : '' }}">
          <a href="{{ route('admin.panel.tools.redirects.index') }}">ابزار ریدایرکت</a>
         </li>
         <li class="item-li {{ Request::routeIs('admin.tools.sitemap.index') ? 'is-active' : '' }}">
          <a href="{{ route('admin.tools.sitemap.index') }}">نقشه سایت</a>
         </li>
         <li class="item-li {{ Request::routeIs('admin.tools.page-builder.index') ? 'is-active' : '' }}">
          <a href="{{ route('admin.tools.page-builder.index') }}">صفحه‌ساز</a>
         </li>
         <li class="item-li {{ Request::routeIs('admin.panel.tools.mail-template.index') ? 'is-active' : '' }}">
          <a href="{{ route('admin.panel.tools.mail-template.index') }}">قالب ایمیل</a>
         </li>
         <li class="item-li {{ Request::routeIs('admin.tools.news-latter.index') ? 'is-active' : '' }}">
          <a href="{{ route('admin.tools.news-latter.index') }}">خبرنامه</a>
         </li>
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
         <li class="item-li"><a href="#">لیست کاربران</a></li>
         <li class="item-li"><a href="#">افزودن کاربر</a></li>
         <li class="item-li"><a href="#">مدیریت نقش‌ها</a></li>
         <li class="item-li"><a href="#">تنظیمات دسترسی</a></li>
         <li class="item-li"><a href="#">کاربران غیرفعال</a></li>
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
         <li class="item-li"><a href="#">لیست پزشکان</a></li>
         <li class="item-li"><a href="#">افزودن پزشک</a></li>
         <li class="item-li"><a href="#">تأیید مدارک</a></li>
         <li class="item-li"><a href="#">مدیریت تخصص‌ها</a></li>
         <li class="item-li"><a href="#">برنامه کاری پزشکان</a></li>
         <li class="item-li"><a href="#">پروفایل پزشکان</a></li>
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
         <li class="item-li"><a href="#">نوبت‌های فعال</a></li>
         <li class="item-li"><a href="#">نوبت‌های لغو شده</a></li>
         <li class="item-li"><a href="#">نوبت‌های در انتظار تأیید</a></li>
         <li class="item-li"><a href="#">تنظیمات نوبت‌دهی</a></li>
         <li class="item-li"><a href="#">تقویم نوبت‌ها</a></li>
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
         <li class="item-li"><a href="#">لیست بیماران</a></li>
         <li class="item-li"><a href="#">افزودن بیمار</a></li>
         <li class="item-li"><a href="#">پرونده پزشکی</a></li>
         <li class="item-li"><a href="#">تاریخچه نوبت‌ها</a></li>
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
         <li class="item-li"><a href="#">صورت‌حساب‌ها</a></li>
         <li class="item-li"><a href="#">پرداخت‌های پزشکان</a></li>
         <li class="item-li"><a href="#">تنظیمات مالی</a></li>
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
         <li class="item-li"><a href="#">تنظیمات عمومی</a></li>
         <li class="item-li"><a href="#">مدیریت صفحات</a></li>
         <li class="item-li"><a href="#">تنظیمات SEO</a></li>
         <li class="item-li"><a href="#">تنظیمات اعلان‌ها</a></li>
         <li class="item-li"><a href="#">پشتیبان‌گیری</a></li>
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
         <li class="item-li"><a href="#">گزارش عملکرد پزشکان</a></li>
         <li class="item-li"><a href="#">آمار بازدید سایت</a></li>
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
         <li class="item-li"><a href="#">تیکت‌های پشتیبانی</a></li>
         <li class="item-li"><a href="#">ارسال تیکت جدید</a></li>
         <li class="item-li"><a href="#">سوالات متداول</a></li>
         <li class="item-li"><a href="#">راهنمای سیستم</a></li>
        </ul>
       </li>

       <!-- خروج -->
       <li class="item-li i-exit">
        <a href="{{ route('admin.auth.logout') }}" class="logout-sidebar">خروج</a>
       </li>
  @endif
 </ul>

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

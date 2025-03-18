<div class="justify-content-center align-items-center">
 <div class="col-md-6 login-container position-relative">
  <div class="login-card custom-rounded custom-shadow p-7">
   <div class="logo-wrapper w-100 d-flex justify-content-center">
    <img class="position-absolute mt-3 cursor-pointer" onclick="location.href='/'" width="85px"
     src="{{ asset('app-assets/logos/benobe.svg') }}" alt="لوگوی به نوبه">
   </div>
   <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
    <div class="d-flex align-items-center">
     <div class="rounded-circle bg-primary me-2" style="width: 16px; height: 16px;"></div>
     <span class="text-custom-gray px-1 fw-bold">ورود کاربر</span>
    </div>
   </div>
   <form wire:submit.prevent="loginRegister">
    <div class="mb-3">
     <div class="d-flex align-items-center mb-2">
      <img src="{{ asset('admin-assets/login/images/phone.svg') }}" alt="آیکون تلفن" class="me-2">
      <label class="text-custom-gray">شماره موبایل</label>
     </div>
     <input wire:model="mobile" dir="ltr"
      class="form-control custom-rounded custom-shadow h-50 @error('mobile') is-invalid @enderror" type="text"
      placeholder="09181234567" maxlength="11" autofocus>
     @error('mobile')
      <div class="invalid-feedback">{{ $message }}</div>
     @enderror
    </div>
    <a href="#"
     wire:click.prevent="$dispatch('navigateTo', { url: '{{ route('admin.auth.login-user-pass-form') }}' })"
     class="text-primary text-decoration-none mb-3 d-block fw-bold">
     ورود با نام کاربری و کلمه عبور
    </a>
    <button type="submit" wire:loading.attr="disabled"
     class="btn btn-primary w-100 custom-gradient custom-rounded py-2 d-flex justify-content-center">
     <span wire:loading.remove wire:target="loginRegister">ادامه</span>
     <div wire:loading wire:target="loginRegister" class="loader"></div>
    </button>
   </form>
  </div>
 </div>
</div>

@push('scripts')
 <script>
  Livewire.on('rateLimitExceeded', (data) => {
   let remainingTime = Math.round(data.remainingTime);
   Swal.fire({
    icon: 'error',
    title: 'تلاش بیش از حد',
    html: `لطفاً <b id="remaining-time">${formatTime(remainingTime)}</b> دیگر صبر کنید.`,
    timer: remainingTime * 1000,
    timerProgressBar: true,
    didOpen: () => {
     const remainingTimeElement = document.getElementById('remaining-time');
     let timerInterval = setInterval(() => {
      remainingTime--;
      remainingTimeElement.innerHTML = formatTime(remainingTime);
      if (remainingTime <= 0) clearInterval(timerInterval);
     }, 1000);
    },
   });
  });

  Livewire.on('otpSent', (data) => {
   toastr.success('کد تأیید با موفقیت ارسال شد');
   localStorage.removeItem('otpTimerData');
  });

  Livewire.on('navigateTo', (event) => {
   window.Livewire.navigate(event.url);
  });

  function formatTime(seconds) {
   if (isNaN(seconds) || seconds < 0) return '0 دقیقه و 0 ثانیه';
   const minutes = Math.floor(seconds / 60);
   const remainingSeconds = seconds % 60;
   return `${minutes} دقیقه و ${remainingSeconds} ثانیه`;
  }

  document.addEventListener('livewire:initialized', () => {
   document.querySelector('input[wire\\:model="mobile"]').focus();
  });
 </script>
@endpush

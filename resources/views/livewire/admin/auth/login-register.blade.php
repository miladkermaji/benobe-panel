<div class="justify-content-center align-items-center">
  <div class="col-md-6 login-container position-relative">
    <div class="login-card custom-rounded custom-shadow p-7">
      <div class="logo-wrapper w-100 d-flex justify-content-center">
        <img class="position-absolute cursor-pointer" onclick="location.href='/'" width="85px"
          src="{{ asset('app-assets/logos/benobe.svg') }}" alt="لوگوی به نوبه">
      </div>
      <div class="d-flex justify-content-between align-items-center mb-3 mt-5">
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
      let timerInterval; // تعریف متغیر interval در scope بالاتر

      Swal.fire({
        icon: 'error',
        title: 'تلاش بیش از حد',
        html: `<span id="remaining-time" style="font-weight: bold;">لطفاً ${formatConditionalTime(remainingTime)} دیگر تلاش کنید</span>`,
        timer: remainingTime * 1000,
        timerProgressBar: true,
        showConfirmButton: true,
        confirmButtonText: 'باشه',
        allowOutsideClick: false,
        didOpen: () => {
          const remainingTimeElement = document.getElementById('remaining-time');
          timerInterval = setInterval(() => {
            remainingTime--;
            if (remainingTime >= 0) {
              remainingTimeElement.innerHTML = `لطفاً ${formatConditionalTime(remainingTime)} دیگر تلاش کنید`;
              // تغییر رنگ بر اساس زمان باقی‌مونده
              if (remainingTime > 180) { // بیشتر از 3 دقیقه
                remainingTimeElement.style.color = '#16a34a'; // سبز
              } else if (remainingTime > 60) { // بین 1 تا 3 دقیقه
                remainingTimeElement.style.color = '#f59e0b'; // زرد
              } else { // کمتر از 1 دقیقه
                remainingTimeElement.style.color = '#dc2626'; // قرمز
              }
            }
            if (remainingTime <= 0) {
              clearInterval(timerInterval);
              console.log('Timer cleared automatically when time reached zero');
            }
          }, 1000);
        },
        willClose: () => {
          if (timerInterval) {
            clearInterval(timerInterval);
            console.log('Timer cleared on SweetAlert close');
          }
        }
      });
    });

    Livewire.on('otpSent', (data) => {
      toastr.success('کد تأیید با موفقیت ارسال شد');
      localStorage.removeItem('otpTimerData');
    });

    Livewire.on('navigateTo', (event) => {
      window.Livewire.navigate(event.url);
    });

    // تابع جدید برای فرمت شرطی زمان
    function formatConditionalTime(seconds) {
      if (isNaN(seconds) || seconds < 0) return '0 ثانیه';
      const hours = Math.floor(seconds / 3600);
      const minutes = Math.floor((seconds % 3600) / 60);
      const secs = seconds % 60;

      if (hours > 0) {
        return `${hours} ساعت ${minutes} دقیقه ${secs} ثانیه`;
      } else if (minutes > 0) {
        return `${minutes} دقیقه ${secs} ثانیه`;
      } else {
        return `${secs} ثانیه`;
      }
    }

    document.addEventListener('livewire:initialized', () => {
      document.querySelector('input[wire\\:model="mobile"]').focus();
    });
  </script>
@endpush
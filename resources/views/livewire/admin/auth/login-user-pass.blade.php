<div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
  style="background-color: #f8f9fa;">
  <div class="col-11 col-sm-9 col-md-7 col-lg-5 col-xl-4 mx-auto d-flex justify-content-center">
    <div class="login-card custom-rounded custom-shadow p-4 p-md-7 bg-white w-100">
      <div class="logo-wrapper w-100 d-flex justify-content-center mb-4">
        <img class="cursor-pointer" onclick="location.href='/'" width="100px"
          src="{{ asset('app-assets/logos/benobe.svg') }}" alt="لوگوی به نوبه">
      </div>
      <div class="text-center mb-4 d-none">
        <h2 class="text-primary fw-bold mb-2" style="font-weight: 700 !important;">پنل مدیریت به نوبه</h2>
        <p class="text-muted fw-bold">به پنل مدیریت به نوبه خوش آمدید</p>
      </div>
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
          <div class="rounded-circle bg-primary me-2" style="width: 16px; height: 16px;"></div>
          <span class="text-custom-gray px-1">کلمه عبور</span>
        </div>
        <a href="#" wire:click.prevent="goBack" class="back-link text-primary d-flex align-items-center"
          style="cursor: pointer;">
          <span class="ms-2">بازگشت</span>
          <img src="{{ asset('admin-assets/login/images/back.svg') }}" alt="آیکون بازگشت" class="img-fluid"
            style="max-width: 24px;">
        </a>
      </div>
      <form wire:submit.prevent="loginWithMobilePass" class="login-user-pass-form">
        <div class="mb-3">
          <div class="d-flex align-items-center mb-2">
            <img src="{{ asset('admin-assets/login/images/password.svg') }}" alt="آیکون رمز" class="me-2">
            <label class="text-custom-gray">رمز عبور</label>
          </div>
          <div class="position-relative">
            <input wire:model="password"
              class="form-control  custom-shadow h-50 text-end @error('password') is-invalid @enderror" type="password"
              placeholder="رمز عبور خود را وارد کنید" id="password-input">
            <img src="{{ asset('admin-assets/login/images/visible.svg') }}" alt="نمایش رمز" class="password-toggle"
              onclick="togglePasswordVisibility('password-input')">
          </div>
          @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <button type="submit" wire:loading.attr="disabled" wire:target="loginWithMobilePass"
          class="btn  w-100 custom-gradient custom-rounded h-50 d-flex justify-content-center">
          <span wire:loading.remove wire:target="loginWithMobilePass">ادامه</span>
          <div wire:loading wire:target="loginWithMobilePass" class="loader"></div>
        </button>
      </form>
    </div>
  </div>
</div>

@push('scripts')
  <script>
    function togglePasswordVisibility(inputId) {
      const input = document.getElementById(inputId);
      const icon = input.nextElementSibling;
      if (input.type === 'password') {
        input.type = 'text';
        icon.style.opacity = '0.7';
      } else {
        input.type = 'password';
        icon.style.opacity = '1';
      }
    }

    Livewire.on('rateLimitExceeded', (data) => {
      let remainingTime = Math.round(data.remainingTime);
      if (remainingTime <= 0) {
        return; // اگر زمان قفل صفر یا منفی است، پیام نمایش داده نشود
      }

      let timerInterval;
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
              remainingTimeElement.innerHTML =
                `لطفاً ${formatConditionalTime(remainingTime)} دیگر تلاش کنید`;
              remainingTimeElement.style.color = remainingTime > 180 ? '#16a34a' : remainingTime > 60 ?
                '#f59e0b' : '#dc2626';
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
    Livewire.on('password-error', () => {
      toastr.error('کلمه عبور اشتباه است.');
    });
    Livewire.on('otpAlreadySent', (data) => {
      toastr.info(data.message);
    })
    Livewire.on('loginSuccess', () => {
      toastr.success('با موفقیت وارد شدید');
      localStorage.removeItem('rateLimitTimerData');
    });

    Livewire.on('navigateTo', (event) => {
      window.Livewire.navigate(event.url);
    });
    document.addEventListener('DOMContentLoaded', () => {
      if (typeof toastr !== 'undefined') {
        toastr.options = {
          timeOut: 10000,
          progressBar: true,
          positionClass: 'toast-top-right',
          preventDuplicates: true, // جلوگیری از نمایش توسترهای تکراری
          newestOnTop: true,
          maxOpened: 1, // فقط یک توستر در هر لحظه
          closeButton: false,

        };

      }
      toastr.options.rtl = true;
    });
    // تابع فرمت زمان مشابه سیستم OTP
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
      const input = document.querySelector('input[wire\\:model="password"]');
      if (input) input.focus();
    });
  </script>
@endpush

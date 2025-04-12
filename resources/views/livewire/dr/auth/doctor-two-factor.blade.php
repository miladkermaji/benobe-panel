<div class="justify-content-center align-items-center">
  <div class="col-md-6 login-container position-relative">
    <div class="login-card custom-rounded custom-shadow p-7">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
          <div class="rounded-circle bg-primary me-2" style="width: 16px; height: 16px;"></div>
          <span class="text-custom-gray px-1">ورود کاربر</span>
        </div>
        <a href="#" wire:click.prevent="goBack" class="back-link text-primary d-flex align-items-center"
          style="cursor: pointer;">
          <span class="ms-2">بازگشت</span>
          <img src="{{ asset('dr-assets/login/images/back.svg') }}" alt="آیکون بازگشت" class="img-fluid"
            style="max-width: 24px;">
        </a>
      </div>
      <form wire:submit.prevent="twoFactorCheck">
        <div class="mb-3">
          <div class="d-flex align-items-center mb-2">
            <img src="{{ asset('dr-assets/login/images/password.svg') }}" alt="آیکون رمز" class="me-2">
            <label class="text-custom-gray">رمز دو عاملی</label>
          </div>
          <div class="position-relative d-flex align-items-center">
            <input wire:model="twoFactorSecret" dir="rtl"
              class="form-control custom-rounded custom-shadow h-50 text-end @error('twoFactorSecret') is-invalid @enderror"
              type="password" placeholder="کد دو عاملی خود را وارد کنید" id="two-factor-input" autofocus>
            <img src="{{ asset('dr-assets/login/images/visible.svg') }}" alt="نمایش رمز" class="password-toggle ms-2"
              style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%);"
              onclick="togglePasswordVisibility('two-factor-input')">
          </div>
          @error('twoFactorSecret')
            <div class="invalid-feedback" style="display: block; margin-top: 5px;">{{ $message }}</div>
          @enderror
        </div>
        <button type="submit" wire:loading.attr="disabled" wire:target="twoFactorCheck"
          class="btn btn-primary w-100 custom-gradient custom-rounded py-2 d-flex justify-content-center">
          <span wire:loading.remove wire:target="twoFactorCheck">ادامه</span>
          <div wire:loading wire:target="twoFactorCheck" class="loader"></div>
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
              remainingTimeElement.innerHTML = `لطفاً ${formatConditionalTime(remainingTime)} دیگر تلاش کنید`;
              if (remainingTime > 180) {
                remainingTimeElement.style.color = '#16a34a'; // سبز
              } else if (remainingTime > 60) {
                remainingTimeElement.style.color = '#f59e0b'; // زرد
              } else {
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

    Livewire.on('loginSuccess', () => {
      toastr.success('با موفقیت وارد شدید');
    });

    Livewire.on('navigateTo', (event) => {
      window.Livewire.navigate(event.url);
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
      document.querySelector('input[wire\\:model="twoFactorSecret"]').focus();
    });
  </script>
@endpush
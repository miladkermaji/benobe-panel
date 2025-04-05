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
          <img src="{{ asset('admin-assets/login/images/back.svg') }}" alt="آیکون بازگشت" class="img-fluid"
            style="max-width: 24px;">
        </a>
      </div>
      <form wire:submit.prevent="loginConfirm">
        <div class="d-flex justify-content-between mb-3" dir="rtl">
          @for ($i = 0; $i < 4; $i++)
            <input wire:model="otpCode.{{ $i }}" type="text" maxlength="1"
              class="form-control otp-input text-center custom-rounded border">
          @endfor
        </div>
        @error('otpCode')
          <div class="invalid-feedback otp-error" style="display: block; text-align: center;" id="otp-error">
            {{ $message }}</div>
        @enderror
        <button type="submit" wire:loading.attr="disabled" wire:target="loginConfirm"
          class="btn btn-primary w-100 custom-gradient custom-rounded py-2 d-flex justify-content-center">
          <span wire:loading.remove wire:target="loginConfirm">ادامه</span>
          <div wire:loading wire:target="loginConfirm" class="loader"></div>
        </button>

        <div id="progress-bar-container" class="mt-2" style="display: none;" wire:ignore>
          <div id="progress-bar" class="progress-bar"></div>
        </div>

        <section id="resend-otp" class="mt-2 text-center" style="{{ $showResendButton ? '' : 'display: none;' }}">
          <a href="#" wire:click.prevent="resendOtp" wire:loading.remove wire:target="resendOtp"
            class="text-decoration-none text-primary fw-bold">دریافت مجدد کد تأیید</a>
          <span wire:loading wire:target="resendOtp" class="spinner-border spinner-border-sm" role="status"></span>
        </section>
        <section style="font-size: 14px" class="text-danger fw-bold fs-6 mt-3 text-center" id="timer" wire:ignore>
          {{ $remainingTime > 0 ? 'زمان باقی‌مانده: ' . floor($remainingTime / 60000) . ' دقیقه و ' . floor(($remainingTime % 60000) / 1000) . ' ثانیه' : '' }}
        </section>
      </form>
    </div>
  </div>
</div>

@push('scripts')
  <script>
    window.timerState = window.timerState || {
      interval: null,
      rateLimitTimerInterval: null,
      otpCountDownDate: null,
      isTimerRunning: false,
      lastPercentage: 0,
      currentToken: null
    };

    function startTimer(countDownDate, token) {
      if (window.timerState.interval) {
        clearInterval(window.timerState.interval);
      }

      const storedData = JSON.parse(localStorage.getItem('otpTimerData') || '{}');
      const storedCountDownDate = storedData.countDownDate;
      const storedToken = storedData.token;
      const now = new Date().getTime();

      if (!storedCountDownDate || storedToken !== token || storedCountDownDate <= now) {
        window.timerState.otpCountDownDate = countDownDate || (new Date().getTime() + 120000);
        window.timerState.isTimerRunning = true;
        window.timerState.lastPercentage = 100;
        window.timerState.currentToken = token;
        localStorage.setItem('otpTimerData', JSON.stringify({
          countDownDate: window.timerState.otpCountDownDate,
          token: window.timerState.currentToken
        }));
      } else {
        window.timerState.otpCountDownDate = Number(storedCountDownDate);
        window.timerState.currentToken = storedToken;
        window.timerState.isTimerRunning = true;
      }

      const totalDuration = 120000; // 2 دقیقه
      const timerElement = document.getElementById('timer');
      const progressBarContainer = document.getElementById('progress-bar-container');
      const progressBar = document.getElementById('progress-bar');
      const resendSection = document.getElementById('resend-otp');

      if (!timerElement || !progressBarContainer || !progressBar || !resendSection) {
        return;
      }

      if (window.timerState.isTimerRunning) {
        timerElement.classList.remove('d-none');
        progressBarContainer.style.display = 'block';
        resendSection.style.display = 'none';
        progressBar.style.width = window.timerState.lastPercentage + '%';
        progressBar.style.backgroundColor = window.timerState.lastPercentage > 50 ? '#28a745' : (window.timerState
          .lastPercentage > 20 ? '#ffc107' : '#dc3545');
      }

      window.timerState.interval = setInterval(() => {
        const now = new Date().getTime();
        const distance = window.timerState.otpCountDownDate - now;

        if (isNaN(distance) || window.timerState.otpCountDownDate === null) {
          console.error('فاصله نامعتبر:', {
            countDownDate: window.timerState.otpCountDownDate,
            now
          });
          clearInterval(window.timerState.interval);
          window.timerState.interval = null;
          window.timerState.isTimerRunning = false;
          localStorage.removeItem('otpTimerData');
          return;
        }

        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        const percentage = Math.max(0, (distance / totalDuration) * 100);

        window.timerState.lastPercentage = percentage;
        progressBar.style.width = percentage + '%';
        progressBar.style.backgroundColor = percentage > 50 ? '#28a745' : (percentage > 20 ? '#ffc107' : '#dc3545');

        if (distance <= 0) {
          clearInterval(window.timerState.interval);
          window.timerState.interval = null;
          window.timerState.isTimerRunning = false;
          timerElement.innerHTML = '';
          timerElement.classList.add('d-none');
          progressBarContainer.style.display = 'none';
          resendSection.style.display = 'block';
          localStorage.removeItem('otpTimerData');
          Livewire.dispatch('updateShowResendButton', {
            show: true
          });
        } else {
          timerElement.innerHTML = `زمان باقی‌مانده: ${minutes} دقیقه و ${seconds} ثانیه`;
        }
      }, 1000);
    }

    function setupOtpInputs() {
      const inputs = document.querySelectorAll('.otp-input');
      if (inputs.length > 0) {
        inputs[3].focus();
        inputs.forEach((input, index) => {
          input.addEventListener('input', (e) => {
            const value = e.target.value.replace(/[^0-9]/g, '');
            e.target.value = value;
            if (value.length === 1 && index > 0) {
              inputs[index - 1].focus();
            }
          });
          input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && e.target.value.length === 0 && index < inputs.length - 1) {
              inputs[index + 1].focus();
            }
          });
          input.addEventListener('focus', () => {
            input.select();
          });
        });
      }
    }

    document.addEventListener('livewire:navigated', () => {
      setupOtpInputs();
      if (window.timerState.otpCountDownDate && window.timerState.isTimerRunning) {
        startTimer(window.timerState.otpCountDownDate, window.timerState.currentToken);
      }
    });

    Livewire.on('initTimer', (data) => {
      const storedData = JSON.parse(localStorage.getItem('otpTimerData') || '{}');
      if (!storedData.countDownDate || storedData.token !== data.token) {
        startTimer(data.countDownDate, data.token);
      } else {
        startTimer(storedData.countDownDate, storedData.token);
      }
      setupOtpInputs();
      const resendSection = document.getElementById('resend-otp');
      if (resendSection) {
        resendSection.style.display = data.showResendButton ? 'block' : 'none';
      }
    });

    Livewire.on('otpResent', (data) => {
      if (!window.otpResentToastShown) {
        toastr.success(data.message);
        window.otpResentToastShown = true;
      }
      startTimer(data.countDownDate, data.token);
      const resendSection = document.getElementById('resend-otp');
      if (resendSection) {
        resendSection.style.display = 'none';
      }
      Livewire.dispatch('updateShowResendButton', {
        show: false
      });
    });

    Livewire.on('otpExpired', () => {
      toastr.error('توکن منقضی شده است');
      window.Livewire.navigate('{{ route('admin.auth.login-register-form') }}');
      localStorage.removeItem('otpTimerData');
      if (window.timerState.interval) {
        clearInterval(window.timerState.interval);
        window.timerState.interval = null;
      }
      window.timerState.isTimerRunning = false;
    });

    Livewire.on('rateLimitExceeded', (data) => {
      let remainingTime = Math.round(data.remainingTime);
      const errorElement = document.getElementById('otp-error');
      if (errorElement) {
        errorElement.innerHTML =
          `شما بیش از حد تلاش کرده‌اید. لطفاً <span id="live-error-time">${formatTime(remainingTime)}</span> صبر کنید.`;
        let errorTimer = remainingTime;
        const errorInterval = setInterval(() => {
          errorTimer--;
          const liveErrorTime = document.getElementById('live-error-time');
          if (liveErrorTime) {
            liveErrorTime.innerHTML = formatTime(errorTimer);
          }
          if (errorTimer <= 0) clearInterval(errorInterval);
        }, 1000);
      }

      Swal.fire({
        icon: 'error',
        title: 'تلاش بیش از حد',
        html: `لطفاً <b id="remaining-time">${formatTime(remainingTime)}</b> دیگر صبر کنید.`,
        timer: remainingTime * 1000,
        timerProgressBar: true,
        showConfirmButton: true,
        confirmButtonText: 'باشه',
        showCancelButton: true,
        cancelButtonText: 'لغو',
        allowOutsideClick: false,
        didOpen: () => {
          const remainingTimeElement = document.getElementById('remaining-time');
          if (!remainingTimeElement) return;

          let liveTimer = remainingTime;
          if (window.timerState.rateLimitTimerInterval) {
            clearInterval(window.timerState.rateLimitTimerInterval);
          }
          window.timerState.rateLimitTimerInterval = setInterval(() => {
            liveTimer--;
            remainingTimeElement.innerHTML = formatTime(liveTimer);
            if (liveTimer <= 0) {
              clearInterval(window.timerState.rateLimitTimerInterval);
              window.timerState.rateLimitTimerInterval = null;
              Swal.close();
            }
          }, 1000);
        },
        willClose: () => {
          if (window.timerState.rateLimitTimerInterval) {
            clearInterval(window.timerState.rateLimitTimerInterval);
            window.timerState.rateLimitTimerInterval = null;
          }
        }
      });

      if (window.timerState.otpCountDownDate && window.timerState.isTimerRunning) {
        startTimer(window.timerState.otpCountDownDate, window.timerState.currentToken);
      }
    });

    Livewire.on('loginSuccess', () => {
      toastr.success('با موفقیت وارد شدید');
      localStorage.removeItem('otpTimerData');
      if (window.timerState.interval) {
        clearInterval(window.timerState.interval);
        window.timerState.interval = null;
      }
      window.timerState.isTimerRunning = false;
    });

    Livewire.on('navigateTo', (event) => {
      window.Livewire.navigate(event.url);
    });

    Livewire.on('updateShowResendButton', (data) => {
      const resendSection = document.getElementById('resend-otp');
      if (resendSection && !window.timerState.isTimerRunning) {
        resendSection.style.display = data.show ? 'block' : 'none';
      }
    });

    function formatTime(seconds) {
      if (isNaN(seconds) || seconds < 0) return '0 دقیقه';
      const minutes = Math.floor(seconds / 60);
      if (minutes > 59) {
        const hours = Math.floor(minutes / 60);
        return `${hours} ساعت`;
      }
      return `${minutes} دقیقه`;
    }

    document.addEventListener('DOMContentLoaded', () => {
      const storedData = JSON.parse(localStorage.getItem('otpTimerData') || '{}');
      const storedCountDownDate = storedData.countDownDate;
      const storedToken = storedData.token;
      const now = new Date().getTime();

      if (storedCountDownDate && storedCountDownDate > now) {
        window.timerState.otpCountDownDate = Number(storedCountDownDate);
        window.timerState.currentToken = storedToken;
        window.timerState.isTimerRunning = true;
        startTimer(window.timerState.otpCountDownDate, window.timerState.currentToken);
      }
      setupOtpInputs();
    });

    document.addEventListener('livewire:init', () => {
      const timerElement = document.getElementById('timer');
      const progressBarContainer = document.getElementById('progress-bar-container');
      if (timerElement) {
        timerElement.setAttribute('wire:ignore', '');
      }
      if (progressBarContainer) {
        progressBarContainer.setAttribute('wire:ignore', '');
      }
      if (window.timerState.otpCountDownDate && window.timerState.isTimerRunning) {
        startTimer(window.timerState.otpCountDownDate, window.timerState.currentToken);
      }
    });
  </script>
@endpush

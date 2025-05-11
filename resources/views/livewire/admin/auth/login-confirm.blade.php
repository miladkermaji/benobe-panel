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
      <form wire:submit.prevent="loginConfirm" class="login-confirm-form">
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
          class="btn my-btn-primary w-100 custom-gradient custom-rounded py-2 d-flex justify-content-center">
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
      otpInterval: null,
      rateLimitInterval: null,
      otpCountDownDate: null,
      rateLimitCountDownDate: null,
      isOtpTimerRunning: false,
      isRateLimitTimerRunning: false,
      lastOtpPercentage: 0,
      currentToken: null
    };

    function startOtpTimer(countDownDate, token) {
      if (window.timerState.otpInterval) clearInterval(window.timerState.otpInterval);

      const storedData = JSON.parse(localStorage.getItem('otpTimerData') || '{}');
      const storedCountDownDate = storedData.countDownDate;
      const storedToken = storedData.token;
      const now = new Date().getTime();

      if (!storedCountDownDate || storedToken !== token || storedCountDownDate <= now) {
        window.timerState.otpCountDownDate = countDownDate || (new Date().getTime() + 120000);
        window.timerState.currentToken = token;
        localStorage.setItem('otpTimerData', JSON.stringify({
          countDownDate: window.timerState.otpCountDownDate,
          token: window.timerState.currentToken
        }));
      } else {
        window.timerState.otpCountDownDate = Number(storedCountDownDate);
        window.timerState.currentToken = storedToken;
      }
      window.timerState.isOtpTimerRunning = true;
      window.timerState.lastOtpPercentage = 100;

      const totalDuration = 120000; // 2 دقیقه
      const timerElement = document.getElementById('timer');
      const progressBarContainer = document.getElementById('progress-bar-container');
      const progressBar = document.getElementById('progress-bar');
      const resendSection = document.getElementById('resend-otp');

      if (!timerElement || !progressBarContainer || !progressBar || !resendSection) return;

      timerElement.classList.remove('d-none');
      progressBarContainer.style.display = 'block';
      resendSection.style.display = 'none';
      progressBar.style.width = window.timerState.lastOtpPercentage + '%';
      progressBar.style.backgroundColor = window.timerState.lastOtpPercentage > 50 ? '#28a745' : (window.timerState
        .lastOtpPercentage > 20 ? '#ffc107' : '#dc3545');

      window.timerState.otpInterval = setInterval(() => {
        const now = new Date().getTime();
        const distance = window.timerState.otpCountDownDate - now;

        if (isNaN(distance) || window.timerState.otpCountDownDate === null) {
          clearInterval(window.timerState.otpInterval);
          window.timerState.isOtpTimerRunning = false;
          localStorage.removeItem('otpTimerData');
          return;
        }

        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        const percentage = Math.max(0, (distance / totalDuration) * 100);

        window.timerState.lastOtpPercentage = percentage;
        progressBar.style.width = percentage + '%';
        progressBar.style.backgroundColor = percentage > 50 ? '#28a745' : (percentage > 20 ? '#ffc107' : '#dc3545');

        if (distance <= 0) {
          clearInterval(window.timerState.otpInterval);
          window.timerState.otpInterval = null;
          window.timerState.isOtpTimerRunning = false;
          timerElement.innerHTML = '';
          timerElement.classList.add('d-none');
          progressBarContainer.style.display = 'none';
          resendSection.style.display = window.timerState.isRateLimitTimerRunning ? 'none' : 'block';
          Livewire.dispatch('updateShowResendButton', {
            show: !window.timerState.isRateLimitTimerRunning
          });
          localStorage.setItem('otpTimerData', JSON.stringify({
            countDownDate: window.timerState.otpCountDownDate,
            token: window.timerState.currentToken
          }));
        } else {
          timerElement.innerHTML = `زمان باقی‌مانده: ${minutes} دقیقه و ${seconds} ثانیه`;
        }
      }, 1000);
    }

    function startRateLimitTimer(remainingTime) {
      if (window.timerState.rateLimitInterval) clearInterval(window.timerState.rateLimitInterval);

      const storedData = JSON.parse(localStorage.getItem('rateLimitTimerData') || '{}');
      const storedCountDownDate = storedData.countDownDate;
      const now = new Date().getTime();

      if (!storedCountDownDate || storedCountDownDate <= now) {
        window.timerState.rateLimitCountDownDate = now + (remainingTime * 1000);
        localStorage.setItem('rateLimitTimerData', JSON.stringify({
          countDownDate: window.timerState.rateLimitCountDownDate
        }));
      } else {
        window.timerState.rateLimitCountDownDate = Number(storedCountDownDate);
        remainingTime = Math.round((window.timerState.rateLimitCountDownDate - now) / 1000); // به‌روزرسانی remainingTime
      }

      window.timerState.isRateLimitTimerRunning = true;

      // محاسبه زمان اولیه به صورت دقیق
      const initialTimeText = formatConditionalTime(remainingTime);

      Swal.fire({
        icon: 'error',
        title: 'تلاش بیش از حد',
        html: `<span id="remaining-time" style="font-weight: bold;">لطفاً ${initialTimeText} دیگر تلاش کنید</span>`,
        timer: remainingTime * 1000,
        timerProgressBar: true,
        showConfirmButton: true,
        confirmButtonText: 'باشه',
        allowOutsideClick: false,
        didOpen: () => {
          const remainingTimeElement = document.getElementById('remaining-time');
          window.timerState.rateLimitInterval = setInterval(() => {
            const now = new Date().getTime();
            const distance = window.timerState.rateLimitCountDownDate - now;
            const secondsLeft = Math.round(distance / 1000);

            if (secondsLeft >= 0) {
              remainingTimeElement.innerHTML = `لطفاً ${formatConditionalTime(secondsLeft)} دیگر تلاش کنید`;
              remainingTimeElement.style.color = secondsLeft > 180 ? '#16a34a' : secondsLeft > 60 ? '#f59e0b' :
                '#dc2626';
            }

            if (distance <= 0) {
              clearInterval(window.timerState.rateLimitInterval);
              window.timerState.rateLimitInterval = null;
              window.timerState.isRateLimitTimerRunning = false;
              localStorage.removeItem('rateLimitTimerData');
              const resendSection = document.getElementById('resend-otp');
              if (resendSection && !window.timerState.isOtpTimerRunning) {
                resendSection.style.display = 'block';
                Livewire.dispatch('updateShowResendButton', {
                  show: true
                });
              }
            }
          }, 1000);
        },
        willClose: () => {
          if (window.timerState.rateLimitInterval) {
            clearInterval(window.timerState.rateLimitInterval);
            window.timerState.rateLimitInterval = null;
          }
        }
      });
    }

    function setupOtpInputs() {
      const inputs = document.querySelectorAll('.otp-input');
      const submitButton = document.querySelector('button[type="submit"]');
      if (inputs.length > 0) {
        inputs[3].focus();
        inputs.forEach((input, index) => {
          input.addEventListener('input', (e) => {
            const value = e.target.value.replace(/[^0-9]/g, '');
            e.target.value = value;
            if (value.length === 1 && index > 0) inputs[index - 1].focus();
            // بررسی پر بودن تمام فیلدها
            const allFilled = Array.from(inputs).every(inp => inp.value.length === 1);
            if (allFilled && submitButton) {
              submitButton.click();
            }
          });
          input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && e.target.value.length === 0 && index < inputs.length - 1) inputs[index +
              1].focus();
          });
          input.addEventListener('focus', () => input.select());
        });
      }
    }

    if ('OTPCredential' in window) {
      window.addEventListener('DOMContentLoaded', () => {
        const ac = new AbortController();
        navigator.credentials.get({
          otp: {
            transport: ['sms']
          },
          signal: ac.signal
        }).then(otp => {
          Swal.fire({
            title: 'دریافت کد OTP',
            text: `آیا می‌خواهید کد ${otp.code} به‌صورت خودکار وارد شود؟`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'بله',
            cancelButtonText: 'خیر'
          }).then((result) => {
            if (result.isConfirmed) {
              const inputs = document.querySelectorAll('.otp-input');
              const code = otp.code.split('').reverse();
              inputs.forEach((input, index) => {
                if (code[index]) {
                  input.value = code[index];
                  Livewire.dispatch('input', {
                    target: {
                      name: `otpCode.${index}`,
                      value: code[index]
                    }
                  });
                }
              });
              const submitButton = document.querySelector('button[type="submit"]');
              if (submitButton) submitButton.click();
            }
            ac.abort();
          });
        }).catch(err => {
          console.error('Failed to retrieve OTP:', err);
        });

        setTimeout(() => ac.abort(), 60000);
      });
    }

    document.addEventListener('livewire:navigated', () => {
      setupOtpInputs();
      if (window.timerState.otpCountDownDate && window.timerState.isOtpTimerRunning) {
        startOtpTimer(window.timerState.otpCountDownDate, window.timerState.currentToken);
      }
    });

    Livewire.on('initTimer', (data) => {
      const storedData = JSON.parse(localStorage.getItem('otpTimerData') || '{}');
      if (!storedData.countDownDate || storedData.token !== data.token) {
        startOtpTimer(data.countDownDate, data.token);
      } else {
        startOtpTimer(storedData.countDownDate, storedData.token);
      }
      setupOtpInputs();
    });

    Livewire.on('otpResent', (data) => {
      toastr.success(data.message);
      startOtpTimer(data.countDownDate, data.token);
    });

    Livewire.on('otpExpired', () => {
      toastr.error('توکن منقضی شده است');
      window.Livewire.navigate('{{ route('admin.auth.login-register-form') }}');
      localStorage.removeItem('otpTimerData');
      if (window.timerState.otpInterval) {
        clearInterval(window.timerState.otpInterval);
        window.timerState.otpInterval = null;
      }
      window.timerState.isOtpTimerRunning = false;
    });

    Livewire.on('rateLimitExceeded', (data) => {
      startRateLimitTimer(data.remainingTime);
      const errorElement = document.getElementById('otp-error');
      if (errorElement) errorElement.innerHTML = '';
    });

    Livewire.on('loginSuccess', () => {
      toastr.success('با موفقیت وارد شدید');
      localStorage.removeItem('otpTimerData');
      localStorage.removeItem('rateLimitTimerData');
      if (window.timerState.otpInterval) clearInterval(window.timerState.otpInterval);
      window.timerState.isOtpTimerRunning = false;
    });

    Livewire.on('navigateTo', (event) => {
      window.Livewire.navigate(event.url);
    });

    Livewire.on('updateShowResendButton', (data) => {
      const resendSection = document.getElementById('resend-otp');
      if (resendSection) resendSection.style.display = data.show ? 'block' : 'none';
    });

    function formatConditionalTime(seconds) {
      if (isNaN(seconds) || seconds < 0) return '0 ثانیه';
      const hours = Math.floor(seconds / 3600);
      const minutes = Math.floor((seconds % 3600) / 60);
      const secs = seconds % 60;
      if (hours > 0) return `${hours} ساعت ${minutes} دقیقه ${secs} ثانیه`;
      else if (minutes > 0) return `${minutes} دقیقه ${secs} ثانیه`;
      else return `${secs} ثانیه`;
    }

    document.addEventListener('DOMContentLoaded', () => {
      const storedOtpData = JSON.parse(localStorage.getItem('otpTimerData') || '{}');
      const storedRateLimitData = JSON.parse(localStorage.getItem('rateLimitTimerData') || '{}');
      const now = new Date().getTime();

      if (!storedOtpData.countDownDate && !storedRateLimitData.countDownDate) {
        window.Livewire.navigate('{{ route('admin.auth.login-register-form') }}');
        return;
      }

      if (storedOtpData.countDownDate) {
        if (storedOtpData.countDownDate > now) {
          window.timerState.otpCountDownDate = Number(storedOtpData.countDownDate);
          window.timerState.currentToken = storedOtpData.token;
          window.timerState.isOtpTimerRunning = true;
          startOtpTimer(window.timerState.otpCountDownDate, window.timerState.currentToken);
        } else {
          toastr.error('زمان کد تأیید به پایان رسیده است');
          window.Livewire.navigate('{{ route('admin.auth.login-register-form') }}');
          localStorage.removeItem('otpTimerData');
          return;
        }
      }

      if (storedRateLimitData.countDownDate && storedRateLimitData.countDownDate > now) {
        window.timerState.rateLimitCountDownDate = Number(storedRateLimitData.countDownDate);
        window.timerState.isRateLimitTimerRunning = true;
        startRateLimitTimer(Math.round((storedRateLimitData.countDownDate - now) / 1000));
      }
      setupOtpInputs();
    });
  </script>
@endpush

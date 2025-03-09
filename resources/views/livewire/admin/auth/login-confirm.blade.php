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
     <div class="invalid-feedback otp-error" style="display: block; text-align: center;">{{ $message }}</div>
    @enderror
    <button type="submit" wire:loading.attr="disabled" wire:target="loginConfirm"
     class="btn btn-primary w-100 custom-gradient custom-rounded py-2 d-flex justify-content-center">
     <span wire:loading.remove wire:target="loginConfirm">ادامه</span>
     <div wire:loading wire:target="loginConfirm" class="loader"></div>
    </button>

    <div id="progress-bar-container" class="mt-2" wire:ignore.self
     style="{{ $remainingTime <= 0 ? 'display: none;' : '' }}">
     <div id="progress-bar" class="progress-bar"
      style="width: {{ ($remainingTime / 120000) * 100 }}%; 
       background-color: {{ $remainingTime > 60000 ? '#28a745' : ($remainingTime > 24000 ? '#ffc107' : '#dc3545') }};">
     </div>
    </div>

    <section id="resend-otp" class="mt-2 text-center" style="{{ $showResendButton ? '' : 'display: none;' }}">
     <a href="#" wire:click.prevent="resendOtp" wire:loading.remove wire:target="resendOtp"
      class="text-decoration-none text-primary fw-bold">دریافت مجدد کد تأیید</a>
     <span wire:loading wire:target="resendOtp" class="spinner-border spinner-border-sm" role="status"></span>
    </section>
    <section style="font-size: 14px" class="text-danger fw-bold fs-6 mt-3 text-center" id="timer" wire:ignore.self>
     {{ $remainingTime > 0 ? 'زمان باقی‌مانده: ' . floor($remainingTime / 60000) . ' دقیقه و ' . floor(($remainingTime % 60000) / 1000) . ' ثانیه' : 'کد تأیید منقضی شده است.' }}
    </section>
   </form>
  </div>
 </div>
</div>

@push('scripts')
    <script>
        window.interval = window.interval || null;

        function startTimer(duration, countDownDate) {
            if (window.interval) {
                clearInterval(window.interval);
            }

            // تبدیل به عدد و مدیریت مقادیر نامعتبر
            duration = Number(duration) || 120000; // پیش‌فرض 2 دقیقه اگه duration نامعتبر باشه
            countDownDate = Number(countDownDate) || (new Date().getTime() + duration);
            const totalDuration = 120000; // زمان کل ثابت (2 دقیقه)


            const timerElement = document.getElementById('timer');
            const progressBarContainer = document.getElementById('progress-bar-container');
            const progressBar = document.getElementById('progress-bar');
            const resendSection = document.getElementById('resend-otp');

            if (!timerElement || !progressBarContainer || !progressBar || !resendSection) {
                return;
            }

            timerElement.classList.remove('d-none');
            progressBarContainer.style.display = 'block';
            resendSection.style.display = 'none';

            window.interval = setInterval(() => {
                const now = new Date().getTime();
                const distance = countDownDate - now;

                if (isNaN(distance)) {
                    console.error('فاصله نامعتبر:', { countDownDate, now });
                    clearInterval(window.interval);
                    window.interval = null;
                    return;
                }

                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                const percentage = Math.max(0, (distance / totalDuration) * 100);
                progressBar.style.width = percentage + '%';

                if (percentage > 50) {
                    progressBar.style.backgroundColor = '#28a745';
                } else if (percentage > 20) {
                    progressBar.style.backgroundColor = '#ffc107';
                } else {
                    progressBar.style.backgroundColor = '#dc3545';
                }

                if (distance <= 0) {
                    clearInterval(window.interval);
                    window.interval = null;
                    timerElement.innerHTML = 'کد تأیید منقضی شده است.';
                    timerElement.classList.add('d-none');
                    progressBarContainer.style.display = 'none';
                    resendSection.style.display = 'block';
                    Livewire.dispatch('updateShowResendButton', { show: true });
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
        });

        Livewire.on('initOtpForm', (data) => {
            startTimer(data.remainingTime, data.countDownDate);
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
            startTimer(data.remainingTime, data.countDownDate);
            const resendSection = document.getElementById('resend-otp');
            if (resendSection) {
                resendSection.style.display = 'none';
            }
            const progressBarContainer = document.getElementById('progress-bar-container');
            if (progressBarContainer) {
                progressBarContainer.style.display = 'block';
            }
            Livewire.dispatch('updateShowResendButton', { show: false });
        });

        Livewire.on('otpExpired', () => {
            toastr.error('توکن منقضی شده است');
            window.Livewire.navigate('{{ route('admin.auth.login-register-form') }}');
        });

        Livewire.on('rateLimitExceeded', (data) => {
            let remainingTime = Math.round(data.remainingTime);
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
                    const timerInterval = setInterval(() => {
                        liveTimer--;
                        remainingTimeElement.innerHTML = formatTime(liveTimer);
                        if (liveTimer <= 0) {
                            clearInterval(timerInterval);
                            Swal.close();
                        }
                    }, 1000);
                },
                willClose: () => {
                    clearInterval(timerInterval);
                }
            });
        });

        Livewire.on('loginSuccess', () => {
            toastr.success('با موفقیت وارد شدید');
        });

        Livewire.on('navigateTo', (event) => {
            window.Livewire.navigate(event.url);
        });

        Livewire.on('updateShowResendButton', (data) => {
            const resendSection = document.getElementById('resend-otp');
            if (resendSection) {
                resendSection.style.display = data.show ? 'block' : 'none';
            }
        });

        function formatTime(seconds) {
            if (isNaN(seconds) || seconds < 0) return '0 دقیقه و 0 ثانیه';
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            return `${minutes} دقیقه و ${remainingSeconds} ثانیه`;
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (window.interval) {
                clearInterval(window.interval);
                window.interval = null;
            }
            const initialRemainingTime = {{ $remainingTime ?? 0 }};
            const initialCountDownDate = {{ $countDownDate ?? 0 }};
            const initialShowResendButton = {{ $showResendButton ? 'true' : 'false' }};
            if (initialRemainingTime > 0) {
                startTimer(initialRemainingTime, initialCountDownDate);
            }
            const resendSection = document.getElementById('resend-otp');
            if (resendSection) {
                resendSection.style.display = initialShowResendButton ? 'block' : 'none';
            }
        });
    </script>
@endpush

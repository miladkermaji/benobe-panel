<div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
  style="background-color: #f8f9fa;">
  <div class="col-11 col-sm-9 col-md-7 col-lg-5 col-xl-4 mx-auto d-flex justify-content-center">
    <div class="login-card custom-rounded custom-shadow p-4 p-md-7 bg-white w-100">
      <div class="logo-wrapper w-100 d-flex justify-content-center mb-4">
        <img class="cursor-pointer" onclick="location.href='/'" width="100px"
          src="{{ asset('app-assets/logos/benobe.svg') }}" alt="لوگوی به نوبه">
      </div>
      <div class="text-center mb-4">
        <h2 class="text-primary fw-bold mb-2 text-center">پنل پزشکان و مراکز درمانی به نوبه</h2>
        {{-- <p class="text-muted fw-bold">به پنل مدیریت پزشکان و مراکز درمانی به نوبه خوش آمدید</p> --}}
      </div>
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center">
          <div class="rounded-circle bg-primary me-2" style="width: 16px; height: 16px;"></div>
          <span class="text-custom-gray px-1 fw-bold">ورود کاربر</span>
        </div>
      </div>
      <form wire:submit.prevent="loginRegister" class="login-register-form">
        <div class="mb-3">
          <div class="d-flex align-items-center mb-2">
            <img src="{{ asset('dr-assets/login/images/phone.svg') }}" alt="آیکون تلفن" class="me-2">
            <label class="text-custom-gray">شماره موبایل</label>
          </div>
          <input wire:model="mobile" dir="ltr"
            class="form-control  h-50 border-3 border-gray-300 @error('mobile') is-invalid @enderror"
            inputmode="numeric" placeholder="09123456789" maxlength="11">
          @error('mobile')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <button type="submit" wire:loading.attr="disabled"
          class="btn w-100 custom-gradient custom-rounded h-50 d-flex justify-content-center border-x-0">
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
              remainingTimeElement.innerHTML =
                `لطفاً ${formatConditionalTime(remainingTime)} دیگر تلاش کنید`;
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
    Livewire.on('pass-form', (data) => {
      toastr.success('موفقیت آمیز');
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
      if (!window.location.href.includes('from_back')) { // فقط در بارگذاری اولیه
        document.querySelector('input[wire\\:model="mobile"]').focus();
      }
    });
  </script>
@endpush

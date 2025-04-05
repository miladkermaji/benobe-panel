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
        <button type="submit" wire:loading.attr="disabled" wire.target="twoFactorCheck"
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
      console.log('Rate limit exceeded event received:', data);
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

    Livewire.on('loginSuccess', () => {
      toastr.success('با موفقیت وارد شدید');
    });

    Livewire.on('navigateTo', (event) => {
      window.Livewire.navigate(event.url);
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

    document.addEventListener('livewire:initialized', () => {
      document.querySelector('input[wire\\:model="twoFactorSecret"]').focus();
    });
  </script>
@endpush

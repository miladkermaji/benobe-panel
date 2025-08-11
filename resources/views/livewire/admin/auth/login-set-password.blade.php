<div class="d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100"
  style="background-color: #f8f9fa;">
  <div class="col-11 col-sm-9 col-md-7 col-lg-5 col-xl-4 mx-auto d-flex justify-content-center">
    <div class="login-card custom-rounded custom-shadow p-4 p-md-7 bg-white w-100">
      <div class="logo-wrapper w-100 d-flex justify-content-center mb-4">
        <img class="cursor-pointer" onclick="location.href='/'" width="100px"
          src="{{ asset('app-assets/logos/benobe.svg') }}" alt="لوگوی به نوبه">
      </div>
      <div class="text-center mb-4">
        <h2 class="text-primary fw-bold mb-2">تعیین رمز عبور</h2>
        <p class="text-muted fw-bold">لطفاً رمز عبور جدید خود را تعیین کنید</p>
      </div>
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
          <div class="rounded-circle bg-primary me-2" style="width: 16px; height: 16px;"></div>
          <span class="text-custom-gray px-1">تعیین رمز عبور</span>
        </div>
        <a href="#" wire:click.prevent="goBack" class="back-link text-primary d-flex align-items-center"
          style="cursor: pointer;">
          <span class="ms-2">بازگشت</span>
          <img src="{{ asset('admin-assets/login/images/back.svg') }}" alt="آیکون بازگشت" class="img-fluid"
            style="max-width: 24px;">
        </a>
      </div>

      <div class="alert alert-info mb-4">
        <div class="d-flex align-items-start">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            class="me-2 mt-1">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="m9 12 2 2 4-4"></path>
          </svg>
          <div>
            <strong>نکات مهم:</strong>
            <ul class="mb-0 mt-2">
              <li>رمز عبور باید حداقل 8 کاراکتر باشد</li>
              <li>باید شامل حداقل یک حرف بزرگ انگلیسی باشد</li>
              <li>باید شامل حداقل یک حرف کوچک انگلیسی باشد</li>
              <li>باید شامل حداقل یک عدد باشد</li>
              <li>باید شامل حداقل یک کاراکتر خاص (@$!%*?&) باشد</li>
            </ul>
          </div>
        </div>
      </div>

      <form wire:submit.prevent="setPassword" class="login-set-password-form">
        <div class="mb-3">
          <div class="d-flex align-items-center mb-2">
            <img src="{{ asset('admin-assets/login/images/password.svg') }}" alt="آیکون رمز" class="me-2">
            <label class="text-custom-gray">رمز عبور جدید</label>
          </div>
          <div class="position-relative">
            <input wire:model="password"
              class="form-control custom-shadow h-50 text-end @error('password') is-invalid @enderror" type="password"
              placeholder="رمز عبور جدید را وارد کنید" id="password-input">
            <img src="{{ asset('admin-assets/login/images/visible.svg') }}" alt="نمایش رمز" class="password-toggle"
              onclick="togglePasswordVisibility('password-input')">
          </div>
          @error('password')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
        </div>

        <div class="mb-4">
          <div class="d-flex align-items-center mb-2">
            <img src="{{ asset('admin-assets/login/images/password.svg') }}" alt="آیکون رمز" class="me-2">
            <label class="text-custom-gray">تکرار رمز عبور</label>
          </div>
          <div class="position-relative">
            <input wire:model="password_confirmation"
              class="form-control custom-shadow h-50 text-end @error('password_confirmation') is-invalid @enderror"
              type="password" placeholder="تکرار رمز عبور را وارد کنید" id="password-confirmation-input">
            <img src="{{ asset('admin-assets/login/images/visible.svg') }}" alt="نمایش رمز" class="password-toggle"
              onclick="togglePasswordVisibility('password-confirmation-input')">
          </div>
          @error('password_confirmation')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
        </div>

        <button type="submit" wire:loading.attr="disabled" wire:target="setPassword"
          class="btn w-100 custom-gradient custom-rounded h-50 d-flex justify-content-center">
          <span wire:loading.remove wire:target="setPassword">تعیین رمز عبور</span>
          <div wire:loading wire:target="setPassword" class="loader"></div>
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
    Livewire.on('otpAlreadySent', (data) => {
      toastr.info(data.message);
    })
    Livewire.on('navigateTo', (event) => {
      window.Livewire.navigate(event.url);
    });

    document.addEventListener('livewire:initialized', () => {
      document.querySelector('input[wire\\:model="password"]').focus();
    });
  </script>
@endpush

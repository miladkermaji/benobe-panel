<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden" style="background: #ffffff;">
    <div class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between  gap-3">
      <div class="d-flex align-items-center gap-3 mb-2">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="custom-animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">افزودن منشی جدید</h5>
      </div>
      <a href="{{ route('admin.panel.secretaries.index') }}"
        class="btn btn-outline-light btn-sm rounded-pill px-4 d-flex align-items-center gap-2 hover:shadow-lg transition-all">
        <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        بازگشت
      </a>
    </div>

    <div class="card-body p-4">
      <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
          <!-- آپلود عکس -->
          <div class="text-center mb-4">
            <div class="position-relative d-inline-block">
              <img src="{{ $this->photoPreview }}" class="rounded-circle shadow border-2 border-white"
                style="width: 100px; height: 100px; object-fit: cover;" alt="پروفایل" wire:loading.class="opacity-50"
                wire:target="profile_photo">
              <label for="profile_photo"
                class="btn my-btn-primary btn-sm rounded-circle position-absolute bottom-0 end-0 p-2 shadow"
                style="transform: translate(10%, 10%);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2">
                  <path d="M4 12h16M12 4v16" />
                </svg>
              </label>
              <input type="file" wire:model="profile_photo" id="profile_photo" class="d-none" accept="image/*">
            </div>
            @error('profile_photo')
              <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>

          <!-- فرم -->
          <div class="row g-4">
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="first_name" class="form-control" id="first_name" placeholder=" "
                required>
              <label for="first_name" class="form-label">نام</label>
              @error('first_name')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="last_name" class="form-control" id="last_name" placeholder=" " required>
              <label for="last_name" class="form-label">نام خانوادگی</label>
              @error('last_name')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="mobile" class="form-control" id="mobile" placeholder=" " required>
              <label for="mobile" class="form-label">موبایل</label>
              @error('mobile')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="national_code" class="form-control" id="national_code" placeholder=" "
                required>
              <label for="national_code" class="form-label">کد ملی</label>
              @error('national_code')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <select wire:model="gender" class="form-select" id="gender">
                <option value="male">مرد</option>
                <option value="female">زن</option>
              </select>
              <label for="gender" class="form-label">جنسیت</label>
              @error('gender')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="email" wire:model="email" class="form-control" id="email" placeholder=" ">
              <label for="email" class="form-label">ایمیل (اختیاری)</label>
              @error('email')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="password" wire:model="password" class="form-control" id="password" placeholder=" ">
              <label for="password" class="form-label">رمز عبور (اختیاری)</label>
              @error('password')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
              <select wire:model.live="doctor_id" class="form-select select2" id="doctor_id">
                <option value="">انتخاب کنید</option>
                @foreach ($doctors as $doctor)
                  <option value="{{ $doctor->id }}">{{ $doctor->first_name . ' ' . $doctor->last_name }}</option>
                @endforeach
              </select>
              <label for="doctor_id" class="form-label">دکتر (اختیاری)</label>
              @error('doctor_id')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>

              <select wire:model="medical_center_id" class="form-select select2" id="medical_center_id">
                <option value="">انتخاب کنید</option>
                @foreach ($clinics as $clinic)
                  <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                @endforeach
              </select>
              <label for="medical_center_id" class="form-label">کلینیک (اختیاری)</label>
              @error('medical_center_id')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-6 col-md-6 position-relative mt-5 d-flex align-items-center">
              <div class="form-check form-switch w-100 d-flex align-items-center">
                <input class="form-check-input" type="checkbox" id="is_active" wire:model="is_active">
                <label class="form-check-label fw-medium" for="is_active">
                  وضعیت: <span
                    class="px-2 text-{{ $is_active ? 'success' : 'danger' }}">{{ $is_active ? 'فعال' : 'غیرفعال' }}</span>
                </label>
              </div>
            </div>
          </div>

          <div class="text-end mt-4 w-100 d-flex justify-content-end">
            <button wire:click="store" onclick="console.log('Store secretary called')"
              class="btn my-btn-primary px-5 py-2 d-flex align-items-center gap-2 shadow-lg hover:shadow-xl transition-all">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M12 5v14M5 12h14" />
              </svg>
              افزودن منشی
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>



  <script>
    document.addEventListener('livewire:init', function() {
      function initializeSelect2() {
        $('#doctor_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });
        $('#medical_center_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });
      }
      initializeSelect2();

      $('#doctor_id').on('change', function() {
        console.log('Doctor selected:', $(this).val());
        @this.set('doctor_id', $(this).val());
      });
      $('#medical_center_id').on('change', function() {
        console.log('Clinic selected:', $(this).val());
        @this.set('medical_center_id', $(this).val());
      });

      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });

      Livewire.hook('message.processed', (message, component) => {
        $('#medical_center_id').select2('destroy');
        $('#medical_center_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });
      });

      Livewire.on('refresh-clinic-select2', (event) => {
        const clinics = event.clinics || [];
        $('#medical_center_id').select2('destroy');
        $('#medical_center_id').empty().select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%',
          data: clinics.map(clinic => ({
            id: clinic.id,
            text: clinic.name
          }))
        });
      });

      document.addEventListener('livewire:updated', function() {
        initializeSelect2();
      });
    });
  </script>
</div>

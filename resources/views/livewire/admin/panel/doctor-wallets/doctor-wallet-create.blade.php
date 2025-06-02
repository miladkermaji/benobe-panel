<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden" style="background: #ffffff;">
    <div
      class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="custom-animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">شارژ کیف‌پول پزشک</h5>
      </div>
      <a href="{{ route('admin.panel.doctor-wallets.index') }}"
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
          <!-- فرم شارژ کیف‌پول -->
          <div class="row g-4">
            <div class="col-12 position-relative mt-5" wire:ignore>
              <select wire:model.live="selectedDoctorId" class="form-control select2" id="doctorSelect">
                <option value="">پزشک را انتخاب کنید</option>
                @foreach ($doctors as $doctor)
                  <option value="{{ $doctor['id'] }}">{{ $doctor['text'] }}</option>
                @endforeach
              </select>
              <label for="doctorSelect" class="form-label">انتخاب پزشک</label>
              @error('selectedDoctorId')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-12 position-relative mt-5">
              <input type="text" wire:model="displayAmount" class="form-control" id="amount" placeholder=" "
                required>
              <label for="amount" class="form-label">مبلغ شارژ (تومان)</label>
              @error('amount')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
          </div>

          <div class="text-end mt-4 w-100 d-flex justify-content-end">
            <button wire:click="chargeWallet" wire:loading.attr="disabled"
              class="btn my-btn-primary px-5 py-2 d-flex align-items-center gap-2 shadow-lg hover:shadow-xl transition-all">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                <path d="M17 21v-8H7v8M7 3v5h8" />
              </svg>
              <span wire:loading.remove>شارژ</span>
              <span wire:loading>در حال پردازش...</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>



  <script>
    document.addEventListener('livewire:init', function() {
      function initializeSelect2() {
        $('#doctorSelect').select2({
          dir: 'rtl',
          placeholder: 'پزشک را انتخاب کنید',
          width: '100%',
        }).on('change', function(e) {
          @this.set('selectedDoctorId', e.target.value);
        });
      }

      // راه‌اندازی اولیه Select2
      initializeSelect2();

      // بازسازی Select2 بعد از هر آپدیت Livewire
      Livewire.on('refresh-select2', () => {
        $('#doctorSelect').select2('destroy');
        initializeSelect2();
        $('#doctorSelect').val(@json($selectedDoctorId)).trigger('change');
      });

      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });

      Livewire.on('redirect-to-gateway', (event) => {
        window.location.href = event.url;
      });
    });
  </script>
</div>

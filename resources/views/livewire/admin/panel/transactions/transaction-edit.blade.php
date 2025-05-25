<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden" style="background: #ffffff;">
    <div
      class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">ویرایش تراکنش</h5>
      </div>
      <a href="{{ route('admin.panel.transactions.index') }}"
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
          <div class="row g-4">
            <div class="col-6 col-md-6 position-relative mt-5">
              <select wire:model="transactable_type" class="form-select select2" id="transactable_type" disabled>
                <option value="">انتخاب کنید</option>
                @foreach ($entities as $type => $label)
                  <option value="{{ $type }}">{{ $label }}</option>
                @endforeach
              </select>
              <label for="transactable_type" class="form-label">نوع موجودیت</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <select wire:model="transactable_id" class="form-select select2" id="transactable_id" disabled>
                <option value="">ابتدا نوع را انتخاب کنید</option>
                @if ($transactable_type === 'App\Models\User')
                  @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                  @endforeach
                @elseif ($transactable_type === 'App\Models\Doctor')
                  @foreach ($doctors as $doctor)
                    <option value="{{ $doctor->id }}">{{ $doctor->full_name }}</option>
                  @endforeach
                @elseif ($transactable_type === 'App\Models\Secretary')
                  @foreach ($secretaries as $secretary)
                    <option value="{{ $secretary->id }}">{{ $secretary->first_name . ' ' . $secretary->last_name }}
                    </option>
                  @endforeach
                @elseif ($transactable_type === 'App\Models\Admin\Manager')
                  @foreach ($managers as $manager)
                    <option value="{{ $manager->id }}">{{ $manager->first_name . ' ' . $manager->last_name }}</option>
                  @endforeach
                @endif
              </select>
              <label for="transactable_id" class="form-label">شناسه موجودیت</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="number" wire:model="amount" class="form-control" id="amount" placeholder=" " required>
              <label for="amount" class="form-label">مبلغ (تومان)</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="gateway" class="form-control" id="gateway" placeholder=" " required>
              <label for="gateway" class="form-label">درگاه پرداخت</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <select wire:model="status" class="form-select" id="status">
                <option value="pending">در انتظار</option>
                <option value="paid">پرداخت‌شده</option>
                <option value="failed">ناموفق</option>
              </select>
              <label for="status" class="form-label">وضعیت</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="transaction_id" class="form-control" id="transaction_id"
                placeholder=" ">
              <label for="transaction_id" class="form-label">شناسه تراکنش (اختیاری)</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <select wire:model="type" class="form-select" id="type">
                <option value="">انتخاب کنید</option>
                <option value="wallet_charge">شارژ کیف‌پول</option>
                <option value="profile_upgrade">ارتقاء پروفایل</option>
                <option value="other">سایر</option>
              </select>
              <label for="type" class="form-label">نوع تراکنش</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="description" class="form-control" id="description"
                placeholder="مثلاً: شارژ کیف‌پول">
              <label for="description" class="form-label">توضیحات</label>
            </div>
          </div>

          <div class="text-end mt-4 w-100 d-flex justify-content-end">
            <button wire:click="update"
              class="btn my-btn-primary px-5 py-2 d-flex align-items-center gap-2 shadow-lg hover:shadow-xl transition-all">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M12 5v14M5 12h14" />
              </svg>
              به‌روزرسانی 
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>


  <script>
    document.addEventListener('livewire:initialized', function() {
      function initializeSelect2() {
        $('#transactable_type').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%',
          disabled: true
        });
        $('#transactable_id').select2({
          dir: 'rtl',
          placeholder: 'ابتدا نوع را انتخاب کنید',
          width: '100%',
          disabled: true
        });
        $('#type').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });
      }
      initializeSelect2();

      $('#type').on('change', function() {
        @this.set('type', $(this).val());
      });

      Livewire.hook('morph.updated', () => {
        $('#transactable_type').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%',
          disabled: true
        });
        $('#transactable_id').select2({
          dir: 'rtl',
          placeholder: 'ابتدا نوع را انتخاب کنید',
          width: '100%',
          disabled: true
        });
        $('#type').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });
      });

      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });
    });
  </script>
</div>

<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden" style="background: #ffffff;">
    <div class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">افزودن تراکنش جدید</h5>
      </div>
      <a href="{{ route('admin.panel.transactions.index') }}" class="btn btn-outline-light btn-sm rounded-pill px-4 d-flex align-items-center gap-2 hover:shadow-lg transition-all">
        <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
              <select wire:model.live="transactable_type" class="form-select select2" id="transactable_type">
                <option value="">انتخاب کنید</option>
                @foreach ($entities as $type => $label)
                  <option value="{{ $type }}">{{ $label }}</option>
                @endforeach
              </select>
              <label for="transactable_type" class="form-label">نوع موجودیت</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <select wire:model.live="transactable_id" class="form-select select2" id="transactable_id" {{ !$transactable_type ? 'disabled' : '' }}>
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
                    <option value="{{ $secretary->id }}">{{ $secretary->first_name . ' ' . $secretary->last_name }}</option>
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
              <input type="text" wire:model="transaction_id" class="form-control" id="transaction_id" placeholder=" ">
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
              <input type="text" wire:model="description" class="form-control" id="description" placeholder="مثلاً: شارژ کیف‌پول">
              <label for="description" class="form-label">توضیحات</label>
            </div>
          </div>

          <div class="text-end mt-4 w-100 d-flex justify-content-end">
            <button wire:click="store" class="btn btn-primary px-5 py-2 d-flex align-items-center gap-2 shadow-lg hover:shadow-xl transition-all">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14M5 12h14" />
              </svg>
              افزودن تراکنش
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <style>
    .bg-gradient-primary {
      background: linear-gradient(90deg, #6b7280, #374151);
    }

    .card {
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .form-control,
    .form-select,
    .form-control textarea {
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      padding: 12px 15px;
      font-size: 14px;
      transition: all 0.3s ease;
      height: 48px;
      background: #fafafa;
      width: 100%;
    }

    .form-control:focus,
    .form-select:focus,
    .form-control textarea:focus {
      border-color: #6b7280;
      box-shadow: 0 0 0 3px rgba(107, 114, 128, 0.2);
      background: #fff;
    }

    .form-label {
      position: absolute;
      top: -25px;
      right: 15px;
      color: #374151;
      font-size: 12px;
      background: #ffffff;
      padding: 0 5px;
      pointer-events: none;
    }

    .btn-primary {
      background: linear-gradient(90deg, #6b7280, #374151);
      border: none;
      color: white;
      font-weight: 600;
    }

    .btn-primary:hover {
      background: linear-gradient(90deg, #4b5563, #1f2937);
      transform: translateY(-2px);
    }

    .select2-container {
      width: 100% !important;
    }

    .select2-container--default .select2-selection--single {
      height: 48px;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      background: #fafafa;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
      line-height: 46px;
      padding-right: 15px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 46px;
    }

    .select2-dropdown {
      z-index: 1050 !important;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
    }
  </style>

  <script>
    document.addEventListener('livewire:initialized', function() {
      function initializeSelect2() {
        $('#transactable_type').select2({ dir: 'rtl', placeholder: 'انتخاب کنید', width: '100%' });
        $('#transactable_id').select2({ dir: 'rtl', placeholder: 'ابتدا نوع را انتخاب کنید', width: '100%', disabled: !@this.transactable_type });
        $('#type').select2({ dir: 'rtl', placeholder: 'انتخاب کنید', width: '100%' });
      }
      initializeSelect2();

      $('#transactable_type').on('change', function() { @this.set('transactable_type', $(this).val()); });
      $('#transactable_id').on('change', function() { @this.set('transactable_id', $(this).val()); });
      $('#type').on('change', function() { @this.set('type', $(this).val()); });

      Livewire.hook('morph.updated', () => {
        $('#transactable_type').select2({ dir: 'rtl', placeholder: 'انتخاب کنید', width: '100%' });
        $('#transactable_id').select2({ dir: 'rtl', placeholder: 'ابتدا نوع را انتخاب کنید', width: '100%', disabled: !@this.transactable_type });
        $('#type').select2({ dir: 'rtl', placeholder: 'انتخاب کنید', width: '100%' });
      });

      Livewire.on('refresh-select2', () => {
        $('#transactable_id').select2('destroy');
        $('#transactable_id').select2({ dir: 'rtl', placeholder: 'ابتدا نوع را انتخاب کنید', width: '100%', disabled: !@this.transactable_type });
      });

      Livewire.on('show-alert', (event) => { toastr[event.type](event.message); });
    });
  </script>
</div>
<div class="payment-setting-content w-100 d-flex justify-content-center mt-4 flex-wrap" x-data="{ mobileSearchOpen: false }">
  <div class="payment-setting-content-wrapper p-3">
    <div class="top-peayment-setting-card w-100 d-flex justify-content-between border-bottom-ddd">
      <div class="d-flex justify-content-center w-100 border-bottom-primary pb-2 cursor-pointer tab"
        data-tab="gozaresh-mali">
        <span class="font-size-13">گزارش مالی</span>
      </div>
    </div>
    <div class="gozaresh-mali-content mt-3">
      <div class="gozaresh-mali-card-bg w-100 d-flex mt-3 p-3 justify-content-around">
        <div class="d-flex flex-column justify-content-center">
          <span class="text-center text-white fw-bold font-size-13">کل در آمد</span>
          <span class="text-center text-white fw-bold mt-2 font-size-13">{{ number_format($totalIncome) }}</span>
          <span class="text-center text-white fw-bold mt-2 font-size-13">تومان</span>
        </div>
        <div class="d-flex flex-column justify-content-center">
          <span class="text-center text-white fw-bold font-size-13">پرداخت شده</span>
          <span class="text-center text-white fw-bold mt-2 font-size-13">{{ number_format($paid) }}</span>
          <span class="text-center text-white fw-bold mt-2 font-size-13">تومان</span>
        </div>
        <div class="d-flex flex-column justify-content-center">
          <span class="text-center text-white fw-bold font-size-13">موجودی</span>
          <span class="text-center text-white fw-bold mt-2 font-size-13">{{ number_format($available) }}</span>
          <span class="text-center text-white fw-bold mt-2 font-size-13">تومان</span>
        </div>
      </div>
      <form wire:submit.prevent="requestSettlement">
        <div>
          <div class="w-100 position-relative mt-4">
            <label for="visit_fee" class="label-top-input-special-takhasos">مبلغ ویزیت (تومان)</label>
            <input type="text" id="visit_fee" wire:model.defer="visit_fee"
              class="form-control h-50 border-radius-4 w-100 text-center" value="{{ $formatted_visit_fee }}">
            @error('visit_fee')
              <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>
          <div class="w-100 position-relative mt-4">
            <label for="card_number" class="label-top-input-special-takhasos">شماره کارت</label>
            <input type="text" id="card_number" wire:model.defer="card_number"
              class="form-control h-50 border-radius-4 w-100 text-right" placeholder="1234-1234-1234-1234">
            @error('card_number')
              <span class="text-danger">{{ $message }}</span>
            @enderror
          </div>
        </div>
        <div class="w-100 mt-3">
          <button type="submit" class="btn my-btn-primary h-50 d-flex justify-content-center w-100">درخواست تسویه
            حساب</button>
        </div>
      </form>
    </div>
  </div>

  <div class="mt-3 w-100">
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white p-4 border-bottom">
        <div class="d-flex align-items-center justify-content-between w-100">
          <div class="d-flex align-items-center">
            <span class="fw-bold">درخواست‌های من</span>
            <span class="ms-2" data-bs-toggle="tooltip" data-bs-placement="top"
              title="موجودی قابل برداشت شامل هزینه‌های نوبت‌های حضوری ویزیت‌شده و مشاوره‌های آنلاین تکمیل‌شده (پاسخ‌داده‌شده) است که هنوز تسویه نشده‌اند.">
              <img src="{{ asset('dr-assets/icons/help.svg') }}" alt="" srcset="">
            </span>
          </div>
          <!-- Mobile Toggle Button -->
          <button class="btn btn-link text-muted p-0 d-md-none mobile-toggle-btn" type="button"
            @click="mobileSearchOpen = !mobileSearchOpen" :aria-expanded="mobileSearchOpen">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              class="toggle-icon" :class="{ 'rotate-180': mobileSearchOpen }">
              <path d="M6 9l6 6 6-6" />
            </svg>
          </button>
        </div>
      </div>
      <!-- Mobile Collapsible Section -->
      <div x-show="mobileSearchOpen" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform -translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform -translate-y-2" class="d-md-block">
        <div class="card-body p-4">
          <!-- Desktop Table View -->
          <div class="table-responsive d-none d-md-block">
            <table class="table table-light align-middle">
              <thead class="table-light">
                <tr>
                  <th class="text-center">ردیف</th>
                  <th class="text-center">کاربر</th>
                  <th class="text-center">مبلغ</th>
                  <th class="text-center">وضعیت</th>
                  <th class="text-center">تاریخ درخواست</th>
                  <th class="text-center">عملیات</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($requests as $index => $request)
                  <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $request->doctor->first_name . ' ' . $request->doctor->last_name }}</td>
                    <td class="text-center">{{ number_format($request->amount) }} تومان</td>
                    <td class="text-center">
                      @if ($request->status === 'pending')
                        <span class="badge bg-primary rounded-pill px-3 py-2">در انتظار ارائه خدمت</span>
                      @elseif ($request->status === 'available')
                        <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2">قابل برداشت</span>
                      @elseif ($request->status === 'requested')
                        <span class="badge bg-warning rounded-pill px-3 py-2">درخواست‌شده</span>
                      @elseif ($request->status === 'paid')
                        <span class="badge bg-success rounded-pill px-3 py-2">پرداخت‌شده</span>
                      @endif
                    </td>
                    <td class="text-center">
                      {{ \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($request->requested_at))->format('Y/m/d') }}
                    </td>
                    <td class="text-center">
                      <button class="btn btn-outline-danger btn-sm rounded-circle delete-transaction"
                        data-id="{{ $request->id }}">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2">
                          <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                        </svg>
                      </button>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="text-center text-muted py-4">موردی ثبت نشده است</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <!-- Mobile/Tablet Cards View -->
          <div class="d-md-none">
            @forelse ($requests as $index => $request)
              <div class="card mb-3 shadow-sm">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="card-title mb-0">درخواست #{{ $index + 1 }}</h6>
                    <button class="btn btn-outline-danger btn-sm rounded-circle delete-transaction"
                      data-id="{{ $request->id }}">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                      </svg>
                    </button>
                  </div>
                  <div class="row g-2">
                    <div class="col-6">
                      <small class="text-muted">کاربر:</small>
                      <div class="fw-bold">{{ $request->doctor->first_name . ' ' . $request->doctor->last_name }}
                      </div>
                    </div>
                    <div class="col-6">
                      <small class="text-muted">مبلغ:</small>
                      <div class="fw-bold">{{ number_format($request->amount) }} تومان</div>
                    </div>
                    <div class="col-6">
                      <small class="text-muted">وضعیت:</small>
                      <div>
                        @if ($request->status === 'pending')
                          <span class="badge bg-primary rounded-pill px-2 py-1 small">در انتظار ارائه خدمت</span>
                        @elseif ($request->status === 'available')
                          <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 small">قابل
                            برداشت</span>
                        @elseif ($request->status === 'requested')
                          <span class="badge bg-warning rounded-pill px-2 py-1 small">درخواست‌شده</span>
                        @elseif ($request->status === 'paid')
                          <span class="badge bg-success rounded-pill px-2 py-1 small">پرداخت‌شده</span>
                        @endif
                      </div>
                    </div>
                    <div class="col-6">
                      <small class="text-muted">تاریخ درخواست:</small>
                      <div class="fw-bold">
                        {{ \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($request->requested_at))->format('Y/m/d') }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            @empty
              <div class="text-center text-muted py-4">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2" class="mb-2">
                  <path d="M5 12h14M12 5l7 7-7 7" />
                </svg>
                <p>موردی ثبت نشده است</p>
              </div>
            @endforelse
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('livewire:init', () => {
      toastr.options = {
        positionClass: 'toast-top-right',
        timeOut: 3000,
      };

      Livewire.on('toast', (event) => {
        if (event.type === 'success') {
          toastr.success(event.message);
        } else if (event.type === 'error') {
          toastr.error(event.message);
        }
      });

      // فقط برای آپدیت مقدار بدون کاما
      const visitFeeInput = document.getElementById('visit_fee');
      visitFeeInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^0-9]/g, ''); // فقط اعداد
        @this.set('visit_fee', value); // مقدار بدون کاما به Livewire
      });

      // فرمت شماره کارت
      const cardNumberInput = document.getElementById('card_number');
      if (cardNumberInput.value) {
        let value = cardNumberInput.value.replace(/[^0-9]/g, '');
        cardNumberInput.value = formatCardNumber(value);
      }
      cardNumberInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^0-9]/g, '');
        if (value.length > 16) value = value.slice(0, 16);
        e.target.value = formatCardNumber(value);
        @this.set('card_number', e.target.value);
      });

      cardNumberInput.addEventListener('keypress', function(e) {
        if (e.target.value.length >= 19) e.preventDefault();
      });

      function formatCardNumber(value) {
        if (!value) return '';
        let formatted = '';
        for (let i = 0; i < value.length; i++) {
          if (i > 0 && i % 4 === 0) formatted += '-';
          formatted += value[i];
        }
        return formatted;
      }
    });

    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('.delete-transaction').forEach(button => {
        button.addEventListener('click', function(e) {
          e.preventDefault();
          const requestId = this.getAttribute('data-id');

          Swal.fire({
            title: 'حذف درخواست',
            text: "آیا مطمئن هستید که می‌خواهید این درخواست را حذف کنید؟",
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'بله',
            cancelButtonText: 'خیر'
          }).then((result) => {
            if (result.isConfirmed) {
              @this.call('deleteRequest', requestId);
            }
          });
        });
      });
    });
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function(tooltipTriggerEl) {
          new bootstrap.Tooltip(tooltipTriggerEl);
        });
      }
    });
  </script>
</div>

<div class="payment-setting-content w-100 d-flex justify-content-center mt-4 flex-wrap">
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
          <span class="text-center text-white font-weight-bold font-size-13">کل در آمد</span>
          <span
            class="text-center text-white font-weight-bold mt-2 font-size-13">{{ number_format($totalIncome) }}</span>
          <span class="text-center text-white font-weight-bold mt-2 font-size-13">تومان</span>
        </div>
        <div class="d-flex flex-column justify-content-center">
          <span class="text-center text-white font-weight-bold font-size-13">پرداخت شده</span>
          <span class="text-center text-white font-weight-bold mt-2 font-size-13">{{ number_format($paid) }}</span>
          <span class="text-center text-white font-weight-bold mt-2 font-size-13">تومان</span>
        </div>
        <div class="d-flex flex-column justify-content-center">
          <span class="text-center text-white font-weight-bold font-size-13">موجودی</span>
          <span class="text-center text-white font-weight-bold mt-2 font-size-13">{{ number_format($available) }}</span>
          <span class="text-center text-white font-weight-bold mt-2 font-size-13">تومان</span>
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
          <button type="submit" class="btn my-btn-primary h-50 w-100">درخواست تسویه حساب</button>
        </div>
      </form>
    </div>
  </div>

  <div class="mt-3 w-100">
    <div class="alert alert-warning">
      <p><i class="fa fa-info-circle fa-2x"></i> صرفاً مبالغ هزینه‌های نوبت حضوری که تاریخ آنها رسیده است و مشاوره‌های
        آنلاینی که پاسخ داده شده‌اند، قابل برداشت می‌باشند و مابقی در حالت انتظار می‌باشند.</p>
    </div>
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-white p-4 border-bottom">
        <span class="font-weight-bold">درخواست‌های من</span>
      </div>
      <div class="card-body p-4">
        <div class="table-responsive">
          <table class="table table-hover table-bordered align-middle">
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
                      <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2">
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
            title: 'آیا مطمئن هستید؟',
            text: "این تراکنش حذف خواهد شد و قابل بازگشت نیست!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'بله، حذف کن!',
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
</div>

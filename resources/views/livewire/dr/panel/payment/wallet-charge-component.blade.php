<div class="wallet-charge-content w-100 d-flex justify-content-center mt-4">
  <div class="wallet-charge-wrapper p-4 bg-white rounded shadow" style="max-width: 700px; width: 100%;">
    <!-- هدر شیک -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="font-weight-bold text-dark m-0">شارژ کیف پول</h4>
      <span class="badge bg-primary text-white p-2">موجودی شما: {{ number_format($availableAmount) }} تومان</span>
    </div>

    <!-- فرم شارژ -->
    <form wire:submit.prevent="chargeWallet" class="bg-light p-2 rounded mb-4">
      <div class="row">
        <div class="col-md-12 mb-3 mt-3">
          <label for="displayAmount" class="form-label fw-bold label-top-input-special-takhasos">مبلغ شارژ
            (تومان)</label>
          <input type="text" id="displayAmount" wire:model.live="displayAmount"
            class="form-control border-0 shadow-sm text-center h-50" placeholder="مبلغ را وارد کنید">
        </div>
        @error('amount')
          <span class="text-danger mb-2">{{ $message }}</span>
        @enderror
      </div>
      <button type="submit" class="btn btn-success w-100 py-2 fw-bold" wire:loading.attr="disabled">
        <span wire:loading.remove>شارژ کیف پول</span>
        <span wire:loading wire:target="chargeWallet">
          <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
          در حال انتقال به درگاه پرداخت...
        </span>
      </button>
    </form>

    <!-- جدول تراکنش‌ها -->
    <div class="card mt-4">
      <div class="card-header bg-dark text-white">
        <span>تراکنش‌های کیف پول</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-hover m-0">
            <thead class="bg-light">
              <tr>
                <th>ردیف</th>
                <th>مبلغ</th>
                <th>وضعیت</th>
                <th>نوع</th>
                <th>تاریخ</th>
                <th>شرح</th>
                <th>عملیات</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($transactions as $index => $transaction)
                <tr>
                  <td>{{ $index + 1 }}</td>
                  <td>{{ number_format($transaction->amount) }} تومان</td>
                  <td>
                    @switch($transaction->status)
                      @case('pending')
                        <span class="badge bg-primary">در انتظار</span>
                      @break

                      @case('available')
                        <span class="badge bg-success">قابل برداشت</span>
                      @break

                      @case('requested')
                        <span class="badge bg-warning">درخواست‌شده</span>
                      @break

                      @case('paid')
                        <span class="badge bg-info">پرداخت‌شده</span>
                      @break
                    @endswitch
                  </td>
                  <td>
                    @switch($transaction->type)
                      @case('online')
                        مشاوره آنلاین
                      @break

                      @case('in_person')
                        نوبت حضوری
                      @break

                      @case('charge')
                        شارژ کیف پول
                      @break
                    @endswitch
                  </td>
                  <td>
                    {{ $transaction->registered_at ? \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($transaction->registered_at))->format('Y/m/d H:i') : '-' }}
                  </td>
                  <td>{{ $transaction->description ?? '-' }}</td>
                  <td>
                    <button class="btn btn-light btn-sm delete-transaction rounded-circle"
                      data-id="{{ $transaction->id }}"><img src="{{ asset('dr-assets/icons/trash.svg') }}"
                        alt="trash" srcset=""></button>
                  </td>
                </tr>
                @empty
                  <tr>
                    <td colspan="7" class="text-center">هیچ تراکنشی ثبت نشده است</td>
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

        Livewire.on('redirect-to-gateway', (event) => {
          window.location.href = event.url;
        });

        const displayAmountInput = document.getElementById('displayAmount');
        displayAmountInput.addEventListener('input', function(e) {
          let value = e.target.value.replace(/[^0-9]/g, ''); // فقط اعداد
          e.target.value = value ? Number(value).toLocaleString('en-US') : '';
          @this.set('displayAmount', e.target.value);
        });

        // مدیریت دکمه حذف با SweetAlert
        document.querySelectorAll('.delete-transaction').forEach(button => {
          button.addEventListener('click', function(e) {
            e.preventDefault();
            const transactionId = this.getAttribute('data-id');

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
                @this.call('deleteTransaction', transactionId);
              }
            });
          });
        });
      });
    </script>
  </div>
  </div>

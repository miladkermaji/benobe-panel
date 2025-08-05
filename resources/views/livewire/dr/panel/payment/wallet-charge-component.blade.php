<div class="wallet-charge-content w-100 d-flex justify-content-center mt-4" x-data="{ mobileSearchOpen: false }">
  <div class="wallet-charge-wrapper p-4 bg-white rounded shadow" style="max-width: 700px; width: 100%;">
    <!-- هدر شیک -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="fw-bold text-dark m-0">شارژ کیف پول</h4>
      <span class="badge bg-primary text-white p-2">موجودی شما: {{ number_format($availableAmount) }} تومان</span>
    </div>

    <!-- فرم شارژ -->
    <form wire:submit.prevent="chargeWallet" class="bg-light p-2 rounded mb-4">
      <div class="row">
        <div class="col-md-12 mb-3 mt-3 position-relative">
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
        <div class="d-flex align-items-center justify-content-between w-100">
          <span>تراکنش‌های کیف پول</span>
          <!-- Mobile Toggle Button -->
          <button class="btn btn-link text-white p-0 d-md-none mobile-toggle-btn" type="button"
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
        <div class="card-body p-0">
          <!-- Desktop Table View -->
          <div class="table-responsive d-none d-md-block">
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
                      <button class="btn-light  delete-transaction rounded-circle" data-id="{{ $transaction->id }}"><img
                          src="{{ asset('dr-assets/icons/trash.svg') }}" alt="trash" srcset=""></button>
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

            <!-- Mobile/Tablet Cards View -->
            <div class="d-md-none p-3">
              @forelse ($transactions as $index => $transaction)
                <div class="card mb-3 shadow-sm">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                      <h6 class="card-title mb-0">تراکنش #{{ $index + 1 }}</h6>
                      <button class="btn-light  delete-transaction rounded-circle" data-id="{{ $transaction->id }}">
                        <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="trash" srcset="">
                      </button>
                    </div>
                    <div class="row g-2">
                      <div class="col-6">
                        <small class="text-muted">مبلغ:</small>
                        <div class="fw-bold">{{ number_format($transaction->amount) }} تومان</div>
                      </div>
                      <div class="col-6">
                        <small class="text-muted">وضعیت:</small>
                        <div>
                          @switch($transaction->status)
                            @case('pending')
                              <span class="badge bg-primary small">در انتظار</span>
                            @break

                            @case('available')
                              <span class="badge bg-success small">قابل برداشت</span>
                            @break

                            @case('requested')
                              <span class="badge bg-warning small">درخواست‌شده</span>
                            @break

                            @case('paid')
                              <span class="badge bg-info small">پرداخت‌شده</span>
                            @break
                          @endswitch
                        </div>
                      </div>
                      <div class="col-6">
                        <small class="text-muted">نوع:</small>
                        <div class="fw-bold">
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
                        </div>
                      </div>
                      <div class="col-6">
                        <small class="text-muted">تاریخ:</small>
                        <div class="fw-bold">
                          {{ $transaction->registered_at ? \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($transaction->registered_at))->format('Y/m/d H:i') : '-' }}
                        </div>
                      </div>
                      <div class="col-12">
                        <small class="text-muted">شرح:</small>
                        <div class="fw-bold">{{ $transaction->description ?? '-' }}</div>
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
                    <p>هیچ تراکنشی ثبت نشده است</p>
                  </div>
                @endforelse
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

            // بررسی پارامتر from_payment یا transaction_id
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('from_payment') === 'success' || urlParams.get('transaction_id')) {
              @this.dispatch('toast', {
                message: 'کیف‌پول شما با موفقیت شارژ شد.',
                type: 'success'
              });
            } else if (urlParams.get('from_payment') === 'error') {
              @this.dispatch('toast', {
                message: 'پرداخت ناموفق بود.',
                type: 'error'
              });
            }

            // مدیریت دکمه حذف با SweetAlert
            document.querySelectorAll('.delete-transaction').forEach(button => {
              button.addEventListener('click', function(e) {
                e.preventDefault();
                const transactionId = this.getAttribute('data-id');

                Swal.fire({
                  title: 'حذف تراکنش',
                  text: "آیا مطمئن هستید که می‌خواهید این تراکنش را حذف کنید؟",
                  confirmButtonColor: '#ef4444',
                  cancelButtonColor: '#6b7280',
                  confirmButtonText: 'بله',
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

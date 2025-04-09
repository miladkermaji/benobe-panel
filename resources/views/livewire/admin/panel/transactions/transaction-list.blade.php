<div class="container-fluid py-2" dir="rtl">
  <div
    class="glass-header text-white p-3 rounded-3 mb-5 shadow-lg d-flex justify-content-between align-items-center flex-wrap gap-3">
    <h1 class="m-0 h3 font-thin flex-grow-1" style="min-width: 200px;">مدیریت تراکنش‌ها</h1>
    <div class="input-group flex-grow-1 position-relative" style="max-width: 400px;">
      <input type="text" class="form-control border-0 shadow-none bg-white text-dark ps-5 rounded-3"
        wire:model.live="search" placeholder="جستجو در تراکنش‌ها..." style="padding-right: 23px">
      <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-3" style="z-index: 5;right: 5px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
          <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
        </svg>
      </span>
    </div>
    <div class="d-flex gap-2 flex-shrink-0 flex-wrap justify-content-center mt-md-2 buttons-container">
      <a href="{{ route('admin.panel.transactions.create') }}"
        class="btn btn-gradient-success rounded-pill px-4 d-flex align-items-center gap-2">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 5v14M5 12h14" />
        </svg>
        <span>افزودن تراکنش</span>
      </a>
    </div>
  </div>

  <div class="container-fluid px-0">
    <div class="card shadow-sm">
      <div class="card-body p-0">
        @if ($readyToLoad)
          <!-- کاربران -->
          <div class="mb-4">
            <h5 class="p-3 bg-gradient-primary text-white">کاربران</h5>
            @forelse ($users as $data)
              <div class="entity-toggle border-bottom">
                <div class="d-flex justify-content-between align-items-center p-3 cursor-pointer"
                  wire:click="toggleEntity('user', {{ $data['entity']->id }})">
                  <div class="d-flex align-items-center gap-3">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                      stroke-width="2">
                      <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                      <circle cx="12" cy="7" r="4" />
                    </svg>
                    <span class="fw-bold">{{ $data['entity']->full_name }} ({{ $data['entity']->mobile }})</span>
                    <span class="badge bg-label-primary">{{ $data['totalTransactions'] }} تراکنش</span>
                  </div>
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                    stroke-width="2"
                    class="transition-transform {{ in_array('user-' . $data['entity']->id, $expandedEntities) ? 'rotate-180' : '' }}">
                    <path d="M6 9l6 6 6-6" />
                  </svg>
                </div>
                @if (in_array('user-' . $data['entity']->id, $expandedEntities))
                  <div class="table-responsive text-nowrap p-3 bg-light">
                    <table class="table table-bordered table-hover w-100 m-0">
                      <thead class="glass-header text-white">
                        <tr>
                          <th class="text-center align-middle" style="width: 70px;">ردیف</th>
                          <th class="align-middle">مبلغ (تومان)</th>
                          <th class="align-middle">درگاه</th>
                          <th class="align-middle">وضعیت</th>
                          <th class="align-middle">تاریخ</th>
                          <th class="text-center align-middle" style="width: 150px;">عملیات</th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse ($data['transactions'] as $index => $transaction)
                          <tr>
                            <td class="text-center align-middle">
                              {{ ($data['currentPage'] - 1) * $transactionsPerPage + $index + 1 }}</td>
                            <td class="align-middle">{{ number_format($transaction->amount) }}</td>
                            <td class="align-middle">{{ $transaction->gateway }}</td>
                            <td class="align-middle">
                              <span
                                class="badge {{ $transaction->status === 'paid' ? 'bg-label-success' : ($transaction->status === 'failed' ? 'bg-label-danger' : 'bg-label-warning') }}">
                                {{ $transaction->status === 'paid' ? 'پرداخت‌شده' : ($transaction->status === 'failed' ? 'ناموفق' : 'در انتظار') }}
                              </span>
                            </td>
                            <td class="align-middle">{{ $transaction->jalali_created_at }}</td>
                            <td class="text-center align-middle">
                              <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('admin.panel.transactions.edit', $transaction->id) }}"
                                  class="btn btn-gradient-success rounded-pill px-3">
                                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path
                                      d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                  </svg>
                                </a>
                                <button wire:click="confirmDelete({{ $transaction->id }})"
                                  class="btn btn-gradient-danger rounded-pill px-3">
                                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path
                                      d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                                  </svg>
                                </button>
                              </div>
                            </td>
                          </tr>
                        @empty
                          <tr>
                            <td colspan="6" class="text-center py-5">هیچ تراکنشی یافت نشد.</td>
                          </tr>
                        @endforelse
                      </tbody>
                    </table>
                    @if ($data['totalTransactions'] > $transactionsPerPage)
                      <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                          نمایش {{ ($data['currentPage'] - 1) * $transactionsPerPage + 1 }} تا
                          {{ min($data['currentPage'] * $transactionsPerPage, $data['totalTransactions']) }} از
                          {{ $data['totalTransactions'] }} تراکنش
                        </div>
                        <nav>
                          <ul class="pagination mb-0">
                            <li class="page-item {{ $data['currentPage'] == 1 ? 'disabled' : '' }}">
                              <button class="page-link"
                                wire:click="setEntityPage('user-{{ $data['entity']->id }}', {{ $data['currentPage'] - 1 }})">قبلی</button>
                            </li>
                            @for ($i = 1; $i <= $data['lastPage']; $i++)
                              <li class="page-item {{ $data['currentPage'] == $i ? 'active' : '' }}">
                                <button class="page-link"
                                  wire:click="setEntityPage('user-{{ $data['entity']->id }}', {{ $i }})">{{ $i }}</button>
                              </li>
                            @endfor
                            <li class="page-item {{ $data['currentPage'] == $data['lastPage'] ? 'disabled' : '' }}">
                              <button class="page-link"
                                wire:click="setEntityPage('user-{{ $data['entity']->id }}', {{ $data['currentPage'] + 1 }})">بعدی</button>
                            </li>
                          </ul>
                        </nav>
                      </div>
                    @endif
                  </div>
                @endif
              </div>
            @empty
              <p class="text-center py-3">هیچ کاربری با تراکنش یافت نشد.</p>
            @endforelse
            @if ($totalUsers > $perPage)
              <div class="d-flex justify-content-between align-items-center mt-3 p-3">
                <div>
                  نمایش {{ ($page - 1) * $perPage + 1 }} تا {{ min($page * $perPage, $totalUsers) }} از
                  {{ $totalUsers }} کاربر
                </div>
                {{ $users->links() }}
              </div>
            @endif
          </div>

          <!-- دکترها -->
          <div class="mb-4">
            <h5 class="p-3 bg-gradient-primary text-white">دکترها</h5>
            @forelse ($doctors as $data)
              <div class="entity-toggle border-bottom">
                <div class="d-flex justify-content-between align-items-center p-3 cursor-pointer"
                  wire:click="toggleEntity('doctor', {{ $data['entity']->id }})">
                  <div class="d-flex align-items-center gap-3">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                      stroke-width="2">
                      <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                      <circle cx="12" cy="7" r="4" />
                    </svg>
                    <span class="fw-bold">{{ $data['entity']->full_name }} ({{ $data['entity']->mobile }})</span>
                    <span class="badge bg-label-primary">{{ $data['totalTransactions'] }} تراکنش</span>
                  </div>
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                    stroke-width="2"
                    class="transition-transform {{ in_array('doctor-' . $data['entity']->id, $expandedEntities) ? 'rotate-180' : '' }}">
                    <path d="M6 9l6 6 6-6" />
                  </svg>
                </div>
                @if (in_array('doctor-' . $data['entity']->id, $expandedEntities))
                  <div class="table-responsive text-nowrap p-3 bg-light">
                    <table class="table table-bordered table-hover w-100 m-0">
                      <thead class="glass-header text-white">
                        <tr>
                          <th class="text-center align-middle" style="width: 70px;">ردیف</th>
                          <th class="align-middle">مبلغ (تومان)</th>
                          <th class="align-middle">درگاه</th>
                          <th class="align-middle">وضعیت</th>
                          <th class="align-middle">تاریخ</th>
                          <th class="text-center align-middle" style="width: 150px;">عملیات</th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse ($data['transactions'] as $index => $transaction)
                          <tr>
                            <td class="text-center align-middle">
                              {{ ($data['currentPage'] - 1) * $transactionsPerPage + $index + 1 }}</td>
                            <td class="align-middle">{{ number_format($transaction->amount) }}</td>
                            <td class="align-middle">{{ $transaction->gateway }}</td>
                            <td class="align-middle">
                              <span
                                class="badge {{ $transaction->status === 'paid' ? 'bg-label-success' : ($transaction->status === 'failed' ? 'bg-label-danger' : 'bg-label-warning') }}">
                                {{ $transaction->status === 'paid' ? 'پرداخت‌شده' : ($transaction->status === 'failed' ? 'ناموفق' : 'در انتظار') }}
                              </span>
                            </td>
                            <td class="align-middle">{{ $transaction->jalali_created_at }}</td>
                            <td class="text-center align-middle">
                              <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('admin.panel.transactions.edit', $transaction->id) }}"
                                  class="btn btn-gradient-success rounded-pill px-3">
                                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path
                                      d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                  </svg>
                                </a>
                                <button wire:click="confirmDelete({{ $transaction->id }})"
                                  class="btn btn-gradient-danger rounded-pill px-3">
                                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path
                                      d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                                  </svg>
                                </button>
                              </div>
                            </td>
                          </tr>
                        @empty
                          <tr>
                            <td colspan="6" class="text-center py-5">هیچ تراکنشی یافت نشد.</td>
                          </tr>
                        @endforelse
                      </tbody>
                    </table>
                    @if ($data['totalTransactions'] > $transactionsPerPage)
                      <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                          نمایش {{ ($data['currentPage'] - 1) * $transactionsPerPage + 1 }} تا
                          {{ min($data['currentPage'] * $transactionsPerPage, $data['totalTransactions']) }} از
                          {{ $data['totalTransactions'] }} تراکنش
                        </div>
                        <nav>
                          <ul class="pagination mb-0">
                            <li class="page-item {{ $data['currentPage'] == 1 ? 'disabled' : '' }}">
                              <button class="page-link"
                                wire:click="setEntityPage('doctor-{{ $data['entity']->id }}', {{ $data['currentPage'] - 1 }})">قبلی</button>
                            </li>
                            @for ($i = 1; $i <= $data['lastPage']; $i++)
                              <li class="page-item {{ $data['currentPage'] == $i ? 'active' : '' }}">
                                <button class="page-link"
                                  wire:click="setEntityPage('doctor-{{ $data['entity']->id }}', {{ $i }})">{{ $i }}</button>
                              </li>
                            @endfor
                            <li class="page-item {{ $data['currentPage'] == $data['lastPage'] ? 'disabled' : '' }}">
                              <button class="page-link"
                                wire:click="setEntityPage('doctor-{{ $data['entity']->id }}', {{ $data['currentPage'] + 1 }})">بعدی</button>
                            </li>
                          </ul>
                        </nav>
                      </div>
                    @endif
                  </div>
                @endif
              </div>
            @empty
              <p class="text-center py-3">هیچ دکتری با تراکنش یافت نشد.</p>
            @endforelse
            @if ($totalDoctors > $perPage)
              <div class="d-flex justify-content-between align-items-center mt-3 p-3">
                <div>
                  نمایش {{ ($page - 1) * $perPage + 1 }} تا {{ min($page * $perPage, $totalDoctors) }} از
                  {{ $totalDoctors }} دکتر
                </div>
                {{ $doctors->links() }}
              </div>
            @endif
          </div>

          <!-- منشی‌ها -->
          <div class="mb-4">
            <h5 class="p-3 bg-gradient-primary text-white">منشی‌ها</h5>
            @forelse ($secretaries as $data)
              <div class="entity-toggle border-bottom">
                <div class="d-flex justify-content-between align-items-center p-3 cursor-pointer"
                  wire:click="toggleEntity('secretary', {{ $data['entity']->id }})">
                  <div class="d-flex align-items-center gap-3">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                      stroke-width="2">
                      <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                      <circle cx="12" cy="7" r="4" />
                    </svg>
                    <span class="fw-bold">{{ $data['entity']->first_name . ' ' . $data['entity']->last_name }}
                      ({{ $data['entity']->mobile }})
                    </span>
                    <span class="badge bg-label-primary">{{ $data['totalTransactions'] }} تراکنش</span>
                  </div>
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                    stroke-width="2"
                    class="transition-transform {{ in_array('secretary-' . $data['entity']->id, $expandedEntities) ? 'rotate-180' : '' }}">
                    <path d="M6 9l6 6 6-6" />
                  </svg>
                </div>
                @if (in_array('secretary-' . $data['entity']->id, $expandedEntities))
                  <div class="table-responsive text-nowrap p-3 bg-light">
                    <table class="table table-bordered table-hover w-100 m-0">
                      <thead class="glass-header text-white">
                        <tr>
                          <th class="text-center align-middle" style="width: 70px;">ردیف</th>
                          <th class="align-middle">مبلغ (تومان)</th>
                          <th class="align-middle">درگاه</th>
                          <th class="align-middle">وضعیت</th>
                          <th class="align-middle">تاریخ</th>
                          <th class="text-center align-middle" style="width: 150px;">عملیات</th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse ($data['transactions'] as $index => $transaction)
                          <tr>
                            <td class="text-center align-middle">
                              {{ ($data['currentPage'] - 1) * $transactionsPerPage + $index + 1 }}</td>
                            <td class="align-middle">{{ number_format($transaction->amount) }}</td>
                            <td class="align-middle">{{ $transaction->gateway }}</td>
                            <td class="align-middle">
                              <span
                                class="badge {{ $transaction->status === 'paid' ? 'bg-label-success' : ($transaction->status === 'failed' ? 'bg-label-danger' : 'bg-label-warning') }}">
                                {{ $transaction->status === 'paid' ? 'پرداخت‌شده' : ($transaction->status === 'failed' ? 'ناموفق' : 'در انتظار') }}
                              </span>
                            </td>
                            <td class="align-middle">{{ $transaction->jalali_created_at }}</td>
                            <td class="text-center align-middle">
                              <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('admin.panel.transactions.edit', $transaction->id) }}"
                                  class="btn btn-gradient-success rounded-pill px-3">
                                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path
                                      d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                  </svg>
                                </a>
                                <button wire:click="confirmDelete({{ $transaction->id }})"
                                  class="btn btn-gradient-danger rounded-pill px-3">
                                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path
                                      d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                                  </svg>
                                </button>
                              </div>
                            </td>
                          </tr>
                        @empty
                          <tr>
                            <td colspan="6" class="text-center py-5">هیچ تراکنشی یافت نشد.</td>
                          </tr>
                        @endforelse
                      </tbody>
                    </table>
                    @if ($data['totalTransactions'] > $transactionsPerPage)
                      <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                          نمایش {{ ($data['currentPage'] - 1) * $transactionsPerPage + 1 }} تا
                          {{ min($data['currentPage'] * $transactionsPerPage, $data['totalTransactions']) }} از
                          {{ $data['totalTransactions'] }} تراکنش
                        </div>
                        <nav>
                          <ul class="pagination mb-0">
                            <li class="page-item {{ $data['currentPage'] == 1 ? 'disabled' : '' }}">
                              <button class="page-link"
                                wire:click="setEntityPage('secretary-{{ $data['entity']->id }}', {{ $data['currentPage'] - 1 }})">قبلی</button>
                            </li>
                            @for ($i = 1; $i <= $data['lastPage']; $i++)
                              <li class="page-item {{ $data['currentPage'] == $i ? 'active' : '' }}">
                                <button class="page-link"
                                  wire:click="setEntityPage('secretary-{{ $data['entity']->id }}', {{ $i }})">{{ $i }}</button>
                              </li>
                            @endfor
                            <li class="page-item {{ $data['currentPage'] == $data['lastPage'] ? 'disabled' : '' }}">
                              <button class="page-link"
                                wire:click="setEntityPage('secretary-{{ $data['entity']->id }}', {{ $data['currentPage'] + 1 }})">بعدی</button>
                            </li>
                          </ul>
                        </nav>
                      </div>
                    @endif
                  </div>
                @endif
              </div>
            @empty
              <p class="text-center py-3">هیچ منشی‌ای با تراکنش یافت نشد.</p>
            @endforelse
            @if ($totalSecretaries > $perPage)
              <div class="d-flex justify-content-between align-items-center mt-3 p-3">
                <div>
                  نمایش {{ ($page - 1) * $perPage + 1 }} تا {{ min($page * $perPage, $totalSecretaries) }} از
                  {{ $totalSecretaries }} منشی
                </div>
                {{ $secretaries->links() }}
              </div>
            @endif
          </div>
        @else
          <div class="text-center py-5">در حال بارگذاری تراکنش‌ها...</div>
        @endif
      </div>
    </div>
  </div>

  <style>
    .glass-header {
      background: linear-gradient(90deg, #6b7280, #374151);
    }

    .btn-gradient-success {
      background: linear-gradient(90deg, #10b981, #059669);
      border: none;
      color: white;
    }

    .btn-gradient-danger {
      background: linear-gradient(90deg, #ef4444, #dc2626);
      border: none;
      color: white;
    }

    .btn-gradient-success:hover,
    .btn-gradient-danger:hover {
      transform: translateY(-2px);
    }

    .card {
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .entity-toggle {
      transition: all 0.3s ease;
    }

    .entity-toggle:hover {
      background: #f9fafb;
    }

    .cursor-pointer {
      cursor: pointer;
    }

    .transition-transform {
      transition: transform 0.3s ease;
    }

    .rotate-180 {
      transform: rotate(180deg);
    }

    .bg-label-primary {
      background: #e5e7eb;
      color: #374151;
    }

    .bg-label-success {
      background: #d1fae5;
      color: #059669;
    }

    .bg-label-danger {
      background: #fee2e2;
      color: #dc2626;
    }

    .bg-label-warning {
      background: #fef3c7;
      color: #d97706;
    }
  </style>

  <script>
    document.addEventListener('livewire:initialized', function() {
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });

      Livewire.on('confirm-delete', (event) => {
        Swal.fire({
          title: 'حذف تراکنش',
          text: 'آیا مطمئن هستید که می‌خواهید این تراکنش را حذف کنید؟',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#ef4444',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'بله، حذف کن',
          cancelButtonText: 'خیر'
        }).then((result) => {
          if (result.isConfirmed) {
            Livewire.dispatch('deleteTransactionConfirmed', {
              id: event.id
            });
          }
        });
      });
    });
  </script>
</div>

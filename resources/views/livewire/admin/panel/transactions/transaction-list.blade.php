@if (session('success'))
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      toastr.success(@json(session('success')));
    });
  </script>
@endif
<div class="transaction-list-container">
  <div class="container py-2 mt-3" dir="rtl" wire:init="loadTransactions">
    <div class="glass-header text-white p-2  shadow-lg">
      <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3 w-100">
        <div class="d-flex flex-column flex-md-row gap-2 w-100 align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-3">
            <h1 class="m-0 h4 font-thin text-nowrap mb-3 mb-md-0">مدیریت تراکنش‌ها</h1>
          </div>
          <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2 w-100">
            <div class="d-flex gap-2 flex-shrink-0 justify-content-center w-100 flex-column flex-md-row">
              <div class="search-container position-relative flex-grow-1 mb-2 mb-md-0 w-100">
                <input type="text"
                  class="form-control search-input border-0 shadow-none bg-white text-dark ps-4 rounded-2 text-start w-100"
                  wire:model.live="search" placeholder="جستجو بر اساس نام، موبایل، مبلغ، درگاه..."
                  style="padding-right: 20px; text-align: right; direction: rtl; width: 100%; max-width: 400px; min-width: 200px;">
                <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-2"
                  style="z-index: 5; top: 50%; right: 8px;">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                    stroke-width="2">
                    <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
                  </svg>
                </span>
              </div>
              <select class="form-select form-select-sm w-100 mb-2 mb-md-0" style="min-width: 0;"
                wire:model.live="statusFilter">
                <option value="">همه وضعیت‌ها</option>
                <option value="paid">پرداخت‌شده</option>
                <option value="failed">ناموفق</option>
                <option value="pending">در انتظار</option>
              </select>
              <select class="form-select form-select-sm w-100 mb-2 mb-md-0" style="min-width: 0;"
                wire:model.live="entityTypeFilter">
                <option value="all">همه نوع‌ها</option>
                <option value="user">کاربران</option>
                <option value="doctor">پزشکان</option>
                <option value="secretary">منشی‌ها</option>
              </select>
              {{--   <a href="{{ route('admin.panel.transactions.create') }}"
                class="btn btn-gradient-success btn-gradient-success-576 rounded-1 px-3 py-1 d-flex align-items-center gap-1 w-100 w-md-auto justify-content-center justify-content-md-start">
                <svg style="transform: rotate(180deg)" width="14" height="14" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2">
                  <path d="M12 5v14M5 12h14" />
                </svg>
                <span>افزودن</span>
              </a> --}}
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="container-fluid px-0">
      <div class="card shadow-sm rounded-2">
        <div class="card-body p-0">
          <!-- Group Actions -->
          <div class="group-actions p-2 border-bottom" x-data="{ show: false }"
            x-show="$wire.selectedTransactions.length > 0 || $wire.applyToAllFiltered">
            <div class="d-flex align-items-center gap-2 justify-content-end">
              <select class="form-select form-select-sm" style="max-width: 200px;" wire:model="groupAction">
                <option value="">عملیات گروهی</option>
                <option value="delete">حذف انتخاب شده‌ها</option>
                <option value="status_paid">تغییر به پرداخت‌شده</option>
                <option value="status_failed">تغییر به ناموفق</option>
                <option value="status_pending">تغییر به در انتظار</option>
              </select>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="applyToAllFiltered" wire:model="applyToAllFiltered">
                <label class="form-check-label" for="applyToAllFiltered">
                  اعمال روی همه نتایج فیلترشده ({{ $totalFilteredCount ?? 0 }})
                </label>
              </div>
              <button class="btn btn-sm btn-primary" wire:click="executeGroupAction" wire:loading.attr="disabled">
                <span wire:loading.remove>اجرا</span>
                <span wire:loading>در حال اجرا...</span>
              </button>
            </div>
          </div>
          <!-- Desktop Table View -->
          <div class="d-none d-md-block">
            @if (
                ($entityTypeFilter === 'user' || $entityTypeFilter === 'all') &&
                    $users->filter(fn($d) => $d['transactions']->isNotEmpty())->isNotEmpty())
              <h5 class="p-3 glass-header text-white">کاربران</h5>
            @endif
            @if ($entityTypeFilter === 'user' || $entityTypeFilter === 'all')
              @foreach ($users as $data)
                @if ($data['transactions']->isNotEmpty())
                  <div class="entity-toggle border-bottom" x-data="{ open: false }">
                    <div class="d-flex justify-content-between align-items-center p-3 cursor-pointer"
                      @click="open = !open">
                      <div class="d-flex align-items-center gap-3">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                          stroke-width="2">
                          <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                          <circle cx="12" cy="7" r="4" />
                        </svg>
                        <span class="fw-bold">{{ $data['entity']->full_name }} ({{ $data['entity']->mobile }})</span>
                        <span class="badge bg-label-primary">{{ $data['totalTransactions'] }} تراکنش</span>
                      </div>
                      <svg :class="{ 'rotate-180': open }" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="#6b7280" stroke-width="2" style="transition: transform 0.2s;">
                        <path d="M6 9l6 6 6-6" />
                      </svg>
                    </div>
                    <div x-show="open" x-transition>
                      <div class="table-responsive text-nowrap p-3 bg-light">
                        <table class="table table-hover w-100 m-0">
                          <thead>
                            <tr>
                              <th class="text-center align-middle" style="width: 40px;">
                                <div class="d-flex justify-content-center align-items-center">
                                  <input type="checkbox" wire:model.live="selectAll"
                                    class="form-check-input m-0 align-middle">
                                </div>
                              </th>
                              <th class="text-center align-middle" style="width: 60px;">ردیف</th>
                              <th class="align-middle">مبلغ (تومان)</th>
                              <th class="align-middle">درگاه</th>
                              <th class="align-middle">وضعیت</th>
                              <th class="align-middle">تاریخ</th>
                              <th class="text-center align-middle" style="width: 120px;">عملیات</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach ($data['transactions'] as $index => $transaction)
                              <tr class="align-middle">
                                <td class="text-center">
                                  <div class="d-flex justify-content-center align-items-center">
                                    <input type="checkbox" wire:model.live="selectedTransactions"
                                      value="{{ $transaction->id }}" class="form-check-input m-0 align-middle">
                                  </div>
                                </td>
                                <td class="text-center">
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
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                @endif
              @endforeach
            @endif
            @if (
                ($entityTypeFilter === 'doctor' || $entityTypeFilter === 'all') &&
                    $doctors->filter(fn($d) => $d['transactions']->isNotEmpty())->isNotEmpty())
              <h5 class="p-3 glass-header text-white">پزشکان</h5>
            @endif
            @if ($entityTypeFilter === 'doctor' || $entityTypeFilter === 'all')
              @foreach ($doctors as $data)
                @if ($data['transactions']->isNotEmpty())
                  <div class="entity-toggle border-bottom" x-data="{ open: false }">
                    <div class="d-flex justify-content-between align-items-center p-3 cursor-pointer"
                      @click="open = !open">
                      <div class="d-flex align-items-center gap-3">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                          stroke-width="2">
                          <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                          <circle cx="12" cy="7" r="4" />
                        </svg>
                        <span class="fw-bold">{{ $data['entity']->full_name }} ({{ $data['entity']->mobile }})</span>
                        <span class="badge bg-label-primary">{{ $data['totalTransactions'] }} تراکنش</span>
                      </div>
                      <svg :class="{ 'rotate-180': open }" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="#6b7280" stroke-width="2" style="transition: transform 0.2s;">
                        <path d="M6 9l6 6 6-6" />
                      </svg>
                    </div>
                    <div x-show="open" x-transition>
                      <div class="table-responsive text-nowrap p-3 bg-light">
                        <table class="table table-hover w-100 m-0">
                          <thead>
                            <tr>
                              <th class="text-center align-middle" style="width: 40px;">
                                <div class="d-flex justify-content-center align-items-center">
                                  <input type="checkbox" wire:model.live="selectAll"
                                    class="form-check-input m-0 align-middle">
                                </div>
                              </th>
                              <th class="text-center align-middle" style="width: 60px;">ردیف</th>
                              <th class="align-middle">مبلغ (تومان)</th>
                              <th class="align-middle">درگاه</th>
                              <th class="align-middle">وضعیت</th>
                              <th class="align-middle">تاریخ</th>
                              <th class="text-center align-middle" style="width: 120px;">عملیات</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach ($data['transactions'] as $index => $transaction)
                              <tr class="align-middle">
                                <td class="text-center">
                                  <div class="d-flex justify-content-center align-items-center">
                                    <input type="checkbox" wire:model.live="selectedTransactions"
                                      value="{{ $transaction->id }}" class="form-check-input m-0 align-middle">
                                  </div>
                                </td>
                                <td class="text-center">
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
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                @endif
              @endforeach
            @endif
            @if (
                ($entityTypeFilter === 'secretary' || $entityTypeFilter === 'all') &&
                    $secretaries->filter(fn($d) => $d['transactions']->isNotEmpty())->isNotEmpty())
              <h5 class="p-3 glass-header text-white">منشی‌ها</h5>
            @endif
            @if ($entityTypeFilter === 'secretary' || $entityTypeFilter === 'all')
              @foreach ($secretaries as $data)
                @if ($data['transactions']->isNotEmpty())
                  <div class="entity-toggle border-bottom" x-data="{ open: false }">
                    <div class="d-flex justify-content-between align-items-center p-3 cursor-pointer"
                      @click="open = !open">
                      <div class="d-flex align-items-center gap-3">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                          stroke-width="2">
                          <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                          <circle cx="12" cy="7" r="4" />
                        </svg>
                        <span class="fw-bold">{{ $data['entity']->full_name }} ({{ $data['entity']->mobile }})</span>
                        <span class="badge bg-label-primary">{{ $data['totalTransactions'] }} تراکنش</span>
                      </div>
                      <svg :class="{ 'rotate-180': open }" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="#6b7280" stroke-width="2" style="transition: transform 0.2s;">
                        <path d="M6 9l6 6 6-6" />
                      </svg>
                    </div>
                    <div x-show="open" x-transition>
                      <div class="table-responsive text-nowrap p-3 bg-light">
                        <table class="table table-hover w-100 m-0">
                          <thead>
                            <tr>
                              <th class="text-center align-middle" style="width: 40px;">
                                <div class="d-flex justify-content-center align-items-center">
                                  <input type="checkbox" wire:model.live="selectAll"
                                    class="form-check-input m-0 align-middle">
                                </div>
                              </th>
                              <th class="text-center align-middle" style="width: 60px;">ردیف</th>
                              <th class="align-middle">مبلغ (تومان)</th>
                              <th class="align-middle">درگاه</th>
                              <th class="align-middle">وضعیت</th>
                              <th class="align-middle">تاریخ</th>
                              <th class="text-center align-middle" style="width: 120px;">عملیات</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach ($data['transactions'] as $index => $transaction)
                              <tr class="align-middle">
                                <td class="text-center">
                                  <div class="d-flex justify-content-center align-items-center">
                                    <input type="checkbox" wire:model.live="selectedTransactions"
                                      value="{{ $transaction->id }}" class="form-check-input m-0 align-middle">
                                  </div>
                                </td>
                                <td class="text-center">
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
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                @endif
              @endforeach
            @endif
          </div>
          <!-- Mobile Card View -->
          <div class="d-md-none">
            @if (
                ($entityTypeFilter === 'user' || $entityTypeFilter === 'all') &&
                    $users->filter(fn($d) => $d['transactions']->isNotEmpty())->isNotEmpty())
              <h5 class="p-3 glass-header text-white">کاربران</h5>
            @endif
            @if ($entityTypeFilter === 'user' || $entityTypeFilter === 'all')
              @foreach ($users as $data)
                @if ($data['transactions']->isNotEmpty())
                  <div class="note-card mb-2" x-data="{ open: false }">
                    <div class="note-card-header d-flex justify-content-between align-items-center px-2 py-2"
                      @click="open = !open" style="cursor:pointer;">
                      <span class="fw-bold">{{ $data['entity']->full_name }} ({{ $data['entity']->mobile }})</span>
                      <svg :class="{ 'rotate-180': open }" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" style="transition: transform 0.2s;">
                        <path d="M6 9l6 6 6-6" />
                      </svg>
                    </div>
                    <div class="note-card-body px-2 py-2" x-show="open" x-transition>
                      <div class="mb-2">
                        <span class="badge bg-label-primary">{{ $data['totalTransactions'] }} تراکنش</span>
                      </div>
                      @foreach ($data['transactions'] as $transaction)
                        <div class="card shadow-sm mb-2 border-0">
                          <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                              <span class="fw-medium">مبلغ: {{ number_format($transaction->amount) }} تومان</span>
                              <span
                                class="badge {{ $transaction->status === 'paid' ? 'bg-label-success' : ($transaction->status === 'failed' ? 'bg-label-danger' : 'bg-label-warning') }}">
                                {{ $transaction->status === 'paid' ? 'پرداخت‌شده' : ($transaction->status === 'failed' ? 'ناموفق' : 'در انتظار') }}
                              </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                              <small class="text-muted">درگاه:</small>
                              <span class="fw-medium">{{ $transaction->gateway }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                              <small class="text-muted">تاریخ:</small>
                              <span class="fw-medium">{{ $transaction->jalali_created_at }}</span>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                              <button wire:click="confirmDelete({{ $transaction->id }})"
                                class="btn btn-gradient-danger btn-sm rounded-pill px-3">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                  stroke="currentColor" stroke-width="2">
                                  <path
                                    d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                                </svg>
                              </button>
                            </div>
                          </div>
                        </div>
                      @endforeach
                    </div>
                  </div>
                @endif
              @endforeach
            @endif
            @if (
                ($entityTypeFilter === 'doctor' || $entityTypeFilter === 'all') &&
                    $doctors->filter(fn($d) => $d['transactions']->isNotEmpty())->isNotEmpty())
              <h5 class="p-3 glass-header text-white">پزشکان</h5>
            @endif
            @if ($entityTypeFilter === 'doctor' || $entityTypeFilter === 'all')
              @foreach ($doctors as $data)
                @if ($data['transactions']->isNotEmpty())
                  <div class="note-card mb-2" x-data="{ open: false }">
                    <div class="note-card-header d-flex justify-content-between align-items-center px-2 py-2"
                      @click="open = !open" style="cursor:pointer;">
                      <span class="fw-bold">{{ $data['entity']->full_name }} ({{ $data['entity']->mobile }})</span>
                      <svg :class="{ 'rotate-180': open }" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" style="transition: transform 0.2s;">
                        <path d="M6 9l6 6 6-6" />
                      </svg>
                    </div>
                    <div class="note-card-body px-2 py-2" x-show="open" x-transition>
                      <div class="mb-2">
                        <span class="badge bg-label-primary">{{ $data['totalTransactions'] }} تراکنش</span>
                      </div>
                      @foreach ($data['transactions'] as $transaction)
                        <div class="card shadow-sm mb-2 border-0">
                          <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                              <span class="fw-medium">مبلغ: {{ number_format($transaction->amount) }} تومان</span>
                              <span
                                class="badge {{ $transaction->status === 'paid' ? 'bg-label-success' : ($transaction->status === 'failed' ? 'bg-label-danger' : 'bg-label-warning') }}">
                                {{ $transaction->status === 'paid' ? 'پرداخت‌شده' : ($transaction->status === 'failed' ? 'ناموفق' : 'در انتظار') }}
                              </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                              <small class="text-muted">درگاه:</small>
                              <span class="fw-medium">{{ $transaction->gateway }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                              <small class="text-muted">تاریخ:</small>
                              <span class="fw-medium">{{ $transaction->jalali_created_at }}</span>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                              <button wire:click="confirmDelete({{ $transaction->id }})"
                                class="btn btn-gradient-danger btn-sm rounded-pill px-3">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                  stroke="currentColor" stroke-width="2">
                                  <path
                                    d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                                </svg>
                              </button>
                            </div>
                          </div>
                        </div>
                      @endforeach
                    </div>
                  </div>
                @endif
              @endforeach
            @endif
            @if (
                ($entityTypeFilter === 'secretary' || $entityTypeFilter === 'all') &&
                    $secretaries->filter(fn($d) => $d['transactions']->isNotEmpty())->isNotEmpty())
              <h5 class="p-3 glass-header text-white">منشی‌ها</h5>
            @endif
            @if ($entityTypeFilter === 'secretary' || $entityTypeFilter === 'all')
              @foreach ($secretaries as $data)
                @if ($data['transactions']->isNotEmpty())
                  <div class="note-card mb-2" x-data="{ open: false }">
                    <div class="note-card-header d-flex justify-content-between align-items-center px-2 py-2"
                      @click="open = !open" style="cursor:pointer;">
                      <span class="fw-bold">{{ $data['entity']->full_name }} ({{ $data['entity']->mobile }})</span>
                      <svg :class="{ 'rotate-180': open }" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" style="transition: transform 0.2s;">
                        <path d="M6 9l6 6 6-6" />
                      </svg>
                    </div>
                    <div class="note-card-body px-2 py-2" x-show="open" x-transition>
                      <div class="mb-2">
                        <span class="badge bg-label-primary">{{ $data['totalTransactions'] }} تراکنش</span>
                      </div>
                      @foreach ($data['transactions'] as $transaction)
                        <div class="card shadow-sm mb-2 border-0">
                          <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                              <span class="fw-medium">مبلغ: {{ number_format($transaction->amount) }} تومان</span>
                              <span
                                class="badge {{ $transaction->status === 'paid' ? 'bg-label-success' : ($transaction->status === 'failed' ? 'bg-label-danger' : 'bg-label-warning') }}">
                                {{ $transaction->status === 'paid' ? 'پرداخت‌شده' : ($transaction->status === 'failed' ? 'ناموفق' : 'در انتظار') }}
                              </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                              <small class="text-muted">درگاه:</small>
                              <span class="fw-medium">{{ $transaction->gateway }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                              <small class="text-muted">تاریخ:</small>
                              <span class="fw-medium">{{ $transaction->jalali_created_at }}</span>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                              <button wire:click="confirmDelete({{ $transaction->id }})"
                                class="btn btn-gradient-danger btn-sm rounded-pill px-3">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                  stroke="currentColor" stroke-width="2">
                                  <path
                                    d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                                </svg>
                              </button>
                            </div>
                          </div>
                        </div>
                      @endforeach
                    </div>
                  </div>
                @endif
              @endforeach
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener('livewire:init', function() {
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

@if (session('success'))
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      toastr.success(@json(session('success')));
    });
  </script>
@endif
<div class="transaction-list-container" x-data="{ mobileSearchOpen: false }">
  <div class="container py-2 mt-3" dir="rtl" wire:init="loadTransactions">
    <!-- Header -->
    <header class="glass-header text-white p-3 rounded-3 mb-3 shadow-lg">
      <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 w-100">
        <!-- Title Section -->
        <div class="d-flex align-items-center gap-2 flex-shrink-0 w-md-100 justify-content-between">
          <h2 class="mb-0 fw-bold fs-5">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              class="header-icon">
              <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
            </svg>
            مدیریت تراکنش‌ها
          </h2>
          <!-- Mobile Toggle Button -->
          <button class="btn btn-link text-white p-0 d-md-none mobile-toggle-btn" type="button"
            @click="mobileSearchOpen = !mobileSearchOpen" :aria-expanded="mobileSearchOpen">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              class="toggle-icon" :class="{ 'rotate-180': mobileSearchOpen }">
              <path d="M6 9l6 6 6-6" />
            </svg>
          </button>
        </div>
        <!-- Mobile Collapsible Section -->
        <div x-show="mobileSearchOpen" x-transition:enter="transition ease-out duration-300"
          x-transition:enter-start="opacity-0 transform -translate-y-2"
          x-transition:enter-end="opacity-100 transform translate-y-0"
          x-transition:leave="transition ease-in duration-200"
          x-transition:leave-start="opacity-100 transform translate-y-0"
          x-transition:leave-end="opacity-0 transform -translate-y-2" class="d-md-none w-100">
          <div class="d-flex flex-column gap-2">
            <div class="search-box position-relative">
              <input type="text" wire:model.live="search" class="form-control ps-5"
                placeholder="جستجو بر اساس نام، موبایل، مبلغ، درگاه...">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" class="search-icon">
                <circle cx="11" cy="11" r="8" />
                <path d="M21 21l-4.35-4.35" />
              </svg>
            </div>
            <select class="form-select form-select-sm" wire:model.live="statusFilter">
              <option value="">همه وضعیت‌ها</option>
              <option value="paid">پرداخت‌شده</option>
              <option value="failed">ناموفق</option>
              <option value="pending">در انتظار</option>
            </select>
            <select class="form-select form-select-sm" wire:model.live="entityTypeFilter">
              <option value="all">همه نوع‌ها</option>
              <option value="user">کاربران</option>
              <option value="doctor">پزشکان</option>
              <option value="secretary">منشی‌ها</option>
            </select>
            <div class="d-flex align-items-center gap-2 justify-content-between">
              <span class="badge bg-white text-primary px-2 py-1 fw-medium flex-shrink-0">
                {{ $readyToLoad ? $totalFilteredCount : 0 }}
              </span>
            </div>
          </div>
        </div>
        <!-- Desktop Search and Actions -->
        <div class="d-none d-md-flex align-items-center gap-3 ms-auto">
          <div class="search-box position-relative">
            <input type="text" wire:model.live="search" class="form-control ps-5"
              placeholder="جستجو بر اساس نام، موبایل، مبلغ، درگاه...">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              class="search-icon">
              <circle cx="11" cy="11" r="8" />
              <path d="M21 21l-4.35-4.35" />
            </svg>
          </div>
          <select class="form-select form-select-sm" style="min-width: 150px;" wire:model.live="statusFilter">
            <option value="">همه وضعیت‌ها</option>
            <option value="paid">پرداخت‌شده</option>
            <option value="failed">ناموفق</option>
            <option value="pending">در انتظار</option>
          </select>
          <select class="form-select form-select-sm" style="min-width: 150px;" wire:model.live="entityTypeFilter">
            <option value="all">همه نوع‌ها</option>
            <option value="user">کاربران</option>
            <option value="doctor">پزشکان</option>
            <option value="secretary">منشی‌ها</option>
          </select>
          <span class="badge bg-white text-primary px-2 py-1 fw-medium flex-shrink-0">
            {{ $readyToLoad ? $totalFilteredCount : 0 }}
          </span>
        </div>
      </div>
    </header>
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
                <input class="form-check-input" type="checkbox" id="applyToAllFiltered"
                  wire:model="applyToAllFiltered">
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
                      <div class="d-flex align-items-center gap-3 mb-2">
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
                      <div class="d-flex align-items-center gap-3 mb-2">
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
                      <div class="d-flex align-items-center gap-3 mb-2">
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

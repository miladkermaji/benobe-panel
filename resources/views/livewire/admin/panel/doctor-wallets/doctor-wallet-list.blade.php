@if (session('success'))
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      toastr.success(@json(session('success')));
    });
  </script>
@endif
<div class="doctor-wallet-list-container" x-data="{ mobileSearchOpen: false }">
  <div class="container py-2 mt-3" dir="rtl" wire:init="loadWallets">
    <!-- Header -->
    <header class="glass-header text-white p-3 rounded-3  shadow-lg">
      <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 w-100">
        <!-- Title Section -->
        <div class="d-flex align-items-center gap-2 flex-shrink-0 w-md-100 justify-content-between">
          <h2 class="mb-0 fw-bold fs-5">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              class="header-icon">
              <path d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9" />
            </svg>
            مدیریت تراکنش‌های کیف‌پول پزشکان
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
                placeholder="جستجو بر اساس نام یا موبایل پزشک...">
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
            </select>
            <div class="d-flex align-items-center gap-2 justify-content-between">
              <a href="{{ route('admin.panel.doctor-wallets.create') }}"
                class="btn btn-success px-3 py-1 d-flex align-items-center gap-1 flex-shrink-0">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="#fff" stroke="#fff" stroke-width="2">
                  <path d="M12 5v14M5 12h14" />
                </svg>
                <span class="text-white">شارژ کیف‌پول</span>
              </a>
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
              placeholder="جستجو بر اساس نام یا موبایل پزشک...">
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
          </select>
          <a href="{{ route('admin.panel.doctor-wallets.create') }}"
            class="btn btn-success px-3 py-1 d-flex align-items-center gap-1 flex-shrink-0">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="#fff" stroke="#fff" stroke-width="2">
              <path d="M12 5v14M5 12h14" />
            </svg>
            <span class="text-white">شارژ کیف‌پول</span>
          </a>
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
            <h5 class="p-3 glass-header text-white">پزشکان</h5>
            @forelse ($doctors as $doctor)
              @if ($doctor->walletTransactions->count() > 0)
                <div class="entity-toggle border-bottom" x-data="{ open: false }">
                  <div class="d-flex justify-content-between align-items-center p-3 cursor-pointer"
                    @click="open = !open">
                    <div class="d-flex align-items-center gap-3 mb-2">
                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                        stroke-width="2">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                      </svg>
                      <span class="fw-bold">{{ $doctor->first_name . ' ' . $doctor->last_name }}
                        ({{ $doctor->mobile }})
                      </span>
                      <span class="badge bg-label-primary">{{ $doctor->walletTransactions->count() }} تراکنش</span>
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
                            <th class="align-middle">توضیحات</th>
                            <th class="text-center align-middle" style="width: 100px;">وضعیت</th>
                            <th class="text-center align-middle" style="width: 150px;">تاریخ ثبت</th>
                            <th class="text-center align-middle" style="width: 120px;">عملیات</th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach ($doctor->walletTransactions as $index => $item)
                            <tr class="align-middle">
                              <td class="text-center">
                                <div class="d-flex justify-content-center align-items-center">
                                  <input type="checkbox" wire:model.live="selectedTransactions"
                                    value="{{ $item->id }}" class="form-check-input m-0 align-middle">
                                </div>
                              </td>
                              <td class="text-center">{{ $index + 1 }}</td>
                              <td class="align-middle">{{ number_format($item->amount) }}</td>
                              <td class="align-middle">{{ $item->description }}</td>
                              <td class="text-center align-middle">
                                <span class="badge {{ $item->status === 'paid' ? 'bg-success' : 'bg-danger' }}">
                                  {{ $item->status === 'paid' ? 'پرداخت‌شده' : 'ناموفق' }}
                                </span>
                              </td>
                              <td class="text-center align-middle">
                                {{ $item->registered_at ? \Morilog\Jalali\Jalalian::fromDateTime($item->registered_at)->format('Y/m/d H:i') : 'نامشخص' }}
                              </td>
                              <td class="text-center align-middle">
                                <button wire:click="confirmDelete({{ $item->id }})"
                                  class="btn btn-gradient-danger rounded-pill px-3">
                                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path
                                      d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                                  </svg>
                                </button>
                              </td>
                            </tr>
                          @endforeach
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              @endif
            @empty
              <div class="text-center py-5">
                <div class="d-flex flex-column align-items-center justify-content-center">
                  <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" class="text-muted mb-3">
                    <path d="M5 12h14M12 5l7 7-7 7" />
                  </svg>
                  <p class="text-muted fw-medium m-0">هیچ پزشکی یافت نشد.</p>
                </div>
              </div>
            @endforelse
            @if ($doctors->isEmpty() || $doctors->sum(fn($d) => $d->walletTransactions->count()) === 0)
              <div class="text-center py-4">
                <div class="d-flex justify-content-center align-items-center flex-column">
                  <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" class="text-muted mb-2">
                    <path d="M5 12h14M12 5l7 7-7 7" />
                  </svg>
                  <p class="text-muted fw-medium">هیچ تراکنشی یافت نشد.</p>
                </div>
              </div>
            @endif
          </div>
          <!-- Mobile Card View -->
          <div class="d-md-none">
            <h5 class="p-3 glass-header text-white">پزشکان</h5>
            @forelse ($doctors as $doctor)
              @if ($doctor->walletTransactions->count() > 0)
                <div class="note-card mb-2" x-data="{ open: false }">
                  <div class="note-card-header d-flex justify-content-between align-items-center px-2 py-2"
                    @click="open = !open" style="cursor:pointer;">
                    <div class="d-flex align-items-center gap-2">
                      <input type="checkbox" wire:model.live="selectedTransactions" value="{{ $item->id }}"
                        class="form-check-input m-0" @click.stop>
                      <span class="fw-bold">{{ $doctor->first_name . ' ' . $doctor->last_name }}
                        ({{ $doctor->mobile }})
                      </span>
                    </div>
                    <svg :class="{ 'rotate-180': open }" width="20" height="20" viewBox="0 0 24 24"
                      fill="none" stroke="currentColor" stroke-width="2" style="transition: transform 0.2s;">
                      <path d="M6 9l6 6 6-6" />
                    </svg>
                  </div>
                  <div class="note-card-body px-2 py-2" x-show="open" x-transition>
                    <div class="mb-2">
                      <span class="badge bg-label-primary">{{ $doctor->walletTransactions->count() }} تراکنش</span>
                    </div>
                    @foreach ($doctor->walletTransactions as $item)
                      <div class="card shadow-sm mb-2 border-0">
                        <div class="card-body p-2">
                          <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-medium">مبلغ: {{ number_format($item->amount) }} تومان</span>
                            <span
                              class="badge {{ $item->status === 'paid' ? 'bg-label-success' : 'bg-label-danger' }}">
                              {{ $item->status === 'paid' ? 'پرداخت‌شده' : 'ناموفق' }}
                            </span>
                          </div>
                          <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">توضیحات:</small>
                            <span class="fw-medium">{{ $item->description }}</span>
                          </div>
                          <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">تاریخ ثبت:</small>
                            <span class="fw-medium">
                              {{ $item->registered_at ? \Morilog\Jalali\Jalalian::fromDateTime($item->registered_at)->format('Y/m/d H:i') : 'نامشخص' }}
                            </span>
                          </div>
                          <div class="d-flex justify-content-end gap-2">
                            <button wire:click="confirmDelete({{ $item->id }})"
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
            @empty
              <div class="text-center py-5">
                <div class="d-flex flex-column align-items-center justify-content-center">
                  <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" class="text-muted mb-3">
                    <path d="M5 12h14M12 5l7 7-7 7" />
                  </svg>
                  <p class="text-muted fw-medium m-0">هیچ پزشکی یافت نشد.</p>
                </div>
              </div>
            @endforelse
            @if ($doctors->isEmpty() || $doctors->sum(fn($d) => $d->walletTransactions->count()) === 0)
              <div class="text-center py-4">
                <div class="d-flex justify-content-center align-items-center flex-column">
                  <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" class="text-muted mb-2">
                    <path d="M5 12h14M12 5l7 7-7 7" />
                  </svg>
                  <p class="text-muted fw-medium">هیچ تراکنشی یافت نشد.</p>
                </div>
              </div>
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

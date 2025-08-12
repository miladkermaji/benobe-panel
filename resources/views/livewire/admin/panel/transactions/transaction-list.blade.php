@if (session('success'))
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      toastr.success(@json(session('success')));
    });
  </script>
@endif

<div class="transaction-list-container" x-data="{ mobileSearchOpen: false }">
  <div class="container py-2 mt-3" dir="rtl">
    <!-- Header -->
    <header class="glass-header text-white p-3 rounded-3 shadow-lg">
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
              <button wire:click="refreshData" class="btn btn-outline-light btn-sm" title="تازه‌سازی">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2">
                  <path d="M1 4v6h6" />
                  <path d="M23 20v-6h-6" />
                  <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15" />
                </svg>
              </button>
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
          <button wire:click="refreshData" class="btn btn-outline-light btn-sm" title="تازه‌سازی">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2">
              <path d="M1 4v6h6" />
              <path d="M23 20v-6h-6" />
              <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15" />
            </svg>
          </button>
        </div>
      </div>
    </header>

    <!-- Loading State -->
    @if (!$readyToLoad)
      <div class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">در حال بارگذاری...</span>
        </div>
        <p class="mt-3 text-muted">برای مشاهده تراکنش‌ها روی دکمه زیر کلیک کنید</p>
        <button wire:click="loadTransactions" class="btn btn-primary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2" class="me-2">
            <path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            <path d="M9 12l2 2 4-4" />
          </svg>
          بارگذاری تراکنش‌ها
        </button>
      </div>
    @else
      <!-- Loading Indicator -->
      @if ($isLoading)
        <div class="text-center py-3">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">در حال بارگذاری...</span>
          </div>
          <p class="mt-2 text-muted">در حال بارگذاری...</p>
        </div>
      @endif

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

            <!-- Content will be loaded here -->
            <div class="p-3">
              <p class="text-muted text-center">در حال بارگذاری تراکنش‌ها...</p>
            </div>
          </div>
        </div>
      </div>
    @endif
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

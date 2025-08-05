<div>
  <div class="container-fluid py-3" wire:init="loadGateways" x-data="{ mobileSearchOpen: false }">
    <!-- Header -->
    <header class="glass-header text-white p-3 rounded-3 mb-3 shadow-lg">
      <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 w-100">
        <!-- Title Section -->
        <div class="d-flex align-items-center gap-2 flex-shrink-0 w-md-100 justify-content-between">
          <h2 class="mb-0 fw-bold fs-5">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              class="header-icon">
              <path d="M3 10h18M3 14h18M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z" />
            </svg>
            درگاه‌های پرداخت
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
              <input type="text" wire:model.live="search" class="form-control ps-5" placeholder="جستجو...">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" class="search-icon">
                <circle cx="11" cy="11" r="8" />
                <path d="M21 21l-4.35-4.35" />
              </svg>
            </div>
            <div class="d-flex align-items-center gap-2 justify-content-between">
              <a href="{{ route('admin.panel.tools.payment_gateways.create') }}"
                class="btn btn-success px-3 py-1 d-flex align-items-center gap-1 flex-shrink-0">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="#fff" stroke="#fff" stroke-width="2">
                  <path d="M12 5v14M5 12h14" />
                </svg>
                <span class="text-white">درگاه جدید</span>
              </a>
              <span class="badge bg-white text-primary px-2 py-1 fw-medium flex-shrink-0">
                {{ $readyToLoad ? $gateways->total() : 0 }}
              </span>
            </div>
          </div>
        </div>
        <!-- Desktop Search and Actions -->
        <div class="d-none d-md-flex align-items-center gap-3 ms-auto">
          <div class="search-box position-relative">
            <input type="text" wire:model.live="search" class="form-control ps-5" placeholder="جستجو...">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              class="search-icon">
              <circle cx="11" cy="11" r="8" />
              <path d="M21 21l-4.35-4.35" />
            </svg>
          </div>
          <a href="{{ route('admin.panel.tools.payment_gateways.create') }}"
            class="btn btn-success px-3 py-1 d-flex align-items-center gap-1 flex-shrink-0">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="#fff" stroke="#fff" stroke-width="2">
              <path d="M12 5v14M5 12h14" />
            </svg>
            <span class="text-white">درگاه جدید</span>
          </a>
          <span class="badge bg-white text-primary px-2 py-1 fw-medium flex-shrink-0">
            {{ $readyToLoad ? $gateways->total() : 0 }}
          </span>
        </div>
      </div>
    </header>

    <!-- Alert -->
    <div class="alert alert-custom rounded-3 mb-3 shadow-sm">
      <div class="d-flex align-items-center gap-2">
        <svg class="text-red-500 animate-pulse" width="20" height="20" fill="none" stroke="currentColor"
          viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span class="fw-medium text-dark">توجه: فقط یک درگاه می‌تواند فعال باشد.</span>
      </div>
    </div>

    <!-- Gateway List -->
    <div class="table-container">
      @if ($readyToLoad)
        <div class="d-none d-md-block">
          <table class="table">
            <thead>
              <tr>
                <th>درگاه</th>
                <th>عنوان</th>
                <th>وضعیت</th>
                <th>عملیات</th>
              </tr>
            </thead>
            <tbody>
              @forelse($gateways as $gateway)
                <tr>
                  <td>
                    <div class="gateway-icon">
                      @if ($gateway->name === 'zarinpal')
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f59e0b"
                          stroke-width="2">
                          <path d="M12 2a10 10 0 110 20 10 10 0 010-20zm0 4v6l5 5" />
                        </svg>
                      @elseif($gateway->name === 'parsian')
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#16a34a"
                          stroke-width="2">
                          <path d="M4 4h16v16H4zM8 8l8 8M8 16L16 8" />
                        </svg>
                      @elseif($gateway->name === 'saman')
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2563eb"
                          stroke-width="2">
                          <path d="M12 2L2 12l10 10 10-10L12 2zm0 4v12" />
                        </svg>
                      @elseif($gateway->name === 'mellat')
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc2626"
                          stroke-width="2">
                          <path d="M4 12a8 8 0 018-8 8 8 0 018 8H4z" />
                        </svg>
                      @else
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                          stroke-width="2">
                          <path d="M12 2a10 10 0 110 20 10 10 0 010-20zM8 12h8M12 8v8" />
                        </svg>
                      @endif
                    </div>
                  </td>
                  <td>{{ $gateway->title }}</td>
                  <td>
                    <span wire:click="toggleStatus({{ $gateway->id }})"
                      class="status-switch {{ $gateway->is_active ? 'active' : 'inactive' }}">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" class="status-icon">
                        @if ($gateway->is_active)
                          <path d="M20 6L9 17l-5-5" />
                        @else
                          <path d="M18 6L6 18M6 6l12 12" />
                        @endif
                      </svg>
                    </span>
                  </td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <a href="{{ route('admin.panel.tools.payment_gateways.edit', $gateway->name) }}"
                        class="btn btn-custom rounded-circle p-2 d-flex align-items-center justify-content-center">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2">
                          <path
                            d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                      </a>
                      <button wire:click="confirmDelete('{{ $gateway->name }}')"
                        class="btn btn-danger rounded-circle p-2 d-flex align-items-center justify-content-center">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2">
                          <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center text-muted">
                    هیچ درگاهی با این جستجو یافت نشد.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="d-md-none">
          @forelse($gateways as $gateway)
            <div class="card mb-3 shadow-sm">
              <div class="card-body d-flex flex-column gap-2">
                <div class="d-flex align-items-center gap-2">
                  <div class="gateway-icon">
                    @if ($gateway->name === 'zarinpal')
                      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f59e0b"
                        stroke-width="2">
                        <path d="M12 2a10 10 0 110 20 10 10 0 010-20zm0 4v6l5 5" />
                      </svg>
                    @elseif($gateway->name === 'parsian')
                      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16a34a"
                        stroke-width="2">
                        <path d="M4 4h16v16H4zM8 8l8 8M8 16L16 8" />
                      </svg>
                    @elseif($gateway->name === 'saman')
                      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb"
                        stroke-width="2">
                        <path d="M12 2L2 12l10 10 10-10L12 2zm0 4v12" />
                      </svg>
                    @elseif($gateway->name === 'mellat')
                      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc2626"
                        stroke-width="2">
                        <path d="M4 12a8 8 0 018-8 8 8 0 018 8H4z" />
                      </svg>
                    @else
                      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                        stroke-width="2">
                        <path d="M12 2a10 10 0 110 20 10 10 0 010-20zM8 12h8M12 8v8" />
                      </svg>
                    @endif
                  </div>
                  <h6 class="mb-0 fs-6">{{ $gateway->title }}</h6>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                  <span wire:click="toggleStatus({{ $gateway->id }})"
                    class="status-switch {{ $gateway->is_active ? 'active' : 'inactive' }}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2" class="status-icon">
                      @if ($gateway->is_active)
                        <path d="M20 6L9 17l-5-5" />
                      @else
                        <path d="M18 6L6 18M6 6l12 12" />
                      @endif
                    </svg>
                  </span>
                  <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.panel.tools.payment_gateways.edit', $gateway->name) }}"
                      class="btn btn-custom rounded-circle p-1 d-flex align-items-center justify-content-center">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path
                          d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                      </svg>
                    </a>
                    <button wire:click="confirmDelete('{{ $gateway->name }}')"
                      class="btn btn-danger rounded-circle p-1 d-flex align-items-center justify-content-center">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          @empty
            <div class="text-center text-muted p-3">
              هیچ درگاهی با این جستجو یافت نشد.
            </div>
          @endforelse
        </div>
      @else
        <div class="text-center text-muted p-3">
          در حال بارگذاری درگاه‌ها...
        </div>
      @endif
    </div>

    <!-- Pagination -->
    <div class="pagination-container">
      @if ($gateways && $gateways->hasPages())
        <nav aria-label="Page navigation">
          {{ $gateways->links('livewire::bootstrap') }}
        </nav>
      @endif
    </div>
  </div>

  <script>
    document.addEventListener('livewire:init', function() {
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });

      Livewire.on('confirm-delete', (event) => {
        Swal.fire({
          title: 'حذف درگاه',
          text: 'آیا مطمئن هستید که می‌خواهید این درگاه را حذف کنید؟',

          showCancelButton: true,
          confirmButtonColor: '#ef4444',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'بله، حذف کن',
          cancelButtonText: 'خیر'
        }).then((result) => {
          if (result.isConfirmed) {
            Livewire.dispatch('deleteGatewayConfirmed', {
              name: event.name
            });
          }
        });
      });
    });
  </script>
</div>

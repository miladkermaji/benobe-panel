<div>
  <div class="container-fluid py-3" wire:init="loadGateways">
    <!-- Header -->
    <header
      class="bg-gradient-header text-white p-3 rounded-3 mb-3 d-flex align-items-center justify-content-between shadow-lg">
      <div class="d-flex align-items-center gap-3 mb-2">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="header-icon">
          <path d="M3 10h18M3 14h18M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z" />
        </svg>
        <h2 class="mb-0 fw-bold">پنل‌های پیامکی</h2>
      </div>
      <div class="d-flex align-items-center gap-3 mb-2">
        <div class="search-box position-relative">
          <input type="text" wire:model.live="search" class="form-control rounded-pill ps-5 pe-3"
            placeholder="جستجو در پنل‌ها...">
        </div>
        <span class="badge bg-white text-primary rounded-pill px-3 py-1 fw-medium">
          {{ $readyToLoad ? $gateways->total() : 0 }} پنل
        </span>
      </div>
    </header>

    <!-- Alert -->
    <div class="alert alert-custom rounded-3 mb-5 d-flex align-items-center gap-3 mb-2 shadow-sm">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2">
        <circle cx="12" cy="12" r="10" />
        <path d="M12 8v4M12 16h.01" />
      </svg>
      <span class="fw-medium text-dark">توجه: فقط یک پنل می‌تواند فعال باشد.</span>
    </div>

    <!-- Gateway List -->
    <div class="gateway-list d-flex flex-wrap gap-4 justify-content-center">
      @if ($readyToLoad)
        @forelse($gateways as $gateway)
          <div
            class="gateway-card card border-0 rounded-3 shadow-lg position-relative overflow-hidden animate__animated animate__zoomIn">
            <div class="card-bg {{ $gateway->is_active ? 'bg-active' : 'bg-inactive' }}"></div>
            <div class="card-body p-4 d-flex flex-column gap-3 position-relative">
              <div class="d-flex align-items-center gap-4">
                <div
                  class="gateway-logo rounded-circle bg-white shadow-lg d-flex align-items-center justify-content-center"
                  style="width: 50px; height: 50px; border: 3px solid #ffffff;">
                  @if ($gateway->name === 'pishgamrayan')
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#f59e0b"
                      stroke-width="2">
                      <path d="M12 2a10 10 0 110 20 10 10 0 010-20zm0 4v6l5 5" />
                    </svg>
                  @elseif($gateway->name === 'farazsms')
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#16a34a"
                      stroke-width="2">
                      <path d="M4 4h16v16H4zM8 8l8 8M8 16L16 8" />
                    </svg>
                  @elseif($gateway->name === 'mellipayamak')
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#dc2626"
                      stroke-width="2">
                      <path d="M4 12a8 8 0 018-8 8 8 0 018 8H4z" />
                    </svg>
                  @elseif($gateway->name === 'kavenegar')
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#2563eb"
                      stroke-width="2">
                      <path d="M12 2L2 12l10 10 10-10L12 2zm0 4v12" />
                    </svg>
                  @elseif($gateway->name === 'payamito')
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#9333ea"
                      stroke-width="2">
                      <path d="M3 6l9 9 9-9M5 4h14v16H5z" />
                    </svg>
                  @endif
                </div>
                <h5 class="mb-0 fw-bold text-dark gateway-title">{{ $gateway->title }}</h5>
              </div>
              <div class="d-flex align-items-center justify-content-between mt-2">
                <span wire:click="toggleStatus({{ $gateway->id }})"
                  class="status-switch {{ $gateway->is_active ? 'active' : 'inactive' }}">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" class="status-icon">
                    @if ($gateway->is_active)
                      <path d="M20 6L9 17l-5-5" />
                    @else
                      <path d="M18 6L6 18M6 6l12 12" />
                    @endif
                  </svg>
                </span>
                <div class="d-flex align-items-center gap-2">
                  <a href="{{ route('admin.sms-gateways.edit', $gateway->name) }}"
                    class="btn btn-custom rounded-pill px-3 py-1 d-flex align-items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2">
                      <path
                        d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                    </svg>
                    <span>ویرایش</span>
                  </a>
                  <button wire:click="confirmDelete('{{ $gateway->name }}')"
                    class="btn btn-danger rounded-pill px-2 py-1 d-flex align-items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2">
                      <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        @empty
          <div class="col-12 text-center text-muted">
            هیچ پنلی با این جستجو یافت نشد.
          </div>
        @endforelse
      @else
        <div class="col-12 text-center text-muted">
          در حال بارگذاری پنل‌ها...
        </div>
      @endif
    </div>

    <!-- Pagination -->
    <div class="pagination-container mt-4">
      @if ($gateways && $gateways->hasPages())
        <nav aria-label="Page navigation">
          {{ $gateways->links('livewire::simple-tailwind') }}
        </nav>
      @endif
    </div>
  </div>

  <style>
    .bg-gradient-header {
      background: linear-gradient(135deg, #6d28d9, #ec4899);
      transition: all 0.3s ease;
    }

    .bg-gradient-header:hover .header-icon {
      transform: scale(1.2);
    }

    .alert-custom {
      background: #fef2f2;
      border-left: 5px solid #ef4444;
      color: #1f2937;
      border-radius: 8px;
    }

    .search-box {
      width: 320px;
      position: relative;
    }

    .search-box input {
      background: #ffffff;
      color: #1f2937;
      border: 1px solid #d1d5db;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
      font-size: 0.95rem;
      padding: 0.5rem 1rem 0.5rem 2.5rem;
    }

    .search-box input:focus {
      border-color: #7c3aed;
      box-shadow: 0 0 12px rgba(124, 58, 237, 0.2);
      outline: none;
    }

    .gateway-card {
      width: 340px;
      height: 180px;
      background: #fafafa;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      overflow: hidden;
    }

    .gateway-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
    }

    .card-bg {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      opacity: 0.05;
      z-index: 0;
      transition: opacity 0.3s ease;
    }

    .bg-active {
      background: linear-gradient(45deg, #a7f3d0, #6ee7b7);
    }

    .bg-inactive {
      background: linear-gradient(45deg, #fecaca, #f87171);
    }

    .gateway-card:hover .card-bg {
      opacity: 0.1;
    }

    .gateway-logo {
      transition: transform 0.3s ease;
      z-index: 1;
    }

    .gateway-card:hover .gateway-logo {
      transform: scale(1.1);
    }

    .gateway-title {
      font-size: 1.1rem;
      transition: color 0.3s ease;
      z-index: 1;
    }

    .gateway-card:hover .gateway-title {
      color: #7c3aed;
    }

    .status-switch {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      cursor: pointer;
      transition: all 0.3s ease;
      z-index: 1;
    }

    .status-switch.active {
      background: #10b981;
      box-shadow: 0 0 8px rgba(16, 185, 129, 0.5);
      color: white;
    }

    .status-switch.inactive {
      background: #f87171;
      box-shadow: 0 0 8px rgba(248, 113, 113, 0.5);
      color: white;
    }

    .status-switch:hover {
      transform: scale(1.15);
    }

    .btn-custom {
      background: #7c3aed;
      color: white;
      border: none;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
      font-size: 0.9rem;
    }

    .btn-custom:hover {
      background: #db2777;
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(219, 39, 119, 0.4);
    }

    .btn-danger {
      background: #ef4444;
      border: none;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
      font-size: 0.9rem;
    }

    .btn-danger:hover {
      background: #dc2626;
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
    }

    .pagination-container {
      padding: 10px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      z-index: 10;
      width: 100%;
    }

    @media (max-width: 768px) {
      .gateway-card {
        width: 100%;
        max-width: 360px;
        height: 160px;
      }

      .search-box {
        width: 100%;
      }
    }
  </style>

  <script>
    document.addEventListener('livewire:init', function() {
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });

      Livewire.on('confirm-delete', (event) => {
        Swal.fire({
          title: 'حذف پنل',
          text: 'آیا مطمئن هستید که می‌خواهید این پنل را حذف کنید؟',

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

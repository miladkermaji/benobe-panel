<div>
 <div class="container-fluid py-3" wire:init="loadGateways">
  <!-- Header -->
  <header class="glass-header text-white p-3 rounded-3 mb-3 d-flex align-items-center justify-content-between shadow-lg">
   <div class="d-flex align-items-center gap-3">
    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
     class="header-icon">
     <path d="M3 10h18M3 14h18M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z" />
    </svg>
    <h2 class="mb-0 fw-bold">درگاه‌های پرداخت</h2>
   </div>
   <div class="d-flex align-items-center gap-3">
    <div class="search-box position-relative">
     <input type="text" wire:model.live="search" class="form-control rounded-pill ps-5 pe-3"
      placeholder="جستجو در درگاه‌ها...">

    </div>
    <span class="badge bg-white text-primary rounded-pill px-3 py-1 fw-medium">
     {{ $readyToLoad ? $gateways->total() : 0 }} درگاه
    </span>
   </div>
  </header>

  <!-- Alert -->
  <div class="alert alert-custom rounded-3 mb-5   shadow-sm">
   <div class="d-flex align-items-center">
    <svg class=" text-red-500  animate-pulse" width="25px" height="25px" fill="none" stroke="currentColor"
     style="width: 24px; height: 24px;" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
    </svg>
    <span class="fw-medium text-dark px-1">توجه: فقط یک درگاه می‌تواند فعال باشد.</span>
   </div>
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
        <div class="gateway-logo rounded-circle bg-white shadow-lg d-flex align-items-center justify-content-center"
         style="width: 50px; height: 50px; border: 3px solid #ffffff;">
         @if ($gateway->name === 'zarinpal')
          <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2">
           <path d="M12 2a10 10 0 110 20 10 10 0 010-20zm0 4v6l5 5" />
          </svg>
         @elseif($gateway->name === 'parsian')
          <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2">
           <path d="M4 4h16v16H4zM8 8l8 8M8 16L16 8" />
          </svg>
         @elseif($gateway->name === 'saman')
          <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2">
           <path d="M12 2L2 12l10 10 10-10L12 2zm0 4v12" />
          </svg>
         @elseif($gateway->name === 'mellat')
          <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2">
           <path d="M4 12a8 8 0 018-8 8 8 0 018 8H4z" />
          </svg>
         @else
          <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
           <path d="M12 2a10 10 0 110 20 10 10 0 010-20zM8 12h8M12 8v8" />
          </svg>
         @endif
        </div>
        <h5 class="mb-0 fw-bold text-dark gateway-title">{{ $gateway->title }}</h5>
       </div>
       <div class="d-flex align-items-center justify-content-between mt-2">
        <span wire:click="toggleStatus({{ $gateway->id }})"
         class="status-switch {{ $gateway->is_active ? 'active' : 'inactive' }}">
         <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="status-icon">
          @if ($gateway->is_active)
           <path d="M20 6L9 17l-5-5" />
          @else
           <path d="M18 6L6 18M6 6l12 12" />
          @endif
         </svg>
        </span>
        <div class="d-flex align-items-center gap-2">
         <a href="{{ route('admin.panel.tools.payment_gateways.edit', $gateway->name) }}"
          class="btn btn-custom rounded-pill px-3 py-1 d-flex align-items-center gap-2">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
           <path
            d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
          </svg>
          <span>ویرایش</span>
         </a>
         <button wire:click="confirmDelete('{{ $gateway->name }}')"
          class="btn btn-danger rounded-pill px-2 py-1 d-flex align-items-center gap-2">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
           <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
          </svg>
         </button>
        </div>
       </div>
      </div>
     </div>
    @empty
     <div class="col-12 text-center text-muted">
      هیچ درگاهی با این جستجو یافت نشد.
     </div>
    @endforelse
   @else
    <div class="col-12 text-center text-muted">
     در حال بارگذاری درگاه‌ها...
    </div>
   @endif
  </div>

  <div class="container-fluid" wire:init="loadGateways">
   <!-- بقیه کد -->
   <div class="pagination-container">
    @if ($gateways && $gateways->hasPages())
     <nav aria-label="Page navigation">
      {{ $gateways->links('livewire::bootstrap') }}
     </nav>
    @endif
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
     title: 'حذف درگاه',
     text: 'آیا مطمئن هستید که می‌خواهید این درگاه را حذف کنید؟',
     icon: 'warning',
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

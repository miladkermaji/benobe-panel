<div class="container-fluid py-4" dir="rtl" wire:init="loadDoctorServices">
  <!-- هدر -->
  <div class="service-header d-flex justify-content-between flex-wrap mb-3">
    <div>
      <h1 class="header-title">مدیریت خدمات و بیمه‌ها</h1>
    </div>
    <div class="header-actions d-flex">
      <div class="search-container">
        <input type="text" class="search-input" wire:model.live="search" placeholder="جستجو در خدمات...">
        <span class="search-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)"
            stroke-width="2">
            <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
          </svg>
        </span>
      </div>
      <div class="action-buttons d-flex gap-2">
        <a href="{{ route('dr.panel.doctor-services.create') }}"
          class="btn btn-primary d-flex align-items-center gap-2">
          <svg style="transform: rotate(180deg)" width="18" height="18" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2">
            <path d="M12 5v14M5 12h14" />
          </svg>
          افزودن خدمت
        </a>
        <button id="delete-selected-btn" class="btn btn-danger d-flex align-items-center gap-2"
          @if (empty($selectedDoctorServices)) disabled @endif>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
          </svg>
          حذف انتخاب‌شده‌ها
        </button>
      </div>
    </div>
  </div>

  <!-- جدول خدمات -->
  <div class="services-container">
    @if ($readyToLoad)
      @forelse ($services as $service)
        <!-- نوار خدمت -->
        <div class="service-section">
          <div class="service-header" wire:click="toggleChildren('{{ $service->id }}')">
            <div class="service-header-row">
              <h5 class="service-title fw-bold">{{ $service->name }}</h5>
              <div
                class="service-toggle mobile-toggle-icon {{ in_array($service->id, $openServices) ? 'rotate-180' : '' }}">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2">
                  <path d="M6 9l6 6 6-6" />
                </svg>
              </div>
            </div>
          </div>
          <div class="services-table {{ in_array($service->id, $openServices) ? 'show' : '' }}">
            <div class="table-header">
              <span>نام بیمه</span>
              <span>توضیحات</span>
              <span>زمان</span>
              <span>قیمت</span>
              <span>تخفیف</span>
              <span>قیمت نهایی</span>
              <span>وضعیت</span>
              <span>عملیات</span>
            </div>
            @foreach ($service->doctorServices as $doctorService)
              <div class="service-row">
                <div class="service-name">
                  <input type="checkbox" wire:model.live="selectedDoctorServices" value="{{ $doctorService->id }}"
                    class="form-check-input">
                  <span>{{ $doctorService->insurance->name ?? 'بیمه نامشخص' }}</span>
                </div>
                <div class="service-description">{{ $doctorService->description ?? 'بدون توضیحات' }}</div>
                <div class="service-duration">
                  {{ $doctorService->duration ? $doctorService->duration . ' دقیقه' : '---' }}</div>
                <div class="service-price">
                  <span
                    class="price-badge {{ $doctorService->price ? 'price-active' : '' }}">{{ $doctorService->price ? number_format($doctorService->price) . ' تومان' : '---' }}</span>
                </div>
                <div class="service-discount">
                  <span class="price-badge {{ $doctorService->discount > 0 ? 'discount-active' : '' }}">
                    @if ($doctorService->discount > 0 && $doctorService->price)
                      {{ number_format(($doctorService->price * $doctorService->discount) / 100) . ' تومان' }}
                    @else
                      ---
                    @endif
                  </span>
                </div>
                <div class="service-final-price">
                  <span
                    class="price-badge {{ $doctorService->price ? 'final-price-active' : '' }}">{{ $doctorService->price ? number_format($doctorService->price - ($doctorService->price * $doctorService->discount) / 100) . ' تومان' : '---' }}</span>
                </div>
                <div class="service-status">
                  <button wire:click="toggleStatus({{ $doctorService->id }})"
                    class="status-badge {{ $doctorService->status ? 'status-active' : 'status-inactive' }}">{{ $doctorService->status ? 'فعال' : 'غیرفعال' }}</button>
                </div>
                <div class="service-actions">
                  <a href="{{ route('dr.panel.doctor-services.edit', $doctorService->id) }}"
                    class="btn btn-outline-primary btn-sm">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2">
                      <path
                        d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                    </svg>
                  </a>
                  <button wire:click="confirmDelete({{ $doctorService->id }})" class="btn btn-outline-danger btn-sm">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2">
                      <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                    </svg>
                  </button>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      @empty
        <div class="empty-state">
          <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)"
            stroke-width="2" class="mb-2">
            <path d="M5 12h14M12 5l7 7-7 7" />
          </svg>
          <p class="text-muted">هیچ خدمتی یافت نشد.</p>
        </div>
      @endforelse
    @else
      <div class="loading-state">
        <p class="text-muted">در حال بارگذاری خدمات...</p>
      </div>
    @endif
  </div>

  <!-- صفحه‌بندی -->
  @if ($services && $services->hasPages())
    <div class="pagination-container">
      <div class="text-muted">
        نمایش {{ $services ? $services->firstItem() : 0 }} تا {{ $services ? $services->lastItem() : 0 }} از
        {{ $services ? $services->total() : 0 }} ردیف
      </div>
      {{ $services->links('livewire::bootstrap') }}
    </div>
  @endif

  <!-- اسکریپت‌ها -->
  <script>
    $(document).ready(function() {
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });

      Livewire.on('confirm-delete', (event) => {
        Swal.fire({
          title: 'حذف خدمت',
          text: 'آیا مطمئن هستید که می‌خواهید این خدمت را حذف کنید؟',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#ef4444',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'بله، حذف کن',
          cancelButtonText: 'خیر'
        }).then((result) => {
          if (result.isConfirmed) {
            Livewire.dispatch('deleteDoctorServiceConfirmed', {
              id: event.id
            });
          }
        });
      });

      // حذف گروهی با تایید و dispatch
      $(document).on('click', '#delete-selected-btn', function(e) {
        if ($(this).is('[disabled]')) return;
        e.preventDefault();
        Swal.fire({
          title: 'حذف گروهی خدمات',
          text: 'آیا مطمئن هستید که می‌خواهید همه خدمات انتخاب‌شده را حذف کنید؟',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#ef4444',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'بله، حذف کن',
          cancelButtonText: 'خیر'
        }).then((result) => {
          if (result.isConfirmed) {
            Livewire.dispatch('deleteSelectedConfirmed');
          }
        });
      });
    });
  </script>
</div>

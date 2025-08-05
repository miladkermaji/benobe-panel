<div class="container-fluid py-4" dir="rtl" wire:init="loadDoctorServices" x-data="{ mobileSearchOpen: false }">
  <!-- هدر -->
  <div class="glass-header text-white p-2  shadow-lg">
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3 w-100">
      <div class="d-flex flex-column flex-md-row gap-2 w-100 align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3 mb-2">
          <h1 class="m-0 h4 font-thin text-nowrap  mb-md-0">مدیریت خدمات و بیمه‌ها</h1>
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
          x-transition:leave-end="opacity-0 transform -translate-y-2" class="d-md-block" id="mobileSearchSection">
          <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2">
            <div class="d-flex gap-2 flex-shrink-0 justify-content-center">
              <div class="search-container position-relative" style="max-width: 100%;">
                <input type="text"
                  class="form-control search-input border-0 shadow-none bg-white text-dark ps-4 rounded-2 text-start"
                  wire:model.live="search" placeholder="جستجو در خدمات..."
                  style="padding-right: 20px; text-align: right; direction: rtl;">
                <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-2"
                  style="z-index: 5; top: 50%; right: 8px;">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                    stroke-width="2">
                    <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
                  </svg>
                </span>
              </div>
              <a href="{{ route('mc.panel.doctor-services.create') }}"
                class="btn btn-gradient-success btn-gradient-success-576 rounded-1 px-3 py-1 d-flex align-items-center gap-1 add-btn-responsive">
                <svg style="transform: rotate(180deg)" width="14" height="14" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2">
                  <path d="M12 5v14M5 12h14" />
                </svg>
                <span>افزودن</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- عملیات گروهی -->
  <div class="group-actions p-2 border-bottom" x-data="{ show: false }" x-show="$wire.selectedDoctorServices.length > 0">
    <div class="d-flex align-items-center gap-2 justify-content-end">
      <select class="form-select form-select-sm" style="max-width: 200px;" wire:model="groupAction">
        <option value="">عملیات گروهی</option>
        <option value="delete">حذف انتخاب شده‌ها</option>
        <option value="status_active">فعال کردن</option>
        <option value="status_inactive">غیرفعال کردن</option>
      </select>
      <button class="btn btn-sm btn-primary" wire:click="executeGroupAction" wire:loading.attr="disabled">
        <span wire:loading.remove>اجرا</span>
        <span wire:loading>در حال اجرا...</span>
      </button>
    </div>
  </div>

  <!-- جدول خدمات -->
  <div class="services-container">
    @if ($readyToLoad)
      <div class="table-responsive text-nowrap d-none d-md-block">
        <table class="table table-hover w-100 m-0">
          <thead>
            <tr>
              <th class="text-center align-middle" style="width: 40px;">
                <div class="d-flex justify-content-center align-items-center">
                  <input type="checkbox" wire:model.live="selectAll" class="form-check-input m-0 align-middle">
                </div>
              </th>
              <th class="align-middle">نام خدمت</th>
              <th class="align-middle">نام بیمه</th>
              <th class="align-middle">توضیحات</th>
              <th class="align-middle">زمان</th>
              <th class="align-middle">قیمت</th>
              <th class="align-middle">تخفیف</th>
              <th class="align-middle">قیمت نهایی</th>
              <th class="text-center align-middle">وضعیت</th>
              <th class="text-center align-middle">عملیات</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($services as $service)
              <tr class="align-middle bg-primary-subtle service-row-main" style="border-bottom: 2px solid #e5e7eb;">
                <td class="text-center">
                  <div class="d-flex justify-content-center align-items-center">
                    @php
                      $doctorServiceIds = collect($service->doctorServices)->pluck('id')->toArray();
                      $allChildrenSelected =
                          count($doctorServiceIds) && !array_diff($doctorServiceIds, $selectedDoctorServices);
                    @endphp
                    <input type="checkbox" class="form-check-input m-0 align-middle"
                      wire:change="toggleParentCheckbox({{ $service->id }})"
                      @if ($allChildrenSelected) checked @endif>
                  </div>
                </td>
                <td colspan="9">
                  <div class="d-flex justify-content-between align-items-center w-100">
                    <span class="fw-bold text-dark" style="font-size: 1.1rem;">{{ $service->name }}</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary ms-2 px-2 py-1"
                      wire:click="toggleChildren({{ $service->id }})">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2"
                        style="transition: transform 0.2s; {{ in_array($service->id, $openServices) ? 'transform: rotate(180deg);' : '' }}">
                        <path d="M6 9l6 6 6-6" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              @if (in_array($service->id, $openServices))
                @foreach ($service->doctorServices as $doctorService)
                  <tr class="align-middle service-row-child" style="background: #fff; border-top: 3px solid #bdbdbd;">
                    <td class="text-center">
                      <div class="d-flex justify-content-center align-items-center">
                        <input type="checkbox" wire:model.live="selectedDoctorServices"
                          value="{{ $doctorService->id }}" class="form-check-input m-0 align-middle">
                      </div>
                    </td>
                    <td></td>
                    <td>{{ $doctorService->insurance->name ?? 'بیمه نامشخص' }}</td>
                    <td>{{ $doctorService->description ?? 'بدون توضیحات' }}</td>
                    <td>{{ $doctorService->duration ? $doctorService->duration . ' دقیقه' : '---' }}</td>
                    <td>
                      <span
                        class="badge bg-primary-subtle text-primary">{{ $doctorService->price ? number_format($doctorService->price) . ' تومان' : '---' }}</span>
                    </td>
                    <td>
                      <span class="badge bg-danger-subtle text-danger">
                        @if ($doctorService->discount > 0 && $doctorService->price)
                          {{ number_format(($doctorService->price * $doctorService->discount) / 100) . ' تومان' }}
                        @else
                          ---
                        @endif
                      </span>
                    </td>
                    <td>
                      <span
                        class="badge bg-success-subtle text-success">{{ $doctorService->price ? number_format($doctorService->price - ($doctorService->price * $doctorService->discount) / 100) . ' تومان' : '---' }}</span>
                    </td>
                    <td class="text-center">
                      <button wire:click="toggleStatus({{ $doctorService->id }})"
                        class="status-badge {{ $doctorService->status ? 'status-active' : 'status-inactive' }}">
                        {{ $doctorService->status ? 'فعال' : 'غیرفعال' }}
                      </button>
                    </td>
                    <td class="text-center d-flex align-itmems-center gap-2">
                      <a href="{{ route('mc.panel.doctor-services.edit', $doctorService->id) }}"
                        class="btn btn-sm btn-gradient-success px-2 py-1">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2">
                          <path
                            d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                      </a>
                      <button wire:click="confirmDelete({{ $doctorService->id }})"
                        class="btn btn-sm btn-gradient-danger px-2 py-1">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2">
                          <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                        </svg>
                      </button>
                    </td>
                  </tr>
                @endforeach
              @endif
            @empty
              <tr>
                <td colspan="10" class="text-center py-4">
                  <div class="d-flex justify-content-center align-items-center flex-column">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2" class="text-muted mb-2">
                      <path d="M5 12h14M12 5l7 7-7 7" />
                    </svg>
                    <p class="text-muted fw-medium">هیچ خدمتی یافت نشد.</p>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <!-- کارت‌های موبایل/تبلت -->
      <div class="notes-cards d-md-none">
        @if ($readyToLoad)
          @forelse ($services as $service)
            <div class="note-card mb-3" style="border: 1px solid #e5e7eb; background: #f8fafc;">
              <div class="note-card-header d-flex justify-content-between align-items-center"
                style="background: #e6f0fa;">
                <div class="d-flex align-items-center gap-2">
                  @php
                    $doctorServiceIds = collect($service->doctorServices)->pluck('id')->toArray();
                    $allChildrenSelected =
                        count($doctorServiceIds) && !array_diff($doctorServiceIds, $selectedDoctorServices);
                  @endphp
                  <input type="checkbox" class="form-check-input m-0 align-middle"
                    wire:change="toggleParentCheckbox({{ $service->id }})"
                    @if ($allChildrenSelected) checked @endif>
                  <span class="fw-bold text-dark" style="font-size: 1.1rem;">{{ $service->name }}</span>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary ms-2 px-2 py-1"
                  wire:click="toggleChildren({{ $service->id }})">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2"
                    style="transition: transform 0.2s; {{ in_array($service->id, $openServices) ? 'transform: rotate(180deg);' : '' }}">
                    <path d="M6 9l6 6 6-6" />
                  </svg>
                </button>
              </div>
              @if (in_array($service->id, $openServices))
                @foreach ($service->doctorServices as $doctorService)
                  <div class="note-card-body border-top" style="background: #fff; border-top: 3px solid #bdbdbd;">
                    <div class="note-card-item"><span class="note-card-label">نام بیمه:</span><span
                        class="note-card-value">{{ $doctorService->insurance->name ?? 'بیمه نامشخص' }}</span></div>
                    <div class="note-card-item"><span class="note-card-label">توضیحات:</span><span
                        class="note-card-value">{{ $doctorService->description ?? 'بدون توضیحات' }}</span></div>
                    <div class="note-card-item"><span class="note-card-label">زمان:</span><span
                        class="note-card-value">{{ $doctorService->duration ? $doctorService->duration . ' دقیقه' : '---' }}</span>
                    </div>
                    <div class="note-card-item"><span class="note-card-label">قیمت:</span><span
                        class="badge bg-primary-subtle text-primary">{{ $doctorService->price ? number_format($doctorService->price) . ' تومان' : '---' }}</span>
                    </div>
                    <div class="note-card-item"><span class="note-card-label">تخفیف:</span><span
                        class="badge bg-danger-subtle text-danger">
                        @if ($doctorService->discount > 0 && $doctorService->price)
                          {{ number_format(($doctorService->price * $doctorService->discount) / 100) . ' تومان' }}@else---
                        @endif
                      </span></div>
                    <div class="note-card-item"><span class="note-card-label">قیمت نهایی:</span><span
                        class="badge bg-success-subtle text-success">{{ $doctorService->price ? number_format($doctorService->price - ($doctorService->price * $doctorService->discount) / 100) . ' تومان' : '---' }}</span>
                    </div>
                    <div class="note-card-item"><span class="note-card-label">وضعیت:</span><button
                        wire:click="toggleStatus({{ $doctorService->id }})"
                        class="status-badge {{ $doctorService->status ? 'status-active' : 'status-inactive' }}">{{ $doctorService->status ? 'فعال' : 'غیرفعال' }}</button>
                    </div>
                    <div class="note-card-item">
                      <a href="{{ route('mc.panel.doctor-services.edit', $doctorService->id) }}"
                        class="btn btn-sm btn-gradient-success px-2 py-1">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2">
                          <path
                            d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                        ویرایش
                      </a>
                      <button wire:click="confirmDelete({{ $doctorService->id }})"
                        class="btn btn-sm btn-gradient-danger px-2 py-1">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2">
                          <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                        </svg>
                        حذف
                      </button>
                    </div>
                  </div>
                @endforeach
              @endif
            </div>
          @empty
            <div class="text-center py-4">
              <div class="d-flex justify-content-center align-items-center flex-column">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2" class="text-muted mb-2">
                  <path d="M5 12h14M12 5l7 7-7 7" />
                </svg>
                <p class="text-muted fw-medium">هیچ خدمتی یافت نشد.</p>
              </div>
            </div>
          @endforelse
        @else
          <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">در حال بارگذاری...</span>
            </div>
          </div>
        @endif
      </div>
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

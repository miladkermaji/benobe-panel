<div class="container-fluid py-2" dir="rtl" wire:init="loadServices">
  <div
    class="glass-header text-white p-3 rounded-3 mb-5 shadow-lg d-flex justify-content-between align-items-center flex-wrap gap-3">
    <h1 class="m-0 h3 font-thin flex-grow-1" style="min-width: 200px;">مدیریت خدمات</h1>
    <div class="input-group flex-grow-1 position-relative" style="max-width: 400px;">
      <input type="text" class="form-control border-0 shadow-none bg-white text-dark ps-5 rounded-3"
        wire:model.live="search" placeholder="جستجو در خدمات..." style="padding-right: 23px">
      <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-3" style="z-index: 5;right: 5px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
          <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
        </svg>
      </span>
    </div>
    <div class="d-flex gap-2 flex-shrink-0 justify-content-center mt-md-2">
      <a href="{{ route('admin.panel.services.create') }}"
        class="btn btn-gradient-success rounded-pill px-4 d-flex align-items-center justify-content-center gap-2 w-100 w-md-auto">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 5v14M5 12h14" />
        </svg>
        <span class="text-truncate">افزودن خدمت</span>
      </a>
      <button wire:click="deleteSelected"
        class="btn btn-gradient-danger rounded-pill px-4 d-flex align-items-center justify-content-center gap-2 w-100 w-md-auto"
        @if (empty($selectedServices)) disabled @endif>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
        </svg>
        <span class="text-truncate">حذف انتخاب‌شده‌ها</span>
      </button>
    </div>
  </div>

  <div class="container-fluid px-0">
    <div class="card shadow-sm">
      <div class="card-body p-0">
        <!-- نمایش جدول در دسکتاپ -->
        <div class="d-none d-md-block">
          <div class="table-responsive text-nowrap">
            <table class="table table-bordered table-hover w-100 m-0">
              <thead class="glass-header text-white">
                <tr>
                  <th class="text-center align-middle" style="width: 50px;">
                    <input type="checkbox" wire:model.live="selectAll" class="form-check-input m-0">
                  </th>
                  <th class="text-center align-middle" style="width: 70px;">ردیف</th>
                  <th class="align-middle">نام</th>
                  <th class="align-middle">توضیحات</th>
                  <th class="text-center align-middle" style="width: 100px;">وضعیت</th>
                  <th class="text-center align-middle" style="width: 150px;">عملیات</th>
                </tr>
              </thead>
              <tbody>
                @if ($readyToLoad)
                  @forelse ($services as $index => $item)
                    <tr>
                      <td class="text-center align-middle">
                        <input type="checkbox" wire:model.live="selectedServices" value="{{ $item->id }}"
                          class="form-check-input m-0">
                      </td>
                      <td class="text-center align-middle">{{ $services->firstItem() + $index }}</td>
                      <td class="align-middle">{{ $item->name }}</td>
                      <td class="align-middle">
                        <div class="text-truncate" style="max-width: 150px;" data-bs-toggle="tooltip"
                          data-bs-placement="top" title="{{ $item->description ?? '-' }}">
                          {{ $item->description ?? '-' }}
                        </div>
                      </td>
                      <td class="text-center align-middle">
                        <button wire:click="toggleStatus({{ $item->id }})"
                          class="badge {{ $item->status ? 'bg-label-success' : 'bg-label-danger' }} border-0 cursor-pointer">
                          {{ $item->status ? 'فعال' : 'غیرفعال' }}
                        </button>
                      </td>
                      <td class="text-center align-middle">
                        <div class="d-flex justify-content-center gap-2">
                          <a href="{{ route('admin.panel.services.edit', $item->id) }}"
                            class="btn btn-gradient-success rounded-pill px-3">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                              stroke-width="2">
                              <path
                                d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                          </a>
                          <button wire:click="confirmDelete({{ $item->id }})"
                            class="btn btn-gradient-danger rounded-pill px-3">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                              stroke-width="2">
                              <path
                                d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                            </svg>
                          </button>
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="6" class="text-center py-5">
                        <div class="d-flex justify-content-center align-items-center flex-column">
                          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" class="text-muted mb-3">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                          </svg>
                          <p class="text-muted fw-medium">هیچ خدمتی یافت نشد.</p>
                        </div>
                      </td>
                    </tr>
                  @endforelse
                @else
                  <tr>
                    <td colspan="6" class="text-center py-5">در حال بارگذاری خدمات...</td>
                  </tr>
                @endif
              </tbody>
            </table>
          </div>
        </div>

        <!-- نمایش کارت در موبایل و تبلت -->
        <div class="d-md-none">
          @if ($readyToLoad)
            @forelse ($services as $index => $item)
              <div class="card shadow-sm mb-3 border-0">
                <div class="card-body p-3">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center gap-2">
                      <input type="checkbox" wire:model.live="selectedServices" value="{{ $item->id }}"
                        class="form-check-input m-0">
                      <span class="badge bg-label-primary">#{{ $services->firstItem() + $index }}</span>
                    </div>
                    <button wire:click="toggleStatus({{ $item->id }})"
                      class="badge {{ $item->status ? 'bg-label-success' : 'bg-label-danger' }} border-0 cursor-pointer">
                      {{ $item->status ? 'فعال' : 'غیرفعال' }}
                    </button>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted">نام:</small>
                    <span class="fw-medium">{{ $item->name }}</span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <small class="text-muted">توضیحات:</small>
                    <div class="text-truncate" style="max-width: 200px;" data-bs-toggle="tooltip"
                      data-bs-placement="top" title="{{ $item->description ?? '-' }}">
                      {{ $item->description ?? '-' }}
                    </div>
                  </div>
                  <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.panel.services.edit', $item->id) }}"
                      class="btn btn-gradient-success rounded-pill px-3">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path
                          d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                      </svg>
                    </a>
                    <button wire:click="confirmDelete({{ $item->id }})"
                      class="btn btn-gradient-danger rounded-pill px-3">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            @empty
              <div class="text-center py-5">
                <div class="d-flex flex-column align-items-center justify-content-center">
                  <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" class="text-muted mb-3">
                    <path d="M5 12h14M12 5l7 7-7 7" />
                  </svg>
                  <p class="text-muted fw-medium m-0">هیچ خدمتی یافت نشد.</p>
                </div>
              </div>
            @endforelse
          @else
            <div class="text-center py-5">در حال بارگذاری خدمات...</div>
          @endif
        </div>
        <div class="d-flex justify-content-between align-items-center mt-4 px-4 flex-wrap gap-3">
          <div class="text-muted">نمایش {{ $services ? $services->firstItem() : 0 }} تا
            {{ $services ? $services->lastItem() : 0 }} از {{ $services ? $services->total() : 0 }} ردیف
          </div>
          @if ($services && $services->hasPages())
            {{ $services->links('livewire::bootstrap') }}
          @endif
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
            Livewire.dispatch('deleteServiceConfirmed', {
              id: event.id
            });
          }
        });
      });

      // Initialize tooltips
      const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
      [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    });
  </script>
</div>

<div class="container-fluid py-2" dir="rtl" wire:init="loadbannertexts">
  <div
    class="glass-header text-white p-3 rounded-3 mb-5 shadow-lg d-flex justify-content-between align-items-center flex-column flex-md-row gap-3">
    <h1 class="m-0 h3 font-thin flex-grow-1" style="min-width: 200px;">مدیریت بنر صفحه اصلی</h1>
    <div class="input-group flex-grow-1 position-relative" style="max-width: 400px;">
      <input type="text" class="form-control border-0 shadow-none bg-white text-dark ps-5 rounded-3"
        wire:model.live.debounce.300ms="search" placeholder="جستجو در بنرها..." style="padding-right: 23px">
      <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-3" style="z-index: 5;right: 5px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
          <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
        </svg>
      </span>
    </div>
    <div class="d-flex gap-2 flex-shrink-0 flex-wrap justify-content-center mt-md-2 buttons-container">
      <a href="{{ route('admin.panel.banner-texts.create') }}"
        class="btn btn-gradient-success rounded-pill px-4 d-flex align-items-center gap-2" style="white-space: nowrap;">
        <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path d="M12 5v14M5 12h14" />
        </svg>
        <span>افزودن بنر</span>
      </a>
      <button wire:click="deleteSelected" wire:loading.attr="disabled"
        class="btn btn-gradient-danger rounded-pill px-4 d-flex align-items-center gap-2"
        @if (empty($selectedbannertexts)) disabled @endif style="white-space: nowrap;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
        </svg>
        <span wire:loading.remove wire:target="deleteSelected">حذف انتخاب‌شده‌ها</span>
        <span wire:loading wire:target="deleteSelected">در حال حذف...</span>
      </button>
    </div>
  </div>

  <div class="container-fluid px-0">
    <div class="card shadow-sm">
      <div class="card-body p-0">
        <!-- Desktop View -->
        <div class="table-responsive text-nowrap d-none d-md-block">
          <table class="table table-bordered table-hover w-100 m-0">
            <thead class="glass-header text-white">
              <tr>
                <th class="text-center align-middle" style="width: 50px;">
                  <input type="checkbox" wire:model.live="selectAll" class="form-check-input m-0 align-middle">
                </th>
                <th class="text-center align-middle" style="width: 70px;">ردیف</th>
                <th class="align-middle">متن اصلی</th>
                <th class="align-middle">کلمات متغیر</th>
                <th class="align-middle">فاصله تعویض (ثانیه)</th>
                <th class="align-middle">تصویر</th>
                <th class="text-center align-middle" style="width: 100px;">وضعیت</th>
                <th class="text-center align-middle" style="width: 150px;">عملیات</th>
              </tr>
            </thead>
            <tbody>
              @if ($readyToLoad)
                @forelse ($bannertexts as $index => $item)
                  <tr>
                    <td class="text-center align-middle">
                      <input type="checkbox" wire:model.live="selectedbannertexts" value="{{ $item->id }}"
                        class="form-check-input m-0 align-middle">
                    </td>
                    <td class="text-center align-middle">{{ $bannertexts->firstItem() + $index }}</td>
                    <td class="align-middle text-truncate" style="max-width: 200px;" title="{{ $item->main_text }}">
                      {{ $item->main_text }}
                    </td>
                    <td class="align-middle text-truncate" style="max-width: 200px;"
                      title="{{ $item->switch_words ? implode(', ', $item->switch_words) : '-' }}">
                      {{ $item->switch_words ? implode(', ', $item->switch_words) : '-' }}
                    </td>
                    <td class="align-middle">{{ $item->switch_interval ?? '-' }}</td>
                    <td class="align-middle">
                      @if ($item->image_path)
                        <img src="{{ asset('storage/' . $item->image_path) }}" alt="تصویر بنر"
                          style="max-width: 50px; border-radius: 5px;">
                      @else
                        -
                      @endif
                    </td>
                    <td class="text-center align-middle">
                      <button wire:click="toggleStatus({{ $item->id }})"
                        class="badge {{ $item->status ? 'bg-label-success' : 'bg-label-danger' }} border-0 cursor-pointer">
                        {{ $item->status ? 'فعال' : 'غیرفعال' }}
                      </button>
                    </td>
                    <td class="text-center align-middle">
                      <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('admin.panel.banner-texts.edit', $item->id) }}"
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
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="8" class="text-center py-5">
                      <div class="d-flex justify-content-center align-items-center flex-column">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2" class="text-muted mb-3">
                          <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                        <p class="text-muted fw-medium">هیچ بنری یافت نشد.</p>
                      </div>
                    </td>
                  </tr>
                @endforelse
              @else
                <tr>
                  <td colspan="8" class="text-center py-5">
                    <div class="d-flex justify-content-center align-items-center flex-column">
                      <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">در حال بارگذاری...</span>
                      </div>
                      <p class="text-muted fw-medium">در حال بارگذاری بنرها...</p>
                    </div>
                  </td>
                </tr>
              @endif
            </tbody>
          </table>
        </div>

        <!-- Mobile/Tablet View -->
        <div class="d-md-none">
          @if ($readyToLoad)
            @forelse ($bannertexts as $index => $item)
              <div class="card m-3 border-0 shadow-sm">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center gap-2">
                      <input type="checkbox" wire:model.live="selectedbannertexts" value="{{ $item->id }}"
                        class="form-check-input m-0 align-middle">
                      <span class="text-muted">{{ $bannertexts->firstItem() + $index }}</span>
                    </div>
                    <button wire:click="toggleStatus({{ $item->id }})"
                      class="badge {{ $item->status ? 'bg-label-success' : 'bg-label-danger' }} border-0 cursor-pointer">
                      {{ $item->status ? 'فعال' : 'غیرفعال' }}
                    </button>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">متن اصلی:</span>
                    <span class="fw-medium text-truncate" style="max-width: 200px;" title="{{ $item->main_text }}">
                      {{ $item->main_text }}
                    </span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">کلمات متغیر:</span>
                    <span class="fw-medium text-truncate" style="max-width: 200px;"
                      title="{{ $item->switch_words ? implode(', ', $item->switch_words) : '-' }}">
                      {{ $item->switch_words ? implode(', ', $item->switch_words) : '-' }}
                    </span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">فاصله تعویض:</span>
                    <span class="fw-medium">{{ $item->switch_interval ?? '-' }} ثانیه</span>
                  </div>
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">تصویر:</span>
                    <span class="fw-medium">
                      @if ($item->image_path)
                        <img src="{{ asset('storage/' . $item->image_path) }}" alt="تصویر بنر"
                          style="max-width: 100px; border-radius: 5px;">
                      @else
                        -
                      @endif
                    </span>
                  </div>
                  <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.panel.banner-texts.edit', $item->id) }}"
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
                <div class="d-flex justify-content-center align-items-center flex-column">
                  <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" class="text-muted mb-3">
                    <path d="M5 12h14M12 5l7 7-7 7" />
                  </svg>
                  <p class="text-muted fw-medium">هیچ بنری یافت نشد.</p>
                </div>
              </div>
            @endforelse
          @else
            <div class="text-center py-5">
              <div class="d-flex justify-content-center align-items-center flex-column">
                <div class="spinner-border text-primary mb-3" role="status">
                  <span class="visually-hidden">در حال بارگذاری...</span>
                </div>
                <p class="text-muted fw-medium">در حال بارگذاری بنرها...</p>
              </div>
            </div>
          @endif
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4 px-4 flex-wrap gap-3">
          <div class="text-muted">نمایش {{ $bannertexts ? $bannertexts->firstItem() : 0 }} تا
            {{ $bannertexts ? $bannertexts->lastItem() : 0 }} از {{ $bannertexts ? $bannertexts->total() : 0 }} ردیف
          </div>
          @if ($bannertexts && $bannertexts->hasPages())
            {{ $bannertexts->links('livewire::bootstrap') }}
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
          title: 'حذف بنر',
          text: 'آیا مطمئن هستید که می‌خواهید این بنر را حذف کنید؟',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#ef4444',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'بله، حذف کن',
          cancelButtonText: 'خیر'
        }).then((result) => {
          if (result.isConfirmed) {
            Livewire.dispatch('deleteBannerTextConfirmed', {
              id: event.id
            });
          }
        });
      });
    });
  </script>
</div>

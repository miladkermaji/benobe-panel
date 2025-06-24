<div class="container-fluid py-2" dir="rtl" wire:init="loadRedirects">
  <div
    class="glass-header text-white p-3 rounded-3 mb-5 shadow-lg d-flex justify-content-between align-items-center flex-wrap gap-3">
    <h1 class="m-0 h3 font-thin flex-grow-1" style="min-width: 200px;">مدیریت ریدایرکت‌ها</h1>
    <div class="search-box position-relative flex-grow-1" style="max-width: 400px;">
      <input type="text" class="form-control border-0 shadow-none bg-white text-dark ps-5 " wire:model.live="search"
        placeholder="جستجو در ریدایرکت‌ها...">
      <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-3" style="z-index: 5;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
          <circle cx="11" cy="11" r="8" />
          <path d="M21 21l-4.35-4.35" />
        </svg>
      </span>
    </div>
    <div class="d-flex gap-2 flex-shrink-0 flex-wrap justify-content-center buttons-container">
      <a href="{{ route('admin.panel.tools.redirects.create') }}"
        class="btn btn-success text-white px-4 d-flex align-items-center gap-2">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 5v14M5 12h14" />
        </svg>
        <span>افزودن ریدایرکت</span>
      </a>
      <button wire:click="deleteSelected" class="btn btn-gradient-danger  px-4 d-flex align-items-center gap-2"
        @if (empty($selectedRedirects)) disabled @endif>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
        </svg>
        <span>حذف انتخاب‌شده‌ها</span>
      </button>
    </div>
  </div>

  <!-- جدول ریدایرکت‌ها -->
  <div class="container-fluid px-0">
    <div class="card shadow-sm">
      <div class="card-body p-0">
        <!-- Desktop View -->
        <div class="d-none d-lg-block">
          <div class="table-responsive text-nowrap">
            <table class="table table-bordered table-hover w-100 m-0">
              <thead class="glass-header text-white">
                <tr>
                  <th class="text-center align-middle" style="width: 50px;">
                    <input type="checkbox" wire:model.live="selectAll" class="form-check-input m-0 align-middle">
                  </th>
                  <th class="text-center align-middle" style="width: 70px;">ردیف</th>
                  <th class="align-middle">آدرس مبدا</th>
                  <th class="align-middle">آدرس مقصد</th>
                  <th class="text-center align-middle" style="width: 100px;">کد وضعیت</th>
                  <th class="text-center align-middle" style="width: 100px;">وضعیت</th>
                  <th class="text-center align-middle" style="width: 150px;">عملیات</th>
                </tr>
              </thead>
              <tbody>
                @if ($readyToLoad)
                  @forelse ($redirects as $index => $redirect)
                    <tr>
                      <td class="text-center align-middle">
                        <input type="checkbox" wire:model.live="selectedRedirects" value="{{ $redirect->id }}"
                          class="form-check-input m-0 align-middle">
                      </td>
                      <td class="text-center align-middle">{{ $redirects->firstItem() + $index }}</td>
                      <td class="align-middle">{{ $redirect->source_url }}</td>
                      <td class="align-middle">{{ $redirect->target_url }}</td>
                      <td class="text-center align-middle">{{ $redirect->status_code }}</td>
                      <td class="text-center align-middle">
                        <div class="form-check form-switch d-flex justify-content-center">
                          <input class="form-check-input" type="checkbox" role="switch"
                            wire:click="toggleStatus({{ $redirect->id }})" @checked($redirect->is_active)>
                        </div>
                      </td>
                      <td class="text-center align-middle">
                        <div class="d-flex justify-content-center gap-2">
                          <a href="{{ route('admin.panel.tools.redirects.edit', $redirect->id) }}"
                            class="btn btn-custom rounded-circle p-2 d-flex align-items-center justify-content-center">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                              stroke-width="2">
                              <path
                                d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                          </a>
                          <button wire:click="confirmDelete({{ $redirect->id }})"
                            class="btn btn-danger rounded-circle p-2 d-flex align-items-center justify-content-center">
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
                      <td colspan="7" class="text-center py-5">
                        <div class="d-flex justify-content-center align-items-center flex-column">
                          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" class="text-muted mb-3">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                          </svg>
                          <p class="text-muted fw-medium">هیچ ریدایرکتی یافت نشد.</p>
                        </div>
                      </td>
                    </tr>
                  @endforelse
                @else
                  <tr>
                    <td colspan="7" class="text-center py-5">در حال بارگذاری ریدایرکت‌ها...</td>
                  </tr>
                @endif
              </tbody>
            </table>
          </div>
        </div>

        <!-- Mobile & Tablet View -->
        <div class="d-lg-none">
          @if ($readyToLoad)
            @forelse($redirects as $redirect)
              <div class="card mb-3 shadow-sm">
                <div class="card-body d-flex flex-column gap-2">
                  <div class="d-flex align-items-center justify-content-between">
                    <div class="form-check">
                      <input type="checkbox" wire:model.live="selectedRedirects" value="{{ $redirect->id }}"
                        class="form-check-input">
                    </div>
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" role="switch"
                        wire:click="toggleStatus({{ $redirect->id }})" @checked($redirect->is_active)>
                    </div>
                  </div>
                  <div class="d-flex flex-column gap-1">
                    <div class="d-flex justify-content-between align-items-center">
                      <span class="text-muted small">آدرس مبدا:</span>
                      <span class="fw-medium">{{ $redirect->source_url }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                      <span class="text-muted small">آدرس مقصد:</span>
                      <span class="fw-medium">{{ $redirect->target_url }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                      <span class="text-muted small">کد وضعیت:</span>
                      <span class="fw-medium">{{ $redirect->status_code }}</span>
                    </div>
                  </div>
                  <div class="d-flex align-items-center gap-2 justify-content-end mt-2">
                    <a href="{{ route('admin.panel.tools.redirects.edit', $redirect->id) }}"
                      class="btn btn-custom rounded-circle p-1 d-flex align-items-center justify-content-center">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path
                          d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                      </svg>
                    </a>
                    <button wire:click="confirmDelete({{ $redirect->id }})"
                      class="btn btn-danger rounded-circle p-1 d-flex align-items-center justify-content-center">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            @empty
              <div class="text-center text-muted p-3">
                <div class="d-flex justify-content-center align-items-center flex-column">
                  <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" class="text-muted mb-3">
                    <path d="M5 12h14M12 5l7 7-7 7" />
                  </svg>
                  <p class="text-muted fw-medium">هیچ ریدایرکتی یافت نشد.</p>
                </div>
              </div>
            @endforelse
          @else
            <div class="text-center text-muted p-3">
              در حال بارگذاری ریدایرکت‌ها...
            </div>
          @endif
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4 px-4 flex-wrap gap-3">
          <div class="text-muted">نمایش {{ $redirects ? $redirects->firstItem() : 0 }} تا
            {{ $redirects ? $redirects->lastItem() : 0 }} از {{ $redirects ? $redirects->total() : 0 }} ردیف
          </div>
          @if ($redirects && $redirects->hasPages())
            {{ $redirects->links('livewire::bootstrap') }}
          @endif
        </div>
      </div>
    </div>
  </div>
  <link rel="stylesheet" href="{{ asset('admin-assets/panel/css/tools/redirect/redirect.css') }}">


  <script>
    document.addEventListener('livewire:init', function() {
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });

      Livewire.on('confirm-delete', (event) => {
        Swal.fire({
          title: 'حذف ریدایرکت',
          text: 'آیا مطمئن هستید که می‌خواهید این ریدایرکت را حذف کنید؟',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#ef4444',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'بله، حذف کن',
          cancelButtonText: 'خیر'
        }).then((result) => {
          if (result.isConfirmed) {
            Livewire.dispatch('deleteRedirectConfirmed', {
              id: event.id
            });
          }
        });
      });
    });
  </script>
</div>

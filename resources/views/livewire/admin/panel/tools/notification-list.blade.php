<div class="container-fluid py-2 mt-3" dir="rtl" wire:init="loadNotifications">
  <!-- Header -->
  <div
    class="glass-header text-white p-3 rounded-3 mb-4 shadow-lg d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3 w-100">
    <div class="d-flex flex-column flex-md-row gap-2 w-100 align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-3">
        <h1 class="m-0 h3 fw-light">مدیریت اعلان‌ها</h1>
      </div>
      <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2">
        <div class="d-flex gap-2 flex-shrink-0 justify-content-center">
          <div class="search-container position-relative" style="max-width: 100%;">
            <input type="text"
              class="form-control search-input border-0 shadow-none bg-white text-dark ps-4 rounded-2 text-start"
              wire:model.live="search" placeholder="جستجو در اعلان‌ها..."
              style="padding-right: 20px; text-align: right; direction: rtl;">
            <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-2"
              style="z-index: 5; top: 50%; right: 8px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
                <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
              </svg>
            </span>
          </div>
          <a href="{{ route('admin.panel.tools.notifications.create') }}"
            class="btn btn-gradient-success btn-gradient-success-576 rounded-1 px-3 py-1 d-flex align-items-center gap-1">
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

  <div class="container-fluid px-0">
    <!-- Group Actions (Desktop & Mobile) -->
    <div class="group-actions p-2 border-bottom mb-2" x-data="{ show: false }"
      x-show="$wire.selectedNotifications.length > 0">
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
    <!-- Desktop Table View -->
    <div class="card shadow-sm d-none d-lg-block rounded-2">
      <div class="card-body p-0">
        <div class="table-responsive text-nowrap">
          <table class="table  w-100 m-0 align-middle">
            <thead class="table-dark">
              <tr>
                <th class="text-center align-middle" style="width: 50px;">
                  <input type="checkbox" wire:model.live="selectAll" class="form-check-input m-0 align-middle">
                </th>
                <th class="text-center align-middle" style="width: 70px;">ردیف</th>
                <th class="align-middle">عنوان</th>
                <th class="align-middle">پیام</th>
                <th class="align-middle">نوع</th>
                <th class="align-middle">هدف</th>
                <th class="text-center align-middle" style="width: 100px;">وضعیت</th>
                <th class="text-center align-middle" style="width: 150px;">عملیات</th>
              </tr>
            </thead>
            <tbody>
              @if ($readyToLoad)
                @forelse ($notifications as $index => $item)
                  <tr>
                    <td class="text-center align-middle">
                      <input type="checkbox" wire:model.live="selectedNotifications" value="{{ $item->id }}"
                        class="form-check-input m-0 align-middle">
                    </td>
                    <td class="text-center align-middle">{{ $notifications->firstItem() + $index }}</td>
                    <td class="align-middle">{{ $item->title }}</td>
                    <td class="align-middle">{{ Str::limit($item->message, 50) }}</td>
                    <td class="align-middle">
                      @php $typeData = $this->getTypeLabel($item->type); @endphp
                      <span class="badge {{ $typeData['class'] }} border-0">{{ $typeData['label'] }}</span>
                    </td>
                    <td class="align-middle">{{ $this->getTargetLabel($item) }}</td>
                    <td class="text-center align-middle">
                      <button wire:click="toggleStatus({{ $item->id }})"
                        class="badge {{ $item->is_active ? 'bg-label-success' : 'bg-label-danger' }} border-0 cursor-pointer">
                        {{ $item->is_active ? 'فعال' : 'غیرفعال' }}
                      </button>
                    </td>
                    <td class="text-center align-middle">
                      <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('admin.panel.tools.notifications.edit', $item->id) }}"
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
                        <p class="text-muted fw-medium">هیچ اعلانی یافت نشد.</p>
                      </div>
                    </td>
                  </tr>
                @endforelse
              @else
                <tr>
                  <td colspan="8" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                      <span class="visually-hidden">در حال بارگذاری...</span>
                    </div>
                  </td>
                </tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Mobile/Tablet Card View -->
    <div class="d-lg-none">
      @if ($readyToLoad)
        @forelse ($notifications as $index => $item)
          <div class="note-card mb-3">
            <div class="note-card-header d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center gap-2">
                <input type="checkbox" wire:model.live="selectedNotifications" value="{{ $item->id }}"
                  class="form-check-input m-0 align-middle">
                <span class="badge {{ $this->getTypeLabel($item->type)['class'] }}">
                  {{ $this->getTypeLabel($item->type)['label'] }}
                </span>
              </div>
              <div class="d-flex gap-1">
                <a href="{{ route('admin.panel.tools.notifications.edit', $item->id) }}"
                  class="btn btn-sm btn-gradient-success  px-2 py-1">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path
                      d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                  </svg>
                </a>
                <button wire:click="confirmDelete({{ $item->id }})"
                  class="btn btn-sm btn-gradient-danger  px-2 py-1">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                  </svg>
                </button>
              </div>
            </div>
            <div class="note-card-body">
              <div class="note-card-item">
                <span class="note-card-label">عنوان:</span>
                <span class="note-card-value">{{ $item->title }}</span>
              </div>
              <div class="note-card-item">
                <span class="note-card-label">پیام:</span>
                <span class="note-card-value">{{ Str::limit($item->message, 100) }}</span>
              </div>
              <div class="note-card-item">
                <span class="note-card-label">هدف:</span>
                <span class="note-card-value">{{ $this->getTargetLabel($item) }}</span>
              </div>
              <div class="note-card-item">
                <span class="note-card-label">وضعیت:</span>
                <div class="form-check form-switch d-inline-block">
                  <input class="form-check-input" type="checkbox" role="switch"
                    wire:click="toggleStatus({{ $item->id }})" {{ $item->is_active ? 'checked' : '' }}
                    style="width: 3em; height: 1.5em; margin-top: 0;">
                </div>
              </div>
            </div>
          </div>
        @empty
          <div class="text-center py-4">
            <div class="d-flex justify-content-center align-items-center flex-column">
              <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" class="text-muted mb-2">
                <path d="M5 12h14M12 5l7 7-7 7" />
              </svg>
              <p class="text-muted fw-medium">هیچ اعلانی یافت نشد.</p>
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

    @if ($readyToLoad && $notifications->hasPages())
      <div class="d-flex justify-content-between align-items-center mt-4 px-4 flex-wrap gap-3">
        <div class="text-muted">نمایش {{ $notifications->firstItem() }} تا
          {{ $notifications->lastItem() }} از {{ $notifications->total() }} ردیف</div>
        {{ $notifications->links('livewire::bootstrap') }}
      </div>
    @endif
  </div>
  <link rel="stylesheet" href="{{ asset('admin-assets/panel/css/tools/notification/notifiation.css') }}">

  <script>
    document.addEventListener('livewire:init', function() {
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });
      Livewire.on('confirm-delete', (event) => {
        Swal.fire({
          title: 'حذف اعلان',
          text: 'آیا مطمئن هستید که می‌خواهید این اعلان را حذف کنید؟',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#ef4444',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'بله، حذف کن',
          cancelButtonText: 'خیر'
        }).then((result) => {
          if (result.isConfirmed) {
            Livewire.dispatch('deleteNotificationConfirmed', {
              id: event.id
            });
          }
        });
      });
    });
  </script>
</div>

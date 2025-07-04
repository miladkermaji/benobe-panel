<div class="container-fluid py-2" dir="rtl" wire:init="loadNotifications">
  <div
    class="glass-header text-white p-3 rounded-3 mb-5 shadow-lg d-flex justify-content-between align-items-center flex-wrap gap-3">
    <h1 class="m-0 h3 font-thin flex-grow-1" style="min-width: 200px;">مدیریت اعلان‌ها</h1>
    <div class="input-group flex-grow-1 position-relative" style="max-width: 400px;">
      <input type="text" class="form-control border-0 shadow-none bg-white text-dark ps-5 rounded-3"
        wire:model.live="search" placeholder="جستجو در اعلان‌ها..." style="padding-right: 23px">
      <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-3"
        style="z-index: 5; right: 5px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
          <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
        </svg>
      </span>
    </div>
    <div class="d-flex gap-2 flex-shrink-0 flex-wrap justify-content-center mt-md-2 buttons-container">
      <a href="{{ route('admin.panel.tools.notifications.create') }}"
        class="btn btn-gradient-success rounded-pill px-4 d-flex align-items-center gap-2">
        <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path d="M12 5v14M5 12h14" />
        </svg>
        <span>افزودن اعلان</span>
      </a>
      <button wire:click="deleteSelected"
        class="btn btn-gradient-danger rounded-pill px-4 d-flex align-items-center gap-2"
        @if (empty($selectedNotifications)) disabled @endif>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
        </svg>
        <span>حذف انتخاب‌شده‌ها</span>
      </button>
    </div>
  </div>

  <div class="container-fluid px-0">
    <!-- Desktop Table View -->
    <div class="card shadow-sm d-none d-lg-block">
      <div class="card-body p-0">
        <div class="table-responsive text-nowrap">
          <table class="table table-bordered table-hover w-100 m-0">
            <thead class="glass-header text-white">
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
                      @php
                        $typeData = $this->getTypeLabel($item->type);
                      @endphp
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
                  <td colspan="8" class="text-center py-5">در حال بارگذاری اعلان‌ها...</td>
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
          <div class="notification-card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center gap-2">
                <input type="checkbox" wire:model.live="selectedNotifications" value="{{ $item->id }}"
                  class="form-check-input m-0 align-middle">
                <span class="notification-number">{{ $notifications->firstItem() + $index }}</span>
              </div>
              <div class="d-flex gap-2">
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
            </div>
            <div class="card-body">
              <h5 class="card-title mb-3">{{ $item->title }}</h5>
              <p class="card-text mb-3">{{ Str::limit($item->message, 100) }}</p>
              <div class="d-flex flex-wrap gap-2 mb-3">
                @php
                  $typeData = $this->getTypeLabel($item->type);
                @endphp
                <span class="badge {{ $typeData['class'] }} border-0">{{ $typeData['label'] }}</span>
                <span class="badge bg-label-secondary border-0">{{ $this->getTargetLabel($item) }}</span>
              </div>
              <button wire:click="toggleStatus({{ $item->id }})"
                class="badge {{ $item->is_active ? 'bg-label-success' : 'bg-label-danger' }} border-0 cursor-pointer">
                {{ $item->is_active ? 'فعال' : 'غیرفعال' }}
              </button>
            </div>
          </div>
        @empty
          <div class="text-center py-5">
            <div class="d-flex justify-content-center align-items-center flex-column">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" class="text-muted mb-3">
                <path d="M5 12h14M12 5l7 7-7 7" />
              </svg>
              <p class="text-muted fw-medium">هیچ اعلانی یافت نشد.</p>
            </div>
          </div>
        @endforelse
      @else
        <div class="text-center py-5">در حال بارگذاری اعلان‌ها...</div>
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

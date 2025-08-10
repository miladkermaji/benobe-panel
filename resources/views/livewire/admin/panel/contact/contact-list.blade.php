<div class="doctor-notes-container" x-data="{ mobileSearchOpen: false }">
  <div class="container py-2 mt-3" dir="rtl" wire:init="loadContacts">
    <!-- Header -->
    <header class="glass-header text-white p-3 rounded-3  shadow-lg">
      <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 w-100">
        <!-- Title Section -->
        <div class="d-flex align-items-center gap-2 flex-shrink-0 w-md-100 justify-content-between">
          <h2 class="mb-0 fw-bold fs-5">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              class="header-icon">
              <path
                d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
            </svg>
            مدیریت پیام‌های تماس
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
              <input type="text" wire:model.live="search" class="form-control ps-5"
                placeholder="جستجو در نام، ایمیل، موضوع و پیام...">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" class="search-icon">
                <circle cx="11" cy="11" r="8" />
                <path d="M21 21l-4.35-4.35" />
              </svg>
            </div>
            <select class="form-select form-select-sm" wire:model.live="statusFilter">
              <option value="">همه وضعیت‌ها</option>
              <option value="new">جدید</option>
              <option value="read">خوانده شده</option>
              <option value="replied">پاسخ داده شده</option>
              <option value="closed">بسته شده</option>
            </select>
            <div class="d-flex align-items-center gap-2 justify-content-between">
              <span class="badge bg-white text-primary px-2 py-1 fw-medium flex-shrink-0">
                {{ $readyToLoad ? $contacts->total() : 0 }}
              </span>
            </div>
          </div>
        </div>
        <!-- Desktop Search and Actions -->
        <div class="d-none d-md-flex align-items-center gap-3 ms-auto">
          <div class="search-box position-relative">
            <input type="text" wire:model.live="search" class="form-control ps-5"
              placeholder="جستجو در نام، ایمیل، موضوع و پیام...">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              class="search-icon">
              <circle cx="11" cy="11" r="8" />
              <path d="M21 21l-4.35-4.35" />
            </svg>
          </div>
          <select class="form-select form-select-sm" style="min-width: 200px;" wire:model.live="statusFilter">
            <option value="">همه وضعیت‌ها</option>
            <option value="new">جدید</option>
            <option value="read">خوانده شده</option>
            <option value="replied">پاسخ داده شده</option>
            <option value="closed">بسته شده</option>
          </select>
          <span class="badge bg-white text-primary px-2 py-1 fw-medium flex-shrink-0">
            {{ $readyToLoad ? $contacts->total() : 0 }}
          </span>
        </div>
      </div>
    </header>
    <div class="container-fluid px-0">
      <div class="card shadow-sm rounded-2">
        <div class="card-body p-0">
          <!-- Group Actions -->
          <div class="group-actions p-2 border-bottom" x-data="{ show: false }"
            x-show="$wire.selectedContacts.length > 0 || $wire.applyToAllFiltered">
            <div class="d-flex align-items-center gap-2 justify-content-end">
              <select class="form-select form-select-sm" style="max-width: 200px;" wire:model="groupAction">
                <option value="">عملیات گروهی</option>
                <option value="mark_read">علامت‌گذاری به عنوان خوانده شده</option>
                <option value="mark_replied">علامت‌گذاری به عنوان پاسخ داده شده</option>
                <option value="mark_closed">علامت‌گذاری به عنوان بسته شده</option>
                <option value="delete">حذف انتخاب شده‌ها</option>
              </select>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="applyToAllFiltered"
                  wire:model="applyToAllFiltered">
                <label class="form-check-label" for="applyToAllFiltered">
                  اعمال روی همه نتایج فیلترشده ({{ $totalFilteredCount ?? 0 }})
                </label>
              </div>
              <button class="btn btn-sm btn-primary" wire:click="executeGroupAction" wire:loading.attr="disabled">
                <span wire:loading.remove>اجرا</span>
                <span wire:loading>در حال اجرا...</span>
              </button>
            </div>
          </div>
          <!-- Desktop Table View -->
          <div class="table-responsive text-nowrap d-none d-md-block">
            <table class="table table-hover w-100 m-0">
              <thead>
                <tr>
                  <th class="text-center align-middle" style="width: 40px;">
                    <input type="checkbox" wire:model.live="selectAll" class="form-check-input m-0 align-middle">
                  </th>
                  <th class="text-center align-middle" style="width: 60px;">ردیف</th>
                  <th class="align-middle">فرستنده</th>
                  <th class="align-middle">موضوع</th>
                  <th class="text-center align-middle" style="width: 100px;">وضعیت</th>
                  <th class="text-center align-middle" style="width: 120px;">تاریخ ارسال</th>
                  <th class="text-center align-middle" style="width: 150px;">عملیات</th>
                </tr>
              </thead>
              <tbody>
                @if ($readyToLoad)
                  @forelse ($contacts as $index => $item)
                    <tr>
                      <td class="text-center align-middle">
                        <input type="checkbox" wire:model.live="selectedContacts" value="{{ $item->id }}"
                          class="form-check-input m-0 align-middle">
                      </td>
                      <td class="text-center align-middle">{{ $contacts->firstItem() + $index }}</td>
                      <td class="align-middle">
                        <div class="d-flex flex-column">
                          <span class="fw-medium text-dark">{{ $item->full_name }}</span>
                          <small class="text-muted">{{ $item->email }}</small>
                          <small class="text-muted">{{ $item->full_phone }}</small>
                        </div>
                      </td>
                      <td class="align-middle">
                        <div class="d-flex flex-column">
                          <span class="fw-medium text-dark">{{ Str::limit($item->subject, 40) }}</span>
                          <small class="text-muted">{{ Str::limit($item->message, 60) }}</small>
                        </div>
                      </td>
                      <td class="text-center align-middle">
                        <span class="badge {{ $item->status_badge_class }}">
                          {{ $item->status_display_name }}
                        </span>
                      </td>
                      <td class="text-center align-middle">
                        <small class="text-muted">{{ $item->created_at ? jdate($item->created_at)->format('Y/m/d H:i') : 'نامشخص' }}</small>
                      </td>
                      <td class="text-center align-middle">
                        <div class="d-flex justify-content-center gap-2">
                          <a href="{{ route('admin.panel.contact.show', $item->id) }}"
                            class="btn btn-gradient-primary rounded-pill px-3">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                              stroke="currentColor" stroke-width="2">
                              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                              <circle cx="12" cy="12" r="3" />
                            </svg>
                          </a>
                          <div class="dropdown">
                            <button class="btn btn-gradient-secondary rounded-pill px-3 dropdown-toggle"
                              type="button" data-bs-toggle="dropdown">
                              <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M12 5v14M5 12h14" />
                              </svg>
                            </button>
                            <ul class="dropdown-menu">
                              <li><a class="dropdown-item" href="#"
                                  wire:click="confirmUpdateStatus({{ $item->id }}, 'read')">علامت‌گذاری به عنوان
                                  خوانده شده</a></li>
                              <li><a class="dropdown-item" href="#"
                                  wire:click="confirmUpdateStatus({{ $item->id }}, 'replied')">علامت‌گذاری به
                                  عنوان پاسخ داده شده</a></li>
                              <li><a class="dropdown-item" href="#"
                                  wire:click="confirmUpdateStatus({{ $item->id }}, 'closed')">علامت‌گذاری به عنوان
                                  بسته شده</a></li>
                              <li>
                                <hr class="dropdown-divider">
                              </li>
                              <li><a class="dropdown-item text-danger" href="#"
                                  wire:click="confirmDelete({{ $item->id }})">حذف</a></li>
                            </ul>
                          </div>
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="7" class="text-center py-4">
                        <div class="d-flex justify-content-center align-items-center flex-column">
                          <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" class="text-muted mb-2">
                            <path
                              d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                          </svg>
                          <p class="text-muted fw-medium">هیچ پیامی یافت نشد.</p>
                        </div>
                      </td>
                    </tr>
                  @endforelse
                @else
                  <tr>
                    <td colspan="7" class="text-center py-4">
                      <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">در حال بارگذاری...</span>
                      </div>
                    </td>
                  </tr>
                @endif
              </tbody>
            </table>
          </div>
          <!-- Mobile Card View -->
          <div class="notes-cards d-md-none">
            @if ($readyToLoad)
              @forelse ($contacts as $index => $item)
                <div class="note-card mb-2" x-data="{ open: false }">
                  <div class="note-card-header d-flex justify-content-between align-items-center px-2 py-2"
                    @click="open = !open" style="cursor:pointer;">
                    <div class="d-flex align-items-center gap-2">
                      <input type="checkbox" wire:model.live="selectedContacts" value="{{ $item->id }}"
                        class="form-check-input m-0" @click.stop>
                      <span class="fw-bold">
                        {{ Str::limit($item->subject, 40) }}
                      </span>
                    </div>
                    <svg :class="{ 'rotate-180': open }" width="20" height="20" viewBox="0 0 24 24"
                      fill="none" stroke="currentColor" stroke-width="2" style="transition: transform 0.2s;">
                      <path d="M6 9l6 6 6-6" />
                    </svg>
                  </div>
                  <div class="note-card-body px-2 py-2" x-show="open" x-transition>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">فرستنده:</span>
                      <span class="note-card-value">{{ $item->full_name }}</span>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">ایمیل:</span>
                      <span class="note-card-value">{{ $item->email }}</span>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">تلفن:</span>
                      <span class="note-card-value">{{ $item->full_phone }}</span>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">موضوع:</span>
                      <span class="note-card-value">{{ $item->subject }}</span>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">پیام:</span>
                      <span class="note-card-value">{{ Str::limit($item->message, 100) }}</span>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">تاریخ ارسال:</span>
                      <span class="note-card-value">{{ $item->created_at ? jdate($item->created_at)->format('Y/m/d H:i') : 'نامشخص' }}</span>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">وضعیت:</span>
                      <span class="badge {{ $item->status_badge_class }}">
                        {{ $item->status_display_name }}
                      </span>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">عملیات:</span>
                      <div class="d-flex gap-2">
                        <a href="{{ route('admin.panel.contact.show', $item->id) }}"
                          class="btn btn-gradient-primary btn-sm rounded-pill px-3">
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                          </svg>
                        </a>
                        <button wire:click="confirmDelete({{ $item->id }})"
                          class="btn btn-gradient-danger btn-sm rounded-pill px-3">
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                          </svg>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              @empty
                <div class="text-center py-4">
                  <div class="d-flex justify-content-center align-items-center flex-column">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2" class="text-muted mb-2">
                      <path
                        d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                    </svg>
                    <p class="text-muted fw-medium">هیچ پیامی یافت نشد.</p>
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
          <!-- Pagination -->
          @if ($readyToLoad && $contacts->hasPages())
            <div class="d-flex justify-content-between align-items-center p-3 border-top">
              <div class="text-muted small">
                نمایش {{ $contacts->firstItem() ?? 0 }} تا {{ $contacts->lastItem() ?? 0 }} از
                {{ $contacts->total() }} مورد
              </div>
              <div>
                {{ $contacts->links() }}
              </div>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });

      Livewire.on('confirm-delete', (event) => {
        Swal.fire({
          title: 'حذف پیام',
          text: 'آیا مطمئن هستید که می‌خواهید این پیام را حذف کنید؟',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#ef4444',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'بله، حذف کن',
          cancelButtonText: 'خیر'
        }).then((result) => {
          if (result.isConfirmed) {
            Livewire.dispatch('deleteContactConfirmed', {
              id: event.id
            });
          }
        });
      });

      Livewire.on('confirm-update-status', (event) => {
        Swal.fire({
          title: 'تغییر وضعیت پیام',
          text: 'آیا مطمئن هستید که می‌خواهید وضعیت این پیام را به "' + event.statusText + '" تغییر دهید؟',
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#1deb3c',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'بله',
          cancelButtonText: 'خیر'
        }).then((result) => {
          if (result.isConfirmed) {
            Livewire.dispatch('updateStatusConfirmed', {
              id: event.id,
              status: event.status
            });
          }
        });
      });

      Livewire.on('confirm-delete-selected', function(data) {
        let text = data.allFiltered ?
          'آیا از حذف همه پیام‌های فیلترشده مطمئن هستید؟ این عملیات غیرقابل بازگشت است.' :
          'آیا از حذف پیام‌های انتخاب شده مطمئن هستید؟ این عملیات غیرقابل بازگشت است.';
        Swal.fire({
          title: 'تایید حذف گروهی',
          text: text,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'بله، حذف شود',
          cancelButtonText: 'لغو',
          reverseButtons: true
        }).then((result) => {
          if (result.isConfirmed) {
            if (data.allFiltered) {
              Livewire.dispatch('deleteSelectedConfirmed', 'allFiltered');
            } else {
              Livewire.dispatch('deleteSelectedConfirmed');
            }
          }
        });
      });
    });
  </script>
</div>

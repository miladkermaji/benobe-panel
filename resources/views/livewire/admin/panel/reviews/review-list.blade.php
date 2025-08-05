<div class="doctor-notes-container" x-data="{ mobileSearchOpen: false }">
  <div class="container py-2 mt-3" dir="rtl" wire:init="loadReviews">
    <!-- Header -->
    <header class="glass-header text-white p-3 rounded-3  shadow-lg">
      <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 w-100">
        <!-- Title Section -->
        <div class="d-flex align-items-center gap-2 flex-shrink-0 w-md-100 justify-content-between">
          <h2 class="mb-0 fw-bold fs-5">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              class="header-icon">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
            </svg>
            مدیریت نظرات
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
                placeholder="جستجو بر اساس نام یا متن نظر...">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" class="search-icon">
                <circle cx="11" cy="11" r="8" />
                <path d="M21 21l-4.35-4.35" />
              </svg>
            </div>
            <select class="form-select form-select-sm" wire:model.live="statusFilter">
              <option value="">همه وضعیت‌ها</option>
              <option value="active">فقط تأیید شده</option>
              <option value="inactive">فقط تأیید نشده</option>
            </select>
            <div class="d-flex align-items-center gap-2 justify-content-between">
              <a href="{{ route('admin.panel.reviews.create') }}"
                class="btn btn-success px-3 py-1 d-flex align-items-center gap-1 flex-shrink-0">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="#fff" stroke="#fff" stroke-width="2">
                  <path d="M12 5v14M5 12h14" />
                </svg>
                <span class="text-white">افزودن نظر</span>
              </a>
              <span class="badge bg-white text-primary px-2 py-1 fw-medium flex-shrink-0">
                {{ $readyToLoad ? $reviews->total() : 0 }}
              </span>
            </div>
          </div>
        </div>
        <!-- Desktop Search and Actions -->
        <div class="d-none d-md-flex align-items-center gap-3 ms-auto">
          <div class="search-box position-relative">
            <input type="text" wire:model.live="search" class="form-control ps-5"
              placeholder="جستجو بر اساس نام یا متن نظر...">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              class="search-icon">
              <circle cx="11" cy="11" r="8" />
              <path d="M21 21l-4.35-4.35" />
            </svg>
          </div>
          <select class="form-select form-select-sm" style="min-width: 150px;" wire:model.live="statusFilter">
            <option value="">همه وضعیت‌ها</option>
            <option value="active">فقط تأیید شده</option>
            <option value="inactive">فقط تأیید نشده</option>
          </select>
          <a href="{{ route('admin.panel.reviews.create') }}"
            class="btn btn-success px-3 py-1 d-flex align-items-center gap-1 flex-shrink-0">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="#fff" stroke="#fff" stroke-width="2">
              <path d="M12 5v14M5 12h14" />
            </svg>
            <span class="text-white">افزودن نظر</span>
          </a>
          <span class="badge bg-white text-primary px-2 py-1 fw-medium flex-shrink-0">
            {{ $readyToLoad ? $reviews->total() : 0 }}
          </span>
        </div>
      </div>
    </header>

    <!-- Group Actions -->
    <div class="group-actions p-2 border-bottom" x-data="{ show: false }"
      x-show="$wire.selectedReviews.length > 0 || $wire.applyToAllFiltered">
      <div class="d-flex align-items-center gap-2 justify-content-end">
        <select class="form-select form-select-sm" style="max-width: 200px;" wire:model="groupAction">
          <option value="">عملیات گروهی</option>
          <option value="delete">حذف انتخاب شده‌ها</option>
          <option value="status_active">تأیید کردن</option>
          <option value="status_inactive">عدم تأیید</option>
        </select>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="checkbox" id="applyToAllFiltered" wire:model="applyToAllFiltered">
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

    <div class="container-fluid px-0">
      <div class="card shadow-sm">
        <div class="card-body p-0">
          <!-- Desktop View -->
          <div class="table-responsive text-nowrap d-none d-md-block">
            <table class="table table-hover w-100 m-0">
              <thead>
                <tr>
                  <th class="text-center align-middle" style="width: 50px;">
                    <input type="checkbox" wire:model.live="selectAll" class="form-check-input m-0 align-middle">
                  </th>
                  <th class="text-center align-middle" style="width: 70px;">ردیف</th>
                  <th class="align-middle">نام</th>
                  <th class="align-middle">نظر</th>
                  <th class="align-middle">تصویر</th>
                  <th class="align-middle">امتیاز</th>
                  <th class="text-center align-middle" style="width: 100px;">وضعیت</th>
                  <th class="text-center align-middle" style="width: 200px;">عملیات</th>
                </tr>
              </thead>
              <tbody>
                @if ($readyToLoad)
                  @forelse ($reviews as $index => $review)
                    <tr>
                      <td class="text-center align-middle">
                        <input type="checkbox" wire:model.live="selectedReviews" value="{{ $review->id }}"
                          class="form-check-input m-0 align-middle">
                      </td>
                      <td class="text-center align-middle">{{ $reviews->firstItem() + $index }}</td>
                      <td class="align-middle">{{ $review->name ?? '-' }}</td>
                      <td class="align-middle text-truncate" style="max-width: 200px;"
                        title="{{ $review->comment }}">
                        {{ $review->comment ?? '-' }}
                      </td>
                      <td class="align-middle">
                        @if ($review->image_url)
                          <img src="{{ $review->image_url }}" alt="تصویر نظر"
                            style="max-width: 50px; height: auto;">
                        @else
                          -
                        @endif
                      </td>
                      <td class="align-middle">{{ $review->rating }}/5</td>
                      <td class="text-center align-middle">
                        <button wire:click="toggleStatus({{ $review->id }})" wire:loading.attr="disabled"
                          class="badge {{ $review->is_approved ? 'bg-label-success' : 'bg-label-danger' }} border-0 cursor-pointer">
                          <span wire:loading.remove wire:target="toggleStatus({{ $review->id }})">
                            {{ $review->is_approved ? 'تأیید شده' : 'تأیید نشده' }}
                          </span>
                          <span wire:loading wire:target="toggleStatus({{ $review->id }})">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                          </span>
                        </button>
                      </td>
                      <td class="text-center align-middle">
                        <div class="d-flex justify-content-center gap-2">
                          <a href="{{ route('admin.panel.reviews.edit', $review->id) }}"
                            class="btn btn-gradient-success rounded-pill px-3">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                              stroke="currentColor" stroke-width="2">
                              <path
                                d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                          </a>
                          <button wire:click="confirmDelete({{ $review->id }})" wire:loading.attr="disabled"
                            class="btn btn-gradient-danger rounded-pill px-3">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                              stroke="currentColor" stroke-width="2">
                              <path
                                d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                            </svg>
                          </button>
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="8" class="text-center py-4">
                        <div class="d-flex justify-content-center align-items-center flex-column">
                          <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" class="text-muted mb-2">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                          </svg>
                          <p class="text-muted fw-medium">هیچ نظری یافت نشد.</p>
                        </div>
                      </td>
                    </tr>
                  @endforelse
                @else
                  <tr>
                    <td colspan="8" class="text-center py-4">
                      <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">در حال بارگذاری...</span>
                      </div>
                    </td>
                  </tr>
                @endif
              </tbody>
            </table>
          </div>

          <!-- Mobile/Tablet View -->
          <!-- Mobile Card View -->
          <div class="notes-cards d-md-none">
            @if ($readyToLoad)
              @forelse ($reviews as $index => $review)
                <div class="note-card mb-2" x-data="{ open: false }">
                  <div class="note-card-header d-flex justify-content-between align-items-center px-2 py-2"
                    @click="open = !open" style="cursor:pointer;">
                    <div class="d-flex align-items-center gap-2">
                      <input type="checkbox" wire:model.live="selectedReviews" value="{{ $review->id }}"
                        class="form-check-input m-0" @click.stop>
                      <span class="fw-bold">{{ $review->name ?? '-' }} <span
                          class="text-muted">({{ $reviews->firstItem() + $index }})</span></span>
                    </div>
                    <svg :class="{ 'rotate-180': open }" width="20" height="20" viewBox="0 0 24 24"
                      fill="none" stroke="currentColor" stroke-width="2" style="transition: transform 0.2s;">
                      <path d="M6 9l6 6 6-6" />
                    </svg>
                  </div>
                  <div class="note-card-body px-2 py-2" x-show="open" x-transition>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">نظر:</span>
                      <span class="note-card-value text-truncate" style="max-width: 200px;"
                        title="{{ $review->comment }}">
                        {{ $review->comment ?? '-' }}
                      </span>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">تصویر:</span>
                      <span class="note-card-value">
                        @if ($review->image_url)
                          <img src="{{ $review->image_url }}" alt="تصویر نظر"
                            style="max-width: 100px; height: auto;">
                        @else
                          -
                        @endif
                      </span>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">امتیاز:</span>
                      <span class="note-card-value">{{ $review->rating }}/5</span>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">وضعیت:</span>
                      <button wire:click="toggleStatus({{ $review->id }})"
                        class="badge {{ $review->is_approved ? 'bg-success' : 'bg-danger' }} border-0 cursor-pointer">
                        {{ $review->is_approved ? 'تأیید شده' : 'تأیید نشده' }}
                      </button>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <a href="{{ route('admin.panel.reviews.edit', $review->id) }}"
                        class="btn btn-gradient-success btn-sm rounded-pill px-3">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2">
                          <path
                            d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                      </a>
                      <button wire:click="confirmDelete({{ $review->id }})"
                        class="btn btn-gradient-danger btn-sm rounded-pill px-3">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2">
                          <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                        </svg>
                      </button>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <input type="checkbox" wire:model.live="selectedReviews" value="{{ $review->id }}"
                        class="form-check-input m-0 align-middle">
                      <span class="note-card-label">انتخاب</span>
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
                    <p class="text-muted fw-medium">هیچ نظری یافت نشد.</p>
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

          <div class="d-flex justify-content-between align-items-center mt-4 px-4 flex-wrap gap-3">
            <div class="text-muted">نمایش {{ $reviews ? $reviews->firstItem() : 0 }} تا
              {{ $reviews ? $reviews->lastItem() : 0 }} از {{ $reviews ? $reviews->total() : 0 }} ردیف
            </div>
            @if ($reviews && $reviews->hasPages())
              <div class="pagination-container">
                {{ $reviews->onEachSide(1)->links('livewire::bootstrap') }}
              </div>
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
            title: 'حذف نظر',
            text: 'آیا مطمئن هستید که می‌خواهید این نظر را حذف کنید؟',

            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'بله، حذف کن',
            cancelButtonText: 'خیر'
          }).then((result) => {
            if (result.isConfirmed) {
              Livewire.dispatch('deleteReviewConfirmed', {
                id: event.id
              });
            }
          });
        });
      });
    </script>
  </div>

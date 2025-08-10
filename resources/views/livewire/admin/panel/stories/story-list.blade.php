<div class="doctor-notes-container" x-data="{ mobileSearchOpen: false }">
  <div class="container py-2 mt-3" dir="rtl" wire:init="loadStories">
    <!-- Header -->
    <header class="glass-header text-white p-3 rounded-3 shadow-lg">
      <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 w-100">
        <!-- Title Section -->
        <div class="d-flex align-items-center gap-2 flex-shrink-0 w-md-100 justify-content-between">
          <h2 class="mb-0 fw-bold fs-5">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              class="header-icon">
              <path
                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            مدیریت استوری‌ها
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
                placeholder="جستجو در عنوان و توضیحات...">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" class="search-icon">
                <circle cx="11" cy="11" r="8" />
                <path d="M21 21l-4.35-4.35" />
              </svg>
            </div>
            <select class="form-select form-select-sm" wire:model.live="statusFilter">
              <option value="">همه وضعیت‌ها</option>
              <option value="active">فعال</option>
              <option value="inactive">غیرفعال</option>
              <option value="pending">در انتظار تأیید</option>
            </select>
            <select class="form-select form-select-sm" wire:model.live="typeFilter">
              <option value="">همه انواع</option>
              <option value="image">تصویر</option>
              <option value="video">ویدیو</option>
            </select>
            <div class="d-flex align-items-center gap-2 justify-content-between">
              <a href="{{ route('admin.panel.stories.create') }}"
                class="btn btn-success px-3 py-1 d-flex align-items-center gap-1 flex-shrink-0">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="#fff" stroke="#fff" stroke-width="2">
                  <path d="M12 5v14M5 12h14" />
                </svg>
                <span class="text-white">افزودن استوری</span>
              </a>
              <span class="badge bg-white text-primary px-2 py-1 fw-medium flex-shrink-0">
                {{ $readyToLoad ? $stories->total() : 0 }}
              </span>
            </div>
          </div>
        </div>
        <!-- Desktop Search and Actions -->
        <div class="d-none d-md-flex align-items-center gap-3 ms-auto">
          <div class="search-box position-relative">
            <input type="text" wire:model.live="search" class="form-control ps-5"
              placeholder="جستجو در عنوان و توضیحات...">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              class="search-icon">
              <circle cx="11" cy="11" r="8" />
              <path d="M21 21l-4.35-4.35" />
            </svg>
          </div>
          <select class="form-select form-select-sm" style="min-width: 150px;" wire:model.live="statusFilter">
            <option value="">همه وضعیت‌ها</option>
            <option value="active">فعال</option>
            <option value="inactive">غیرفعال</option>
            <option value="pending">در انتظار تأیید</option>
          </select>
          <select class="form-select form-select-sm" style="min-width: 120px;" wire:model.live="typeFilter">
            <option value="">همه انواع</option>
            <option value="image">تصویر</option>
            <option value="video">ویدیو</option>
          </select>
          <a href="{{ route('admin.panel.stories.create') }}"
            class="btn btn-success px-3 py-1 d-flex align-items-center gap-1 flex-shrink-0">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="#fff" stroke="#fff" stroke-width="2">
              <path d="M12 5v14M5 12h14" />
            </svg>
            <span class="text-white">افزودن استوری</span>
          </a>
          <span class="badge bg-white text-primary px-2 py-1 fw-medium flex-shrink-0">
            {{ $readyToLoad ? $stories->total() : 0 }}
          </span>
        </div>
      </div>
    </header>

    <div class="container-fluid px-0">
      <div class="card shadow-sm rounded-2">
        <div class="card-body p-0">
          <!-- Group Actions -->
          <div class="group-actions p-2 border-bottom" x-data="{ show: false }"
            x-show="$wire.selectedStories.length > 0 || $wire.applyToAllFiltered">
            <div class="d-flex align-items-center gap-2 justify-content-end">
              <select class="form-select form-select-sm" style="max-width: 200px;" wire:model="groupAction">
                <option value="">عملیات گروهی</option>
                <option value="delete">حذف انتخاب شده‌ها</option>
                <option value="activate">فعال کردن</option>
                <option value="deactivate">غیرفعال کردن</option>
                <option value="approve">تأیید</option>
                <option value="reject">رد کردن</option>
              </select>
              <div class="form-check">
                <input wire:model="applyToAllFiltered" type="checkbox" class="form-check-input" id="applyToAll">
                <label class="form-check-label small" for="applyToAll">
                  اعمال به همه فیلتر شده ({{ $totalFilteredCount }})
                </label>
              </div>
              <button wire:click="executeGroupAction" class="btn btn-warning btn-sm"
                @if (empty($groupAction)) disabled @endif>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2">
                  <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
                </svg>
                اجرا
              </button>
            </div>
          </div>

          <!-- Stories List -->
          @if ($readyToLoad)
            @if ($stories->count() > 0)
              <div class="table-responsive">
                <table class="table table-hover mb-0">
                  <thead class="table-light">
                    <tr>
                      <th class="border-0">
                        <div class="form-check">
                          <input wire:model.live="selectAll" type="checkbox" class="form-check-input"
                            id="selectAll">
                        </div>
                      </th>
                      <th class="border-0">رسانه</th>
                      <th class="border-0">عنوان</th>
                      <th class="border-0">مالک</th>
                      <th class="border-0">نوع</th>
                      <th class="border-0">وضعیت</th>
                      <th class="border-0">آمار</th>
                      <th class="border-0">تاریخ</th>
                      <th class="border-0">عملیات</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($stories as $story)
                      <tr>
                        <td>
                          <div class="form-check">
                            <input wire:model.live="selectedStories" type="checkbox" class="form-check-input"
                              value="{{ $story->id }}" id="story_{{ $story->id }}">
                          </div>
                        </td>
                        <td>
                          <div class="d-flex align-items-center">
                            @if ($story->media_path)
                              @if ($story->type === 'image')
                                <img src="{{ Storage::url($story->media_path) }}" class="rounded"
                                  alt="{{ $story->title }}" style="width: 50px; height: 50px; object-fit: cover;">
                              @else
                                <div class="position-relative">
                                  <video class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                    <source src="{{ Storage::url($story->media_path) }}" type="video/mp4">
                                  </video>
                                  <div class="position-absolute top-50 start-50 translate-middle">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="white"
                                      stroke="white" stroke-width="2">
                                      <polygon points="5,3 19,12 5,21" />
                                    </svg>
                                  </div>
                                </div>
                              @endif
                            @else
                              <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                style="width: 50px; height: 50px;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                  stroke="currentColor" stroke-width="2">
                                  <path
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                              </div>
                            @endif
                          </div>
                        </td>
                        <td>
                          <div>
                            <div class="fw-medium">{{ Str::limit($story->title, 40) }}</div>
                            @if ($story->description)
                              <small class="text-muted">{{ Str::limit($story->description, 60) }}</small>
                            @endif
                          </div>
                        </td>
                        <td>
                          <small class="text-muted">
                            @if ($story->user)
                              کاربر: {{ $story->user->first_name }} {{ $story->user->last_name }}
                            @elseif($story->doctor)
                              پزشک: {{ $story->doctor->first_name }} {{ $story->doctor->last_name }}
                            @elseif($story->medicalCenter)
                              مرکز: {{ $story->medicalCenter->name }}
                            @elseif($story->manager)
                              مدیر: {{ $story->manager->first_name }} {{ $story->manager->last_name }}
                            @endif
                          </small>
                        </td>
                        <td>
                          @if ($story->type === 'image')
                            <span class="badge bg-info">تصویر</span>
                          @else
                            <span class="badge bg-primary">ویدیو</span>
                          @endif
                        </td>
                        <td>
                          @if ($story->status === 'active')
                            <span class="badge bg-success">فعال</span>
                          @elseif($story->status === 'inactive')
                            <span class="badge bg-secondary">غیرفعال</span>
                          @else
                            <span class="badge bg-warning">در انتظار</span>
                          @endif
                          @if ($story->is_live)
                            <span class="badge bg-danger ms-1">لایو</span>
                          @endif
                        </td>
                        <td>
                          <div class="d-flex flex-column">
                            <small class="text-muted">بازدید: {{ number_format($story->views_count) }}</small>
                            <small class="text-muted">لایک: {{ number_format($story->likes_count) }}</small>
                          </div>
                        </td>
                        <td>
                          <small class="text-muted">{{ $story->created_at->format('Y/m/d H:i') }}</small>
                        </td>
                        <td>
                          <div class="d-flex gap-1">
                            <a href="{{ route('admin.panel.stories.edit', $story->id) }}"
                              class="btn btn-sm btn-outline-primary">
                              <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                              </svg>
                            </a>

                            @if ($story->status === 'pending')
                              <button wire:click="confirmApprove({{ $story->id }})"
                                class="btn btn-sm btn-success">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                  stroke="currentColor" stroke-width="2">
                                  <polyline points="20,6 9,17 4,12" />
                                </svg>
                              </button>
                              <button wire:click="confirmReject({{ $story->id }})" class="btn btn-sm btn-warning">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                  stroke="currentColor" stroke-width="2">
                                  <line x1="18" y1="6" x2="6" y2="18" />
                                  <line x1="6" y1="6" x2="18" y2="18" />
                                </svg>
                              </button>
                            @else
                              <button wire:click="confirmToggleStatus({{ $story->id }})"
                                class="btn btn-sm btn-outline-secondary">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                  stroke="currentColor" stroke-width="2">
                                  <path d="M9 12l2 2 4-4" />
                                  <path d="M21 12c-1 0-2-1-2-2s1-2 2-2 2 1 2 2-1 2-2 2z" />
                                  <path d="M3 12c1 0 2-1 2-2s-1-2-2-2-2 1-2 2 1 2 2 2z" />
                                </svg>
                              </button>
                            @endif

                            <button wire:click="confirmDelete({{ $story->id }})"
                              class="btn btn-sm btn-outline-danger">
                              <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <polyline points="3,6 5,6 21,6" />
                                <path
                                  d="M19,6v14a2,2 0 0,1 -2,2H7a2,2 0 0,1 -2,-2V6m3,0V4a2,2 0 0,1 2,-2h4a2,2 0 0,1 2,2v2" />
                              </svg>
                            </button>
                          </div>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>

              <!-- Pagination -->
              <div class="d-flex justify-content-center p-3">
                {{ $stories->links() }}
              </div>
            @else
              <div class="text-center py-5">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="1" class="text-muted mb-3">
                  <path
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h5 class="text-muted">هیچ استوری‌ای یافت نشد!</h5>
                <p class="text-muted">با تغییر فیلترها یا ایجاد استوری جدید شروع کنید.</p>
              </div>
            @endif
          @else
            <div class="text-center py-5">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">در حال بارگذاری...</span>
              </div>
              <p class="mt-3 text-muted">در حال بارگذاری استوری‌ها...</p>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  <!-- Confirmation Modals Script -->
  <script>
    // Delete confirmation
    window.addEventListener('confirm-delete', event => {
      if (confirm(`آیا از حذف استوری "${event.detail.name}" اطمینان دارید؟`)) {
        @this.call('deleteStory', event.detail.id);
      }
    });

    // Toggle status confirmation
    window.addEventListener('confirm-toggle-status', event => {
      if (confirm(`آیا از ${event.detail.action} استوری "${event.detail.name}" اطمینان دارید؟`)) {
        @this.call('toggleStatusConfirmed', event.detail.id);
      }
    });

    // Approve confirmation
    window.addEventListener('confirm-approve', event => {
      if (confirm(`آیا از تأیید استوری "${event.detail.name}" اطمینان دارید؟`)) {
        @this.call('approveStory', event.detail.id);
      }
    });

    // Reject confirmation
    window.addEventListener('confirm-reject', event => {
      if (confirm(`آیا از رد کردن استوری "${event.detail.name}" اطمینان دارید؟`)) {
        @this.call('rejectStory', event.detail.id);
      }
    });

    // Delete selected confirmation
    window.addEventListener('confirm-delete-selected', event => {
      const message = event.detail.allFiltered === 'allFiltered' ?
        'آیا از حذف تمام استوری‌های فیلتر شده اطمینان دارید؟' :
        'آیا از حذف استوری‌های انتخاب شده اطمینان دارید؟';

      if (confirm(message)) {
        @this.call('deleteSelected', event.detail.allFiltered);
      }
    });
  </script>
</div>

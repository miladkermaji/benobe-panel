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
              <option value="">همه موارد</option>
              <option value="active">فعال</option>
              <option value="inactive">غیرفعال</option>
              <option value="pending">در انتظار تأیید</option>
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
            <option value="">همه موارد</option>
            <option value="active">فعال</option>
            <option value="inactive">غیرفعال</option>
            <option value="pending">در انتظار تأیید</option>
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
                  <th class="text-center align-middle" style="width: 80px;">رسانه</th>
                  <th class="align-middle">عنوان</th>
                  <th class="align-middle">مالک</th>
                  <th class="align-middle">نوع</th>
                  <th class="text-center align-middle" style="width: 100px;">وضعیت</th>
                  <th class="align-middle">آمار</th>
                  <th class="align-middle">تاریخ</th>
                  <th class="text-center align-middle" style="width: 100px;">عملیات</th>
                </tr>
              </thead>
              <tbody>
                @if ($readyToLoad)
                  @forelse ($stories as $index => $story)
                    <tr>
                      <td class="text-center align-middle">
                        <input type="checkbox" wire:model.live="selectedStories" value="{{ $story->id }}"
                          class="form-check-input m-0 align-middle">
                      </td>
                      <td class="text-center align-middle">{{ $stories->firstItem() + $index }}</td>
                      <td class="text-center align-middle">
                        <div class="position-relative" style="width: 40px; height: 40px;">
                          @if ($story->media_path)
                            @if ($story->type === 'image')
                              <img src="{{ Storage::url($story->media_path) }}" class="rounded-circle"
                                style="width: 40px; height: 40px; object-fit: cover;" alt="{{ $story->title }}"
                                onerror="this.parentElement.innerHTML='<div class=\'rounded-circle bg-light d-flex align-items-center justify-content-center\' style=\'width: 40px; height: 40px; background-color: #f8f9fa;\'><svg width=\'20\' height=\'20\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'#6c757d\' stroke-width=\'2\'><path d=\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\' /></svg></div>';">
                            @else
                              <video class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;"
                                preload="none">
                                <source src="{{ Storage::url($story->media_path) }}" type="video/mp4">
                              </video>
                              <div class="position-absolute top-50 start-50 translate-middle">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="white"
                                  stroke="white" stroke-width="2">
                                  <polygon points="5,3 19,12 5,21" />
                                </svg>
                              </div>
                            @endif
                          @else
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                              style="width: 40px; height: 40px; background-color: #f8f9fa;">
                              <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="#6c757d" stroke-width="2">
                                <path
                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                              </svg>
                            </div>
                          @endif
                        </div>
                      </td>
                      <td class="align-middle">
                        <div class="fw-medium">{{ Str::limit($story->title, 40) }}</div>
                        @if ($story->description)
                          <small class="text-muted">{{ Str::limit($story->description, 60) }}</small>
                        @endif
                      </td>
                      <td class="align-middle">
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
                      <td class="align-middle">
                        @if ($story->type === 'image')
                          <span class="badge bg-info">تصویر</span>
                        @else
                          <span class="badge bg-primary">ویدیو</span>
                        @endif
                      </td>
                      <td class="text-center align-middle">
                        <label class="switch">
                          <input type="checkbox" wire:model="storyStatus.{{ $story->id }}"
                            wire:change="toggleStatus({{ $story->id }})"
                            {{ $story->status === 'active' ? 'checked' : '' }}>
                          <span class="slider round"></span>
                        </label>
                        @if ($story->is_live)
                          <span class="badge bg-danger ms-1">لایو</span>
                        @endif
                      </td>
                      <td class="align-middle">
                        <div class="d-flex flex-column">
                          <small class="text-muted">بازدید: {{ number_format($story->views_count) }}</small>
                          <small class="text-muted">لایک: {{ number_format($story->likes_count) }}</small>
                        </div>
                      </td>
                      <td class="align-middle">
                        <small
                          class="text-muted">{{ \Morilog\Jalali\Jalalian::fromCarbon($story->created_at)->format('Y/m/d H:i') }}</small>
                      </td>
                      <td class="text-center align-middle">
                        <div class="d-flex justify-content-center gap-2">
                          <a href="{{ route('admin.panel.stories.edit', $story->id) }}"
                            class="btn btn-gradient-primary rounded-pill px-3">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                              stroke="currentColor" stroke-width="2">
                              <path
                                d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                          </a>
                          <button wire:click="confirmDelete({{ $story->id }})"
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
                      <td colspan="10" class="text-center py-4">
                        <div class="d-flex justify-content-center align-items-center flex-column">
                          <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" class="text-muted mb-2">
                            <path
                              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                          </svg>
                          <p class="text-muted fw-medium">هیچ استوری‌ای یافت نشد.</p>
                        </div>
                      </td>
                    </tr>
                  @endforelse
                @else
                  <tr>
                    <td colspan="10" class="text-center py-4">
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
              @forelse ($stories as $index => $story)
                <div class="note-card mb-2" x-data="{ open: false }">
                  <div class="note-card-header d-flex justify-content-between align-items-center px-2 py-2"
                    @click="open = !open" style="cursor:pointer;">
                    <div class="d-flex align-items-center gap-2">
                      <input type="checkbox" wire:model.live="selectedStories" value="{{ $story->id }}"
                        class="form-check-input m-0" @click.stop>
                      <span class="fw-bold">
                        {{ Str::limit($story->title, 40) }}
                        <span class="text-muted">({{ $story->type === 'image' ? 'تصویر' : 'ویدیو' }})</span>
                      </span>
                    </div>
                    <svg :class="{ 'rotate-180': open }" width="20" height="20" viewBox="0 0 24 24"
                      fill="none" stroke="currentColor" stroke-width="2" style="transition: transform 0.2s;">
                      <path d="M6 9l6 6 6-6" />
                    </svg>
                  </div>
                  <div class="note-card-body px-2 py-2" x-show="open" x-transition>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">رسانه:</span>
                      <div class="note-card-value">
                        @if ($story->media_path)
                          @if ($story->type === 'image')
                            <img src="{{ Storage::url($story->media_path) }}" class="rounded"
                              style="width: 50px; height: 50px; object-fit: cover;" alt="{{ $story->title }}"
                              onerror="this.parentElement.innerHTML='<div class=\'bg-light rounded d-flex align-items-center justify-content-center\' style=\'width: 50px; height: 50px;\'><svg width=\'20\' height=\'20\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\'><path d=\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\' /></svg></div>';">
                          @else
                            <video class="rounded" style="width: 50px; height: 50px; object-fit: cover;"
                              preload="none">
                              <source src="{{ Storage::url($story->media_path) }}" type="video/mp4">
                            </video>
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
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">عنوان:</span>
                      <span class="note-card-value">{{ Str::limit($story->title, 40) }}</span>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">توضیحات:</span>
                      <span
                        class="note-card-value">{{ $story->description ? Str::limit($story->description, 60) : '---' }}</span>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">مالک:</span>
                      <span class="note-card-value">
                        @if ($story->user)
                          کاربر: {{ $story->user->first_name }} {{ $story->user->last_name }}
                        @elseif($story->doctor)
                          پزشک: {{ $story->doctor->first_name }} {{ $story->doctor->last_name }}
                        @elseif($story->medicalCenter)
                          مرکز: {{ $story->medicalCenter->name }}
                        @elseif($story->manager)
                          مدیر: {{ $story->manager->first_name }} {{ $story->manager->last_name }}
                        @endif
                      </span>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">نوع:</span>
                      <span class="note-card-value">{{ $story->type === 'image' ? 'تصویر' : 'ویدیو' }}</span>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">وضعیت:</span>
                      <label class="switch">
                        <input type="checkbox" wire:model="storyStatus.{{ $story->id }}"
                          wire:change="toggleStatus({{ $story->id }})"
                          {{ $story->status === 'active' ? 'checked' : '' }}>
                        <span class="slider round"></span>
                      </label>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">آمار:</span>
                      <div class="note-card-value">
                        بازدید: {{ number_format($story->views_count) }}<br>
                        لایک: {{ number_format($story->likes_count) }}
                      </div>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">تاریخ:</span>
                      <span
                        class="note-card-value">{{ \Morilog\Jalali\Jalalian::fromCarbon($story->created_at)->format('Y/m/d H:i') }}</span>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">عملیات:</span>
                      <div class="d-flex gap-2">
                        <a href="{{ route('admin.panel.stories.edit', $story->id) }}"
                          class="btn btn-gradient-primary btn-sm rounded-pill px-3">
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path
                              d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                          </svg>
                        </a>
                        <button wire:click="confirmDelete({{ $story->id }})"
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
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-muted fw-medium">هیچ استوری‌ای یافت نشد.</p>
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
          <div class="d-flex justify-content-between align-items-center px-4 flex-wrap gap-3">
            <div class="text-muted">نمایش
              {{ $readyToLoad && $stories instanceof \Illuminate\Pagination\LengthAwarePaginator ? $stories->firstItem() : 0 }}
              تا
              {{ $readyToLoad && $stories instanceof \Illuminate\Pagination\LengthAwarePaginator ? $stories->lastItem() : 0 }}
              از
              {{ $readyToLoad ? $stories->total() : 0 }} ردیف
            </div>
            @if ($readyToLoad && $stories && $stories->hasPages())
              <div class="pagination-container">
                {{ $stories->onEachSide(1)->links('livewire::bootstrap') }}
              </div>
            @endif
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
              title: 'حذف استوری',
              text: `آیا مطمئن هستید که می‌خواهید استوری "${event.name}" را حذف کنید؟`,
              showCancelButton: true,
              confirmButtonColor: '#ef4444',
              cancelButtonColor: '#6b7280',
              confirmButtonText: 'بله، حذف کن',
              cancelButtonText: 'خیر'
            }).then((result) => {
              if (result.isConfirmed) {
                Livewire.dispatch('deleteStoryConfirmed', {
                  id: event.id
                });
              }
            });
          });

          Livewire.on('confirm-toggle-status', (event) => {
            Swal.fire({
              title: event.action + ' استوری',
              text: `آیا مطمئن هستید که می‌خواهید "${event.name}" را ${event.action} کنید؟`,
              showCancelButton: true,
              confirmButtonColor: '#1deb3c',
              cancelButtonColor: '#6b7280',
              confirmButtonText: 'بله',
              cancelButtonText: 'خیر'
            }).then((result) => {
              if (result.isConfirmed) {
                Livewire.dispatch('toggleStatusConfirmed', {
                  id: event.id
                });
              }
            });
          });

          Livewire.on('confirm-delete-selected', function(data) {
            let text = data.allFiltered ?
              'آیا از حذف همه استوری‌های فیلترشده مطمئن هستید؟ این عملیات غیرقابل بازگشت است.' :
              'آیا از حذف استوری‌های انتخاب شده مطمئن هستید؟ این عملیات غیرقابل بازگشت است.';
            Swal.fire({
              title: 'تایید حذف گروهی',
              text: text,
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
      <style>
        .switch {
          position: relative;
          display: inline-block;
          width: 50px;
          height: 24px;
        }

        .switch input {
          opacity: 0;
          width: 0;
          height: 0;
        }

        .slider {
          position: absolute;
          cursor: pointer;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          background-color: #ccc;
          transition: .4s;
          border-radius: 24px;
        }

        .slider:before {
          position: absolute;
          content: "";
          height: 20px;
          width: 20px;
          left: 2px;
          bottom: 2px;
          background-color: white;
          transition: .4s;
          border-radius: 50%;
        }

        input:checked+.slider {
          background-color: #28a745;
        }

        input:checked+.slider:before {
          transform: translateX(26px);
        }
      </style>
    </div>
  </div>
</div>

<div class="doctor-notes-container" x-data="{ mobileSearchOpen: false }">
  <div class="container py-2 mt-3" dir="rtl" wire:init="loadFaqs">
    <!-- Header -->
    <header class="glass-header text-white p-3 rounded-3  shadow-lg">
      <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 w-100">
        <!-- Title Section -->
        <div class="d-flex align-items-center gap-2 flex-shrink-0 w-md-100 justify-content-between">
          <h2 class="mb-0 fw-bold fs-5">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              class="header-icon">
              <path d="M21.21 15.89A10 10 0 1 1 8 2.83M22 12h-4M12 2v4" />
            </svg>
            مدیریت سوالات متداول
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
                placeholder="جستجو در سوالات و پاسخ‌ها...">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" class="search-icon">
                <circle cx="11" cy="11" r="8" />
                <path d="M21 21l-4.35-4.35" />
              </svg>
            </div>
            <select class="form-select form-select-sm" wire:model.live="statusFilter">
              <option value="">همه وضعیت‌ها و دسته‌بندی‌ها</option>
              <option value="1_citizens">فقط فعال - شهروندان</option>
              <option value="1_doctors">فقط فعال - پزشکان</option>
              <option value="0_citizens">فقط غیرفعال - شهروندان</option>
              <option value="0_doctors">فقط غیرفعال - پزشکان</option>
            </select>
            <div class="d-flex align-items-center gap-2 justify-content-between">
              <a href="{{ route('admin.panel.faqs.create') }}"
                class="btn btn-success px-3 py-1 d-flex align-items-center gap-1 flex-shrink-0">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="#fff" stroke="#fff" stroke-width="2">
                  <path d="M12 5v14M5 12h14" />
                </svg>
                <span class="text-white">افزودن سوال</span>
              </a>
              <span class="badge bg-white text-primary px-2 py-1 fw-medium flex-shrink-0">
                {{ $readyToLoad ? $faqs->total() : 0 }}
              </span>
            </div>
          </div>
        </div>
        <!-- Desktop Search and Actions -->
        <div class="d-none d-md-flex align-items-center gap-3 ms-auto">
          <div class="search-box position-relative">
            <input type="text" wire:model.live="search" class="form-control ps-5"
              placeholder="جستجو در سوالات و پاسخ‌ها...">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              class="search-icon">
              <circle cx="11" cy="11" r="8" />
              <path d="M21 21l-4.35-4.35" />
            </svg>
          </div>
          <select class="form-select form-select-sm" style="min-width: 200px;" wire:model.live="statusFilter">
            <option value="">همه وضعیت‌ها و دسته‌بندی‌ها</option>
            <option value="1_citizens">فقط فعال - شهروندان</option>
            <option value="1_doctors">فقط فعال - پزشکان</option>
            <option value="0_citizens">فقط غیرفعال - شهروندان</option>
            <option value="0_doctors">فقط غیرفعال - پزشکان</option>
          </select>
          <a href="{{ route('admin.panel.faqs.create') }}"
            class="btn btn-success px-3 py-1 d-flex align-items-center gap-1 flex-shrink-0">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="#fff" stroke="#fff" stroke-width="2">
              <path d="M12 5v14M5 12h14" />
            </svg>
            <span class="text-white">افزودن سوال</span>
          </a>
          <span class="badge bg-white text-primary px-2 py-1 fw-medium flex-shrink-0">
            {{ $readyToLoad ? $faqs->total() : 0 }}
          </span>
        </div>
      </div>
    </header>
    <div class="container-fluid px-0">
      <div class="card shadow-sm rounded-2">
        <div class="card-body p-0">
          <!-- Group Actions -->
          <div class="group-actions p-2 border-bottom" x-data="{ show: false }"
            x-show="$wire.selectedFaqs.length > 0 || $wire.applyToAllFiltered">
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
                  <th class="align-middle">سوال</th>
                  <th class="align-middle">دسته‌بندی</th>
                  <th class="text-center align-middle" style="width: 80px;">ترتیب</th>
                  <th class="text-center align-middle" style="width: 100px;">وضعیت</th>
                  <th class="text-center align-middle" style="width: 120px;">تاریخ ایجاد</th>
                  <th class="text-center align-middle" style="width: 150px;">عملیات</th>
                </tr>
              </thead>
              <tbody>
                @if ($readyToLoad)
                  @forelse ($faqs as $index => $item)
                    <tr>
                      <td class="text-center align-middle">
                        <input type="checkbox" wire:model.live="selectedFaqs" value="{{ $item->id }}"
                          class="form-check-input m-0 align-middle">
                      </td>
                      <td class="text-center align-middle">{{ $faqs->firstItem() + $index }}</td>
                      <td class="align-middle">
                        <div class="d-flex flex-column">
                          <span class="fw-medium text-dark">{{ Str::limit($item->question, 60) }}</span>
                          <small class="text-muted">{{ Str::limit($item->answer, 80) }}</small>
                        </div>
                      </td>
                      <td class="align-middle">
                        <span class="badge bg-{{ $item->category === 'citizens' ? 'info' : 'warning' }}">
                          {{ $item->category_display_name }}
                        </span>
                      </td>
                      <td class="text-center align-middle">
                        <span class="badge bg-secondary">{{ $item->order }}</span>
                      </td>
                      <td class="text-center align-middle">
                        <button wire:click="confirmToggleStatus({{ $item->id }})"
                          class="badge {{ $item->is_active ? 'bg-success' : 'bg-danger' }} border-0 cursor-pointer">
                          {{ $item->is_active ? 'فعال' : 'غیرفعال' }}
                        </button>
                      </td>
                      <td class="text-center align-middle">
                        <small class="text-muted">{{ $item->created_at->format('Y/m/d H:i') }}</small>
                      </td>
                      <td class="text-center align-middle">
                        <div class="d-flex justify-content-center gap-2">
                          <a href="{{ route('admin.panel.faqs.edit', $item->id) }}"
                            class="btn btn-gradient-primary rounded-pill px-3">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                              stroke="currentColor" stroke-width="2">
                              <path
                                d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                          </a>
                          <button wire:click="confirmDelete({{ $item->id }})"
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
                            <path d="M21.21 15.89A10 10 0 1 1 8 2.83M22 12h-4M12 2v4" />
                          </svg>
                          <p class="text-muted fw-medium">هیچ سوال متداولی یافت نشد.</p>
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
          <!-- Mobile Card View -->
          <div class="notes-cards d-md-none">
            @if ($readyToLoad)
              @forelse ($faqs as $index => $item)
                <div class="note-card mb-2" x-data="{ open: false }">
                  <div class="note-card-header d-flex justify-content-between align-items-center px-2 py-2"
                    @click="open = !open" style="cursor:pointer;">
                    <div class="d-flex align-items-center gap-2">
                      <input type="checkbox" wire:model.live="selectedFaqs" value="{{ $item->id }}"
                        class="form-check-input m-0" @click.stop>
                      <span class="fw-bold">
                        {{ Str::limit($item->question, 40) }}
                      </span>
                    </div>
                    <svg :class="{ 'rotate-180': open }" width="20" height="20" viewBox="0 0 24 24"
                      fill="none" stroke="currentColor" stroke-width="2" style="transition: transform 0.2s;">
                      <path d="M6 9l6 6 6-6" />
                    </svg>
                  </div>
                  <div class="note-card-body px-2 py-2" x-show="open" x-transition>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">سوال:</span>
                      <span class="note-card-value">{{ $item->question }}</span>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">پاسخ:</span>
                      <span class="note-card-value">{{ Str::limit($item->answer, 100) }}</span>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">دسته‌بندی:</span>
                      <span class="note-card-value">
                        <span class="badge bg-{{ $item->category === 'citizens' ? 'info' : 'warning' }}">
                          {{ $item->category_display_name }}
                        </span>
                      </span>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">ترتیب:</span>
                      <span class="note-card-value">{{ $item->order }}</span>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">تاریخ ایجاد:</span>
                      <span class="note-card-value">{{ $item->created_at->format('Y/m/d H:i') }}</span>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">وضعیت:</span>
                      <button wire:click="confirmToggleStatus({{ $item->id }})"
                        class="badge {{ $item->is_active ? 'bg-success' : 'bg-danger' }} border-0 cursor-pointer">
                        {{ $item->is_active ? 'فعال' : 'غیرفعال' }}
                      </button>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">عملیات:</span>
                      <div class="d-flex gap-2">
                        <a href="{{ route('admin.panel.faqs.edit', $item->id) }}"
                          class="btn btn-gradient-primary btn-sm rounded-pill px-3">
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path
                              d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
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
                      <path d="M21.21 15.89A10 10 0 1 1 8 2.83M22 12h-4M12 2v4" />
                    </svg>
                    <p class="text-muted fw-medium">هیچ سوال متداولی یافت نشد.</p>
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
          @if ($readyToLoad && $faqs->hasPages())
            <div class="d-flex justify-content-between align-items-center p-3 border-top">
              <div class="text-muted small">
                نمایش {{ $faqs->firstItem() ?? 0 }} تا {{ $faqs->lastItem() ?? 0 }} از {{ $faqs->total() }} مورد
              </div>
              <div>
                {{ $faqs->links() }}
              </div>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener('livewire:init', () => {
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });
    });
  </script>
</div>

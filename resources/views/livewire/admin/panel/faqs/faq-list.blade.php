<div class="faq-container" x-data="{ mobileSearchOpen: false }">
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
              <option value="">همه وضعیت‌ها</option>
              <option value="1">فقط فعال</option>
              <option value="0">فقط غیرفعال</option>
            </select>
            <select class="form-select form-select-sm" wire:model.live="categoryFilter">
              <option value="">همه دسته‌بندی‌ها</option>
              <option value="citizens">شهروندان</option>
              <option value="doctors">پزشکان</option>
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
          <select class="form-select form-select-sm" style="min-width: 150px;" wire:model.live="statusFilter">
            <option value="">همه وضعیت‌ها</option>
            <option value="1">فقط فعال</option>
            <option value="0">فقط غیرفعال</option>
          </select>
          <select class="form-select form-select-sm" style="min-width: 150px;" wire:model.live="categoryFilter">
            <option value="">همه دسته‌بندی‌ها</option>
            <option value="citizens">شهروندان</option>
            <option value="doctors">پزشکان</option>
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
              <button class="btn btn-primary btn-sm" wire:click="executeGroupAction" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="executeGroupAction">اجرا</span>
                <span wire:loading wire:target="executeGroupAction">
                  <svg class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></svg>
                </span>
              </button>
            </div>
          </div>

          <!-- Table -->
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th class="border-0">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" wire:model="selectAll" id="selectAll">
                    </div>
                  </th>
                  <th class="border-0">سوال</th>
                  <th class="border-0">دسته‌بندی</th>
                  <th class="border-0">ترتیب</th>
                  <th class="border-0">وضعیت</th>
                  <th class="border-0">تاریخ ایجاد</th>
                  <th class="border-0 text-center">عملیات</th>
                </tr>
              </thead>
              <tbody>
                @forelse($faqs as $faq)
                  <tr>
                    <td>
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" wire:model="selectedFaqs"
                          value="{{ $faq->id }}">
                      </div>
                    </td>
                    <td>
                      <div class="d-flex flex-column">
                        <span class="fw-medium text-dark">{{ Str::limit($faq->question, 60) }}</span>
                        <small class="text-muted">{{ Str::limit($faq->answer, 80) }}</small>
                      </div>
                    </td>
                    <td>
                      <span class="badge bg-{{ $faq->category === 'citizens' ? 'info' : 'warning' }}">
                        {{ $faq->category_display_name }}
                      </span>
                    </td>
                    <td>
                      <span class="badge bg-secondary">{{ $faq->order }}</span>
                    </td>
                    <td>
                      <span class="badge bg-{{ $faq->is_active ? 'success' : 'danger' }}">
                        {{ $faq->is_active ? 'فعال' : 'غیرفعال' }}
                      </span>
                    </td>
                    <td>
                      <small class="text-muted">{{ $faq->created_at->format('Y/m/d H:i') }}</small>
                    </td>
                    <td>
                      <div class="d-flex align-items-center justify-content-center gap-1">
                        <a href="{{ route('admin.panel.faqs.edit', $faq->id) }}"
                          class="btn btn-sm btn-outline-primary" title="ویرایش">
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                            <path d="m18.5 2.5 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                          </svg>
                        </a>
                        <button class="btn btn-sm btn-outline-{{ $faq->is_active ? 'warning' : 'success' }}"
                          wire:click="confirmToggleStatus({{ $faq->id }})"
                          title="{{ $faq->is_active ? 'غیرفعال کردن' : 'فعال کردن' }}">
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            @if ($faq->is_active)
                              <path
                                d="M18.364 18.364A9 9 0 1 1 5.636 5.636a9 9 0 0 1 12.728 12.728zM12 8v4M12 16h.01" />
                            @else
                              <path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 15a5 5 0 1 1 5-5 5 5 0 0 1-5 5z" />
                            @endif
                          </svg>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" wire:click="confirmDelete({{ $faq->id }})"
                          title="حذف">
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path
                              d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="text-center py-4">
                      <div class="text-muted">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="1" class="mb-3">
                          <path d="M21.21 15.89A10 10 0 1 1 8 2.83M22 12h-4M12 2v4" />
                        </svg>
                        <p class="mb-0">هیچ سوال متداولی یافت نشد.</p>
                      </div>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
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
</div>



<div class="specialty-container" x-data="{ mobileSearchOpen: false }">
  <div class="container py-2 mt-3" dir="rtl" wire:init="loadSpecialties">
    <div class="glass-header text-white p-2 shadow-lg">
      <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3 w-100">
        <div class="d-flex flex-column flex-md-row gap-2 w-100 align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-3 mb-2">
            <h1 class="m-0 h4 font-thin text-nowrap mb-md-0">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" class="me-2">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              مدیریت تخصص
            </h1>
            <!-- Mobile Toggle Button -->
            <button class="btn btn-link text-white p-0 d-md-none mobile-toggle-btn" type="button"
              @click="mobileSearchOpen = !mobileSearchOpen" :aria-expanded="mobileSearchOpen">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" class="toggle-icon" :class="{ 'rotate-180': mobileSearchOpen }">
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
            x-transition:leave-end="opacity-0 transform -translate-y-2" class="d-md-block" id="mobileSearchSection">
            <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2">
              <div class="d-flex gap-2 flex-shrink-0 justify-content-center">
                <div class="search-container position-relative" style="max-width: 100%;">
                  <input type="text" wire:model.live="search"
                    class="form-control search-input border-0 shadow-none bg-white text-dark ps-4 rounded-2 text-start h-50"
                    placeholder="جستجو بر اساس نام یا توضیحات..."
                    style="padding-right: 20px; text-align: right; direction: rtl;">
                  <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-2"
                    style="z-index: 5; top: 50%; right: 8px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                      stroke-width="2">
                      <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
                    </svg>
                  </span>
                </div>
                <a href="{{ route('mc.panel.specialties.create') }}"
                  class="btn btn-gradient-success btn-gradient-success-576 rounded-1 px-3 py-1 d-flex align-items-center gap-1 h-50">
                  <svg style="transform: rotate(180deg)" width="14" height="14" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14M5 12h14" />
                  </svg>
                  <span>افزودن تخصص</span>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="container-fluid px-0">
      <div class="card shadow-sm rounded-2">
        <div class="card-body p-0">
          <!-- Group Actions -->
          <div class="group-actions p-2 border-bottom" x-data="{ show: false }"
            x-show="$wire.selectedSpecialties.length > 0 || $wire.applyToAllFiltered">
            <div class="d-flex align-items-center gap-2 justify-content-end">
              <select class="form-select form-select-sm" style="max-width: 200px;" wire:model="groupAction">
                <option value="">عملیات گروهی</option>
                <option value="delete">حذف انتخاب شده‌ها</option>
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
          <!-- Desktop Table View -->
          <div class="table-responsive text-nowrap d-none d-md-block">
            <table class="table table-hover w-100 m-0">
              <thead>
                <tr>
                  <th class="text-center align-middle" style="width: 40px;">
                    <div class="d-flex justify-content-center align-items-center">
                      <input type="checkbox" wire:model.live="selectAll" class="form-check-input m-0 align-middle">
                    </div>
                  </th>
                  <th class="text-center align-middle" style="width: 60px;">ردیف</th>
                  <th class="align-middle">نام تخصص</th>
                  <th class="align-middle">توضیحات</th>
                  <th class="text-center align-middle" style="width: 100px;">وضعیت</th>
                  <th class="text-center align-middle" style="width: 120px;">عملیات</th>
                </tr>
              </thead>
              <tbody>
                @forelse($specialties as $index => $specialty)
                  <tr>
                    <td class="text-center align-middle">
                      <div class="d-flex justify-content-center align-items-center">
                        <input type="checkbox" wire:model.live="selectedSpecialties" value="{{ $specialty->id }}"
                          class="form-check-input m-0 align-middle">
                      </div>
                    </td>
                    <td class="text-center align-middle">{{ $specialties->firstItem() + $index }}</td>
                    <td class="align-middle">
                      <div class="d-flex align-items-center">
                        <span class="fw-medium">{{ $specialty->name }}</span>
                      </div>
                    </td>
                    <td class="align-middle">
                      <span class="text-muted">{{ $specialty->description ?: 'بدون توضیحات' }}</span>
                    </td>
                    <td class="text-center align-middle">
                      <span class="badge bg-{{ $specialty->status ? 'success' : 'danger' }}">
                        {{ $specialty->status ? 'فعال' : 'غیرفعال' }}
                      </span>
                    </td>
                    <td class="text-center align-middle">
                      <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary"
                          wire:click="confirmToggleStatus({{ $specialty->id }})"
                          title="{{ $specialty->status ? 'غیرفعال کردن' : 'فعال کردن' }}">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                          </svg>
                        </button>
                        <a href="{{ route('mc.panel.specialties.edit', $specialty->id) }}"
                          class="btn btn-sm btn-outline-warning" title="ویرایش">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                          </svg>
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger"
                          wire:click="confirmDelete({{ $specialty->id }})" title="حذف">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path
                              d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2M10 11v6M14 11v6" />
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="text-center py-4">
                      <div class="d-flex flex-column align-items-center">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                          stroke-width="1">
                          <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-muted mt-2 mb-0">هیچ تخصصی یافت نشد</p>
                      </div>
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
          <!-- Mobile Card View -->
          <div class="d-md-none">
            @forelse($specialties as $index => $specialty)
              <div class="card m-2 border-0 shadow-sm">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="d-flex align-items-center gap-2">
                      <input type="checkbox" wire:model.live="selectedSpecialties" value="{{ $specialty->id }}"
                        class="form-check-input">
                      <h6 class="mb-0 fw-medium">{{ $specialty->name }}</h6>
                    </div>
                    <span class="badge bg-{{ $specialty->status ? 'success' : 'danger' }}">
                      {{ $specialty->status ? 'فعال' : 'غیرفعال' }}
                    </span>
                  </div>
                  <p class="text-muted small mb-3">{{ $specialty->description ?: 'بدون توضیحات' }}</p>
                  <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-primary flex-fill"
                      wire:click="confirmToggleStatus({{ $specialty->id }})">
                      {{ $specialty->status ? 'غیرفعال' : 'فعال' }}
                    </button>
                    <a href="{{ route('mc.panel.specialties.edit', $specialty->id) }}"
                      class="btn btn-sm btn-outline-warning flex-fill">ویرایش</a>
                    <button type="button" class="btn btn-sm btn-outline-danger flex-fill"
                      wire:click="confirmDelete({{ $specialty->id }})">حذف</button>
                  </div>
                </div>
              </div>
            @empty
              <div class="text-center py-4">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                  stroke-width="1">
                  <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-muted mt-2 mb-0">هیچ تخصصی یافت نشد</p>
              </div>
            @endforelse
          </div>
          <!-- Pagination -->
          @if ($specialties->hasPages())
            <div class="d-flex justify-content-center p-3">
              {{ $specialties->links() }}
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

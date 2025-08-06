<div class="doctor-notes-container" x-data="{ mobileSearchOpen: false }">
  <div class="container py-2 mt-3" dir="rtl" wire:init="loadManagers">
    <!-- Header -->
    <header class="glass-header text-white p-3 rounded-3  shadow-lg">
      <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 w-100">
        <!-- Title Section -->
        <div class="d-flex align-items-center gap-2 flex-shrink-0 w-md-100 justify-content-between">
          <h2 class="mb-0 fw-bold fs-5">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              class="header-icon">
              <path
                d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              <path d="M20.5899 22C20.5899 18.13 16.7399 15 11.9999 15C7.25991 15 3.40991 18.13 3.40991 22"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            مدیریت مدیران
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
                placeholder="جستجو بر اساس نام، موبایل یا ایمیل...">
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
            <select class="form-select form-select-sm" wire:model.live="permissionLevelFilter">
              <option value="">همه سطوح</option>
              <option value="1">مدیر عادی</option>
              <option value="2">مدیر ارشد</option>
            </select>
            <div class="d-flex align-items-center gap-2 justify-content-between">
              <a href="{{ route('admin.panel.managers.create') }}"
                class="btn btn-success px-3 py-1 d-flex align-items-center gap-1 flex-shrink-0">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="#fff" stroke="#fff" stroke-width="2">
                  <path d="M12 5v14M5 12h14" />
                </svg>
                <span class="text-white">افزودن مدیر</span>
              </a>
              <span class="badge bg-white text-primary px-2 py-1 fw-medium flex-shrink-0">
                {{ $readyToLoad ? $managers->total() : 0 }}
              </span>
            </div>
          </div>
        </div>
        <!-- Desktop Search and Actions -->
        <div class="d-none d-md-flex align-items-center gap-3 ms-auto">
          <div class="search-box position-relative">
            <input type="text" wire:model.live="search" class="form-control ps-5"
              placeholder="جستجو بر اساس نام، موبایل یا ایمیل...">
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
          <select class="form-select form-select-sm" style="min-width: 150px;"
            wire:model.live="permissionLevelFilter">
            <option value="">همه سطوح</option>
            <option value="1">مدیر عادی</option>
            <option value="2">مدیر ارشد</option>
          </select>
          <a href="{{ route('admin.panel.managers.create') }}"
            class="btn btn-success px-3 py-1 d-flex align-items-center gap-1 flex-shrink-0">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="#fff" stroke="#fff" stroke-width="2">
              <path d="M12 5v14M5 12h14" />
            </svg>
            <span class="text-white">افزودن مدیر</span>
          </a>
          <span class="badge bg-white text-primary px-2 py-1 fw-medium flex-shrink-0">
            {{ $readyToLoad ? $managers->total() : 0 }}
          </span>
        </div>
      </div>
    </header>
    <div class="container-fluid px-0">
      <div class="card shadow-sm rounded-2">
        <div class="card-body p-0">
          <!-- Group Actions -->
          <div class="group-actions p-2 border-bottom" x-data="{ show: false }"
            x-show="$wire.selectedManagers.length > 0 || $wire.applyToAllFiltered">
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
                  <th class="text-center align-middle" style="width: 80px;">عکس</th>
                  <th class="align-middle">نام و نام خانوادگی</th>
                  <th class="align-middle">ایمیل</th>
                  <th class="align-middle">موبایل</th>
                  <th class="align-middle">سطح دسترسی</th>
                  <th class="text-center align-middle" style="width: 100px;">وضعیت</th>
                  <th class="text-center align-middle" style="width: 150px;">عملیات</th>
                </tr>
              </thead>
              <tbody>
                @if ($readyToLoad)
                  @forelse ($managers as $index => $manager)
                    <tr>
                      <td class="text-center align-middle">
                        <input type="checkbox" wire:model.live="selectedManagers" value="{{ $manager->id }}"
                          class="form-check-input m-0 align-middle">
                      </td>
                      <td class="text-center align-middle">{{ $managers->firstItem() + $index }}</td>
                      <td class="text-center align-middle">
                        <div class="position-relative" style="width: 40px; height: 40px;">
                          @if ($manager->avatar)
                            <img loading="lazy" src="{{ Storage::url($manager->avatar) }}" class="rounded-circle"
                              style="width: 40px; height: 40px; object-fit: cover;" alt="پروفایل"
                              onerror="this.src='{{ asset('admin-assets/images/default-avatar.png') }}'">
                          @else
                            <div
                              class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white"
                              style="width: 40px; height: 40px;">
                              {{ strtoupper(substr($manager->first_name, 0, 1)) }}{{ strtoupper(substr($manager->last_name, 0, 1)) }}
                            </div>
                          @endif
                        </div>
                      </td>
                      <td class="align-middle">{{ $manager->first_name . ' ' . $manager->last_name }}</td>
                      <td class="align-middle">{{ $manager->email }}</td>
                      <td class="align-middle">{{ $manager->mobile ?: '-' }}</td>
                      <td class="align-middle">
                        @if ($manager->permission_level == 1)
                          <span class="badge bg-label-primary">مدیر عادی</span>
                        @else
                          <span class="badge bg-label-warning">مدیر ارشد</span>
                        @endif
                      </td>
                      <td class="text-center align-middle">
                        <button wire:click="toggleStatus({{ $manager->id }})"
                          class="badge {{ $manager->is_active ? 'bg-success' : 'bg-danger' }} border-0 cursor-pointer">
                          {{ $manager->is_active ? 'فعال' : 'غیرفعال' }}
                        </button>
                      </td>
                      <td class="text-center align-middle">
                        <div class="d-flex justify-content-center gap-2">
                          <a href="{{ route('admin.panel.managers.edit', $manager->id) }}"
                            class="btn btn-gradient-primary rounded-pill px-3">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                              stroke="currentColor" stroke-width="2">
                              <path
                                d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                          </a>
                          <button wire:click="confirmDelete({{ $manager->id }})"
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
                      <td colspan="9" class="text-center py-4">
                        <div class="text-muted">
                          <svg width="48" height="48" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24" class="mb-3">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                            </path>
                          </svg>
                          <p>هیچ مدیری یافت نشد!</p>
                        </div>
                      </td>
                    </tr>
                  @endforelse
                @else
                  <tr>
                    <td colspan="9" class="text-center py-5">
                      <button wire:click="loadManagers" class="btn btn-primary">
                        بارگذاری لیست مدیران
                      </button>
                    </td>
                  </tr>
                @endif
              </tbody>
            </table>
          </div>

          <!-- Mobile Card View -->
          <div class="d-md-none">
            @if ($readyToLoad)
              @forelse ($managers as $index => $manager)
                <div class="card m-2 border-0 shadow-sm">
                  <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3 mb-3">
                      <div class="position-relative" style="width: 50px; height: 50px;">
                        @if ($manager->avatar)
                          <img loading="lazy" src="{{ Storage::url($manager->avatar) }}" class="rounded-circle"
                            style="width: 50px; height: 50px; object-fit: cover;" alt="پروفایل"
                            onerror="this.src='{{ asset('admin-assets/images/default-avatar.png') }}'">
                        @else
                          <div
                            class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white"
                            style="width: 50px; height: 50px;">
                            {{ strtoupper(substr($manager->first_name, 0, 1)) }}{{ strtoupper(substr($manager->last_name, 0, 1)) }}
                          </div>
                        @endif
                      </div>
                      <div class="flex-grow-1">
                        <h6 class="mb-1 fw-bold">{{ $manager->first_name . ' ' . $manager->last_name }}</h6>
                        <p class="mb-1 text-muted small">{{ $manager->email }}</p>
                        <p class="mb-0 text-muted small">{{ $manager->mobile ?: 'بدون موبایل' }}</p>
                      </div>
                      <div class="form-check">
                        <input type="checkbox" wire:model.live="selectedManagers" value="{{ $manager->id }}"
                          class="form-check-input">
                      </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                      <div class="d-flex gap-2">
                        @if ($manager->permission_level == 1)
                          <span class="badge bg-label-primary">مدیر عادی</span>
                        @else
                          <span class="badge bg-label-warning">مدیر ارشد</span>
                        @endif
                        <button wire:click="toggleStatus({{ $manager->id }})"
                          class="badge {{ $manager->is_active ? 'bg-success' : 'bg-danger' }} border-0">
                          {{ $manager->is_active ? 'فعال' : 'غیرفعال' }}
                        </button>
                      </div>
                      <div class="d-flex gap-1">
                        <a href="{{ route('admin.panel.managers.edit', $manager->id) }}"
                          class="btn btn-sm btn-outline-primary">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path
                              d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                          </svg>
                        </a>
                        <button wire:click="confirmDelete({{ $manager->id }})"
                          class="btn btn-sm btn-outline-danger">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                          </svg>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              @empty
                <div class="text-center py-5">
                  <div class="text-muted">
                    <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                      class="mb-3">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                      </path>
                    </svg>
                    <p>هیچ مدیری یافت نشد!</p>
                  </div>
                </div>
              @endforelse
            @else
              <div class="text-center py-5">
                <button wire:click="loadManagers" class="btn btn-primary">
                  بارگذاری لیست مدیران
                </button>
              </div>
            @endif
          </div>

          <!-- Pagination -->
          @if ($readyToLoad && $managers->hasPages())
            <div class="pagination-container d-flex justify-content-center mt-4">
              {{ $managers->links() }}
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <script>
    document.addEventListener('livewire:init', () => {
      Livewire.on('confirm-delete', (event) => {
        if (confirm('آیا از حذف این مدیر اطمینان دارید؟')) {
          Livewire.dispatch('deleteManagerConfirmed', {
            id: event.id
          });
        }
      });

      Livewire.on('confirm-delete-selected', (event) => {
        const message = event.allFiltered ? 'آیا از حذف تمام مدیران فیلتر شده اطمینان دارید؟' :
          'آیا از حذف مدیران انتخاب شده اطمینان دارید؟';
        if (confirm(message)) {
          Livewire.dispatch('deleteSelectedConfirmed', {
            allFiltered: event.allFiltered
          });
        }
      });
    });
  </script>
</div>

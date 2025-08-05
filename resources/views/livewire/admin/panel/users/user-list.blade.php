<div class="doctor-notes-container" x-data="{ mobileSearchOpen: false }">
  <div class="container py-2 mt-3" dir="rtl" wire:init="loadUsers">
    <!-- Header -->
    <header class="glass-header text-white p-3 rounded-3 mb-3 shadow-lg">
      <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 w-100">
        <!-- Title Section -->
        <div class="d-flex align-items-center gap-2 flex-shrink-0 w-md-100 justify-content-between">
          <h2 class="mb-0 fw-bold fs-5">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              class="header-icon">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
              <circle cx="9" cy="7" r="4" />
              <path d="m22 21-2-2" />
              <path d="M16 16h6" />
            </svg>
            مدیریت کاربران
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
                placeholder="جستجو بر اساس نام، نام خانوادگی، موبایل یا ایمیل...">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" class="search-icon">
                <circle cx="11" cy="11" r="8" />
                <path d="M21 21l-4.35-4.35" />
              </svg>
            </div>
            <select class="form-select form-select-sm" wire:model.live="statusFilter">
              <option value="">همه وضعیت‌ها</option>
              <option value="active">فقط فعال</option>
              <option value="inactive">فقط غیرفعال</option>
            </select>
            <select class="form-select form-select-sm" wire:model.live="userTypeFilter">
              <option value="all">همه نوع کاربرها</option>
              <option value="doctor">پزشک</option>
              <option value="secretary">منشی</option>
              <option value="patient">بیمار</option>
            </select>
            <div class="d-flex align-items-center gap-2 justify-content-between">
              <a href="{{ route('admin.panel.users.create') }}"
                class="btn btn-success px-3 py-1 d-flex align-items-center gap-1 flex-shrink-0">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="#fff" stroke="#fff" stroke-width="2">
                  <path d="M12 5v14M5 12h14" />
                </svg>
                <span class="text-white">افزودن</span>
              </a>
              <span class="badge bg-white text-primary px-2 py-1 fw-medium flex-shrink-0">
                {{ $readyToLoad ? $users->total() : 0 }}
              </span>
            </div>
          </div>
        </div>
        <!-- Desktop Search and Actions -->
        <div class="d-none d-md-flex align-items-center gap-3 ms-auto">
          <div class="search-box position-relative">
            <input type="text" wire:model.live="search" class="form-control ps-5"
              placeholder="جستجو بر اساس نام، نام خانوادگی، موبایل یا ایمیل...">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              class="search-icon">
              <circle cx="11" cy="11" r="8" />
              <path d="M21 21l-4.35-4.35" />
            </svg>
          </div>
          <select class="form-select form-select-sm" style="min-width: 150px;" wire:model.live="statusFilter">
            <option value="">همه وضعیت‌ها</option>
            <option value="active">فقط فعال</option>
            <option value="inactive">فقط غیرفعال</option>
          </select>
          <select class="form-select form-select-sm" style="min-width: 150px;" wire:model.live="userTypeFilter">
            <option value="all">همه نوع کاربرها</option>
            <option value="doctor">پزشک</option>
            <option value="secretary">منشی</option>
            <option value="patient">بیمار</option>
          </select>
          <a href="{{ route('admin.panel.users.create') }}"
            class="btn btn-success px-3 py-1 d-flex align-items-center gap-1 flex-shrink-0">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="#fff" stroke="#fff" stroke-width="2">
              <path d="M12 5v14M5 12h14" />
            </svg>
            <span class="text-white">افزودن</span>
          </a>
          <span class="badge bg-white text-primary px-2 py-1 fw-medium flex-shrink-0">
            {{ $readyToLoad ? $users->total() : 0 }}
          </span>
        </div>
      </div>
    </header>
    <div class="container-fluid px-0">
      <div class="card shadow-sm rounded-2">
        <div class="card-body p-0">
          <!-- Group Actions -->
          <div class="group-actions p-2 border-bottom" x-data="{ show: false }"
            x-show="$wire.selectedUsers.length > 0 || $wire.applyToAllFiltered">
            <div class="d-flex align-items-center gap-2 justify-content-end">
              <select class="form-select form-select-sm" style="max-width: 200px;" wire:model="groupAction">
                <option value="">عملیات گروهی</option>
                <option value="delete">حذف انتخاب شده‌ها</option>
                <option value="status_active">فعال کردن</option>
                <option value="status_inactive">غیرفعال کردن</option>
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
                    <div class="d-flex justify-content-center align-items-center">
                      <input type="checkbox" wire:model.live="selectAll" class="form-check-input m-0 align-middle">
                    </div>
                  </th>
                  <th class="text-center align-middle" style="width: 60px;">ردیف</th>
                  <th class="text-center align-middle" style="width: 80px;">عکس</th>
                  <th class="align-middle">نام</th>
                  <th class="align-middle">نام خانوادگی</th>
                  <th class="align-middle">ایمیل</th>
                  <th class="align-middle">موبایل</th>
                  <th class="align-middle">کد ملی</th>
                  <th class="align-middle">نوع کاربر</th>
                  <th class="text-center align-middle" style="width: 100px;">وضعیت</th>
                  <th class="text-center align-middle" style="width: 150px;">عملیات</th>
                </tr>
              </thead>
              <tbody>
                @if ($readyToLoad)
                  @forelse ($users as $index => $user)
                    <tr class="align-middle">
                      <td class="text-center">
                        <div class="d-flex justify-content-center align-items-center">
                          <input type="checkbox" wire:model.live="selectedUsers" value="{{ $user->id }}"
                            class="form-check-input m-0 align-middle">
                        </div>
                      </td>
                      <td class="text-center">{{ $users->firstItem() + $index }}</td>
                      <td class="text-center align-middle">
                        <div class="position-relative" style="width: 40px; height: 40px;">
                          <img loading="lazy"
                            src="{{ str_starts_with($user->profile_photo_url, 'http') ? $user->profile_photo_url : asset('admin-assets/images/default-avatar.png') }}"
                            class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;"
                            alt="پروفایل" onerror="this.src='{{ asset('admin-assets/images/default-avatar.png') }}'">
                        </div>
                      </td>
                      <td class="align-middle">{{ $user->first_name }}</td>
                      <td class="align-middle">{{ $user->last_name }}</td>
                      <td class="align-middle">{{ $user->email }}</td>
                      <td class="align-middle">{{ $user->mobile }}</td>
                      <td class="align-middle">{{ $user->national_code ?? '-' }}</td>
                      <td class="align-middle">
                        @if (isset($user->user_type) && $user->user_type === 'doctor')
                          پزشک
                        @elseif(isset($user->user_type) && $user->user_type === 'secretary')
                          منشی
                        @else
                          بیمار
                        @endif
                      </td>
                      <td class="text-center align-middle">
                        <button wire:click="toggleStatus({{ $user->id }})"
                          class="badge {{ $user->status ? 'bg-success' : 'bg-danger' }} border-0 cursor-pointer">
                          {{ $user->status ? 'فعال' : 'غیرفعال' }}
                        </button>
                      </td>
                      <td class="text-center align-middle">
                        <div class="d-flex justify-content-center gap-2">
                          <a href="{{ route('admin.panel.users.edit', $user->id) }}"
                            class="btn btn-gradient-primary rounded-pill px-3">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                              stroke="currentColor" stroke-width="2">
                              <path
                                d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                          </a>
                          <button wire:click="confirmDelete({{ $user->id }})"
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
                            <path d="M5 12h14M12 5l7 7-7 7" />
                          </svg>
                          <p class="text-muted fw-medium">هیچ کاربری یافت نشد.</p>
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
              @forelse ($users as $index => $user)
                <div class="note-card mb-2" x-data="{ open: false }">
                  <div class="note-card-header d-flex justify-content-between align-items-center px-2 py-2"
                    @click="open = !open" style="cursor:pointer;">
                    <span class="fw-bold">{{ $user->first_name }} {{ $user->last_name }} <span
                        class="text-muted">({{ $user->national_code ?? '-' }})</span></span>
                    <svg :class="{ 'rotate-180': open }" width="20" height="20" viewBox="0 0 24 24"
                      fill="none" stroke="currentColor" stroke-width="2" style="transition: transform 0.2s;">
                      <path d="M6 9l6 6 6-6" />
                    </svg>
                  </div>
                  <div class="note-card-body px-2 py-2" x-show="open" x-transition>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">ایمیل:</span>
                      <span class="note-card-value">{{ $user->email }}</span>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">موبایل:</span>
                      <span class="note-card-value">{{ $user->mobile }}</span>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">نوع کاربر:</span>
                      <span class="note-card-value">
                        @if (isset($user->user_type) && $user->user_type === 'doctor')
                          پزشک
                        @elseif(isset($user->user_type) && $user->user_type === 'secretary')
                          منشی
                        @else
                          بیمار
                        @endif
                      </span>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <span class="note-card-label">وضعیت:</span>
                      <button wire:click="toggleStatus({{ $user->id }})"
                        class="badge {{ $user->status ? 'bg-success' : 'bg-danger' }} border-0 cursor-pointer">
                        {{ $user->status ? 'فعال' : 'غیرفعال' }}
                      </button>
                    </div>
                    <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                      <a href="{{ route('admin.panel.users.edit', $user->id) }}"
                        class="btn btn-gradient-primary btn-sm rounded-pill px-3">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2">
                          <path
                            d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                      </a>
                      <button wire:click="confirmDelete({{ $user->id }})"
                        class="btn btn-gradient-danger btn-sm rounded-pill px-3">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2">
                          <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                        </svg>
                      </button>
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
                    <p class="text-muted fw-medium">هیچ کاربری یافت نشد.</p>
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
          <!-- Pagination (unchanged) -->
          <div class="d-flex justify-content-between align-items-center mt-4 px-4 flex-wrap gap-3">
            <div class="text-muted">نمایش {{ $users ? $users->firstItem() : 0 }} تا
              {{ $users ? $users->lastItem() : 0 }} از {{ $users ? $users->total() : 0 }} ردیف
            </div>
            @if ($users && $users->hasPages())
              <div class="pagination-container">
                {{ $users->onEachSide(1)->links('livewire::bootstrap') }}
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
              title: 'حذف کاربر',
              text: 'آیا مطمئن هستید که می‌خواهید این کاربر را حذف کنید؟',

              showCancelButton: true,
              confirmButtonColor: '#ef4444',
              cancelButtonColor: '#6b7280',
              confirmButtonText: 'بله، حذف کن',
              cancelButtonText: 'خیر'
            }).then((result) => {
              if (result.isConfirmed) {
                Livewire.dispatch('deleteUserConfirmed', {
                  id: event.id
                });
              }
            });
          });
          Livewire.on('confirm-delete-selected', function(data) {
            let text = data.allFiltered ?
              'آیا از حذف همه کاربران فیلترشده مطمئن هستید؟ این عملیات غیرقابل بازگشت است.' :
              'آیا از حذف کاربران انتخاب شده مطمئن هستید؟ این عملیات غیرقابل بازگشت است.';
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
    </div>
  </div>
</div>

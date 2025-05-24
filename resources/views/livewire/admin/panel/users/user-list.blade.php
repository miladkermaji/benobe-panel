<div class="container-fluid py-2" dir="rtl" wire:init="loadUsers">
  <!-- Header -->
  <header class="glass-header text-white p-3 rounded-3 mb-5 shadow-lg">
    <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
      <!-- Title Section -->
      <div class="d-flex align-items-center gap-2 flex-shrink-0">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="header-icon">
          <path
            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <h2 class="mb-0 fw-bold fs-5">مدیریت کاربران</h2>
      </div>
      <!-- Search and Actions -->
      <div class="d-flex flex-column flex-md-row align-items-center gap-3 w-100 w-md-auto ms-auto">
        <div class="search-box position-relative">
          <input type="text" wire:model.live="search"
            class="form-control border-0 shadow-none bg-white text-dark ps-5" placeholder="جستجو...">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2"
            class="search-icon">
            <circle cx="11" cy="11" r="8" />
            <path d="M21 21l-4.35-4.35" />
          </svg>
        </div>
        <div class="d-flex gap-3 flex-shrink-0 flex-wrap justify-content-center mt-md-2 buttons-container">
          <a href="{{ route('admin.panel.users.create') }}"
            class="btn btn-gradient-success rounded-pill px-4 d-flex align-items-center gap-2">
            <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2">
              <path d="M12 5v14M5 12h14" />
            </svg>
            <span>افزودن کاربر</span>
          </a>
          <button wire:click="deleteSelected"
            class="btn btn-gradient-danger rounded-pill px-4 d-flex align-items-center gap-2"
            @if (empty($selectedUsers)) disabled @endif>
            <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2">
              <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
            </svg>
            <span>حذف انتخاب‌شده‌ها</span>
          </button>
        </div>
      </div>
    </div>
  </header>

  <!-- Desktop View -->
  <div class="container-fluid px-0 d-none d-md-block">
    <div class="card shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive text-nowrap">
          <table class="table table-hover w-100 m-0">
            <thead>
              <tr>
                <th class="text-center align-middle" style="width: 50px;">
                  <input type="checkbox" wire:model.live="selectAll" class="form-check-input m-0">
                </th>
                <th class="text-center align-middle" style="width: 70px;">ردیف</th>
                <th class="text-center align-middle" style="width: 80px;">عکس</th>
                <th class="align-middle">نام</th>
                <th class="align-middle">نام خانوادگی</th>
                <th class="align-middle">ایمیل</th>
                <th class="align-middle">موبایل</th>
                <th class="align-middle">کد ملی</th>
                <th class="text-center align-middle" style="width: 100px;">وضعیت</th>
                <th class="text-center align-middle" style="width: 150px;">عملیات</th>
              </tr>
            </thead>
            <tbody>
              @if ($readyToLoad)
                @forelse ($users as $index => $user)
                  <tr>
                    <td class="text-center align-middle">
                      <input type="checkbox" wire:model.live="selectedUsers" value="{{ $user->id }}"
                        class="form-check-input m-0">
                    </td>
                    <td class="text-center align-middle">{{ $users->firstItem() + $index }}</td>
                    <td class="text-center align-middle">
                      <div class="position-relative" style="width: 40px; height: 40px;">
                        <img loading="lazy"
                          src="{{ str_starts_with($user->profile_photo_url, 'http') ? $user->profile_photo_url : asset('admin-assets/images/default-avatar.png') }}"
                          class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;" alt="پروفایل"
                          onerror="this.src='{{ asset('admin-assets/images/default-avatar.png') }}'">
                        <div
                          class="position-absolute top-0 start-0 w-100 h-100 rounded-circle bg-light d-none align-items-center justify-content-center"
                          style="background-color: #f8f9fa;">
                          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6c757d"
                            stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                          </svg>
                        </div>
                      </div>
                    </td>
                    <td class="align-middle">{{ $user->first_name }}</td>
                    <td class="align-middle">{{ $user->last_name }}</td>
                    <td class="align-middle">{{ $user->email }}</td>
                    <td class="align-middle">{{ $user->mobile }}</td>
                    <td class="align-middle">{{ $user->national_code ?? '-' }}</td>
                    <td class="text-center align-middle">
                      <button wire:click="toggleStatus({{ $user->id }})"
                        class="badge {{ $user->status ? 'bg-label-success' : 'bg-label-danger' }} border-0 cursor-pointer">
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
                            <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="10" class="text-center py-5">
                      <div class="d-flex justify-content-center align-items-center flex-column">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2" class="text-muted mb-3">
                          <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                        <p class="text-muted fw-medium">هیچ کاربری یافت نشد.</p>
                      </div>
                    </td>
                  </tr>
                @endforelse
              @else
                <tr>
                  <td colspan="10" class="text-center py-5">در حال بارگذاری کاربران...</td>
                </tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Mobile/Tablet View -->
  <div class="container-fluid px-0 d-md-none">
    @if ($readyToLoad)
      @forelse ($users as $index => $user)
        <div class="card shadow-sm mb-3">
          <div class="card-body">
            <div class="d-flex align-items-center gap-3 mb-3">
              <input type="checkbox" wire:model.live="selectedUsers" value="{{ $user->id }}"
                class="form-check-input m-0">
              <div class="position-relative" style="width: 50px; height: 50px;">
                <img loading="lazy"
                  src="{{ str_starts_with($user->profile_photo_url, 'http') ? $user->profile_photo_url : asset('admin-assets/images/default-avatar.png') }}"
                  class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover;" alt="پروفایل"
                  onerror="this.src='{{ asset('admin-assets/images/default-avatar.png') }}'">
                <div
                  class="position-absolute top-0 start-0 w-100 h-100 rounded-circle bg-light d-none align-items-center justify-content-center"
                  style="background-color: #f8f9fa;">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6c757d"
                    stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                  </svg>
                </div>
              </div>
              <div>
                <h6 class="mb-1">{{ $user->first_name }} {{ $user->last_name }}</h6>
                <small class="text-muted">{{ $user->email }}</small>
              </div>
            </div>
            <div class="row g-3">
              <div class="col-6">
                <small class="text-muted d-block">موبایل</small>
                <span>{{ $user->mobile }}</span>
              </div>
              <div class="col-6">
                <small class="text-muted d-block">کد ملی</small>
                <span>{{ $user->national_code ?? '-' }}</span>
              </div>
              <div class="col-6">
                <small class="text-muted d-block">وضعیت</small>
                <button wire:click="toggleStatus({{ $user->id }})"
                  class="badge {{ $user->status ? 'bg-label-success' : 'bg-label-danger' }} border-0 cursor-pointer">
                  {{ $user->status ? 'فعال' : 'غیرفعال' }}
                </button>
              </div>
              <div class="col-6">
                <small class="text-muted d-block">عملیات</small>
                <div class="d-flex gap-2">
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
          </div>
        </div>
      @empty
        <div class="text-center py-5">
          <div class="d-flex justify-content-center align-items-center flex-column">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2" class="text-muted mb-3">
              <path d="M5 12h14M12 5l7 7-7 7" />
            </svg>
            <p class="text-muted fw-medium">هیچ کاربری یافت نشد.</p>
          </div>
        </div>
      @endforelse
    @else
      <div class="text-center py-5">در حال بارگذاری کاربران...</div>
    @endif
  </div>

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
  <script>
    document.addEventListener('livewire:init', function() {
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });

      Livewire.on('confirm-delete', (event) => {
        Swal.fire({
          title: 'حذف کاربر',
          text: 'آیا مطمئن هستید که می‌خواهید این کاربر را حذف کنید؟',
          icon: 'warning',
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
    });

    document.addEventListener('DOMContentLoaded', function() {
      // Handle lazy loaded images
      const lazyImages = document.querySelectorAll('img[loading="lazy"]');

      const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const img = entry.target;

            // Add loaded class when image is actually loaded
            if (img.complete) {
              img.classList.add('loaded');
            } else {
              img.addEventListener('load', function() {
                img.classList.add('loaded');
              });
            }

            observer.unobserve(img);
          }
        });
      });

      lazyImages.forEach(img => {
        imageObserver.observe(img);
      });
    });
  </script>
</div>

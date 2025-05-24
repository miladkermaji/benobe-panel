<div class="container-fluid py-2" dir="rtl" wire:init="loadUsers">
  <div
    class="glass-header text-white p-3 rounded-3 mb-5 shadow-lg d-flex justify-content-between align-items-center flex-wrap gap-3">
    <h1 class="m-0 h3 font-thin flex-grow-1" style="min-width: 200px;">مدیریت کاربران</h1>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
      <div class="d-flex align-items-center gap-2">
        <div class="input-group">
          <span class="input-group-text">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2">
              <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </span>
          <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="جستجو...">
        </div>
      </div>

      <div class="d-flex gap-2 flex-shrink-0 flex-wrap justify-content-center mt-md-2 buttons-container">
        <a href="{{ route('admin.panel.users.create') }}"
          class="btn btn-gradient-primary rounded-pill px-4 d-flex align-items-center gap-2">
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
                      <img src="{{ $user->profile_photo_url }}" class="rounded-circle"
                        style="width: 40px; height: 40px; object-fit: cover;" alt="پروفایل">
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
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path
                              d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                          </svg>
                        </a>
                        <button wire:click="confirmDelete({{ $user->id }})"
                          class="btn btn-gradient-danger rounded-pill px-3">
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
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
              <img src="{{ $user->profile_photo_url }}" class="rounded-circle"
                style="width: 50px; height: 50px; object-fit: cover;" alt="پروفایل">
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
      {{ $users->links('livewire::bootstrap') }}
    @endif
  </div>

  <style>
    :root {
      --primary: #2E86C1;
      --primary-light: #84CAF9;
      --secondary: #1DEB3C;
      --secondary-hover: #15802A;
      --background-light: #F0F8FF;
      --background-footer: #D4ECFD;
      --background-card: #FFFFFF;
      --text-primary: #000000;
      --text-secondary: #707070;
      --text-discount: #008000;
      --text-original: #FF0000;
      --border-neutral: #E5E7EB;
      --shadow: rgba(0, 0, 0, 0.35);
      --gradient-instagram-from: #F92CA7;
      --gradient-instagram-to: #6B1A93;
      --button-mobile: #4F9ACD;
      --button-mobile-light: #A2CDEB;
      --support-section: #2E86C1;
      --support-text: #084D7C;
    }

    .glass-header {
      background: var(--gradient-primary);
    }

    .btn-gradient-primary {
      background: var(--gradient-primary);
      border: none;
      color: white;
    }

    .btn-gradient-primary:hover {
      background: linear-gradient(90deg, var(--primary) 0%, var(--primary-light) 100%);
      color: white;
    }

    .btn-gradient-danger {
      background: linear-gradient(90deg, #f44336, #e53935);
      border: none;
      color: white;
    }

    .btn-gradient-danger:hover {
      background: linear-gradient(90deg, #e53935, #d32f2f);
      color: white;
    }

    .bg-label-success {
      background-color: #e8f5e9;
      color: var(--text-discount);
    }

    .bg-label-danger {
      background-color: #ffebee;
      color: var(--text-original);
    }

    .card {
      border-radius: var(--radius-card);
      border: 1px solid var(--border-neutral);
      background: var(--background-card);
    }

    .btn {
      border-radius: var(--radius-button);
    }

    .btn-sm {
      border-radius: var(--radius-button);
    }

    .rounded-pill {
      border-radius: var(--radius-circle);
    }

    .form-control {
      border: 1px solid var(--border-neutral);
      border-radius: var(--radius-button);
    }

    .form-control:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(46, 134, 193, 0.2);
    }

    .input-group-text {
      background: var(--background-light);
      border: 1px solid var(--border-neutral);
      border-radius: var(--radius-button);
    }

    @media (max-width: 767px) {
      .glass-header {
        flex-direction: column;
        gap: 1rem;
      }

      .buttons-container {
        width: 100%;
      }

      .btn {
        width: 100%;
        justify-content: center;
      }
    }

    /* Table Styles */
    .table-container {
      background: var(--background-card);
      border-radius: 4px;
      box-shadow: 0 1px 4px var(--shadow);
      overflow: hidden;
      margin-bottom: 1rem;
      border: 1px solid var(--border-neutral);
    }

    .table {
      margin-bottom: 0;
    }

    .table thead th {
      background: var(--background-light);
      color: var(--text-primary);
      font-weight: 600;
      border: none;
      padding: 0.75rem;
      text-align: right;
      font-size: 0.85rem;
      border-bottom: 1px solid var(--border-neutral);
    }

    .table tbody td {
      padding: 0.75rem;
      vertical-align: middle;
      border-bottom: 1px solid var(--border-neutral);
      color: var(--text-primary);
      font-size: 0.85rem;
      text-align: right;
    }

    .table tbody tr:last-child td {
      border-bottom: none;
    }

    .table tbody tr:hover {
      background-color: var(--background-light);
    }

    .form-check-input {
      width: 1.125rem;
      height: 1.125rem;
      margin-top: 0;
      border: 1px solid #dee2e6;
    }

    .form-check-input:checked {
      background-color: var(--primary);
      border-color: var(--primary);
    }

    .badge {
      padding: 0.5rem 0.75rem;
      font-weight: 500;
      font-size: 0.75rem;
    }

    .bg-label-success {
      background-color: rgba(29, 235, 60, 0.1);
      color: var(--text-discount);
    }

    .bg-label-danger {
      background-color: rgba(255, 0, 0, 0.1);
      color: var(--text-original);
    }

    .btn {
      padding: 0.5rem 1rem;
      font-size: 0.875rem;
    }

    .btn svg {
      width: 1rem;
      height: 1rem;
    }

    @media (max-width: 768px) {
      .table thead {
        display: none;
      }

      .table tbody td {
        padding: 0.75rem;
      }

      .btn-custom,
      .btn-danger {
        padding: 0.4rem;
      }

      .btn-custom svg,
      .btn-danger svg {
        width: 14px;
        height: 14px;
      }
    }
  </style>

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
  </script>
</div>

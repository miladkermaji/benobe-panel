<div class="container-fluid py-2" dir="rtl" wire:init="loadDoctorComments">
  <div
    class="glass-header text-white p-3 rounded-3 mb-5 shadow-lg d-flex justify-content-between align-items-center flex-wrap gap-3">
    <h1 class="m-0 h3 font-thin flex-grow-1" style="min-width: 200px;">مدیریت نظرات پزشکان</h1>
    <div class="input-group flex-grow-1 position-relative" style="max-width: 400px;">
      <input type="text" class="form-control border-0 shadow-none bg-white text-dark ps-5 rounded-3"
        wire:model.live="search" placeholder="جستجو در نام، نظر یا پزشک..." style="padding-right: 23px">
      <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-3"
        style="z-index: 5; top: 11px; right: 5px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
          <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
        </svg>
      </span>
    </div>
    <div class="d-flex gap-2 flex-shrink-0 flex-wrap justify-content-center mt-md-2 buttons-container">
      <a href="{{ route('admin.panel.doctor-comments.create') }}"
        class="btn btn-gradient-success rounded-pill px-4 d-flex align-items-center gap-2">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 5v14M5 12h14" />
        </svg>
        <span>افزودن نظر</span>
      </a>
      <button wire:click="toggleSelectedStatus"
        class="btn btn-gradient-warning rounded-pill px-4 d-flex align-items-center gap-2"
        @if (empty($selectedDoctorComments)) disabled @endif>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M10 12h4" />
          <path d="M12 10v4" />
        </svg>
        <span>تغییر وضعیت انتخاب‌شده‌ها</span>
      </button>
      <button wire:click="deleteSelected"
        class="btn btn-gradient-danger rounded-pill px-4 d-flex align-items-center gap-2"
        @if (empty($selectedDoctorComments)) disabled @endif>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
        </svg>
        <span>حذف انتخاب‌شده‌ها</span>
      </button>
    </div>
  </div>

  <div class="container-fluid px-0">
    <div class="card shadow-sm">
      <div class="card-body p-0">
        @if ($readyToLoad)
          @forelse ($doctors as $doctor)
            <div class="doctor-toggle border-bottom">
              <div class="d-flex justify-content-between align-items-center p-3 cursor-pointer"
                wire:click="toggleDoctor({{ $doctor->id }})">
                <div class="d-flex align-items-center gap-3">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                    stroke-width="2">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                  </svg>
                  <span class="fw-bold">{{ $doctor->first_name . ' ' . $doctor->last_name }}</span>
                  <span class="badge bg-label-primary">{{ $doctor->comments->total() }} نظر</span>
                </div>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2"
                  class="transition-transform {{ in_array($doctor->id, $expandedDoctors) ? 'rotate-180' : '' }}">
                  <path d="M6 9l6 6 6-6" />
                </svg>
              </div>

              @if (in_array($doctor->id, $expandedDoctors))
                <div class="table-responsive text-nowrap p-3 bg-light">
                  <table class="table table-bordered table-hover w-100 m-0">
                    <thead class="glass-header text-white">
                      <tr>
                        <th class="text-center align-middle" style="width: 50px;">
                          <input type="checkbox" wire:model.live="selectAll.{{ $doctor->id }}"
                            class="form-check-input m-0">
                        </th>
                        <th class="text-center align-middle" style="width: 70px;">#</th>
                        <th class="align-middle">نام کاربر</th>
                        <th class="align-middle">شماره تماس</th>
                        <th class="align-middle">نظر</th>
                        <th class="text-center align-middle" style="width: 100px;">وضعیت</th>
                        <th class="text-center align-middle" style="width: 150px;">عملیات</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse ($doctor->comments as $index => $comment)
                        <tr>
                          <td class="text-center align-middle">
                            <input type="checkbox" wire:model.live="selectedDoctorComments" value="{{ $comment->id }}"
                              class="form-check-input m-0">
                          </td>
                          <td class="text-center align-middle">{{ $doctor->comments->firstItem() + $index }}</td>
                          <td class="align-middle">{{ $comment->user_name }}</td>
                          <td class="align-middle">{{ $comment->user_phone ?? 'ثبت نشده' }}</td>
                          <td class="align-middle">{{ Str::limit($comment->comment, 50) }}</td>
                          <td class="text-center align-middle">
                            <button wire:click="toggleStatus({{ $comment->id }})"
                              class="badge {{ $comment->status ? 'bg-label-success' : 'bg-label-danger' }} border-0 cursor-pointer px-3 py-1">
                              {{ $comment->status ? 'فعال' : 'غیرفعال' }}
                            </button>
                          </td>
                          <td class="text-center align-middle">
                            <div class="d-flex justify-content-center gap-2">
                              <a href="{{ route('admin.panel.doctor-comments.edit', $comment->id) }}"
                                class="btn btn-gradient-success rounded-pill px-3">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                  stroke="currentColor" stroke-width="2">
                                  <path
                                    d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                </svg>
                              </a>
                              <button wire:click="confirmDelete({{ $comment->id }})"
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
                          <td colspan="7" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                              <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" class="text-muted mb-3">
                                <path d="M5 12h14M12 5l7 7-7 7" />
                              </svg>
                              <p class="text-muted fw-medium m-0">هیچ نظری یافت نشد.</p>
                            </div>
                          </td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                  @if ($doctor->comments->hasPages())
                    <div class="p-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                      <span class="text-muted">نمایش {{ $doctor->comments->firstItem() }} تا
                        {{ $doctor->comments->lastItem() }} از {{ $doctor->comments->total() }} نظر</span>
                      {{ $doctor->comments->links('livewire::bootstrap') }}
                    </div>
                  @endif
                </div>
              @endif
            </div>
          @empty
            <div class="text-center py-5">
              <div class="d-flex flex-column align-items-center justify-content-center">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2" class="text-muted mb-3">
                  <path d="M5 12h14M12 5l7 7-7 7" />
                </svg>
                <p class="text-muted fw-medium m-0">هیچ پزشک یا نظری یافت نشد.</p>
              </div>
            </div>
          @endforelse
        @else
          <div class="text-center py-5">در حال بارگذاری نظرات...</div>
        @endif
      </div>
    </div>
  </div>

  <style>
    .glass-header {
      background: linear-gradient(90deg, rgba(107, 114, 128, 0.9), rgba(55, 65, 81, 0.9));
      backdrop-filter: blur(10px);
    }

    .btn-gradient-success {
      background: linear-gradient(90deg, #10b981, #059669);
      color: white;
    }

    .btn-gradient-warning {
      background: linear-gradient(90deg, #f59e0b, #d97706);
      color: white;
    }

    .btn-gradient-danger {
      background: linear-gradient(90deg, #ef4444, #dc2626);
      color: white;
    }

    .doctor-toggle {
      transition: all 0.3s ease;
    }

    .doctor-toggle:hover {
      background: #f9fafb;
    }

    .cursor-pointer {
      cursor: pointer;
    }

    .transition-transform {
      transition: transform 0.3s ease;
    }

    .rotate-180 {
      transform: rotate(180deg);
    }

    .bg-label-primary {
      background: #e5e7eb;
      color: #374151;
    }

    .bg-label-success {
      background: #d1fae5;
      color: #059669;
    }

    .bg-label-danger {
      background: #fee2e2;
      color: #dc2626;
    }

    .card {
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .table-bordered {
      border: 1px solid #e5e7eb;
    }

    .table-bordered th,
    .table-bordered td {
      border: 1px solid #e5e7eb;
    }
  </style>

  <script>
    document.addEventListener('livewire:init', function() {
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });

      Livewire.on('confirm-delete', (event) => {
        Swal.fire({
          title: 'حذف نظر',
          text: 'آیا مطمئن هستید که می‌خواهید این نظر را حذف کنید؟',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#ef4444',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'بله، حذف کن',
          cancelButtonText: 'خیر'
        }).then((result) => {
          if (result.isConfirmed) {
            Livewire.dispatch('deleteDoctorCommentConfirmed', {
              id: event.id
            });
          }
        });
      });
    });
  </script>
</div>

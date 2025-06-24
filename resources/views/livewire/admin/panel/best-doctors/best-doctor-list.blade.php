<div class="container-fluid py-2" dir="rtl" wire:init="loadBestDoctors">
  <div
    class="glass-header text-white p-3 rounded-3 mb-5 shadow-lg d-flex justify-content-between align-items-center flex-wrap gap-3">
    <h1 class="m-0 h3 font-thin flex-grow-1" style="min-width: 200px;">مدیریت بهترین پزشکان</h1>
    <div class="input-group flex-grow-1 position-relative" style="max-width: 400px;">
      <input type="text" class="form-control border-0 shadow-none bg-white text-dark ps-5 rounded-3"
        wire:model.live="search" placeholder="جستجو در نام نمایشی پزشکان..." style="padding-right: 23px">
      <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-3" style="z-index: 5;right: 5px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
          <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
        </svg>
      </span>
    </div>
    <div class="d-flex gap-2 flex-shrink-0 flex-wrap justify-content-center mt-md-2 buttons-container">
      <a href="{{ route('admin.panel.best-doctors.create') }}"
        class="btn btn-gradient-success rounded-pill px-4 d-flex align-items-center gap-2">
        <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path d="M12 5v14M5 12h14" />
        </svg>
        <span>افزودن بهترین پزشک</span>
      </a>
      <button wire:click="deleteSelected"
        class="btn btn-gradient-danger rounded-pill px-4 d-flex align-items-center gap-2"
        @if (empty($selectedBestDoctors)) disabled @endif>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
        </svg>
        <span>حذف انتخاب‌شده‌ها</span>
      </button>
    </div>
  </div>

  <div class="container-fluid px-0">
    <!-- Desktop View -->
    <div class="container-fluid px-0 d-none d-md-block">
      <div class="card shadow-sm">
        <div class="card-body p-0">
          <div class="table-responsive text-nowrap">
            <table class="table table-bordered table-hover w-100 m-0">
              <thead class="glass-header text-white">
                <tr>
                  <th class="text-center align-middle" style="width: 50px;">
                    <input type="checkbox" wire:model.live="selectAll" class="form-check-input m-0 align-middle">
                  </th>
                  <th class="text-center align-middle" style="width: 70px;">ردیف</th>
                  <th class="text-center">نام پزشک</th>
                  <th class="text-center">کلینیک</th>
                  <th class="align-middle">بهترین پزشک</th>
                  <th class="align-middle">بهترین مشاور</th>
                  <th class="text-center align-middle" style="width: 100px;">وضعیت</th>
                  <th class="text-center align-middle" style="width: 150px;">عملیات</th>
                </tr>
              </thead>
              <tbody>
                @if ($readyToLoad)
                  @forelse ($bestdoctors as $index => $item)
                    <tr>
                      <td class="text-center align-middle">
                        <input type="checkbox" wire:model.live="selectedBestDoctors" value="{{ $item->id }}"
                          class="form-check-input m-0 align-middle">
                      </td>
                      <td class="text-center align-middle">{{ $bestdoctors->firstItem() + $index }}</td>
                      <td class="text-center">{{ $item->doctor->first_name . ' ' . $item->doctor->last_name }}</td>
                      <td class="text-center">{{ $item->clinic ? $item->clinic->name : '-' }}</td>
                      <td class="align-middle">{{ $item->best_doctor ? 'بله' : 'خیر' }}</td>
                      <td class="align-middle">{{ $item->best_consultant ? 'بله' : 'خیر' }}</td>
                      <td class="text-center align-middle">
                        <button wire:click="toggleStatus({{ $item->id }})"
                          class="badge {{ $item->status ? 'bg-label-success' : 'bg-label-danger' }} border-0 cursor-pointer">
                          {{ $item->status ? 'فعال' : 'غیرفعال' }}
                        </button>
                      </td>
                      <td class="text-center align-middle">
                        <div class="d-flex justify-content-center gap-2">
                          <a href="{{ route('admin.panel.best-doctors.edit', $item->id) }}"
                            class="btn btn-gradient-success rounded-pill px-3">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                              stroke-width="2">
                              <path
                                d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                          </a>
                          <button wire:click="confirmDelete({{ $item->id }})"
                            class="btn btn-gradient-danger rounded-pill px-3">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                              stroke-width="2">
                              <path
                                d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                            </svg>
                          </button>
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="8" class="text-center py-5">
                        <div class="d-flex justify-content-center align-items-center flex-column">
                          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" class="text-muted mb-3">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                          </svg>
                          <p class="text-muted fw-medium">هیچ پزشکی یافت نشد.</p>
                        </div>
                      </td>
                    </tr>
                  @endforelse
                @else
                  <tr>
                    <td colspan="8" class="text-center py-5">در حال بارگذاری بهترین پزشکان...</td>
                  </tr>
                @endif
              </tbody>
            </table>
          </div>
          <div class="d-flex justify-content-between align-items-center mt-4 px-4 flex-wrap gap-3">
            <div class="text-muted">نمایش {{ $bestdoctors ? $bestdoctors->firstItem() : 0 }} تا
              {{ $bestdoctors ? $bestdoctors->lastItem() : 0 }} از {{ $bestdoctors ? $bestdoctors->total() : 0 }} ردیف
            </div>
            @if ($bestdoctors && $bestdoctors->hasPages())
              <nav>
                <ul class="pagination">
                  {{ $bestdoctors->links('livewire::bootstrap') }}
                </ul>
              </nav>
            @endif
          </div>
        </div>
      </div>
    </div>
    <!-- Mobile/Tablet View -->
    <div class="container-fluid px-0 d-md-none">
      @if ($readyToLoad)
        @forelse ($bestdoctors as $index => $item)
          <div class="card shadow-sm mb-3">
            <div class="card-body">
              <div class="d-flex align-items-center gap-3 mb-3">
                <input type="checkbox" wire:model.live="selectedBestDoctors" value="{{ $item->id }}"
                  class="form-check-input m-0 align-middle">
                <div>
                  <h6 class="mb-1">{{ $item->doctor->first_name . ' ' . $item->doctor->last_name }}</h6>
                  <small class="text-muted">{{ $item->clinic ? $item->clinic->name : '-' }}</small>
                </div>
              </div>
              <div class="row g-3">
                <div class="col-6">
                  <small class="text-muted d-block">بهترین پزشک</small>
                  <span>{{ $item->best_doctor ? 'بله' : 'خیر' }}</span>
                </div>
                <div class="col-6">
                  <small class="text-muted d-block">بهترین مشاور</small>
                  <span>{{ $item->best_consultant ? 'بله' : 'خیر' }}</span>
                </div>
                <div class="col-6">
                  <small class="text-muted d-block">وضعیت</small>
                  <button wire:click="toggleStatus({{ $item->id }})"
                    class="badge {{ $item->status ? 'bg-label-success' : 'bg-label-danger' }} border-0 cursor-pointer">
                    {{ $item->status ? 'فعال' : 'غیرفعال' }}
                  </button>
                </div>
                <div class="col-6">
                  <small class="text-muted d-block">عملیات</small>
                  <div class="d-flex gap-2">
                    <a href="{{ route('admin.panel.best-doctors.edit', $item->id) }}"
                      class="btn btn-gradient-success btn-sm rounded-pill px-3">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path
                          d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                      </svg>
                    </a>
                    <button wire:click="confirmDelete({{ $item->id }})"
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
              <p class="text-muted fw-medium">هیچ پزشکی یافت نشد.</p>
            </div>
          </div>
        @endforelse
      @else
        <div class="text-center py-5">در حال بارگذاری بهترین پزشکان...</div>
      @endif
    </div>
  </div>

  <script>
    document.addEventListener('livewire:init', function() {
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });

      Livewire.on('confirm-delete', (event) => {
        Swal.fire({
          title: 'حذف بهترین پزشک',
          text: 'آیا مطمئن هستید که می‌خواهید این پزشک را حذف کنید؟',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#ef4444',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'بله، حذف کن',
          cancelButtonText: 'خیر'
        }).then((result) => {
          if (result.isConfirmed) {
            Livewire.dispatch('deleteBestDoctorConfirmed', {
              id: event.id
            });
          }
        });
      });
    });
  </script>
</div>

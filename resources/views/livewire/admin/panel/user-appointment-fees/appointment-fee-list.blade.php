@push('styles')
  <link rel="stylesheet" href="{{ asset('admin-assets/css/panel/doctor.css') }}">
@endpush

<div class="container-fluid py-2" dir="rtl" wire:init="loadFees">
  <header class="glass-header text-white p-3 rounded-3 shadow-lg">
    <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
      <div class="d-flex align-items-center gap-2 flex-shrink-0">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="header-icon">
          <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
        <h2 class="mb-0 fw-bold fs-5">مدیریت هزینه‌های نوبت</h2>
      </div>
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
          <a href="{{ route('admin.panel.user-appointment-fees.create') }}"
            class="btn btn-gradient-success rounded-pill px-4 d-flex align-items-center gap-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2">
              <path d="M12 5v14M5 12h14" />
            </svg>
            <span>افزودن حق نوبت</span>
          </a>
          <button wire:click="deleteSelected"
            class="btn btn-gradient-danger rounded-pill px-4 d-flex align-items-center gap-2"
            @if (empty($selectedFees)) disabled @endif>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2">
              <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
            </svg>
            <span>حذف انتخاب‌شده‌ها</span>
          </button>
        </div>
      </div>
    </div>
  </header>

  <div class="container-fluid px-0">
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
                  <th class="text-center align-middle cursor-pointer" style="width: 70px;" wire:click="sortBy('id')">
                    شناسه
                    @if ($sortField === 'id')
                      @if ($sortDirection === 'asc')
                        ↑
                      @else
                        ↓
                      @endif
                    @endif
                  </th>
                  <th class="align-middle cursor-pointer" wire:click="sortBy('name')">نام</th>
                  <th class="align-middle cursor-pointer" wire:click="sortBy('price')">قیمت</th>
                  <th class="align-middle cursor-pointer" wire:click="sortBy('discount')">تخفیف</th>
                  <th class="text-center align-middle cursor-pointer" style="width: 100px;"
                    wire:click="sortBy('status')">وضعیت</th>
                  <th class="text-center align-middle" style="width: 150px;">عملیات</th>
                </tr>
              </thead>
              <tbody>
                @if ($readyToLoad)
                  @forelse ($fees as $index => $fee)
                    <tr>
                      <td class="text-center align-middle">
                        <input type="checkbox" wire:model.live="selectedFees" value="{{ $fee->id }}"
                          class="form-check-input m-0">
                      </td>
                      <td class="text-center align-middle">{{ $fees->firstItem() + $index }}</td>
                      <td class="align-middle">{{ $fee->name }}</td>
                      <td class="align-middle">{{ number_format($fee->price) }} تومان</td>
                      <td class="align-middle">{{ $fee->discount }}%</td>
                      <td class="text-center align-middle">
                        <span class="badge {{ $fee->status ? 'bg-label-success' : 'bg-label-danger' }} border-0">
                          {{ $fee->status ? 'فعال' : 'غیرفعال' }}
                        </span>
                      </td>
                      <td class="text-center align-middle">
                        <div class="d-flex justify-content-center gap-2">
                          <a href="{{ route('admin.panel.user-appointment-fees.edit', $fee->id) }}"
                            class="btn btn-gradient-primary rounded-pill px-3">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                              stroke-width="2">
                              <path
                                d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                          </a>
                          <button wire:click="confirmDelete({{ $fee->id }})"
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
                      <td colspan="7" class="text-center py-5">
                        <div class="d-flex justify-content-center align-items-center flex-column">
                          <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" class="text-muted mb-3">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                          </svg>
                          <p class="text-muted fw-medium">هیچ هزینه نوبتی یافت نشد.</p>
                        </div>
                      </td>
                    </tr>
                  @endforelse
                @else
                  <tr>
                    <td colspan="7" class="text-center py-5">در حال بارگذاری هزینه‌های نوبت...</td>
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
        @forelse ($fees as $index => $fee)
          <div class="card shadow-sm mb-3">
            <div class="card-body">
              <div class="d-flex align-items-center gap-3 mb-3">
                <input type="checkbox" wire:model.live="selectedFees" value="{{ $fee->id }}"
                  class="form-check-input m-0">
                <div>
                  <h6 class="mb-1">{{ $fee->name }}</h6>
                  <small class="text-muted">{{ number_format($fee->price) }} تومان</small>
                </div>
              </div>
              <div class="row g-3">
                <div class="col-6">
                  <small class="text-muted d-block">شناسه</small>
                  <span>{{ $fees->firstItem() + $index }}</span>
                </div>
                <div class="col-6">
                  <small class="text-muted d-block">تخفیف</small>
                  <span>{{ $fee->discount }}%</span>
                </div>
                <div class="col-6">
                  <small class="text-muted d-block">وضعیت</small>
                  <span class="badge {{ $fee->status ? 'bg-label-success' : 'bg-label-danger' }} border-0">
                    {{ $fee->status ? 'فعال' : 'غیرفعال' }}
                  </span>
                </div>
                <div class="col-12">
                  <small class="text-muted d-block">عملیات</small>
                  <div class="d-flex gap-2">
                    <a href="{{ route('admin.panel.user-appointment-fees.edit', $fee->id) }}"
                      class="btn btn-gradient-primary btn-sm rounded-pill px-3">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path
                          d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                      </svg>
                    </a>
                    <button wire:click="confirmDelete({{ $fee->id }})"
                      class="btn btn-gradient-danger btn-sm rounded-pill px-3">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path
                          d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
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
              <p class="text-muted fw-medium">هیچ هزینه نوبتی یافت نشد.</p>
            </div>
          </div>
        @endforelse
      @else
        <div class="text-center py-5">در حال بارگذاری هزینه‌های نوبت...</div>
      @endif
    </div>

    <div class="d-flex justify-content-between align-items-center mt-4 px-4 flex-wrap gap-3">
      <div class="text-muted">
        نمایش {{ $fees ? $fees->firstItem() : 0 }} تا
        {{ $fees ? $fees->lastItem() : 0 }} از {{ $fees ? $fees->total() : 0 }} ردیف
      </div>
      @if ($fees && $fees->hasPages())
        <div class="pagination-container">
          {{ $fees->onEachSide(1)->links('livewire::bootstrap') }}
        </div>
      @endif
    </div>
  </div>

  <script>
    document.addEventListener('livewire:init', function () {
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });

      Livewire.on('confirm-delete', (event) => {
        Swal.fire({
          title: 'حذف هزینه نوبت',
          text: 'آیا مطمئن هستید که می‌خواهید این هزینه نوبت را حذف کنید؟',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#ef4444',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'بله، حذف کن',
          cancelButtonText: 'خیر'
        }).then((result) => {
          if (result.isConfirmed) {
            Livewire.dispatch('deleteAppointmentFeeConfirmed', {
              id: event.id
            });
          }
        });
      });
    });
  </script>
</div>
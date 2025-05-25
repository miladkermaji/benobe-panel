<div class="container-fluid py-2" dir="rtl" wire:init="loadInsurances">
  <div
    class="glass-header text-white p-3 rounded-3 mb-5 shadow-lg d-flex justify-content-between align-items-center flex-wrap gap-3">
    <h1 class="m-0 h3 font-thin flex-grow-1" style="min-width: 200px;">مدیریت بیمه‌های پزشکان</h1>
    <div class="input-group flex-grow-1 position-relative" style="max-width: 400px;">
      <input type="text" class="form-control border-0 shadow-none bg-white text-dark ps-5 rounded-3"
        wire:model.live="search" placeholder="جستجو در پزشکان..." style="padding-right: 23px">
      <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-3" style="z-index: 5;right: 5px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
          <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
        </svg>
      </span>
    </div>
    <div class="d-flex gap-2 flex-shrink-0 flex-wrap justify-content-center mt-md-2">
      <a href="{{ route('admin.panel.doctor-insurances.create') }}"
        class="btn btn-gradient-success rounded-pill px-4 d-flex align-items-center gap-2 w-100 w-md-auto justify-content-center">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 5v14M5 12h14" />
        </svg>
        <span class="text-truncate">افزودن</span>
      </a>
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
                  <span class="fw-bold">{{ $doctor->first_name . ' ' . $doctor->last_name }}
                    ({{ $doctor->mobile }})
                  </span>
                  <span class="badge bg-label-primary">{{ $doctor->insurances->count() }} بیمه</span>
                </div>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2"
                  class="transition-transform {{ in_array($doctor->id, $expandedDoctors) ? 'rotate-180' : '' }}">
                  <path d="M6 9l6 6 6-6" />
                </svg>
              </div>

              @if (in_array($doctor->id, $expandedDoctors))
                <!-- نمایش جدول در دسکتاپ -->
                <div class="d-none d-md-block">
                  <div class="table-responsive text-nowrap p-3 bg-light">
                    <table class="table table-bordered table-hover w-100 m-0">
                      <thead class="glass-header text-white">
                        <tr>
                          <th class="text-center align-middle" style="width: 70px;">ردیف</th>
                          <th class="align-middle">نام بیمه</th>
                          <th class="align-middle">کلینیک</th>
                          <th class="align-middle">روش محاسبه</th>
                          <th class="align-middle">قیمت نوبت</th>
                          <th class="align-middle">درصد بیمه</th>
                          <th class="align-middle">قیمت نهایی</th>
                          <th class="text-center align-middle" style="width: 150px;">عملیات</th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse ($doctor->insurances as $index => $insurance)
                          <tr>
                            <td class="text-center align-middle">{{ $index + 1 }}</td>
                            <td class="align-middle">{{ $insurance->name }}</td>
                            <td class="align-middle">{{ $insurance->clinic ? $insurance->clinic->name : 'ندارد' }}</td>
                            <td class="align-middle">
                              {{ $insurance->calculation_method == 0 ? 'مبلغ ثابت' : ($insurance->calculation_method == 1 ? 'درصد از مبلغ نوبت' : ($insurance->calculation_method == 2 ? 'مبلغ ثابت + درصد' : ($insurance->calculation_method == 3 ? 'فقط برای آمار' : 'پویا'))) }}
                            </td>
                            <td class="align-middle">
                              {{ $insurance->appointment_price ? number_format($insurance->appointment_price) : '-' }}
                            </td>
                            <td class="align-middle">
                              {{ $insurance->insurance_percent ? $insurance->insurance_percent . '%' : '-' }}</td>
                            <td class="align-middle">
                              {{ $insurance->final_price ? number_format($insurance->final_price) : '-' }}</td>
                            <td class="text-center align-middle">
                              <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('admin.panel.doctor-insurances.edit', $insurance->id) }}"
                                  class="btn btn-gradient-success rounded-pill px-3">
                                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path
                                      d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                  </svg>
                                </a>
                                <button wire:click="confirmDelete({{ $insurance->id }})"
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
                            <td colspan="8" class="text-center py-5">
                              <div class="d-flex flex-column align-items-center justify-content-center">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                                  stroke="currentColor" stroke-width="2" class="text-muted mb-3">
                                  <path d="M5 12h14M12 5l7 7-7 7" />
                                </svg>
                                <p class="text-muted fw-medium m-0">هیچ بیمه‌ای یافت نشد.</p>
                              </div>
                            </td>
                          </tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- نمایش کارت در موبایل و تبلت -->
                <div class="d-md-none p-3 bg-light">
                  @forelse ($doctor->insurances as $index => $insurance)
                    <div class="card shadow-sm mb-3 border-0">
                      <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                          <span class="badge bg-label-primary">#{{ $index + 1 }}</span>
                          <div class="d-flex gap-2">
                            <a href="{{ route('admin.panel.doctor-insurances.edit', $insurance->id) }}"
                              class="btn btn-gradient-success rounded-pill px-3">
                              <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path
                                  d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                              </svg>
                            </a>
                            <button wire:click="confirmDelete({{ $insurance->id }})"
                              class="btn btn-gradient-danger rounded-pill px-3">
                              <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path
                                  d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                              </svg>
                            </button>
                          </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                          <small class="text-muted">نام بیمه:</small>
                          <span class="fw-medium">{{ $insurance->name }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                          <small class="text-muted">کلینیک:</small>
                          <span class="fw-medium">{{ $insurance->clinic ? $insurance->clinic->name : 'ندارد' }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                          <small class="text-muted">روش محاسبه:</small>
                          <span class="fw-medium">
                            {{ $insurance->calculation_method == 0 ? 'مبلغ ثابت' : ($insurance->calculation_method == 1 ? 'درصد از مبلغ نوبت' : ($insurance->calculation_method == 2 ? 'مبلغ ثابت + درصد' : ($insurance->calculation_method == 3 ? 'فقط برای آمار' : 'پویا'))) }}
                          </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                          <small class="text-muted">قیمت نوبت:</small>
                          <span
                            class="fw-medium">{{ $insurance->appointment_price ? number_format($insurance->appointment_price) : '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                          <small class="text-muted">درصد بیمه:</small>
                          <span
                            class="fw-medium">{{ $insurance->insurance_percent ? $insurance->insurance_percent . '%' : '-' }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                          <small class="text-muted">قیمت نهایی:</small>
                          <span
                            class="fw-medium">{{ $insurance->final_price ? number_format($insurance->final_price) : '-' }}</span>
                        </div>
                      </div>
                    </div>
                  @empty
                    <div class="text-center py-5">
                      <div class="d-flex flex-column align-items-center justify-content-center">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2" class="text-muted mb-3">
                          <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                        <p class="text-muted fw-medium m-0">هیچ بیمه‌ای یافت نشد.</p>
                      </div>
                    </div>
                  @endforelse
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
                <p class="text-muted fw-medium m-0">هیچ پزشکی یافت نشد.</p>
              </div>
            </div>
          @endforelse
          @if ($doctors->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4 px-4 flex-wrap gap-3">
              <div class="text-muted">نمایش {{ $doctors->firstItem() }} تا {{ $doctors->lastItem() }} از
                {{ $doctors->total() }} ردیف</div>
              {{ $doctors->links('livewire::bootstrap') }}
            </div>
          @endif
        @else
          <div class="text-center py-5">در حال بارگذاری پزشکان و بیمه‌ها...</div>
        @endif
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('livewire:init', function() {
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });

      Livewire.on('confirm-delete', (event) => {
        Swal.fire({
          title: 'حذف بیمه',
          text: 'آیا مطمئن هستید که می‌خواهید این بیمه را حذف کنید؟',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#ef4444',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'بله، حذف کن',
          cancelButtonText: 'خیر'
        }).then((result) => {
          if (result.isConfirmed) {
            Livewire.dispatch('deleteInsuranceConfirmed', {
              id: event.id
            });
          }
        });
      });
    });
  </script>
</div>

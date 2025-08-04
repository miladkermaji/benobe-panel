<div class="container-fluid py-2" dir="rtl" wire:init="loadManualAppointments">
  <div
    class="glass-header text-white p-3 rounded-3 mb-5 shadow-lg d-flex justify-content-between align-items-center flex-wrap gap-3">
    <h1 class="m-0 h3 font-thin flex-grow-1" style="min-width: 200px;">مدیریت نوبت‌های دستی</h1>
    <div class="input-group flex-grow-1 position-relative" style="max-width: 400px;">
      <input type="text" class="form-control border-0 shadow-none bg-white text-dark ps-5 rounded-3"
        wire:model.live="search" placeholder="جستجو بر اساس نام، نام خانوادگی یا کد ملی..." style="padding-right: 23px">
      <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-3" style="z-index: 5;right: 5px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
          <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
        </svg>
      </span>
    </div>
    <div class="d-flex gap-2 flex-shrink-0 flex-wrap justify-content-center mt-md-2 buttons-container">
      <a href="{{ route('admin.panel.manual-appointments.create') }}"
        class="btn btn-gradient-success rounded-pill px-4 d-flex align-items-center gap-2 justify-content-center">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 5v14M5 12h14" />
        </svg>
        <span>افزودن</span>
      </a>
    </div>
  </div>

  <div class="container-fluid px-0">
    <div class="card shadow-sm">
      <div class="card-body p-0">
        @if ($readyToLoad)
          @forelse ($doctors as $data)
            <div class="doctor-toggle border-bottom">
              <div class="d-flex justify-content-between align-items-center p-3 cursor-pointer"
                wire:click="toggleDoctor({{ $data['doctor']->id }})">
                <div class="d-flex align-items-center gap-3 mb-2">
                  <img src="{{ $data['doctor']->profile_photo_url ?? asset('default-avatar.png') }}"
                    class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;" alt="پروفایل پزشک">
                  <span class="fw-bold">{{ $data['doctor']->first_name . ' ' . $data['doctor']->last_name }}</span>
                  <span class="badge bg-label-primary">{{ $data['totalAppointments'] }} نوبت</span>
                </div>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2"
                  class="transition-transform {{ in_array($data['doctor']->id, $expandedDoctors) ? 'rotate-180' : '' }}">
                  <path d="M6 9l6 6 6-6" />
                </svg>
              </div>

              @if (in_array($data['doctor']->id, $expandedDoctors))
                <div class="table-responsive text-nowrap p-3 bg-light d-none d-md-block">
                  <table class="table table-bordered table-hover w-100 m-0">
                    <thead class="glass-header text-white">
                      <tr>
                        <th class="text-center align-middle" style="width: 70px;">ردیف</th>
                        <th class="align-middle">بیمار</th>
                        <th class="align-middle">کد ملی بیمار</th>
                        <th class="align-middle">تاریخ نوبت</th>
                        <th class="align-middle">ساعت نوبت</th>
                        <th class="align-middle">وضعیت نوبت</th>
                        <th class="align-middle">وضعیت پرداخت</th>
                        <th class="align-middle">هزینه</th>
                        <th class="align-middle">کد رهگیری</th>
                        <th class="align-middle">یادداشت</th>
                        <th class="text-center align-middle" style="width: 150px;">عملیات</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse ($data['appointments'] as $index => $appointment)
                        <tr>
                          <td class="text-center align-middle">
                            {{ ($data['currentPage'] - 1) * $appointmentsPerPage + $index + 1 }}</td>
                          <td class="align-middle">
                            {{ $appointment->user->first_name . ' ' . $appointment->user->last_name ?? 'نامشخص' }}</td>
                          <td class="align-middle">{{ $appointment->user->national_code ?? 'نامشخص' }}</td>
                          <td class="align-middle">
                            {{ \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($appointment->appointment_date))->format('Y/m/d') }}
                          </td>
                          <td class="align-middle">{{ $appointment->appointment_time }}</td>
                          <td class="text-center align-middle">
                            <button wire:click="toggleStatus({{ $appointment->id }})"
                              class="badge {{ $appointment->status === 'scheduled' ? 'bg-label-success' : 'bg-label-danger' }} border-0 cursor-pointer">
                              {{ $appointment->status === 'scheduled' ? 'فعال' : 'لغو شده' }}
                            </button>
                          </td>
                          <td class="text-center align-middle">
                            <span
                              class="badge {{ $appointment->payment_status === 'paid' ? 'bg-label-success' : ($appointment->payment_status === 'unpaid' ? 'bg-label-danger' : 'bg-label-warning') }}">
                              {{ $appointment->payment_status === 'paid' ? 'پرداخت شده' : ($appointment->payment_status === 'unpaid' ? 'پرداخت نشده' : 'در انتظار') }}
                            </span>
                          </td>
                          <td class="align-middle">{{ number_format($appointment->fee) ?? 'نامشخص' }}</td>
                          <td class="align-middle">{{ $appointment->tracking_code ?? 'نامشخص' }}</td>
                          <td class="align-middle">{{ $appointment->description ?? '---' }}</td>
                          <td class="text-center align-middle">
                            <div class="d-flex justify-content-center gap-2">
                              <a href="{{ route('admin.panel.manual-appointments.edit', $appointment->id) }}"
                                class="btn btn-gradient-success rounded-pill px-3">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                  stroke="currentColor" stroke-width="2">
                                  <path
                                    d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                </svg>
                              </a>
                              <button wire:click="confirmDelete({{ $appointment->id }})"
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
                          <td colspan="11" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                              <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" class="text-muted mb-3">
                                <path d="M5 12h14M12 5l7 7-7 7" />
                              </svg>
                              <p class="text-muted fw-medium m-0">هیچ نوبت یافت نشد.</p>
                            </div>
                          </td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>

                <div class="d-md-none">
                  @forelse ($data['appointments'] as $index => $appointment)
                    <div class="card mb-3">
                      <div class="card-body">
                        <h5 class="card-title">
                          {{ $appointment->user->first_name . ' ' . $appointment->user->last_name ?? 'نامشخص' }}</h5>
                        <p class="card-text">کد ملی: {{ $appointment->user->national_code ?? 'نامشخص' }}</p>
                        <p class="card-text">تاریخ نوبت:
                          {{ \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($appointment->appointment_date))->format('Y/m/d') }}
                        </p>
                        <p class="card-text">ساعت نوبت: {{ $appointment->appointment_time }}</p>
                        <p class="card-text">وضعیت نوبت: <span
                            class="badge {{ $appointment->status === 'scheduled' ? 'bg-label-success' : 'bg-label-danger' }}">{{ $appointment->status === 'scheduled' ? 'فعال' : 'لغو شده' }}</span>
                        </p>
                        <p class="card-text">وضعیت پرداخت: <span
                            class="badge {{ $appointment->payment_status === 'paid' ? 'bg-label-success' : ($appointment->payment_status === 'unpaid' ? 'bg-label-danger' : 'bg-label-warning') }}">{{ $appointment->payment_status === 'paid' ? 'پرداخت شده' : ($appointment->payment_status === 'unpaid' ? 'پرداخت نشده' : 'در انتظار') }}</span>
                        </p>
                        <p class="card-text">هزینه: {{ number_format($appointment->fee) ?? 'نامشخص' }}</p>
                        <p class="card-text">کد رهگیری: {{ $appointment->tracking_code ?? 'نامشخص' }}</p>
                        <p class="card-text">یادداشت: {{ $appointment->description ?? '---' }}</p>
                        <div class="d-flex justify-content-end gap-2">
                          <a href="{{ route('admin.panel.manual-appointments.edit', $appointment->id) }}"
                            class="btn btn-gradient-success rounded-pill px-3">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                              stroke="currentColor" stroke-width="2">
                              <path
                                d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                          </a>
                          <button wire:click="confirmDelete({{ $appointment->id }})"
                            class="btn btn-gradient-danger rounded-pill px-3">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                              stroke="currentColor" stroke-width="2">
                              <path
                                d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                            </svg>
                          </button>
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
                        <p class="text-muted fw-medium m-0">هیچ نوبت یافت نشد.</p>
                      </div>
                    </div>
                  @endforelse
                </div>

                @if ($data['totalAppointments'] > $appointmentsPerPage)
                  <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                      نمایش {{ ($data['currentPage'] - 1) * $appointmentsPerPage + 1 }} تا
                      {{ min($data['currentPage'] * $appointmentsPerPage, $data['totalAppointments']) }} از
                      {{ $data['totalAppointments'] }} نوبت
                    </div>
                    <nav>
                      <ul class="pagination mb-0">
                        <li class="page-item {{ $data['currentPage'] == 1 ? 'disabled' : '' }}">
                          <button class="page-link"
                            wire:click="setDoctorPage({{ $data['doctor']->id }}, {{ $data['currentPage'] - 1 }})">قبلی</button>
                        </li>
                        @for ($i = 1; $i <= $data['lastPage']; $i++)
                          <li class="page-item {{ $data['currentPage'] == $i ? 'active' : '' }}">
                            <button class="page-link"
                              wire:click="setDoctorPage({{ $data['doctor']->id }}, {{ $i }})">{{ $i }}</button>
                          </li>
                        @endfor
                        <li class="page-item {{ $data['currentPage'] == $data['lastPage'] ? 'disabled' : '' }}">
                          <button class="page-link"
                            wire:click="setDoctorPage({{ $data['doctor']->id }}, {{ $data['currentPage'] + 1 }})">بعدی</button>
                        </li>
                      </ul>
                    </nav>
                  </div>
                @endif
              @endif
            </div>
          @empty
            <div class="text-center py-5">
              <div class="d-flex flex-column align-items-center justify-content-center">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2" class="text-muted mb-3">
                  <path d="M5 12h14M12 5l7 7-7 7" />
                </svg>
                <p class="text-muted fw-medium m-0">هیچ نوبت دستی یافت نشد.</p>
              </div>
            </div>
          @endforelse
        @else
          <div class="text-center py-5">در حال بارگذاری نوبت‌های دستی...</div>
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
          title: 'حذف نوبت دستی',
          text: 'آیا مطمئن هستید که می‌خواهید این نوبت را حذف کنید؟',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#ef4444',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'بله، حذف کن',
          cancelButtonText: 'خیر'
        }).then((result) => {
          if (result.isConfirmed) {
            Livewire.dispatch('deleteManualAppointmentConfirmed', {
              id: event.id
            });
          }
        });
      });
    });
  </script>
</div>

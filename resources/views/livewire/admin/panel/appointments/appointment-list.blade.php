<div class="container-fluid py-4" dir="rtl" wire:init="loadAppointments">
  <div
    class="glass-header p-4 rounded-xl mb-6 shadow-lg d-flex justify-content-between align-items-center flex-wrap gap-4">
    <h1 class="m-0 h3 font-light flex-grow-1" style="min-width: 200px; color: var(--text-primary);">مدیریت نوبت‌ها</h1>
    <div class="input-group flex-grow-1 position-relative" style="max-width: 450px;">
      <input type="text"
        class="form-control border-0 shadow-none bg-background-card text-text-primary ps-5 rounded-full h-12"
        wire:model.live="search" placeholder="جستجو بر اساس نام، نام خانوادگی یا کد ملی...">
      <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-4 text-text-secondary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
        </svg>
      </span>
    </div>
    <div class="d-flex gap-2 flex-shrink-0 flex-wrap justify-content-center mt-md-2">
      <a href="{{ route('admin.panel.appointments.create') }}"
        class="btn btn-gradient-success rounded-pill px-4 d-flex align-items-center gap-2">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 5v14M5 12h14" />
        </svg>
        <span>افزودن نوبت</span>
      </a>
    </div>
  </div>

  <div class="container-fluid px-0">
    <div class="card shadow-xl rounded-2xl overflow-hidden bg-background-card">
      <div class="card-body p-0">
        @if ($readyToLoad)
          @forelse ($doctors as $doctor)
            <div class="doctor-appointments border-bottom border-soft">
              <div
                class="d-flex justify-content-between align-items-center p-3 bg-background-light cursor-pointer hover:bg-background-hover transition-colors duration-200"
                wire:click="toggleDoctor({{ $doctor['doctor']->id }})">
                <div class="d-flex align-items-center gap-3">
                  <h3 class="h6 m-0 text-text-primary fw-medium">{{ $doctor['doctor']->full_name }}</h3>
                  <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-label-primary">{{ $doctor['totalAppointments'] }} نوبت</span>
                    <span
                      class="text-text-secondary text-xs">({{ $doctor['currentPage'] }}/{{ $doctor['lastPage'] }})</span>
                  </div>
                </div>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2"
                  class="transition-transform {{ in_array($doctor['doctor']->id, $expandedDoctors) ? 'rotate-180' : '' }}">
                  <path d="M19 9l-7 7-7-7" />
                </svg>
              </div>

              @if (in_array($doctor['doctor']->id, $expandedDoctors))
                <!-- Desktop Table View -->
                <div class="table-responsive position-relative w-100 d-none d-md-block">
                  <table class="table table-hover w-100 text-sm text-center">
                    <thead class="bg-light">
                      <tr>
                        <th><input class="form-check-input" type="checkbox"
                            wire:click="$set('selectedAppointments', [])"></th>
                        <th scope="col" class="px-6 py-3 fw-bolder">نام بیمار</th>
                        <th scope="col" class="px-6 py-3 fw-bolder">شماره‌ موبایل</th>
                        <th scope="col" class="px-6 py-3 fw-bolder">کد ملی</th>
                        <th scope="col" class="px-6 py-3 fw-bolder">زمان نوبت</th>
                        <th scope="col" class="px-6 py-3 fw-bolder">بیعانه</th>
                        <th scope="col" class="px-6 py-3 fw-bolder">وضعیت پرداخت</th>
                        <th scope="col" class="px-6 py-3 fw-bolder">بیمه</th>
                        <th scope="col" class="px-6 py-3 fw-bolder">قیمت نهایی</th>
                        <th scope="col" class="px-6 py-3 fw-bolder">عملیات</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse ($doctor['appointments'] as $appointment)
                        <tr class="hover:bg-background-light transition-colors duration-200">
                          <td class="text-center align-middle">
                            <input type="checkbox" wire:model.live="selectedAppointments" value="{{ $appointment->id }}"
                              class="form-check-input m-0 shadow-sm">
                          </td>
                          <td class="align-middle text-text-primary">{{ $appointment->patient->full_name }}</td>
                          <td class="align-middle text-text-secondary">{{ $appointment->patient->phone }}</td>
                          <td class="align-middle text-text-secondary">{{ $appointment->patient->national_code }}</td>
                          <td class="align-middle text-text-primary">
                            {{ \Morilog\Jalali\Jalalian::fromDateTime($appointment->appointment_date)->format('Y/m/d') }}
                            {{ $appointment->appointment_time }}
                          </td>
                          <td class="align-middle">
                            <span class="badge bg-label-success">{{ number_format($appointment->deposit) }}
                              تومان</span>
                          </td>
                          <td class="align-middle">
                            <span
                              class="badge {{ $appointment->payment_status === 'paid' ? 'bg-label-success' : 'bg-label-danger' }}">
                              {{ $appointment->payment_status === 'paid' ? 'پرداخت شده' : 'پرداخت نشده' }}
                            </span>
                          </td>
                          <td class="align-middle text-text-secondary">
                            {{ $appointment->insurance_type ?? 'بدون بیمه' }}</td>
                          <td class="align-middle text-text-primary">{{ number_format($appointment->fee) }}
                            تومان</td>
                          <td class="align-middle">
                            <div class="d-flex justify-content-center gap-2">
                              <a href="{{ route('admin.panel.appointments.edit', $appointment->id) }}"
                                class="btn btn-sm btn-outline-primary rounded-pill">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                  stroke="currentColor" stroke-width="2">
                                  <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                                  <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                </svg>
                              </a>
                              <button wire:click="confirmDelete({{ $appointment->id }})"
                                class="btn btn-sm btn-outline-danger rounded-pill">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
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
                          <td colspan="10" class="text-center py-6">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                              <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                                stroke="var(--text-secondary)" stroke-width="2" class="mb-3 animate-bounce">
                                <path d="M5 12h14M12 5l7 7-7 7" />
                              </svg>
                              <p class="text-text-secondary font-medium m-0">هیچ نوبتی یافت نشد</p>
                            </div>
                          </td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>

                <!-- Mobile Cards View -->
                <div class="d-md-none p-3">
                  @forelse ($doctor['appointments'] as $appointment)
                    <div class="card mb-3 rounded-xl shadow-sm bg-background-card">
                      <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                          <div class="d-flex align-items-center gap-2">
                            <input type="checkbox" wire:model.live="selectedAppointments"
                              value="{{ $appointment->id }}" class="form-check-input m-0 shadow-sm">
                            <span class="text-text-primary font-medium">{{ $appointment->patient->full_name }}</span>
                          </div>
                          <span
                            class="badge {{ $appointment->payment_status === 'paid' ? 'bg-label-success' : 'bg-label-danger' }}">
                            {{ $appointment->payment_status === 'paid' ? 'پرداخت شده' : 'پرداخت نشده' }}
                          </span>
                        </div>

                        <div class="d-flex flex-column gap-2">
                          <div class="d-flex justify-content-between">
                            <span class="text-text-secondary">شماره موبایل:</span>
                            <span class="text-text-primary">{{ $appointment->patient->phone }}</span>
                          </div>
                          <div class="d-flex justify-content-between">
                            <span class="text-text-secondary">کد ملی:</span>
                            <span class="text-text-primary">{{ $appointment->patient->national_code }}</span>
                          </div>
                          <div class="d-flex justify-content-between">
                            <span class="text-text-secondary">زمان نوبت:</span>
                            <span class="text-text-primary">
                              {{ \Morilog\Jalali\Jalalian::fromDateTime($appointment->appointment_date)->format('Y/m/d') }}
                              {{ $appointment->appointment_time }}
                            </span>
                          </div>
                          <div class="d-flex justify-content-between">
                            <span class="text-text-secondary">بیعانه:</span>
                            <span class="badge bg-label-success">{{ number_format($appointment->deposit) }}
                              تومان</span>
                          </div>
                          <div class="d-flex justify-content-between">
                            <span class="text-text-secondary">بیمه:</span>
                            <span class="text-text-primary">{{ $appointment->insurance_type ?? 'بدون بیمه' }}</span>
                          </div>
                          <div class="d-flex justify-content-between">
                            <span class="text-text-secondary">قیمت نهایی:</span>
                            <span class="text-text-primary">{{ number_format($appointment->fee) }} تومان</span>
                          </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-3 pt-3 border-top">
                          <a href="{{ route('admin.panel.appointments.edit', $appointment->id) }}"
                            class="btn btn-sm btn-outline-primary rounded-pill">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                              stroke="currentColor" stroke-width="2">
                              <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                              <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                          </a>
                          <button wire:click="confirmDelete({{ $appointment->id }})"
                            class="btn btn-sm btn-outline-danger rounded-pill">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                              stroke="currentColor" stroke-width="2">
                              <path
                                d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                            </svg>
                          </button>
                        </div>
                      </div>
                    </div>
                  @empty
                    <div class="text-center py-6">
                      <div class="d-flex flex-column align-items-center justify-content-center">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                          stroke="var(--text-secondary)" stroke-width="2" class="mb-3 animate-bounce">
                          <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                        <p class="text-text-secondary font-medium m-0">هیچ نوبتی یافت نشد</p>
                      </div>
                    </div>
                  @endforelse
                </div>

                @if ($doctor['totalAppointments'] > $appointmentsPerPage)
                  <div class="d-flex justify-content-center p-2 bg-background-light">
                    <div class="btn-group">
                      @if ($doctor['currentPage'] > 1)
                        <button wire:click="setDoctorPage({{ $doctor['doctor']->id }}, 1)"
                          class="btn btn-sm btn-outline-primary">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                          </svg>
                        </button>
                        <button
                          wire:click="setDoctorPage({{ $doctor['doctor']->id }}, {{ $doctor['currentPage'] - 1 }})"
                          class="btn btn-sm btn-outline-primary">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M15 18l-6-6 6-6" />
                          </svg>
                        </button>
                      @endif

                      @for ($i = max(1, $doctor['currentPage'] - 1); $i <= min($doctor['lastPage'], $doctor['currentPage'] + 1); $i++)
                        <button wire:click="setDoctorPage({{ $doctor['doctor']->id }}, {{ $i }})"
                          class="btn btn-sm {{ $doctor['currentPage'] == $i ? 'btn-primary' : 'btn-outline-primary' }}">
                          {{ $i }}
                        </button>
                      @endfor

                      @if ($doctor['currentPage'] < $doctor['lastPage'])
                        <button
                          wire:click="setDoctorPage({{ $doctor['doctor']->id }}, {{ $doctor['currentPage'] + 1 }})"
                          class="btn btn-sm btn-outline-primary">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M9 18l6-6-6-6" />
                          </svg>
                        </button>
                        <button wire:click="setDoctorPage({{ $doctor['doctor']->id }}, {{ $doctor['lastPage'] }})"
                          class="btn btn-sm btn-outline-primary">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M13 7l5 5m0 0l-5 5m5-5H6" />
                          </svg>
                        </button>
                      @endif
                    </div>
                  </div>
                @endif
              @endif
            </div>
          @empty
            <div class="text-center py-6">
              <div class="d-flex flex-column align-items-center justify-content-center">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                  stroke="var(--text-secondary)" stroke-width="2" class="mb-3 animate-bounce">
                  <path d="M5 12h14M12 5l7 7-7 7" />
                </svg>
                <p class="text-text-secondary font-medium m-0">هیچ نوبتی یافت نشد</p>
              </div>
            </div>
          @endforelse
        @else
          <div class="text-center py-6">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">در حال بارگذاری...</span>
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

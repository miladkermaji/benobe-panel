<div class="doctor-prescriptions-container">
  <div class="py-2" dir="rtl">
    <div class="glass-header text-white p-2  shadow-lg">
      <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3 w-100">
        <div class="d-flex flex-column flex-md-row gap-2 w-100 align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-3">
            <h1 class="m-0 h4 font-thin text-nowrap  mb-md-0">نسخه‌های من</h1>
          </div>
          <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2 search-filter-bar">
            <div class="d-flex gap-2 flex-shrink-0 justify-content-center w-100 flex-row-reverse flex-md-row">
              <div class="search-container position-relative flex-grow-1">
                <input type="text"
                  class="form-control search-input border-0 shadow-none bg-white text-dark ps-4 rounded-2 text-start"
                  wire:model.live="search" placeholder="جستجو در نام بیمار، کد ملی یا کد رهگیری..."
                  style="padding-right: 36px; text-align: right; direction: rtl; max-width: 100%; min-width: 0;">
                <span class="search-icon position-absolute top-50 end-0 translate-middle-y pe-2">
                  <i class="bi bi-search"></i>
                </span>
              </div>
              <select class="form-select flex-shrink-0 filter-select-mobile" wire:model.live="status"
                style="max-width: 140px; min-width: 100px;">
                <option value="">همه وضعیت‌ها</option>
                <option value="pending">در انتظار</option>
                <option value="completed">پایان یافته</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="container-fluid px-0">
      <div class="card shadow-sm rounded-2">
        <div class="card-body p-0">
          <div class="table-responsive text-nowrap d-none d-md-block">
            <table class="table table-hover w-100 m-0">
              <thead>
                <tr>
                  <th>#</th>
                  <th>بیمار</th>
                  <th>نوع</th>
                  <th>بیمه</th>
                  <th>کلینیک</th>
                  <th>مبلغ</th>
                  <th>توضیحات</th>
                  <th>انسولین‌ها</th>
                  <th>کد رهگیری</th>
                  <th>تاریخ</th>
                  <th>عملیات</th>
                </tr>
              </thead>
              <tbody>
                @forelse($prescriptions as $index => $item)
                  <tr>
                    <td>{{ $prescriptions->firstItem() + $index }}</td>
                    <td>
                      {{ optional($item->patient)->first_name }} {{ optional($item->patient)->last_name }}
                      <button type="button" class="btn btn-link p-0 ms-1 align-baseline" title="اطلاعات بیمار"
                        wire:click="showPatientInfo({{ optional($item->patient)->id }})">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                          xmlns="http://www.w3.org/2000/svg">
                          <circle cx="12" cy="12" r="10" stroke="#4f9acd" stroke-width="2" />
                          <path d="M12 8v2m0 4h.01" stroke="#4f9acd" stroke-width="2" stroke-linecap="round" />
                        </svg>
                      </button>
                    </td>
                    <td>
                      @switch($item->type)
                        @case('renew_lab')
                          آزمایش
                        @break

                        @case('renew_drug')
                          دارو
                        @break

                        @case('renew_insulin')
                          انسولین
                        @break

                        @case('sonography')
                          سونوگرافی
                        @break

                        @case('mri')
                          MRI
                        @break

                        @case('other')
                          سایر
                        @break

                        @default
                          -
                      @endswitch
                    </td>
                    <td>
                      @if ($item->insurances && $item->insurances->count())
                        @foreach ($item->insurances as $insurance)
                          {{ $insurance->name }}
                          @php
                            $parent = $insurance->parent;
                            $referralNames = ['سلامت همگانی(ایرانیان)', 'کمیته امداد', 'سایر اقشار', 'بهزیستی'];
                            $needsReferral = $parent && $parent->id == 3 && in_array($insurance->name, $referralNames);
                          @endphp
                          @if ($needsReferral)
                            <span class="text-secondary small"> (کد ارجاع:
                              {{ $insurance->pivot->referral_code ?: '-' }})</span>
                          @endif
                          @if (!$loop->last)
                            ,
                          @endif
                        @endforeach
                      @else
                        -
                      @endif
                    </td>
                    <td>{{ optional($item->medicalCenter)->name ?? '-' }}</td>
                    <td>
                      {{ $item->price ? number_format($item->price) . ' تومان' : '-' }}
                      @switch($item->payment_status)
                        @case('pending')
                          <span class="badge bg-warning text-dark ms-1 align-middle">در انتظار</span>
                        @break

                        @case('paid')
                          <span class="badge bg-success ms-1 align-middle">پرداخت شده</span>
                        @break

                        @case('failed')
                          <span class="badge bg-danger ms-1 align-middle">ناموفق</span>
                        @break

                        @default
                          <span class="badge bg-secondary ms-1 align-middle">-</span>
                      @endswitch
                    </td>
                    <td><span title="{{ $item->description }}">{{ \Str::limit($item->description, 30) }}</span></td>
                    <td>
                      @if ($item->insulins && $item->insulins->count())
                        @foreach ($item->insulins as $insulin)
                          <span class="badge bg-info text-dark mb-1">{{ $insulin->name }}
                            ({{ $insulin->pivot->count }})
                          </span>
                        @endforeach
                      @else
                        -
                      @endif
                    </td>
                    <td>
                      @if ($item->tracking_code)
                        <span class="badge bg-success">{{ $item->tracking_code }}</span>
                      @else
                        <span class="text-muted">ثبت نشده</span>
                      @endif
                    </td>
                    <td>{{ jdate($item->created_at)->format('Y/m/d H:i') }}</td>
                    <td>
                      <button
                        class="btn btn-sm btn-primary @if ($item->status === 'completed') disabled bg-secondary border-0 @endif"
                        wire:click="editTrackingCode({{ $item->id }})"
                        @if ($item->status === 'completed') disabled @endif>
                        @if ($item->status === 'completed')
                          پاسخ داده شده
                        @else
                          پاسخ نسخه
                        @endif
                      </button>
                    </td>
                  </tr>
                  @empty
                    <tr>
                      <td colspan="13" class="text-center py-4">هیچ نسخه‌ای یافت نشد.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
            <!-- کارت‌های نسخه برای موبایل و تبلت -->
            <div class="d-md-none">
              <div class="row g-3">
                @forelse($prescriptions as $index => $item)
                  <div class="col-12">
                    <div class="card prescription-card shadow-sm rounded-3 h-100 mb-2">
                      <div
                        class="card-header d-flex justify-content-between align-items-center prescription-card-header-mobile p-2"
                        wire:ignore
                        style="cursor:pointer; background: linear-gradient(90deg, #4f9acd 0%, #2e86c1 100%); min-height:38px; color:#fff; transition: background 0.3s;"
                        aria-controls="prescCollapse{{ $item->id }}">
                        <span class="fw-bold text-white small">
                          {{ optional($item->patient)->first_name }} {{ optional($item->patient)->last_name }}
                          @if (optional($item->patient)->national_code)
                            <span class="text-white-50"> ({{ optional($item->patient)->national_code }})</span>
                          @endif
                        </span>
                        <button type="button" class="chevron-icon ms-2 btn btn-link p-0 shadow-none"
                          onclick="toggleAccordion(this)">
                          <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M7 10L12 15L17 10" stroke="#fff" stroke-width="2" stroke-linecap="round"
                              stroke-linejoin="round" />
                          </svg>
                        </button>
                      </div>
                      <div class="accordion-content">
                        <div class="card-body d-flex flex-column gap-2">
                          <div class="note-card-item"><span class="note-card-label">کد رهگیری:</span><span
                              class="note-card-value">{{ $item->tracking_code ?? 'ثبت نشده' }}</span></div>
                          <div class="note-card-item"><span class="note-card-label">نوع:</span><span
                              class="note-card-value">
                              @switch($item->type)
                                @case('renew_lab')
                                  آزمایش
                                @break

                                @case('renew_drug')
                                  دارو
                                @break

                                @case('renew_insulin')
                                  انسولین
                                @break

                                @case('sonography')
                                  سونوگرافی
                                @break

                                @case('mri')
                                  MRI
                                @break

                                @case('other')
                                  سایر
                                @break

                                @default
                                  -
                              @endswitch
                            </span></div>
                          <div class="note-card-item"><span class="note-card-label">بیمه:</span><span
                              class="note-card-value">
                              @if ($item->insurances && $item->insurances->count())
                                @foreach ($item->insurances as $insurance)
                                  {{ $insurance->name }}
                                  @php
                                    $parent = $insurance->parent;
                                    $referralNames = ['سلامت همگانی(ایرانیان)', 'کمیته امداد', 'سایر اقشار', 'بهزیستی'];
                                    $needsReferral =
                                        $parent && $parent->id == 3 && in_array($insurance->name, $referralNames);
                                  @endphp
                                  @if ($needsReferral)
                                    <span class="text-secondary small"> (کد ارجاع:
                                      {{ $insurance->pivot->referral_code ?: '-' }})</span>
                                  @endif
                                  @if (!$loop->last)
                                    ,
                                  @endif
                                @endforeach
                              @else
                                -
                              @endif
                            </span></div>
                          <div class="note-card-item"><span class="note-card-label">کلینیک:</span><span
                              class="note-card-value">{{ optional($item->medicalCenter)->name ?? '-' }}</span></div>
                          <div class="note-card-item"><span class="note-card-label">مبلغ:</span><span
                              class="note-card-value">{{ $item->price ? number_format($item->price) . ' تومان' : '-' }}
                              @switch($item->payment_status)
                                @case('pending')
                                  <span class="badge bg-warning text-dark ms-1 align-middle">در انتظار</span>
                                @break

                                @case('paid')
                                  <span class="badge bg-success ms-1 align-middle">پرداخت شده</span>
                                @break

                                @case('failed')
                                  <span class="badge bg-danger ms-1 align-middle">ناموفق</span>
                                @break

                                @default
                                  <span class="badge bg-secondary ms-1 align-middle">-</span>
                              @endswitch
                            </span></div>
                          <div class="note-card-item"><span class="note-card-label">تاریخ:</span><span
                              class="note-card-value">{{ jdate($item->created_at)->format('Y/m/d H:i') }}</span></div>
                          <div class="note-card-item"><span class="note-card-label">توضیحات:</span><span
                              class="note-card-value"
                              title="{{ $item->description }}">{{ \Str::limit($item->description, 30) }}</span></div>
                          @if ($item->insulins && $item->insulins->count())
                            <div class="note-card-item"><span class="note-card-label">انسولین‌ها:</span>
                              <span class="note-card-value">
                                @foreach ($item->insulins as $insulin)
                                  <span class="badge bg-info text-dark mb-1">{{ $insulin->name }}
                                    ({{ $insulin->pivot->count }})
                                  </span>
                                @endforeach
                              </span>
                            </div>
                          @endif
                          <div class="d-flex justify-content-end mt-2">
                            <button
                              class="btn btn-sm btn-primary w-100 @if ($item->status === 'completed') disabled bg-secondary border-0 @endif"
                              wire:click="editTrackingCode({{ $item->id }})"
                              @if ($item->status === 'completed') disabled @endif>
                              @if ($item->status === 'completed')
                                پاسخ داده شده
                              @else
                                پاسخ نسخه
                              @endif
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  @empty
                    <div class="col-12">
                      <div class="alert alert-light text-center">هیچ نسخه‌ای یافت نشد.</div>
                    </div>
                  @endforelse
                </div>
                <div class="d-flex justify-content-center mt-3">
                  {{ $prescriptions->links() }}
                </div>
              </div>
            </div>
          </div>
          <!-- Modal ثبت کد رهگیری -->
          <div class="modal fade" id="trackingModal" tabindex="-1" aria-labelledby="trackingModalLabel"
            aria-hidden="true" wire:ignore.self>
            <div class="modal-dialog modal-dialog-centered">
              <!-- اضافه کردن modal-dialog-centered برای وسط چین شدن عمودی -->
              <div class="modal-content tracking-modal-animate"> <!-- کلاس افکت -->
                <div class="modal-header">
                  <h5 class="modal-title" id="trackingModalLabel">ثبت/ویرایش کد رهگیری نسخه</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <input type="text" class="form-control mb-2" wire:model.defer="tracking_code"
                    placeholder="کد رهگیری نسخه">
                  <textarea class="form-control" wire:model.defer="doctor_description" placeholder="توضیحات پزشک" rows="3"></textarea>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
                  <button type="button" class="btn btn-primary" wire:click="updateTrackingCode">ثبت</button>
                </div>
              </div>
            </div>
          </div>
          <script>
            document.addEventListener('livewire:init', function() {
              Livewire.on('showTrackingModal', () => {
                setTimeout(function() {
                  if (typeof bootstrap !== 'undefined') {
                    var modal = new bootstrap.Modal(document.getElementById('trackingModal'));
                    modal.show();
                  }
                }, 100);
              });
              Livewire.on('hideTrackingModal', () => {
                if (typeof bootstrap !== 'undefined') {
                  var modal = bootstrap.Modal.getInstance(document.getElementById('trackingModal'));
                  if (modal) modal.hide();
                }
              });
              Livewire.on('show-alert', (event) => {
                toastr[event.type](event.message);
              });
            });
            // رفع مشکل باز نشدن دراپ‌داون در صورت نبود Bootstrap JS
            document.querySelectorAll('.chevron-icon[data-bs-toggle="collapse"]').forEach(function(btn) {
              btn.addEventListener('click', function(e) {
                var target = btn.getAttribute('data-bs-target');
                if (!window.bootstrap) {
                  var el = document.querySelector(target);
                  if (el) {
                    el.classList.toggle('show');
                  }
                }
              });
            });

            function toggleAccordion(btn) {
              var content = btn.closest('.card').querySelector('.accordion-content');
              content.classList.toggle('show');
              btn.classList.toggle('open');
            }
          </script>
        </div>
      </div>

      {{-- Patient Info Modal --}}
      <div class="modal fade" id="patientInfoModal" tabindex="-1" aria-labelledby="patientInfoModalLabel"
        aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="patientInfoModalLabel">اطلاعات بیمار</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body">
              @if ($selectedPatient)
                <div class="bg-light rounded-3 p-3 mb-2 border" style="font-size:1.04em;">
                  <ul class="list-unstyled mb-0">
                    <li class="d-flex align-items-center mb-2">
                      <span class="me-2 text-primary">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7"
                          viewBox="0 0 24 24">
                          <path
                            d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v2h20v-2c0-3.3-6.7-5-10-5z" />
                        </svg>
                      </span>
                      <span class="fw-bold">نام و نام خانوادگی:</span>
                      <span class="ms-2">{{ $selectedPatient['full_name'] }}</span>
                    </li>
                    <li class="d-flex align-items-center mb-2">
                      <span class="me-2 text-info">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7"
                          viewBox="0 0 24 24">
                          <path d="M4 7V4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3" />
                          <rect width="16" height="12" x="4" y="7" rx="2" />
                          <path d="M8 11h8M8 15h6" />
                        </svg>
                      </span>
                      <span class="fw-bold">کد ملی:</span>
                      <span class="ms-2">{{ $selectedPatient['national_code'] }}</span>
                    </li>
                    <li class="d-flex align-items-center mb-2">
                      <span class="me-2 text-success">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7"
                          viewBox="0 0 24 24">
                          <path
                            d="M3 5.5A2.5 2.5 0 0 1 5.5 3h13A2.5 2.5 0 0 1 21 5.5v13A2.5 2.5 0 0 1 18.5 21h-13A2.5 2.5 0 0 1 3 18.5v-13z" />
                          <path d="M7 10h.01M12 10h.01M17 10h.01M7 14h.01M12 14h.01M17 14h.01" />
                        </svg>
                      </span>
                      <span class="fw-bold">شماره موبایل:</span>
                      <span class="ms-2">{{ $selectedPatient['mobile'] }}</span>
                    </li>
                    @if ($selectedPatient['date_of_birth'])
                      <li class="d-flex align-items-center mb-2">
                        <span class="me-2 text-warning">
                          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7"
                            viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 6v6l4 2" />
                          </svg>
                        </span>
                        <span class="fw-bold">تاریخ تولد:</span>
                        <span class="ms-2">{{ $selectedPatient['date_of_birth'] }}</span>
                      </li>
                    @endif
                    @if ($selectedPatient['sex'])
                      <li class="d-flex align-items-center mb-2">
                        <span class="me-2 text-secondary">
                          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7"
                            viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 8v4" />
                            <path d="M12 16h.01" />
                          </svg>
                        </span>
                        <span class="fw-bold">جنسیت:</span>
                        <span
                          class="ms-2">{{ $selectedPatient['sex'] == 'male' ? 'مرد' : ($selectedPatient['sex'] == 'female' ? 'زن' : $selectedPatient['sex']) }}</span>
                      </li>
                    @endif
                    @if ($selectedPatient['province'] || $selectedPatient['city'])
                      <li class="d-flex align-items-center mb-2">
                        <span class="me-2 text-primary">
                          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7"
                            viewBox="0 0 24 24">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" />
                            <circle cx="12" cy="9" r="2.5" />
                          </svg>
                        </span>
                        <span class="fw-bold">استان/شهر:</span>
                        <span
                          class="ms-2">{{ $selectedPatient['province'] }}{{ $selectedPatient['province'] && $selectedPatient['city'] ? ' / ' : '' }}{{ $selectedPatient['city'] }}</span>
                      </li>
                    @endif
                    @if ($selectedPatient['address'])
                      <li class="d-flex align-items-center mb-2">
                        <span class="me-2 text-dark">
                          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7"
                            viewBox="0 0 24 24">
                            <path d="M3 21v-2a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                          </svg>
                        </span>
                        <span class="fw-bold">آدرس:</span>
                        <span class="ms-2">{{ $selectedPatient['address'] }}</span>
                      </li>
                    @endif
                    @if ($selectedPatient['email'])
                      <li class="d-flex align-items-center mb-2">
                        <span class="me-2 text-danger">
                          <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7"
                            viewBox="0 0 24 24">
                            <rect x="2" y="4" width="20" height="16" rx="2" />
                            <path d="M22 6 12 13 2 6" />
                          </svg>
                        </span>
                        <span class="fw-bold">ایمیل:</span>
                        <span class="ms-2">{{ $selectedPatient['email'] }}</span>
                      </li>
                    @endif
                  </ul>
                </div>
              @else
                <div class="text-danger">اطلاعاتی برای این بیمار یافت نشد.</div>
              @endif
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
            </div>
          </div>
        </div>
      </div>
      <script>
        document.addEventListener('livewire:init', function() {
          Livewire.on('showPatientInfoModal', () => {
            setTimeout(function() {
              if (typeof bootstrap !== 'undefined') {
                var modal = new bootstrap.Modal(document.getElementById('patientInfoModal'));
                modal.show();
              }
            }, 100);
          });
          Livewire.on('hidePatientInfoModal', () => {
            if (typeof bootstrap !== 'undefined') {
              var modal = bootstrap.Modal.getInstance(document.getElementById('patientInfoModal'));
              if (modal) modal.hide();
            }
          });
        });
      </script>

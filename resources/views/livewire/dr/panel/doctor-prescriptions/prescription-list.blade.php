<div class="doctor-prescriptions-container">
  <div class="py-2" dir="rtl">
    <div class="glass-header text-white p-2 rounded-2 mb-4 shadow-lg">
      <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3 w-100">
        <div class="d-flex flex-column flex-md-row gap-2 w-100 align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-3">
            <h1 class="m-0 h4 font-thin text-nowrap mb-3 mb-md-0">نسخه‌های من</h1>
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
                    <td>{{ optional($item->prescriptionInsurance)->name ?? '-' }}</td>
                    <td>{{ optional($item->clinic)->name ?? '-' }}</td>
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
                        <span class="fw-bold text-white small">{{ optional($item->patient)->first_name }}
                          {{ optional($item->patient)->last_name }}
                        </span>
                        <button type="button" class="btn btn-link p-0 ms-1 align-baseline" title="اطلاعات بیمار"
                          wire:click.stop="showPatientInfo({{ optional($item->patient)->id }})">
                          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="10" stroke="#fff" stroke-width="2" />
                            <path d="M12 8v2m0 4h.01" stroke="#fff" stroke-width="2" stroke-linecap="round" />
                          </svg>
                        </button>
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
                              class="note-card-value">{{ optional($item->prescriptionInsurance)->name ?? '-' }}</span>
                          </div>
                          <div class="note-card-item"><span class="note-card-label">کلینیک:</span><span
                              class="note-card-value">{{ optional($item->clinic)->name ?? '-' }}</span></div>
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
                  var modal = new bootstrap.Modal(document.getElementById('trackingModal'));
                  modal.show();
                }, 100);
              });
              Livewire.on('hideTrackingModal', () => {
                var modal = bootstrap.Modal.getInstance(document.getElementById('trackingModal'));
                if (modal) modal.hide();
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
                <div class="mb-2"><strong>نام و نام خانوادگی:</strong> {{ $selectedPatient['full_name'] }}</div>
                <div class="mb-2"><strong>کد ملی:</strong> {{ $selectedPatient['national_code'] }}</div>
                <div class="mb-2"><strong>شماره موبایل:</strong> {{ $selectedPatient['mobile'] }}</div>
                @if ($selectedPatient['date_of_birth'])
                  <div class="mb-2"><strong>تاریخ تولد:</strong> {{ $selectedPatient['date_of_birth'] }}</div>
                @endif
                @if ($selectedPatient['sex'])
                  <div class="mb-2"><strong>جنسیت:</strong>
                    {{ $selectedPatient['sex'] == 'male' ? 'مرد' : ($selectedPatient['sex'] == 'female' ? 'زن' : $selectedPatient['sex']) }}
                  </div>
                @endif
                @if ($selectedPatient['province'] || $selectedPatient['city'])
                  <div class="mb-2"><strong>استان/شهر:</strong>
                    {{ $selectedPatient['province'] }}{{ $selectedPatient['province'] && $selectedPatient['city'] ? ' / ' : '' }}{{ $selectedPatient['city'] }}
                  </div>
                @endif
                @if ($selectedPatient['address'])
                  <div class="mb-2"><strong>آدرس:</strong> {{ $selectedPatient['address'] }}</div>
                @endif
                @if ($selectedPatient['email'])
                  <div class="mb-2"><strong>ایمیل:</strong> {{ $selectedPatient['email'] }}</div>
                @endif
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
              var modal = new bootstrap.Modal(document.getElementById('patientInfoModal'));
              modal.show();
            }, 100);
          });
          Livewire.on('hidePatientInfoModal', () => {
            var modal = bootstrap.Modal.getInstance(document.getElementById('patientInfoModal'));
            if (modal) modal.hide();
          });
        });
      </script>

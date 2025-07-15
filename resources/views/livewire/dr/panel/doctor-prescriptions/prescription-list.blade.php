<div class="doctor-prescriptions-container">
  <div class="container py-2" dir="rtl">
    <div class="glass-header text-white p-2 rounded-2 mb-4 shadow-lg">
      <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3 w-100">
        <div class="d-flex flex-column flex-md-row gap-2 w-100 align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-3">
            <h1 class="m-0 h4 font-thin text-nowrap mb-3 mb-md-0">نسخه‌های من</h1>
          </div>
          <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2">
            <div class="d-flex gap-2 flex-shrink-0 justify-content-center">
              <input type="text"
                class="form-control search-input border-0 shadow-none bg-white text-dark ps-4 rounded-2 text-start"
                wire:model.live="search" placeholder="جستجو در نام بیمار، کد ملی یا کد رهگیری..."
                style="padding-right: 20px; text-align: right; direction: rtl; max-width: 220px;">
              <select class="form-select" wire:model.live="status" style="max-width: 140px;">
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
                  <th>پرداخت</th>
                  <th>وضعیت</th>
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
                      {{ optional($item->patient)->first_name }} {{ optional($item->patient)->last_name }}<br>
                      <span class="text-muted small">{{ optional($item->patient)->national_code }}</span>
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
                    <td>{{ $item->price ? number_format($item->price) . ' تومان' : '-' }}</td>
                    <td>
                      @switch($item->payment_status)
                        @case('pending')
                          <span class="badge bg-warning text-dark">در انتظار</span>
                        @break

                        @case('paid')
                          <span class="badge bg-success">پرداخت شده</span>
                        @break

                        @case('failed')
                          <span class="badge bg-danger">ناموفق</span>
                        @break

                        @default
                          -
                      @endswitch
                    </td>
                    <td>
                      @switch($item->status)
                        @case('pending')
                          <span class="badge bg-secondary">در انتظار</span>
                        @break

                        @case('paid')
                          <span class="badge bg-success">پرداخت شده</span>
                        @break

                        @case('rejected')
                          <span class="badge bg-danger">رد شده</span>
                        @break

                        @case('completed')
                          <span class="badge bg-primary">پایان یافته</span>
                        @break

                        @default
                          -
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
                        @if ($item->status === 'completed') disabled @endif>پاسخ نسخه</button>
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
                    <div class="card prescription-card shadow-sm rounded-3 h-100">
                      <div class="card-body d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                          <div class="fw-bold text-primary">{{ optional($item->patient)->first_name }}
                            {{ optional($item->patient)->last_name }}</div>
                          <span class="badge bg-light text-dark border">{{ $item->tracking_code ?? 'ثبت نشده' }}</span>
                        </div>
                        <div class="mb-1 small text-muted">کد ملی: {{ optional($item->patient)->national_code ?? '-' }}
                        </div>
                        <div class="d-flex flex-wrap gap-2 mb-2">
                          <span class="badge bg-info text-dark">
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
                          </span>
                          <span
                            class="badge bg-light text-dark border">{{ optional($item->prescriptionInsurance)->name ?? '-' }}</span>
                          <span class="badge bg-light text-dark border">{{ optional($item->clinic)->name ?? '-' }}</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mb-2">
                          <span class="badge bg-secondary">
                            @switch($item->status)
                              @case('pending')
                                در انتظار
                              @break

                              @case('paid')
                                پرداخت شده
                              @break

                              @case('rejected')
                                رد شده
                              @break

                              @case('completed')
                                پایان یافته
                              @break

                              @default
                                -
                            @endswitch
                          </span>
                          <span
                            class="badge bg-@if ($item->payment_status == 'paid') success@elseif($item->payment_status == 'pending')warning text-dark@elseif($item->payment_status == 'failed')danger @else light text-dark border @endif">
                            @switch($item->payment_status)
                              @case('pending')
                                در انتظار
                              @break

                              @case('paid')
                                پرداخت شده
                              @break

                              @case('failed')
                                ناموفق
                              @break

                              @default
                                -
                            @endswitch
                          </span>
                        </div>
                        <div class="mb-1 small">مبلغ: <span
                            class="fw-bold">{{ $item->price ? number_format($item->price) . ' تومان' : '-' }}</span>
                        </div>
                        <div class="mb-1 small">تاریخ: <span
                            class="fw-bold">{{ jdate($item->created_at)->format('Y/m/d H:i') }}</span></div>
                        <div class="mb-1 small">توضیحات: <span
                            title="{{ $item->description }}">{{ \Str::limit($item->description, 30) }}</span></div>
                        @if ($item->insulins && $item->insulins->count())
                          <div class="mb-1 small">انسولین‌ها:
                            @foreach ($item->insulins as $insulin)
                              <span class="badge bg-info text-dark mb-1">{{ $insulin->name }}
                                ({{ $insulin->pivot->count }})
                              </span>
                            @endforeach
                          </div>
                        @endif
                        <div class="d-flex justify-content-end mt-2">
                          <button
                            class="btn btn-sm btn-primary w-100 @if ($item->status === 'completed') disabled bg-secondary border-0 @endif"
                            wire:click="editTrackingCode({{ $item->id }})"
                            @if ($item->status === 'completed') disabled @endif>پاسخ نسخه</button>
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
        </div>
        <!-- Modal ثبت کد رهگیری -->
        <div class="modal fade" id="trackingModal" tabindex="-1" aria-labelledby="trackingModalLabel" aria-hidden="true"
          wire:ignore.self>
          <div class="modal-dialog modal-dialog-centered"> <!-- اضافه کردن modal-dialog-centered برای وسط چین شدن عمودی -->
            <div class="modal-content tracking-modal-animate"> <!-- کلاس افکت -->
              <div class="modal-header">
                <h5 class="modal-title" id="trackingModalLabel">ثبت/ویرایش کد رهگیری نسخه</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <input type="text" class="form-control" wire:model.defer="tracking_code" placeholder="کد رهگیری نسخه">
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
                <button type="button" class="btn btn-primary" wire:click="updateTrackingCode">ثبت</button>
              </div>
            </div>
          </div>
        </div>
        <style>
          .tracking-modal-animate {
            animation: trackingModalFadeInScale 0.35s cubic-bezier(.4, 2, .6, 1) both;
          }

          @keyframes trackingModalFadeInScale {
            0% {
              opacity: 0;
              transform: scale(0.8);
            }

            100% {
              opacity: 1;
              transform: scale(1);
            }
          }

          .prescription-card {
            border: 1px solid #e3e3e3;
            transition: box-shadow 0.2s;
          }

          .prescription-card:hover {
            box-shadow: 0 4px 24px 0 rgba(0, 0, 0, 0.08);
          }
        </style>
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
        </script>
      </div>
    </div>

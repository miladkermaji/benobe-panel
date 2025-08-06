<div class="specialty-edit-container">
  <div class="container py-2 mt-3" dir="rtl">
    <div class="glass-header text-white p-2 shadow-lg">
      <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3 w-100">
        <div class="d-flex flex-column flex-md-row gap-2 w-100 align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-3 mb-2">
            <h1 class="m-0 h4 font-thin text-nowrap mb-md-0">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                class="me-2">
                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
              </svg>
              ویرایش تخصص
            </h1>
          </div>
        </div>
      </div>
    </div>

    <div class="container-fluid px-0">
      <div class="card shadow-sm rounded-2">
        <div class="card-body p-4">
          @if ($currentSpecialty)
            <!-- Current Specialty Info -->
            <div class="row mb-4">
              <div class="col-12">
                <div class="alert alert-info">
                  <div class="d-flex align-items-center">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2" class="me-2">
                      <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                      <strong>تخصص فعلی:</strong> {{ $currentSpecialty->name }}
                      @if ($currentSpecialty->description)
                        <br><small class="text-muted">{{ $currentSpecialty->description }}</small>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <form wire:submit="update">
              <!-- Search Section -->
              <div class="row mb-4">
                <div class="col-12">
                  <div class="search-container position-relative">
                    <input type="text" wire:model.live="search"
                      class="form-control search-input border-0 shadow-sm bg-white text-dark ps-4 rounded-2 text-start"
                      placeholder="جستجو در تخصص‌های موجود..."
                      style="padding-right: 20px; text-align: right; direction: rtl;">
                    <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-2"
                      style="z-index: 5; top: 50%; right: 8px;">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                        stroke-width="2">
                        <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
                      </svg>
                    </span>
                  </div>
                </div>
              </div>

              <!-- Available Specialties -->
              <div class="row mb-4">
                <div class="col-12">
                  <h5 class="mb-3">انتخاب تخصص جدید</h5>
                  @if ($availableSpecialties->count() > 0)
                    <div class="row">
                      @foreach ($availableSpecialties as $specialty)
                        <div class="col-md-6 col-lg-4 mb-3">
                          <div class="card border h-100">
                            <div class="card-body d-flex align-items-center">
                              <div class="form-check flex-grow-1">
                                <input class="form-check-input" type="checkbox" wire:model.live="selectedSpecialtyIds"
                                  value="{{ $specialty->id }}" id="specialty_{{ $specialty->id }}">
                                <label class="form-check-label" for="specialty_{{ $specialty->id }}">
                                  <div class="fw-medium">{{ $specialty->name }}</div>
                                  @if ($specialty->description)
                                    <div class="text-muted small">{{ $specialty->description }}</div>
                                  @endif
                                </label>
                              </div>
                            </div>
                          </div>
                        </div>
                      @endforeach
                    </div>
                  @else
                    <div class="text-center py-4">
                      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                        stroke-width="1">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <p class="text-muted mt-2 mb-0">
                        @if ($search)
                          هیچ تخصصی با این جستجو یافت نشد
                        @else
                          تمام تخصص‌های موجود قبلاً اضافه شده‌اند
                        @endif
                      </p>
                    </div>
                  @endif
                </div>
              </div>

              <!-- Selected Count -->
              @if (count($selectedSpecialtyIds) > 0)
                <div class="row mb-4">
                  <div class="col-12">
                    <div class="alert alert-success">
                      <div class="d-flex align-items-center">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2" class="me-2">
                          <path d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ count($selectedSpecialtyIds) }} تخصص انتخاب شده است</span>
                      </div>
                    </div>
                  </div>
                </div>
              @endif

              <!-- Form Actions -->
              <div class="row">
                <div class="col-12">
                  <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('mc.panel.specialties.index') }}" class="btn btn-outline-secondary">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" class="me-1">
                        <path d="M19 12H5M12 19l-7-7 7-7" />
                      </svg>
                      بازگشت
                    </a>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                      <span wire:loading.remove>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2" class="me-1">
                          <path d="M5 13l4 4L19 7" />
                        </svg>
                        به‌روزرسانی تخصص‌ها
                      </span>
                      <span wire:loading>
                        <svg class="spinner-border spinner-border-sm me-1" width="16" height="16"
                          viewBox="0 0 24 24">
                          <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"
                            fill="none" stroke-dasharray="31.416" stroke-dashoffset="31.416">
                            <animate attributeName="stroke-dasharray" dur="2s"
                              values="0 31.416;15.708 15.708;0 31.416" repeatCount="indefinite" />
                            <animate attributeName="stroke-dashoffset" dur="2s" values="0;-15.708;-31.416"
                              repeatCount="indefinite" />
                          </circle>
                        </svg>
                        در حال به‌روزرسانی...
                      </span>
                    </button>
                  </div>
                </div>
              </div>
            </form>
          @else
            <div class="text-center py-4">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                stroke-width="1">
                <path
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
              <p class="text-muted mt-2 mb-0">تخصص مورد نظر یافت نشد</p>
              <a href="{{ route('mc.panel.specialties.index') }}" class="btn btn-outline-secondary mt-3">
                بازگشت به لیست
              </a>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

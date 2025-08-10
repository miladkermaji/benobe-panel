@push('styles')
  <link rel="stylesheet" href="{{ asset('admin-assets/css/panel/story/story.css') }}">
  <link rel="stylesheet" href="{{ asset('admin-assets/panel/css/select2/select2.css') }}">
@endpush

@push('scripts')
  <script src="{{ asset('admin-assets/js/select2/select2.js') }}"></script>
@endpush

<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
    <div class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between gap-3">
      <div class="d-flex align-items-center gap-3 mb-2">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="custom-animate-bounce">
          <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
          <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">ویرایش استوری: {{ $story->title ?? '' }}</h5>
      </div>
      <a href="{{ route('admin.panel.stories.index') }}"
        class="btn btn-outline-light btn-sm rounded-pill px-4 d-flex align-items-center gap-2 hover:shadow-lg transition-all">
        <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        بازگشت
      </a>
    </div>

    <div class="card-body p-4">
      <form wire:submit.prevent="save">
        <div class="row">
          <!-- Basic Information -->
          <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
              <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" class="me-2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z" />
                    <path d="M14 2v6h6" />
                  </svg>
                  اطلاعات اصلی
                </h6>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-12">
                    <div class="position-relative">
                      <input wire:model="title" type="text" class="form-control @error('title') is-invalid @enderror"
                        id="title" placeholder=" " required>
                      <label for="title" class="form-label">عنوان استوری <span class="text-danger">*</span></label>
                      @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>

                  <div class="col-12">
                    <div class="position-relative">
                      <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" id="description"
                        rows="4" placeholder=" "></textarea>
                      <label for="description" class="form-label">توضیحات</label>
                      @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="position-relative">
                      <select wire:model="type" class="form-select @error('type') is-invalid @enderror" id="type"
                        required>
                        <option value="">انتخاب کنید</option>
                        <option value="image">تصویر</option>
                        <option value="video">ویدیو</option>
                      </select>
                      <label for="type" class="form-label">نوع استوری <span class="text-danger">*</span></label>
                      @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="position-relative">
                      <select wire:model="status" class="form-select @error('status') is-invalid @enderror"
                        id="status" required>
                        <option value="">انتخاب کنید</option>
                        <option value="active">فعال</option>
                        <option value="inactive">غیرفعال</option>
                        <option value="pending">در انتظار تأیید</option>
                      </select>
                      <label for="status" class="form-label">وضعیت <span class="text-danger">*</span></label>
                      @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="position-relative">
                      <input wire:model="order" type="number" class="form-control @error('order') is-invalid @enderror"
                        id="order" placeholder=" " min="0">
                      <label for="order" class="form-label">ترتیب نمایش</label>
                      @error('order')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="position-relative">
                      <input wire:model="duration" type="number"
                        class="form-control @error('duration') is-invalid @enderror" id="duration" placeholder=" "
                        min="1">
                      <label for="duration" class="form-label">مدت زمان (ثانیه)</label>
                      @error('duration')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>
                </div>

                <!-- Live Settings -->
                <div class="card mt-4 border-0 bg-light">
                  <div class="card-header bg-transparent border-0">
                    <div class="form-check">
                      <input wire:model="is_live" type="checkbox" class="form-check-input" id="is_live">
                      <label class="form-check-label fw-bold" for="is_live">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2" class="me-2">
                          <circle cx="12" cy="12" r="10" />
                          <path d="M8 14s1.5 2 4 2 4-2 4-2" />
                          <line x1="9" y1="9" x2="9.01" y2="9" />
                          <line x1="15" y1="9" x2="15.01" y2="9" />
                        </svg>
                        تنظیمات لایو
                      </label>
                    </div>
                  </div>
                  <div class="card-body" x-data="{ show: @entangle('is_live') }" x-show="show" x-transition>
                    <div class="row g-3">
                      <div class="col-md-6">
                        <div class="position-relative">
                          <input wire:model="live_start_time" type="text"
                            class="form-control jalali-datepicker text-end @error('live_start_time') is-invalid @enderror"
                            id="live_start_time" placeholder=" " data-jdp>
                          <label for="live_start_time" class="form-label">زمان شروع لایو</label>
                          @error('live_start_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                          @enderror
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="position-relative">
                          <input wire:model="live_end_time" type="text"
                            class="form-control jalali-datepicker text-end @error('live_end_time') is-invalid @enderror"
                            id="live_end_time" placeholder=" " data-jdp>
                          <label for="live_end_time" class="form-label">زمان پایان لایو</label>
                          @error('live_end_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                          @enderror
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="col-lg-4">
            <!-- Current Media Preview -->
            @if ($current_media_path && Storage::disk('public')->exists($current_media_path))
              <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                  <h6 class="mb-0 fw-bold">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2" class="me-2">
                      <path
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    رسانه فعلی
                  </h6>
                </div>
                <div class="card-body">
                  @if ($type === 'image')
                    <img src="{{ Storage::url($current_media_path) }}" class="img-fluid rounded mb-2"
                      alt="رسانه فعلی">
                  @else
                    <video class="img-fluid rounded mb-2" controls preload="none">
                      <source src="{{ Storage::url($current_media_path) }}" type="video/mp4">
                      مرورگر شما از پخش ویدیو پشتیبانی نمی‌کند.
                    </video>
                  @endif
                  <button type="button" wire:click="deleteMedia" class="btn btn-sm btn-outline-danger w-100">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2" class="me-1">
                      <polyline points="3,6 5,6 21,6" />
                      <path d="M19,6v14a2,2 0 0,1 -2,2H7a2,2 0 0,1 -2,-2V6m3,0V4a2,2 0 0,1 2,-2h4a2,2 0 0,1 2,2v2" />
                    </svg>
                    حذف رسانه
                  </button>
                </div>
              </div>
            @endif

            @if ($current_thumbnail_path && Storage::disk('public')->exists($current_thumbnail_path))
              <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                  <h6 class="mb-0 fw-bold">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2" class="me-2">
                      <path
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    تصویر بندانگشتی فعلی
                  </h6>
                </div>
                <div class="card-body">
                  <img src="{{ Storage::url($current_thumbnail_path) }}" class="img-fluid rounded mb-2"
                    alt="تصویر بندانگشتی فعلی">
                  <button type="button" wire:click="deleteThumbnail" class="btn btn-sm btn-outline-danger w-100">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2" class="me-1">
                      <polyline points="3,6 5,6 21,6" />
                      <path d="M19,6v14a2,2 0 0,1 -2,2H7a2,2 0 0,1 -2,-2V6m3,0V4a2,2 0 0,1 2,-2h4a2,2 0 0,1 2,2v2" />
                    </svg>
                    حذف تصویر بندانگشتی
                  </button>
                </div>
              </div>
            @endif

            <!-- Owner Selection -->
            <div class="card border-0 shadow-sm mb-4">
              <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" class="me-2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                  </svg>
                  انتخاب مالک
                </h6>
              </div>
              <div class="card-body">
                <div class="mb-3">
                  <div class="position-relative">
                    <select wire:model.live="owner_type" class="form-select @error('owner_type') is-invalid @enderror"
                      id="owner_type" required>
                      <option value="">انتخاب کنید</option>
                      <option value="user">کاربر</option>
                      <option value="doctor">پزشک</option>
                      <option value="medical_center">مرکز درمانی</option>
                      <option value="manager">مدیر</option>
                    </select>
                    <label for="owner_type" class="form-label">نوع مالک <span class="text-danger">*</span></label>
                    @error('owner_type')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>

                <div x-data="select2Handler()" x-init="init()">
                  <div x-show="$wire.owner_type === 'user'" x-transition>
                    <div class="position-relative">
                      <select wire:model="user_id" class="form-select @error('user_id') is-invalid @enderror"
                        id="user_id" x-ref="userSelect">
                        <option value="">کاربر را انتخاب کنید</option>
                        @if ($selected_owner && $owner_type === 'user')
                          <option value="{{ $selected_owner['id'] }}" selected>{{ $selected_owner['text'] }}</option>
                        @endif
                      </select>
                      <label for="user_id" class="form-label">انتخاب کاربر <span
                          class="text-danger">*</span></label>
                      @error('user_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>

                  <div x-show="$wire.owner_type === 'doctor'" x-transition>
                    <div class="position-relative">
                      <select wire:model="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror"
                        id="doctor_id" x-ref="doctorSelect">
                        <option value="">پزشک را انتخاب کنید</option>
                        @if ($selected_owner && $owner_type === 'doctor')
                          <option value="{{ $selected_owner['id'] }}" selected>{{ $selected_owner['text'] }}</option>
                        @endif
                      </select>
                      <label for="doctor_id" class="form-label">انتخاب پزشک <span
                          class="text-danger">*</span></label>
                      @error('doctor_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>

                  <div x-show="$wire.owner_type === 'medical_center'" x-transition wire:ignore>
                    <div class="position-relative">
                      <select wire:model="medical_center_id"
                        class="form-select @error('medical_center_id') is-invalid @enderror" id="medical_center_id"
                        x-ref="medicalCenterSelect">
                        <option value="">مرکز درمانی را انتخاب کنید</option>
                        @if ($selected_owner && $owner_type === 'medical_center')
                          <option value="{{ $selected_owner['id'] }}" selected>{{ $selected_owner['text'] }}</option>
                        @endif
                      </select>
                      <label for="medical_center_id" class="form-label">انتخاب مرکز درمانی <span
                          class="text-danger">*</span></label>
                      @error('medical_center_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>

                  <div x-show="$wire.owner_type === 'manager'" x-transition>
                    <div class="position-relative">
                      <select wire:model="manager_id" class="form-select @error('manager_id') is-invalid @enderror"
                        id="manager_id" x-ref="managerSelect">
                        <option value="">مدیر را انتخاب کنید</option>
                        @if ($selected_owner && $owner_type === 'manager')
                          <option value="{{ $selected_owner['id'] }}" selected>{{ $selected_owner['text'] }}</option>
                        @endif
                      </select>
                      <label for="manager_id" class="form-label">انتخاب مدیر <span
                          class="text-danger">*</span></label>
                      @error('manager_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                      @enderror
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- File Uploads -->
            <div class="card border-0 shadow-sm">
              <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" class="me-2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="7,10 12,15 17,10" />
                    <line x1="12" y1="15" x2="12" y2="3" />
                  </svg>
                  آپلود فایل‌های جدید
                </h6>
              </div>
              <div class="card-body">
                <div class="mb-3">
                  <div class="position-relative">
                    <input wire:model="media_file" type="file"
                      class="form-control @error('media_file') is-invalid @enderror" id="media_file"
                      accept="{{ $type === 'image' ? 'image/*' : 'video/*' }}" placeholder=" ">
                    <label for="media_file" class="form-label">فایل {{ $type === 'image' ? 'تصویر' : 'ویدیو' }}
                      جدید</label>
                    <div class="form-text">
                      @if ($type === 'image')
                        فرمت‌های مجاز: JPG, PNG, GIF - حداکثر 100MB
                      @else
                        فرمت‌های مجاز: MP4, AVI, MOV - حداکثر 100MB
                      @endif
                    </div>
                    @error('media_file')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>

                <div class="mb-3">
                  <div class="position-relative">
                    <input wire:model="thumbnail_file" type="file"
                      class="form-control @error('thumbnail_file') is-invalid @enderror" id="thumbnail_file"
                      accept="image/*" placeholder=" ">
                    <label for="thumbnail_file" class="form-label">تصویر بندانگشتی جدید</label>
                    <div class="form-text">فرمت‌های مجاز: JPG, PNG - حداکثر 5MB</div>
                    @error('thumbnail_file')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Preview New Uploaded Files -->
        @if ($media_file || $thumbnail_file)
          <div class="row mt-4">
            <div class="col-12">
              <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                  <h6 class="mb-0 fw-bold">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2" class="me-2">
                      <path
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    پیش‌نمایش فایل‌های جدید
                  </h6>
                </div>
                <div class="card-body">
                  <div class="row g-3">
                    @if ($media_file)
                      <div class="col-md-6">
                        <div class="border rounded p-3">
                          <h6 class="fw-bold mb-2">فایل {{ $type === 'image' ? 'تصویر' : 'ویدیو' }} جدید:</h6>
                          <div class="text-center">
                            @if ($type === 'image')
                              @if (method_exists($media_file, 'temporaryUrl') && $media_file->temporaryUrl())
                                <img src="{{ $media_file->temporaryUrl() }}" class="img-fluid rounded mb-2"
                                  alt="پیش‌نمایش تصویر جدید" style="max-height: 200px;">
                              @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                  style="height: 200px; border: 2px dashed #dee2e6;">
                                  <div class="text-center">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                                      stroke="currentColor" stroke-width="1" class="text-muted mb-2">
                                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z" />
                                      <path d="M14 2v6h6" />
                                    </svg>
                                    <div class="text-muted">تصویر انتخاب شده</div>
                                  </div>
                                </div>
                              @endif
                            @else
                              @if (method_exists($media_file, 'temporaryUrl') && $media_file->temporaryUrl())
                                <video class="img-fluid rounded mb-2" controls preload="none"
                                  style="max-height: 200px;">
                                  <source src="{{ $media_file->temporaryUrl() }}" type="video/mp4">
                                  مرورگر شما از پخش ویدیو پشتیبانی نمی‌کند.
                                </video>
                              @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                  style="height: 200px; border: 2px dashed #dee2e6;">
                                  <div class="text-center">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                                      stroke="currentColor" stroke-width="1" class="text-muted mb-2">
                                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z" />
                                      <path d="M14 2v6h6" />
                                    </svg>
                                    <div class="text-muted">ویدیو انتخاب شده</div>
                                  </div>
                                </div>
                              @endif
                            @endif
                            <div class="text-muted small mt-2">
                              نام فایل: {{ $media_file->getClientOriginalName() }}<br>
                              اندازه: {{ number_format($media_file->getSize() / 1024 / 1024, 2) }} MB<br>
                              نوع: {{ $media_file->getMimeType() }}
                            </div>
                          </div>
                        </div>
                      </div>
                    @endif

                    @if ($thumbnail_file)
                      <div class="col-md-6">
                        <div class="border rounded p-3">
                          <h6 class="fw-bold mb-2">تصویر بندانگشتی جدید:</h6>
                          <div class="text-center">
                            @if (method_exists($thumbnail_file, 'temporaryUrl') && $thumbnail_file->temporaryUrl())
                              <img src="{{ $thumbnail_file->temporaryUrl() }}" class="img-fluid rounded mb-2"
                                alt="پیش‌نمایش تصویر بندانگشتی جدید" style="max-height: 200px;">
                            @else
                              <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                style="height: 200px; border: 2px dashed #dee2e6;">
                                <div class="text-center">
                                  <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1" class="text-muted mb-2">
                                    <path
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                  </svg>
                                  <div class="text-muted">تصویر بندانگشتی انتخاب شده</div>
                                </div>
                              </div>
                            @endif
                            <div class="text-muted small mt-2">
                              نام فایل: {{ $thumbnail_file->getClientOriginalName() }}<br>
                              اندازه: {{ number_format($thumbnail_file->getSize() / 1024 / 1024, 2) }} MB<br>
                              نوع: {{ $thumbnail_file->getMimeType() }}
                            </div>
                          </div>
                        </div>
                      </div>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          </div>
        @endif

        <!-- Submit Buttons -->
        <div class="d-flex justify-content-between mt-4">
          <a href="{{ route('admin.panel.stories.index') }}" class="btn btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2" class="me-2">
              <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            بازگشت
          </a>
          <button type="submit" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2" class="me-2">
              <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
              <polyline points="17,21 17,13 7,13 7,21" />
              <polyline points="7,3 7,8 15,8" />
            </svg>
            به‌روزرسانی استوری
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('select2Handler', () => ({
        select2Instances: {},

        init() {
          // Initialize Jalali Datepicker
          jalaliDatepicker.startWatch({
            minDate: "attr",
            maxDate: "attr",
            showTodayBtn: true,
            showEmptyBtn: true,
            time: true,
            zIndex: 1050,
            dateFormatter: function(unix) {
              return new Date(unix).toLocaleDateString('fa-IR', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
              });
            }
          });

          document.getElementById('live_start_time')?.addEventListener('change', function() {
            @this.set('live_start_time', this.value);
          });
          document.getElementById('live_end_time')?.addEventListener('change', function() {
            @this.set('live_end_time', this.value);
          });

          // Watch for owner type changes
          this.$watch('$wire.owner_type', (value) => {
            this.destroyAllSelect2();
            this.$nextTick(() => {
              this.initializeSelect2ForType(value);
            });
          });

          // Initialize Select2 for current owner type
          this.$nextTick(() => {
            this.initializeSelect2ForType(this.$wire.owner_type);
          });
        },

        initializeSelect2ForType(type) {
          if (!type) return;

          const configs = {
            user: {
              ref: 'userSelect',
              url: "{{ route('admin.panel.stories.ajax.users') }}",
              placeholder: 'کاربر را انتخاب کنید',
              model: 'user_id'
            },
            doctor: {
              ref: 'doctorSelect',
              url: "{{ route('admin.panel.stories.ajax.doctors') }}",
              placeholder: 'پزشک را انتخاب کنید',
              model: 'doctor_id'
            },
            medical_center: {
              ref: 'medicalCenterSelect',
              url: "{{ route('admin.panel.stories.ajax.medical-centers') }}",
              placeholder: 'مرکز درمانی را انتخاب کنید',
              model: 'medical_center_id'
            },
            manager: {
              ref: 'managerSelect',
              url: "{{ route('admin.panel.stories.ajax.managers') }}",
              placeholder: 'مدیر را انتخاب کنید',
              model: 'manager_id'
            }
          };

          const config = configs[type];
          if (!config || !this.$refs[config.ref]) return;

          const $element = $(this.$refs[config.ref]);

          // Initialize Select2
          const select2Instance = $element.select2({
            dir: 'rtl',
            placeholder: config.placeholder,
            width: '100%',
            ajax: {
              url: config.url,
              dataType: 'json',
              delay: 250,
              data: function(params) {
                return {
                  search: params.term,
                  page: params.page || 1
                };
              },
              processResults: function(data, params) {
                params.page = params.page || 1;
                return {
                  results: data.results,
                  pagination: {
                    more: data.pagination.more
                  }
                };
              },
              cache: true
            }
          });

          // Handle change event
          $element.on('change', (e) => {
            @this.set(config.model, e.target.value);
          });

          // Store instance
          this.select2Instances[type] = select2Instance;
        },

        destroyAllSelect2() {
          Object.keys(this.select2Instances).forEach(key => {
            if (this.select2Instances[key]) {
              $(this.select2Instances[key]).select2('destroy');
              delete this.select2Instances[key];
            }
          });
        }
      }));
    });

    // Handle alerts
    window.addEventListener('show-alert', event => {
      toastr[event.detail.type](event.detail.message);
    });
  </script>
</div>

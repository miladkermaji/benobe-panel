@push('styles')
    <link rel="stylesheet" href="{{ asset('admin-assets/css/panel/story/story.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/panel/css/select2/select2.css') }}">
@endpush

@push('scripts')
    <!-- لود Livewire قبل از Alpine.js -->
    @livewireScripts
    <script src="{{ asset('admin-assets/js/select2/select2.js') }}"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush

<div class="container-fluid py-4" dir="rtl" x-data="storyForm()">
    <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
        <div class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3 mb-2">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     class="custom-animate-bounce">
                    <path
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h5 class="mb-0 fw-bold text-shadow">ایجاد استوری جدید</h5>
            </div>
            <a href="{{ route('admin.panel.stories.index') }}"
               class="btn btn-outline-light btn-sm rounded-pill px-4 d-flex align-items-center gap-2 hover:shadow-lg transition-all">
                <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2">
                    <path d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                بازگشت
            </a>
        </div>

        <div class="card-body p-4">
            <form wire:submit.prevent="save">
                <div class="row">
                    <!-- اطلاعات اصلی -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 fw-bold">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" class="me-2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/>
                                        <path d="M14 2v6h6"/>
                                    </svg>
                                    اطلاعات اصلی
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="position-relative">
                                            <input wire:model="title" type="text"
                                                   class="form-control @error('title') is-invalid @enderror"
                                                   id="title" placeholder=" " required>
                                            <label for="title" class="form-label">عنوان استوری <span
                                                    class="text-danger">*</span></label>
                                            @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="position-relative">
                                            <textarea wire:model="description"
                                                     class="form-control @error('description') is-invalid @enderror"
                                                     id="description" rows="4" placeholder=" "></textarea>
                                            <label for="description" class="form-label">توضیحات</label>
                                            @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="position-relative">
                                            <select wire:model="type" class="form-select @error('type') is-invalid @enderror"
                                                    id="type" required>
                                                <option value="">انتخاب کنید</option>
                                                <option value="image">تصویر</option>
                                                <option value="video">ویدیو</option>
                                            </select>
                                            <label for="type" class="form-label">نوع استوری <span
                                                    class="text-danger">*</span></label>
                                            @error('type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="position-relative">
                                            <select wire:model="status"
                                                    class="form-select @error('status') is-invalid @enderror"
                                                    id="status" required>
                                                <option value="">انتخاب کنید</option>
                                                <option value="active">فعال</option>
                                                <option value="inactive">غیرفعال</option>
                                                <option value="pending">در انتظار تأیید</option>
                                            </select>
                                            <label for="status" class="form-label">وضعیت <span
                                                    class="text-danger">*</span></label>
                                            @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="position-relative">
                                            <input wire:model="order" type="number"
                                                   class="form-control @error('order') is-invalid @enderror"
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
                                                   class="form-control @error('duration') is-invalid @enderror"
                                                   id="duration" placeholder=" " min="1">
                                            <label for="duration" class="form-label">مدت زمان (ثانیه)</label>
                                            @error('duration')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- تنظیمات لایو -->
                                <div class="card mt-4 border-0 bg-light">
                                    <div class="card-header bg-transparent border-0">
                                        <div class="form-check">
                                            <input wire:model="is_live" type="checkbox" class="form-check-input"
                                                   id="is_live">
                                            <label class="form-check-label fw-bold" for="is_live">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                     stroke="currentColor" stroke-width="2" class="me-2">
                                                    <circle cx="12" cy="12" r="10"/>
                                                    <path d="M8 14s1.5 2 4 2 4-2 4-2"/>
                                                    <line x1="9" y1="9" x2="9.01" y2="9"/>
                                                    <line x1="15" y1="9" x2="15.01" y2="9"/>
                                                </svg>
                                                تنظیمات لایو
                                            </label>
                                        </div>
                                    </div>
                                    <div class="card-body" x-data="{ show: $wire.is_live }" x-show="show" x-transition>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="position-relative">
                                                    <input wire:model="live_start_time" type="text"
                                                           class="form-control jalali-datepicker text-end @error('live_start_time') is-invalid @enderror"
                                                           id="live_start_time" placeholder=" " data-jdp
                                                           x-ref="liveStartTime">
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
                                                           id="live_end_time" placeholder=" " data-jdp
                                                           x-ref="liveEndTime">
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

                    <!-- سایدبار -->
                    <div class="col-lg-4">
                        <!-- انتخاب مالک -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 fw-bold">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" class="me-2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                    انتخاب مالک
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="position-relative">
                                        <select wire:model.live="owner_type"
                                                class="form-select @error('owner_type') is-invalid @enderror"
                                                id="owner_type" required x-on:change="updateOwnerType($event.target.value)">
                                            <option value="">انتخاب کنید</option>
                                            <option value="user">کاربر</option>
                                            <option value="doctor">پزشک</option>
                                            <option value="medical_center">مرکز درمانی</option>
                                            <option value="manager">مدیر</option>
                                        </select>
                                        <label for="owner_type" class="form-label">نوع مالک <span
                                                class="text-danger">*</span></label>
                                        @error('owner_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div x-data="{ ownerType: $wire.owner_type }">
                                    <div x-show="ownerType === 'user'" x-transition>
                                        <div class="position-relative">
                                            <select wire:model.live="user_id"
                                                    class="form-select select2-user @error('user_id') is-invalid @enderror"
                                                    id="user_id" required x-ref="userSelect">
                                                <option value="">کاربر را انتخاب کنید</option>
                                            </select>
                                            <label for="user_id" class="form-label">انتخاب کاربر <span
                                                    class="text-danger">*</span></label>
                                            @error('user_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div x-show="ownerType === 'doctor'" x-transition>
                                        <div class="position-relative">
                                            <select wire:model.live="doctor_id"
                                                    class="form-select select2-doctor @error('doctor_id') is-invalid @enderror"
                                                    id="doctor_id" required x-ref="doctorSelect">
                                                <option value="">پزشک را انتخاب کنید</option>
                                            </select>
                                            <label for="doctor_id" class="form-label">انتخاب پزشک <span
                                                    class="text-danger">*</span></label>
                                            @error('doctor_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div x-show="ownerType === 'medical_center'" x-transition>
                                        <div class="position-relative">
                                            <select wire:model.live="medical_center_id"
                                                    class="form-select select2-medical-center @error('medical_center_id') is-invalid @enderror"
                                                    id="medical_center_id" required x-ref="medicalCenterSelect">
                                                <option value="">مرکز درمانی را انتخاب کنید</option>
                                            </select>
                                            <label for="medical_center_id" class="form-label">انتخاب مرکز درمانی <span
                                                    class="text-danger">*</span></label>
                                            @error('medical_center_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div x-show="ownerType === 'manager'" x-transition>
                                        <div class="position-relative">
                                            <select wire:model.live="manager_id"
                                                    class="form-select select2-manager @error('manager_id') is-invalid @enderror"
                                                    id="manager_id" required x-ref="managerSelect">
                                                <option value="">مدیر را انتخاب کنید</option>
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

                        <!-- آپلود فایل‌ها -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 fw-bold">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2" class="me-2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                        <polyline points="7,10 12,15 17,10"/>
                                        <line x1="12" y1="15" x2="12" y2="3"/>
                                    </svg>
                                    آپلود فایل‌ها
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="position-relative">
                                        <input wire:model="media_file" type="file"
                                               class="form-control @error('media_file') is-invalid @enderror"
                                               id="media_file" accept="{{ $type === 'image' ? 'image/*' : 'video/*' }}"
                                               placeholder=" ">
                                        <label for="media_file" class="form-label">فایل {{ $type === 'image' ? 'تصویر' : 'ویدیو' }}
                                            <span class="text-danger">*</span></label>
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
                                               class="form-control @error('thumbnail_file') is-invalid @enderror"
                                               id="thumbnail_file" accept="image/*" placeholder=" ">
                                        <label for="thumbnail_file" class="form-label">تصویر بندانگشتی</label>
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

                <!-- دکمه‌های ارسال -->
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('admin.panel.stories.index') }}" class="btn btn-secondary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" class="me-2">
                            <path d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        بازگشت
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" class="me-2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17,21 17,13 7,13 7,21"/>
                            <polyline points="7,3 7,8 15,8"/>
                        </svg>
                        ذخیره استوری
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('storyForm', () => ({
                init() {
                    this.initializeDatepickers();
                    this.initializeSelect2();

                    this.$watch('$wire.owner_type', () => {
                        this.reinitializeSelect2();
                    });

                    this.$watch('$wire.user_id', value => {
                        if (this.$refs.userSelect && value) {
                            $(this.$refs.userSelect).val(value).trigger('change');
                        }
                    });

                    this.$watch('$wire.doctor_id', value => {
                        if (this.$refs.doctorSelect && value) {
                            $(this.$refs.doctorSelect).val(value).trigger('change');
                        }
                    });

                    this.$watch('$wire.medical_center_id', value => {
                        if (this.$refs.medicalCenterSelect && value) {
                            $(this.$refs.medicalCenterSelect).val(value).trigger('change');
                        }
                    });

                    this.$watch('$wire.manager_id', value => {
                        if (this.$refs.managerSelect && value) {
                            $(this.$refs.managerSelect).val(value).trigger('change');
                        }
                    });

                    Livewire.on('owner-type-changed', () => {
                        this.reinitializeSelect2();
                    });

                    Livewire.on('show-alert', event => {
                        toastr[event.type](event.message);
                    });
                },

                initializeDatepickers() {
                    jalaliDatepicker.startWatch({
                        minDate: "attr",
                        maxDate: "attr",
                        showTodayBtn: true,
                        showEmptyBtn: true,
                        time: true,
                        zIndex: 1050,
                        dateFormatter: function (unix) {
                            return new Date(unix).toLocaleDateString('fa-IR', {
                                day: 'numeric',
                                month: 'long',
                                year: 'numeric'
                            });
                        }
                    });

                    if (this.$refs.liveStartTime) {
                        this.$refs.liveStartTime.addEventListener('change', () => {
                            this.$wire.set('live_start_time', this.$refs.liveStartTime.value);
                        });
                    }

                    if (this.$refs.liveEndTime) {
                        this.$refs.liveEndTime.addEventListener('change', () => {
                            this.$wire.set('live_end_time', this.$refs.liveEndTime.value);
                        });
                    }
                },

                initializeSelect2() {
                    const select2Options = {
                        dir: 'rtl',
                        width: '100%',
                        ajax: {
                            delay: 250,
                            dataType: 'json',
                            data: params => ({
                                search: params.term,
                                page: params.page || 1
                            }),
                            processResults: (data, params) => {
                                params.page = params.page || 1;
                                return {
                                    results: data.results,
                                    pagination: { more: data.pagination.more }
                                };
                            },
                            cache: true
                        }
                    };

                    if (this.$refs.userSelect && !$(this.$refs.userSelect).hasClass('select2-hidden-accessible')) {
                        $(this.$refs.userSelect).select2({
                            ...select2Options,
                            placeholder: 'کاربر را انتخاب کنید',
                            ajax: { ...select2Options.ajax, url: "{{ route('admin.panel.stories.ajax.users') }}" }
                        }).on('change', () => {
                            this.$wire.set('user_id', $(this.$refs.userSelect).val());
                        });
                    }

                    if (this.$refs.doctorSelect && !$(this.$refs.doctorSelect).hasClass('select2-hidden-accessible')) {
                        $(this.$refs.doctorSelect).select2({
                            ...select2Options,
                            placeholder: 'پزشک را انتخاب کنید',
                            ajax: { ...select2Options.ajax, url: "{{ route('admin.panel.stories.ajax.doctors') }}" }
                        }).on('change', () => {
                            this.$wire.set('doctor_id', $(this.$refs.doctorSelect).val());
                        });
                    }

                    if (this.$refs.medicalCenterSelect && !$(this.$refs.medicalCenterSelect).hasClass('select2-hidden-accessible')) {
                        $(this.$refs.medicalCenterSelect).select2({
                            ...select2Options,
                            placeholder: 'مرکز درمانی را انتخاب کنید',
                            ajax: { ...select2Options.ajax, url: "{{ route('admin.panel.stories.ajax.medical-centers') }}" }
                        }).on('change', () => {
                            this.$wire.set('medical_center_id', $(this.$refs.medicalCenterSelect).val());
                        });
                    }

                    if (this.$refs.managerSelect && !$(this.$refs.managerSelect).hasClass('select2-hidden-accessible')) {
                        $(this.$refs.managerSelect).select2({
                            ...select2Options,
                            placeholder: 'مدیر را انتخاب کنید',
                            ajax: { ...select2Options.ajax, url: "{{ route('admin.panel.stories.ajax.managers') }}" }
                        }).on('change', () => {
                            this.$wire.set('manager_id', $(this.$refs.managerSelect).val());
                        });
                    }
                },

                reinitializeSelect2() {
                    ['userSelect', 'doctorSelect', 'medicalCenterSelect', 'managerSelect'].forEach(ref => {
                        if (this.$refs[ref] && $(this.$refs[ref]).hasClass('select2-hidden-accessible')) {
                            $(this.$refs[ref]).select2('destroy');
                        }
                    });

                    this.$nextTick(() => {
                        this.initializeSelect2();
                        if (this.$wire.owner_type === 'user' && this.$wire.user_id) {
                            $(this.$refs.userSelect).val(this.$wire.user_id).trigger('change');
                        } else if (this.$wire.owner_type === 'doctor' && this.$wire.doctor_id) {
                            $(this.$refs.doctorSelect).val(this.$wire.doctor_id).trigger('change');
                        } else if (this.$wire.owner_type === 'medical_center' && this.$wire.medical_center_id) {
                            $(this.$refs.medicalCenterSelect).val(this.$wire.medical_center_id).trigger('change');
                        } else if (this.$wire.owner_type === 'manager' && this.$wire.manager_id) {
                            $(this.$refs.managerSelect).val(this.$wire.manager_id).trigger('change');
                        }
                    });
                },

                updateOwnerType(value) {
                    this.$wire.set('owner_type', value);
                }
            }));
        });
    </script>
</div>
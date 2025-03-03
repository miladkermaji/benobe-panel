<div>
    <div class="card shadow-sm">
        <div class="card-body p-3">
            <form wire:submit.prevent="create">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="fw-bold mb-1">موبایل: <span class="text-danger">*</span></label>
                        <input type="text" wire:model="mobile" class="form-control input-shiny"
                            placeholder="موبایل ...">
                        @error('mobile') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold mb-1">نام: <span class="text-danger">*</span></label>
                        <input type="text" wire:model="first_name" class="form-control input-shiny"
                            placeholder="نام خود را وارد کنید">
                        @error('first_name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold mb-1">نام خانوادگی: <span class="text-danger">*</span></label>
                        <input type="text" wire:model="last_name" class="form-control input-shiny"
                            placeholder="نام خانوادگی خود را وارد کنید">
                        @error('last_name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold mb-1">شماره پروانه: <span class="text-danger">*</span></label>
                        <input type="text" wire:model="license_number" class="form-control input-shiny"
                            placeholder="شماره پروانه خود را وارد کنید">
                        @error('license_number') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold mb-1">جنسیت: <span class="text-danger">*</span></label>
                        <div wire:ignore>
                            <select id="sex-select" class="form-control">
                                <option value="male">مرد</option>
                                <option value="female">زن</option>
                                <option value="other">سایر</option>
                            </select>
                        </div>
                        @error('sex') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold mb-1">تصویر پرسنلی:</label>
                        <input type="file" wire:model="avatar" class="form-control input-shiny">
                      
                        @error('avatar') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-12">
                        <label class="fw-bold mb-1">درباره من: <span class="text-danger">*</span></label>
                        <textarea wire:model="aboutme" class="form-control input-shiny"
                            style="height: 100px;"></textarea>
                        @error('aboutme') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-12">
                        <label class="fw-bold mb-1">نکات مهم:</label>
                        <textarea wire:model="important_points" class="form-control input-shiny"
                            style="height: 100px;"></textarea>
                        @error('important_points') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold mb-1">تلفن تماس کلینیک:</label>
                        <input type="text" wire:model="clinic_tel" class="form-control input-shiny"
                            placeholder="تلفن تماس">
                        @error('clinic_tel') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold mb-1">آدرس کلینیک: <span class="text-danger">*</span></label>
                        <input type="text" wire:model="clinic_address" class="form-control input-shiny"
                            placeholder="آدرس">
                        @error('clinic_address') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold mb-1">استان کلینیک: <span class="text-danger">*</span></label>
                        <div wire:ignore>
                            <select id="province-select" class="form-control">
                                <option value="">انتخاب استان</option>
                                @foreach ($provinces as $province)
                                    <option value="{{ $province->id }}">{{ $province->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('province_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold mb-1">شهر:</label>
                        <div wire:ignore.self>
                            <select id="city-select" class="form-control">
                                <option value="">-- بدون انتخاب --</option>
                                @foreach ($cities as $city)
                                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('city_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-12">
                        <label class="fw-bold mb-1">انتخاب تخصص‌ها: <span class="text-danger">*</span></label>
                        <div wire:ignore>
                            <select id="specialties-select" multiple class="form-control">
                                @foreach ($specialtiesList as $specialty)
                                    <option value="{{ $specialty->id }}">{{ $specialty->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('specialties') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold mb-1">حالت Security پیشرفته:</label>
                        <div wire:ignore>
                            <select id="security-select" class="form-control">
                                <option value="0">غیرفعال</option>
                                <option value="1">فعال</option>
                            </select>
                        </div>
                        @error('security') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold mb-1">تعرفه ویزیت آزاد (تومان):</label>
                        <input type="number" wire:model="price_doctor_nobat" class="form-control input-shiny" min="0">
                        <small class="text-muted">چنانچه رایگان است، 0 وارد کنید。</small>
                        @error('price_doctor_nobat') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold mb-1">تعرفه حق نوبت سایت (تومان):</label>
                        <input type="number" wire:model="price_per_nobatsite" class="form-control input-shiny" min="0">
                        <small class="text-muted">چنانچه رایگان است، 0 وارد کنید。</small>
                        @error('price_per_nobatsite') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-12">
                        <div class="form-check">
                            <input type="checkbox" wire:model="status_moshavere" class="form-check-input"
                                id="status_moshavere">
                            <label class="form-check-label" for="status_moshavere">آیا مشاوره تلفنی فعال باشد؟</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-check">
                            <input type="checkbox" wire:model="status_nobatdehi" class="form-check-input"
                                id="status_nobatdehi">
                            <label class="form-check-label" for="status_nobatdehi">آیا نوبت‌دهی حضوری فعال باشد؟</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-check">
                            <input type="checkbox" wire:model="send_sms" class="form-check-input" id="send_sms">
                            <label class="form-check-label" for="send_sms">آیا پیامک تغییرات به پزشک ارسال شود؟</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-check">
                            <input type="checkbox" wire:model="auth" class="form-check-input" id="auth">
                            <label class="form-check-label" for="auth">احراز هویت انجام شود؟</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold mb-1">وضعیت: <span class="text-danger">*</span></label>
                        <div wire:ignore>
                            <select id="status-select" class="form-control">
                                <option value="0">مرحله اول ثبت‌نام</option>
                                <option value="1">مرحله انتخاب تخصص</option>
                                <option value="2">مرحله تنظیم برنامه نوبت‌دهی</option>
                                <option value="3">مرحله تنظیم برنامه مشاوره</option>
                                <option value="4">نهایی شده</option>
                            </select>
                        </div>
                        @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-gradient-primary">ثبت و ذخیره</button>
                        <a href="{{ route('admin.doctors.doctors-management.index') }}"
                            class="btn btn-secondary">بازگشت</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // مقداردهی اولیه Select2
            function initializeSelect2() {
                $('#province-select').select2({
                    dir: 'rtl',
                    placeholder: 'انتخاب استان',
                    allowClear: true
                }).val("{{ $province_id ?? '' }}").trigger('change');

                $('#city-select').select2({
                    dir: 'rtl',
                    placeholder: 'انتخاب شهر',
                    allowClear: true
                }).val("{{ $city_id ?? '' }}").trigger('change');

                $('#sex-select').select2({
                    dir: 'rtl',
                    placeholder: 'انتخاب جنسیت',
                    allowClear: true
                }).val("{{ $sex ?? '' }}").trigger('change');

                $('#specialties-select').select2({
                    dir: 'rtl',
                    placeholder: 'تخصص‌ها را انتخاب کنید',
                    allowClear: true
                }).val(@json($specialties ?? [])).trigger('change');

                $('#security-select').select2({
                    dir: 'rtl',
                    placeholder: 'انتخاب حالت',
                    allowClear: true
                }).val("{{ $security ?? '' }}").trigger('change');

                $('#status-select').select2({
                    dir: 'rtl',
                    placeholder: 'انتخاب وضعیت',
                    allowClear: true
                }).val("{{ $status ?? '' }}").trigger('change');
            }

            // بارگذاری اولیه
            initializeSelect2();

            // همگام‌سازی انتخاب‌ها با Livewire
            let isUpdating = false;

            $('#province-select').on('change', function () {
                if (!isUpdating) {
                    isUpdating = true;
                    const provinceId = $(this).val();
                    @this.set('province_id', provinceId).then(() => {
                        // بازسازی Select2 برای شهرها و استان بعد از آپدیت
                        $('#city-select').select2({
                            dir: 'rtl',
                            placeholder: 'انتخاب شهر',
                            allowClear: true
                        }).val(null).trigger('change');

                        $('#province-select').select2({
                            dir: 'rtl',
                            placeholder: 'انتخاب استان',
                            allowClear: true
                        }).val(provinceId).trigger('change');

                        isUpdating = false;
                    });
                }
            });

            $('#city-select').on('change', function () {
                if (!isUpdating) {
                    isUpdating = true;
                    @this.set('city_id', $(this).val()).then(() => {
                        isUpdating = false;
                    });
                }
            });

            $('#sex-select').on('change', function () {
                if (!isUpdating) {
                    isUpdating = true;
                    @this.set('sex', $(this).val()).then(() => {
                        isUpdating = false;
                    });
                }
            });

            $('#specialties-select').on('change', function () {
                if (!isUpdating) {
                    isUpdating = true;
                    @this.set('specialties', $(this).val()).then(() => {
                        isUpdating = false;
                    });
                }
            });

            $('#security-select').on('change', function () {
                if (!isUpdating) {
                    isUpdating = true;
                    @this.set('security', $(this).val()).then(() => {
                        isUpdating = false;
                    });
                }
            });

            $('#status-select').on('change', function () {
                if (!isUpdating) {
                    isUpdating = true;
                    @this.set('status', $(this).val()).then(() => {
                        isUpdating = false;
                    });
                }
            });

            // نمایش توستر
            Livewire.on('toast', (event) => {
                const data = event[0];
                const { message, type } = data;
                toastr[type || 'info'](message, '', {
                    positionClass: 'toast-top-right',
                    timeOut: 3000,
                    progressBar: false,
                });
            });
        });
    </script>

    <style>
        .glass-header {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.9), rgba(124, 58, 237, 0.7));
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        .glass-header:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .input-shiny {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #fff;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
            height: 40px;
        }

        .input-shiny:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
        }

        .btn-gradient-primary {
            background: linear-gradient(90deg, #4f46e5, #7c3aed);
            border: none;
            color: white;
        }

        .btn-gradient-primary:hover {
            background: linear-gradient(90deg, #4338ca, #6d28d9);
            transform: translateY(-1px);
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-selection {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 8px;
            font-size: 14px;
            height: 40px;
            display: flex;
            align-items: center;
        }

        .select2-selection__rendered {
            line-height: 24px !important;
        }

        .select2-selection--multiple {
            height: auto !important;
            min-height: 40px;
        }

        .select2-selection__choice {
            margin-top: 4px !important;
        }
    </style>
</div>
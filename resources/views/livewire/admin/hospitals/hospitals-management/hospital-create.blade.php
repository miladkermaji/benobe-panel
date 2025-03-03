<div class="container-fluid py-1">
    <header class="glass-header p-3 rounded-3 mb-2 shadow-lg">
        <h4 class="mb-0 fw-bold text-white">افزودن بیمارستان جدید</h4>
    </header>

    <div class="card shadow-sm">
        <div class="card-body p-3">
            <form wire:submit.prevent="save">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="control-label fw-bold mb-1 fs-6">نام مسئول (پزشک)</label>
                        <div wire:ignore>
                            <select id="doctor-select"
                                class="form-control select2 @error('doctor_id') is-invalid @enderror">
                                <option value="">انتخاب کنید...</option>
                                @foreach ($doctors as $doctor)
                                    <option value="{{ $doctor->id }}">{{ $doctor->first_name . ' ' . $doctor->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('doctor_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="control-label fw-bold mb-1 fs-6">نام بیمارستان</label>
                        <input type="text" wire:model="name"
                            class="form-control input-shiny @error('name') is-invalid @enderror"
                            placeholder="نام بیمارستان">
                        @error('name')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="control-label fw-bold mb-1 fs-6">شماره تماس</label>
                        <input type="text" wire:model="phone_number"
                            class="form-control input-shiny @error('phone_number') is-invalid @enderror"
                            placeholder="شماره تماس">
                        @error('phone_number')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="control-label fw-bold mb-1 fs-6">استان</label>
                        <div wire:ignore>
                            <select id="province-select"
                                class="form-control select2 @error('province_id') is-invalid @enderror">
                                <option value="">انتخاب کنید...</option>
                                @foreach ($provinces as $province)
                                    <option value="{{ $province->id }}">{{ $province->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('province_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="control-label fw-bold mb-1 fs-6">شهر</label>
                        <div wire:ignore.self>
                            <select id="city-select"
                                class="form-control select2 @error('city_id') is-invalid @enderror">
                                <option value="">ابتدا استان را انتخاب کنید</option>
                                @foreach ($cities as $city)
                                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('city_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-12">
                        <label class="control-label fw-bold mb-1 fs-6">آدرس</label>
                        <textarea wire:model="address"
                            class="form-control input-shiny @error('address') is-invalid @enderror"
                            placeholder="آدرس بیمارستان"></textarea>
                        @error('address')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">ذخیره</button>
                        <a href="{{ route('admin.content.hospitals.hospitals-management.index') }}"
                            class="btn btn-secondary">بازگشت</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <style>
        .input-shiny {
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .input-shiny:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
        }

        .is-invalid {
            border-color: #dc3545;
        }

        .text-danger {
            color: #dc3545;
            font-size: 0.875rem;
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
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // مقداردهی اولیه Select2
            function initializeSelect2() {
                $('#doctor-select').select2({
                    dir: 'rtl',
                    placeholder: 'انتخاب کنید...'
                }).val("{{ $doctor_id ?? '' }}").trigger('change');

                $('#province-select').select2({
                    dir: 'rtl',
                    placeholder: 'انتخاب کنید...'
                }).val("{{ $province_id ?? '' }}").trigger('change');

                $('#city-select').select2({
                    dir: 'rtl',
                    placeholder: 'انتخاب کنید...'
                }).val("{{ $city_id ?? '' }}").trigger('change');
            }

            initializeSelect2();

            // همگام‌سازی با Livewire
            let isUpdating = false;

            $('#doctor-select').on('change', function () {
                if (!isUpdating) {
                    isUpdating = true;
                    @this.set('doctor_id', $(this).val()).then(() => {
                        isUpdating = false;
                    });
                }
            });

            $('#province-select').on('change', function () {
                if (!isUpdating) {
                    isUpdating = true;
                    const provinceId = $(this).val();
                    @this.set('province_id', provinceId).then(() => {
                        $('#city-select').select2({
                            dir: 'rtl',
                            placeholder: 'انتخاب کنید...'
                        }).val(null).trigger('change');
                        $('#province-select').select2({
                            dir: 'rtl',
                            placeholder: 'انتخاب کنید...'
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

            // آپدیت Select2 بعد از رندر Livewire
            document.addEventListener('livewire:updated', () => {
                $('#doctor-select').select2({
                    dir: 'rtl',
                    placeholder: 'انتخاب کنید...'
                }).val(@this.doctor_id).trigger('change');

                $('#province-select').select2({
                    dir: 'rtl',
                    placeholder: 'انتخاب کنید...'
                }).val(@this.province_id).trigger('change');

                $('#city-select').select2({
                    dir: 'rtl',
                    placeholder: 'انتخاب کنید...'
                }).val(@this.city_id).trigger('change');
            });

            // نمایش توستر
            Livewire.on('toast', (event) => {
                const data = event[0];
                const { message, type } = data;
                toastr[type === 'success' ? 'success' : 'error'](message, '', {
                    positionClass: 'toast-top-right',
                    timeOut: 3000
                });
            });
        });
    </script>
</div>
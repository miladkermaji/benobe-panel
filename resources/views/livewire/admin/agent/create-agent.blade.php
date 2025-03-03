<div class="container-fluid py-4">
    <!-- هدر اصلی -->
    <div class="bg-light text-dark p-4 rounded-top border">
        <div class="d-flex align-items-center">
            <i class="fas fa-user-plus me-3"></i>
            <h5 class="mb-0 fw-bold">افزودن نماینده</h5>
        </div>
    </div>

    <!-- بدنه اصلی -->
    <div class="bg-white p-4 rounded-bottom shadow-sm">
        <form wire:submit.prevent="save">
            <div class="row g-4">
                <!-- نام و نام خانوادگی -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label fw-bold">نام و نام خانوادگی</label>
                        <input type="text" class="form-control" wire:model="full_name">
                        @error('full_name') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- موبایل -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label fw-bold">موبایل</label>
                        <input type="text" class="form-control" wire:model="mobile">
                        @error('mobile') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- کد ملی -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label fw-bold">کد ملی</label>
                        <input type="text" class="form-control" wire:model="national_code">
                        @error('national_code') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- استان -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label fw-bold">استان</label>
                        <select class="form-control" id="province-select" wire:model.live="province_id">
                            <option value="">انتخاب استان</option>
                            @foreach ($provinces as $province)
                                <option value="{{ $province['id'] }}" {{ $province_id == $province['id'] ? 'selected' : '' }}>
                                    {{ $province['name'] }}</option>
                            @endforeach
                        </select>
                        @error('province_id') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- شهر -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label fw-bold">شهر</label>
                        <select class="form-control" id="city-select" wire:model="city_id">
                            <option value="">انتخاب شهر</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city['id'] }}" {{ $city_id == $city['id'] ? 'selected' : '' }}>
                                    {{ $city['name'] }}</option>
                            @endforeach
                        </select>
                        @error('city_id') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- وضعیت -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label fw-bold">وضعیت</label>
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" wire:model="status" @checked($status)>
                            <label class="form-check-label">{{ $status ? 'فعال' : 'غیرفعال' }}</label>
                        </div>
                        @error('status') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- دکمه‌ها -->
            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('admin.agent.agent') }}" class="btn btn-outline-warning">
                    <i class="fas fa-arrow-right me-2"></i> بازگشت
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i> ثبت
                </button>
            </div>
        </form>
    </div>

    <!-- استایل‌ها -->
    <style>
        .bg-light {
            background-color: #f8f9fa !important;
        }

        .border {
            border-color: #dee2e6 !important;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .btn {
            border-radius: 0.375rem;
            padding: 0.75rem 1.5rem;
        }

        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }

        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }

        .btn-outline-warning {
            color: #ffc107;
            border-color: #ffc107;
        }

        .btn-outline-warning:hover {
            background-color: #ffc107;
            color: #fff;
        }

        .form-switch .form-check-input {
            width: 2.5em;
            height: 1.25em;
        }
    </style>

    <!-- اسکریپت TomSelect -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            const provinceSelect = new TomSelect('#province-select', {
                create: false,
                sortField: 'text',
                placeholder: 'انتخاب استان',
                valueField: 'value',
                labelField: 'text',
                searchField: ['text'],
                options: [
                    { value: '', text: 'انتخاب استان' },
                    @foreach ($provinces as $province)
                        { value: '{{ $province['id'] }}', text: '{{ $province['name'] }}' },
                    @endforeach
                ],
                onChange: function (value) {
                    @this.set('province_id', value);
                },
                onInitialize: function () {
                    this.setValue('{{ $province_id ?? '' }}');
                }
            });

            const citySelect = new TomSelect('#city-select', {
                create: false,
                sortField: 'text',
                placeholder: 'انتخاب شهر',
                valueField: 'value',
                labelField: 'text',
                searchField: ['text'],
                options: [
                    { value: '', text: 'انتخاب شهر' }
                ],
                onChange: function (value) {
                    @this.set('city_id', value);
                },
                onInitialize: function () {
                    this.setValue('{{ $city_id ?? '' }}');
                }
            });

            // آپدیت شهرها
            document.addEventListener('livewire:updated', function () {
                const cities = [
                    { value: '', text: 'انتخاب شهر' },
                    @foreach ($cities as $city)
                        { value: '{{ $city['id'] }}', text: '{{ $city['name'] }}' },
                    @endforeach
                ];
                citySelect.clearOptions();
                citySelect.addOptions(cities);
                citySelect.setValue('{{ $city_id ?? '' }}');
            });
        });
           document.addEventListener('livewire:initialized', () => {
                Livewire.on('toast', (message, options = {}) => {
                    if (typeof toastr === 'undefined') {
                        console.error('Toastr is not loaded!');
                        return;
                    }
                    const type = options.type || 'info';
                    if (type === 'success') {
                        toastr.success(message, '', {
                            positionClass: options.position || 'toast-top-right',
                            timeOut: options.timeOut || 3000,
                            progressBar: options.progressBar || false,
                        });
                    } else if (type === 'error') {
                        toastr.error(message, '', {
                            positionClass: options.position || 'toast-top-right',
                            timeOut: options.timeOut || 3000,
                            progressBar: options.progressBar || false,
                        });
                    } else {
                        toastr.info(message, '', {
                            positionClass: options.position || 'toast-top-right',
                            timeOut: options.timeOut || 3000,
                            progressBar: options.progressBar || false,
                        });
                    }
                });
            });
    </script>
</div>
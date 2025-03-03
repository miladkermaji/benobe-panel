<div class="container-fluid py-4">
    <!-- هدر اصلی -->
    <div class="bg-light text-dark p-4 rounded-top border">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="fas fa-image me-3"></i>
                <h5 class="mb-0 fw-bold">تغییر لوگوی سایت</h5>
            </div>
            <a href="{{ route('admin.Dashboard.setting.index') }}" class="btn btn-outline-primary">
                <i class="fa fa-arrow-right me-2"></i> بازگشت
            </a>
        </div>
    </div>

    <!-- بدنه اصلی -->
    <div class="bg-white p-4 rounded-bottom shadow-sm">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="p-3 border rounded bg-light">
                    <label class="form-label fw-bold text-dark mb-2">انتخاب تصویر لوگو</label>
                    <input type="file" class="form-control" wire:model="logo" accept="image/png,image/jpg,image/jpeg">
                    <small class="text-muted mt-2 d-block">ابعاد فایل باید 200x53 پیکسل باشد. فرمت‌های مجاز: PNG, JPG,
                        JPEG.</small>

                    @if ($currentLogo)
                        <div class="mt-3 text-center">
                            <img src="{{ Storage::url($currentLogo->path) }}" class="img-thumbnail" width="200"
                                alt="لوگوی فعلی">
                            <p class="text-muted mt-2">لوگوی فعلی</p>
                        </div>
                    @endif

                    <div class="mt-4 text-end">
                        <button wire:click="saveLogo" class="btn btn-primary">
                            <i class="fa fa-upload me-2"></i> ذخیره لوگو
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- استایل‌ها -->
    <style>
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .bg-light {
            background-color: #f8f9fa !important;
        }

        .border {
            border-color: #dee2e6 !important;
        }
    </style>
    <script>
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
                } else if (type === 'warning') {
                    toastr.warning(message, '', {
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
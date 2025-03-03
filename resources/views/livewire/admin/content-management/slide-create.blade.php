<div class="container-fluid py-4">
    <!-- هدر اصلی -->
    <div class="bg-light text-dark p-4 rounded-top border">
        <div class="d-flex align-items-center">
            <i class="fas fa-plus me-3"></i>
            <h5 class="mb-0 fw-bold">افزودن اسلایدر</h5>
        </div>
    </div>

    <!-- بدنه اصلی -->
    <div class="bg-white p-4 rounded-bottom shadow-sm">
        <form wire:submit.prevent="save" enctype="multipart/form-data">
            <div class="row g-4">
                <!-- عنوان اسلاید -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label fw-bold">عنوان اسلاید</label>
                        <input type="text" class="form-control" wire:model="title">
                        @error('title') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- تصویر -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label fw-bold">تصویر اسلایدر</label>
                        <input type="file" class="form-control" wire:model="image" accept="image/*">
                        @error('image') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- لینک -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label fw-bold">لینک نمایش</label>
                        <input type="text" class="form-control" wire:model="link">
                        @error('link') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- توضیحات -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label fw-bold">توضیحات</label>
                        <textarea class="form-control" wire:model="description" rows="3"></textarea>
                        @error('description') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- نمایش -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label fw-bold">قابل نمایش در</label>
                        <select class="form-control" wire:model="display">
                            <option value="site">سایت</option>
                            <option value="mobile">موبایل</option>
                        </select>
                        @error('display') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
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
                <a href="{{ route('admin.content-management.slide.index') }}" class="btn btn-outline-warning">
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
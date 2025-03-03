<div class="container-fluid py-4">
    <!-- هدر اصلی -->
    <div class="bg-light text-dark p-4 rounded-top border">
        <div class="d-flex align-items-center">
            <i class="fas fa-plus me-3"></i>
            <h5 class="mb-0 fw-bold">افزودن خبر</h5>
        </div>
    </div>

    <!-- بدنه اصلی -->
    <div class="bg-white p-4 rounded-bottom shadow-sm">
        <form wire:submit.prevent="save" enctype="multipart/form-data">
            <div class="row g-4">
                <!-- عنوان خبر -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label fw-bold">عنوان خبر</label>
                        <input type="text" class="form-control" wire:model="title">
                        @error('title') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- دسته‌بندی -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label fw-bold">دسته‌بندی</label>
                        <select class="form-control" wire:model="category_id">
                            <option value="">انتخاب کنید</option>
                            @foreach($categories as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- تاریخ -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label fw-bold">📅 تاریخ</label>
                        <input type="text" id="date-picker" class="form-control text-center" wire:model="selectedDate"
                            placeholder="مثلاً ۱۴۰۳/۰۱/۰۱">
                        @error('selectedDate') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- تصویر -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label fw-bold">تصویر خبر</label>
                        <input type="file" class="form-control" wire:model="image" accept="image/*">
                        @error('image') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- توضیح کوتاه -->
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="form-label fw-bold">توضیح کوتاه</label>
                        <textarea class="form-control" wire:model="short_description" rows="3"></textarea>
                        @error('short_description') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- متن خبر -->
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="form-label fw-bold">متن خبر</label>
                        <textarea class="form-control" wire:model="content" rows="10"></textarea>
                        @error('content') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- انتشار در صفحه اصلی -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label fw-bold">انتشار در صفحه اصلی</label>
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" wire:model="is_index" @checked($is_index)>
                            <label class="form-check-label">{{ $is_index ? 'بله' : 'خیر' }}</label>
                        </div>
                        @error('is_index') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
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

                <!-- Page Title -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label fw-bold">Page Title</label>
                        <input type="text" class="form-control" wire:model="page_title">
                        @error('page_title') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- URL SEO -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label fw-bold">URL SEO</label>
                        <input type="text" class="form-control" wire:model="url_seo">
                        @error('url_seo') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Meta Description -->
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="form-label fw-bold">Meta Description</label>
                        <textarea class="form-control" wire:model="meta_description" rows="3"></textarea>
                        @error('meta_description') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- دکمه‌ها -->
            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('admin.content-management.blog.index') }}" class="btn btn-outline-warning">
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
    document.addEventListener("DOMContentLoaded", function () {
        // مقدار initialDate رو از یه متغیر Blade که از کامپوننت میاد می‌گیریم
        const initialDate = @json($selectedDate ?? '');

        flatpickr("#date-picker", {
            dateFormat: "Y/m/d",
            locale: "fa", // استفاده از لوکال فارسی
            defaultDate: initialDate,
            onChange: function (selectedDates, dateStr) {
                // به جای @this مستقیماً از emit استفاده می‌کنیم
                Livewire.dispatch('updateSelectedDate', { date: dateStr });
            }
        });

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
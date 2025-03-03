<div class="container-fluid py-4">
    <!-- هدر اصلی -->
    <div class="bg-light text-dark p-4 rounded-top border">
        <div class="d-flex align-items-center">
            <i class="fas fa-bars me-3"></i>
            <h5 class="mb-0 fw-bold">افزودن منو</h5>
        </div>
    </div>

    <!-- بدنه اصلی -->
    <div class="bg-white p-4 rounded-bottom shadow-sm">
        <!-- پیام موفقیت -->
        @if ($successMessage)
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                {{ $successMessage }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form wire:submit.prevent="store">
            <div class="row g-4">
                <!-- نام منو -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label fw-bold">نام منو</label>
                        <input type="text" class="form-control" wire:model="name">
                        @error('name') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- لینک منو -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label fw-bold">لینک منو</label>
                        <input type="text" class="form-control" wire:model="url">
                        @error('url') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- آیکون -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label fw-bold">آیکون</label>
                        <input type="file" class="form-control" wire:model="icon" accept="image/*">
                        @error('icon') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- جایگاه -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label fw-bold">جایگاه</label>
                        <select class="form-select" wire:model="position">
                            <option value="top">بالا</option>
                            <option value="bottom">پایین</option>
                            <option value="top_bottom">بالا و پایین</option>
                        </select>
                        @error('position') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- زیرمجموعه -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label fw-bold">زیرمجموعه</label>
                        <select class="form-select" wire:model="parent_id">
                            <option value="">[دسته اصلی]</option>
                            @foreach ($menus as $menu)
                                <option value="{{ $menu->id }}">{{ $menu->name }}</option>
                            @endforeach
                        </select>
                        @error('parent_id') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- ترتیب -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label fw-bold">ترتیب</label>
                        <input type="number" class="form-control" wire:model="order">
                        @error('order') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- وضعیت -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label fw-bold">وضعیت</label>
                        <select class="form-select" wire:model="status">
                            <option value="1">فعال</option>
                            <option value="0">غیرفعال</option>
                        </select>
                        @error('status') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- دکمه‌ها -->
            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('admin.Dashboard.menu.index') }}" class="btn btn-outline-warning">
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
        .panel-default {
            border: none;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    
        .panel-heading {
            background: #f8f9fa;
            padding: 1.25rem;
            font-size: 1.25rem;
            font-weight: bold;
            border-bottom: 1px solid #dee2e6;
        }
    
        .panel-body {
            padding: 2rem;
        }
    
        .form-label {
            color: #495057;
        }
    
        .form-control,
        .form-select {
            border-radius: 0.375rem;
            padding: 0.75rem 1rem;
            border: 1px solid #ced4da;
            transition: border-color 0.3s ease;
        }
    
        .form-control:focus,
        .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
    
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            transition: all 0.3s ease;
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
    
        .alert-success {
            border-radius: 0.375rem;
        }
    </style>
    
    <script>
        document.addEventListener('livewire:load', function () {
            Livewire.on('menuAdded', () => {
                setTimeout(() => {
                    document.querySelector('.alert-success')?.remove();
                }, 5000); // حذف بعد از 5 ثانیه
            });
        });
    </script>
</div>


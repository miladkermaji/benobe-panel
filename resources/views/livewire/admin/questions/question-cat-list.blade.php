<div class="container-fluid py-2">
    <!-- هدر -->
    <header class="glass-header p-4 rounded-3 mb-2 shadow-lg">
        <div class="d-flex align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <i class="fas fa-list fs-4 text-white animate-bounce"></i>
                <h4 class="mb-0 fw-bold text-white">لیست دسته‌بندی‌های پرسش و پاسخ</h4>
            </div>
        </div>
    </header>

    <!-- جستجو و فیلتر -->
    <div class="container px-0 mb-2">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 bg-light p-3 rounded-3 shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input shadow-sm" wire:model.live="selectAll" id="selectAll">
                    <label class="form-check-label fw-medium text-dark" for="selectAll">انتخاب همه</label>
                </div>
                <div class="input-group" style="max-width: 350px;">
                    <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" class="form-control border-0 shadow-none" wire:model.live="search" placeholder="جستجو در نام دسته‌بندی">
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <select wire:model.live="perPage" class="form-select border-0 shadow-sm" style="width: 100px;">
                    <option value="7">7</option>
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                </select>
                <button onclick="confirmDeleteSelected()" 
                        class="btn btn-gradient-danger rounded-pill px-4 py-2 d-flex align-items-center gap-2" 
                        @if(empty($selectedCategories)) disabled @endif>
                    <i class="fas fa-trash fs-6"></i> حذف انتخاب‌شده‌ها
                </button>
                <a href="{{ route('admin.questions.question-cat.create') }}" 
                   class="btn btn-gradient-success rounded-pill px-4 py-2 d-flex align-items-center gap-2">
                    <i class="fas fa-plus fs-6"></i> افزودن دسته‌بندی
                </a>
            </div>
        </div>
    </div>

    <!-- لیست دسته‌بندی‌ها -->
    <div class="container px-0">
        <div class="row g-3">
            @forelse ($categories as $index => $category)
                <div class="col-12">
                    <div class="card comment-card border-0 rounded-3 shadow-sm bg-gradient-card">
                        <div class="card-body p-4 d-flex align-items-center justify-content-between gap-3 flex-wrap">
                            <div class="d-flex align-items-center gap-3">
                                <input type="checkbox" class="form-check-input flex-shrink-0 shadow-sm" 
                                    wire:model.live="selectedCategories" value="{{ $category->id }}">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-semibold text-dark">{{ $category->name }}</h6>
                                    <small class="text-muted d-block">{{ $category->alt_name ?? 'بدون نام جایگزین' }}</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input shadow-sm" 
                                        wire:model="categoryStatuses.{{ $category->id }}" 
                                        wire:click="toggleStatus({{ $category->id }})" 
                                        id="status-{{ $category->id }}"
                                        {{ $categoryStatuses[$category->id] ? 'checked' : '' }}>
                                    <label class="form-check-label text-muted fw-medium mx-2" for="status-{{ $category->id }}">
                                        {{ $categoryStatuses[$category->id] ? 'فعال' : 'غیرفعال' }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-list fs-1 text-muted mb-3"></i>
                        <p class="text-muted fw-medium">هیچ دسته‌بندی یافت نشد.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- صفحه‌بندی -->
        <div class="mt-5 d-flex justify-content-center">
            {{ $categories->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <style>
        .glass-header {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.85), rgba(124, 58, 237, 0.65));
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        .glass-header:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .bg-gradient-card {
            background: linear-gradient(145deg, #ffffff, #f9fafb);
            border: 1px solid #e5e7eb;
        }

        .comment-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .comment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .btn-gradient-danger {
            background: linear-gradient(90deg, #f87171, #fca5a5);
            border: none;
            color: white;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .btn-gradient-danger:hover:not(:disabled) {
            background: linear-gradient(90deg, #ef4444, #f87171);
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-gradient-danger:disabled {
            background: #d1d5db;
            cursor: not-allowed;
            box-shadow: none;
        }

        .btn-gradient-success {
            background: linear-gradient(90deg, #10b981, #34d399);
            border: none;
            color: white;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .btn-gradient-success:hover {
            background: linear-gradient(90deg, #059669, #10b981);
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(0, 0, 0, 0.15);
        }

        .form-check-input:checked {
            background-color: #10b981;
            border-color: #10b981;
        }

        .form-check-input {
            cursor: pointer;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
        }

        .text-ellipsis {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .pagination .page-link {
            border-radius: 6px;
            margin: 0 3px;
            transition: all 0.3s ease;
        }

        .pagination .page-item.active .page-link {
            background: #4f46e5;
            border-color: #4f46e5;
            color: white;
        }

        .pagination .page-link:hover {
            background: #e5e7eb;
            color: #4f46e5;
        }

        .animate-bounce {
            animation: bounce 1s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
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
                const toastOptions = {
                    positionClass: options.position || 'toast-top-right',
                    timeOut: options.timeOut || 3000,
                    progressBar: options.progressBar || false,
                };
                if (type === 'success') toastr.success(message, '', toastOptions);
                else if (type === 'error') toastr.error(message, '', toastOptions);
                else toastr.info(message, '', toastOptions);
            });

            // اعمال استایل‌ها بعد از لود صفحه
            setTimeout(() => {
                document.querySelectorAll('.form-check-input').forEach(checkbox => {
                    checkbox.dispatchEvent(new Event('change'));
                });
            }, 100); // یه تأخیر کوچک برای اطمینان از لود کامل DOM
        });

        function confirmDeleteSelected() {
            Swal.fire({
                title: 'آیا مطمئن هستید؟',
                text: 'دسته‌بندی‌های انتخاب‌شده حذف خواهند شد و قابل بازگشت نیستند!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#d1d5db',
                confirmButtonText: 'بله، حذف کن',
                cancelButtonText: 'خیر',
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('deleteSelected');
                }
            });
        }
    </script>
</div>
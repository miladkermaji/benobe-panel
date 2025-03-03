<div class="container-fluid py-1">
    <!-- هدر -->
    <header class="glass-header p-4 rounded-3 mb-2 shadow-lg">
        <div class="d-flex align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <i class="fas fa-comment fs-4 text-white animate-bounce"></i>
                <h4 class="mb-0 fw-bold text-white">نمایش و ویرایش نظر</h4>
            </div>
        </div>
    </header>

    <div class="container px-0">
        <!-- اطلاعات نظر -->
        <div class="card border-0 rounded-3 shadow-md mb-2 bg-gradient-card">
            <div class="card-body p-5">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="fw-semibold text-dark mb-2 d-flex align-items-center gap-2">
                            <i class="fas fa-calendar-alt text-muted"></i> تاریخ ارسال
                        </label>
                        <p class="text-muted mb-0 bg-white p-2 rounded-2 shadow-sm">{{ $persianDate }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-semibold text-dark mb-2 d-flex align-items-center gap-2">
                            <i class="fas fa-user text-muted"></i> نام
                        </label>
                        <p class="text-muted mb-0 bg-white p-2 rounded-2 shadow-sm">{{ $name ?? 'ناشناس' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-semibold text-dark mb-2 d-flex align-items-center gap-2">
                            <i class="fas fa-envelope text-muted"></i> ایمیل
                        </label>
                        <p class="text-muted mb-0 bg-white p-2 rounded-2 shadow-sm">{{ $email ?? 'نامشخص' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-semibold text-dark mb-2 d-flex align-items-center gap-2">
                            <i class="fas fa-network-wired text-muted"></i> IP
                        </label>
                        <p class="text-muted mb-0 bg-white p-2 rounded-2 shadow-sm">{{ $ip ?? 'نامشخص' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-semibold text-dark mb-2 d-flex align-items-center gap-2">
                            <i class="fas fa-toggle-on text-muted"></i> وضعیت
                        </label>
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input shadow-sm" wire:model.live="approve"
                                wire:change="updateStatus" id="approveSwitch">
                            <label class="form-check-label text-muted fw-medium mx-2" for="approveSwitch">
                                {{ $approve ? 'فعال' : 'غیرفعال' }}
                            </label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="fw-semibold text-dark mb-2 d-flex align-items-center gap-2">
                            <i class="fas fa-comment-dots text-muted"></i> نظر
                        </label>
                        <textarea class="form-control shadow-sm border-0 bg-white" rows="5"
                            readonly>{{ $commentText }}</textarea>
                    </div>
                    @if($reply)
                        <div class="col-12 mt-3">
                            <label class="fw-semibold text-dark mb-2 d-flex align-items-center gap-2">
                                <i class="fas fa-reply text-muted"></i> پاسخ داده‌شده
                            </label>
                            <div class="bg-success-subtle p-3 rounded-2 shadow-sm">
                                <p class="text-muted mb-0">{{ $reply }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- فرم پاسخ -->
        <div class="card border-0 rounded-3 shadow-md bg-gradient-card">
            <div class="card-header bg-gradient-primary text-white p-4 rounded-top-3">
                <h5 class="mb-0 fw-bold">پاسخ به نظر</h5>
            </div>
            <div class="card-body p-5">
                <form wire:submit.prevent="saveReply">
                    <div class="form-group mb-4">
                        <label class="fw-semibold text-dark mb-2 d-flex align-items-center gap-2">
                            <i class="fas fa-reply text-muted"></i> پاسخ
                        </label>
                        <textarea class="form-control shadow-sm border-0 bg-white" wire:model="reply" rows="5"
                            placeholder="پاسخ خود را اینجا بنویسید"></textarea>
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <a href="{{ route('admin.content-management.comments.index') }}"
                            class="btn btn-gradient-warning rounded-pill px-4 py-2 d-flex align-items-center gap-2">
                            <i class="fas fa-arrow-right"></i> بازگشت
                        </a>
                        <button type="submit"
                            class="btn btn-gradient-success rounded-pill px-4 py-2 d-flex align-items-center gap-2">
                            <i class="fas fa-save"></i> ثبت پاسخ
                        </button>
                    </div>
                </form>
            </div>
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

        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .bg-gradient-primary {
            background: linear-gradient(90deg, #4f46e5, #7c3aed);
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

        .btn-gradient-warning {
            background: linear-gradient(90deg, #f59e0b, #fbbf24);
            border: none;
            color: white;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .btn-gradient-warning:hover {
            background: linear-gradient(90deg, #d97706, #f59e0b);
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(0, 0, 0, 0.15);
        }

        .form-check-input:checked {
            background-color: #10b981;
            border-color: #10b981;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.25);
        }

        .animate-bounce {
            animation: bounce 1s infinite;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-5px);
            }
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
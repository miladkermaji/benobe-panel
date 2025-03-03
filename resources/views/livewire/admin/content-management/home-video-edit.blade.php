<div class="container-fluid py-5">
    <!-- هدر -->
    <header class="glass-header p-4 rounded-3 mb-5 shadow-lg">
        <div class="d-flex align-items-center gap-3">
            <i class="fas fa-video fs-4 text-white animate-bounce"></i>
            <h4 class="mb-0 fw-bold text-white">ویرایش ویدئو صفحه نخست</h4>
        </div>
    </header>

    <div class="container px-0">
        <div class="card border-0 rounded-3 shadow-md bg-gradient-card">
            <div class="card-header bg-gradient-primary text-white p-4 rounded-top-3">
                <h5 class="mb-0 fw-bold">ویرایش ویدئو</h5>
            </div>
            <div class="card-body p-5">
                <form wire:submit.prevent="save">
                    <div class="row g-4">
                        <!-- عنوان -->
                        <div class="col-md-6">
                            <label class="fw-semibold text-dark mb-2 d-flex align-items-center gap-2">
                                <i class="fas fa-heading text-muted"></i> عنوان
                            </label>
                            <input type="text" class="form-control shadow-sm border-0 bg-white" wire:model="title">
                            @error('title') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- تصویر -->
                        <div class="col-md-6">
                            <label class="fw-semibold text-dark mb-2 d-flex align-items-center gap-2">
                                <i class="fas fa-image text-muted"></i> تصویر
                            </label>
                            @if($existingImage)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $existingImage) }}" class="img-thumbnail"
                                        style="width: 100px; height: auto;">
                                </div>
                            @endif
                            <input type="file" class="form-control shadow-sm border-0 bg-white" wire:model="image"
                                accept="image/*">
                            <div wire:loading wire:target="image" class="progress mt-2" style="height: 8px;">
                                <div class="progress-bar bg-gradient-primary animate-pulse" role="progressbar"
                                    style="width: 100%;"></div>
                            </div>
                            @error('image') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- فایل ویدئو -->
                        <div class="col-md-6">
                            <label class="fw-semibold text-dark mb-2 d-flex align-items-center gap-2">
                                <i class="fas fa-video text-muted"></i> فایل ویدئو
                            </label>
                            @if($existingVideo)
                                <div class="mb-2">
                                    <video controls class="w-100" style="max-height: 100px;">
                                        <source src="{{ asset('storage/' . $existingVideo) }}" type="video/mp4">
                                    </video>
                                </div>
                            @endif
                            <input type="file" class="form-control shadow-sm border-0 bg-white" wire:model="video"
                                accept="video/mp4">
                            <div wire:loading wire:target="video" class="progress mt-2" style="height: 8px;">
                                <div class="progress-bar bg-gradient-primary animate-pulse" role="progressbar"
                                    style="width: 100%;"></div>
                            </div>
                            @error('video') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- وضعیت -->
                        <div class="col-md-6">
                            <label class="fw-semibold text-dark mb-2 d-flex align-items-center gap-2">
                                <i class="fas fa-toggle-on text-muted"></i> وضعیت نمایش
                            </label>
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input shadow-sm" wire:model="approve"
                                    id="approveSwitch">
                                <label class="form-check-label text-muted fw-medium mx-2" for="approveSwitch">
                                    {{ $approve ? 'فعال' : 'غیرفعال' }}
                                </label>
                            </div>
                            @error('approve') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- توضیحات -->
                        <div class="col-12">
                            <label class="fw-semibold text-dark mb-2 d-flex align-items-center gap-2">
                                <i class="fas fa-align-justify text-muted"></i> توضیحات
                            </label>
                            <textarea class="form-control shadow-sm border-0 bg-white" wire:model="description"
                                rows="5"></textarea>
                            @error('description') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- دکمه‌ها -->
                        <div class="col-12 d-flex justify-content-between align-items-center gap-3 mt-4">
                            <a href="{{ route('admin.content-management.home-video.index') }}"
                                class="btn btn-gradient-warning rounded-pill px-4 py-2 d-flex align-items-center gap-2">
                                <i class="fas fa-arrow-right"></i> بازگشت
                            </a>
                            <button type="submit"
                                class="btn btn-gradient-success rounded-pill px-4 py-2 d-flex align-items-center gap-2">
                                <i class="fas fa-save"></i> ثبت تغییرات
                            </button>
                        </div>
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

        .progress-bar.animate-pulse {
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }

            100% {
                opacity: 1;
            }
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
                const toastOptions = {
                    positionClass: options.position || 'toast-top-right',
                    timeOut: options.timeOut || 3000,
                    progressBar: options.progressBar || false,
                };
                if (type === 'success') toastr.success(message, '', toastOptions);
                else if (type === 'error') toastr.error(message, '', toastOptions);
                else toastr.info(message, '', toastOptions);
            });
        });
    </script>
</div>
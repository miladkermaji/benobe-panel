<div class="container-fluid py-1">
    <!-- هدر -->
    <header class="glass-header p-4 rounded-3 mb-2 shadow-lg">
        <div class="d-flex align-items-center justify-content-between gap-4">
            <div class="d-flex align-items-center gap-3">
                <i class="fas fa-folder fs-3 text-white animate-bounce"></i>
                <h4 class="mb-0 fw-bold text-white">مدیریت فایل‌ها</h4>
            </div>
            <div class="d-flex align-items-center gap-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="#" wire:click="changePath('')" class="text-white">Root</a></li>
                        @foreach (explode('/', $currentPath) as $segment)
                            @if ($segment)
                                <li class="breadcrumb-item text-white">{{ $segment }}</li>
                            @endif
                        @endforeach
                    </ol>
                </nav>
            </div>
        </div>
    </header>

    <!-- ابزارها -->
    <div class="container px-0 mb-5">
        <div class="bg-light p-4 rounded-3 shadow-sm">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-0"><i class="fas fa-folder-plus text-muted"></i></span>
                        <input type="text" class="form-control border-0 shadow-none" wire:model="newFolderName" placeholder="نام پوشه جدید">
                        <button wire:click="createFolder" class="btn btn-gradient-success px-4">
                            <i class="fas fa-plus"></i> ایجاد
                        </button>
                    </div>
                    @error('newFolderName') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control border-0 shadow-none" wire:model.live="search" placeholder="جستجو در فایل‌ها و پوشه‌ها">
                    </div>
                </div>
                <div class="col-md-4">
                    <input type="file" class="form-control border-0 shadow-none" wire:model="filesToUpload" multiple>
                    @if($filesToUpload)
                        <div class="progress mt-2" style="height: 8px;">
                            <div class="progress-bar bg-gradient-primary animate-pulse" role="progressbar" style="width: 100%;"></div>
                        </div>
                    @endif
                    @error('filesToUpload.*') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- لیست فایل‌ها و پوشه‌ها -->
    <div class="container px-0">
        <div class="row g-4">
            @if($currentPath)
                <div class="col-md-3 col-sm-6">
                    <div wire:click="goBack" class="card comment-card border-0 rounded-3 shadow-sm bg-gradient-card h-100 cursor-pointer">
                        <div class="card-body p-4 d-flex flex-column align-items-center gap-3 text-center">
                            <i class="fas fa-arrow-right fa-3x text-muted animate-bounce"></i>
                            <h6 class="fw-semibold text-dark">بازگشت</h6>
                        </div>
                    </div>
                </div>
            @endif
            @forelse ($items as $item)
                <div class="col-md-3 col-sm-6">
                    <div class="card comment-card border-0 rounded-3 shadow-sm bg-gradient-card h-100">
                        <div class="card-body p-4 d-flex flex-column align-items-center gap-3 text-center">
                            @if($item['type'] === 'folder')
                                <div wire:click="changePath('{{ $item['path'] }}')" class="cursor-pointer">
                                    <i class="fas fa-folder fa-3x text-muted animate-bounce"></i>
                                </div>
                                @if($renamingPath === $item['path'])
                                    <input type="text" class="form-control shadow-sm border-0 bg-white" wire:model.live="newName" placeholder="نام جدید">
                                    <div class="d-flex gap-2 mt-auto">
                                        <button wire:click="renameItem" class="btn btn-gradient-success px-4 py-2">
                                            <i class="fas fa-check"></i> ثبت
                                        </button>
                                        <button wire:click="cancelRename" class="btn btn-gradient-danger px-4 py-2">
                                            <i class="fas fa-times"></i> لغو
                                        </button>
                                    </div>
                                @else
                                    <h6 class="fw-semibold text-dark text-ellipsis" style="max-width: 100%;">{{ $item['name'] }}</h6>
                                    <div class="d-flex gap-2 mt-auto">
                                        <button wire:click="startRename('{{ $item['path'] }}')" class="btn btn-gradient-warning rounded-full w-10 h-10 flex items-center justify-center">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="confirmDelete('{{ $item['path'] }}')" class="btn btn-gradient-danger rounded-full w-10 h-10 flex items-center justify-center">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                @endif
                            @else
                                @if($item['isImage'])
                                    <img src="{{ $item['url'] }}" class="img-thumbnail cursor-pointer" style="max-height: 120px; object-fit: cover;" wire:click="selectImage('{{ $item['url'] }}')">
                                @else
                                    <i class="fas fa-file fa-3x text-muted animate-bounce"></i>
                                @endif
                                @if($renamingPath === $item['path'])
                                    <input type="text" class="form-control shadow-sm border-0 bg-white" wire:model.live="newName" placeholder="نام جدید">
                                    <div class="d-flex gap-2 mt-auto">
                                        <button wire:click="renameItem" class="btn btn-gradient-success px-4 py-2">
                                            <i class="fas fa-check"></i> ثبت
                                        </button>
                                        <button wire:click="cancelRename" class="btn btn-gradient-danger px-4 py-2">
                                            <i class="fas fa-times"></i> لغو
                                        </button>
                                    </div>
                                @else
                                    <h6 class="fw-semibold text-dark text-ellipsis" style="max-width: 100%;">{{ $item['name'] }}</h6>
                                    <div class="d-flex gap-2 mt-auto">
                                        <a href="{{ $item['url'] }}" download class="btn btn-gradient-secondary rounded-pill px-3 py-1">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <button wire:click="startRename('{{ $item['path'] }}')" class="btn btn-gradient-warning rounded-full w-10 h-10 flex items-center justify-center">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="confirmDelete('{{ $item['path'] }}')" class="btn btn-gradient-danger rounded-full w-10 h-10 flex items-center justify-center">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-folder-open fs-1 text-muted mb-3"></i>
                        <p class="text-muted fw-medium">هیچ فایل یا پوشه‌ای یافت نشد.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- پیش‌نمایش تصویر -->
    @if($selectedImage)
        <div class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
            <div class="relative max-w-4xl w-full p-4">
                <img src="{{ $selectedImage }}" class="w-full h-auto rounded-lg shadow-lg" style="max-height: 80vh; object-fit: contain;">
                <button wire:click="closePreview" class="absolute top-4 right-4 btn btn-gradient-danger rounded-full w-10 h-10 flex items-center justify-center">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif

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

        .btn-gradient-primary {
            background: linear-gradient(90deg, #4f46e5, #7c3aed);
            border: none;
            color: white;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .btn-gradient-primary:hover {
            background: linear-gradient(90deg, #4338ca, #6b21a8);
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-gradient-danger {
            background: linear-gradient(90deg, #f87171, #fca5a5);
            border: none;
            color: white;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .btn-gradient-danger:hover {
            background: linear-gradient(90deg, #ef4444, #f87171);
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(0, 0, 0, 0.15);
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

        .btn-gradient-secondary {
            background: linear-gradient(90deg, #6b7280, #9ca3af);
            border: none;
            color: white;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .btn-gradient-secondary:hover {
            background: linear-gradient(90deg, #4b5563, #6b7280);
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(0, 0, 0, 0.15);
        }

        .form-control {
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.25);
        }

        .text-ellipsis {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .progress-bar.animate-pulse {
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .animate-bounce {
            animation: bounce 1s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        .breadcrumb-item + .breadcrumb-item::before {
            color: #ffffff;
        }

        .rounded-full {
            border-radius: 50%;
            padding: 0;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .w-10 {
            width: 2.5rem;
        }

        .h-10 {
            height: 2.5rem;
        }
    </style>

    <script>
        document.addEventListener('livewire:init', () => {
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

        function confirmDelete(path) {
            Swal.fire({
                title: 'آیا مطمئن هستید؟',
                text: 'این آیتم حذف خواهد شد و قابل بازگشت نیست!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#d1d5db',
                confirmButtonText: 'بله، حذف کن',
                cancelButtonText: 'خیر',
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.deleteItem(path);
                }
            });
        }
    </script>
</div>
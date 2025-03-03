<div class="container-fluid py-1">
    <!-- هدر -->
    <header class="glass-header p-4 rounded-3 mb-2 shadow-lg">
        <div class="d-flex align-items-center justify-content-between gap-4">
            <div class="d-flex align-items-center gap-3">
                <i class="fas fa-envelope fs-3 text-white animate-bounce"></i>
                <h4 class="mb-0 fw-bold text-white">مدیریت خبرنامه</h4>
            </div>
            <div class="text-white fw-medium">اعضای خبرنامه</div>
        </div>
    </header>

    <!-- ابزارها -->
    <div class="container px-0 mb-2">
        <div class="bg-light p-4 rounded-3 shadow-sm">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-0"><i class="fas fa-plus text-muted"></i></span>
                        <input type="email" class="form-control border-0 shadow-none" wire:model="newEmail" placeholder="ایمیل جدید">
                        <button wire:click="addMember" class="btn btn-gradient-success  px-4">
                            <i class="fas fa-plus mx-1"></i> افزودن
                        </button>
                    </div>
                    @error('newEmail') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control border-0 shadow-none" wire:model.live="search" placeholder="جستجو در اعضا...">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex gap-2">
                        <button wire:click="export" class="btn btn-gradient-secondary rounded-pill px-4">
                            <i class="fas fa-download mx-1"></i> خروجی CSV
                        </button>
                        <button wire:click="deleteSelected" class="btn btn-gradient-danger rounded-pill px-4" @if(empty($selectedMembers)) disabled @endif>
                            <i class="fas fa-trash mx-1"></i> حذف انتخاب‌شده‌ها
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- لیست اعضا -->
    <div class="container px-0">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><input type="checkbox" wire:model.live="selectAll" class="form-check-input"></th>
                                <th>ردیف</th>
                                <th>ایمیل</th>
                                <th>وضعیت</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($members as $index => $member)
                                <tr>
                                    <td><input type="checkbox" wire:model.live="selectedMembers" value="{{ $member->id }}" class="form-check-input"></td>
                                    <td>{{ $members->firstItem() + $index }}</td>
                                    <td>
                                        @if($editId === $member->id)
                                            <input type="email" class="form-control border-0 shadow-none" wire:model.live="editEmail">
                                        @else
                                            {{ $member->email }}
                                        @endif
                                    </td>
                                    <td>
                                        <button wire:click="toggleStatus({{ $member->id }})" class="badge {{ $member->is_active ? 'bg-label-success' : 'bg-label-danger' }} border-0 cursor-pointer">
                                            {{ $member->is_active ? 'فعال' : 'غیرفعال' }}
                                        </button>
                                    </td>
                                    <td>
                                        @if($editId === $member->id)
                                            <div class="d-flex gap-2">
                                                <button wire:click="updateMember" class="btn btn-gradient-success rounded-pill px-3">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button wire:click="cancelEdit" class="btn btn-gradient-danger rounded-pill px-3">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        @else
                                            <div class="d-flex gap-2">
                                                <button wire:click="startEdit({{ $member->id }})" class="btn btn-gradient-warning rounded-full w-8 h-8 flex items-center justify-center">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button onclick="confirmDelete({{ $member->id }})" class="btn btn-gradient-danger rounded-full w-8 h-8 flex items-center justify-center">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <i class="fas fa-envelope-open fs-1 text-muted mb-3"></i>
                                        <p class="text-muted fw-medium">هیچ عضوی یافت نشد.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between mt-4">
                    <div class="text-muted">نمایش {{ $members->firstItem() }} تا {{ $members->lastItem() }} از {{ $members->total() }} ردیف</div>
                    {{ $members->links() }}
                </div>
            </div>
        </div>
    </div>

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

        .btn-gradient-danger:hover:not(:disabled) {
            background: linear-gradient(90deg, #ef4444, #f87171);
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-gradient-danger:disabled {
            background: #d1d5db;
            cursor: not-allowed;
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

        .rounded-full {
            border-radius: 50%;
            padding: 0;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .w-8 {
            width: 2rem;
        }

        .h-8 {
            height: 2rem;
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
                else if (type === 'warning') toastr.warning(message, '', toastOptions);
                else toastr.info(message, '', toastOptions);
            });

            Livewire.on('confirmDeleteSelected', () => {
                Swal.fire({
                    title: 'آیا مطمئن هستید؟',
                    text: 'اعضای انتخاب‌شده از خبرنامه حذف خواهند شد و قابل بازگشت نیستند!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#d1d5db',
                    confirmButtonText: 'بله، حذف کن',
                    cancelButtonText: 'خیر',
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.confirmDeleteSelected();
                    }
                });
            });
        });

        function confirmDelete(id) {
            Swal.fire({
                title: 'آیا مطمئن هستید؟',
                text: 'این عضو از خبرنامه حذف خواهد شد و قابل بازگشت نیست!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#d1d5db',
                confirmButtonText: 'بله، حذف کن',
                cancelButtonText: 'خیر',
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.deleteMember(id);
                }
            });
        }
    </script>
</div>
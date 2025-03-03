<div class="container-fluid py-4">
    <!-- هدر اصلی -->
    <div class="bg-light text-dark p-4 rounded-top border">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="fas fa-comments me-3 text-primary"></i>
                <h5 class="mb-0 fw-bold text-dark">نظرات پزشکان</h5>
            </div>
        </div>
    </div>

    <!-- بدنه اصلی -->
    <div class="bg-white p-4 rounded-bottom shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="input-group w-25">
                <span class="input-group-text bg-light border-end-0 rounded-start">
                    <i class="fas fa-search text-muted align-middle"></i>
                </span>
                <input type="text" class="form-control border-start-0 rounded-end" wire:model.live="search"
                    placeholder="جستجوی نام کاربر یا نظر...">
            </div>
            <div class="d-flex align-items-center gap-3">
                <select wire:model.live="perPage" class="form-select form-select-sm w-auto">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <button class="btn btn-danger btn-sm" id="delete-selected-btn" @if (empty($selectedComments)) disabled
                @endif>
                    <i class="fas fa-trash me-1"></i> حذف انتخاب‌شده‌ها
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;" class="align-middle">
                            <input type="checkbox" class="form-check-input" wire:model.live="selectAll">
                        </th>
                        <th class="align-middle">ردیف</th>
                        <th class="align-middle">نام کاربر</th>
                        <th class="align-middle">نظر</th>
                        <th class="align-middle">وضعیت</th>
                        <th class="align-middle">تاریخ ارسال</th>
                        <th class="align-middle">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($comments as $comment)
                        <tr>
                            <td class="align-middle">
                                <input type="checkbox" class="form-check-input" wire:model.live="selectedComments"
                                    value="{{ $comment->id }}">
                            </td>
                            <td class="align-middle">{{ $comments->firstItem() + $loop->index }}</td>
                            <td class="align-middle">{{ $comment->user_name }} @if ($comment->user_phone) -
                            {{ $comment->user_phone }} @endif</td>
                            <td class="align-middle">{{ Str::limit($comment->comment, 50) }}</td>
                            <td class="align-middle text-center">
                                <div class="form-check form-switch d-flex align-items-center justify-content-center">
                                    <input type="checkbox" class="form-check-input"
                                        wire:model.live="commentStatuses.{{ $comment->id }}"
                                        wire:change="toggleStatus({{ $comment->id }})" @checked($comment->status)>
                                    <label
                                        class="form-check-label mx-1 text-muted">{{ $comment->status ? 'فعال' : 'غیرفعال' }}</label>
                                </div>
                            </td>
                            <td class="align-middle">
                                {{ \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($comment->created_at))->format('Y/m/d H:i') }}
                            </td>
                            <td class="align-middle">
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.content.doctors.comment-doctor.show', $comment->id) }}"
                                        class="btn btn-info btn-sm d-flex align-items-center gap-1">
                                        <i class="fas fa-eye"></i> مشاهده
                                    </a>
                                    <button class="btn btn-danger btn-sm d-flex align-items-center gap-1 delete-comment"
                                        data-id="{{ $comment->id }}">
                                        <i class="fas fa-trash"></i> حذف
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">هیچ نظری یافت نشد.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- صفحه‌بندی -->
        <div class="mt-4">
            {{ $comments->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <!-- استایل‌ها -->
    <style>
        .bg-light {
            background-color: #f8f9fa !important;
            border-radius: 12px 12px 0 0;
        }

        .border {
            border-color: #dee2e6 !important;
        }

        .input-group-text {
            background-color: #f8f9fa;
            padding: 0.5rem 0.75rem;

        }

        .input-group-text i {
            font-size: 1rem;
            color: #6c757d;
        }

        .form-control {
            padding: 0.5rem 0.75rem;
            font-size: 1rem;
            height: calc(2.25rem + 2px);
        }

        .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
        }

        .btn {
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2);
        }

        .btn-danger:disabled {
            background-color: #6c757d;
            border-color: #6c757d;
            cursor: not-allowed;
        }

        .btn-info {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .btn-info:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);
        }

        .form-check-input {
            cursor: pointer;
        }

        .form-switch .form-check-input {
            width: 2.5em;
            height: 1.25em;
        }

        .table-bordered th,
        .table-bordered td {
            border-color: #dee2e6 !important;
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.3s ease;
        }
    </style>

    <script>
        document.addEventListener('livewire:initialized', () => {
            // مدیریت حذف تک‌نظر
            document.querySelectorAll('.delete-comment').forEach(button => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    const commentId = this.getAttribute('data-id');
                    Swal.fire({
                        title: 'آیا مطمئن هستید؟',
                        text: "این نظر حذف خواهد شد و قابل بازگشت نیست!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'بله، حذف کن',
                        cancelButtonText: 'خیر'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            @this.call('deleteComment', commentId);
                        }
                    });
                });
            });

            // رفتار چک‌باکس انتخاب همه
            Livewire.on('updateSelectAll', (selectAll) => {
                const checkboxes = document.querySelectorAll('input[type="checkbox"][wire\\:model="selectedComments"]');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = selectAll;
                });
            });
        });
    </script>
</div>
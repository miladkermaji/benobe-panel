<div class="container-fluid py-1">
    <header class="glass-header p-3 rounded-3 mb-2 shadow-lg">
        <div class="d-flex align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-hospital fs-4 text-white animate-bounce"></i>
                <h4 class="mb-0 fw-bold text-white">مدیریت بیمارستان‌ها</h4>
            </div>
            <a href="{{ route('admin.content.hospitals.hospitals-management.create') }}" class="btn btn-outline-light rounded-pill px-3">
                <i class="fas fa-plus"></i> افزودن بیمارستان
            </a>
        </div>
    </header>

    <div class="bg-light p-3 rounded-3 shadow-sm mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-md-6 col-sm-12">
                <div class="input-group">
                    <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" class="form-control border-0 shadow-none" wire:model.live="search" placeholder="جستجو در بیمارستان‌ها...">
                </div>
            </div>
            <div class="col-md-6 col-sm-12 d-flex justify-content-end gap-2">
                <button wire:click="deleteSelected" class="btn btn-outline-danger rounded-pill px-3" 
                        @if (empty($selectedHospitals)) disabled @endif>
                    <i class="fas fa-trash"></i> حذف انتخاب‌شده‌ها
                </button>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-3">
            <div class="table-responsive text-nowrap">
                <table class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th style="width: 5%;"><input type="checkbox" wire:model.live="selectAll" class="form-check-input"></th>
                            <th style="width: 5%;">#</th>
                            <th style="width: 20%;">نام مسئول</th>
                            <th style="width: 25%;">نام بیمارستان</th>
                            <th style="width: 15%;">شماره تماس</th>
                            <th style="width: 15%;">تاریخ ثبت‌نام</th>
                            <th style="width: 10%;">وضعیت</th>
                            <th style="width: 15%;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($hospitals as $index => $hospital)
                            <tr>
                                <td><input type="checkbox" wire:model.live="selectedHospitals" value="{{ $hospital->id }}" class="form-check-input"></td>
                                <td>{{ $hospitals->firstItem() + $index }}</td>
                                <td>{{ $hospital->doctor->first_name . ' ' . $hospital->doctor->last_name }}</td>
                                <td>{{ $hospital->name }}</td>
                                <td>{{ $hospital->phone_number ?? '---' }}</td>
                                <td>{{ \App\Helpers\JalaliHelper::toJalaliDateTime($hospital->created_at) }}</td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" 
                                               wire:click="toggleStatus({{ $hospital->id }})" 
                                               {{ $hospital->is_active ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-gradient-secondary btn-sm" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="https://benobe.ir/authlogin/{{ $hospital->id }}" target="_blank">
                                                <i class="fa fa-sign-in"></i> ورود
                                            </a></li>
                                            <li><a class="dropdown-item" href="{{ route('admin.content.hospitals.hospitals-management.edit', $hospital->id) }}">
                                                <i class="fa fa-edit"></i> ویرایش
                                            </a></li>
                                            <li><a class="dropdown-item" href="#">مدیریت پزشکان</a></li>
                                            <li><a class="dropdown-item text-danger" href="#" wire:click.prevent="deleteHospital({{ $hospital->id }})">
                                                <i class="fa fa-trash"></i> حذف
                                            </a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-hospital fs-1 text-muted mb-3"></i>
                                    <p class="text-muted fw-medium">هیچ بیمارستانی یافت نشد.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between mt-3">
                <div class="text-muted fs-6">نمایش {{ $hospitals->firstItem() }} تا {{ $hospitals->lastItem() }} از {{ $hospitals->total() }} ردیف</div>
                {{ $hospitals->links() }}
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
        .btn-gradient-secondary {
            background: linear-gradient(90deg, #6b7280, #9ca3af);
            border: none;
            color: white;
        }
        .btn-gradient-secondary:hover {
            background: linear-gradient(90deg, #4b5563, #6b7280);
            transform: translateY(-1px);
        }
        .btn-outline-danger:disabled {
            border-color: #d1d5db;
            color: #d1d5db;
            cursor: not-allowed;
        }
        .form-switch .form-check-input {
            width: 2em;
            height: 1em;
        }
    </style>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('toast', (event) => {
                const data = event[0];
                const { message, type } = data;
                if (typeof toastr !== 'undefined') {
                    const toastOptions = { positionClass: 'toast-top-right', timeOut: 3000, progressBar: false };
                    if (type === 'success') toastr.success(message, '', toastOptions);
                    else if (type === 'error') toastr.error(message, '', toastOptions);
                    else if (type === 'warning') toastr.warning(message, '', toastOptions);
                    else toastr.info(message, '', toastOptions);
                }
            });
        });
    </script>
</div>
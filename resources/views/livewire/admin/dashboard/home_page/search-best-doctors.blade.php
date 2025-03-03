<div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-20">
            <div>
                <input type="search" class="form-control w-100 me-2" placeholder="جستجو پزشک" wire:model="search"
                    wire:keyup="searchUpdated">
            </div>
            <div>
                <button class="btn btn-danger" id="deleteButton" @disabled(empty($selectedRows)) onclick="confirmDelete()">
                    <i class="ti ti-trash"></i> حذف انتخاب‌شده‌ها
                </button>
               
            </div>
            <div>
                <a href="{{ route('admin.Dashboard.home_page.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus"></i> افزودن پزشک برتر
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" class="form-check-input" wire:model="selectAll"
                                x-on:change="$wire.dispatch('updateDeleteButton')">
                        </th>
                        <th>نام پزشک</th>
                        <th>مرکز درمان</th>
                        <th>پزشک برتر</th>
                        <th>مشاور برتر</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bestDoctors as $doctor)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input" wire:model="selectedRows"
                                    value="{{ $doctor->id }}" x-on:change="$wire.dispatch('updateDeleteButton')">
                            </td>
                            <td>{{ $doctor->doctor->first_name . ' ' . $doctor->doctor->last_name }}</td>
                            <td>{{ $doctor->hospital->name ?? '---' }}</td>
                            <td>
                                {!! $doctor->best_doctor ? '<i class="fa fa-check-circle" style="color:green"></i>' : '---' !!}
                            </td>
                            <td>
                                {!! $doctor->best_consultant ? '<i class="fa fa-check-circle" style="color:green"></i>' : '---' !!}
                            </td>
                            <td>
                                <span wire:click="toggleStatus({{ $doctor->id }})"
                                    class="badge bg-label-{{ $doctor->status ? 'success' : 'danger' }} cursor-pointer">
                                    {{ $doctor->status ? 'فعال' : 'غیرفعال' }}
                                </span>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn p-0" data-bs-toggle="dropdown">
                                        <i class="ti ti-dots-vertical"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item"
                                            href="{{ route('admin.Dashboard.home_page.edit', $doctor->id) }}">
                                            <i class="ti ti-pencil"></i> ویرایش
                                        </a>
                                        <button class="dropdown-item text-danger"
                                            wire:click="deleteSelected({{ $doctor->id }})">
                                            <i class="ti ti-trash"></i> حذف
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between mt-3 p-3">
            <span>نمایش {{ $bestDoctors->count() }} از {{ $bestDoctors->total() }} پزشک</span>
            {{ $bestDoctors->links() }}
        </div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        Livewire.on('refreshDeleteButton', (data) => {
            document.getElementById('deleteButton').disabled = !data.hasSelectedRows;
        });
    });

    function confirmDelete() {
        Swal.fire({
            title: "آیا مطمئن هستید؟",
            text: "این عملیات غیرقابل بازگشت است!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "بله، حذف شود!",
            cancelButtonText: "لغو",
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch('deleteConfirmed'); // ✅ اصلاح شد
            }
        });
    }

</script>
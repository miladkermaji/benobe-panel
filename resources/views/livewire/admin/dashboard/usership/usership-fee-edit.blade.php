<div>
    <h4 class="py-3 mb-4">
        <span class="text-muted fw-light">تعرفه‌ها /</span> ویرایش تعرفه
    </h4>

    @if ($successMessage)
        <div class="alert alert-success">
            {{ $successMessage }}
        </div>
        <script>
            setTimeout(function () {
                window.location.href = "{{ route('admin.Dashboard.usershipfee.index') }}";
            }, 5000);
        </script>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="card-title">ویرایش تعرفه</h5>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="update">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">نام بسته</label>
                        <input type="text" class="form-control" wire:model="name">
                        @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">تعداد روز</label>
                        <input type="number" class="form-control" wire:model="days">
                        @error('days') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-6 mt-3">
                        <label class="form-label">قیمت (تومان)</label>
                        <input type="number" class="form-control" wire:model="price">
                        @error('price') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-6 mt-3">
                        <label class="form-label">قرارگیری</label>
                        <input type="number" class="form-control" wire:model="sort">
                        @error('sort') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-success">بروزرسانی تعرفه</button>
                        <a href="{{ route('admin.Dashboard.usershipfee.index') }}"
                            class="btn btn-secondary">بازگشت</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
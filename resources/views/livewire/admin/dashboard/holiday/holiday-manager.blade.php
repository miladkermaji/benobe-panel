<div class="card p-3 shadow-sm">
    <h5 class="mb-3">مدیریت تعطیلات</h5>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="holidayPicker" class="form-label fw-bold">📅 انتخاب تاریخ تعطیلی</label>
            <input type="text" id="holidayPicker" class="form-control text-center" wire:model="selectedDate" placeholder="مثلاً ۱۴۰۳/۰۱/۰۱">
        </div>

        <div class="col-md-6 mb-3">
            <label for="title" class="form-label fw-bold">🏷 عنوان تعطیلی</label>
            <input type="text" id="title" class="form-control text-center" wire:model="title" placeholder="مثلاً عید نوروز">
        </div>
    </div>

    <button class="btn btn-success w-100" wire:click="addHoliday">✅ ثبت تعطیلی</button>

    <hr class="my-4">

    <h5 class="mb-3">📜 تعطیلات ثبت‌شده:</h5>

    <ul class="list-group">
        @foreach($holidaysList as $holiday)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>📅 {{ verta($holiday->date)->format('Y/m/d') }} - {{ $holiday->title ?? 'بدون عنوان' }}</span>
            <button class="btn btn-danger btn-sm" wire:click="removeHoliday('{{ $holiday->date }}')">حذف</button>

            </li>
        @endforeach
    </ul>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            flatpickr("#holidayPicker", {
                dateFormat: "Y-m-d",
                locale: "fa", // نمایش ماه شمسی
                disable: @json($holidays),
                onChange: function (selectedDates, dateStr) {
                    @this.set('selectedDate', dateStr);
                }
            });
        });
    </script>
</div>

<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden" style="background: #ffffff;">
    <div
      class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">ویرایش نوبت دستی</h5>
      </div>
      <a href="{{ route('admin.panel.manual-appointments.index') }}"
        class="btn btn-outline-light btn-sm rounded-pill px-4 d-flex align-items-center gap-2 hover:shadow-lg transition-all">
        <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        بازگشت
      </a>
    </div>

    <div class="card-body p-4">
      <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
          <div class="row g-4">
            <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
              <select wire:model.live="doctor_id" class="form-select select2" id="doctor_id">
                <option value="">انتخاب کنید</option>
                @foreach ($doctors as $doctor)
                  <option value="{{ $doctor->id }}" {{ $doctor->id == $doctor_id ? 'selected' : '' }}>
                    {{ $doctor->first_name . ' ' . $doctor->last_name }}
                    ({{ $doctor->specialty->name ?? 'نامشخص' }})
                  </option>
                @endforeach
              </select>
              <label for="doctor_id" class="form-label">پزشک</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
              <select wire:model.live="user_id" class="form-select select2" id="user_id">
                <option value="">انتخاب کنید</option>
                @foreach ($users as $user)
                  <option value="{{ $user->id }}" {{ $user->id == $user_id ? 'selected' : '' }}>
                    {{ $user->first_name . ' ' . $user->last_name }}
                  </option>
                @endforeach
              </select>
              <label for="user_id" class="form-label">بیمار (اختیاری)</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="appointment_date" class="form-control jalali-datepicker text-end"
                id="appointment_date" data-jdp required>
              <label for="appointment_date" class="form-label">تاریخ نوبت</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5" dir="rtl">
              <input data-timepicker type="text" wire:model="appointment_time" class="form-control  h-50"
                id="appointment_time" required>
              <label for="appointment_time" class="form-label">ساعت نوبت</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <select wire:model="status" class="form-select" id="status">
                <option value="scheduled">برنامه‌ریزی‌شده</option>
                <option value="cancelled">لغو شده</option>
                <option value="attended">حضور یافته</option>
                <option value="missed">غایب</option>
                <option value="pending_review">در انتظار بررسی</option>
              </select>
              <label for="status" class="form-label">وضعیت نوبت</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <select wire:model="payment_status" class="form-select" id="payment_status">
                <option value="paid">پرداخت شده</option>
                <option value="unpaid">پرداخت نشده</option>
                <option value="pending">در انتظار پرداخت</option>
              </select>
              <label for="payment_status" class="form-label">وضعیت پرداخت</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="tracking_code" class="form-control" id="tracking_code" placeholder=" ">
              <label for="tracking_code" class="form-label">کد رهگیری (اختیاری)</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="number" wire:model="fee" class="form-control" id="fee" placeholder=" ">
              <label for="fee" class="form-label">هزینه (تومان)</label>
            </div>
            <div class="col-12 position-relative mt-5">
              <textarea wire:model="description" class="form-control" id="description" rows="3" placeholder=" "></textarea>
              <label for="description" class="form-label">یادداشت‌ها (اختیاری)</label>
            </div>
          </div>

          <div class="text-end mt-4 w-100 d-flex justify-content-end">
            <button wire:click="update"
              class="btn my-btn-primary px-5 py-2 d-flex align-items-center gap-2 shadow-lg hover:shadow-xl transition-all">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                <path d="M17 21v-8H7v8M7 3v5h8" />
              </svg>
              ذخیره تغییرات
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>



  <script>
    document.addEventListener('livewire:init', function() {
   

      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });

      $('#doctor_id').select2({
        dir: 'rtl',
        placeholder: 'انتخاب کنید',
        width: '100%'
      }).val('{{ $doctor_id }}').trigger('change');
      $('#user_id').select2({
        dir: 'rtl',
        placeholder: 'انتخاب کنید',
        width: '100%'
      }).val('{{ $user_id }}').trigger('change');

      $('#doctor_id').on('change', function() {
        @this.set('doctor_id', $(this).val());
      });
      $('#user_id').on('change', function() {
        @this.set('user_id', $(this).val());
      });

      jalaliDatepicker.startWatch({
        minDate: "attr",
        maxDate: "attr",
        showTodayBtn: true,
        showEmptyBtn: true,
        time: false,
        dateFormatter: function(unix) {
          return new Date(unix).toLocaleDateString('fa-IR', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
          });
        }
      });
      document.getElementById('appointment_date').addEventListener('change', function() {
        @this.set('appointment_date', this.value);
      });
    });
  </script>
</div>

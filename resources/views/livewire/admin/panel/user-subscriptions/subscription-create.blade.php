<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
    <div
      class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="custom-animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">افزودن اشتراک جدید</h5>
      </div>
      <a href="{{ route('admin.panel.user-subscriptions.index') }}"
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
              <select id="user_id" class="form-select select2-ajax" wire:model.defer="user_id"></select>
              <label for="user_id" class="form-label">کاربر</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
              <select wire:model.defer="membership_plan_id" class="form-select select2" id="membership_plan_id">
                <option value="">انتخاب کنید</option>
                @foreach ($plans as $plan)
                  <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                @endforeach
              </select>
              <label for="membership_plan_id" class="form-label">طرح عضویت</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model.defer="start_date" class="form-control jalali-datepicker text-end"
                id="start_date" placeholder="" data-jdp>
              <label for="start_date" class="form-label">تاریخ شروع</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model.defer="end_date" class="form-control jalali-datepicker text-end"
                id="end_date" placeholder="" data-jdp>
              <label for="end_date" class="form-label">تاریخ پایان</label>
            </div>
            <div class="col-12 position-relative mt-5">
              <textarea wire:model.defer="description" class="form-control" id="description" rows="4" placeholder=" "></textarea>
              <label for="description" class="form-label">توضیحات (اختیاری)</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5 d-flex align-items-center">
              <div class="form-check form-switch w-100 d-flex align-items-center">
                <input class="form-check-input" type="checkbox" id="status" wire:model.defer="status">
                <label class="form-check-label fw-medium" for="status">
                  وضعیت: <span
                    class="px-2 text-{{ $status ? 'success' : 'danger' }}">{{ $status ? 'فعال' : 'غیرفعال' }}</span>
                </label>
              </div>
            </div>
          </div>

          <div class="text-end mt-4 w-100 d-flex justify-content-end">
            <button wire:click="save"
              class="btn my-btn-primary px-5 py-2 d-flex align-items-center gap-2 shadow-lg hover:shadow-xl transition-all">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M12 5v14M5 12h14" />
              </svg>
              افزودن اشتراک
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('livewire:init', function() {
      function initializeSelect2() {
        $('#user_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%',
          ajax: {
            url: '/admin/api/users/search',
            dataType: 'json',
            delay: 250,
            data: function(params) {
              return {
                q: params.term
              };
            },
            processResults: function(data) {
              return {
                results: data.results
              };
            },
            cache: true
          }
        });
        $('#membership_plan_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });
      }
      initializeSelect2();

      $('#user_id').on('change', function() {
        @this.set('user_id', $(this).val());
      });
      $('#membership_plan_id').on('change', function() {
        @this.set('membership_plan_id', $(this).val());
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

      document.getElementById('start_date').addEventListener('change', function() {
        @this.set('start_date', this.value);
      });
      document.getElementById('end_date').addEventListener('change', function() {
        @this.set('end_date', this.value);
      });

      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });
    });
  </script>
</div>

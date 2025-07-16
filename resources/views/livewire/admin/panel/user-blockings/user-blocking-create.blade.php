<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-sm">
    <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
      <h5 class="m-0">افزودن کاربر مسدود جدید</h5>
      <a href="{{ route('admin.panel.user-blockings.index') }}" class="btn btn-light rounded-pill px-4">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M19 12H5M12 19l-7-7 7-7" />
        </svg>
        <span class="ms-2">بازگشت</span>
      </a>
    </div>
    <div class="card-body">
      <form wire:submit="save">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="label-top-input-special-takhasos">نوع کاربر</label>
            <select wire:model.live="type" class="form-select" id="type">
              <option value="">انتخاب کنید</option>
              <option value="user">کاربر</option>
              <option value="doctor">پزشک</option>
            </select>
            @error('type')
              <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6 mb-3">
            <label class="label-top-input-special-takhasos">کاربر</label>
            <div wire:ignore>
              <select wire:model.live="user_id" class="form-select select2" id="user_id">
                <option value="">انتخاب کنید</option>
              </select>
            </div>
            @error('user_id')
              <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6 mb-3">
            <label class="label-top-input-special-takhasos">تاریخ شروع</label>
            <input type="text" wire:model="blocked_at" class="form-control jalali-datepicker text-end"
              id="blocked_at" data-jdp>
            @error('blocked_at')
              <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6 mb-3">
            <label class="label-top-input-special-takhasos">تاریخ پایان</label>
            <input type="text" wire:model="unblocked_at" class="form-control jalali-datepicker text-end"
              id="unblocked_at" data-jdp>
            @error('unblocked_at')
              <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-12 mb-3">
            <label class="label-top-input-special-takhasos">دلیل</label>
            <textarea wire:model="reason" class="form-control" rows="3"></textarea>
            @error('reason')
              <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-12">
            <div class="form-check form-switch">
              <input type="checkbox" wire:model="status" class="form-check-input" id="status">
              <label class="form-check-label" for="status">فعال</label>
            </div>
            @error('status')
              <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
          </div>
        </div>

        <div class="mt-4">
          <button type="submit" class="btn btn-gradient-success rounded-pill px-4">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2">
              <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
              <polyline points="17 21 17 13 7 13 7 21" />
              <polyline points="7 3 7 8 15 8" />
            </svg>
            <span class="ms-2">ذخیره</span>
          </button>
        </div>
      </form>
    </div>
  </div>



  <script>
    document.addEventListener('livewire:init', function() {
      function getType() {
        return document.getElementById('type')?.value || 'user';
      }
      $('#user_id').select2({
        dir: 'rtl',
        placeholder: 'انتخاب کنید',
        width: '100%',
        ajax: {
          url: '{{ route('admin.panel.user-blockings.search-users') }}',
          dataType: 'json',
          delay: 250,
          data: function(params) {
            return {
              q: params.term,
              type: getType()
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
      $('#user_id').on('change', function() {
        @this.set('user_id', $(this).val());
      });
      document.getElementById('type').addEventListener('change', function() {
        $('#user_id').val('').trigger('change');
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
      document.getElementById('blocked_at').addEventListener('change', function() {
        @this.set('blocked_at', this.value);
      });
      document.getElementById('unblocked_at').addEventListener('change', function() {
        @this.set('unblocked_at', this.value);
      });
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });
    });
  </script>
</div>

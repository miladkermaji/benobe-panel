<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-sm">
    <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
      <h5 class="m-0">ویرایش کاربر مسدود</h5>
      <a href="{{ route('admin.panel.user-blockings.index') }}" class="btn btn-light rounded-pill px-4">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M19 12H5M12 19l-7-7 7-7" />
        </svg>
        <span class="ms-2">بازگشت</span>
      </a>
    </div>
    <div class="card-body">
      <form wire:submit="update">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="label-top-input-special-takhasos">نوع کاربر</label>
            <select wire:model="type" class="form-select" disabled>
              <option value="">انتخاب کنید</option>
              <option value="user" {{ $userBlocking->user_id ? 'selected' : '' }}>کاربر</option>
              <option value="doctor" {{ $userBlocking->doctor_id ? 'selected' : '' }}>پزشک</option>
            </select>
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
            <input type="text" wire:model="blocked_at" class="form-control" id="blocked_at">
            @error('blocked_at')
              <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6 mb-3">
            <label class="label-top-input-special-takhasos">تاریخ پایان</label>
            <input type="text" wire:model="unblocked_at" class="form-control" id="unblocked_at">
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
            <span class="ms-2">ذخیره تغییرات</span>
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    let select2Initialized = false;

    function getType() {
      return '{{ $type }}';
    }

    function setInitialUser() {
      var userId = '{{ $user_id ?? '' }}';
      if (userId) {
        $.ajax({
          url: '{{ route('admin.panel.user-blockings.search-users') }}',
          data: {
            q: '',
            type: getType()
          },
          dataType: 'json',
          success: function(data) {
            var found = data.results.find(x => x.id == userId);
            if (found) {
              var option = new Option(found.text, found.id, true, true);
              $('#user_id').append(option).trigger('change');
            }
          }
        });
      }
    }

    function initializeSelect2() {
      if (select2Initialized) return;
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
      select2Initialized = true;
      setInitialUser();
    }
    document.addEventListener('DOMContentLoaded', function() {
      initializeSelect2();
      $('#user_id').on('change', function() {
        let component = document.querySelector('[wire\\:id][wire\\:initial-data*="user-blocking-edit"]');
        if (component) {
          let id = component.getAttribute('wire:id');
          Livewire.find(id).set('user_id', $(this).val());
        }
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
        let component = document.querySelector('[wire\\:id][wire\\:initial-data*="user-blocking-edit"]');
        if (component) {
          let id = component.getAttribute('wire:id');
          Livewire.find(id).set('blocked_at', this.value);
        }
      });
      document.getElementById('unblocked_at').addEventListener('change', function() {
        let component = document.querySelector('[wire\\:id][wire\\:initial-data*="user-blocking-edit"]');
        if (component) {
          let id = component.getAttribute('wire:id');
          Livewire.find(id).set('unblocked_at', this.value);
        }
      });
    });
    document.addEventListener('livewire:init', function() {
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });
    });
  </script>
</div>

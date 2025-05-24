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
            <label class="form-label">نوع کاربر</label>
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
            <label class="form-label">کاربر</label>
            <div wire:ignore>
              <select wire:model.live="user_id" class="form-select select2" id="user_id">
                <option value="">انتخاب کنید</option>
                @if ($type == 'user')
                  @foreach ($users as $user)
                    <option value="{{ $user->id }}">
                      {{ $user->first_name . ' ' . $user->last_name . ' (' . $user->mobile . ')' }}
                    </option>
                  @endforeach
                @elseif ($type == 'doctor')
                  @foreach ($doctors as $doctor)
                    <option value="{{ $doctor->id }}">
                      {{ $doctor->first_name . ' ' . $doctor->last_name . ' (' . $doctor->mobile . ')' }}
                    </option>
                  @endforeach
                @endif
              </select>
            </div>
            @error('user_id')
              <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">تاریخ شروع</label>
            <input type="text" wire:model="blocked_at" class="form-control jalali-datepicker text-end"
              id="blocked_at" data-jdp>
            @error('blocked_at')
              <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">تاریخ پایان</label>
            <input type="text" wire:model="unblocked_at" class="form-control jalali-datepicker text-end"
              id="unblocked_at" data-jdp>
            @error('unblocked_at')
              <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-12 mb-3">
            <label class="form-label">دلیل</label>
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

  <style>
    .bg-gradient-primary {
      background: linear-gradient(90deg, #2e86c1, #3498db);
    }

    .card {
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .form-control,
    .form-select {
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      padding: 12px 15px;
      font-size: 14px;
      transition: all 0.3s ease;
      background: #fff;
      width: 100%;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: #2e86c1;
      box-shadow: 0 0 0 3px rgba(46, 134, 193, 0.2);
      background: #f8f9fa;
    }

    .form-label {
      color: #333;
      font-size: 14px;
      margin-bottom: 8px;
      display: block;
    }

    .btn-gradient-success {
      background: linear-gradient(90deg, #28a745, #34c759);
      border: none;
      color: #fff;
      font-weight: 600;
    }

    .btn-gradient-success:hover {
      background: linear-gradient(90deg, #34c759, #28a745);
      transform: translateY(-2px);
    }

    .btn-outline-light {
      border-color: rgba(255, 255, 255, 0.8);
    }

    .btn-outline-light:hover {
      background: rgba(255, 255, 255, 0.15);
      transform: translateY(-2px);
    }

    /* Select2 Styles */
    .select2-container {
      width: 100% !important;
    }

    .select2-container--default .select2-selection--single {
      height: 45px;
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      background-color: #fff;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
      line-height: 45px;
      padding-right: 15px;
      color: #333;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 43px;
      width: 30px;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
      background-color: #2e86c1;
    }

    .select2-dropdown {
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .select2-container--default .select2-results__option {
      padding: 8px 15px;
    }

    .select2-container--default .select2-results__option[aria-selected=true] {
      background-color: #f8f9fa;
    }

    /* Datepicker Styles */
    .jalali-datepicker {
      text-align: right !important;
    }

    .jalali-datepicker:focus {
      border-color: #2e86c1;
      box-shadow: 0 0 0 3px rgba(46, 134, 193, 0.2);
    }

    @media (max-width: 767px) {
      .card-header {
        flex-direction: column;
        gap: 1rem;
      }

      .btn-outline-light {
        width: 100%;
        justify-content: center;
      }
    }

    @media (max-width: 575px) {
      .card-body {
        padding: 1.5rem;
      }

      .btn-gradient-success {
        width: 100%;
        justify-content: center;
      }

      .form-control,
      .form-select {
        font-size: 13px;
        padding: 10px 12px;
      }

      .form-label {
        font-size: 13px;
      }
    }
  </style>

  <script>
    document.addEventListener('livewire:init', function() {
      function initializeSelect2() {
        $('#user_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%',
          data: [{
            id: '',
            text: 'انتخاب کنید'
          }]
        });
      }
      initializeSelect2();

      Livewire.on('select2:refresh', (event) => {
        const items = event.items || [];
        $('#user_id').select2('destroy');
        $('#user_id').empty().select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%',
          data: [{
              id: '',
              text: 'انتخاب کنید'
            },
            ...items.map(item => ({
              id: item.id,
              text: item.first_name + ' ' + item.last_name + ' (' + item.mobile + ')'
            }))
          ]
        });
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

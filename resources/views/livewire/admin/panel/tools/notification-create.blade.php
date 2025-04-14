<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden" style="background: #ffffff;">
    <div
      class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">افزودن اعلان جدید</h5>
      </div>
      <a href="{{ route('admin.panel.tools.notifications.index') }}"
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
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" wire:model="title" class="form-control" id="title" placeholder=" " required>
              <label for="title" class="form-label">عنوان</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <select wire:model="type" class="form-select" id="type">
                <option value="info">اطلاع‌رسانی</option>
                <option value="success">موفقیت</option>
                <option value="warning">هشدار</option>
                <option value="error">خطا</option>
              </select>
              <label for="type" class="form-label">نوع اعلان</label>
            </div>
            <div class="col-12 position-relative mt-5">
              <textarea wire:model="message" class="form-control" id="message" placeholder=" " rows="4" required></textarea>
              <label for="message" class="form-label">پیام</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <select wire:model="target_mode" class="form-select" id="target_mode">
                <option value="group">گروهی</option>
                <option value="single">تکی (شماره تلفن)</option>
                <option value="multiple">چندانتخابی</option>
              </select>
              <label for="target_mode" class="form-label">حالت هدف</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5" x-show="$wire.target_mode === 'group'">
              <select wire:model="target_group" class="form-select" id="target_group">
                <option value="all">همه</option>
                <option value="doctors">پزشکان</option>
                <option value="secretaries">منشی‌ها</option>
                <option value="patients">بیماران</option>
              </select>
              <label for="target_group" class="form-label">گروه هدف</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5" x-show="$wire.target_mode === 'single'">
              <input type="text" wire:model="single_phone" class="form-control" id="single_phone" placeholder=" "
                required>
              <label for="single_phone" class="form-label">شماره تلفن</label>
            </div>
            <div class="col-12 position-relative mt-5" x-show="$wire.target_mode === 'multiple'" wire:ignore>
              <select wire:model="selected_recipients" class="form-select select2" id="selected_recipients" multiple>
                <option value="">انتخاب کنید</option>
                @foreach ($allRecipients as $recipient)
                  <option value="{{ $recipient['id'] }}">{{ $recipient['text'] }}</option>
                @endforeach
              </select>
              <label for="selected_recipients" class="form-label">گیرندگان</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" data-jdp wire:model="start_at" class="form-control jalali-datepicker text-end"
                id="start_at" placeholder=" ">
              <label for="start_at" class="form-label">زمان شروع (اختیاری)</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="text" data-jdp wire:model="end_at" class="form-control jalali-datepicker text-end"
                id="end_at" placeholder=" ">
              <label for="end_at" class="form-label">زمان پایان (اختیاری)</label>
            </div>
            <div class="col-6 col-md-6 position-relative mt-5 d-flex align-items-center">
              <div class="form-check form-switch w-100 d-flex align-items-center">
                <input class="form-check-input" type="checkbox" id="is_active" wire:model="is_active">
                <label class="form-check-label fw-medium" for="is_active">
                  وضعیت: <span
                    class="px-2 text-{{ $is_active ? 'success' : 'danger' }}">{{ $is_active ? 'فعال' : 'غیرفعال' }}</span>
                </label>
              </div>
            </div>
          </div>

          <div class="text-end mt-4 w-100 d-flex justify-content-end">
            <button wire:click="store"
              class="btn my-btn-primary px-5 py-2 d-flex align-items-center gap-2 shadow-lg hover:shadow-xl transition-all">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M12 5v14M5 12h14" />
              </svg>
              افزودن اعلان
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <style>
    .bg-gradient-primary {
      background: linear-gradient(90deg, #6b7280, #374151);
    }

    .card {
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .form-control,
    .form-select {
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      padding: 12px 15px;
      font-size: 14px;
      transition: all 0.3s ease;
      height: 48px;
      background: #fafafa;
      width: 100%;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: #6b7280;
      box-shadow: 0 0 0 3px rgba(107, 114, 128, 0.2);
      background: #fff;
    }

    .form-label {
      position: absolute;
      top: -25px;
      right: 15px;
      color: #374151;
      font-size: 12px;
      background: #ffffff;
      padding: 0 5px;
      pointer-events: none;
    }

    .my-btn-primary {
      background: linear-gradient(90deg, #6b7280, #374151);
      border: none;
      color: white;
      font-weight: 600;
    }

    .my-btn-primary:hover {
      background: linear-gradient(90deg, #4b5563, #1f2937);
      transform: translateY(-2px);
    }

    .btn-outline-light {
      border-color: rgba(255, 255, 255, 0.8);
    }

    .btn-outline-light:hover {
      background: rgba(255, 255, 255, 0.15);
      transform: translateY(-2px);
    }

    .form-check-input {
      margin-top: 0;
      height: 20px;
      width: 20px;
      vertical-align: middle;
    }

    .form-check-label {
      margin-right: 25px;
      line-height: 1.5;
      vertical-align: middle;
    }

    .form-check-input:checked {
      background-color: #6b7280;
      border-color: #6b7280;
    }

    .animate-bounce {
      animation: bounce 1s infinite;
    }

    @keyframes bounce {

      0%,
      100% {
        transform: translateY(0);
      }

      50% {
        transform: translateY(-5px);
      }
    }

    .text-shadow {
      text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    }

    .select2-container {
      width: 100% !important;
    }

    .select2-container--default .select2-selection--single,
    .select2-container--default .select2-selection--multiple {
      height: 48px;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      background: #fafafa;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
      line-height: 46px;
      padding-right: 15px;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__rendered {
      padding: 5px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 46px;
    }

    .select2-dropdown {
      z-index: 1050 !important;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
    }

    .jalali-datepicker {
      z-index: 1050 !important;
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

      .col-md-6 {
        flex: 0 0 100%;
        max-width: 100%;
      }
    }

    @media (max-width: 575px) {
      .card-body {
        padding: 2rem 1.5rem;
      }

      .my-btn-primary {
        width: 100%;
        justify-content: center;
      }
    }
  </style>

  <script>
    document.addEventListener('livewire:init', function() {
      $('#selected_recipients').select2({
        dir: 'rtl',
        placeholder: 'انتخاب کنید',
        width: '100%'
      }).on('change', function() {
        @this.set('selected_recipients', $(this).val());
      });

      jalaliDatepicker.startWatch({
        minDate: "attr",
        maxDate: "attr",
        showTodayBtn: true,
        showEmptyBtn: true,
        time: true,
        zIndex: 1050,
        dateFormatter: function(unix) {
          return new Date(unix).toLocaleDateString('fa-IR', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
          });
        }
      });

      document.getElementById('start_at').addEventListener('change', function() {
        @this.set('start_at', this.value);
      });
      document.getElementById('end_at').addEventListener('change', function() {
        @this.set('end_at', this.value);
      });

      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });
    });
  </script>
</div>

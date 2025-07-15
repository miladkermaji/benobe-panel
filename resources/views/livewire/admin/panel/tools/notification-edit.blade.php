<script>
  function notificationForm() {
    return {
      mode: @entangle('target_mode'),
      recipients: [],
      selectedRecipients: @entangle('selected_recipients'),
      search: '',
      loading: false,
      init() {
        const self = this;
        $('#selected_recipients').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%',
          ajax: {
            url: '/admin/panel/tools/recipients-search',
            dataType: 'json',
            delay: 250,
            data: function(params) {
              return {
                q: params.term
              };
            },
            processResults: function(data) {
              return {
                results: data
              };
            },
            cache: true
          },
          minimumInputLength: 2
        }).on('change', function() {
          let val = $(this).val() || [];
          if (!Array.isArray(val)) val = [val];
          if ((self.selectedRecipients || []).join(',') !== val.join(',')) {
            self.selectedRecipients = val;
          }
        });
        this.$nextTick(() => {
          let arr = this.selectedRecipients || [];
          if (!Array.isArray(arr)) arr = [arr];
          $('#selected_recipients').val(arr).trigger('change');
        });
        this.$watch('selectedRecipients', value => {
          let arr = value || [];
          if (!Array.isArray(arr)) arr = [arr];
          if (($('#selected_recipients').val() || []).join(',') !== arr.join(',')) {
            $('#selected_recipients').val(arr).trigger('change');
          }
        });
      }
    }
  }
</script>
<div x-data="notificationForm()">
  <div class="container-fluid py-4" dir="rtl">
    <div class="card shadow-lg border-0 rounded-3 overflow-hidden" style="background: #ffffff;">
      <div
        class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            class="custom-animate-bounce">
            <path d="M5 12h14M12 5l7 7-7 7" />
          </svg>
          <h5 class="mb-0 fw-bold text-shadow">ویرایش اعلان</h5>
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
                <select x-model="mode" wire:model="target_mode" class="form-select" id="target_mode">
                  <option value="group">گروهی</option>
                  <option value="single">تکی (شماره تلفن)</option>
                  <option value="multiple">چندانتخابی</option>
                </select>
                <label for="target_mode" class="form-label">حالت هدف</label>
              </div>
              <div class="col-6 col-md-6 position-relative mt-5" x-show="mode === 'group'">
                <select wire:model="target_group" class="form-select" id="target_group">
                  <option value="all">همه</option>
                  <option value="doctors">پزشکان</option>
                  <option value="secretaries">منشی‌ها</option>
                  <option value="patients">بیماران</option>
                </select>
                <label for="target_group" class="form-label">گروه هدف</label>
              </div>
              <div class="col-6 col-md-6 position-relative mt-5" x-show="mode === 'single'">
                <input type="text" wire:model="single_phone" class="form-control" id="single_phone" placeholder=" "
                  required>
                <label for="single_phone" class="form-label">شماره تلفن</label>
              </div>
              <div class="col-12 position-relative mt-5" x-show="mode === 'multiple'" wire:ignore>
                <select id="selected_recipients" multiple></select>
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
              <button wire:click="update"
                class="btn my-btn-primary px-5 py-2 d-flex align-items-center gap-2 shadow-lg hover:shadow-xl transition-all">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2">
                  <path d="M12 5v14M5 12h14" />
                </svg>
                به‌روزرسانی اعلان
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>



    <script>
      document.addEventListener('livewire:init', function() {
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

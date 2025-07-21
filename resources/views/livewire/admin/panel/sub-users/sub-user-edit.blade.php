<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden" style="background: #ffffff;">
    <div
      class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="custom-animate-bounce">
          <path d="M5 12h14M12 5l7 7-7 7" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">ویرایش کاربر زیرمجموعه</h5>
      </div>
      <a href="{{ route('admin.panel.sub-users.index') }}"
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
            <div class="col-12 position-relative mt-5" wire:ignore>
              <label for="owner_type" class="form-label">نوع مالک</label>
              <select wire:model="owner_type" class="form-select" id="owner_type" required>
                <option value="">انتخاب کنید</option>
                @foreach ($ownerTypes as $type => $label)
                  <option value="{{ $type }}">{{ $label }}</option>
                @endforeach
              </select>
              @error('owner_type')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-12 position-relative mt-5" wire:ignore>
              <label for="owner_id" class="form-label">مالک</label>
              <select wire:model="owner_id" class="form-select select2" id="owner_id" required>
                <option value="">انتخاب کنید</option>
                @foreach ($owners as $owner)
                  <option value="{{ $owner->id }}">
                    @if ($owner_type === 'App\\Models\\Doctor')
                      {{ $owner->first_name . ' ' . $owner->last_name }}
                    @elseif ($owner_type === 'App\\Models\\Secretary')
                      {{ $owner->first_name . ' ' . $owner->last_name }}
                    @elseif ($owner_type === 'App\\Models\\Admin\\Manager')
                      {{ $owner->name }}
                    @elseif ($owner_type === 'App\\Models\\User')
                      {{ $owner->first_name . ' ' . $owner->last_name }}
                    @endif
                  </option>
                @endforeach
              </select>
              @error('owner_id')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-12 position-relative mt-5" wire:ignore>
              <select wire:model="status" class="form-select select2" id="status" required>
                <option value="active" {{ $status == 'active' ? 'selected' : '' }}>فعال</option>
                <option value="inactive" {{ $status == 'inactive' ? 'selected' : '' }}>غیرفعال</option>
              </select>
              <label for="status" class="form-label">وضعیت</label>
              @error('status')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
          </div>

          <div class="text-end mt-4 w-100 d-flex justify-content-end">
            <button wire:click="update"
              class="btn my-btn-primary px-5 py-2 d-flex align-items-center justify-content-center gap-2 shadow-lg hover:shadow-xl transition-all">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                <path d="M17 21v-8H7v8M7 3v5h8" />
              </svg>
              ذخیره
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>



  <script>
    document.addEventListener('livewire:init', function() {
      function initializeSelect2() {
        $('#owner_id').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });
        $('#status').select2({
          dir: 'rtl',
          placeholder: 'انتخاب کنید',
          width: '100%'
        });
      }

      initializeSelect2();

      $('#owner_type').on('change', function() {
        @this.set('owner_type', $(this).val());
      });
      $('#owner_id').on('change', function() {
        @this.set('owner_id', $(this).val());
      });
      $('#status').on('change', function() {
        @this.set('status', $(this).val());
      });
      // Set selected user if editing
      if (@this.user_id) {
        $.ajax({
          type: 'GET',
          url: '/admin/api/users/search',
          data: {
            q: ''
          },
          success: function(data) {
            var user = data.results.find(u => u.id == @this.user_id);
            if (user) {
              var option = new Option(user.text, user.id, true, true);
              $('#owner_id').append(option).trigger('change');
            }
          }
        });
      }

      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });

      // نمایش خطاها بعد از کلیک روی دکمه
      document.querySelector('[wire\\:click="update"]').addEventListener('click', function() {
        @this.call('update').catch((error) => {
          if (error instanceof Error && error.message.includes('validation')) {
            // خطاها به‌صورت خودکار توسط updated مدیریت می‌شن
          }
        });
      });
    });
  </script>
</div>

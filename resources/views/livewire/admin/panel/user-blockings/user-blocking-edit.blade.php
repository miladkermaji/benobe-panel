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
            <label class="form-label">نوع کاربر</label>
            <select wire:model="type" class="form-select" disabled>
              <option value="">انتخاب کنید</option>
              <option value="user" {{ $userBlocking->user_id ? 'selected' : '' }}>کاربر</option>
              <option value="doctor" {{ $userBlocking->doctor_id ? 'selected' : '' }}>پزشک</option>
            </select>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">کاربر</label>
            <select wire:model="user_id" class="form-select" disabled>
              <option value="">انتخاب کنید</option>
              @if ($userBlocking->user_id)
                @foreach ($users as $user)
                  <option value="{{ $user->id }}" {{ $userBlocking->user_id == $user->id ? 'selected' : '' }}>
                    {{ $user->first_name . ' ' . $user->last_name . ' (' . $user->mobile . ')' }}
                  </option>
                @endforeach
              @elseif ($userBlocking->doctor_id)
                @foreach ($doctors as $doctor)
                  <option value="{{ $doctor->id }}" {{ $userBlocking->doctor_id == $doctor->id ? 'selected' : '' }}>
                    {{ $doctor->first_name . ' ' . $doctor->last_name . ' (' . $doctor->mobile . ')' }}
                  </option>
                @endforeach
              @endif
            </select>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">تاریخ شروع</label>
            <input type="text" wire:model="blocked_at" class="form-control" id="blocked_at">
            @error('blocked_at')
              <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">تاریخ پایان</label>
            <input type="text" wire:model="unblocked_at" class="form-control" id="unblocked_at">
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
            <span class="ms-2">ذخیره تغییرات</span>
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    document.addEventListener('livewire:init', function() {
      // Initialize datepickers
      const blockedAtInput = document.getElementById('blocked_at');
      const unblockedAtInput = document.getElementById('unblocked_at');

      if (blockedAtInput) {
        new PDate(blockedAtInput, {
          format: 'YYYY/MM/DD',
          initialValue: false,
          autoClose: true
        });
      }

      if (unblockedAtInput) {
        new PDate(unblockedAtInput, {
          format: 'YYYY/MM/DD',
          initialValue: false,
          autoClose: true
        });
      }

      // Show alerts
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });
    });
  </script>
</div>

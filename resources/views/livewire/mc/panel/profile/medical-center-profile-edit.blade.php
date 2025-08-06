<div class="container-fluid py-2 mt-3" dir="rtl">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-8">
      <div class="card shadow-sm rounded-2">
        <div class="card-header bg-gradient-primary text-white p-3">
          <div class="d-flex align-items-center justify-content-between w-100">
            <div class="d-flex align-items-center gap-2">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                <circle cx="12" cy="7" r="4" />
              </svg>
              <h5 class="mb-0 fw-bold text-shadow">ویرایش پروفایل مرکز درمانی</h5>
            </div>
            <a href="{{ route('mc-panel') }}"
              class="btn btn-outline-light btn-sm rounded-pill px-3 py-1 d-flex align-items-center gap-1 hover:shadow-lg transition-all">
              <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <path d="M19 12H5M12 19l-7-7 7-7" />
              </svg>
              بازگشت
            </a>
          </div>
        </div>

        <div class="card-body p-4">
          <form wire:submit="update">
            <div class="row g-3">
              <!-- نام مرکز درمانی -->
              <div class="col-12 col-md-6 mb-3">
                <div class="form-group">
                  <label class="form-label fw-bold" for="name">نام مرکز درمانی *</label>
                  <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror"
                    id="name" placeholder="نام مرکز درمانی را وارد کنید">
                  @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- عنوان -->
              <div class="col-12 col-md-6 mb-3">
                <div class="form-group">
                  <label class="form-label fw-bold" for="title">عنوان</label>
                  <input type="text" wire:model="title" class="form-control @error('title') is-invalid @enderror"
                    id="title" placeholder="عنوان را وارد کنید">
                  @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- آدرس -->
              <div class="col-12">
                <div class="form-group">
                  <label class="form-label fw-bold" for="address">آدرس *</label>
                  <textarea wire:model="address" class="form-control @error('address') is-invalid @enderror" id="address"
                    placeholder="آدرس را وارد کنید" style="height: 100px"></textarea>
                  @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- شماره تلفن منشی -->
              <div class="col-12 col-md-6 mb-3">
                <div class="form-group">
                  <label class="form-label fw-bold" for="secretary_phone">شماره تلفن منشی *</label>
                  <input type="text" wire:model="secretary_phone"
                    class="form-control @error('secretary_phone') is-invalid @enderror" id="secretary_phone"
                    placeholder="شماره تلفن منشی را وارد کنید">
                  @error('secretary_phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- شماره تلفن -->
              <div class="col-12 col-md-6 mb-3">
                <div class="form-group">
                  <label class="form-label fw-bold" for="phone_number">شماره تلفن *</label>
                  <input type="text" wire:model="phone_number"
                    class="form-control @error('phone_number') is-invalid @enderror" id="phone_number"
                    placeholder="شماره تلفن را وارد کنید">
                  @error('phone_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- کد پستی -->
              <div class="col-12 col-md-6 mb-3">
                <div class="form-group">
                  <label class="form-label fw-bold" for="postal_code">کد پستی</label>
                  <input type="text" wire:model="postal_code"
                    class="form-control @error('postal_code') is-invalid @enderror" id="postal_code"
                    placeholder="کد پستی را وارد کنید">
                  @error('postal_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- کد سیام -->
              <div class="col-12 col-md-6 mb-3">
                <div class="form-group">
                  <label class="form-label fw-bold" for="siam_code">کد سیام</label>
                  <input type="text" wire:model="siam_code"
                    class="form-control @error('siam_code') is-invalid @enderror" id="siam_code"
                    placeholder="کد سیام را وارد کنید">
                  @error('siam_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- هزینه مشاوره -->
              <div class="col-12 col-md-6 mb-3">
                <div class="form-group">
                  <label class="form-label fw-bold" for="consultation_fee">هزینه مشاوره (تومان)</label>
                  <input type="number" wire:model="consultation_fee"
                    class="form-control @error('consultation_fee') is-invalid @enderror" id="consultation_fee"
                    placeholder="هزینه مشاوره را وارد کنید" step="0.01" min="0">
                  @error('consultation_fee')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- تعرفه نسخه -->
              <div class="col-12 col-md-6 mb-3">
                <div class="form-group">
                  <label class="form-label fw-bold" for="prescription_tariff">تعرفه نسخه (تومان)</label>
                  <input type="number" wire:model="prescription_tariff"
                    class="form-control @error('prescription_tariff') is-invalid @enderror" id="prescription_tariff"
                    placeholder="تعرفه نسخه را وارد کنید" step="0.01" min="0">
                  @error('prescription_tariff')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- توضیحات -->
              <div class="col-12">
                <div class="form-group">
                  <label class="form-label fw-bold" for="description">توضیحات</label>
                  <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" id="description"
                    placeholder="توضیحات را وارد کنید" style="height: 120px"></textarea>
                  @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- وضعیت فعال -->
              <div class="col-12">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" wire:model="is_active" id="is_active">
                  <label class="form-check-label fw-bold" for="is_active">
                    مرکز درمانی فعال است
                  </label>
                </div>
                @error('is_active')
                  <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
              </div>

              <!-- دکمه‌های عملیات -->
              <div class="col-12">
                <div class="d-flex gap-2 justify-content-end">
                  <a href="{{ route('mc-panel') }}" class="btn btn-outline-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2" class="me-1">
                      <path d="M18 6L6 18M6 6l12 12" />
                    </svg>
                    انصراف
                  </a>
                  <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove>
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" class="me-1">
                        <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                        <polyline points="17,21 17,13 7,13 7,21" />
                        <polyline points="7,3 7,8 15,8" />
                      </svg>
                      ذخیره تغییرات
                    </span>
                    <span wire:loading>
                      <div class="spinner-border spinner-border-sm me-1" role="status">
                        <span class="visually-hidden">در حال بارگذاری...</span>
                      </div>
                      در حال ذخیره...
                    </span>
                  </button>
                </div>
              </div>
            </div>
          </form>
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

    Livewire.on('show-toastr', (data) => {
      const toastrData = Array.isArray(data) ? data[0] : data;
      toastr.clear();
      toastr.options.rtl = true;
      if (toastrData.type === 'success') {
        toastr.success(toastrData.message);
      } else if (toastrData.type === 'warning') {
        toastr.warning(toastrData.message);
      } else if (toastrData.type === 'error') {
        toastr.error(toastrData.message);
      }
    });
  });
</script>

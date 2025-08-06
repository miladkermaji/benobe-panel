<div class="container-fluid py-2 mt-3" dir="rtl">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-8">
      <div class="card shadow-sm rounded-2">
        <div class="card-header bg-gradient-primary text-white p-3">
          <div class="d-flex align-items-center justify-content-between">
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
              <svg style="transform: rotate(180deg)" width="14" height="14" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <path d="M17 21v-8H7v8M7 3v5h8" />
              </svg>
              بازگشت
            </a>
          </div>
        </div>

        <div class="card-body p-4">
          <form wire:submit="update">
            <div class="row g-3">
              <!-- نام مرکز درمانی -->
              <div class="col-12 col-md-6">
                <div class="form-floating">
                  <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror"
                    id="name" placeholder="نام مرکز درمانی">
                  <label for="name">نام مرکز درمانی *</label>
                  @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- عنوان -->
              <div class="col-12 col-md-6">
                <div class="form-floating">
                  <input type="text" wire:model="title" class="form-control @error('title') is-invalid @enderror"
                    id="title" placeholder="عنوان">
                  <label for="title">عنوان</label>
                  @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- آدرس -->
              <div class="col-12">
                <div class="form-floating">
                  <textarea wire:model="address" class="form-control @error('address') is-invalid @enderror" id="address"
                    placeholder="آدرس" style="height: 100px"></textarea>
                  <label for="address">آدرس *</label>
                  @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- شماره تلفن منشی -->
              <div class="col-12 col-md-6">
                <div class="form-floating">
                  <input type="text" wire:model="secretary_phone"
                    class="form-control @error('secretary_phone') is-invalid @enderror" id="secretary_phone"
                    placeholder="شماره تلفن منشی">
                  <label for="secretary_phone">شماره تلفن منشی *</label>
                  @error('secretary_phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- شماره تلفن -->
              <div class="col-12 col-md-6">
                <div class="form-floating">
                  <input type="text" wire:model="phone_number"
                    class="form-control @error('phone_number') is-invalid @enderror" id="phone_number"
                    placeholder="شماره تلفن">
                  <label for="phone_number">شماره تلفن *</label>
                  @error('phone_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- کد پستی -->
              <div class="col-12 col-md-6">
                <div class="form-floating">
                  <input type="text" wire:model="postal_code"
                    class="form-control @error('postal_code') is-invalid @enderror" id="postal_code"
                    placeholder="کد پستی">
                  <label for="postal_code">کد پستی</label>
                  @error('postal_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- کد سیام -->
              <div class="col-12 col-md-6">
                <div class="form-floating">
                  <input type="text" wire:model="siam_code"
                    class="form-control @error('siam_code') is-invalid @enderror" id="siam_code" placeholder="کد سیام">
                  <label for="siam_code">کد سیام</label>
                  @error('siam_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- هزینه مشاوره -->
              <div class="col-12 col-md-6">
                <div class="form-floating">
                  <input type="number" wire:model="consultation_fee"
                    class="form-control @error('consultation_fee') is-invalid @enderror" id="consultation_fee"
                    placeholder="هزینه مشاوره" step="0.01" min="0">
                  <label for="consultation_fee">هزینه مشاوره (تومان)</label>
                  @error('consultation_fee')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- تعرفه نسخه -->
              <div class="col-12 col-md-6">
                <div class="form-floating">
                  <input type="number" wire:model="prescription_tariff"
                    class="form-control @error('prescription_tariff') is-invalid @enderror" id="prescription_tariff"
                    placeholder="تعرفه نسخه" step="0.01" min="0">
                  <label for="prescription_tariff">تعرفه نسخه (تومان)</label>
                  @error('prescription_tariff')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- توضیحات -->
              <div class="col-12">
                <div class="form-floating">
                  <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" id="description"
                    placeholder="توضیحات" style="height: 120px"></textarea>
                  <label for="description">توضیحات</label>
                  @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- وضعیت فعال -->
              <div class="col-12">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" wire:model="is_active" id="is_active">
                  <label class="form-check-label" for="is_active">
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

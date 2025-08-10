@push('styles')
  <link rel="stylesheet" href="{{ asset('admin-assets/css/panel/doctor/doctor.css') }}">
@endpush

<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
    <div class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between gap-3">
      <div class="d-flex align-items-center gap-3 mb-2">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="custom-animate-bounce">
          <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
          <path d="m18.5 2.5 3 3L12 15l-4 1 1-4 9.5-9.5z" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">ویرایش سوال متداول</h5>
      </div>
      <a href="{{ route('admin.panel.faqs.index') }}"
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
          <!-- فرم -->
          <div class="row g-4">
            <div class="col-12 position-relative mt-5">
              <input type="text" wire:model="question" class="form-control" id="question" placeholder=" " required>
              <label for="question" class="form-label">سوال</label>
              @error('question')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-12 position-relative mt-5">
              <textarea wire:model="answer" class="form-control" id="answer" rows="6" placeholder=" " required></textarea>
              <label for="answer" class="form-label">پاسخ</label>
              @error('answer')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-6 col-md-6 position-relative mt-5">
              <select wire:model="category" class="form-select" id="category">
                <option value="citizens">سؤالات متداول برای شهروندان</option>
                <option value="doctors">سؤالات متداول برای پزشکان</option>
              </select>
              <label for="category" class="form-label">دسته‌بندی</label>
              @error('category')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-6 col-md-6 position-relative mt-5">
              <input type="number" wire:model="order" class="form-control" id="order" placeholder=" "
                min="0">
              <label for="order" class="form-label">ترتیب نمایش</label>
              @error('order')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-12 position-relative mt-5">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" wire:model="is_active" id="is_active">
                <label class="form-check-label" for="is_active">
                  فعال
                </label>
              </div>
              @error('is_active')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <!-- دکمه‌های عملیات -->
            <div class="col-12 mt-5">
              <div class="d-flex gap-3 justify-content-end">
                <button type="button" wire:click="cancel" class="btn btn-outline-secondary px-4">
                  انصراف
                </button>
                <button type="button" wire:click="save" wire:loading.attr="disabled"
                  class="btn btn-primary px-4 d-flex align-items-center gap-2">
                  <span wire:loading.remove wire:target="save">ذخیره تغییرات</span>
                  <span wire:loading wire:target="save">
                    <svg class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></svg>
                    در حال ذخیره...
                  </span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

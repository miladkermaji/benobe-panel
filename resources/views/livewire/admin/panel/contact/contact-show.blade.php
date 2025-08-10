<div class="container-fluid py-4" dir="rtl">
  <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
    <div class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between gap-3">
      <div class="d-flex align-items-center gap-3 mb-2">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="custom-animate-bounce">
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
          <circle cx="12" cy="12" r="3" />
        </svg>
        <h5 class="mb-0 fw-bold text-shadow">مشاهده پیام تماس</h5>
      </div>
      <a href="{{ route('admin.panel.contact.index') }}"
        class="btn btn-outline-light btn-sm rounded-pill px-4 d-flex align-items-center gap-2 hover:shadow-lg transition-all">
        <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        بازگشت
      </a>
    </div>

    <div class="card-body p-4">
      <div class="row">
        <!-- پیام اصلی -->
        <div class="col-12 col-lg-6">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-light">
              <h6 class="mb-0 fw-bold">اطلاعات فرستنده</h6>
            </div>
            <div class="card-body">
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label fw-medium">نام و نام خانوادگی:</label>
                  <p class="form-control-plaintext">{{ $contact->full_name }}</p>
                </div>
                <div class="col-12">
                  <label class="form-label fw-medium">ایمیل:</label>
                  <p class="form-control-plaintext">{{ $contact->email }}</p>
                </div>
                <div class="col-12">
                  <label class="form-label fw-medium">شماره تماس:</label>
                  <p class="form-control-plaintext">{{ $contact->full_phone }}</p>
                </div>
                <div class="col-12">
                  <label class="form-label fw-medium">موضوع:</label>
                  <p class="form-control-plaintext">{{ $contact->subject }}</p>
                </div>
                <div class="col-12">
                  <label class="form-label fw-medium">پیام:</label>
                  <div class="border rounded p-3 bg-light">
                    {{ $contact->message }}
                  </div>
                </div>
                <div class="col-12">
                  <label class="form-label fw-medium">تاریخ ارسال:</label>
                  <p class="form-control-plaintext">{{ $contact->created_at->format('Y/m/d H:i') }}</p>
                </div>
                @if ($contact->replied_at)
                  <div class="col-12">
                    <label class="form-label fw-medium">تاریخ پاسخ:</label>
                    <p class="form-control-plaintext">{{ $contact->replied_at->format('Y/m/d H:i') }}</p>
                  </div>
                @endif
              </div>
            </div>
          </div>
        </div>

        <!-- پاسخ و وضعیت -->
        <div class="col-12 col-lg-6">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-light">
              <h6 class="mb-0 fw-bold">مدیریت پیام</h6>
            </div>
            <div class="card-body">
              <!-- وضعیت -->
              <div class="mb-4">
                <label class="form-label fw-medium">وضعیت:</label>
                <div class="d-flex gap-2 flex-wrap">
                  <button type="button" wire:click="updateStatus('new')"
                    class="btn btn-sm {{ $status === 'new' ? 'btn-danger' : 'btn-outline-danger' }}">
                    جدید
                  </button>
                  <button type="button" wire:click="updateStatus('read')"
                    class="btn btn-sm {{ $status === 'read' ? 'btn-warning' : 'btn-outline-warning' }}">
                    خوانده شده
                  </button>
                  <button type="button" wire:click="updateStatus('replied')"
                    class="btn btn-sm {{ $status === 'replied' ? 'btn-success' : 'btn-outline-success' }}">
                    پاسخ داده شده
                  </button>
                  <button type="button" wire:click="updateStatus('closed')"
                    class="btn btn-sm {{ $status === 'closed' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                    بسته شده
                  </button>
                </div>
              </div>

              <!-- پاسخ -->
              <div class="mb-4">
                <label for="adminReply" class="form-label fw-medium">پاسخ:</label>
                <textarea wire:model="adminReply" id="adminReply" rows="8"
                  class="form-control @error('adminReply') is-invalid @enderror" placeholder="پاسخ خود را اینجا بنویسید..."></textarea>
                @error('adminReply')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <!-- دکمه‌های عملیات -->
              <div class="d-flex gap-3 justify-content-end">
                <button type="button" wire:click="backToList" class="btn btn-outline-secondary px-4">
                  بازگشت
                </button>
                <button type="button" wire:click="saveReply" wire:loading.attr="disabled"
                  class="btn btn-primary px-4 d-flex align-items-center gap-2">
                  <span wire:loading.remove wire:target="saveReply">ذخیره پاسخ</span>
                  <span wire:loading wire:target="saveReply">
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
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });
    });
  </script>

</div>

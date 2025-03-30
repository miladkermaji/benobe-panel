<div class="container-fluid py-1" wire:init="loadInitialData" dir="rtl">
  <!-- هدر -->
  <div
    class="glass-header text-white p-3 rounded-3 mb-5 shadow-lg d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div class="d-flex align-items-center flex-grow-1 gap-3 header-title">
      <h1 class="m-0 h3 font-thin">مدیریت نقشه سایت</h1>
      <a href="{{ route('admin.tools.sitemap.settings') }}"
        class="btn btn-gradient-primary px-3 py-1 text-white d-flex align-items-center gap-1" style="font-size: 0.9rem;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="animate-spin-slow">
          <path
            d="M12 2a10 10 0 0 0-10 10c0 4.42 2.87 8.17 6.84 9.5M12 2v4m10 6a10 10 0 0 1-10 10c-4.42 0-8.17-2.87-9.5-6.84M22 12h-4m-6 10v-4M2 12a10 10 0 0 0 10-10" />
        </svg>
        <span>تنظیمات پیمایش</span>
      </a>
    </div>
    <div class="d-flex gap-2 flex-shrink-0 flex-wrap justify-content-center buttons-container">
      <button wire:click="crawlSite" class="btn btn-gradient-info px-4 py-2 d-flex align-items-center gap-2"
        @if ($isCrawling) disabled @endif>
        <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path d="M21 10H3m18-4H3m18 8H3m18 4H3" />
        </svg>
        <span wire:loading wire:target="crawlSite">در حال پیمایش...</span>
        <span wire:loading.remove wire:target="crawlSite">پیمایش سایت</span>
      </button>
      @if ($isCrawling)
        <button wire:click="stopCrawl" class="btn btn-gradient-danger px-4 py-2 d-flex align-items-center gap-2">
          <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2">
            <path d="M18 6L6 18M6 6l12 12" />
          </svg>
          <span>توقف پیمایش</span>
        </button>
      @endif
      <button wire:click="generateSitemap" class="btn btn-gradient-success px-4 py-2 d-flex align-items-center gap-2">
        <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path d="M12 5v14M5 12h14" />
        </svg>
        <span>تولید نقشه سایت</span>
      </button>
      @if ($isGenerated)
        <a href="{{ route('admin.tools.sitemap.download') }}"
          class="btn btn-gradient-primary px-4 py-2 d-flex align-items-center gap-2">
          <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2">
            <path d="M12 15V3m0 12l-4-4m4 4l4-4M4 19h16" />
          </svg>
          <span>دانلود</span>
        </a>
      @endif
      <button wire:click="deleteSelected" class="btn btn-gradient-danger px-4 py-2 d-flex align-items-center gap-2"
        @if (empty($selectedRows)) disabled @endif>
        <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none"
          stroke="currentColor" stroke-width="2">
          <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
        </svg>
        <span>حذف انتخاب‌شده‌ها</span>
      </button>
    </div>
  </div>

  <!-- نوار پیشرفت و URLها -->
  <div class="mb-5" wire:poll.1000ms="updateCrawlProgress"
    @if (!$isCrawling) style="display: none;" @endif>
    <div class="progress rounded-pill" style="height: 30px; background: #e5e7eb; direction: rtl;">
      <div class="progress-bar bg-gradient-info text-dark fw-bold" role="progressbar"
        style="width: {{ 100 - $crawlProgress }}%; direction: ltr;" aria-valuenow="{{ $crawlProgress }}"
        aria-valuemin="0" aria-valuemax="100">
        {{ number_format($crawlProgress, 1) }}%
      </div>
    </div>
    <div class="card mt-3 shadow-sm border-0"
      style="max-height: 250px; overflow-y: auto; background: #f9f9f9; border: 1px solid #e5e7eb; border-radius: 8px;">
      <div class="card-body p-3">
        <h6 class="fw-bold mb-3 text-right">آدرس‌های در حال بررسی</h6>
        <ul class="list-unstyled mb-0" dir="ltr">
          @forelse ($crawlLogs as $log)
            <li class="mb-2 d-flex justify-content-between align-items-center">
              <span class="text-truncate" style="max-width: 70%;">{{ $log['url'] }}</span>
              <span
                class="badge {{ $log['status'] === 'crawled' ? 'bg-success' : ($log['status'] === 'failed' ? 'bg-danger' : 'bg-warning') }} text-white">
                {{ $log['status'] === 'crawled' ? 'بررسی شد' : ($log['status'] === 'failed' ? 'خطا' : 'در انتظار') }}
              </span>
            </li>
          @empty
            <li class="text-center text-muted">هنوز آدرس‌ای بررسی نشده است.</li>
          @endforelse
        </ul>
      </div>
    </div>
  </div>

  <!-- فرم افزودن URL -->
  <div class="container-fluid px-0 mb-5">
    <div class="bg-light p-4 rounded-3 shadow-sm border">
      <div class="row g-3 align-items-end">
        <div class="col-md-5">
          <label class="form-label fw-bold text-dark">آدرس URL</label>
          <input type="text" wire:model="newUrl" class="form-control bg-white border-dark text-dark"
            placeholder="https://example.com/page">
        </div>
        <div class="col-md-2">
          <label class="form-label fw-bold text-dark">اولویت</label>
          <select wire:model="newPriority" class="form-select bg-white border-dark text-dark custom-select">
            @for ($i = 1.0; $i >= 0.1; $i -= 0.1)
              <option value="{{ number_format($i, 1) }}">{{ number_format($i, 1) }}</option>
            @endfor
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-bold text-dark">فرکانس تغییر</label>
          <select wire:model="newFrequency" class="form-select bg-white border-dark text-dark custom-select">
            <option value="always">همیشه</option>
            <option value="hourly">ساعتی</option>
            <option value="daily">روزانه</option>
            <option value="weekly">هفتگی</option>
            <option value="monthly">ماهانه</option>
            <option value="yearly">سالانه</option>
            <option value="never">هرگز</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-bold text-dark">نوع</label>
          <select wire:model="newType" class="form-select bg-white border-dark text-dark custom-select">
            <option value="page">صفحه</option>
            <option value="image">تصویر</option>
            <option value="video">ویدیو</option>
          </select>
        </div>
        <div class="col-md-1">
          <button wire:click="addUrl" class="btn btn-gradient-success w-100 py-2 text-white">افزودن</button>
        </div>
      </div>
    </div>
  </div>

  <!-- جدول URLها -->
  <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
    <div class="card shadow-sm border-0">
      <div class="card-body p-0">
        <div class="table-responsive text-nowrap">
          <table class="table table-bordered table-hover w-100 m-0" dir="rtl">
            <thead class="glass-header text-white">
              <tr>
                <th class="text-center align-middle" style="width: 50px;">
                  <input type="checkbox" wire:model.live="selectAll" class="form-check-input m-0">
                </th>
                <th class="text-center align-middle">آدرس</th>
                <th class="text-center align-middle" style="width: 100px;">اولویت</th>
                <th class="text-center align-middle" style="width: 150px;">فرکانس تغییر</th>
                <th class="text-center align-middle" style="width: 100px;">نوع</th>
                <th class="text-center align-middle" style="width: 100px;">وضعیت</th>
                <th class="text-center align-middle" style="width: 100px;">عملیات</th>
              </tr>
            </thead>
            <tbody>
              @if ($readyToLoad)
                @forelse ($urls as $index => $url)
                  <tr>
                    <td class="text-center align-middle">
                      <input type="checkbox" wire:model.live="selectedRows" value="{{ $url['id'] }}"
                        class="form-check-input m-0">
                    </td>
                    <td class="text-center align-middle">{{ $url['url'] }}</td>
                    <td class="text-center align-middle">{{ $url['priority'] }}</td>
                    <td class="text-center align-middle">{{ $url['frequency'] }}</td>
                    <td class="text-center align-middle">{{ $url['type'] }}</td>
                    <td class="text-center align-middle">
                      <button wire:click="toggleStatus({{ $url['id'] }})"
                        class="badge {{ $url['is_active'] ? 'bg-success' : 'bg-danger' }} border-0 cursor-pointer text-white">
                        {{ $url['is_active'] ? 'فعال' : 'غیرفعال' }}
                      </button>
                    </td>
                    <td class="text-center align-middle">
                      <button wire:click="confirmDelete({{ $url['id'] }})"
                        class="btn btn-gradient-danger rounded-pill px-3">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2">
                          <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                        </svg>
                      </button>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="text-center py-5 text-dark">هیچ URLای ثبت نشده است.</td>
                  </tr>
                @endforelse
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- پیش‌نمایش نقشه سایت -->
  @if ($previewXml)
    <div class="container-fluid px-0 mb-5">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h5 class="fw-bold mb-3 text-right">پیش‌نمایش نقشه سایت</h5>
          <pre class="vscode-preview p-3 rounded-3"
            style="max-height: 300px; overflow-y: auto; direction: ltr; text-align: left; font-family: 'Courier New', monospace; font-size: 14px; background: #1e1e1e; color: #d4d4d4;">
                        {{ $previewXml }}
                    </pre>
        </div>
      </div>
    </div>
  @endif
  <link rel="stylesheet" href="{{ asset('admin-assets/css/panel/tools/site-map/sitemap-manager.css') }}">


  <script>
    document.addEventListener('livewire:init', function() {
      Livewire.on('show-alert', (event) => {
        toastr[event.type](event.message);
      });

      Livewire.on('confirm-delete', (event) => {
        Swal.fire({
          title: 'حذف مسیر',
          text: 'آیا مطمئن هستید که می‌خواهید این مسیر را حذف کنید؟',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#ef4444',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'بله، حذف کن',
          cancelButtonText: 'خیر'
        }).then((result) => {
          if (result.isConfirmed) {
            Livewire.dispatch('deleteUrlConfirmed', {
              id: event.id
            });
          }
        });
      });

      Livewire.on('confirm-delete-selected', () => {
        Swal.fire({
          title: 'حذف مسیرهای انتخاب‌شده',
          text: 'آیا مطمئن هستید که می‌خواهید مسیرهای انتخاب‌شده را حذف کنید؟',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#ef4444',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'بله، حذف کن',
          cancelButtonText: 'خیر'
        }).then((result) => {
          if (result.isConfirmed) {
            Livewire.dispatch('deleteSelectedConfirmed');
          }
        });
      });
    });
  </script>
</div>

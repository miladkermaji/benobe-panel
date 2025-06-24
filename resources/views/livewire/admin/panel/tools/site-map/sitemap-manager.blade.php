<div class="container-fluid py-2" dir="rtl" wire:init="loadInitialData">
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
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 10H3m18-4H3m18 8H3m18 4H3" />
        </svg>
        <span wire:loading wire:target="crawlSite">در حال پیمایش...</span>
        <span wire:loading.remove wire:target="crawlSite">پیمایش سایت</span>
      </button>
      @if ($isCrawling)
        <button wire:click="stopCrawl" class="btn btn-gradient-danger px-4 py-2 d-flex align-items-center gap-2">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 6L6 18M6 6l12 12" />
          </svg>
          <span>توقف پیمایش</span>
        </button>
      @endif
      <button wire:click="generateSitemap" class="btn btn-gradient-success px-4 py-2 d-flex align-items-center gap-2">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 5v14M5 12h14" />
        </svg>
        <span>تولید نقشه سایت</span>
      </button>
      @if ($isGenerated)
        <a href="{{ route('admin.tools.sitemap.download') }}"
          class="btn btn-gradient-primary px-4 py-2 d-flex align-items-center gap-2 text-white">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 15V3m0 12l-4-4m4 4l4-4M4 19h16" />
          </svg>
          <span>دانلود</span>
        </a>
      @endif
      <button wire:click="deleteSelected" class="btn btn-gradient-danger px-4 py-2 d-flex align-items-center gap-2"
        @if (empty($selectedRows)) disabled @endif>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
    <!-- Desktop View -->
    <div class="d-none d-lg-block">
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

    <!-- Mobile & Tablet View -->
    <div class="d-lg-none">
      <button type="button"
        class="btn btn-gradient-primary w-100 d-flex align-items-center justify-content-between gap-2"
        data-bs-toggle="modal" data-bs-target="#addUrlModal">
        <span>افزودن URL جدید</span>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
          stroke-width="2">
          <path d="M12 5v14M5 12h14" />
        </svg>
      </button>
    </div>
  </div>

  <!-- Modal -->
  <div wire:ignore class="modal fade" id="addUrlModal" tabindex="-1" aria-labelledby="addUrlModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addUrlModalLabel">افزودن URL جدید</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="d-flex flex-column gap-3">
            <div>
              <label class="form-label fw-bold text-dark">آدرس URL</label>
              <input type="text" wire:model="newUrl" class="form-control bg-white border-dark text-dark"
                placeholder="https://example.com/page">
            </div>
            <div>
              <label class="form-label fw-bold text-dark">اولویت</label>
              <select wire:model="newPriority" class="form-select bg-white border-dark text-dark custom-select">
                @for ($i = 1.0; $i >= 0.1; $i -= 0.1)
                  <option value="{{ number_format($i, 1) }}">{{ number_format($i, 1) }}</option>
                @endfor
              </select>
            </div>
            <div>
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
            <div>
              <label class="form-label fw-bold text-dark">نوع</label>
              <select wire:model="newType" class="form-select bg-white border-dark text-dark custom-select">
                <option value="page">صفحه</option>
                <option value="image">تصویر</option>
                <option value="video">ویدیو</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
          <button type="button" class="btn btn-gradient-success" wire:click="addUrl"
            data-bs-dismiss="modal">افزودن</button>
        </div>
      </div>
    </div>
  </div>

  <!-- جدول URLها -->
  <div class="container-fluid px-0 mt-3">
    <div class="card shadow-sm">
      <div class="card-body p-0">
        <!-- Desktop View -->
        <div class="d-none d-lg-block">
          <div class="table-responsive text-nowrap">
            <table class="table table-bordered table-hover w-100 m-0">
              <thead class="glass-header text-white">
                <tr>
                  <th class="text-center align-middle" style="width: 50px;">
                    <div class="d-flex justify-content-center">
                      <input type="checkbox" wire:model.live="selectAll" class="form-check-input m-0 align-middle">
                    </div>
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
                        <div class="d-flex justify-content-center">
                          <input type="checkbox" wire:model.live="selectedRows" value="{{ $url['id'] }}"
                            class="form-check-input m-0 align-middle">
                        </div>
                      </td>
                      <td class="text-center align-middle">{{ $url['url'] }}</td>
                      <td class="text-center align-middle">{{ $url['priority'] }}</td>
                      <td class="text-center align-middle">{{ $url['frequency'] }}</td>
                      <td class="text-center align-middle">{{ $url['type'] }}</td>
                      <td class="text-center align-middle">
                        <div class="form-check form-switch d-flex justify-content-center">
                          <input class="form-check-input" type="checkbox" role="switch"
                            wire:click="toggleStatus({{ $url['id'] }})" @checked($url['is_active'])>
                        </div>
                      </td>
                      <td class="text-center align-middle">
                        <button wire:click="confirmDelete({{ $url['id'] }})"
                          class="btn btn-gradient-danger rounded-circle p-2 d-flex align-items-center justify-content-center">
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                          </svg>
                        </button>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="7" class="text-center py-5">
                        <div class="d-flex justify-content-center align-items-center flex-column">
                          <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" class="text-muted mb-3">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                          </svg>
                          <p class="text-muted fw-medium">هیچ URLای ثبت نشده است.</p>
                        </div>
                      </td>
                    </tr>
                  @endforelse
                @else
                  <tr>
                    <td colspan="7" class="text-center py-5">در حال بارگذاری URLها...</td>
                  </tr>
                @endif
              </tbody>
            </table>
          </div>
        </div>

        <!-- Mobile & Tablet View -->
        <div class="d-lg-none">
          @if ($readyToLoad)
            @forelse($urls as $url)
              <div class="card mb-3 shadow-sm">
                <div class="card-body d-flex flex-column gap-2">
                  <div class="d-flex align-items-center justify-content-between">
                    <div class="form-check">
                      <input type="checkbox" wire:model.live="selectedRows" value="{{ $url['id'] }}"
                        class="form-check-input">
                    </div>
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" role="switch"
                        wire:click="toggleStatus({{ $url['id'] }})" @checked($url['is_active'])>
                    </div>
                  </div>
                  <div class="d-flex flex-column gap-1">
                    <div class="d-flex justify-content-between align-items-center">
                      <span class="text-muted small">آدرس:</span>
                      <span class="fw-medium">{{ $url['url'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                      <span class="text-muted small">اولویت:</span>
                      <span class="fw-medium">{{ $url['priority'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                      <span class="text-muted small">فرکانس تغییر:</span>
                      <span class="fw-medium">{{ $url['frequency'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                      <span class="text-muted small">نوع:</span>
                      <span class="fw-medium">{{ $url['type'] }}</span>
                    </div>
                  </div>
                  <div class="d-flex align-items-center gap-2 justify-content-end mt-2">
                    <button wire:click="confirmDelete({{ $url['id'] }})"
                      class="btn btn-gradient-danger rounded-circle p-1 d-flex align-items-center justify-content-center">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            @empty
              <div class="text-center text-muted p-3">
                <div class="d-flex justify-content-center align-items-center flex-column">
                  <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" class="text-muted mb-3">
                    <path d="M5 12h14M12 5l7 7-7 7" />
                  </svg>
                  <p class="text-muted fw-medium">هیچ URLای ثبت نشده است.</p>
                </div>
              </div>
            @endforelse
          @else
            <div class="text-center text-muted p-3">
              در حال بارگذاری URLها...
            </div>
          @endif
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4 px-4 flex-wrap gap-3">
          <div class="text-muted">
            @if (is_array($urls))
              نمایش {{ count($urls) }} ردیف
            @else
              نمایش {{ $urls ? $urls->firstItem() : 0 }} تا
              {{ $urls ? $urls->lastItem() : 0 }} از {{ $urls ? $urls->total() : 0 }} ردیف
            @endif
          </div>
          @if (!is_array($urls) && $urls && $urls->hasPages())
            {{ $urls->links('livewire::bootstrap') }}
          @endif
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

  <style>
    :root {
      --primary-color: #0ea5e9;
      --primary-dark: #0284c7;
      --success-color: #22c55e;
      --success-dark: #16a34a;
      --danger-color: #ef4444;
      --danger-dark: #dc2626;
      --warning-color: #f59e0b;
      --warning-dark: #d97706;
      --info-color: #3b82f6;
      --info-dark: #2563eb;
    }

    .glass-header {
      background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    }

    .btn-gradient-primary {
      background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
      border: none;
      color: white;
      transition: all 0.3s ease;
    }

    .btn-gradient-success {
      background: linear-gradient(135deg, var(--success-color), var(--success-dark));
      border: none;
      color: white;
      transition: all 0.3s ease;
    }

    .btn-gradient-danger {
      background: linear-gradient(135deg, var(--danger-color), var(--danger-dark));
      border: none;
      color: white;
      transition: all 0.3s ease;
    }

    .btn-gradient-info {
      background: linear-gradient(135deg, var(--info-color), var(--info-dark));
      border: none;
      color: white;
      transition: all 0.3s ease;
    }

    .btn-gradient-warning {
      background: linear-gradient(135deg, var(--warning-color), var(--warning-dark));
      border: none;
      color: white;
      transition: all 0.3s ease;
    }

    .btn-gradient-primary:hover,
    .btn-gradient-success:hover,
    .btn-gradient-danger:hover,
    .btn-gradient-info:hover,
    .btn-gradient-warning:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      color: white;
    }

    .form-check-input:checked {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
    }

    .form-check-input:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.25rem rgba(14, 165, 233, 0.25);
    }

    .form-switch .form-check-input {
      width: 3em;
      height: 1.5em;
    }

    .form-switch .form-check-input:checked {
      background-color: var(--success-color);
      border-color: var(--success-color);
    }

    .table {
      background: white;
    }

    .table thead th {
      border-bottom: none;
      font-weight: 600;
    }

    .table tbody tr:hover {
      background-color: #f8fafc;
    }

    .card {
      border: none;
      transition: all 0.3s ease;
    }

    .card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .vscode-preview {
      background: #1e1e1e;
      color: #d4d4d4;
      border-radius: 8px;
      font-family: 'Courier New', monospace;
      font-size: 14px;
      line-height: 1.5;
    }

    .vscode-preview::-webkit-scrollbar {
      width: 8px;
      height: 8px;
    }

    .vscode-preview::-webkit-scrollbar-track {
      background: #1e1e1e;
    }

    .vscode-preview::-webkit-scrollbar-thumb {
      background: #424242;
      border-radius: 4px;
    }

    .vscode-preview::-webkit-scrollbar-thumb:hover {
      background: #4f4f4f;
    }

    @media (max-width: 768px) {
      .header-title {
        flex-direction: column;
        align-items: flex-start;
      }

      .buttons-container {
        width: 100%;
        justify-content: flex-start;
      }

      .btn {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
      }

      .form-control,
      .form-select {
        font-size: 0.9rem;
      }
    }
  </style>

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

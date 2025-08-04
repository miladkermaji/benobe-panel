<div>
  <div class="container-fluid py-3" wire:init="loadEntries">
    <!-- Header -->
    <header
      class="bg-gradient-header text-white p-3 rounded-3 mb-3 d-flex align-items-center justify-content-between shadow-lg">
      <div class="d-flex align-items-center gap-3 mb-2">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="header-icon">
          <path d="M12 2a10 10 0 110 20 10 10 0 010-20zm0 4v6l5 5" />
        </svg>
        <h2 class="mb-0 fw-bold">تلسکوپ</h2>
      </div>
      <div class="d-flex align-items-center gap-3 mb-2">
        <div class="search-box position-relative">
          <input type="text" wire:model.live="search" class="form-control rounded-pill ps-5 pe-3"
            placeholder="جستجو در ورودی‌ها...">
        </div>
        <select wire:model.live="type" class="form-select rounded-pill" style="width: 150px;">
          <option value="all">همه</option>
          <option value="request">درخواست‌ها</option>
          <option value="query">کوئری‌ها</option>
          <option value="log">لاگ‌ها</option>
          <option value="exception">خطاها</option>
          <option value="mail">ایمیل‌ها</option>
          <option value="job">جاب‌ها</option>
        </select>
        <span class="badge bg-white text-primary rounded-pill px-3 py-1 fw-medium">
          {{ $readyToLoad ? $entries->total() : 0 }} ورودی
        </span>
      </div>
    </header>

    <!-- Entries List -->
    <div class="gateway-list d-flex flex-wrap gap-4 justify-content-center">
      @if ($readyToLoad)
        @forelse($entries as $entry)
          <div
            class="gateway-card card border-0 rounded-3 shadow-lg position-relative overflow-hidden animate__animated animate__zoomIn">
            <div class="card-bg {{ $entry->type === 'exception' ? 'bg-inactive' : 'bg-active' }}"></div>
            <div class="card-body p-4 d-flex flex-column gap-3 position-relative">
              <div class="d-flex align-items-center gap-4">
                <div
                  class="gateway-logo rounded-circle bg-white shadow-lg d-flex align-items-center justify-content-center"
                  style="width: 50px; height: 50px; border: 3px solid #ffffff;">
                  @switch($entry->type)
                    @case('request')
                      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#2563eb"
                        stroke-width="2">
                        <path d="M12 2L2 12l10 10 10-10L12 2zm0 4v12" />
                      </svg>
                    @break

                    @case('query')
                      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#16a34a"
                        stroke-width="2">
                        <path d="M4 4h16v16H4zM8 8l8 8M8 16L16 8" />
                      </svg>
                    @break

                    @case('log')
                      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#f59e0b"
                        stroke-width="2">
                        <path d="M12 2a10 10 0 110 20 10 10 0 010-20zm0 4v6l5 5" />
                      </svg>
                    @break

                    @case('exception')
                      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#dc2626"
                        stroke-width="2">
                        <path d="M4 12a8 8 0 018-8 8 8 0 018 8H4z" />
                      </svg>
                    @break

                    @default
                      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                        stroke-width="2">
                        <path d="M12 2a10 10 0 110 20 10 10 0 010-20zM8 12h8M12 8v8" />
                      </svg>
                  @endswitch
                </div>
                <h5 class="mb-0 fw-bold text-dark gateway-title">
                  {{ $entry->type === 'request' ? 'درخواست' : ($entry->type === 'query' ? 'کوئری' : ($entry->type === 'log' ? 'لاگ' : ($entry->type === 'exception' ? 'خطا' : $entry->type))) }}
                </h5>
              </div>
              <div class="d-flex align-items-center justify-content-between mt-2">
                <span class="badge bg-secondary rounded-pill px-2 py-1">
                  {{ \Carbon\Carbon::parse($entry->created_at)->locale('fa')->diffForHumans() }}
                </span>
                <!-- تغییر $emit به $dispatch -->
                <button class="btn btn-custom rounded-pill px-3 py-1"
                  wire:click="$dispatch('showDetails', '{{ $entry->uuid }}')">
                  جزئیات
                </button>
              </div>
            </div>
          </div>
          @empty
            <div class="col-12 text-center text-muted">
              هیچ ورودی با این جستجو یافت نشد.
            </div>
          @endforelse
        @else
          <div class="col-12 text-center text-muted">
            در حال بارگذاری ورودی‌ها...
          </div>
        @endif
      </div>

      <!-- Pagination -->
      <div class="pagination-container mt-4">
        @if ($entries && $entries->hasPages())
          <nav aria-label="Page navigation">
            {{ $entries->links('livewire::simple-tailwind') }}
          </nav>
        @endif
      </div>
    </div>

    <style>
      /* استایل‌ها بدون تغییر */
    </style>

    <script>
      document.addEventListener('livewire:init', function() {
        // تغییر Livewire.on به Livewire.dispatch
        Livewire.on('showDetails', (uuid) => {
          alert('نمایش جزئیات برای ورودی: ' + uuid);
        });
      });
    </script>
  </div>

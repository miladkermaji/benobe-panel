@php
  use Morilog\Jalali\Jalalian;
@endphp

<div class="container" dir="rtl" wire:init="loadReports">
  <!-- Top Section -->
  <div class="top-section mb-2">
    <div class="top-section-content">
      <h1 class="top-section-title">گزارش مالی</h1>
      <div class="top-section-actions">
        <div class="search-box">
          <input type="text" class="search-input" wire:model.live.debounce.500ms="search" placeholder="جستجو...">
          <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none"
            stroke="var(--text-secondary)" stroke-width="2">
            <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
          </svg>
        </div>
        <button wire:click="exportExcel" class="btn btn-export">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 4h16v16H4zM8 2v2m8-2v2m-4 14v-6m-3 3l3 3 3-3" />
          </svg>
          اکسل
        </button>
        <button wire:click="exportPdf" class="btn btn-export">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 4h16v16H4zM8 2v2m8-2v2m-4 14v-6m-3 3l3 3 3-3" />
          </svg>
          PDF
        </button>
      </div>
    </div>
  </div>

  <!-- Summary Cards -->
  <div class="summary-container mb-2">
    <div class="summary-card">
      <span class="summary-label">روزانه</span>
      <span class="summary-value">{{ number_format($summary['daily']) }} <small>ریال</small></span>
    </div>
    <div class="summary-card">
      <span class="summary-label">هفتگی</span>
      <span class="summary-value">{{ number_format($summary['weekly']) }} <small>ریال</small></span>
    </div>
    <div class="summary-card">
      <span class="summary-label">ماهانه</span>
      <span class="summary-value">{{ number_format($summary['monthly']) }} <small>ریال</small></span>
    </div>
    <div class="summary-card">
      <span class="summary-label">سالانه</span>
      <span class="summary-value">{{ number_format($summary['yearly']) }} <small>ریال</small></span>
    </div>
    <div class="summary-card">
      <span class="summary-label">کل</span>
      <span class="summary-value">{{ number_format($summary['total']) }} <small>ریال</small></span>
    </div>
  </div>

  <!-- Filters -->
  <div class="filters-card mb-2">
    <div class="filters-header">
      <h4>فیلترها</h4>
      <button class="filters-toggle" onclick="toggleFilters()">نمایش/مخفی</button>
    </div>
    <div class="filters-body" id="filters-body">
      <div class="filter-group">
        <label>بازه زمانی</label>
        <select class="filter-select" wire:model.live.debounce.500ms="dateFilter">
          <option value="daily">روزانه</option>
          <option value="weekly">هفتگی</option>
          <option value="monthly">ماهانه</option>
          <option value="yearly">سالانه</option>
          <option value="custom">دلخواه</option>
          <option value="all">همه</option>
        </select>
      </div>
      @if ($dateFilter === 'custom')
        <div class="filter-group">
          <label>از تاریخ</label>
          <input data-jdp type="text" class="filter-input jalali-datepicker"
            wire:model.live.debounce.500ms="startDate" placeholder="انتخاب تاریخ">
        </div>
        <div class="filter-group">
          <label>تا تاریخ</label>
          <input data-jdp type="text" class="filter-input jalali-datepicker" wire:model.live.debounce.500ms="endDate"
            placeholder="انتخاب تاریخ">
        </div>
      @endif
      <div class="filter-group">
        <label>نوع تراکنش</label>
        <select class="filter-select" wire:model.live.debounce.500ms="transactionType">
          <option value="">همه</option>
          <option value="online">آنلاین</option>
          <option value="in_person">حضوری</option>
          <option value="charge">شارژ</option>
        </select>
      </div>
      <div class="filter-group">
        <label>وضعیت تراکنش</label>
        <select class="filter-select" wire:model.live.debounce.500ms="transactionStatus">
          <option value="">همه</option>
          <option value="pending">در انتظار</option>
          <option value="available">موجود</option>
          <option value="requested">درخواست‌شده</option>
          <option value="paid">پرداخت‌شده</option>
        </select>
      </div>
      <div class="filter-group">
        <label>کلینیک</label>
        <select class="filter-select" wire:model.live.debounce.500ms="clinicId">
          <option value="">همه</option>
          @foreach ($clinics as $clinic)
            <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="filter-group">
        <label>روش پرداخت</label>
        <select class="filter-select" wire:model.live.debounce.500ms="paymentMethod">
          <option value="">همه</option>
          <option value="online">آنلاین</option>
          <option value="cash">نقدی</option>
          <option value="card_to_card">کارت به کارت</option>
          <option value="pos">POS</option>
        </select>
      </div>
      <div class="filter-group">
        <label>بیمه</label>
        <select class="filter-select" wire:model.live.debounce.500ms="insuranceId">
          <option value="">همه</option>
          @foreach ($insurances as $insurance)
            <option value="{{ $insurance->id }}">{{ $insurance->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="filter-group">
        <label>حداقل مبلغ (ریال)</label>
        <input type="number" class="filter-input" wire:model.live.debounce.500ms="minAmount" placeholder="0">
      </div>
      <div class="filter-group">
        <label>حداکثر مبلغ (ریال)</label>
        <input type="number" class="filter-input" wire:model.live.debounce.500ms="maxAmount" placeholder="0">
      </div>
    </div>
  </div>

  <!-- Chart -->
  <div class="chart-card mb-2">
    <h4 class="section-title">📊 نمودار تراکنش‌ها</h4>
    <div class="chart-container">
      <canvas id="financialChart"></canvas>
    </div>
  </div>

  <!-- Transactions Table -->
  <div class="table-card">
    <div class="table-responsive">
      <table class="transactions-table">
        <thead>
          <tr>
            <th>ردیف</th>
            <th>تاریخ</th>
            <th>کلینیک</th>
            <th>نوع</th>
            <th>وضعیت</th>
            <th>مبلغ (ریال)</th>
            <th class="hidden-mobile">روش پرداخت</th>
            <th class="hidden-mobile">بیمه</th>
            <th class="hidden-mobile">توضیحات</th>
          </tr>
        </thead>
        <tbody>
          @if ($readyToLoad)
            @forelse ($transactions as $index => $transaction)
              <tr>
                <td>{{ $transactions->firstItem() + $index }}</td>
                <td>{{ Jalalian::fromCarbon(\Carbon\Carbon::parse($transaction->registered_at))->format('Y/m/d H:i') }}
                </td>
                <td>{{ $transaction->clinic ? $transaction->clinic->name : 'بدون کلینیک' }}</td>
                <td>
                  @switch($transaction->type)
                    @case('online')
                      آنلاین
                    @break

                    @case('in_person')
                      حضوری
                    @break

                    @case('charge')
                      شارژ
                    @break
                  @endswitch
                </td>
                <td>
                  @switch($transaction->status)
                    @case('pending')
                      در انتظار
                    @break

                    @case('available')
                      موجود
                    @break

                    @case('requested')
                      درخواست‌شده
                    @break

                    @case('paid')
                      پرداخت‌شده
                    @break
                  @endswitch
                </td>
                <td>{{ number_format($transaction->amount) }}</td>
                <td class="hidden-mobile">
                  {{ $transaction->appointment ? $this->formatPaymentMethod($transaction->appointment->payment_method) : '-' }}
                </td>
                <td class="hidden-mobile">
                  {{ $transaction->appointment && $transaction->appointment->insurance ? $transaction->appointment->insurance->name : '-' }}
                </td>
                <td class="hidden-mobile">{{ $transaction->description ?? 'بدون توضیح' }}</td>
              </tr>
              @empty
                <tr>
                  <td colspan="9" class="no-data">هیچ تراکنشی یافت نشد.</td>
                </tr>
              @endforelse
            @else
              <tr>
                <td colspan="9" class="no-data">در حال بارگذاری...</td>
              </tr>
            @endif
          </tbody>
        </table>
      </div>
      <div class="table-footer">
        @if ($readyToLoad)
          @if ($transactions->total() > 0)
            <span>نمایش {{ $transactions->firstItem() }} تا {{ $transactions->lastItem() }} از
              {{ $transactions->total() }} ردیف</span>
          @else
            <span>هیچ تراکنشی یافت نشد.</span>
          @endif
          @if ($transactions->hasPages())
            {{ $transactions->links('livewire::bootstrap') }}
          @endif
        @else
          <span>در حال بارگذاری...</span>
        @endif
      </div>
    </div>

    <!-- Total Summary -->
    <div class="total-card mt-2">
      <h4>جمع‌بندی</h4>
      <p>مجموع تراکنش‌ها: <strong>{{ number_format($totalAmount) }}</strong> ریال</p>
    </div>

    <!-- اسکریپت‌های جاوااسکریپت -->
    <script>
      document.addEventListener('livewire:initialized', function() {
        // متغیر جهانی برای نمودار
        window.financialChart = null;

        // تابع تخریب نمودار
        function destroyChart() {
          if (window.financialChart) {
            window.financialChart.destroy();
            window.financialChart = null;
          }
        }

        // Cleanup هنگام dehydrate کامپوننت
        Livewire.on('component.dehydrated', () => {
          destroyChart();
        });

        // رندر نمودار
        Livewire.on('updateChart', (chartData) => {
          const renderChart = () => {
            const canvas = document.getElementById('financialChart');
            if (!canvas) {
              console.error('Canvas element not found');
              return;
            }
            const ctx = canvas.getContext('2d');
            if (!ctx) {
              console.error('Failed to get canvas context');
              return;
            }

            // Destroy previous chart
            destroyChart();

            // Check for valid data
            if (!chartData.labels || !chartData.values || chartData.labels.length === 0) {
              canvas.parentNode.innerHTML = '<p>داده‌ای برای نمایش وجود ندارد</p>';
              return;
            }

            window.financialChart = new Chart(ctx, {
              // ... existing chart configuration ...
            });
          };

          // Observe DOM for canvas element
          const targetNode = document.querySelector('.chart-container');
          if (!targetNode) {
            console.error('Chart container not found');
            return;
          }

          const observer = new MutationObserver((mutations, obs) => {
            const canvas = document.getElementById('financialChart');
            if (canvas) {
              renderChart();
              obs.disconnect(); // Stop observing once canvas is found
            }
          });

          observer.observe(targetNode, {
            childList: true,
            subtree: true
          });

          // Fallback: Try rendering immediately in case canvas is already present
          renderChart();
        });

        // Initialize Jalali Datepicker

      });

      function toggleFilters() {
        const filtersBody = document.getElementById('filters-body');
        filtersBody.classList.toggle('active');
      }
    </script>
  </div>

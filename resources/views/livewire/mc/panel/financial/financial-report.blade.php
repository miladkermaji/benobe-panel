@php
  use Morilog\Jalali\Jalalian;
  use Illuminate\Support\Carbon;
@endphp

<div class="container" dir="rtl" wire:init="loadReports">
  <!-- Top Section -->
  <div class="top-section mb-2 mt-2">
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
      <span class="summary-value">{{ number_format($summary['daily']) }} <small>تومان</small></span>
    </div>
    <div class="summary-card">
      <span class="summary-label">هفتگی</span>
      <span class="summary-value">{{ number_format($summary['weekly']) }} <small>تومان</small></span>
    </div>
    <div class="summary-card">
      <span class="summary-label">ماهانه</span>
      <span class="summary-value">{{ number_format($summary['monthly']) }} <small>تومان</small></span>
    </div>
    <div class="summary-card">
      <span class="summary-label">سالانه</span>
      <span class="summary-value">{{ number_format($summary['yearly']) }} <small>تومان</small></span>
    </div>
    <div class="summary-card">
      <span class="summary-label">کل</span>
      <span class="summary-value">{{ number_format($summary['total']) }} <small>تومان</small></span>
    </div>
  </div>

  <!-- Filters -->
  <div class="filters-card mb-2">
    <div class="filters-header">
      <h4>فیلترها</h4>
      <button class="filters-toggle" data-tooltip="نمایش یا مخفی کردن فیلترها" onclick="toggleFilters()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2">
          <path d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
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
          <option value="wallet_charge">شارژ کیف پول</option>
          <option value="profile_upgrade">ارتقای حساب</option>
          <option value="appointment">نوبت‌دهی</option>
          <option value="online">آنلاین</option>
          <option value="in_person">حضوری</option>
          <option value="charge">شارژ</option>
          <option value="phone">تلفنی</option>
          <option value="video">تصویری</option>
          <option value="text">متنی</option>
          <option value="manual">دستی</option>
        </select>
      </div>
      <div class="filter-group">
        <label>وضعیت تراکنش</label>
        <select class="filter-select" wire:model.live.debounce.500ms="transactionStatus">
          <option value="">همه</option>
          <option value="pending">در انتظار</option>
          <option value="paid">پرداخت‌شده</option>
          <option value="failed">ناموفق</option>
          <option value="available">موجود</option>
          <option value="requested">درخواست‌شده</option>
          <option value="unpaid">پرداخت‌نشده</option>
          <option value="scheduled">برنامه‌ریزی‌شده</option>
          <option value="cancelled">لغو شده</option>
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
          <option value="card">کارت</option>
          <option value="insurance">بیمه</option>
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
        <label>حداقل مبلغ (تومان)</label>
        <input type="number" class="filter-input" wire:model.live.debounce.500ms="minAmount" placeholder="0">
      </div>
      <div class="filter-group">
        <label>حداکثر مبلغ (تومان)</label>
        <input type="number" class="filter-input" wire:model.live.debounce.500ms="maxAmount" placeholder="0">
      </div>
    </div>
  </div>

  <!-- Transactions Table/Card -->
  <div class="table-card">
    <div class="table-responsive">
      <table class="transactions-table desktop-only">
        <thead>
          <tr>
            <th>ردیف</th>
            <th>تاریخ</th>
            <th>کلینیک</th>
            <th>نوع</th>
            <th>وضعیت</th>
            <th>مبلغ (تومان)</th>
            <th>روش پرداخت</th>
            <th>بیمه</th>
            <th>توضیحات</th>
          </tr>
        </thead>
        <tbody>
          @if ($readyToLoad)
            @forelse ($transactions as $index => $transaction)
              <tr>
                <td>{{ $transactions->firstItem() + $index }}</td>
                <td>{{ Jalalian::fromCarbon(Carbon::parse($transaction['date']))->format('Y/m/d H:i') }}</td>
                <td>
                  @if ($transaction['medical_center_id'])
                    {{ \App\Models\MedicalCenter::find($transaction['medical_center_id']) ? \App\Models\MedicalCenter::find($transaction['medical_center_id'])->name : 'بدون مرکز درمانی' }}
                  @else
                    بدون مرکز درمانی
                  @endif
                </td>
                <td>{{ $this->formatTransactionType($transaction['transaction_type']) }}</td>
                <td>{{ $this->formatStatus($transaction['status']) }}</td>
                <td>{{ number_format($transaction['amount']) }}</td>
                <td>{{ $this->formatPaymentMethod($transaction['payment_method']) }}</td>
                <td>
                  @if ($transaction['insurance_id'])
                    {{ \App\Models\Insurance::find($transaction['insurance_id']) ? \App\Models\Insurance::find($transaction['insurance_id'])->name : '-' }}
                  @else
                    -
                  @endif
                </td>
                <td>{{ $transaction['description'] }}</td>
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
        <tfoot>
          @if ($readyToLoad && $transactions->isNotEmpty())
            <tr class="total-row">
              <td colspan="5" class="total-label fw-bolder fs-5">جمع کل</td>
              <td class="fw-bolder fs-5">{{ number_format($totalAmount) }}</td>
              <td colspan="4" class="total-label fw-bolder fs-5">جمع امروز: {{ number_format($todayAmount) }}
                تومان</td>
            </tr>
          @endif
        </tfoot>
      </table>

      <!-- Mobile/Tablet Card View -->
      <div class="transaction-cards mobile-only" id="transaction-cards">
        @if ($readyToLoad)
          @forelse ($transactions as $index => $transaction)
            <div class="transaction-card" data-index="{{ $index }}">
              <div class="transaction-card-header">
                <span class="transaction-index">{{ $transactions->firstItem() + $index }}</span>
                <span
                  class="transaction-date">{{ Jalalian::fromCarbon(Carbon::parse($transaction['date']))->format('Y/m/d H:i') }}</span>
              </div>
              <div class="transaction-card-body">
                <div class="transaction-item">
                  <span class="transaction-label">کلینیک:</span>
                  <span class="transaction-value">
                    @if ($transaction['medical_center_id'])
                      {{ \App\Models\MedicalCenter::find($transaction['medical_center_id']) ? \App\Models\MedicalCenter::find($transaction['medical_center_id'])->name : 'بدون مرکز درمانی' }}
                    @else
                      بدون مرکز درمانی
                    @endif
                  </span>
                </div>
                <div class="transaction-item">
                  <span class="transaction-label">نوع:</span>
                  <span
                    class="transaction-value">{{ $this->formatTransactionType($transaction['transaction_type']) }}</span>
                </div>
                <div class="transaction-item">
                  <span class="transaction-label">وضعیت:</span>
                  <span class="transaction-value">{{ $this->formatStatus($transaction['status']) }}</span>
                </div>
                <div class="transaction-item">
                  <span class="transaction-label">مبلغ:</span>
                  <span class="transaction-value">{{ number_format($transaction['amount']) }} تومان</span>
                </div>
                <div class="transaction-item">
                  <span class="transaction-label">روش پرداخت:</span>
                  <span
                    class="transaction-value">{{ $this->formatPaymentMethod($transaction['payment_method']) }}</span>
                </div>
                <div class="transaction-item">
                  <span class="transaction-label">بیمه:</span>
                  <span class="transaction-value">
                    @if ($transaction['insurance_id'])
                      {{ \App\Models\Insurance::find($transaction['insurance_id']) ? \App\Models\Insurance::find($transaction['insurance_id'])->name : '-' }}
                    @else
                      -
                    @endif
                  </span>
                </div>
                <div class="transaction-item">
                  <span class="transaction-label">توضیحات:</span>
                  <span class="transaction-value">{{ $transaction['description'] }}</span>
                </div>
              </div>
            </div>
          @empty
            <div class="no-data">هیچ تراکنشی یافت نشد.</div>
          @endforelse
          @if ($readyToLoad && $transactions->isNotEmpty())
            <div class="total-card mobile-only">
              <div class="total-item">
                <span class="total-label">جمع کل:</span>
                <span class="total-value">{{ number_format($totalAmount) }} تومان</span>
              </div>
              <div class="total-item">
                <span class="total-label">جمع امروز:</span>
                <span class="total-value">{{ number_format($todayAmount) }} تومان</span>
              </div>
            </div>
          @endif
          @if ($transactions->count() > 2)
            <div class="toggle-buttons">
              <button class="btn btn-toggle" onclick="showMore()">بیشتر...</button>
              <button class="btn btn-toggle" onclick="showLess()" style="display: none;">کمتر...</button>
            </div>
          @endif
        @else
          <div class="no-data">در حال بارگذاری...</div>
        @endif
      </div>
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

  <!-- JavaScript Scripts -->
  <script>
    function toggleFilters() {
      const filtersBody = document.getElementById('filters-body');
      filtersBody.classList.toggle('active');
    }

    function showMore() {
      const cards = document.querySelectorAll('.transaction-card');
      cards.forEach(card => card.style.display = 'block');
      document.querySelector('.btn-toggle[onclick="showMore()"]').style.display = 'none';
      document.querySelector('.btn-toggle[onclick="showLess()"]').style.display = 'inline-flex';
    }

    function showLess() {
      const cards = document.querySelectorAll('.transaction-card');
      cards.forEach((card, index) => {
        if (index >= 2) card.style.display = 'none';
      });
      document.querySelector('.btn-toggle[onclick="showMore()"]').style.display = 'inline-flex';
      document.querySelector('.btn-toggle[onclick="showLess()"]').style.display = 'none';
    }

    // Initially hide cards beyond the first two
    const cards = document.querySelectorAll('.transaction-card');
    cards.forEach((card, index) => {
      if (index >= 2) card.style.display = 'none';
    });
  </script>
</div>

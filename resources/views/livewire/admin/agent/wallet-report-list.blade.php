<div class="container-fluid py-4">
  <!-- هدر اصلی -->
  <div class="bg-light text-dark p-4 rounded-top border">
    <div class="d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center">
        <i class="fas fa-wallet me-3"></i>
        <h5 class="mb-0 fw-bold">لیست گزارش کیف پول</h5>
      </div>
    </div>
  </div>

  <!-- بدنه اصلی -->
  <div class="bg-white p-4 rounded-bottom shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div class="input-group w-25">
        <span class="input-group-text"><i class="fas fa-search"></i></span>
        <input type="text" class="form-control" wire:model.live="search" placeholder="جستجو توضیح یا تاریخ">
      </div>
      <div class="d-flex align-items-center gap-3 mb-2">
        <select wire:model.live="perPage" class="form-select w-auto">
          <option value="10">10</option>
          <option value="20">20</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </select>
        <button class="btn btn-danger" id="delete-selected-btn" @if (empty($selectedReports)) disabled @endif>
          <i class="fas fa-trash me-2"></i> حذف انتخاب‌شده‌ها
        </button>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead class="table-light">
          <tr>
            <th style="width: 50px;">
              <input type="checkbox" class="form-check-input" wire:model.live="selectAll">
            </th>
            <th>ردیف</th>
            <th>تاریخ ثبت</th>
            <th>توضیح</th>
            <th>مبلغ</th>
            <th>وضعیت</th>
            <th>عملیات</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($reports as $index => $report)
            <tr>
              <td>
                <input type="checkbox" class="form-check-input" wire:model.live="selectedReports"
                  value="{{ $report->id }}">
              </td>
              <td>{{ $reports->firstItem() + $index }}</td>
              <td>{{ $report->persian_date }}</td>
              <td>{{ Str::limit($report->description, 30) }}</td>
              <td>{{ number_format($report->amount) }} تومان</td>
              <td>
                <div class="form-check form-switch">
                  <input type="checkbox" class="form-check-input" wire:model.live="reportStatuses.{{ $report->id }}"
                    wire:change="toggleStatus({{ $report->id }})" @checked($report->status === 'پرداخت‌شده')>
                  <label class="form-check-label mx-1">{{ $report->status }}</label>
                </div>
              </td>
              <td>
                <span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $report->description }}">
                  <i class="fas fa-info-circle me-2"></i>
                </span>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center">هیچ گزارشی یافت نشد.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- صفحه‌بندی -->
    <div class="mt-4">
      {{ $reports->links() }}
    </div>
  </div>

  <!-- استایل‌ها -->
  <style>
    .bg-light {
      background-color: #f8f9fa !important;
    }

    .border {
      border-color: #dee2e6 !important;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: #007bff;
      box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .btn {
      border-radius: 0.375rem;
      padding: 0.75rem 1.5rem;
    }

    .btn-danger {
      background-color: #dc3545;
      border-color: #dc3545;
    }

    .btn-danger:hover {
      background-color: #c82333;
      border-color: #bd2130;
    }

    .btn-danger:disabled {
      background-color: #6c757d;
      border-color: #6c757d;
      cursor: not-allowed;
    }

    .form-switch .form-check-input {
      width: 2.5em;
      height: 1.25em;
    }

    .tooltip-inner {
      max-width: 300px;
      text-align: right;
      font-family: IRANSans, sans-serif;
      background-color: #333;
      color: #fff;
      padding: 5px 10px;
      font-size: 14px;
    }

    .bs-tooltip-top .tooltip-arrow::before {
      border-top-color: #333;
    }
  </style>


  <script>
    document.addEventListener('livewire:initialized', () => {
      // فعال‌سازی تولتیپ‌ها
      if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function(tooltipTriggerEl) {
          new bootstrap.Tooltip(tooltipTriggerEl);
        });
      }

      document.getElementById('delete-selected-btn').addEventListener('click', function(e) {
        e.preventDefault();
        Swal.fire({
          title: 'آیا مطمئن هستید؟',
          text: "این گزارش‌ها حذف خواهند شد و قابل بازگشت نیستند!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#dc3545',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'بله، حذف کن',
          cancelButtonText: 'خیر'
        }).then((result) => {
          if (result.isConfirmed) {
            @this.call('deleteSelected');
            console.log('Delete confirmed');
          }
        });
      });

      Livewire.on('toast', (message, options = {}) => {
        if (typeof toastr === 'undefined') {
          console.error('Toastr is not loaded!');
          return;
        }
        const type = options.type || 'info';
        if (type === 'success') {
          toastr.success(message, '', {
            positionClass: options.position || 'toast-top-right',
            timeOut: options.timeOut || 3000,
            progressBar: options.progressBar || false,
          });
        } else if (type === 'error') {
          toastr.error(message, '', {
            positionClass: options.position || 'toast-top-right',
            timeOut: options.timeOut || 3000,
            progressBar: options.progressBar || false,
          });
        } else {
          toastr.info(message, '', {
            positionClass: options.position || 'toast-top-right',
            timeOut: options.timeOut || 3000,
            progressBar: options.progressBar || false,
          });
        }
      });
    });
  </script>
</div>

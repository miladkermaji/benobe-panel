<div class="container-fluid py-4">
  <!-- هدر اصلی -->
  <div class="bg-light text-dark p-4 rounded-top border">
    <div class="d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center">
        <i class="fas fa-wallet me-3"></i>
        <h5 class="mb-0 fw-bold">درخواست‌های کیف پول</h5>
      </div>
    </div>
  </div>

  <!-- بدنه اصلی -->
  <div class="bg-white p-4 rounded-bottom shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div class="input-group w-25">
        <span class="input-group-text"><i class="fas fa-search"></i></span>
        <input type="text" class="form-control" wire:model.live="search" placeholder="جستجوی نام کاربر...">
      </div>
      <div class="d-flex align-items-center gap-3">
        <select wire:model.live="perPage" class="form-select w-auto">
          <option value="10">10</option>
          <option value="20">20</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </select>
        <button class="btn btn-danger" id="delete-selected-btn" @if (empty($selectedRequests)) disabled @endif>
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
            <th>کاربر</th>
            <th>مبلغ</th>
            <th>وضعیت</th>
            <th>تاریخ درخواست</th>
            <th>عملیات</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($requestsPaginated as $request)
            <tr>
              <td>
                <input type="checkbox" class="form-check-input" wire:model.live="selectedRequests"
                  value="{{ $request->id }}">
              </td>
              <td>{{ $requestsPaginated->firstItem() + $loop->index }}</td>
              <td>{{ $request->doctor->first_name . ' ' . $request->doctor->last_name }}</td>
              <td>{{ number_format($request->amount) }} تومان</td>
              <td>
                @if ($request->status === 'pending')
                  <label class="badge badge-primary">در انتظار ارائه خدمت</label>
                @elseif ($request->status === 'available')
                  <label class="badge badge-outline-green">قابل برداشت</label>
                @elseif ($request->status === 'requested')
                  <label class="badge badge-warning">درخواست‌شده</label>
                @elseif ($request->status === 'paid')
                  <label class="badge badge-success">پرداخت‌شده</label>
                @endif
              </td>
              <td>
                {{ \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($request->requested_at))->format('Y/m/d') }}
              </td>
              <td>
                <button class="btn btn-light btn-sm delete-transaction" data-id="{{ $request->id }}"><img
                    src="{{ asset('dr-assets/icons/trash.svg') }}" alt="trash" srcset=""></button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center">هیچ درخواستی یافت نشد.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- صفحه‌بندی -->
    <div class="mt-4">
      {{ $requestsPaginated->links('pagination::bootstrap-5') }}
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

    .form-check-input {
      cursor: pointer;
    }

    .input-shiny {
      border: 1px solid #d1d5db;
      border-radius: 6px;
      padding: 8px;
      font-size: 14px;
      transition: all 0.3s ease;
      background: #fff;
      box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
      height: 40px;
    }

    .input-shiny:focus {
      border-color: #4f46e5;
      box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
    }
  </style>

  <script>
    document.addEventListener('livewire:initialized', () => {
      toastr.options = {
        positionClass: 'toast-top-right',
        timeOut: 3000,
      };

      Livewire.on('toast', (event, options = {}) => {
        const type = options.type || 'success';
        toastr[type](event.message, '', {
          positionClass: options.position || 'toast-top-right',
          timeOut: options.timeOut || 3000,
          progressBar: options.progressBar || false,
        });
      });

      // فقط برای آپدیت مقدار بدون کاما


      // فرمت شماره کارت
      const cardNumberInput = document.getElementById('card_number');
      if (cardNumberInput) { // چک کردن وجود المنت
        if (cardNumberInput.value) {
          let value = cardNumberInput.value.replace(/[^0-9]/g, '');
          cardNumberInput.value = formatCardNumber(value);
        }
        cardNumberInput.addEventListener('input', function(e) {
          let value = e.target.value.replace(/[^0-9]/g, '');
          if (value.length > 16) value = value.slice(0, 16);
          e.target.value = formatCardNumber(value);
          @this.set('card_number', e.target.value);
        });

        cardNumberInput.addEventListener('keypress', function(e) {
          if (e.target.value.length >= 19) e.preventDefault();
        });
      }

      function formatCardNumber(value) {
        if (!value) return '';
        let formatted = '';
        for (let i = 0; i < value.length; i++) {
          if (i > 0 && i % 4 === 0) formatted += '-';
          formatted += value[i];
        }
        return formatted;
      }
    });

    document.querySelectorAll('.delete-transaction').forEach(button => {
      button.addEventListener('click', function(e) {
        e.preventDefault();
        const requestId = this.getAttribute('data-id');

        Swal.fire({
          title: 'آیا مطمئن هستید؟',
          text: "این تراکنش حذف خواهد شد و قابل بازگشت نیست!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#dc3545',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'بله، حذف کن',
          cancelButtonText: 'خیر'
        }).then((result) => {
          if (result.isConfirmed) {
            @this.call('deleteRequest', requestId);
          }
        });
      });
    });

    // رفتار چک‌باکس انتخاب همه
    document.addEventListener('livewire:initialized', () => {
      Livewire.on('updateSelectAll', (selectAll) => {
        const checkboxes = document.querySelectorAll('input[type="checkbox"][wire\\:model="selectedRequests"]');
        checkboxes.forEach(checkbox => {
          checkbox.checked = selectAll;
        });
      });
    });

    // مدیریت کلیک روی دکمه حذف انتخاب‌شده‌ها
    document.getElementById('delete-selected-btn').addEventListener('click', function(e) {
      e.preventDefault();
      Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: "این درخواست‌ها حذف خواهند شد و قابل بازگشت نیستند!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'بله، حذف کن',
        cancelButtonText: 'خیر'
      }).then((result) => {
        if (result.isConfirmed) {
          @this.call('deleteSelectedRequests');
          console.log('Delete confirmed');
        }
      });
    });
  </script>
</div>

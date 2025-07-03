@extends('dr.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/doctor-clinic/deposite.css') }}" rel="stylesheet" />

@endsection

@section('site-header')
  {{ 'پنل مدیریت | مدیریت بیعانه' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت بیعانه')
<div class="container">
  <div class="mt-5 d-flex justify-content-between align-items-center mb-4">
    <h1 class="fs-4 fw-bold">مدیریت بیعانه‌ها</h1>
    <button class="btn my-btn-primary h-50" data-bs-toggle="modal" data-bs-target="#depositModal">
      <i class="fas fa-plus me-2"></i> افزودن بیعانه
    </button>
  </div>
  <div class="card table-responsive d-none d-md-block">
    <table class="table table-hover" dir="ltr">
      <thead>
        <tr>
          <th>عملیات</th>
          <th>مطب</th>
          <th>مبلغ (تومان)</th>
        </tr>
      </thead>
      <tbody id="depositList">
        @foreach ($deposits as $deposit)
          <tr data-id="{{ $deposit->id }}">
            <td>
              <button class="btn btn-icon edit-btn btn-light rounded-circle" data-id="{{ $deposit->id }}">
                <img src="{{ asset('dr-assets/icons/edit.svg') }}" alt="ویرایش">
              </button>
              <button class="btn btn-icon delete-btn btn-light rounded-circle" data-id="{{ $deposit->id }}">
                <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
              </button>
            </td>
            <td>{{ $deposit->clinic_id ? $clinics->find($deposit->clinic_id)->name : 'ویزیت آنلاین' }}</td>
            <td>{{ $deposit->deposit_amount ? number_format($deposit->deposit_amount) : 'بدون بیعانه' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <!-- کارت‌های بیعانه برای موبایل/تبلت -->
  <div class="notes-cards d-md-none">
    @foreach ($deposits as $deposit)
      <div class="note-card mb-3 position-relative" data-id="{{ $deposit->id }}">
        <div class="note-card-header d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center gap-2">
            <span
              class="badge bg-primary-subtle text-primary">{{ $deposit->clinic_id ? $clinics->find($deposit->clinic_id)->name : 'ویزیت آنلاین' }}</span>
          </div>
          <div class="d-flex gap-1">
            <button class="btn btn-sm btn-gradient-success px-2 py-1 edit-btn" data-id="{{ $deposit->id }}"
              title="ویرایش">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path
                  d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
              </svg>
            </button>
            <button class="btn btn-sm btn-gradient-danger px-2 py-1 delete-btn" data-id="{{ $deposit->id }}"
              title="حذف">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
              </svg>
            </button>
          </div>
        </div>
        <div class="note-card-body">
          <div class="note-card-item">
            <span class="note-card-label">مطب:</span>
            <span
              class="note-card-value">{{ $deposit->clinic_id ? $clinics->find($deposit->clinic_id)->name : 'ویزیت آنلاین' }}</span>
          </div>
          <div class="note-card-item">
            <span class="note-card-label">مبلغ:</span>
            <span
              class="note-card-value">{{ $deposit->deposit_amount ? number_format($deposit->deposit_amount) : 'بدون بیعانه' }}</span>
          </div>
        </div>
      </div>
    @endforeach
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="depositModal" tabindex="-1" aria-labelledby="depositModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="depositModalLabel">افزودن بیعانه</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="depositForm">
          @csrf
          <input type="hidden" name="id" id="depositId">
          <input type="hidden" name="selectedClinicId" value="{{ $selectedClinicId }}">
          <input type="hidden" name="is_custom_price" id="isCustomPrice" value="0">
          <div class="mb-3 position-relative">
            <label for="depositAmount" class="label-top-input-special-takhasos">مبلغ بیعانه</label>
            <select name="deposit_amount" id="depositAmount" class="form-select h-50 position-relative">
              <option value="">انتخاب کنید</option>
              <option value="50000">50,000 تومان</option>
              <option value="100000">100,000 تومان</option>
              <option value="150000">150,000 تومان</option>
              <option value="custom">قیمت دلخواه</option>
            </select>
          </div>
          <div class="mb-3 position-relative" id="customPriceContainer" style="display: none;">
            <label for="customPrice" class="label-top-input-special-takhasos">مبلغ دلخواه (تومان)</label>
            <input type="number" name="custom_price" id="customPrice" class="form-control h-50"
              placeholder="مبلغ را وارد کنید" min="0" step="1" required>
          </div>
          <div class="form-check mb-3 position-relative">
            <input class="form-check-input position-relative" type="checkbox" name="no_deposit" id="noDeposit"
              value="1">
            <label class="form-check-label" for="noDeposit">بدون بیعانه</label>
          </div>
          <button type="submit" class="btn my-btn-primary h-50 w-100">ذخیره</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('dr-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
<script>
  $(document).ready(function() {
    // مدیریت dropdown


    const modal = $('#depositModal');
    const form = $('#depositForm');
    const depositSelect = $('#depositAmount');
    const customPriceContainer = $('#customPriceContainer');
    const customPriceInput = $('#customPrice');
    const noDepositCheckbox = $('#noDeposit');
    const modalTitle = $('#depositModalLabel');
    const isCustomPrice = $('#isCustomPrice');
    const clinics = @json($clinics->pluck('name', 'id')->toArray());

    depositSelect.on('change', function() {
      const isCustom = this.value === 'custom';
      customPriceContainer.toggle(isCustom);
      isCustomPrice.val(isCustom ? '1' : '0');
      // اگر قیمت دلخواه انتخاب شده، مقدار deposit_amount را خالی کنیم
      if (isCustom) {
        $(this).val('');
      }
    });

    noDepositCheckbox.on('change', function() {
      const isChecked = this.checked;
      depositSelect.prop('disabled', isChecked);
      customPriceInput.prop('disabled', isChecked);
      customPriceContainer.toggle(!isChecked && depositSelect.val() === 'custom');
      if (isChecked) {
        depositSelect.val('');
        customPriceInput.val('');
        isCustomPrice.val('0');
      }
    });

    form.on('submit', async function(e) {
      e.preventDefault();
      const id = $('#depositId').val();
      const isUpdate = !!id;
      const url = isUpdate ?
        '{{ route('doctors.clinic.deposit.update', ':id') }}'.replace(':id', id) :
        '{{ route('doctors.clinic.deposit.store') }}';
      const submitBtn = $(this).find('button[type="submit"]');
      submitBtn.prop('disabled', true).text('در حال ذخیره...');

      // اعتبارسنجی سمت کلاینت
      if (!noDepositCheckbox.prop('checked') && depositSelect.val() === 'custom' && !customPriceInput.val()) {
        toastr.error('لطفاً مبلغ دلخواه را وارد کنید.');
        submitBtn.prop('disabled', false).text('ذخیره');
        return;
      }

      try {
        const response = await fetch(url, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json',
          },
          body: new FormData(this)
        });
        const data = await response.json();
        if (data.success) {
          toastr.success(data.message);
          updateDepositList(data.deposit);
          modal.modal('hide');
        } else {
          toastr.error(data.message || 'خطایی رخ داد');
          if (data.errors) {
            Object.values(data.errors).forEach(error => toastr.error(error[0]));
          }
        }
      } catch (error) {
        toastr.error('خطا در ارتباط با سرور');
      } finally {
        submitBtn.prop('disabled', false).text('ذخیره');
      }
    });

    $(document).on('click', '.edit-btn', function() {
      const id = $(this).data('id');
      const row = $(`tr[data-id="${id}"], .table-card .card[data-id="${id}"]`);
      const amount = row.find('td:eq(2), .card-body strong:contains("مبلغ")').text().trim() === 'بدون بیعانه' ?
        '' :
        row.find('td:eq(2), .card-body strong:contains("مبلغ")').text().replace(/,/g, '');
      modalTitle.text('ویرایش بیعانه');
      $('#depositId').val(id);
      depositSelect.val(amount && !['50000', '100000', '150000'].includes(amount) ? 'custom' : amount);
      customPriceInput.val(amount && !['50000', '100000', '150000'].includes(amount) ? amount : '');
      noDepositCheckbox.prop('checked', amount === '');
      customPriceContainer.toggle(depositSelect.val() === 'custom');
      isCustomPrice.val(depositSelect.val() === 'custom' ? '1' : '0');
      depositSelect.prop('disabled', noDepositCheckbox.prop('checked'));
      customPriceInput.prop('disabled', noDepositCheckbox.prop('checked'));
      modal.modal('show');
    });

    $(document).on('click', '.delete-btn', function() {
      const id = $(this).data('id');
      const url = '{{ route('doctors.clinic.deposit.destroy', ':id') }}'.replace(':id', id);

      Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: 'این بیعانه حذف خواهد شد و قابل بازگشت نیست!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'بله، حذف کن',
        cancelButtonText: 'خیر'
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            const response = await fetch(url, {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json',
              },
              body: new FormData(form[0])
            });
            const data = await response.json();
            if (data.success) {
              toastr.success(data.message);
              $(`tr[data-id="${id}"], .table-card .card[data-id="${id}"]`).remove();
            } else {
              toastr.error(data.message || 'خطا در حذف');
            }
          } catch (error) {
            toastr.error('خطا در ارتباط با سرور');
          }
        }
      });
    });

    function updateDepositList(deposit) {
      const tbody = $('#depositList');
      const tableCard = $('.table-card');
      const clinicName = deposit.clinic_id ? clinics[deposit.clinic_id] || 'نامشخص' : 'ویزیت آنلاین';
      const rowHtml = `
            <tr data-id="${deposit.id}">
                <td>
                    <button class="btn btn-icon edit-btn" data-id="${deposit.id}">
                        <img src="{{ asset('dr-assets/icons/edit.svg') }}" alt="ویرایش">
                    </button>
                    <button class="btn btn-icon delete-btn" data-id="${deposit.id}">
                        <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
                    </button>
                </td>
                <td>${clinicName}</td>
                <td>${deposit.deposit_amount ? Number(deposit.deposit_amount).toLocaleString() : 'بدون بیعانه'}</td>
            </tr>
        `;
      const cardHtml = `
            <div class="card mb-3 position-relative" data-id="${deposit.id}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>مطب:</strong> ${clinicName}<br>
                            <strong>مبلغ:</strong> ${deposit.deposit_amount ? Number(deposit.deposit_amount).toLocaleString() : 'بدون بیعانه'}
                        </div>
                        <div>
                            <button class="btn btn-icon edit-btn" data-id="${deposit.id}">
                                <img src="{{ asset('dr-assets/icons/edit.svg') }}" alt="ویرایش">
                            </button>
                            <button class="btn btn-icon delete-btn" data-id="${deposit.id}">
                                <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
      const existingRow = $(`tr[data-id="${deposit.id}"]`);
      const existingCard = $(`.table-card .card[data-id="${deposit.id}"]`);
      if (existingRow.length) {
        existingRow.replaceWith(rowHtml);
      } else {
        tbody.append(rowHtml);
      }
      if (existingCard.length) {
        existingCard.replaceWith(cardHtml);
      } else {
        tableCard.append(cardHtml);
      }
    }
  });
</script>
@endsection

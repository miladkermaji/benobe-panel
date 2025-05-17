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
<div class="container-fluid">
  <div class="mt-5 d-flex justify-content-between align-items-center mb-4">
    <h1 class="fs-4 fw-bold">مدیریت بیعانه‌ها</h1>
    <button class="btn my-btn-primary h-50" data-bs-toggle="modal" data-bs-target="#depositModal">
      <i class="fas fa-plus me-2"></i> افزودن بیعانه
    </button>
  </div>
  <div class="card table-responsive">
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
  <!-- کارت‌ها برای موبایل -->
  <div class="table-card">
    @foreach ($deposits as $deposit)
      <div class="card mb-3" data-id="{{ $deposit->id }}">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <strong>مطب:</strong>
              {{ $deposit->clinic_id ? $clinics->find($deposit->clinic_id)->name : 'ویزیت آنلاین' }}<br>
              <strong>مبلغ:</strong>
              {{ $deposit->deposit_amount ? number_format($deposit->deposit_amount) : 'بدون بیعانه' }}
            </div>
            <div>
              <button class="btn btn-icon edit-btn btn-light rounded-circle" data-id="{{ $deposit->id }}">
                <img src="{{ asset('dr-assets/icons/edit.svg') }}" alt="ویرایش">
              </button>
              <button class="btn btn-icon delete-btn btn-light rounded-circle" data-id="{{ $deposit->id }}">
                <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
              </button>
            </div>
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
          <div class="mb-3">
            <label for="depositAmount" class="form-label">مبلغ بیعانه</label>
            <select name="deposit_amount" id="depositAmount" class="form-select h-50">
              <option value="">انتخاب کنید</option>
              <option value="50000">50,000 تومان</option>
              <option value="100000">100,000 تومان</option>
              <option value="150000">150,000 تومان</option>
              <option value="custom">قیمت دلخواه</option>
            </select>
          </div>
          <div class="mb-3" id="customPriceContainer" style="display: none;">
            <label for="customPrice" class="form-label">مبلغ دلخواه (تومان)</label>
            <input type="number" name="custom_price" id="customPrice" class="form-control h-50"
              placeholder="مبلغ را وارد کنید" min="0" step="1" required>
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="no_deposit" id="noDeposit" value="1">
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
    let dropdownOpen = false;
    const selectedClinic = localStorage.getItem('selectedClinic');
    const selectedClinicId = localStorage.getItem('selectedClinicId');

    if (selectedClinic && selectedClinicId) {
      $('.dropdown-label').text(selectedClinic);
      $('.option-card').each(function() {
        if ($(this).attr('data-id') === selectedClinicId) {
          $('.option-card').removeClass('card-active');
          $(this).addClass('card-active');
        }
      });
    } else {
      localStorage.setItem('selectedClinic', 'مشاوره آنلاین به نوبه');
      localStorage.setItem('selectedClinicId', 'default');
    }

    function checkInactiveClinics() {
      const hasInactiveClinics = $('.option-card[data-active="0"]').length > 0;
      $('.dropdown-trigger').toggleClass('warning', hasInactiveClinics);
    }
    checkInactiveClinics();

    $('.dropdown-trigger').on('click', function(event) {
      event.stopPropagation();
      dropdownOpen = !dropdownOpen;
      $(this).toggleClass('border border-primary');
      $('.my-dropdown-menu').toggleClass('d-none');
    });

    $(document).on('click', function() {
      if (dropdownOpen) {
        $('.dropdown-trigger').removeClass('border border-primary');
        $('.my-dropdown-menu').addClass('d-none');
        dropdownOpen = false;
      }
    });

    $('.option-card').on('click', function() {
      const selectedText = $(this).find('.fw-bold.d-block.fs-15').text().trim();
      const selectedId = $(this).attr('data-id');
      $('.option-card').removeClass('card-active');
      $(this).addClass('card-active');
      $('.dropdown-label').text(selectedText);
      localStorage.setItem('selectedClinic', selectedText);
      localStorage.setItem('selectedClinicId', selectedId);
      checkInactiveClinics();
      $('.dropdown-trigger').removeClass('border border-primary');
      $('.my-dropdown-menu').addClass('d-none');
      dropdownOpen = false;
      window.location.href = `${window.location.pathname}?selectedClinicId=${selectedId}`;
    });

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
            <div class="card mb-3" data-id="${deposit.id}">
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

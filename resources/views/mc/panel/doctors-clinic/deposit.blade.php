@extends('mc.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('mc-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('mc-assets/panel/css/doctor-clinic/deposite.css') }}" rel="stylesheet" />

@endsection

@section('site-header')
  {{ 'پنل پزشک | مدیریت بیعانه' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت بیعانه')

<div class="doctor-clinics-container">
  <div class="container py-2 mt-3" dir="rtl">
    <div class="glass-header text-white p-2  shadow-lg">
      <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3 w-100">
        <div class="d-flex flex-column flex-md-row gap-2 w-100 align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-3 mb-2">
            <h1 class="m-0 h4 font-thin text-nowrap  mb-md-0">بیعانه‌های من</h1>
          </div>
          <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2">
            <div class="d-flex gap-2 flex-shrink-0 justify-content-center">
              <div class="search-container position-relative" style="max-width: 100%;">
                <input type="text"
                  class="form-control search-input border-0 shadow-none bg-white text-dark ps-4 rounded-2 text-start"
                  id="depositSearchInput" placeholder="جستجو در بیعانه‌ها..."
                  style="padding-right: 20px; text-align: right; direction: rtl;">
                <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-2"
                  style="z-index: 5; top: 50%; right: 8px;">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                    stroke-width="2">
                    <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
                  </svg>
                </span>
              </div>
              <button
                class="btn btn-gradient-success btn-gradient-success-576 rounded-1 px-3 py-1 d-flex align-items-center gap-1"
                onclick="openXModal('depositModal')">
                <svg style="transform: rotate(180deg)" width="14" height="14" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2">
                  <path d="M12 5v14M5 12h14" />
                </svg>
                <span>افزودن</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="container-fluid px-0">
      <div class="card shadow-sm rounded-2">
        <div class="card-body p-0">
          <!-- Desktop Table View -->
          <div class="table-responsive text-nowrap d-none d-md-block">
            <table class="table table-hover w-100 m-0" id="depositTable">
              <thead>
                <tr>
                  <th class="text-center align-middle" style="width: 60px;">ردیف</th>
                  <th class="align-middle">مرکز درمانی</th>
                  <th class="align-middle">مبلغ (تومان)</th>
                  <th class="text-center align-middle" style="width: 120px;">عملیات</th>
                </tr>
              </thead>
              <tbody id="depositList">
                @foreach ($deposits as $index => $deposit)
                  <tr data-id="{{ $deposit->id }}">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                      @if (Auth::guard('medical_center')->check())
                        {{ $deposit->medical_center_id ? Auth::guard('medical_center')->user()->name : 'ویزیت آنلاین' }}
                      @else
                        {{ $deposit->medical_center_id ? $clinics->find($deposit->medical_center_id)->name : 'ویزیت آنلاین' }}
                      @endif
                    </td>
                    <td>{{ $deposit->deposit_amount ? number_format($deposit->deposit_amount) : 'بدون بیعانه' }}</td>
                    <td class="text-center">
                      <div class="d-flex justify-content-center gap-1">
                        <button class="btn btn-icon edit-btn btn-light rounded-circle" data-id="{{ $deposit->id }}">
                          <img src="{{ asset('mc-assets/icons/edit.svg') }}" alt="ویرایش">
                        </button>
                        <button class="btn btn-icon delete-btn btn-light rounded-circle" data-id="{{ $deposit->id }}">
                          <img src="{{ asset('mc-assets/icons/trash.svg') }}" alt="حذف">
                        </button>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <!-- Mobile Card View -->
          <div class="notes-cards d-md-none">
            @foreach ($deposits as $index => $deposit)
              <div class="note-card mb-3" data-id="{{ $deposit->id }}">
                <div class="note-card-header d-flex justify-content-between align-items-center">
                  <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary">
                      @if (Auth::guard('medical_center')->check())
                        {{ $deposit->medical_center_id ? Auth::guard('medical_center')->user()->name : 'ویزیت آنلاین' }}
                      @else
                        {{ $deposit->medical_center_id ? $clinics->find($deposit->medical_center_id)->name : 'ویزیت آنلاین' }}
                      @endif
                    </span>
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
                    <span class="note-card-label">مرکز درمانی:</span>
                    <span class="note-card-value">
                      @if (Auth::guard('medical_center')->check())
                        {{ $deposit->medical_center_id ? Auth::guard('medical_center')->user()->name : 'ویزیت آنلاین' }}
                      @else
                        {{ $deposit->medical_center_id ? $clinics->find($deposit->medical_center_id)->name : 'ویزیت آنلاین' }}
                      @endif
                    </span>
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
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<x-custom-modal id="depositModal" title="افزودن بیعانه" size="md">
  <form id="depositForm">
    @csrf
    <input type="hidden" name="id" id="depositId">
    @if (Auth::guard('medical_center')->check())
      <input type="hidden" name="selectedClinicId" value="{{ Auth::guard('medical_center')->id() }}">
    @else
      <input type="hidden" name="selectedClinicId" value="{{ $selectedClinicId }}">
    @endif
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
      <input type="text" name="custom_price" id="customPrice" class="form-control h-50"
        placeholder="مبلغ را وارد کنید" pattern="[0-9]*" inputmode="numeric" required>
    </div>
    <div class="form-check mb-3 position-relative">
      <input class="form-check-input position-relative" type="checkbox" name="no_deposit" id="noDeposit"
        value="1">
      <label class="form-check-label" for="noDeposit">بدون بیعانه</label>
    </div>
    <div class="mt-3 d-flex gap-2">
      <button type="button" class="btn btn-secondary flex-grow-1"
        onclick="closeXModal('depositModal')">انصراف</button>
      <button type="button" class="btn my-btn-primary h-50 flex-grow-1" id="submitDepositBtn">ذخیره</button>
    </div>
  </form>
</x-custom-modal>
@endsection

@section('scripts')
<script src="{{ asset('mc-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
<script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>
<script>
  $(document).ready(function() {
    const form = $('#depositForm');
    const depositSelect = $('#depositAmount');
    const customPriceContainer = $('#customPriceContainer');
    const customPriceInput = $('#customPrice');
    const noDepositCheckbox = $('#noDeposit');
    const isCustomPrice = $('#isCustomPrice');
    const clinics = @json($clinics->pluck('name', 'id')->toArray());
    @if (Auth::guard('medical_center')->check())
      const medicalCenterName = '{{ Auth::guard('medical_center')->user()->name }}';
    @endif

    // Function to convert Persian/Farsi numbers to English numbers
    function convertPersianToEnglishNumbers(str) {
      const persianNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
      const englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

      for (let i = 0; i < 10; i++) {
        str = str.replace(new RegExp(persianNumbers[i], 'g'), englishNumbers[i]);
      }
      return str;
    }

    // Function to convert English numbers to Persian/Farsi numbers for display
    function convertEnglishToPersianNumbers(str) {
      const englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
      const persianNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

      for (let i = 0; i < 10; i++) {
        str = str.replace(new RegExp(englishNumbers[i], 'g'), persianNumbers[i]);
      }
      return str;
    }

    depositSelect.on('change', function() {
      const isCustom = this.value === 'custom';
      customPriceContainer.toggle(isCustom);
      $('#isCustomPrice').val(isCustom ? '1' : '0');
      customPriceInput.prop('required', isCustom);
      if (isCustom) {
        // Don't clear the select value when custom is selected
        customPriceInput.focus();
      } else {
        customPriceInput.val('');
      }
    });

    noDepositCheckbox.on('change', function() {
      const isChecked = this.checked;
      depositSelect.prop('disabled', isChecked);
      customPriceInput.prop('disabled', isChecked);

      if (isChecked) {
        depositSelect.val('');
        customPriceInput.val('');
        $('#isCustomPrice').val('0');
        customPriceContainer.hide();
      } else {
        // Re-enable and show custom price container if it was custom before
        if (depositSelect.val() === 'custom') {
          customPriceContainer.show();
        }
      }

      customPriceInput.prop('required', !isChecked && depositSelect.val() === 'custom');
    });

    // Handle Enter key in form inputs
    form.find('input, select').on('keypress', function(e) {
      if (e.which === 13) { // Enter key
        e.preventDefault();
        $('#submitDepositBtn').click();
      }
    });

    // Prevent custom price input from being cleared in Chrome
    let customPriceValue = '';

    customPriceInput.on('input', function() {
      // Convert Persian numbers to English and only allow numbers
      let value = convertPersianToEnglishNumbers($(this).val());
      value = value.replace(/[^0-9]/g, '');
      $(this).val(value);
      customPriceValue = value;
    });

    customPriceInput.on('blur', function(e) {
      // Store the current value
      customPriceValue = $(this).val();
    });

    customPriceInput.on('focus', function() {
      // Restore value if it was cleared
      if (!$(this).val() && customPriceValue) {
        $(this).val(customPriceValue);
      }
    });

    // Also prevent on focusout
    customPriceInput.on('focusout', function(e) {
      // Ensure value is preserved
      if (!$(this).val() && customPriceValue) {
        setTimeout(() => {
          $(this).val(customPriceValue);
        }, 10);
      }
    });

    // Prevent form reset when clicking outside
    $(document).on('click', function(e) {
      if (!$(e.target).closest('#depositModal').length) {
        // Click outside modal - preserve custom price value
        if (customPriceValue && !customPriceInput.val()) {
          setTimeout(() => {
            customPriceInput.val(customPriceValue);
          }, 50);
        }
      }
    });

    // Additional protection for Chrome
    customPriceInput.attr('autocomplete', 'off');
    customPriceInput.attr('data-chrome-fix', 'true');

    // Prevent the input from losing focus and value
    customPriceInput.on('mouseleave', function() {
      if (customPriceValue && !$(this).val()) {
        $(this).val(customPriceValue);
      }
    });

    // Monitor for value changes and restore if needed
    setInterval(() => {
      if (customPriceValue && !customPriceInput.val() && depositSelect.val() === 'custom') {
        customPriceInput.val(customPriceValue);
      }
    }, 100);

    // Prevent the input from being cleared when clicking the submit button
    $('#submitDepositBtn').on('mousedown', function() {
      if (customPriceValue && !customPriceInput.val()) {
        customPriceInput.val(customPriceValue);
      }
    });

    $('#submitDepositBtn').on('click', async function() {
      // Ensure custom price value is preserved before submission
      if (customPriceValue && !customPriceInput.val() && depositSelect.val() === 'custom') {
        customPriceInput.val(customPriceValue);
      }

      const id = $('#depositId').val();
      const isUpdate = !!id;
      const url = isUpdate ?
        '{{ route('doctors.clinic.deposit.update', ':id') }}'.replace(':id', id) :
        '{{ route('doctors.clinic.deposit.store') }}';
      const submitBtn = $(this);
      submitBtn.prop('disabled', true).text('در حال ذخیره...');

      // اعتبارسنجی سمت کلاینت
      const isNoDeposit = noDepositCheckbox.prop('checked');
      const selectedAmount = depositSelect.val();
      const customPrice = customPriceInput.val();

      console.log('Form validation:', {
        isNoDeposit,
        selectedAmount,
        customPrice,
        isCustomPrice: $('#isCustomPrice').val(),
        customPriceValue
      });

      if (!isNoDeposit) {
        // Check if custom price is selected (either by select or by having a custom value)
        const isCustomSelected = selectedAmount === 'custom' || (customPrice && !['50000', '100000', '150000']
          .includes(customPrice));

        if (isCustomSelected && !customPrice) {
          toastr.error('لطفاً مبلغ دلخواه را وارد کنید.');
          submitBtn.prop('disabled', false).text('ذخیره');
          customPriceInput.focus();
          return;
        }

        if (!selectedAmount && !customPrice) {
          toastr.error('لطفاً مبلغ بیعانه را انتخاب کنید یا مبلغ دلخواه وارد کنید.');
          submitBtn.prop('disabled', false).text('ذخیره');
          depositSelect.focus();
          return;
        }
      }

      try {
        // Prepare form data
        const formData = new FormData(form[0]);

        // Ensure is_custom_price is set correctly
        const isCustomSelected = selectedAmount === 'custom' || (customPrice && !['50000', '100000', '150000']
          .includes(customPrice));

        if (isCustomSelected) {
          formData.set('is_custom_price', '1');
          // If custom is selected but no amount in select, set deposit_amount to custom
          if (selectedAmount === 'custom') {
            formData.set('deposit_amount', 'custom');
          }
        } else {
          formData.set('is_custom_price', '0');
        }

        // If no deposit is checked, ensure deposit_amount is empty
        if (isNoDeposit) {
          formData.set('deposit_amount', '');
          formData.set('custom_price', '');
        }

        const response = await fetch(url, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json',
          },
          body: formData
        });
        const data = await response.json();
        if (data.success) {
          toastr.success(data.message);
          if (isUpdate) {
            updateDepositItem(data.deposit);
          } else {
            addDepositItem(data.deposit);
          }
          resetForm();
          closeXModal('depositModal');
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
      const row = $(`tr[data-id="${id}"]`);
      const card = $(`.note-card[data-id="${id}"]`);

      let amount = '';
      if (row.length) {
        // Desktop view
        const amountText = row.find('td:eq(2)').text().trim();
        amount = amountText === 'بدون بیعانه' ? '' : amountText.replace(/,/g, '');
      } else if (card.length) {
        // Mobile view
        const amountElement = card.find('.note-card-item').filter(function() {
          return $(this).find('.note-card-label').text().includes('مبلغ');
        }).find('.note-card-value');
        const amountText = amountElement.text().trim();
        amount = amountText === 'بدون بیعانه' ? '' : amountText.replace(/,/g, '');
      }

      // modalTitle.text('ویرایش بیعانه'); // This line is removed as per the edit hint
      $('#depositId').val(id);

      // Determine if it's a custom price
      const isCustomPrice = amount && !['50000', '100000', '150000'].includes(amount);

      if (isCustomPrice) {
        depositSelect.val('custom');
        customPriceInput.val(amount);
        $('#isCustomPrice').val('1');
        customPriceContainer.show();
      } else {
        depositSelect.val(amount);
        customPriceInput.val('');
        $('#isCustomPrice').val('0');
        customPriceContainer.hide();
      }

      noDepositCheckbox.prop('checked', amount === '');
      depositSelect.prop('disabled', noDepositCheckbox.prop('checked'));
      customPriceInput.prop('disabled', noDepositCheckbox.prop('checked'));
      openXModal('depositModal');
    });

    $(document).on('click', '.delete-btn', function() {
      const id = $(this).data('id');
      const url = '{{ route('doctors.clinic.deposit.destroy', ':id') }}'.replace(':id', id);

      Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: 'این بیعانه حذف خواهد شد و قابل بازگشت نیست!',

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
              }
            });
            const data = await response.json();
            if (data.success) {
              toastr.success(data.message);
              removeDepositItem(id);
            } else {
              toastr.error(data.message || 'خطا در حذف');
            }
          } catch (error) {
            toastr.error('خطا در ارتباط با سرور');
          }
        }
      });
    });

    function resetForm() {
      form[0].reset();
      $('#depositId').val('');
      customPriceContainer.hide();
      depositSelect.prop('disabled', false);
      customPriceInput.prop('disabled', false);
      $('#isCustomPrice').val('0');
      noDepositCheckbox.prop('checked', false);
      customPriceValue = ''; // Reset the stored custom price value
    }

    function addDepositItem(deposit) {
      @if (Auth::guard('medical_center')->check())
        const clinicName = deposit.medical_center_id ? medicalCenterName : 'ویزیت آنلاین';
      @else
        const clinicName = deposit.medical_center_id ? clinics[deposit.medical_center_id] || 'نامشخص' :
          'ویزیت آنلاین';
      @endif
      const amountText = deposit.deposit_amount ? Number(deposit.deposit_amount).toLocaleString() : 'بدون بیعانه';

      // Desktop table row
      const newRowIndex = $('#depositList tr').length + 1;
      const rowHtml = `
        <tr data-id="${deposit.id}">
          <td class="text-center">${newRowIndex}</td>
          <td>${clinicName}</td>
          <td>${amountText}</td>
          <td class="text-center">
            <div class="d-flex justify-content-center gap-1">
              <button class="btn btn-icon edit-btn btn-light rounded-circle" data-id="${deposit.id}">
                <img src="{{ asset('mc-assets/icons/edit.svg') }}" alt="ویرایش">
              </button>
              <button class="btn btn-icon delete-btn btn-light rounded-circle" data-id="${deposit.id}">
                <img src="{{ asset('mc-assets/icons/trash.svg') }}" alt="حذف">
              </button>
            </div>
          </td>
        </tr>
      `;

      // Mobile card
      const cardHtml = `
        <div class="note-card mb-3" data-id="${deposit.id}">
          <div class="note-card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-primary-subtle text-primary">
                ${clinicName}
              </span>
            </div>
            <div class="d-flex gap-1">
              <button class="btn btn-sm btn-gradient-success px-2 py-1 edit-btn" data-id="${deposit.id}" title="ویرایش">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                </svg>
              </button>
              <button class="btn btn-sm btn-gradient-danger px-2 py-1 delete-btn" data-id="${deposit.id}" title="حذف">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                </svg>
              </button>
            </div>
          </div>
          <div class="note-card-body">
            <div class="note-card-item">
              <span class="note-card-label">مرکز درمانی:</span>
              <span class="note-card-value">${clinicName}</span>
            </div>
            <div class="note-card-item">
              <span class="note-card-label">مبلغ:</span>
              <span class="note-card-value">${amountText}</span>
            </div>
          </div>
        </div>
      `;

      $('#depositList').append(rowHtml);
      $('.notes-cards').append(cardHtml);

      // Update row numbers
      updateRowNumbers();
    }

    function updateDepositItem(deposit) {
      @if (Auth::guard('medical_center')->check())
        const clinicName = deposit.medical_center_id ? medicalCenterName : 'ویزیت آنلاین';
      @else
        const clinicName = deposit.medical_center_id ? clinics[deposit.medical_center_id] || 'نامشخص' :
          'ویزیت آنلاین';
      @endif
      const amountText = deposit.deposit_amount ? Number(deposit.deposit_amount).toLocaleString() : 'بدون بیعانه';

      // Update desktop table row
      const row = $(`tr[data-id="${deposit.id}"]`);
      if (row.length) {
        row.find('td:eq(1)').text(clinicName);
        row.find('td:eq(2)').text(amountText);
      }

      // Update mobile card
      const card = $(`.note-card[data-id="${deposit.id}"]`);
      if (card.length) {
        card.find('.badge').text(clinicName);
        card.find('.note-card-value').each(function() {
          const label = $(this).prev('.note-card-label').text();
          if (label.includes('مرکز درمانی')) {
            $(this).text(clinicName);
          } else if (label.includes('مبلغ')) {
            $(this).text(amountText);
          }
        });
      }
    }

    function removeDepositItem(id) {
      // Remove from desktop table
      $(`tr[data-id="${id}"]`).remove();

      // Remove from mobile cards
      $(`.note-card[data-id="${id}"]`).remove();

      // Update row numbers
      updateRowNumbers();
    }

    function updateRowNumbers() {
      $('#depositList tr').each(function(index) {
        $(this).find('td:first').text(index + 1);
      });
    }

    // Reset form when modal is closed
    document.addEventListener('DOMContentLoaded', function() {
      const modal = document.getElementById('depositModal');
      if (modal) {
        modal.addEventListener('x-modal-closed', function() {
          resetForm();
        });
      }
    });
  });
</script>
@endsection

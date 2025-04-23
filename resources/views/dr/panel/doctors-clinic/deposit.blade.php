@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />

  <style>
    :root {
      --primary: #4f46e5;
      --secondary: #f9fafb;
      --text: #1f2a44;
      --danger: #dc2626;
      --success: #10b981;
      --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
      --border-radius: 10px;
      --transition: all 0.2s ease-in-out;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }

    .header {
      background: white;
      padding: 20px;
      border-radius: var(--border-radius);
      box-shadow: var(--shadow);
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 24px;
    }

    .header h1 {
      font-size: 1.75rem;
      color: var(--text);
      font-weight: 700;
      margin: 0;
    }

    .card {
      background: white;
      border-radius: var(--border-radius);
      box-shadow: var(--shadow);
      padding: 24px;
      transition: var(--transition);
    }

    .card:hover {
      transform: translateY(-2px);
      box-shadow: 0 7px 14px rgba(0, 0, 0, 0.1);
    }

    .table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0 12px;
      direction: rtl;
      /* جهت جدول از راست به چپ */
    }

    .table th {
      background: var(--secondary);
      color: var(--text);
      font-weight: 600;
      font-size: 0.9rem;
      text-transform: uppercase;
      padding: 12px 20px;
      text-align: right;
    }

    .table td {
      background: white;
      color: #6b7280;
      padding: 16px 20px;
      text-align: right;
      border-radius: 8px;
      transition: var(--transition);
    }

    .table tr:hover td {
      background: #f3f4f6;
    }

    /* عملیات که چپه، استایل جدا داره */
    .table th:first-child,
    .table td:first-child {
      text-align: left;
    }

    /* معکوس کردن ترتیب ستون‌ها با flex روی ردیف‌ها */
    .table tr {
      display: flex;
      flex-direction: row-reverse;
      /* معکوس کردن جهت از چپ به راست */
    }

    .table th,
    .table td {
      flex: 1;
      /* تقسیم فضای برابر بین ستون‌ها */
    }

    /* تنظیم عرض ستون عملیات */
    .table th:first-child,
    .table td:first-child {
      flex: 0 0 auto;
      /* عرض ثابت برای ستون عملیات */
      width: 120px;
      /* عرض مشخص برای دکمه‌ها */
    }

    .btn {
      padding: 10px 20px;
      border-radius: var(--border-radius);
      font-weight: 600;
      transition: var(--transition);
    }

    .my-btn-primary {
      background: var(--primary);
      color: white;
      border: none;
    }

    .my-btn-primary:hover {
      background: #4338ca;
      transform: translateY(-1px);
    }

    .btn-icon {
      padding: 8px;
      background: #f3f4f6;
      border: none;
    }

    .btn-icon:hover {
      background: #e5e7eb;
    }

    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.6);
      align-items: center;
      justify-content: center;
      z-index: 1050;
    }

    .modal-content {
      background: white;
      padding: 16px;
      border-radius: var(--border-radius);
      max-width: 500px;
      width: 100%;
      box-shadow: var(--shadow);
      animation: slideIn 0.2s ease-out;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 16px;
    }

    .modal-header h2 {
      font-size: 1.5rem;
      color: var(--text);
      font-weight: 700;
      margin: 0;
    }

    .close-modal {
      background: none;
      border: none;
      font-size: 24px;
      color: #9ca3af;
      cursor: pointer;
      transition: var(--transition);
    }

    .close-modal:hover {
      color: var(--text);
    }

    .form-group {
      margin-bottom: 16px;
    }

    .form-group label {
      color: var(--text);
      font-weight: 600;
      margin-bottom: 8px;
      display: block;
    }

    .form-control,
    .form-select {
      width: 100%;
      padding: 12px 16px;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      background: #f9fafb;
      color: var(--text);
      transition: var(--transition);
    }

    .form-control:focus,
    .form-select:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
      outline: none;
    }

    .checkbox-wrapper {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-top: 8px;
    }

    .checkbox-wrapper input[type="checkbox"] {
      appearance: none;
      width: 20px;
      height: 20px;
      border: 2px solid #d1d5db;
      border-radius: 4px;
      background: white;
      cursor: pointer;
      position: relative;
      transition: var(--transition);
    }

    .checkbox-wrapper input[type="checkbox"]:checked {
      background: var(--primary);
      border-color: var(--primary);
    }

    .checkbox-wrapper input[type="checkbox"]:checked::after {
      content: '✔';
      color: white;
      font-size: 14px;
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
    }

    .checkbox-wrapper label {
      color: var(--text);
      font-weight: 500;
      line-height: 1;
    }
  </style>
@endsection

@section('site-header')
  {{ 'پنل مدیریت | مدیریت بیعانه' }}
@endsection

@section('content')
@section('bread-crumb-title', 'مدیریت بیعانه')
<div class="container">
  <div class="header">
    <h1>مدیریت بیعانه‌ها</h1>
    <button class="btn my-btn-primary" id="addDepositBtn">+ افزودن بیعانه</button>
  </div>
  <div class="card">
    <table class="table">
      <thead>
        <tr>
          <th>عملیات</th> <!-- ستون عملیات اول میاد -->
          <th>مطب</th>
          <th>مبلغ (تومان)</th>
        </tr>
      </thead>
      <tbody id="depositList">
        @foreach ($deposits as $deposit)
          <tr data-id="{{ $deposit->id }}">
            <td>
              <button class="btn btn-icon edit-btn" data-id="{{ $deposit->id }}">
                <img src="{{ asset('dr-assets/icons/edit.svg') }}" alt="ویرایش">
              </button>
              <button class="btn btn-icon delete-btn" data-id="{{ $deposit->id }}">
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
</div>

<div class="modal" id="depositModal">
  <div class="modal-content">
    <div class="modal-header">
      <h2 id="modalTitle">افزودن بیعانه</h2>
      <button class="close-modal" id="closeModal">×</button>
    </div>
    <form id="depositForm">
      @csrf
      <input type="hidden" name="id" id="depositId">
      <input type="hidden" name="selectedClinicId" value="{{ $selectedClinicId }}">
      <input type="hidden" name="is_custom_price" id="isCustomPrice" value="0">
      <div class="form-group">
        <label for="depositAmount">مبلغ بیعانه</label>
        <select name="deposit_amount" id="depositAmount" class="form-select">
          <option value="">انتخاب کنید</option>
          <option value="50000">50,000 تومان</option>
          <option value="100000">100,000 تومان</option>
          <option value="150000">150,000 تومان</option>
          <option value="custom">قیمت دلخواه</option>
        </select>
      </div>
      <div class="form-group" id="customPriceContainer" style="display: none;">
        <label for="customPrice">مبلغ دلخواه (تومان)</label>
        <input type="number" name="custom_price" id="customPrice" class="form-control" placeholder="مبلغ را وارد کنید"
          min="0">
      </div>
      <div class="checkbox-wrapper d-flex align-items-center">
        <input type="checkbox" name="no_deposit" id="noDeposit" value="1">
        <label for="noDeposit">بدون بیعانه</label>
      </div>
      <button type="submit" class="btn my-btn-primary" style="width: 100%; margin-top: 16px;">ذخیره</button>
    </form>
  </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('dr-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
<script>
  $(document).ready(function() {
    let dropdownOpen = false;
    let selectedClinic = localStorage.getItem('selectedClinic');
    let selectedClinicId = localStorage.getItem('selectedClinicId');
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
      var hasInactiveClinics = $('.option-card[data-active="0"]').length > 0;
      if (hasInactiveClinics) {
        $('.dropdown-trigger').addClass('warning');
      } else {
        $('.dropdown-trigger').removeClass('warning');
      }
    }
    checkInactiveClinics();
    $('.dropdown-trigger').on('click', function(event) {
      event.stopPropagation();
      dropdownOpen = !dropdownOpen;
      $(this).toggleClass('border border-primary');
      $('.my-dropdown-menu').toggleClass('d-none');
      setTimeout(() => {
        dropdownOpen = $('.my-dropdown-menu').is(':visible');
      }, 100);
    });
    $(document).on('click', function() {
      if (dropdownOpen) {
        $('.dropdown-trigger').removeClass('border border-primary');
        $('.my-dropdown-menu').addClass('d-none');
        dropdownOpen = false;
      }
    });
    $('.my-dropdown-menu').on('click', function(event) {
      event.stopPropagation();
    });
    $('.option-card').on('click', function() {
      var selectedText = $(this).find('.fw-bold.d-block.fs-15').text().trim();
      var selectedId = $(this).attr('data-id');
      $('.option-card').removeClass('card-active');
      $(this).addClass('card-active');
      $('.dropdown-label').text(selectedText);
      localStorage.setItem('selectedClinic', selectedText);
      localStorage.setItem('selectedClinicId', selectedId);
      checkInactiveClinics();
      $('.dropdown-trigger').removeClass('border border-primary');
      $('.my-dropdown-menu').addClass('d-none');
      dropdownOpen = false;
      window.location.href = window.location.pathname + "?selectedClinicId=" + selectedId;
    });
  });
  $(document).ready(function() {
    const modal = $('#depositModal');
    const addBtn = $('#addDepositBtn');
    const closeModal = $('#closeModal');
    const form = $('#depositForm');
    const depositSelect = $('#depositAmount');
    const customPriceContainer = $('#customPriceContainer');
    const customPriceInput = $('#customPrice');
    const noDepositCheckbox = $('#noDeposit');
    const modalTitle = $('#modalTitle');
    const isCustomPrice = $('#isCustomPrice');
    const clinics = @json($clinics->pluck('name', 'id')->toArray());

    // Modal controls
    addBtn.on('click', function() {
      resetForm();
      modalTitle.text('افزودن بیعانه');
      modal.css('display', 'flex');
    });

    closeModal.on('click', function() {
      modal.css('display', 'none');
    });

    // Form controls
    depositSelect.on('change', function() {
      customPriceContainer.css('display', this.value === 'custom' ? 'block' : 'none');
      isCustomPrice.val(this.value === 'custom' ? '1' : '0');
    });

    noDepositCheckbox.on('change', function() {
      depositSelect.prop('disabled', this.checked);
      customPriceInput.prop('disabled', this.checked);
      customPriceContainer.css('display', this.checked ? 'none' : (depositSelect.val() === 'custom' ? 'block' :
        'none'));
    });

    // Form submission
    form.on('submit', async function(e) {
      e.preventDefault();
      const id = $('#depositId').val();
      const url = id ? `{{ route('doctors.clinic.deposit.update', '') }}/${id}` :
        '{{ route('doctors.clinic.deposit.store') }}';
      const submitBtn = $(this).find('button[type="submit"]');
      submitBtn.prop('disabled', true).text('در حال ذخیره...');

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
          Swal.fire({
            icon: 'success',
            title: 'موفقیت',
            text: data.message,
            timer: 2000,
            showConfirmButton: false
          });
          updateDepositList(data.deposit);
          modal.css('display', 'none');
        } else {
          Swal.fire({
            icon: 'error',
            title: 'خطا',
            text: data.message || 'خطایی رخ داد'
          });
        }
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'خطا',
          text: 'خطا در ارتباط با سرور'
        });
      } finally {
        submitBtn.prop('disabled', false).text('ذخیره');
      }
    });

    // Edit handler
    $(document).on('click', '.edit-btn', function() {
      const id = $(this).data('id');
      const row = $(`tr[data-id="${id}"]`);
      const amount = row.find('td:eq(2)').text().trim() === 'بدون بیعانه' ? '' : row.find('td:eq(2)').text()
        .replace(/,/g, ''); // تغییر به td:eq(2) چون مبلغ حالا ستون سومه
      modal.css('display', 'flex');
      modalTitle.text('ویرایش بیعانه');
      $('#depositId').val(id);
      depositSelect.val(amount ? (['50000', '100000', '150000'].includes(amount) ? amount : 'custom') : '');
      customPriceInput.val(amount && !['50000', '100000', '150000'].includes(amount) ? amount : '');
      noDepositCheckbox.prop('checked', !amount);
      customPriceContainer.css('display', depositSelect.val() === 'custom' ? 'block' : 'none');
      isCustomPrice.val(depositSelect.val() === 'custom' ? '1' : '0');
    });

    // Delete handler
    $(document).on('click', '.delete-btn', function() {
      const id = $(this).data('id');
      const url = `{{ route('doctors.clinic.deposit.destroy', '') }}/${id}`;

      Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: 'این بیعانه حذف خواهد شد و قابل بازگشت نیست!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'بله، حذف کن',
        cancelButtonText: 'خیر'
      }).then((result) => {
        if (result.isConfirmed) {
          fetch(url, {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json',
              },
              body: new FormData(form[0])
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                Swal.fire({
                  icon: 'success',
                  title: 'حذف شد',
                  text: data.message,
                  timer: 2000,
                  showConfirmButton: false
                });
                $(`tr[data-id="${id}"]`).remove();
              } else {
                Swal.fire({
                  icon: 'error',
                  title: 'خطا',
                  text: data.message || 'خطا در حذف'
                });
              }
            })
            .catch(() => {
              Swal.fire({
                icon: 'error',
                title: 'خطا',
                text: 'خطا در ارتباط با سرور'
              });
            });
        }
      });
    });

    // Update table
    function updateDepositList(deposit) {
      const tbody = $('#depositList');
      const existingRow = $(`tr[data-id="${deposit.id}"]`);
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
      if (existingRow.length) {
        existingRow.replaceWith(rowHtml);
      } else {
        tbody.append(rowHtml);
      }
    }

    function resetForm() {
      form[0].reset();
      depositSelect.prop('disabled', false);
      customPriceInput.prop('disabled', false);
      customPriceContainer.css('display', 'none');
      isCustomPrice.val('0');
      $('#depositId').val('');
    }
  });
</script>
@endsection

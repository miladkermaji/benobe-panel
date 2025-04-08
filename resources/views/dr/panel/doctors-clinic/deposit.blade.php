@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <style>
    :root {
      --primary: #3b82f6;
      /* Softer, modern blue */
      --secondary: #f8fafc;
      /* Light gray for background */
      --text: #1e293b;
      /* Darker, elegant text color */
      --danger: #ef4444;
      /* Vibrant red */
      --shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
      /* Softer shadow */
      --border-radius: 12px;
      /* More rounded corners */
      --transition: all 0.3s ease;
      /* Smooth transitions */
    }

    .container {
      max-width: 1100px;
      /* Slightly wider container */
      margin: 0 auto;
    }

    .header {
      background: white;
      padding: 20px 28px;
      border-radius: var(--border-radius);
      box-shadow: var(--shadow);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .header h1 {
      font-size: 1.5rem;
      /* Larger, bolder title */
      color: var(--text);
      margin: 0;
      font-weight: 700;
      letter-spacing: -0.025em;
    }

    .card {
      background: white;
      border-radius: var(--border-radius);
      box-shadow: var(--shadow);
      padding: 24px;
      overflow: hidden;
      transition: var(--transition);
    }

    .card:hover {
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
      /* Subtle hover effect */
    }

    .table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0 8px;
      /* Space between rows */
    }

    .table th,
    .table td {
      padding: 14px 20px;
      text-align: right;
      border-bottom: none;
      /* Remove default border */
    }

    .table th {
      background: #f1f5f9;
      /* Softer header background */
      color: var(--text);
      font-weight: 700;
      font-size: 0.95rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    .table td {
      color: #64748b;
      /* Softer text color */
      background: #fff;
      border-radius: 8px;
      transition: var(--transition);
    }

    .table tr:hover td {
      background: #f8fafc;
      /* Subtle row hover effect */
    }

    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      /* Darker overlay */
      align-items: center;
      justify-content: center;
      z-index: 1000;
      backdrop-filter: blur(2px);
      /* Modern blur effect */
    }

    .modal-content {
      background: white;
      padding: 12px;
      border-radius: var(--border-radius);
      width: 100%;
      max-width: 480px;
      /* Slightly wider modal */
      box-shadow: var(--shadow);
      transform: scale(0.95);
      transition: var(--transition);
    }

    .modal[style*="display: flex"] .modal-content {
      transform: scale(1);
      /* Smooth scale-in animation */
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 24px;
    }

    .modal-header h2 {
      font-size: 1.25rem;
      color: var(--text);
      margin: 0;
      font-weight: 700;
    }

    .close-modal {
      background: none;
      border: none;
      font-size: 28px;
      color: #94a3b8;
      /* Softer gray */
      cursor: pointer;
      padding: 0;
      transition: var(--transition);
    }

    .close-modal:hover {
      color: var(--text);
      transform: rotate(90deg);
      /* Fun close animation */
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      color: var(--text);
      font-weight: 600;
      margin-bottom: 8px;
      font-size: 0.95rem;
    }

    .form-control,
    .form-select {
      width: 100%;
      padding: 12px 16px;
      /* Larger input fields */
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      background: #f9fafb;
      transition: var(--transition);
      font-size: 1rem;
      color: var(--text);
    }

    .form-control:focus,
    .form-select:focus {
      border-color: var(--primary);
      outline: none;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
      /* Softer focus ring */
    }

    .checkbox-wrapper {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 20px;
    }

    .checkbox-wrapper input {
      accent-color: var(--primary);
      width: 18px;
      height: 18px;
      cursor: pointer;
    }

    .checkbox-wrapper label {
      font-weight: 500;
      color: var(--text);
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
    <button class="btn btn-primary h-50" id="addDepositBtn">افزودن بیعانه جدید</button>
  </div>
  <div class="card">
    <table class="table table-light">
      <thead>
        <tr>
          <th>مبلغ (تومان)</th>
          <th>مطب</th>
          <th>عملیات</th>
        </tr>
      </thead>
      <tbody id="depositList">
        @foreach ($deposits as $deposit)
          <tr data-id="{{ $deposit->id }}">
            <td>{{ $deposit->deposit_amount ? number_format($deposit->deposit_amount) : 'بدون بیعانه' }}</td>
            <td>{{ $deposit->clinic_id ? $clinics->find($deposit->clinic_id)->name : 'ویزیت آنلاین' }}</td>
            <td>
              <button class="btn btn-light edit-btn rounded-circle" data-id="{{ $deposit->id }}"><img
                  src="{{ asset('dr-assets/icons/edit.svg') }}" alt=""></button>
              <button class="btn btn-light delete-btn rounded-circle" data-id="{{ $deposit->id }}"><img
                  src="{{ asset('dr-assets/icons/trash.svg') }}" alt=""></button>
            </td>
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
      <div class="form-group mt-3" id="customPriceContainer" style="display: none;">
        <label for="customPrice">مبلغ دلخواه (تومان)</label>
        <input type="number" name="custom_price" id="customPrice" class="form-control h-50"
          placeholder="مبلغ را وارد کنید" min="0">
      </div>
      <div class="checkbox-wrapper d-flex align-items-center mt-2">
        <div>
          <input type="checkbox" name="no_deposit" id="noDeposit" value="1">

        </div>
        <div>
          <label for="noDeposit">بدون بیعانه</label>

        </div>
      </div>
      <button type="submit" class="btn btn-primary h-50" style="width: 100%;">ذخیره</button>
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
      localStorage.setItem('selectedClinic', 'ویزیت آنلاین به نوبه');
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
      var selectedText = $(this).find('.font-weight-bold.d-block.fs-15').text().trim();
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
  document.addEventListener("DOMContentLoaded", function() {
    const modal = document.getElementById('depositModal');
    const addBtn = document.getElementById('addDepositBtn');
    localStorage.getItem('selectedClinicId') === 'default' ? addBtn.setAttribute('disabled', true) : ''
    const closeModal = document.getElementById('closeModal');
    const form = document.getElementById('depositForm');
    const depositSelect = document.getElementById('depositAmount');
    const customPriceContainer = document.getElementById('customPriceContainer');
    const customPriceInput = document.getElementById('customPrice');
    const noDepositCheckbox = document.getElementById('noDeposit');
    const modalTitle = document.getElementById('modalTitle');
    const isCustomPrice = document.getElementById('isCustomPrice');
    const clinics = @json($clinics->pluck('name', 'id')->toArray());
    // Modal controls
    addBtn.addEventListener('click', () => {
      resetForm();
      modalTitle.textContent = 'افزودن بیعانه';
      modal.style.display = 'flex';
    });
    closeModal.addEventListener('click', () => modal.style.display = 'none');
    // Form controls
    depositSelect.addEventListener('change', function() {
      customPriceContainer.style.display = this.value === 'custom' ? 'block' : 'none';
      isCustomPrice.value = this.value === 'custom' ? '1' : '0';
    });
    noDepositCheckbox.addEventListener('change', function() {
      depositSelect.disabled = this.checked;
      customPriceInput.disabled = this.checked;
      customPriceContainer.style.display = this.checked ? 'none' :
        (depositSelect.value === 'custom' ? 'block' : 'none');
    });
    // Form submission
    form.addEventListener('submit', async function(e) {
      e.preventDefault();
      const id = document.getElementById('depositId').value;
      const url = id ? `{{ route('doctors.clinic.deposit.update', '') }}/${id}` :
        '{{ route('doctors.clinic.deposit.store') }}';
      const submitBtn = this.querySelector('button[type="submit"]');
      submitBtn.disabled = true;
      submitBtn.textContent = 'در حال ذخیره...';
      try {
        const response = await fetch(url, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
          },
          body: new FormData(this)
        });
        const data = await response.json();
        if (data.success) {
          toastr.success(data.message);
          updateDepositList(data.deposit);
          modal.style.display = 'none';
        } else {
          toastr.error(data.message || 'خطایی رخ داد');
          if (data.errors) {
            Object.values(data.errors).forEach(error => toastr.error(error[0]));
          }
        }
      } catch (error) {
        toastr.error('خطا در ارتباط با سرور');
      } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'ذخیره';
      }
    });
    // Edit handler
    document.querySelectorAll('.edit-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const row = document.querySelector(`tr[data-id="${id}"]`);
        const amount = row.cells[0].textContent.trim() === 'بدون بیعانه' ? '' :
          row.cells[0].textContent.replace(/,/g, '');
        modal.style.display = 'flex';
        modalTitle.textContent = 'ویرایش بیعانه';
        document.getElementById('depositId').value = id;
        depositSelect.value = amount ? (['50000', '100000', '150000'].includes(amount) ?
          amount : 'custom') : '';
        customPriceInput.value = amount && !['50000', '100000', '150000'].includes(amount) ? amount : '';
        noDepositCheckbox.checked = !amount;
        customPriceContainer.style.display = depositSelect.value === 'custom' ? 'block' : 'none';
        isCustomPrice.value = depositSelect.value === 'custom' ? '1' : '0';
      });
    });
    // Delete handler
    document.querySelectorAll('.delete-btn').forEach(btn => {
      btn.addEventListener('click', async function() {
        if (!confirm('آیا از حذف این بیعانه مطمئن هستید؟')) return;
        const id = this.dataset.id;
        const url = `{{ route('doctors.clinic.deposit.destroy', '') }}/${id}`;
        try {
          const response = await fetch(url, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
              'Accept': 'application/json',
            },
            body: new FormData(form)
          });
          const data = await response.json();
          if (data.success) {
            toastr.success(data.message);
            document.querySelector(`tr[data-id="${id}"]`).remove();
          } else {
            toastr.error(data.message || 'خطا در حذف');
          }
        } catch (error) {
          toastr.error('خطا در ارتباط با سرور');
        }
      });
    });
    // Update table
    function updateDepositList(deposit) {
      const tbody = document.getElementById('depositList');
      const existingRow = document.querySelector(`tr[data-id="${deposit.id}"]`);
      const clinicName = deposit.clinic_id ? clinics[deposit.clinic_id] || 'نامشخص' : 'ویزیت آنلاین';
      const rowHtml = `
                    <tr data-id="${deposit.id}">
                        <td>${deposit.deposit_amount ? Number(deposit.deposit_amount).toLocaleString() : 'بدون بیعانه'}</td>
                        <td>${clinicName}</td>
                    
                         <td>
              <button class="btn btn-light edit-btn rounded-circle" data-id="${deposit.id}"><img src="{{ asset('dr-assets/icons/edit.svg') }}" alt=""></button>
              <button class="btn btn-light delete-btn rounded-circle" data-id="${deposit.id}"><img src="{{ asset('dr-assets/icons/trash.svg') }}" alt=""></button>
            </td>
                    </tr>
                `;
      if (existingRow) {
        existingRow.outerHTML = rowHtml;
      } else {
        tbody.insertAdjacentHTML('beforeend', rowHtml);
      }
      bindButtonEvents();
    }

    function bindButtonEvents() {
      document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.removeEventListener('click', editHandler);
        btn.addEventListener('click', editHandler);
      });
      document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.removeEventListener('click', deleteHandler);
        btn.addEventListener('click', deleteHandler);
      });
    }

    function editHandler() {
      const id = this.dataset.id;
      const row = document.querySelector(`tr[data-id="${id}"]`);
      const amount = row.cells[0].textContent.trim() === 'بدون بیعانه' ? '' :
        row.cells[0].textContent.replace(/,/g, '');
      modal.style.display = 'flex';
      modalTitle.textContent = 'ویرایش بیعانه';
      document.getElementById('depositId').value = id;
      depositSelect.value = amount ? (['50000', '100000', '150000'].includes(amount) ?
        amount : 'custom') : '';
      customPriceInput.value = amount && !['50000', '100000', '150000'].includes(amount) ? amount : '';
      noDepositCheckbox.checked = !amount;
      customPriceContainer.style.display = depositSelect.value === 'custom' ? 'block' : 'none';
      isCustomPrice.value = depositSelect.value === 'custom' ? '1' : '0';
    }

   // داخل تابع bindButtonEvents
function deleteHandler() {
    const id = this.dataset.id;
    const url = `{{ route('doctors.clinic.deposit.destroy', '') }}/${id}`;

    Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: 'بیعانه انتخاب‌شده حذف خواهد شد و این عملیات قابل بازگشت نیست!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'بله، حذف کن',
        cancelButtonText: 'خیر'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: new FormData(form)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire(
                        'حذف شد!',
                        data.message,
                        'success'
                    );
                    document.querySelector(`tr[data-id="${id}"]`).remove();
                } else {
                    Swal.fire(
                        'خطا!',
                        data.message || 'خطا در حذف',
                        'error'
                    );
                }
            })
            .catch(() => {
                Swal.fire(
                    'خطا!',
                    'خطا در ارتباط با سرور',
                    'error'
                );
            });
        }
    });
}

// اطمینان از اینکه رویدادها به دکمه‌ها متصل شوند
function bindButtonEvents() {
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.removeEventListener('click', editHandler);
        btn.addEventListener('click', editHandler);
    });
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.removeEventListener('click', deleteHandler);
        btn.addEventListener('click', deleteHandler);
    });
}

    function resetForm() {
      form.reset();
      depositSelect.disabled = false;
      customPriceInput.disabled = false;
      customPriceContainer.style.display = 'none';
      isCustomPrice.value = '0';
      document.getElementById('depositId').value = '';
    }
  });
</script>
@endsection

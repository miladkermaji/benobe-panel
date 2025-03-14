<div>
 <input type="hidden" id="reqDoctorValue" value="{{ $reqDoctor ?? '0' }}">
 <div class="container-fluid py-1">
  <header class="glass-header p-3 rounded-3 mb-2 shadow-lg">
   <div class="d-flex align-items-center justify-content-between gap-3">
    <div class="d-flex align-items-center gap-2">
     <i class="fas fa-calendar-check fs-4 text-white animate-bounce"></i>
     <h4 class="mb-0 fw-bold text-white">گزارش رزرو و نوبت دهی پزشکان</h4>
    </div>
    <div class="text-white fw-medium fs-6">جستجو و مدیریت نوبت‌ها</div>
   </div>
  </header>
  <div class="panel panel-default shadow-sm mb-4">
   <div class="panel-heading py-2">جستجو و گزارش‌گیری</div>
   <div class="panel-body p-3">
    <div class="row g-3">
     <div class="col-md-3 col-sm-6">
      <label class="control-label fw-bold mb-1 fs-6">انتخاب پزشک</label>
      <select wire:model.live="reqDoctor" class="form-control input-shiny tom-select" data-placeholder="انتخاب پزشک...">
       <option value="0">-- تمام پزشکان --</option>
       @foreach ($doctors as $doctor)
        <option value="{{ $doctor->id }}">{{ $doctor->full_name }}</option>
       @endforeach
      </select>
     </div>
     <div class="col-md-3 col-sm-6">
      <label class="control-label fw-bold mb-1 fs-6">موبایل</label>
      <input type="text" class="form-control input-shiny" wire:model.live="mobile" placeholder="شماره موبایل">
     </div>
     <div class="col-md-3 col-sm-6">
      <label class="control-label fw-bold mb-1 fs-6">کد پیگیری</label>
      <input type="text" class="form-control input-shiny" wire:model.live="trackingCode" placeholder="کد پیگیری">
     </div>
     <div class="col-md-3 col-sm-6">
      <label class="control-label fw-bold mb-1 fs-6">از تاریخ</label>
      <input type="text" id="startDate" class="form-control input-shiny custom-datepicker"
       wire:model.live="startDate" placeholder="انتخاب تاریخ ..." readonly>
     </div>
     <div class="col-md-3 col-sm-6">
      <label class="control-label fw-bold mb-1 fs-6">تا تاریخ</label>
      <input type="text" id="endDate" class="form-control input-shiny custom-datepicker" wire:model.live="endDate"
       placeholder="انتخاب تاریخ ..." readonly>
     </div>
     <div class="col-md-1 col-sm-1 d-flex align-items-end gap-2">
      <button wire:click="resetFilters" class="btn btn-danger w-100 py-2">ریست</button>
     </div>
    </div>
   </div>
  </div>
  <div class="bg-light p-3 rounded-3 shadow-sm mb-4">
   <div class="row g-3 align-items-center">
    <div class="col-md-6 col-sm-12">
     <div class="input-group">
      <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
      <input type="text" class="form-control border-0 shadow-none" wire:model.live="search"
       placeholder="جستجو در نوبت‌ها...">
     </div>
    </div>
    <div class="col-md-6 col-sm-12 d-flex justify-content-end gap-2">
     <button wire:click="export" class="btn btn-outline-primary rounded-pill px-3">
      <i class="fas fa-download"></i> خروجی CSV
     </button>
     <button wire:click="deleteSelected" class="btn btn-outline-danger rounded-pill px-3"
      @if (empty($selectedAppointments)) disabled @endif>
      <i class="fas fa-trash"></i> حذف انتخاب‌شده‌ها
     </button>
    </div>
   </div>
  </div>
  <div class="card shadow-sm">
   <div class="card-body p-3">
    <div class="table-responsive text-nowrap">
     <table class="table table-bordered table-striped table-sm">
      <thead>
       <tr>
        <th style="width: 2%;"><input type="checkbox" wire:model.live="selectAll" class="form-check-input"></th>
        <th style="width: 3%;">ردیف</th>
        <th style="width: 10%;">پزشک</th>
        <th style="width: 8%;">شماره تماس</th>
        <th style="width: 10%;">استان / شهر</th>
        <th style="width: 8%;">تاریخ ملاقات</th>
        <th style="width: 6%;">زمان</th>
        <th style="width: 12%;">نام کاربر</th>
        <th style="width: 8%;">کدملی</th>
        <th style="width: 10%;">تاریخ رزرو</th>
        <th style="width: 8%;">کد پیگیری</th>
        <th style="width: 8%;">وضعیت</th>
        <th style="width: 5%;">فنی</th>
        <th style="width: 5%;">عملیات</th>
       </tr>
      </thead>
      <tbody>
       @forelse ($appointments as $index => $appointment)
        <tr>
         <td><input type="checkbox" wire:model.live="selectedAppointments" value="{{ $appointment->id }}"
           class="form-check-input"></td>
         <td>{{ $appointments->firstItem() + $index }}</td>
         <td>{{ $appointment->doctor->full_name }}</td>
         <td>{{ $appointment->doctor->mobile }}</td>
         <td>{{ $appointment->doctor->province->name ?? '' }} / {{ $appointment->doctor->city->name ?? '' }}</td>
         <td>{{ \App\Helpers\JalaliHelper::toJalaliDate($appointment->appointment_date) }}</td>
         <td>{{ $appointment->start_time }}</td>
         <td>{{ $appointment->patient->first_name ?? '' }} {{ $appointment->patient->last_name ?? '' }}
          ({{ $appointment->patient->mobile ?? '' }})
         </td>
         <td>{{ $appointment->patient->national_code ?? '' }}</td>
         <td>{{ \App\Helpers\JalaliHelper::toJalaliDateTime($appointment->reserved_at) }}</td>
         <td>{{ $appointment->tracking_code }}</td>
         <td>
          <span
           class="badge 
                {{ $appointment->status === 'scheduled'
                    ? 'bg-label-info'
                    : ($appointment->status === 'cancelled'
                        ? 'bg-label-danger'
                        : ($appointment->status === 'attended'
                            ? 'bg-label-success'
                            : 'bg-label-warning')) }}">
           {{ $appointment->status === 'scheduled'
               ? 'در انتظار خدمت'
               : ($appointment->status === 'cancelled'
                   ? 'لغو شده'
                   : ($appointment->status === 'attended'
                       ? 'حضور یافته'
                       : 'غایب')) }}
          </span>
          @if ($appointment->notification_sent)
           <span style="display:block; text-align:center; color:dodgerblue" title="پیامک تأیید نوبت ارسال شده است">
            <i class="fa fa-file-text"></i>
           </span>
          @endif
         </td>
         <td class="text-center">
          <button type="button" class="btn btn-sm btn-gradient-secondary" data-bs-toggle="popover"
           data-bs-content="Site/App: {{ $appointment->appointment_type }}<br>methodpay: {{ $appointment->payment_status }}<br>bank_refid: {{ $appointment->fee ? 'N/A' : '0' }}<br>system: onlinepayment<br>type: {{ $appointment->appointment_type }}">
           <i class="fa fa-info-circle"></i>
          </button>
         </td>
         <td class="text-center">
          <div class="dropdown">
           <button class="btn btn-gradient-secondary btn-sm" type="button" data-bs-toggle="dropdown">
            <i class="fas fa-ellipsis-v"></i>
           </button>
           <ul class="dropdown-menu dropdown-menu-end">
            @php
             $userName = trim(
                 ($appointment->patient->first_name ?? '') . ' ' . ($appointment->patient->last_name ?? ''),
             );
             $userName = $userName !== '' ? $userName : 'کاربر بدون نام';
             $isBlocked = \App\Models\UserBlocking::where('user_id', $appointment->patient_id)
                 ->where('doctor_id', $appointment->doctor_id)
                 ->where('status', 1)
                 ->exists();
            @endphp
            <li>
             <a class="dropdown-item" href="#"
              wire:click.prevent="toggleBlockUser({{ $appointment->patient_id }}, {{ $appointment->doctor_id }}, '{{ addslashes($userName) }}', {{ $isBlocked ? 0 : 1 }})">
              {{ $isBlocked ? 'خروج از مسدودی' : 'مسدود کردن' }}
             </a>
            </li>
            <li><a class="dropdown-item" href="#"
              wire:click.prevent="cancelAppointment({{ $appointment->id }})">لغو
              نوبت</a></li>
            <li><a class="dropdown-item text-danger" href="#"
              wire:click.prevent="deleteAppointment({{ $appointment->id }})">حذف</a></li>
           </ul>
          </div>
         </td>
        </tr>
       @empty
        <tr>
         <td colspan="14" class="text-center py-5">
          <i class="fas fa-calendar-times fs-1 text-muted mb-3"></i>
          <p class="text-muted fw-medium">هیچ نوبت‌ی یافت نشد.</p>
         </td>
        </tr>
       @endforelse
      </tbody>
     </table>
    </div>
    <div class="d-flex justify-content-between mt-3">
     <div class="text-muted fs-6">نمایش {{ $appointments->firstItem() }} تا {{ $appointments->lastItem() }} از
      {{ $appointments->total() }} ردیف</div>
     {{ $appointments->links() }}
    </div>
   </div>
  </div>
  <style>
   .glass-header {
    background: linear-gradient(135deg, rgba(79, 70, 229, 0.9), rgba(124, 58, 237, 0.7));
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
   }

   .glass-header:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
   }

   .panel-default {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
   }

   .panel-heading {
    background: linear-gradient(135deg, #f9fafb, #e5e7eb);
    padding: 10px;
    font-weight: bold;
    border-bottom: 1px solid #e5e7eb;
    border-radius: 8px 8px 0 0;
    color: #4b5563;
   }

   .panel-body {
    padding: 15px;
   }

   .input-shiny {
    border: 1px solid #d1d5db;
    border-radius: 6px;
    padding: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: #fff;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
   }

   .input-shiny:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
   }

   .btn-gradient-secondary {
    background: linear-gradient(90deg, #6b7280, #9ca3af);
    border: none;
    color: white;
   }

   .btn-gradient-secondary:hover {
    background: linear-gradient(90deg, #4b5563, #6b7280);
    transform: translateY(-1px);
   }

   .btn-outline-secondary {
    border: 1px solid #6b7280;
    color: #6b7280;
    background: transparent;
   }

   .btn-outline-secondary:hover {
    background: #6b7280;
    color: white;
    transform: translateY(-1px);
   }

   .btn-outline-primary {
    border: 1px solid #4f46e5;
    color: #4f46e5;
    background: transparent;
   }

   .btn-outline-primary:hover {
    background: #4f46e5;
    color: white;
    transform: translateY(-1px);
   }

   .btn-outline-danger {
    border: 1px solid #ef4444;
    color: #ef4444;
    background: transparent;
   }

   .btn-outline-danger:hover:not(:disabled) {
    background: #ef4444;
    color: white;
    transform: translateY(-1px);
   }

   .btn-outline-danger:disabled {
    border-color: #d1d5db;
    color: #d1d5db;
    cursor: not-allowed;
   }

   .bg-light {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
   }

   .table-sm td,
   .table-sm th {
    padding: 0.5rem;
    font-size: 0.875rem;
   }

   .dropdown-menu {
    min-width: 120px;
   }
  </style>
  <script>
   document.addEventListener('DOMContentLoaded', () => {
    function initializeTomSelect() {
     document.querySelectorAll('.tom-select:not(.tomselected)').forEach(select => {
      const tom = new TomSelect(select, {
       create: false,
       maxOptions: null,
       direction: 'rtl',
       search: true,
       placeholder: select.dataset.placeholder,
       render: {
        option: function(data, escape) {
         return `<div>${escape(data.text)}</div>`;
        },
        item: function(data, escape) {
         return `<div>${escape(data.text)}</div>`;
        }
       },
       onChange: function(value) {
        select.dispatchEvent(new Event('change'));
       },
       onInitialize: function() {
        const initialValue = document.getElementById('reqDoctorValue').value;
        this.setValue(initialValue, true);
       }
      });
     });
    }
    initializeTomSelect();
    $('[data-bs-toggle="popover"]').popover();

    document.addEventListener('livewire:init', () => {
     Livewire.on('toast', (event) => {
      const data = event[0];
      const {
       message,
       type
      } = data;
      if (typeof toastr === 'undefined') {
       console.error('Toastr is not loaded!');
       return;
      }
      const toastOptions = {
       positionClass: 'toast-top-right',
       timeOut: 3000,
       progressBar: false,
      };
      if (type === 'success') toastr.success(message, '', toastOptions);
      else if (type === 'error') toastr.error(message, '', toastOptions);
      else if (type === 'warning') toastr.warning(message, '', toastOptions);
      else toastr.info(message, '', toastOptions);
     });

     Livewire.on('show-ban-form', (event) => {
      const data = event[0];
      const {
       userId,
       doctorId,
       userName,
       status
      } = data;
      const isBlocking = status === 1;
      Swal.fire({
       title: isBlocking ? 'مسدود کردن کاربر' : 'خروج از مسدودی',
       html: `<p style="text-align: right;">آیا از ${isBlocking ? 'مسدود کردن' : 'خروج از مسدودی'} "${userName}" مطمئن هستید؟</p>` +
        (isBlocking ?
         '<input id="banReason" class="swal2-input" placeholder="دلیل مسدود کردن" style="width: 100%;">' +
         '<label style="display: block; text-align: right;">تاریخ انقضا:</label>' +
         '<input id="banExpiry" class="form-control input-shiny custom-datepicker" placeholder="انتخاب تاریخ ..." style="width: 100%; z-index: 9999;" readonly>' :
         ''),
       showCancelButton: true,
       confirmButtonText: 'ثبت',
       cancelButtonText: 'انصراف',
       didOpen: () => {
        if (isBlocking) {
         const expiryInput = document.getElementById('banExpiry');
         window.initializeDatepicker(expiryInput);
         const calendar = expiryInput.parentNode.querySelector('.calendar');
         if (calendar) {
          calendar.style.zIndex = '10000';
         }
        }
       },
       preConfirm: () => {
        if (isBlocking) {
         const reason = document.getElementById('banReason').value;
         const expiry = document.getElementById('banExpiry').value;
         if (!reason || !expiry) {
          Swal.showValidationMessage('لطفاً دلیل و تاریخ انقضا را وارد کنید');
          return false;
         }
         return {
          status: 1,
          reason: reason,
          expiry: expiry
         };
        }
        return {
         status: 0
        };
       }
      }).then((result) => {
       if (result.isConfirmed) {

        @this.toggleBlockUserConfirm(userId, doctorId, result.value);
       }
      });
     });

     Livewire.on('confirm-action', (event) => {
      const data = event[0];
      const {
       action,
       id
      } = data;
      Swal.fire({
       title: 'آیا مطمئن هستید؟',
       text: action === 'cancel' ? 'این نوبت لغو خواهد شد!' : 'این نوبت حذف خواهد شد!',
       icon: 'warning',
       showCancelButton: true,
       confirmButtonColor: '#ef4444',
       cancelButtonText: 'خیر',
       confirmButtonText: 'بله'
      }).then((result) => {
       if (result.isConfirmed) {
        if (action === 'cancel') {
         @this.confirmCancel(id);
        } else {
         @this.confirmDelete(id);
        }
       }
      });
     });

     Livewire.on('confirm-delete-selected', (event) => {
      Swal.fire({
       title: 'آیا مطمئن هستید؟',
       text: 'نوبت‌های انتخاب‌شده حذف خواهند شد!',
       icon: 'warning',
       showCancelButton: true,
       confirmButtonColor: '#ef4444',
       cancelButtonText: 'خیر',
       confirmButtonText: 'بله'
      }).then((result) => {
       if (result.isConfirmed) {
        @this.confirmDeleteSelected();
       }
      });
     });
    });

    Livewire.hook('morph.updated', () => {
     document.querySelectorAll('select.tom-select').forEach(select => {
      const tom = select.tomselect;
      const currentValue = document.getElementById('reqDoctorValue').value;
      if (!tom || !document.querySelector('.ts-wrapper')) {
       const newTom = new TomSelect(select, {
        create: false,
        maxOptions: null,
        direction: 'rtl',
        search: true,
        placeholder: select.dataset.placeholder,
        render: {
         option: function(data, escape) {
          return `<div>${escape(data.text)}</div>`;
         },
         item: function(data, escape) {
          return `<div>${escape(data.text)}</div>`;
         }
        },
        onChange: function(value) {
         select.dispatchEvent(new Event('change'));
        }
       });
       newTom.setValue(currentValue, true);
      } else {
       tom.setValue(currentValue, true);
      }
     });

     document.querySelectorAll('.custom-datepicker').forEach(input => {
      if (!input.dataset.datepickerInitialized || !input.parentNode.querySelector('.datepicker-container')) {
       window.initializeDatepicker(input);
      } else {
       input.removeEventListener('click', input.clickHandler);
       input.clickHandler = function(e) {
        e.stopPropagation();
        const calendar = input.parentNode.querySelector('.calendar');
        calendar.classList.toggle('active');
        if (calendar.classList.contains('active')) {
         const daysContainer = calendar.querySelector('.days');
         if (!daysContainer.innerHTML) {
          window.initializeDatepicker(input);
         }
        }
       };
       input.addEventListener('click', input.clickHandler);
      }
     });
    });
   });
  </script>
 </div>
</div>

<div class="container-fluid py-4" dir="rtl">
 <div class="card shadow-lg border-0 rounded-3 overflow-hidden" style="background: #ffffff;">
  <div
   class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
   <div class="d-flex align-items-center gap-3">
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
     class="animate-bounce">
     <path d="M5 12h14M12 5l7 7-7 7" />
    </svg>
    <h5 class="mb-0 fw-bold text-shadow">افزودن خدمت پزشک جدید</h5>
   </div>
   <a href="{{ route('admin.panel.doctorservices.index') }}"
    class="btn btn-outline-light btn-sm rounded-pill px-4 d-flex align-items-center gap-2 hover:shadow-lg transition-all">
    <svg style="transform: rotate(180deg)" width="16" height="16" viewBox="0 0 24 24" fill="none"
     stroke="currentColor" stroke-width="2">
     <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
    </svg>
    بازگشت
   </a>
  </div>

  <div class="card-body p-4">
   <div class="row justify-content-center">
    <div class="col-12 col-md-10 col-lg-8">
     <div class="row g-4">
      <!-- پزشک -->
      <!-- پزشک -->
      <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
       <select wire:model.live="doctor_id" class="form-select select2" id="doctor_id" required>
        <option value="">انتخاب کنید</option>
        @foreach ($doctors as $doctor)
         <option value="{{ $doctor->id }}">{{ $doctor->first_name . ' ' . $doctor->last_name }}</option>
        @endforeach
       </select>
       <label for="doctor_id" class="form-label">پزشک</label>
       @error('doctor_id')
        <span class="text-danger small">{{ $message }}</span>
       @enderror
      </div>

      <!-- کلینیک -->
      <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
       <select wire:model.live="clinic_id" class="form-select select2" id="clinic_id">
        <option value="">انتخاب کنید</option>
        @foreach ($clinics as $clinic)
         <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
        @endforeach
       </select>
       <label for="clinic_id" class="form-label">کلینیک (اختیاری)</label>
       @error('clinic_id')
        <span class="text-danger small">{{ $message }}</span>
       @enderror
      </div>

      <!-- نام -->
      <div class="col-6 col-md-6 position-relative mt-5">
       <input type="text" wire:model.live="name" class="form-control" id="name"
        placeholder="نام خدمت را وارد کنید" required>
       <label for="name" class="form-label">نام خدمت</label>
       @error('name')
        <span class="text-danger small">{{ $message }}</span>
       @enderror
      </div>

      <!-- مدت زمان -->
      <div class="col-6 col-md-6 position-relative mt-5">
       <input type="number" wire:model.live="duration" class="form-control" id="duration" placeholder="مدت زمان خدمت">
       <label for="duration" class="form-label">مدت زمان (دقیقه،)</label>
       @error('duration')
        <span class="text-danger small">{{ $message }}</span>
       @enderror
      </div>

      <!-- قیمت -->
      <div class="col-6 col-md-6 position-relative mt-5">
       <input type="number" wire:model.live="price" class="form-control" id="price" placeholder="قیمت خدمت"
        step="0.01">
       <label for="price" class="form-label">قیمت</label>
       @error('price')
        <span class="text-danger small">{{ $message }}</span>
       @enderror
      </div>

      <!-- تخفیف -->
      <div class="col-6 col-md-6 position-relative mt-5">
       <input type="number" wire:model.live="discount" class="form-control" id="discount" placeholder="درصد تخفیف"
        step="0.01">
       <label for="discount" class="form-label">تخفیف (درصد، اختیاری)</label>
       @error('discount')
        <span class="text-danger small">{{ $message }}</span>
       @enderror
      </div>

      <!-- توضیحات -->
      <div class="col-12 position-relative mt-5">
       <textarea wire:model.live="description" class="form-control" id="description" rows="3" placeholder="توضیحات خدمت"></textarea>
       <label for="description" class="form-label">توضیحات (اختیاری)</label>
       @error('description')
        <span class="text-danger small">{{ $message }}</span>
       @enderror
      </div>

      <!-- وضعیت -->
      <div class="col-6 col-md-6 position-relative mt-5 d-flex align-items-center">
       <div class="form-check form-switch w-100 d-flex align-items-center">
        <input class="form-check-input" type="checkbox" id="status" wire:model.live="status">
        <label class="form-check-label fw-medium" for="status">
         وضعیت: <span
          class="px-2 text-{{ $status ? 'success' : 'danger' }}">{{ $status ? 'فعال' : 'غیرفعال' }}</span>
        </label>
       </div>
       @error('status')
        <span class="text-danger small">{{ $message }}</span>
       @enderror
      </div>

      <!-- خدمت مادر -->
      <div class="col-6 col-md-6 position-relative mt-5" wire:ignore>
       <select wire:model.live="parent_id" class="form-select select2" id="parent_id">
        <option value="">بدون زیرگروه</option>
        @foreach ($parentServices as $service)
         <option value="{{ $service->id }}">{{ $service->name }}</option>
        @endforeach
       </select>
       <label for="parent_id" class="form-label">زیر گروه (اختیاری)</label>
       @error('parent_id')
        <span class="text-danger small">{{ $message }}</span>
       @enderror
      </div>
     </div>

     <!-- دکمه افزودن -->
     <div class="text-end mt-4 w-100 d-flex justify-content-end">
      <button wire:click="store"
       class="btn btn-primary px-5 py-2 d-flex align-items-center gap-2 shadow-lg hover:shadow-xl transition-all">
       <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
        stroke-width="2">
        <path d="M12 5v14M5 12h14" />
       </svg>
       افزودن خدمت پزشک
      </button>
     </div>
    </div>
   </div>
  </div>
 </div>

 <style>
  .bg-gradient-primary {
   background: linear-gradient(90deg, #6b7280, #374151);
  }

  .card {
   border-radius: 12px;
   box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  }

  .form-control,
  .form-select {
   border: 1px solid #e5e7eb;
   border-radius: 8px;
   padding: 12px 15px;
   font-size: 14px;
   transition: all 0.3s ease;
   height: 48px;
   background: #fafafa;
   width: 100%;
  }

  .form-control:focus,
  .form-select:focus {
   border-color: #6b7280;
   box-shadow: 0 0 0 3px rgba(107, 114, 128, 0.2);
   background: #fff;
  }

  .form-label {
   position: absolute;
   top: -25px;
   right: 15px;
   color: #374151;
   font-size: 12px;
   background: #ffffff;
   padding: 0 5px;
   pointer-events: none;
  }

  .btn-primary {
   background: linear-gradient(90deg, #6b7280, #374151);
   border: none;
   color: white;
   font-weight: 600;
  }

  .btn-primary:hover {
   background: linear-gradient(90deg, #4b5563, #1f2937);
   transform: translateY(-2px);
  }

  .btn-outline-light {
   border-color: rgba(255, 255, 255, 0.8);
  }

  .btn-outline-light:hover {
   background: rgba(255, 255, 255, 0.15);
   transform: translateY(-2px);
  }

  .form-check-input {
   margin-top: 0;
   height: 20px;
   width: 20px;
   vertical-align: middle;
  }

  .form-check-label {
   margin-right: 25px;
   line-height: 1.5;
   vertical-align: middle;
  }

  .form-check-input:checked {
   background-color: #6b7280;
   border-color: #6b7280;
  }

  .animate-bounce {
   animation: bounce 1s infinite;
  }

  @keyframes bounce {

   0%,
   100% {
    transform: translateY(0);
   }

   50% {
    transform: translateY(-5px);
   }
  }

  .text-shadow {
   text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
  }

  .select2-container {
   width: 100% !important;
  }

  .select2-container--default .select2-selection--single {
   height: 48px;
   border: 1px solid #e5e7eb;
   border-radius: 8px;
   background: #fafafa;
  }

  .select2-container--default .select2-selection--single .select2-selection__rendered {
   line-height: 46px;
   padding-right: 15px;
   color: #000 !important;
  }

  .select2-container--default .select2-selection--single .select2-selection__arrow {
   height: 46px;
  }

  .select2-dropdown {
   z-index: 1050 !important;
   border: 1px solid #e5e7eb;
   border-radius: 8px;
  }

  .select2-results__option {
   color: #000 !important;
  }
 </style>

 <script>
  document.addEventListener('DOMContentLoaded', function() {
   if (typeof jQuery === 'undefined') {
    console.error('jQuery لود نشده است.');
    return;
   }
   if (typeof jQuery.fn.select2 === 'undefined') {
    console.error('Select2 لود نشده است.');
    return;
   }

   function initializeSelect2() {
    $('#doctor_id').select2({
     dir: 'rtl',
     placeholder: 'انتخاب کنید',
     width: '100%'
    });
    $('#clinic_id').select2({
     dir: 'rtl',
     placeholder: 'انتخاب کنید',
     width: '100%'
    });
    $('#parent_id').select2({
     dir: 'rtl',
     placeholder: 'انتخاب کنید',
     width: '100%'
    });

    $('#doctor_id').on('change', function() {
     @this.set('doctor_id', $(this).val());
    });
    $('#clinic_id').on('change', function() {
     @this.set('clinic_id', $(this).val());
    });
    $('#parent_id').on('change', function() {
     @this.set('parent_id', $(this).val());
    });
   }

   initializeSelect2();

   Livewire.hook('morph.updated', () => {
    $('#doctor_id').select2('destroy');
    $('#clinic_id').select2('destroy');
    $('#parent_id').select2('destroy');
    initializeSelect2();

    const doctorValue = @json($this->doctor_id);
    const clinicValue = @json($this->clinic_id);
    const parentValue = @json($this->parent_id);
    if (doctorValue) $('#doctor_id').val(doctorValue).trigger('change');
    if (clinicValue) $('#clinic_id').val(clinicValue).trigger('change');
    if (parentValue) $('#parent_id').val(parentValue).trigger('change');
   });

   Livewire.on('show-alert', (event) => {
    toastr[event.type](event.message);
   });
  });
 </script>
</div>

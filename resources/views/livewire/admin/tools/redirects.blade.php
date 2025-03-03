<div class="container-fluid py-1">
 <!-- هدر -->
 <div class="bg-white-only lter b-b wrapper-md clrfix mb-5">
  <h1 class="m-n h3 font-thin">مدیریت ریدایرکت‌ها</h1>
 </div>

 <!-- فرم افزودن -->
 <div class="wrapper-md mb-2">
  <div class="panel panel-default shadow-sm">
   <div class="panel-heading">افزودن ریدایرکت جدید</div>
   <div class="panel-body">
    <div class="row g-4">
     <div class="col-md-5">
      <div class="form-group">
       <label class="control-label fw-bold mb-2">آدرس مسیر ریدایرکت</label>
       <input type="url" class="form-control input-shiny" wire:model="newSourceUrl" placeholder="https://" dir="ltr">
       @error('newSourceUrl')
        <span class="text-danger d-block mt-1">{{ $message }}</span>
       @enderror
      </div>
     </div>
     <div class="col-md-5">
      <div class="form-group">
       <label class="control-label fw-bold mb-2">آدرس هدف ریدایرکت</label>
       <input type="url" class="form-control input-shiny" wire:model="newDestinationUrl"
        placeholder="https://" dir="ltr">
       @error('newDestinationUrl')
        <span class="text-danger d-block mt-1">{{ $message }}</span>
       @enderror
      </div>
     </div>
     <div class="col-md-2 d-flex align-items-end">
      <button wire:click="addRedirect" class="btn h-50 btn-gradient-success w-100 py-2">ثبت و
       ذخیره</button>
     </div>
    </div>
   </div>
  </div>
 </div>

 <!-- ابزارها و جستجو -->
 <div class="container px-0 mb-5">
  <div class="bg-light p-4 rounded-3 shadow-sm">
   <div class="row g-4">
    <div class="col-md-6">
     <div class="input-group">
      <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
      <input type="text" class="form-control border-0 shadow-none" wire:model.live="search"
       placeholder="جستجو در ریدایرکت‌ها...">
     </div>
    </div>
    <div class="col-md-6">
     <div class="d-flex gap-2 justify-content-end">
      <button wire:click="export" class="btn btn-gradient-secondary rounded-pill px-4">
       <i class="fas fa-download"></i> خروجی CSV
      </button>
      <button wire:click="deleteSelected" class="btn btn-gradient-danger rounded-pill px-4"
       @if (empty($selectedRedirects)) disabled @endif>
       <i class="fas fa-trash"></i> حذف انتخاب‌شده‌ها
      </button>
     </div>
    </div>
   </div>
  </div>
 </div>

 <!-- لیست ریدایرکت‌ها -->
 <div class="container px-0">
  <div class="card shadow-sm">
   <div class="card-body">
    <div class="table-responsive text-nowrap">
     <table class="table table-bordered">
      <thead>
       <tr>
        <th><input type="checkbox" wire:model.live="selectAll" class="form-check-input"></th>
        <th>ردیف</th>
        <th>آدرس مبدا</th>
        <th>آدرس مقصد</th>
        <th>وضعیت</th>
        <th>عملیات</th>
       </tr>
      </thead>
      <tbody>
       @forelse ($redirects as $index => $redirect)
        <tr>
         <td><input type="checkbox" wire:model.live="selectedRedirects" value="{{ $redirect->id }}"
           class="form-check-input"></td>
         <td>{{ $redirects->firstItem() + $index }}</td>
         <td>
          @if ($editId === $redirect->id)
           <input type="url" class="form-control input-shiny" wire:model.live="editSourceUrl">
           @if ($errors->has('newSourceUrl'))
            <span class="text-danger d-block mt-1">{{ $errors->first('newSourceUrl') }}</span>
           @endif
          @else
           {{ $redirect->source_url }}
          @endif
         </td>
         <td>
          @if ($editId === $redirect->id)
           <input type="url" class="form-control input-shiny" wire:model.live="editDestinationUrl">
           @if ($errors->has('newSourceUrl'))
            <span class="text-danger d-block mt-1">{{ $errors->first('newSourceUrl') }}</span>
           @endif
          @else
           {{ $redirect->destination_url }}
          @endif
         </td>
         <td>
          <button wire:click="toggleStatus({{ $redirect->id }})"
           class="badge {{ $redirect->is_active ? 'bg-label-success' : 'bg-label-danger' }} border-0 cursor-pointer">
           {{ $redirect->is_active ? 'فعال' : 'غیرفعال' }}
          </button>
         </td>
         <td>
          @if ($editId === $redirect->id)
           <div class="d-flex gap-2">
            <button wire:click="updateRedirect" class="btn btn-gradient-success rounded-pill px-3">
             <i class="fas fa-check"></i>
            </button>
            <button wire:click="cancelEdit" class="btn btn-gradient-danger rounded-pill px-3">
             <i class="fas fa-times"></i>
            </button>
           </div>
          @else
           <div class="dropdown">
            <button class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" type="button">
             <i class="ti ti-dots-vertical"></i>
            </button>
            <div class="dropdown-menu">
             <a class="dropdown-item" wire:click="startEdit({{ $redirect->id }})" href="javascript:void(0);">
              <i class="ti ti-edit me-1"></i> ویرایش
             </a>
             <a class="dropdown-item" onclick="confirmDelete({{ $redirect->id }})" href="javascript:void(0);">
              <i class="ti ti-trash me-1"></i> حذف
             </a>
            </div>
           </div>
          @endif
         </td>
        </tr>
       @empty
        <tr>
         <td colspan="6" class="text-center py-5">
          <i class="fas fa-link fs-1 text-muted mb-3"></i>
          <p class="text-muted fw-medium">هیچ ریدایرکتی یافت نشد.</p>
         </td>
        </tr>
       @endforelse
      </tbody>
     </table>
    </div>
    <div class="d-flex justify-content-between mt-4">
     <div class="text-muted">نمایش {{ $redirects->firstItem() }} تا {{ $redirects->lastItem() }} از
      {{ $redirects->total() }} ردیف</div>
     {{ $redirects->links() }}
    </div>
   </div>
  </div>
 </div>

 <style>
  .panel-default {
   border: 1px solid #e5e7eb;
   border-radius: 8px;
  }

  .panel-heading {
   background: linear-gradient(135deg, #f9fafb, #e5e7eb);
   padding: 15px;
   font-weight: bold;
   border-bottom: 1px solid #e5e7eb;
   border-radius: 8px 8px 0 0;
   color: #4b5563;
  }

  .panel-body {
   padding: 20px;
  }

  .input-shiny {
   border: 1px solid #d1d5db;
   border-radius: 8px;
   padding: 10px;
   font-size: 14px;
   transition: all 0.3s ease;
   background: #fff;
   box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
  }

  .input-shiny:focus {
   border-color: #4f46e5;
   box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.25), inset 0 1px 3px rgba(0, 0, 0, 0.05);
  }

  .form-control {
   border-radius: 8px;
   border: 1px solid #e5e7eb;
   transition: all 0.3s ease;
  }

  .form-control:focus {
   border-color: #4f46e5;
   box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.25);
  }

  .btn-gradient-success {
   background: linear-gradient(90deg, #10b981, #34d399);
   border: none;
   color: white;
   box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
   transition: all 0.3s ease;
  }

  .btn-gradient-success:hover {
   background: linear-gradient(90deg, #059669, #10b981);
   transform: translateY(-2px);
   box-shadow: 0 5px 12px rgba(0, 0, 0, 0.15);
  }

  .bg-light {
   background: #f9fafb;
   border: 1px solid #e5e7eb;
  }

  .btn-gradient-danger {
   background: linear-gradient(90deg, #f87171, #fca5a5);
   border: none;
   color: white;
   box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
   transition: all 0.3s ease;
  }

  .btn-gradient-danger:hover:not(:disabled) {
   background: linear-gradient(90deg, #ef4444, #f87171);
   transform: translateY(-2px);
   box-shadow: 0 5px 12px rgba(0, 0, 0, 0.15);
  }

  .btn-gradient-danger:disabled {
   background: #d1d5db;
   cursor: not-allowed;
  }

  .btn-gradient-secondary {
   background: linear-gradient(90deg, #6b7280, #9ca3af);
   border: none;
   color: white;
   box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
   transition: all 0.3s ease;
  }

  .btn-gradient-secondary:hover {
   background: linear-gradient(90deg, #4b5563, #6b7280);
   transform: translateY(-2px);
   box-shadow: 0 5px 12px rgba(0, 0, 0, 0.15);
  }

  .table-bordered {
   border: 1px solid #e5e7eb;
  }

  .table-bordered th,
  .table-bordered td {
   border: 1px solid #e5e7eb;
  }

  .rounded-pill {
   border-radius: 50rem;
  }

  .w-8 {
   width: 2rem;
  }

  .h-8 {
   height: 2rem;
  }

  .cursor-pointer {
   cursor: pointer;
  }
 </style>

 <script>
  document.addEventListener('livewire:init', () => {
   Livewire.on('toast', (message, options = {}) => {
    if (typeof toastr === 'undefined') {
     console.error('Toastr is not loaded!');
     return;
    }
    const type = options.type || 'info';
    const toastOptions = {
     positionClass: options.position || 'toast-top-right',
     timeOut: options.timeOut || 3000,
     progressBar: options.progressBar || false,
    };
    if (type === 'success') toastr.success(message, '', toastOptions);
    else if (type === 'error') toastr.error(message, '', toastOptions);
    else if (type === 'warning') toastr.warning(message, '', toastOptions);
    else toastr.info(message, '', toastOptions);
   });

   Livewire.on('confirmDeleteSelected', () => {
    Swal.fire({
     title: 'آیا مطمئن هستید؟',
     text: 'ریدایرکت‌های انتخاب‌شده حذف خواهند شد و قابل بازگشت نیستند!',
     icon: 'warning',
     showCancelButton: true,
     confirmButtonColor: '#ef4444',
     cancelButtonColor: '#d1d5db',
     confirmButtonText: 'بله، حذف کن',
     cancelButtonText: 'خیر',
    }).then((result) => {
     if (result.isConfirmed) {
      @this.confirmDeleteSelected();
     }
    });
   });
  });

  function confirmDelete(id) {
   Swal.fire({
    title: 'آیا مطمئن هستید؟',
    text: 'این ریدایرکت حذف خواهد شد و قابل بازگشت نیست!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#d1d5db',
    confirmButtonText: 'بله، حذف کن',
    cancelButtonText: 'خیر',
   }).then((result) => {
    if (result.isConfirmed) {
     @this.deleteRedirect(id);
    }
   });
  }
 </script>
</div>

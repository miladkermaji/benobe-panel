<div class="app-content-body">
 <div class="bg-white-only lter b-b wrapper-md clrfix">
  <h1 class="m-n h3 font-thin">ویرایش تگ</h1>
 </div>
 <div class="wrapper-md">
  <form wire:submit.prevent="update" class="row tagform">
   <div class="form-group mt-3">
    <label class="control-label">نام برچسب:</label>
    <input type="text" name="name" class="form-control" wire:model="name">
    @error('name')
     <span class="text-danger d-block mt-1">{{ $message }}</span>
    @enderror
   </div>
   <div class="form-group mt-3">
    <label class="control-label">تعداد استفاده:</label>
    <input type="number" name="usage_count" class="form-control" wire:model="usage_count">
    @error('usage_count')
     <span class="text-danger d-block mt-1">{{ $message }}</span>
    @enderror
   </div>
   <div class="form-group mt-3">
    <label class="control-label">وضعیت:</label>
    <div class="form-check form-switch">
     <input type="checkbox" class="form-check-input" wire:model="status" @checked($status)>
     <label class="form-check-label">{{ $status ? 'فعال' : 'غیرفعال' }}</label>
    </div>
    @error('status')
     <span class="text-danger d-block mt-1">{{ $message }}</span>
    @enderror
   </div>
   <div class="col-md-12">
    <input type="submit" class="btn btn-success mt-3" value="ثبت و ذخیره">
    <a href="{{ route('admin.content-management.tags.index') }}" class="btn btn-outline-warning mt-3">
     <i class="fas fa-arrow-right me-2"></i> بازگشت
    </a>
   </div>
  </form>
 </div>
 <style>
  .bg-white-only {
   background-color: #fff !important;
  }

  .form-control:focus {
   border-color: #007bff;
   box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
  }

  .btn {
   border-radius: 0.375rem;
   padding: 0.75rem 1.5rem;
  }

  .btn-success {
   background-color: #28a745;
   border-color: #28a745;
  }

  .btn-success:hover {
   background-color: #218838;
   border-color: #1e7e34;
  }

  .btn-outline-warning {
   color: #ffc107;
   border-color: #ffc107;
  }

  .btn-outline-warning:hover {
   background-color: #ffc107;
   color: #fff;
  }
 </style>
 <script>
  document.addEventListener('livewire:initialized', () => {
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

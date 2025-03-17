<div class="container-fluid py-4" dir="rtl">
 <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
  <div class="card-header glass-header text-white p-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
   <div class="d-flex align-items-center gap-3">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
     class="animate-bounce">
     <path d="M5 12h14M12 5l7 7-7 7" />
    </svg>
    <h5 class="mb-0 fw-bold">ویرایش منو</h5>
   </div>
   <a href="{{ route('admin.panel.menus.index') }}"
    class="btn btn-outline-light btn-sm rounded-pill d-flex align-items-center gap-2 hover:shadow-md transition-all">
    <svg width="16" style="transform: rotate(180deg)" height="16" viewBox="0 0 24 24" fill="none"
     stroke="currentColor" stroke-width="2">
     <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
    </svg>
    بازگشت
   </a>
  </div>

  <div class="card-body p-4">
   <!-- پیام موفقیت -->
   @if ($successMessage)
    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
     {{ $successMessage }}
     <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
   @endif

   <form wire:submit.prevent="update">
    <div class="row g-3">
     <!-- نام منو -->
     <div class="col-md-6 col-12">
      <label class="fw-bold mb-1">نام منو: <span class="text-danger">*</span></label>
      <input type="text" class="form-control input-shiny" wire:model="name" placeholder="نام منو را وارد کنید">
      @error('name')
       <span class="text-danger small">{{ $message }}</span>
      @enderror
     </div>

     <!-- لینک منو -->
     <div class="col-md-6 col-12">
      <label class="fw-bold mb-1">لینک منو:</label>
      <input type="text" class="form-control input-shiny" wire:model="url" placeholder="لینک منو را وارد کنید">
      @error('url')
       <span class="text-danger small">{{ $message }}</span>
      @enderror
     </div>

     <!-- آیکون -->
     <div class="col-md-6 col-12">
      <label class="fw-bold mb-1">آیکون:</label>
      <input type="file" class="form-control input-shiny" wire:model="icon" accept="image/*">
      @if ($menu->icon)
       <div class="mt-2">
        <img src="{{ asset('storage/' . $menu->icon) }}" alt="آیکون فعلی" style="max-width: 100px;">
        <small class="text-muted">آیکون فعلی</small>
       </div>
      @endif
      @error('icon')
       <span class="text-danger small">{{ $message }}</span>
      @enderror
     </div>

     <!-- جایگاه -->
     <div class="col-md-6 col-12">
      <label class="fw-bold mb-1">جایگاه: <span class="text-danger">*</span></label>
      <select class="form-control input-shiny" wire:model="position">
       <option value="top">بالا</option>
       <option value="bottom">پایین</option>
       <option value="top_bottom">بالا و پایین</option>
      </select>
      @error('position')
       <span class="text-danger small">{{ $message }}</span>
      @enderror
     </div>

     <!-- زیرمجموعه -->
     <div class="col-md-6 col-12">
      <label class="fw-bold mb-1">زیرمجموعه:</label>
      <select class="form-control input-shiny" wire:model="parent_id">
       <option value="">[دسته اصلی]</option>
       @foreach ($menus as $menuItem)
        <option value="{{ $menuItem->id }}">{{ $menuItem->name }}</option>
       @endforeach
      </select>
      @error('parent_id')
       <span class="text-danger small">{{ $message }}</span>
      @enderror
     </div>

     <!-- ترتیب -->
     <div class="col-md-6 col-12">
      <label class="fw-bold mb-1">ترتیب:</label>
      <input type="number" class="form-control input-shiny" wire:model="order" placeholder="ترتیب را وارد کنید">
      @error('order')
       <span class="text-danger small">{{ $message }}</span>
      @enderror
     </div>

     <!-- وضعیت -->
     <div class="col-md-6 col-12">
      <label class="fw-bold mb-1">وضعیت: <span class="text-danger">*</span></label>
      <select class="form-control input-shiny" wire:model="status">
       <option value="1">فعال</option>
       <option value="0">غیرفعال</option>
      </select>
      @error('status')
       <span class="text-danger small">{{ $message }}</span>
      @enderror
     </div>
    </div>

    <!-- دکمه‌ها -->
    <div class="col-md-12 text-end mt-4">
     <button type="submit" class="btn btn-gradient-primary px-4">
      به‌روزرسانی
     </button>
    </div>
   </form>
  </div>
 </div>

 <script>
  document.addEventListener('livewire:init', function() {
   Livewire.on('menuUpdated', () => {
    toastr.success('منو با موفقیت به‌روزرسانی شد!');
    setTimeout(() => {
     window.location.href = "{{ route('admin.panel.menus.index') }}";
    }, 3000); // هدایت به ایندکس بعد از 3 ثانیه
   });
   Livewire.on('show-alert', (event) => {
    toastr[event.type](event.message);
   });
  });
 </script>

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

  .btn-gradient-primary {
   background: linear-gradient(90deg, #4f46e5, #7c3aed);
   border: none;
   color: white;
   border-radius: 6px;
  }

  .btn-gradient-primary:hover {
   background: linear-gradient(90deg, #4338ca, #6d28d9);
   transform: translateY(-1px);
  }
 </style>
</div>

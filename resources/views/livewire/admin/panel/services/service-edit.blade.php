<div class="container-fluid py-4" dir="rtl">
 <div class="card shadow-lg border-0 rounded-3 overflow-hidden">
  <div
   class="card-header bg-gradient-primary text-white p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
   <div class="d-flex align-items-center gap-3">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
     class="animate-bounce">
     <path d="M5 12h14M12 5l7 7-7 7" />
    </svg>
    <h5 class="mb-0 fw-bold text-shadow">ویرایش خدمت: {{ $name ?? 'نامشخص' }}</h5>
   </div>
   <a href="{{ route('admin.panel.services.index') }}"
    class="btn btn-outline-light btn-sm rounded-pill px-4 d-flex align-items-center gap-2 hover:shadow-lg transition-all">
    <svg width="16" height="16" style="transform: rotate(180deg)" viewBox="0 0 24 24" fill="none"
     stroke="currentColor" stroke-width="2">
     <path d="M10 19l-7-7m0 0l7-7m-7 7h18" />
    </svg>
    بازگشت
   </a>
  </div>

  <div class="card-body p-4">
   <div class="row g-4 justify-content-center">
    <div class="col-md-12 col-lg-12">
     <!-- نام -->
     <div class="rounded-3 p-3 shadow-sm hover:shadow-md transition-all position-relative">
      <label for="name" class="form-label fw-bold text-dark mb-2">نام خدمت</label>
      <input type="text" wire:model="name" class="form-control input-shiny" id="name" placeholder="نام خدمت"
       required>
      @error('name')
       <span class="text-danger small d-block mt-1">{{ $message }}</span>
      @enderror
     </div>

     <!-- توضیحات -->
     <div class="rounded-3 p-3 shadow-sm hover:shadow-md transition-all position-relative mt-4">
      <label for="description" class="form-label fw-bold text-dark mb-2">توضیحات (اختیاری)</label>
      <textarea wire:model="description" class="form-control input-shiny" id="description" rows="3"
       placeholder="توضیحات خدمت"></textarea>
      @error('description')
       <span class="text-danger small d-block mt-1">{{ $message }}</span>
      @enderror
     </div>

     <!-- وضعیت -->
     <div class="rounded-3 p-3 shadow-sm hover:shadow-md transition-all mt-4">
      <div class="d-flex align-items-center gap-3">
       <input class="form-check-input flex-shrink-0" type="checkbox" id="isActive" wire:model="status"
        style="width: 20px; height: 20px;">
       <label class="form-check-label fw-medium flex-grow-1 mb-0 mx-4" for="isActive">
        وضعیت: <span class="text-{{ $status ? 'success' : 'danger' }}">{{ $status ? 'فعال' : 'غیرفعال' }}</span>
       </label>
      </div>
      @error('status')
       <span class="text-danger small d-block mt-1">{{ $message }}</span>
      @enderror
     </div>

     <!-- دکمه ذخیره -->
     <div class="text-end mt-4">
      <button wire:click="update"
       class="btn btn-primary rounded-pill px-5 py-2 d-flex align-items-center gap-2 shadow-md hover:shadow-lg transition-all">
       <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
        <path d="M17 21v-8H7v8M7 3v5h8" />
       </svg>
       ذخیره تغییرات
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

  .form-control {
   border: 1px solid #e5e7eb;
   border-radius: 8px;
   padding: 12px 15px;
   font-size: 14px;
   transition: all 0.3s ease;
   background: #fafafa;
   width: 100%;
  }

  .form-control:focus {
   border-color: #6b7280;
   box-shadow: 0 0 0 3px rgba(107, 114, 128, 0.2);
   background: #fff;
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

  .input-shiny {
   box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.05);
  }
 </style>

 <script>
  document.addEventListener('livewire:init', function() {
   Livewire.on('show-alert', (event) => {
    toastr[event.type](event.message);
   });
  });
 </script>
</div>

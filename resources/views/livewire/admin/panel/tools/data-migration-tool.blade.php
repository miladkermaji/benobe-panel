<div class="container-fluid py-1" dir="rtl">
 <!-- هدر -->
 <header class="glass-header p-3 rounded-3 mb-4 shadow-lg">
  <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
   <div class="d-flex align-items-center gap-2">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" class="animate-bounce">
     <path d="M12 2v20M2 12h20" />
    </svg>
    <h4 class="mb-0 fw-bold text-white">ابزار انتقال داده‌ها</h4>
   </div>
   <div class="text-white fw-medium hover:text-gray-100 transition-colors">انتقال داده‌ها از جداول قدیمی به جدید</div>
  </div>
 </header>

 <!-- فرم انتخاب جداول -->
 <div class="bg-light p-3 rounded-3 shadow-md mb-4 hover:shadow-lg transition-shadow">
  <div class="row g-3">
   <div class="col-md-6">
    <div class="d-flex flex-column gap-2">
     <label class="form-label fw-bold text-gray-800">فایل جدول قدیمی (CSV یا SQL)</label>
     <div class="input-group">
      <span class="input-group-text bg-white border-0">
       <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2" class="animate-pulse">
        <path d="M12 2v20M2 12h20" />
       </svg>
      </span>
      <input type="file" class="form-control input-shiny border-0 shadow-sm" wire:model.defer="oldTableFile">
     </div>
     @error('oldTableFile')
      <span class="text-danger d-block mt-1 text-sm">{{ $message }}</span>
     @enderror
     <div class="upload-progress-bar mt-2 shadow-sm" style="display: none;">
      <div class="progress-fill" style="width: 0%;">0%</div>
     </div>
    </div>
   </div>
   <div class="col-md-6">
    <div class="d-flex flex-column gap-2">
     <label class="form-label fw-bold text-gray-800">جدول جدید (دیتابیس فعلی)</label>
     <div class="d-flex" wire:ignore>
      <span class="input-group-text bg-white border-0">
       <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2" class="animate-pulse">
        <path d="M12 2v20M2 12h20" />
       </svg>
      </span>
      <select class="form-control input-shiny border-0 shadow-sm select2-table" id="newTableSelect">
       <option value="">جدول جدید را انتخاب کنید</option>
       @foreach ($tables as $table)
        <option value="{{ $table }}" {{ $newTable === $table ? 'selected' : '' }}>{{ $table }}</option>
       @endforeach
      </select>
     </div>
     @error('newTable')
      <span class="text-danger d-block mt-1 text-sm">{{ $message }}</span>
     @enderror
    </div>
   </div>
  </div>
 </div>

 <!-- پیش‌نمایش و نگاشت فیلدها -->
 @if (!empty($oldTableFields) && !empty($newTableFields))
  <div class="row g-3 mb-4">
   <div class="col-md-6">
    <div class="card shadow-md border-0 rounded-3 hover:shadow-xl transition-all">
     <div class="card-header glass-header text-white d-flex justify-content-between align-items-center">
      <span>فیلدهای جدول قدیمی</span>
      <span class="text-sm">{{ $oldTableFile->getClientOriginalName() }}</span>
     </div>
     <div class="card-body">
      <input type="text" class="form-control input-shiny mb-3 shadow-sm" wire:model.live="searchOld" placeholder="جستجو در فیلدها...">
      <ul class="list-group">
       @foreach (array_filter($oldTableFields, fn($field) => str_contains($field, $searchOld)) as $field)
        <li class="list-group-item d-flex justify-content-between align-items-center hover:bg-gray-50 transition-colors">
         {{ $field }}
         <select class="form-select w-50 input-shiny shadow-sm" wire:model.live="fieldMapping.{{ $field }}">
          <option value="">انتخاب فیلد جدید</option>
          @foreach ($newTableFields as $newField)
           <option value="{{ $newField }}">{{ $newField }}</option>
          @endforeach
         </select>
        </li>
       @endforeach
      </ul>
     </div>
    </div>
   </div>
   <div class="col-md-6">
    <div class="card shadow-md border-0 rounded-3 hover:shadow-xl transition-all">
     <div class="card-header glass-header text-white">فیلدهای جدول جدید: {{ $newTable }}</div>
     <div class="card-body">
      <input type="text" class="form-control input-shiny mb-3 shadow-sm" wire:model.live="searchNew" placeholder="جستجو در فیلدها...">
      <ul class="list-group">
       @foreach (array_filter($newTableFields, fn($field) => str_contains($field, $searchNew)) as $field)
        <li class="list-group-item hover:bg-gray-50 transition-colors">{{ $field }}</li>
       @endforeach
      </ul>
     </div>
    </div>
   </div>
  </div>

  <!-- دکمه انتقال و پروگرس بار انتقال -->
  <div class="d-flex justify-content-center gap-3 mb-4">
   <button wire:click="migrateData" class="btn btn-gradient-primary px-4 shadow-md hover:shadow-lg transition-all" wire:loading.attr="disabled">
    <span wire:loading.remove>شروع انتقال</span>
    <span wire:loading>در حال انتقال...</span>
   </button>
  </div>
  <div class="migration-progress-bar mb-4 shadow-md" style="display: {{ $isMigrating || $progress > 0 ? 'block' : 'none' }};">
   <div class="progress-fill" style="width: {{ $progress }}%;">{{ $progress }}%</div>
  </div>
 @endif

 <!-- استایل‌ها -->
 <style>
  .glass-header {
   background: linear-gradient(135deg, rgba(79, 70, 229, 0.95), rgba(124, 58, 237, 0.85));
   backdrop-filter: blur(12px);
   border: 1px solid rgba(255, 255, 255, 0.3);
   box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
   transition: all 0.3s ease;
  }

  .glass-header:hover {
   box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
   transform: translateY(-2px);
  }

  .bg-light {
   background: #f9fafb;
   border: 1px solid #e5e7eb;
   border-radius: 8px;
   transition: all 0.3s ease;
  }

  .input-shiny,
  .form-control,
  .form-select,
  .select2-container .select2-selection--single {
   border: 1px solid #d1d5db;
   border-radius: 6px;
   height: 40px;
   padding: 10px 15px;
   font-size: 14px;
   background: #fff;
   box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
   transition: all 0.3s ease;
  }

  .input-shiny:hover,
  .form-control:hover,
  .form-select:hover {
   border-color: #a5b4fc;
   box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  }

  .input-shiny:focus,
  .form-control:focus,
  .form-select:focus {
   border-color: #4f46e5;
   box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
   outline: none;
  }

  .select2-container--default .select2-selection--single .select2-selection__rendered {
   line-height: 40px;
   padding-right: 30px;
  }

  .select2-container--default .select2-selection--single .select2-selection__arrow {
   height: 40px;
   top: 0;
   right: 5px;
  }

  .select2-container--default .select2-results__option--highlighted[aria-selected],
  .select2-container--default .select2-results__option:hover {
   background-color: #4f46e5 !important;
   color: white !important;
  }

  .btn-gradient-primary {
   background: linear-gradient(90deg, #4f46e5, #7c3aed);
   border: none;
   color: white;
   font-weight: 600;
   border-radius: 6px;
   transition: all 0.3s ease;
  }

  .btn-gradient-primary:hover {
   background: linear-gradient(90deg, #4338ca, #6b21a8);
   transform: translateY(-2px);
   box-shadow: 0 8px 20px rgba(79, 70, 229, 0.25);
  }

  .upload-progress-bar,
  .migration-progress-bar {
   width: 100%;
   height: 20px;
   background: #e5e7eb;
   border-radius: 10px;
   overflow: hidden;
   box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
   transition: all 0.3s ease;
  }

  .progress-fill {
   height: 100%;
   background: linear-gradient(90deg, #4f46e5, #7c3aed);
   color: white;
   text-align: center;
   line-height: 20px;
   font-size: 12px;
   font-weight: 600;
   transition: width 0.5s ease-in-out;
   box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .list-group-item {
   border: none;
   border-bottom: 1px solid #e5e7eb;
   padding: 10px 0;
  }

  .list-group-item:last-child {
   border-bottom: none;
  }

  /* انیمیشن‌ها */
  .animate-bounce {
   animation: bounce 1s infinite;
  }

  @keyframes bounce {
   0%, 100% { transform: translateY(0); }
   50% { transform: translateY(-4px); }
  }

  .animate-pulse {
   animation: pulse 1.5s infinite;
  }

  @keyframes pulse {
   0% { opacity: 1; }
   50% { opacity: 0.7; }
   100% { opacity: 1; }
  }

  /* بهبود در تبلت و موبایل */
  @media (max-width: 991px) { /* تبلت */
   .glass-header {
    padding: 2rem;
    border-radius: 10px;
   }
   
   .bg-light {
    padding: 2rem;
    border-radius: 10px;
   }
   
   .card {
    margin-bottom: 1rem;
   }
   
   .btn-gradient-primary {
    padding: 0.75rem 2rem;
    font-size: 0.9rem;
   }
   
   .input-shiny,
   .form-control,
   .form-select {
    height: 38px;
    font-size: 0.9rem;
   }
  }

  @media (max-width: 767px) { /* موبایل */
   .glass-header {
    padding: 1.5rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
   }
   
   .bg-light {
    padding: 1.5rem;
   }
   
   .card-body {
    padding: 1rem;
   }
   
   .btn-gradient-primary {
    width: 100%;
    padding: 0.75rem;
    font-size: 0.875rem;
   }
   
   .input-shiny,
   .form-control,
   .form-select {
    font-size: 0.875rem;
    height: 36px;
   }
   
   .list-group-item {
    font-size: 0.875rem;
    padding: 8px 0;
   }
   
   .form-select.w-50 {
    width: 100% !important;
    margin-top: 0.5rem;
   }
   
   /* تنظیم لیبل و اینپوت در موبایل */
   .form-label {
    font-size: 0.875rem;
    white-space: normal; /* جلوگیری از اوورفلو */
    overflow-wrap: break-word; /* شکستن کلمات طولانی */
   }
   
   .input-group {
    width: 100%;
   }
  }
 </style>

 <!-- اسکریپت‌ها -->
 <script>
  document.addEventListener('livewire:init', () => {
   $('#newTableSelect').select2({
    placeholder: 'جدول جدید را انتخاب کنید',
    dir: 'rtl',
    width: '100%'
   });

   $('#newTableSelect').val(@json($newTable)).trigger('change');

   $('#newTableSelect').on('change', function () {
    const selectedValue = $(this).val();
    @this.set('newTable', selectedValue);
   });

   Livewire.on('toast', (message, options = {}) => {
    toastr[options.type || 'info'](message, null, {
     timeOut: options.duration || 5000,
     tapToDismiss: false,
     escapeHtml: false
    });
   });

   Livewire.on('uploadProgressUpdated', (event) => {
    setTimeout(() => {
     const progress = event.detail ? event.detail.progress : @this.uploadProgress;
     const uploadProgressBar = document.querySelector('.upload-progress-bar .progress-fill');
     if (uploadProgressBar) {
      uploadProgressBar.style.width = `${progress}%`;
      uploadProgressBar.innerText = `${progress}%`;
      uploadProgressBar.closest('.upload-progress-bar').style.display = 'block';
      if (progress === 100) {
       setTimeout(() => {
        uploadProgressBar.closest('.upload-progress-bar').style.display = 'none';
       }, 10000);
      }
     }
    }, 100);
   });

   Livewire.on('progressUpdated', (event) => {
    const progress = event.detail ? event.detail.progress : @this.progress;
    const migrationProgressBar = document.querySelector('.migration-progress-bar .progress-fill');
    if (migrationProgressBar) {
     migrationProgressBar.style.width = `${progress}%`;
     migrationProgressBar.innerText = `${progress}%`;
     migrationProgressBar.closest('.migration-progress-bar').style.display = 'block';
    }
   });
  });
  document.addEventListener('livewire:init', () => {
    Livewire.on('showDuplicateConfirm', (data) => {
        Swal.fire({
            title: 'رکورد تکراری شناسایی شد',
            html: `رکورد با شناسه ${data.id} در جدول ${data.table} از قبل وجود دارد. چه عملیاتی انجام شود؟`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'جایگزین کن',
            cancelButtonText: 'رد کن',
            showDenyButton: true,
            denyButtonText: 'برای همه موارد تکراری جایگزین کن',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch('handleDuplicateResponse', {action: 'replace'});
            } else if (result.isDenied) {
                Livewire.dispatch('handleDuplicateResponse', {action: 'replace-all'});
            } else {
                Livewire.dispatch('handleDuplicateResponse', {action: 'skip'});
            }
        });
    });
});
 </script>
</div>
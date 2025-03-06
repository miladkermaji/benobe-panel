<div class="container-fluid py-1" dir="rtl">
 <!-- هدر -->
 <header class="glass-header p-3 rounded-3 mb-4 shadow-lg">
  <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
   <div class="d-flex align-items-center gap-2">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"
     class="animate-bounce">
     <path d="M4 4h16v12H4z" />
     <path d="M4 8l8 4 8-4" />
    </svg>
    <h4 class="mb-0 fw-bold text-white">مدیریت قالب‌های ایمیل</h4>
   </div>
   <div class="text-white fw-medium">لیست قالب‌ها</div>
  </div>
 </header>

 <!-- فرم و سلکت قالب -->
 <div class="row g-3 mb-4">
  <div class="col-md-3">
   <div class="card shadow-sm border-0">
    <div class="card-header bg-gradient-light text-dark fw-bold py-2">انتخاب قالب</div>
    <div class="card-body pt-3 pb-2">
     <label class="fw-bold mb-2 text-dark">انتخاب:</label>
     <select class="form-control input-shiny" wire:model.live="selectedTemplateId">
      <option value="">-- انتخاب کنید --</option>
      @foreach ($allTemplates as $template)
       <option value="{{ $template->id }}">{{ $template->subject }}</option>
      @endforeach
     </select>
    </div>
   </div>
  </div>
  <div class="col-md-9">
   <div class="card shadow-sm border-0">
    <div class="card-header bg-gradient-light text-dark fw-bold py-2">ایجاد یا ویرایش قالب</div>
    <div class="card-body pt-3 pb-2">
     <div class="form-group mb-3">
      <label class="fw-bold mb-2 text-dark">عنوان قالب</label>
      <input type="text" class="form-control input-shiny" wire:model="newSubject"
       placeholder="مثال: بازگردانی رمزعبور">
      @if ($errors->has('newSubject'))
       <span class="text-danger d-block mt-1">{{ $errors->first('newSubject') }}</span>
      @endif
     </div>
     <div class="form-group mb-3">
      <label class="fw-bold mb-2 text-dark">محتوای قالب</label>
      <textarea class="form-control input-shiny" wire:model="newTemplate" rows="8" id="newTemplateEditor"></textarea>
      @if ($errors->has('newTemplate'))
       <span class="text-danger d-block mt-1">{{ $errors->first('newTemplate') }}</span>
      @endif
     </div>
     <button wire:click="{{ $editId ? 'updateTemplate' : 'addTemplate' }}" class="btn btn-gradient-success w-100 py-2">
      {{ $editId ? 'ویرایش' : 'ذخیره تغییرات' }}
     </button>
    </div>
   </div>
  </div>
 </div>

 <!-- ابزارها و جستجو -->
 <div class="container px-0 mb-4">
  <div class="bg-light p-3 rounded-3 shadow-sm">
   <div class="row g-3">
    <div class="col-md-6">
     <div class="input-group">
      <span class="input-group-text bg-white border-0">
       <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
        <circle cx="11" cy="11" r="8" />
        <path d="M21 21l-4.35-4.35" />
       </svg>
      </span>
      <input type="text" class="form-control input-shiny border-0 shadow-none" wire:model.live="search"
       placeholder="جستجو در قالب‌ها...">
     </div>
    </div>
    <div class="col-md-6">
     <div class="d-flex gap-2 justify-content-end">
      <button wire:click="deleteSelected"
       class="btn btn-gradient-danger rounded-pill px-3 d-flex align-items-center gap-2"
       @if (empty($selectedTemplates)) disabled @endif>
       <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
        <path d="M3 6h18" />
        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
       </svg>
       حذف انتخاب‌شده‌ها
      </button>
     </div>
    </div>
   </div>
  </div>
 </div>

 <!-- لیست قالب‌ها -->
 <div class="container px-0">
  <div class="card shadow-sm border-0">
   <div class="card-body p-0">
    <div class="table-responsive text-nowrap">
     <table class="table table-bordered table-hover w-100 m-0">
      <thead class="glass-header text-white">
       <tr>
        <th class="text-center align-middle">
         <input type="checkbox" wire:model.live="selectAll" class="py-2">
        </th>
        <th class="text-center align-middle">ردیف</th>
        <th class="text-center align-middle">عنوان</th>
        <th class="text-center align-middle">وضعیت</th>
        <th class="text-center align-middle">عملیات</th>
       </tr>
      </thead>
      <tbody>
       @forelse ($templates as $index => $template)
        <tr>
         <td class="align-middle text-center">
          <input type="checkbox" wire:model.live="selectedTemplates" value="{{ $template->id }}"
           class="py-2">
         </td>
         <td class="align-middle text-center">{{ $templates->firstItem() + $index }}</td>
         <td class="align-middle text-center">{{ $template->subject }}</td>
         <td class="align-middle text-center">
          <button wire:click="toggleStatus({{ $template->id }})"
           class="badge {{ $template->is_active ? 'bg-success' : 'bg-danger' }} border-0 cursor-pointer text-white py-1 px-2">
           {{ $template->is_active ? 'فعال' : 'غیرفعال' }}
          </button>
         </td>
         <td class="align-middle text-center">
          <div class="d-flex gap-2 justify-content-center">
           <button wire:click="startEdit({{ $template->id }})"
            class="btn btn-gradient-warning rounded-pill px-3 d-flex align-items-center gap-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
             <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
             <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
            </svg>
            
           </button>
           <button wire:click="previewTemplate({{ $template->id }})"
            class="btn btn-gradient-primary rounded-pill px-3 d-flex align-items-center gap-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
             <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
             <circle cx="12" cy="12" r="3" />
            </svg>
            
           </button>
           <button onclick="confirmDelete({{ $template->id }})"
            class="btn btn-gradient-danger rounded-pill px-3 d-flex align-items-center gap-2">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
             <path d="M3 6h18" />
             <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
             <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
            </svg>
            
           </button>
          </div>
         </td>
        </tr>
       @empty
        <tr>
         <td colspan="5" class="text-center py-4">
          <div class="d-flex justify-content-center align-items-center flex-column">
           <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2"
            class="mb-2">
            <path d="M4 4h16v12H4z" />
            <path d="M4 8l8 4 8-4" />
           </svg>
           <p class="text-muted fw-medium">هیچ قالبی یافت نشد.</p>
          </div>
         </td>
        </tr>
       @endforelse
      </tbody>
     </table>
    </div>
    <div class="d-flex justify-content-between mt-3 px-3">
     <div class="text-muted">نمایش {{ $templates->firstItem() }} تا {{ $templates->lastItem() }} از
      {{ $templates->total() }} ردیف</div>
     {{ $templates->links() }}
    </div>
   </div>
  </div>
 </div>

 <!-- نکته راهنما -->
 <div class="help-block mt-4 p-3 rounded-3 shadow-sm d-flex align-items-center gap-2">
  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2">
   <circle cx="12" cy="12" r="10" />
   <path d="M12 16v-4" />
   <path d="M12 8h.01" />
  </svg>
  <span class="text-dark">کاربر گرامی، برای تغییر و جایگزینی هر گونه محتوا ابتدا یک پشتیبان از آن دریافت کنید و سپس
   اقدام به تغییر اطلاعات نمایید. همچنین توجه داشته باشید که عباراتی مثل [_sitename_] حاوی اطلاعات هستند که پس از ارسال
   ایمیل در ایمیل کاربران قابل مشاهده می‌شوند.</span>
 </div>

 <!-- استایل‌ها -->
 <style>
  .glass-header {
   background: linear-gradient(135deg, rgba(79, 70, 229, 0.95), rgba(124, 58, 237, 0.85));
   backdrop-filter: blur(10px);
   border: 1px solid rgba(255, 255, 255, 0.2);
   box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
   transition: all 0.3s ease;
  }

  .glass-header:hover {
   box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
  }

  .bg-gradient-light {
   background: linear-gradient(135deg, #f9fafb, #e5e7eb);
   border-bottom: 1px solid #e5e7eb;
  }

  .input-shiny,
  .form-control {
   border: 1px solid #d1d5db;
   border-radius: 0;
   /* گوشه‌ها تیز */
   height: 50px;
   /* ارتفاع بیشتر */
   padding: 10px 15px;
   font-size: 14px;
   background: #fff;
   box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
   transition: all 0.3s ease;
  }

  textarea.input-shiny,
  textarea.form-control {
   height: auto;
   /* برای textarea ارتفاع ثابت نباشه */
  }

  .input-shiny:focus,
  .form-control:focus {
   border-color: #4f46e5;
   box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.25), inset 0 2px 4px rgba(0, 0, 0, 0.05);
  }

  .btn-gradient-primary {
   background: linear-gradient(90deg, #4f46e5, #7c3aed);
   border: none;
   color: white;
   transition: all 0.3s ease;
   height: 40px;
   /* تراز با اینپوت‌ها */
  }

  .btn-gradient-primary:hover {
   background: linear-gradient(90deg, #4338ca, #6b21a8);
   transform: translateY(-2px);
   box-shadow: 0 5px 12px rgba(0, 0, 0, 0.15);
  }

  .btn-gradient-success {
   background: linear-gradient(90deg, #10b981, #34d399);
   border: none;
   color: white;
   transition: all 0.3s ease;
   height: 40px;
  }

  .btn-gradient-success:hover {
   background: linear-gradient(90deg, #059669, #10b981);
   transform: translateY(-2px);
   box-shadow: 0 5px 12px rgba(0, 0, 0, 0.15);
  }

  .btn-gradient-danger {
   background: linear-gradient(90deg, #f87171, #fca5a5);
   border: none;
   color: white;
   transition: all 0.3s ease;
   height: 40px;
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

  .btn-gradient-warning {
   background: linear-gradient(90deg, #f59e0b, #fbbf24);
   border: none;
   color: white;
   transition: all 0.3s ease;
   height: 40px;
  }

  .btn-gradient-warning:hover {
   background: linear-gradient(90deg, #d97706, #f59e0b);
   transform: translateY(-2px);
   box-shadow: 0 5px 12px rgba(0, 0, 0, 0.15);
  }

  .btn-gradient-secondary {
   background: linear-gradient(90deg, #6b7280, #9ca3af);
   border: none;
   color: white;
   transition: all 0.3s ease;
   height: 40px;
  }

  .btn-gradient-secondary:hover {
   background: linear-gradient(90deg, #4b5563, #6b7280);
   transform: translateY(-2px);
   box-shadow: 0 5px 12px rgba(0, 0, 0, 0.15);
  }

  .bg-light {
   background: #f9fafb;
   border: 1px solid #e5e7eb;
   border-radius: 6px;
  }

  .table-bordered {
   border: 1px solid #e5e7eb;
   border-radius: 6px;
   overflow: hidden;
  }

  .table-bordered th,
  .table-bordered td {
   border: 1px solid #e5e7eb;
  }

  .cursor-pointer {
   cursor: pointer;
  }

  .help-block {
   background: #fef3c7;
   padding: 12px;
   border-radius: 6px;
   color: #d97706;
   font-size: 14px;
   border: 1px solid #fde68a;
  }
 </style>

 <!-- اسکریپت‌ها -->
 <script>
  document.addEventListener('livewire:init', () => {
   let editor = CodeMirror.fromTextArea(document.getElementById('newTemplateEditor'), {
    mode: 'htmlmixed',
    lineNumbers: true,
    dragDrop: false,
    indentWithTabs: false,
    lineWrapping: true,
    indentUnit: 4,
    theme: 'default'
   });

   Livewire.on('updateEditor', (template) => {
    editor.setValue(template);
   });

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
     text: 'قالب‌های انتخاب‌شده حذف خواهند شد و قابل بازگشت نیستند!',
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

   Livewire.on('openPreview', (template) => {
    let x = screen.width / 2 - 700 / 2;
    let y = screen.height / 2 - 450 / 2;
    let previewWindow = window.open('', 'Preview', 'height=500,width=600,left=' + x + ',top=' + y);
    previewWindow.document.open();
    previewWindow.document.write(template);
    previewWindow.document.close();
    if (previewWindow.focus) previewWindow.focus();
   });

   // همگام‌سازی ادیتور با Livewire
   editor.on('change', (cm) => {
    @this.set('newTemplate', cm.getValue());
   });
  });

  function confirmDelete(id) {
   Swal.fire({
    title: 'آیا مطمئن هستید؟',
    text: 'این قالب حذف خواهد شد و قابل بازگشت نیست!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#d1d5db',
    confirmButtonText: 'بله، حذف کن',
    cancelButtonText: 'خیر',
   }).then((result) => {
    if (result.isConfirmed) {
     @this.deleteTemplate(id);
    }
   });
  }
 </script>
</div>

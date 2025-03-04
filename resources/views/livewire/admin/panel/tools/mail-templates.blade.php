<div class="container-fluid py-1">
 <!-- هدر -->
 <header class="glass-header p-4 rounded-3 mb-2 shadow-lg">
  <div class="d-flex align-items-center justify-content-between gap-4">
   <div class="d-flex align-items-center gap-3">
    <i class="fas fa-envelope fs-3 text-white animate-bounce"></i>
    <h4 class="mb-0 fw-bold text-white">مدیریت قالب‌های ایمیل</h4>
   </div>
   <div class="text-white fw-medium">لیست قالب‌ها</div>
  </div>
 </header>

 <!-- فرم و سلکت قالب -->
 <div class="row g-4 mb-2">
  <div class="col-md-3">
   <div class="panel panel-default shadow-sm">
    <div class="panel-heading">انتخاب قالب</div>
    <div class="panel-body">
     <div class="form-group">
      <label class="control-label fw-bold mb-2">انتخاب:</label>
      <select class="form-control input-shiny" wire:model.live="selectedTemplateId">
       <option value="">-- انتخاب کنید --</option>
       @foreach ($allTemplates as $template)
        <option value="{{ $template->id }}">{{ $template->subject }}</option>
       @endforeach
      </select>
     </div>
    </div>
   </div>
  </div>
  <div class="col-md-9">
   <div class="panel panel-default shadow-sm">
    <div class="panel-heading">ایجاد یا ویرایش قالب</div>
    <div class="panel-body">
     <div class="form-group mb-3">
      <label class="control-label fw-bold mb-2">عنوان قالب</label>
      <input type="text" class="form-control input-shiny" wire:model="newSubject"
       placeholder="مثال: بازگردانی رمزعبور">
      @if ($errors->has('newSubject'))
       <span class="text-danger d-block mt-1">{{ $errors->first('newSubject') }}</span>
      @endif
     </div>
     <div class="form-group mb-3">
      <label class="control-label fw-bold mb-2">محتوای قالب</label>
      <textarea class="form-control" wire:model="newTemplate" rows="10" id="newTemplateEditor"></textarea>
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
 <div class="container px-0 mb-5">
  <div class="bg-light p-4 rounded-3 shadow-sm">
   <div class="row g-4">
    <div class="col-md-6">
     <div class="input-group">
      <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
      <input type="text" class="form-control border-0 shadow-none" wire:model.live="search"
       placeholder="جستجو در قالب‌ها...">
     </div>
    </div>
    <div class="col-md-6">
     <div class="d-flex gap-2 justify-content-end">
      <button wire:click="export" class="btn btn-gradient-secondary rounded-pill px-4">
       <i class="fas fa-download"></i> خروجی CSV
      </button>
      <button wire:click="deleteSelected" class="btn btn-gradient-danger rounded-pill px-4"
       @if (empty($selectedTemplates)) disabled @endif>
       <i class="fas fa-trash"></i> حذف انتخاب‌شده‌ها
      </button>
     </div>
    </div>
   </div>
  </div>
 </div>

 <!-- لیست قالب‌ها -->
 <div class="container px-0">
  <div class="card shadow-sm">
   <div class="card-body">
    <div class="table-responsive text-nowrap">
     <table class="table table-bordered">
      <thead>
       <tr>
        <th><input type="checkbox" wire:model.live="selectAll" class="form-check-input"></th>
        <th>ردیف</th>
        <th>عنوان</th>
        <th>وضعیت</th>
        <th>عملیات</th>
       </tr>
      </thead>
      <tbody>
       @forelse ($templates as $index => $template)
        <tr>
         <td><input type="checkbox" wire:model.live="selectedTemplates" value="{{ $template->id }}"
           class="form-check-input"></td>
         <td>{{ $templates->firstItem() + $index }}</td>
         <td>{{ $template->subject }}</td>
         <td>
          <button wire:click="toggleStatus({{ $template->id }})"
           class="badge {{ $template->is_active ? 'bg-label-success' : 'bg-label-danger' }} border-0 cursor-pointer">
           {{ $template->is_active ? 'فعال' : 'غیرفعال' }}
          </button>
         </td>
         <td>
          <div class="d-flex gap-2">
           <button wire:click="startEdit({{ $template->id }})"
            class="btn btn-gradient-warning rounded-full w-8 h-8 flex items-center justify-center">
            <i class="fas fa-edit"></i>
           </button>
           <button wire:click="previewTemplate({{ $template->id }})"
            class="btn btn-gradient-primary rounded-full w-8 h-8 flex items-center justify-center">
            <i class="fas fa-eye"></i>
           </button>
           <button onclick="confirmDelete({{ $template->id }})"
            class="btn btn-gradient-danger rounded-full w-8 h-8 flex items-center justify-center">
            <i class="fas fa-trash"></i>
           </button>
          </div>
         </td>
        </tr>
       @empty
        <tr>
         <td colspan="5" class="text-center py-5">
          <i class="fas fa-envelope-open fs-1 text-muted mb-3"></i>
          <p class="text-muted fw-medium">هیچ قالبی یافت نشد.</p>
         </td>
        </tr>
       @endforelse
      </tbody>
     </table>
    </div>
    <div class="d-flex justify-content-between mt-4">
     <div class="text-muted">نمایش {{ $templates->firstItem() }} تا {{ $templates->lastItem() }} از
      {{ $templates->total() }} ردیف</div>
     {{ $templates->links() }}
    </div>
   </div>
  </div>
 </div>

 <!-- نکته راهنما -->
 <div class="help-block mt-4">
  <i class="fa fa-lightbulb-o"></i> <i class="fa fa-angle-double-left"></i> کاربر گرامی، برای تغییر و جایگزینی هر گونه
  محتوا ابتدا یک پشتیبان از آن دریافت کنید و سپس اقدام به تغییر اطلاعات نمایید. همچنین توجه داشته باشید که عباراتی مثل
  [_sitename_] حاوی اطلاعات هستند که پس از ارسال ایمیل در ایمیل کاربران قابل مشاهده می‌شوند.
 </div>

 <!-- استایل‌ها -->
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
   padding: 15px;
   font-weight: bold;
   border-bottom: 1px solid #e5e7eb;
   border-radius: 8px 8px 0 0;
   color: #4b5563;
  }

  .panel-body {
   padding: 20px;
  }

  .input-shiny,
  .form-control {
   border: 1px solid #d1d5db;
   border-radius: 8px;
   padding: 10px;
   font-size: 14px;
   transition: all 0.3s ease;
   background: #fff;
   box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
  }

  .input-shiny:focus,
  .form-control:focus {
   border-color: #4f46e5;
   box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.25), inset 0 1px 3px rgba(0, 0, 0, 0.05);
  }

  .btn-gradient-primary {
   background: linear-gradient(90deg, #4f46e5, #7c3aed);
   border: none;
   color: white;
   box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
   transition: all 0.3s ease;
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
   box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
   transition: all 0.3s ease;
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

  .btn-gradient-warning {
   background: linear-gradient(90deg, #f59e0b, #fbbf24);
   border: none;
   color: white;
   box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
   transition: all 0.3s ease;
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
   box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
   transition: all 0.3s ease;
  }

  .btn-gradient-secondary:hover {
   background: linear-gradient(90deg, #4b5563, #6b7280);
   transform: translateY(-2px);
   box-shadow: 0 5px 12px rgba(0, 0, 0, 0.15);
  }

  .bg-light {
   background: #f9fafb;
   border: 1px solid #e5e7eb;
  }

  .table-bordered {
   border: 1px solid #e5e7eb;
  }

  .table-bordered th,
  .table-bordered td {
   border: 1px solid #e5e7eb;
  }

  .rounded-full {
   border-radius: 50%;
   padding: 0;
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

  .help-block {
   background: #fef3c7;
   padding: 15px;
   border-radius: 8px;
   color: #d97706;
   font-size: 14px;
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

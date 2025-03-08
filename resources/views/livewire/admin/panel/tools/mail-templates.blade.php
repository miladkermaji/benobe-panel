<div class="container-fluid py-4" dir="rtl">
 <!-- هدر -->
 <header class="glass-header p-4 rounded-xl mb-5 shadow-lg animate__animated animate__fadeIn">
  <div class="d-flex align-items-center justify-content-start gap-3 flex-nowrap">
   <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"
    class="animate-pulse">
    <path d="M4 4h16v12H4z" />
    <path d="M4 8l8 4 8-4" />
   </svg>
   <h4 class="mb-0 fw-bold text-white tracking-tight">مدیریت قالب‌های ایمیل</h4>
  </div>
 </header>
 <!-- فرم و سلکت قالب -->
 <div class="row g-4 mb-5">
  <div class="col-lg-3 col-md-4 col-sm-12">
   <div class="card shadow-sm border-0 rounded-xl animate__animated animate__fadeInUp">
    <div class="card-header bg-gradient-light text-dark fw-bold py-3 px-4 rounded-top-xl">انتخاب قالب</div>
    <div class="card-body p-4">
     <label class="fw-bold mb-2 text-dark">انتخاب:</label>
     <select class="form-control input-modern w-100" wire:model.live="selectedTemplateId">
      <option value="">-- انتخاب کنید --</option>
      @foreach ($allTemplates as $template)
       <option value="{{ $template->id }}">{{ $template->subject }}</option>
      @endforeach
     </select>
    </div>
   </div>
  </div>
  <div class="col-lg-9 col-md-8 col-sm-12">
   <div class="card shadow-sm border-0 rounded-xl animate__animated animate__fadeInUp">
    <div class="card-header bg-gradient-light text-dark fw-bold py-3 px-4 rounded-top-xl">ایجاد یا ویرایش قالب</div>
    <div class="card-body p-4">
     <div class="form-group mb-4">
      <label class="fw-bold mb-2 text-dark">عنوان قالب</label>
      <input type="text" class="form-control input-modern w-100" wire:model="newSubject"
       placeholder="مثال: بازگردانی رمزعبور">
      @if ($errors->has('newSubject'))
       <span class="text-danger d-block mt-2 text-sm">{{ $errors->first('newSubject') }}</span>
      @endif
     </div>
     <div class="form-group mb-4">
      <label class="fw-bold mb-2 text-dark">محتوای قالب</label>
      <textarea class="form-control input-modern w-100" wire:model="newTemplate" rows="10" id="newTemplateEditor" dir="ltr"></textarea>
      @if ($errors->has('newTemplate'))
       <span class="text-danger d-block mt-2 text-sm">{{ $errors->first('newTemplate') }}</span>
      @endif
     </div>
     <button wire:click="{{ $editId ? 'updateTemplate' : 'addTemplate' }}"
      class="btn btn-gradient-success w-100 py-3 rounded-xl shadow-sm">
      {{ $editId ? 'ویرایش' : 'ذخیره تغییرات' }}
     </button>
    </div>
   </div>
  </div>
 </div>
 <!-- ابزارها و جستجو -->
 <div class="container-fluid px-0 mb-5">
  <div class="bg-light p-4 rounded-xl shadow-sm animate__animated animate__fadeInUp">
   <div class="row g-4 align-items-center">
    <div class="col-md-6 col-sm-12">
     <div class="input-group align-items-center">
      <span class="input-group-text bg-white border-0 rounded-start-xl flex-shrink-0"
       style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
       <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
        <circle cx="11" cy="11" r="8" />
        <path d="M21 21l-4.35-4.35" />
       </svg>
      </span>
      <input type="text" class="form-control input-modern border-0 rounded-end-xl shadow-none flex-grow-1"
       wire:model.live="search" placeholder="جستجو در قالب‌ها...">
     </div>
    </div>
    <div class="col-md-6 col-sm-12 d-flex justify-content-md-end justify-content-center">
     <button wire:click="deleteSelected"
      class="btn btn-gradient-danger rounded-xl px-4 py-3 d-flex align-items-center gap-2 shadow-sm"
      @if (empty($selectedTemplates)) disabled @endif>
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
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
 <!-- لیست قالب‌ها -->
 <div class="container-fluid px-0">
  <div class="card shadow-sm border-0 rounded-xl animate__animated animate__fadeInUp">
   <div class="card-body p-0">
    <div class="table-responsive text-nowrap">
     <table class="table table-bordered table-hover w-100 m-0 rounded-xl">
      <thead class="glass-header text-white">
       <tr>
        <th class="text-center align-middle py-3"><input type="checkbox" wire:model.live="selectAll" class="py-2">
        </th>
        <th class="text-center align-middle py-3">ردیف</th>
        <th class="text-center align-middle py-3">عنوان</th>
        <th class="text-center align-middle py-3">وضعیت</th>
        <th class="text-center align-middle py-3">عملیات</th>
       </tr>
      </thead>
      <tbody>
       @forelse ($templates as $index => $template)
        <tr class="animate__animated animate__fadeIn">
         <td class="align-middle text-center"><input type="checkbox" wire:model.live="selectedTemplates"
           value="{{ $template->id }}" class="py-2"></td>
         <td class="align-middle text-center">{{ $templates->firstItem() + $index }}</td>
         <td class="align-middle text-center">{{ $template->subject }}</td>
         <td class="align-middle text-center">
          <button wire:click="toggleStatus({{ $template->id }})"
           class="badge {{ $template->is_active ? 'bg-success' : 'bg-danger' }} border-0 cursor-pointer text-white py-2 px-3 rounded-lg">
           {{ $template->is_active ? 'فعال' : 'غیرفعال' }}
          </button>
         </td>
         <td class="align-middle text-center">
          <div class="d-flex gap-3 justify-content-center flex-wrap">
           <button wire:click="startEdit({{ $template->id }})"
            class="btn btn-gradient-warning rounded-xl px-3 py-2 d-flex align-items-center gap-2 shadow-sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
             <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
             <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
            </svg>
           </button>
           <button wire:click="previewTemplate({{ $template->id }})"
            class="btn btn-gradient-primary rounded-xl px-3 py-2 d-flex align-items-center gap-2 shadow-sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
             <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
             <circle cx="12" cy="12" r="3" />
            </svg>
           </button>
           <button onclick="confirmDelete({{ $template->id }})"
            class="btn btn-gradient-danger rounded-xl px-3 py-2 d-flex align-items-center gap-2 shadow-sm">
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
         <td colspan="5" class="text-center py-5">
          <div class="d-flex justify-content-center align-items-center flex-column">
           <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2"
            class="mb-3">
            <path d="M4 4h16v12H4z" />
            <path d="M4 8l8 4 8-4" />
           </svg>
           <p class="text-muted fw-medium text-lg">هیچ قالبی یافت نشد.</p>
          </div>
         </td>
        </tr>
       @endforelse
      </tbody>
     </table>
    </div>
    <div class="d-flex justify-content-between mt-4 px-4 flex-wrap gap-3">
     <div class="text-muted">نمایش {{ $templates->firstItem() }} تا {{ $templates->lastItem() }} از
      {{ $templates->total() }} ردیف</div>
     {{ $templates->links() }}
    </div>
   </div>
  </div>
 </div>
 <!-- نکته راهنما -->
 <div
  class="help-block mt-5 p-4 rounded-xl shadow-sm d-flex align-items-center gap-3 animate__animated animate__fadeInUp">
  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2">
   <circle cx="12" cy="12" r="10" />
   <path d="M12 16v-4" />
   <path d="M12 8h.01" />
  </svg>
  <span class="text-dark text-sm leading-relaxed">کاربر گرامی، برای تغییر و جایگزینی هر گونه محتوا ابتدا یک پشتیبان از
   آن دریافت کنید و سپس اقدام به تغییر اطلاعات نمایید. همچنین توجه داشته باشید که عباراتی مثل [_sitename_] حاوی
   اطلاعات هستند که پس از ارسال ایمیل در ایمیل کاربران قابل مشاهده می‌شوند.</span>
 </div>
 <link rel="stylesheet" href="{{ asset('admin-assets/css/panel/tools/mail-template/mail-template.css') }}">
 <!-- استایل‌ها -->
 <!-- اسکریپت‌ها (بدون تغییر) -->
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
    // اطمینان از اینکه مقدار همیشه یک رشته باشد
    Livewire.on('updateEditor', (template) => {
      const safeTemplate = (template && typeof template === 'string') ? template : '';
      editor.setValue(safeTemplate);
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
      // لاگ برای دیباگ
      // اگر template آرایه باشد، اولین عنصر آن را بگیریم یا مقدار پیش‌فرضを設定 کنیم
      const safePreview = (template && typeof template === 'string')
        ? template
        : (Array.isArray(template) && template[0] && typeof template[0] === 'string')
          ? template[0]
          : '<p>محتوای پیش‌نمایش در دسترس نیست.</p>';
      let x = screen.width / 2 - 700 / 2;
      let y = screen.height / 2 - 450 / 2;
      let previewWindow = window.open('', 'Preview', 'height=500,width=600,left=' + x + ',top=' + y);
      previewWindow.document.open();
      previewWindow.document.write(safePreview);
      previewWindow.document.close();
      if (previewWindow.focus) previewWindow.focus();
    });
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

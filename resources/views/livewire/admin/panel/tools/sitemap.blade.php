<div class="container-fluid py-5">
 <!-- هدر -->
 <header class="glass-header p-4 rounded-3 mb-5 shadow-lg">
  <div class="d-flex align-items-center justify-content-between gap-4">
   <div class="d-flex align-items-center gap-3">
    <i class="fas fa-sitemap fs-3 text-white animate-bounce"></i>
    <h4 class="mb-0 fw-bold text-white">مدیریت نقشه سایت</h4>
   </div>
   <div class="text-white fw-medium">مشاهده و مدیریت لینک‌ها</div>
  </div>
 </header>

 <!-- وضعیت و عملیات -->
 <div class="row g-4 mb-5">
  <div class="col-md-4">
   <div class="panel panel-default shadow-sm">
    <div class="panel-heading">وضعیت نقشه سایت</div>
    <div class="panel-body">
     <table class="table table-bordered table-striped">
      <tbody>
       <tr>
        <td width="200">آخرین بروزرسانی:</td>
        <td>
         @if ($lastUpdated)
          @php
           $gregorianDate = explode(' ', $lastUpdated)[0];
           [$year, $month, $day] = explode('-', $gregorianDate);
           $jalaliDate = \App\Helpers\JalaliHelper::gregorianToJalali($year, $month, $day);
           $persianMonths = [
               'فروردین',
               'اردیبهشت',
               'خرداد',
               'تیر',
               'مرداد',
               'شهریور',
               'مهر',
               'آبان',
               'آذر',
               'دی',
               'بهمن',
               'اسفند',
           ];
           echo "$jalaliDate[2] {$persianMonths[$jalaliDate[1] - 1]} $jalaliDate[0] " . substr($lastUpdated, 11, 5);
          @endphp
         @else
          هنوز بروزرسانی نشده
         @endif
        </td>
       </tr>
       <tr>
        <td>تعداد لینک ثبت‌شده:</td>
        <td>{{ $totalLinks }}</td>
       </tr>
      </tbody>
     </table>
    </div>
   </div>
  </div>
  <div class="col-md-8">
   <div class="panel panel-default shadow-sm">
    <div class="panel-heading">تنظیمات و عملیات</div>
    <div class="panel-body">
     <div class="row g-4">
      <div class="col-md-6">
       <div class="form-group">
        <label class="control-label fw-bold mb-2">اولویت پیش‌فرض</label>
        <input type="number" step="0.1" min="0" max="1" class="form-control input-shiny"
         wire:model="defaultPriority">
       </div>
      </div>
      <div class="col-md-6">
       <div class="form-group">
        <label class="control-label fw-bold mb-2">فرکانس پیش‌فرض</label>
        <select class="form-control input-shiny" wire:model="defaultChangefreq">
         <option value="always">همیشه</option>
         <option value="hourly">ساعتی</option>
         <option value="daily">روزانه</option>
         <option value="weekly">هفتگی</option>
         <option value="monthly">ماهانه</option>
         <option value="yearly">سالانه</option>
         <option value="never">هرگز</option>
        </select>
       </div>
      </div>
     </div>
     <div class="d-flex gap-2 mt-3">
      <button wire:click="updateSitemap" class="btn btn-gradient-warning flex-grow-1">
       <i class="fa fa-refresh px-1"></i> بروزرسانی نقشه سایت
      </button>
      <a href="{{ Storage::url('sitemap.xml') }}" target="_blank" class="btn btn-gradient-secondary flex-grow-1">
       <i class="fa fa-file px-1"></i> مشاهده نقشه سایت
      </a>
     </div>
    </div>
   </div>
  </div>
 </div>

 <!-- فرم افزودن -->
 <div class="wrapper-md mb-5">
  <div class="panel panel-default shadow-sm">
   <div class="panel-heading">افزودن لینک جدید</div>
   <div class="panel-body">
    <div class="row g-4">
     <div class="col-md-4">
      <div class="form-group">
       <label class="control-label fw-bold mb-2">URL</label>
       <input type="url" class="form-control input-shiny" wire:model="newUrl"
        placeholder="مثال: https://example.com/page">
       @if ($errors->has('newUrl'))
        <span class="text-danger d-block mt-1">{{ $errors->first('newUrl') }}</span>
       @endif
      </div>
     </div>
     <div class="col-md-2">
      <div class="form-group">
       <label class="control-label fw-bold mb-2">اولویت</label>
       <input type="number" step="0.1" min="0" max="1" class="form-control input-shiny"
        wire:model="newPriority">
       @if ($errors->has('newPriority'))
        <span class="text-danger d-block mt-1">{{ $errors->first('newPriority') }}</span>
       @endif
      </div>
     </div>
     <div class="col-md-3">
      <div class="form-group">
       <label class="control-label fw-bold mb-2">فرکانس تغییر</label>
       <select class="form-control input-shiny" wire:model="newChangefreq">
        <option value="always">همیشه</option>
        <option value="hourly">ساعتی</option>
        <option value="daily">روزانه</option>
        <option value="weekly">هفتگی</option>
        <option value="monthly">ماهانه</option>
        <option value="yearly">سالانه</option>
        <option value="never">هرگز</option>
       </select>
       @if ($errors->has('newChangefreq'))
        <span class="text-danger d-block mt-1">{{ $errors->first('newChangefreq') }}</span>
       @endif
      </div>
     </div>
     <div class="col-md-2">
      <div class="form-group">
       <label class="control-label fw-bold mb-2">آخرین تغییر</label>
       <input type="text" id="new-date-input" class="form-control input-shiny custom-datepicker"
        wire:model="newLastmod" placeholder="تاریخ را انتخاب کنید" readonly>
       @if ($errors->has('newLastmod'))
        <span class="text-danger d-block mt-1">{{ $errors->first('newLastmod') }}</span>
       @endif
      </div>
     </div>
     <div class="col-md-1 d-flex align-items-end">
      <button wire:click="addLink" class="btn btn-gradient-success w-100 py-2">افزودن</button>
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
       placeholder="جستجو در لینک‌ها...">
     </div>
    </div>
    <div class="col-md-6">
     <div class="d-flex gap-2 justify-content-end">
      <button wire:click="export" class="btn btn-gradient-secondary rounded-pill px-4">
       <i class="fas fa-download"></i> خروجی CSV
      </button>
      <button wire:click="deleteSelected" class="btn btn-gradient-danger rounded-pill px-4"
       @if (empty($selectedLinks)) disabled @endif>
       <i class="fas fa-trash"></i> حذف انتخاب‌شده‌ها
      </button>
     </div>
    </div>
   </div>
  </div>
 </div>

 <!-- لیست لینک‌ها -->
 <div class="container px-0">
  <div class="card shadow-sm">
   <div class="card-body">
    <div class="table-responsive text-nowrap">
     <table class="table table-bordered">
      <thead>
       <tr>
        <th><input type="checkbox" wire:model.live="selectAll" class="form-check-input"></th>
        <th>ردیف</th>
        <th>URL</th>
        <th>اولویت</th>
        <th>فرکانس تغییر</th>
        <th>آخرین تغییر</th>
        <th>وضعیت</th>
        <th>عملیات</th>
       </tr>
      </thead>
      <tbody>
       @forelse ($links as $index => $link)
        <tr>
         <td><input type="checkbox" wire:model.live="selectedLinks" value="{{ $link->id }}"
           class="form-check-input"></td>
         <td>{{ $links->firstItem() + $index }}</td>
         <td>
          @if ($editId === $link->id)
           <input type="url" class="form-control input-shiny" wire:model.live="editUrl">
           @if ($errors->has('editUrl'))
            <span class="text-danger d-block mt-1">{{ $errors->first('editUrl') }}</span>
           @endif
          @else
           {{ $link->url }}
          @endif
         </td>
         <td>
          @if ($editId === $link->id)
           <input type="number" step="0.1" min="0" max="1" class="form-control input-shiny"
            wire:model.live="editPriority">
           @if ($errors->has('editPriority'))
            <span class="text-danger d-block mt-1">{{ $errors->first('editPriority') }}</span>
           @endif
          @else
           {{ number_format($link->priority, 1) }}
          @endif
         </td>
         <td>
          @if ($editId === $link->id)
           <select class="form-control input-shiny" wire:model.live="editChangefreq">
            <option value="always">همیشه</option>
            <option value="hourly">ساعتی</option>
            <option value="daily">روزانه</option>
            <option value="weekly">هفتگی</option>
            <option value="monthly">ماهانه</option>
            <option value="yearly">سالانه</option>
            <option value="never">هرگز</option>
           </select>
           @if ($errors->has('editChangefreq'))
            <span class="text-danger d-block mt-1">{{ $errors->first('editChangefreq') }}</span>
           @endif
          @else
           {{ $link->changefreq }}
          @endif
         </td>
         <td>
          @if ($editId === $link->id)
           <input type="text" id="edit-date-input-{{ $link->id }}"
            class="form-control input-shiny custom-datepicker" wire:model.live="editLastmod"
            placeholder="تاریخ را انتخاب کنید" readonly>
           @if ($errors->has('editLastmod'))
            <span class="text-danger d-block mt-1">{{ $errors->first('editLastmod') }}</span>
           @endif
          @else
           @php
            if ($link->lastmod) {
                [$year, $month, $day] = explode('-', $link->lastmod);
                $jalaliDate = \App\Helpers\JalaliHelper::gregorianToJalali($year, $month, $day);
                $persianMonths = [
                    'فروردین',
                    'اردیبهشت',
                    'خرداد',
                    'تیر',
                    'مرداد',
                    'شهریور',
                    'مهر',
                    'آبان',
                    'آذر',
                    'دی',
                    'بهمن',
                    'اسفند',
                ];
                echo "$jalaliDate[2] {$persianMonths[$jalaliDate[1] - 1]} $jalaliDate[0]";
            } else {
                echo '-';
            }
           @endphp
          @endif
         </td>
         <td>
          <button wire:click="toggleStatus({{ $link->id }})"
           class="badge {{ $link->is_active ? 'bg-label-success' : 'bg-label-danger' }} border-0 cursor-pointer">
           {{ $link->is_active ? 'فعال' : 'غیرفعال' }}
          </button>
         </td>
         <td>
          @if ($editId === $link->id)
           <div class="d-flex gap-2">
            <button wire:click="updateLink" class="btn btn-gradient-success rounded-pill px-3">
             <i class="fas fa-check"></i>
            </button>
            <button wire:click="cancelEdit" class="btn btn-gradient-danger rounded-pill px-3">
             <i class="fas fa-times"></i>
            </button>
           </div>
          @else
           <div class="d-flex gap-2">
            <button wire:click="startEdit({{ $link->id }})"
             class="btn btn-gradient-warning rounded-full w-8 h-8 flex items-center justify-center">
             <i class="fas fa-edit"></i>
            </button>
            <button onclick="confirmDelete({{ $link->id }})"
             class="btn btn-gradient-danger rounded-full w-8 h-8 flex items-center justify-center">
             <i class="fas fa-trash"></i>
            </button>
           </div>
          @endif
         </td>
        </tr>
       @empty
        <tr>
         <td colspan="8" class="text-center py-5">
          <i class="fas fa-sitemap fs-1 text-muted mb-3"></i>
          <p class="text-muted fw-medium">هیچ لینکی یافت نشد.</p>
         </td>
        </tr>
       @endforelse
      </tbody>
     </table>
    </div>
    <div class="d-flex justify-content-between mt-4">
     <div class="text-muted">نمایش {{ $links->firstItem() }} تا {{ $links->lastItem() }} از
      {{ $links->total() }} ردیف</div>
     {{ $links->links() }}
    </div>
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
     text: 'لینک‌های انتخاب‌شده حذف خواهند شد و قابل بازگشت نیستند!',
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
    text: 'این لینک حذف خواهد شد و قابل بازگشت نیست!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#d1d5db',
    confirmButtonText: 'بله، حذف کن',
    cancelButtonText: 'خیر',
   }).then((result) => {
    if (result.isConfirmed) {
     @this.deleteLink(id);
    }
   });
  }
 </script>
</div>

<div class="container-fluid py-4" dir="rtl">
 <!-- هدر -->
 <header class="glass-header p-4 rounded-xl mb-5 shadow-lg animate__animated animate__fadeIn">
  <div class="d-flex align-items-center justify-content-start gap-3 flex-nowrap">
   <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"
    class="animate-pulse">
    <path d="M4 4h16v12H4z" />
    <path d="M4 8l8 4 8-4" />
   </svg>
   <h4 class="mb-0 fw-bold text-white tracking-tight">مدیریت خبرنامه</h4>
  </div>
 </header>

 <!-- ابزارها -->
 <div class="container-fluid px-0 mb-5">
  <div class="bg-light p-4 rounded-xl shadow-sm animate__animated animate__fadeInUp">
   <div class="row g-4 align-items-center">
    <div class="col-md-4 col-sm-12">
     <div class="input-group align-items-center">
      <span class="input-group-text bg-white border-0 rounded-start-xl flex-shrink-0"
       style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
       <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
        <path d="M12 5v14M5 12h14" />
       </svg>
      </span>
      <input type="email" class="form-control input-modern border-0 shadow-none flex-grow-1" wire:model="newEmail"
       placeholder="ایمیل جدید">
      <button wire:click="addMember"
       class="btn btn-gradient-success px-4 py-2 d-flex align-items-center gap-2 shadow-sm rounded-end-xl">
       <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
        <path d="M12 5v14M5 12h14" />
       </svg>
       افزودن
      </button>
     </div>
     @error('newEmail')
      <span class="text-danger d-block mt-2 text-sm">{{ $message }}</span>
     @enderror
    </div>
    <div class="col-md-4 col-sm-12">
     <div class="input-group align-items-center">
      <span class="input-group-text bg-white border-0 rounded-start-xl flex-shrink-0"
       style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
       <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
        <circle cx="11" cy="11" r="8" />
        <path d="M21 21l-4.35-4.35" />
       </svg>
      </span>
      <input type="text" class="form-control input-modern border-0 shadow-none rounded-end-xl flex-grow-1"
       wire:model.live="search" placeholder="جستجو در اعضا...">
     </div>
    </div>
    <div class="col-md-4 col-sm-12 d-flex justify-content-md-end justify-content-center">
     <button wire:click="deleteSelected"
      class="btn btn-gradient-danger rounded-xl px-4 py-3 d-flex align-items-center gap-2 shadow-sm"
      @if (empty($selectedMembers)) disabled @endif>
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

 <!-- لیست اعضا -->
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
        <th class="text-center align-middle py-3">ایمیل</th>
        <th class="text-center align-middle py-3">وضعیت</th>
        <th class="text-center align-middle py-3">عملیات</th>
       </tr>
      </thead>
      <tbody>
       @forelse ($members as $index => $member)
        <tr class="animate__animated animate__fadeIn">
         <td class="align-middle text-center"><input type="checkbox" wire:model.live="selectedMembers"
           value="{{ $member->id }}" class="py-2"></td>
         <td class="align-middle text-center">{{ $members->firstItem() + $index }}</td>
         <td class="align-middle text-center">
          @if ($editId === $member->id)
           <input type="email" class="form-control input-modern border-0 shadow-none" wire:model.live="editEmail">
          @else
           {{ $member->email }}
          @endif
         </td>
         <td class="align-middle text-center">
          <button wire:click="toggleStatus({{ $member->id }})"
           class="badge {{ $member->is_active ? 'bg-success' : 'bg-danger' }} border-0 cursor-pointer text-white py-2 px-3 rounded-lg">
           {{ $member->is_active ? 'فعال' : 'غیرفعال' }}
          </button>
         </td>
         <td class="align-middle text-center">
          @if ($editId === $member->id)
           <div class="d-flex gap-3 justify-content-center flex-wrap">
            <button wire:click="updateMember"
             class="btn btn-gradient-success rounded-xl px-3 py-2 d-flex align-items-center gap-2 shadow-sm">
             <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
              <path d="M20 6L9 17l-5-5" />
             </svg>
             تأیید
            </button>
            <button wire:click="cancelEdit"
             class="btn btn-gradient-danger rounded-xl px-3 py-2 d-flex align-items-center gap-2 shadow-sm">
             <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white"
              stroke-width="2">
              <path d="M18 6L6 18M6 6l12 12" />
             </svg>
             لغو
            </button>
           </div>
          @else
           <div class="d-flex gap-3 justify-content-center flex-wrap">
            <button wire:click="startEdit({{ $member->id }})"
             class="btn btn-gradient-warning rounded-xl px-3 py-2 d-flex align-items-center gap-2 shadow-sm">
             <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white"
              stroke-width="2">
              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
             </svg>
            </button>
            <button onclick="confirmDelete({{ $member->id }})"
             class="btn btn-gradient-danger rounded-xl px-3 py-2 d-flex align-items-center gap-2 shadow-sm">
             <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white"
              stroke-width="2">
              <path d="M3 6h18" />
              <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
             </svg>
            </button>
           </div>
          @endif
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
           <p class="text-muted fw-medium text-lg">هیچ عضوی یافت نشد.</p>
          </div>
         </td>
        </tr>
       @endforelse
      </tbody>
     </table>
    </div>
    <div class="d-flex justify-content-between mt-4 px-4 flex-wrap gap-3">
     <div class="text-muted">نمایش {{ $members->firstItem() }} تا {{ $members->lastItem() }} از
      {{ $members->total() }} ردیف</div>
     {{ $members->links() }}
    </div>
   </div>
  </div>
 </div>
 <link rel="stylesheet" href="{{ asset('admin-assets/css/panel/tools/news-letter/news-letter.css') }}">
 <!-- استایل‌ها -->


 <!-- اسکریپت‌ها (بدون تغییر) -->
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
     text: 'اعضای انتخاب‌شده از خبرنامه حذف خواهند شد و قابل بازگشت نیستند!',
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
    text: 'این عضو از خبرنامه حذف خواهد شد و قابل بازگشت نیست!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#d1d5db',
    confirmButtonText: 'بله، حذف کن',
    cancelButtonText: 'خیر',
   }).then((result) => {
    if (result.isConfirmed) {
     @this.deleteMember(id);
    }
   });
  }
 </script>
</div>

<div class="container-fluid py-3">
 <!-- هدر -->
 <header class="glass-header p-3 rounded-xl mb-4 shadow-md">
  <div class="d-flex align-items-center justify-content-between gap-3">
   <div class="d-flex align-items-center gap-2">
    <img src="{{ asset('admin-assets/icons/folder-check-svgrepo-com.svg') }}" class="fs-4 text-white animate-bounce"
     alt="">
    <h4 class="mb-0 fw-semibold text-white">مدیریت فایل‌ها</h4>
   </div>
   <div class="d-flex align-items-center">
    <nav aria-label="breadcrumb">
     <ol class="breadcrumb mb-0 text-gray-100">
      @foreach (explode('/', $currentPath) as $segment)
       @if ($segment)
        <li class="breadcrumb-item">{{ $segment }}</li>
       @endif
      @endforeach
      <li class="breadcrumb-item"><a href="#" wire:click="changePath('')"
        class="text-gray-100 hover:text-white transition-colors">Root</a></li>
     </ol>
    </nav>
   </div>
  </div>
 </header>
 <!-- ابزارها -->
 <div class="container px-0 mb-4">
  <div class="bg-white p-3 rounded-xl shadow-sm border border-gray-100">
   <div class="row g-3">
    <div class="col-md-4">
     <div class="input-group align-items-center">
      <span class="input-group-text bg-transparent border-0 pe-2"><img
        src="{{ asset('admin-assets/icons/folder-add-svgrepo-com.svg') }}" class="fs-4 text-primary animate-bounce"
        alt=""></span>
      <input type="text" class="form-control rounded-lg shadow-sm" wire:model="newFolderName"
       placeholder="نام پوشه جدید">
      <button wire:click="createFolder" class="btn btn-gradient-success px-3 rounded-lg">
       <i class="fas fa-plus me-1"></i> ایجاد
      </button>
     </div>
     @error('newFolderName')
      <span class="text-danger text-sm d-block mt-1">{{ $message }}</span>
     @enderror
    </div>
    <div class="col-md-4">
     <div class="input-group align-items-center">
      <span class="input-group-text bg-transparent border-0 pe-2"><img
        src="{{ asset('admin-assets/icons/search-folder-svgrepo-com.svg') }}" class="fs-4 text-primary animate-bounce"
        alt=""></span>
      <input type="text" class="form-control rounded-lg shadow-sm" wire:model.live="search"
       placeholder="جستجو در فایل‌ها و پوشه‌ها">
     </div>
    </div>
    <div class="col-md-4">
     <input type="file" class="form-control rounded-lg shadow-sm" wire:model="filesToUpload" multiple>
     @if ($filesToUpload)
      <div class="progress mt-2 rounded-full" style="height: 6px;">
       <div class="progress-bar bg-gradient-primary animate-pulse" role="progressbar"
        style="width: 100%; border-radius: 9999px;"></div>
      </div>
     @endif
     @error('filesToUpload.*')
      <span class="text-danger text-sm d-block mt-1">{{ $message }}</span>
     @enderror
    </div>
   </div>
  </div>
 </div>
 <!-- لیست فایل‌ها و پوشه‌ها -->
 <div class="container px-0">
  <div class="row g-3">
   @if ($currentPath)
    <div class="col-md-3 col-sm-6">
     <div wire:click="goBack"
      class="card comment-card border-0 rounded-xl shadow-sm bg-gradient-card h-100 cursor-pointer">
      <div class="card-body p-3 d-flex flex-column align-items-center gap-2 text-center">
       <img src="{{ asset('admin-assets/icons/arrow-right-square-svgrepo-com.svg') }}"
        class="fs-4 text-primary animate-bounce" alt="">
       <h6 class="fw-medium text-gray-700">بازگشت</h6>
      </div>
     </div>
    </div>
   @endif
   @forelse ($items as $item)
    <div class="col-md-3 col-sm-6">
     <div class="card comment-card border-0 rounded-xl shadow-sm bg-gradient-card h-100">
      <div class="card-body p-3 d-flex flex-column align-items-center  text-center">
       @if ($item['type'] === 'folder')
        <div wire:click="changePath('{{ $item['path'] }}')" class="cursor-pointer">
         <img src="{{ asset('admin-assets/icons/folder-with-files-svgrepo-com.svg') }}"
          class="fs-4 text-primary animate-bounce" alt="">
        </div>
        @if ($renamingPath === $item['path'])
         <input type="text" class="form-control rounded-lg shadow-sm" wire:model.live="newName"
          placeholder="نام جدید">
         <div class="d-flex gap-2 mt-2">
          <button wire:click="renameItem"
           class="btn btn-gradient-success rounded-full w-9 h-9 d-flex align-items-center justify-content-center p-0">
           <img src="{{ asset('admin-assets/icons/check-svgrepo-com.svg') }}" class="w-5 h-5 object-contain"
            alt="">
          </button>
          <button wire:click="cancelRename"
           class="btn btn-gradient-danger rounded-full w-9 h-9 d-flex align-items-center justify-content-center p-0">
           <img src="{{ asset('admin-assets/icons/times-square-svgrepo-com.svg') }}" class="w-5 h-5 object-contain"
            alt="">
          </button>
         </div>
        @else
         <h6 class="fw-medium text-gray-700 text-ellipsis" style="max-width: 100%;">{{ $item['name'] }}</h6>
         <div class="d-flex gap-2 mt-2">
          <button wire:click="startRename('{{ $item['path'] }}')"
           class="btn btn-gradient-warning rounded-full w-9 h-9 d-flex align-items-center justify-content-center p-0">
           <img src="{{ asset('admin-assets/icons/edit.svg') }}" class="w-5 h-5 object-contain" alt="">
          </button>
          <button onclick="confirmDelete('{{ $item['path'] }}')"
           class="btn btn-gradient-danger rounded-full w-9 h-9 d-flex align-items-center justify-content-center p-0">
           <img src="{{ asset('admin-assets/icons/trash.svg') }}" class="w-5 h-5 object-contain" alt="">
          </button>
         </div>
        @endif
       @else
        @if ($item['isImage'])
         <img src="{{ $item['url'] }}" class="img-thumbnail rounded-lg cursor-pointer"
          style="max-height: 100px; object-fit: cover;" wire:click="selectImage('{{ $item['url'] }}')">
        @elseif ($item['isText'])
         <div wire:click="editFile('{{ $item['path'] }}')" class="cursor-pointer">
          <img src="{{ asset('admin-assets/icons/file-svgrepo-com.svg') }}" class="fs-4 text-primary animate-bounce"
           alt="">
         </div>
        @else
         <div>
          <img src="{{ asset('admin-assets/icons/file-svgrepo-com.svg') }}" class="fs-4 text-primary animate-bounce"
           alt="">
         </div>
        @endif
        @if ($renamingPath === $item['path'])
         <input type="text" class="form-control rounded-lg shadow-sm" wire:model.live="newName"
          placeholder="نام جدید">
         <div class="d-flex gap-2 mt-2">
          <button wire:click="renameItem"
           class="btn btn-gradient-success rounded-full w-9 h-9 d-flex align-items-center justify-content-center p-0">
           <img src="{{ asset('admin-assets/icons/check-svgrepo-com.svg') }}" class="w-5 h-5 object-contain"
            alt="">
          </button>
          <button wire:click="cancelRename"
           class="btn btn-gradient-danger rounded-full w-9 h-9 d-flex align-items-center justify-content-center p-0">
           <img src="{{ asset('admin-assets/icons/times-square-svgrepo-com.svg') }}" class="w-5 h-5 object-contain"
            alt="">
          </button>
         </div>
        @else
         <h6 class="fw-medium text-gray-700 text-ellipsis" style="max-width: 100%;">{{ $item['name'] }}</h6>
         <div class="d-flex gap-2 mt-2">
          <a href="{{ $item['url'] }}" download
           class="btn btn-gradient-secondary rounded-full w-9 h-9 d-flex align-items-center justify-content-center p-0">
           <img src="{{ asset('admin-assets/icons/download-minimalistic-svgrepo-com.svg') }}"
            class="w-5 h-5 object-contain" alt="">
          </a>
          <button wire:click="startRename('{{ $item['path'] }}')"
           class="btn btn-gradient-warning rounded-full w-9 h-9 d-flex align-items-center justify-content-center p-0">
           <img src="{{ asset('admin-assets/icons/edit.svg') }}" class="w-5 h-5 object-contain" alt="">
          </button>
          <button onclick="confirmDelete('{{ $item['path'] }}')"
           class="btn btn-gradient-danger rounded-full w-9 h-9 d-flex align-items-center justify-content-center p-0">
           <img src="{{ asset('admin-assets/icons/trash.svg') }}" class="w-5 h-5 object-contain" alt="">
          </button>
         </div>
        @endif
       @endif
      </div>
     </div>
    </div>
   @empty
    <div class="col-12">
     <div class="text-center py-4 d-flex justify-content-center align-items-center flex-column">
      <img src="{{ asset('admin-assets/icons/folder-open-svgrepo-com.svg') }}"
       class="fs-4 text-gray-500 animate-bounce" alt="">
      <p class="text-gray-500 fw-medium">هیچ فایل یا پوشه‌ای یافت نشد.</p>
     </div>
    </div>
   @endforelse
  </div>
 </div>
 <!-- پیش‌نمایش تصویر -->
 @if ($selectedImage)
  <div class="fixed inset-0 bg-black bg-opacity-80 d-flex align-items-center justify-content-center z-50">
   <div class="relative max-w-3xl w-full p-3">
    <img src="{{ $selectedImage }}" class="w-full h-auto rounded-xl shadow-xl"
     style="max-height: 85vh; object-fit: contain;">
    <button wire:click="closePreview"
     class="absolute top-3 right-3 btn btn-gradient-danger rounded-full w-9 h-9 d-flex align-items-center justify-content-center p-0">
     <img src="{{ asset('admin-assets/icons/times-square-svgrepo-com.svg') }}" class="w-5 h-5 object-contain"
      alt="">
    </button>
   </div>
  </div>
 @endif
 <!-- ویرایشگر فایل متنی -->
 @if ($editingFile)
  <div class="fixed inset-0 bg-black bg-opacity-80 d-flex align-items-center justify-content-center z-50">
   <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl p-4">
    <h5 class="fw-semibold mb-3">ویرایش فایل: {{ basename($editingFile) }}</h5>
    <textarea wire:model.live="fileContent" class="form-control rounded-lg shadow-sm"
     style="min-height: 250px; resize: vertical;" placeholder="محتوای فایل..."></textarea>
    <div class="d-flex gap-3 mt-3 justify-content-end">
     <button wire:click="saveFile" class="btn btn-gradient-success px-3 rounded-lg">ذخیره</button>
     <button wire:click="closeEditor" class="btn btn-gradient-danger px-3 rounded-lg">بستن</button>
    </div>
   </div>
  </div>
 @endif
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

  .bg-gradient-card {
   background: linear-gradient(145deg, #ffffff, #f8fafc);
   border: 1px solid #e2e8f0;
  }

  .comment-card {
   transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  .comment-card:hover {
   transform: translateY(-4px);
   box-shadow: 0 8px 16px rgba(0, 0, 0, 0.08);
  }

  .btn-gradient-primary {
   background: linear-gradient(90deg, #4f46e5, #7c3aed);
   border: none;
   color: white;
   box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
   transition: all 0.2s ease;
  }

  .btn-gradient-primary:hover {
   background: linear-gradient(90deg, #4338ca, #6b21a8);
   transform: translateY(-1px);
   box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
  }

  .btn-gradient-danger {
   background: linear-gradient(90deg, #f87171, #fca5a5);
   border: none;
   color: white;
   box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
   transition: all 0.2s ease;
  }

  .btn-gradient-danger:hover {
   background: linear-gradient(90deg, #ef4444, #f87171);
   transform: translateY(-1px);
   box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
  }

  .btn-gradient-success {
   background: linear-gradient(90deg, #10b981, #34d399);
   border: none;
   color: white;
   box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
   transition: all 0.2s ease;
  }

  .btn-gradient-success:hover {
   background: linear-gradient(90deg, #059669, #10b981);
   transform: translateY(-1px);
   box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
  }

  .btn-gradient-warning {
   background: linear-gradient(90deg, #f59e0b, #fbbf24);
   border: none;
   color: white;
   box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
   transition: all 0.2s ease;
  }

  .btn-gradient-warning:hover {
   background: linear-gradient(90deg, #d97706, #f59e0b);
   transform: translateY(-1px);
   box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
  }

  .btn-gradient-secondary {
   background: linear-gradient(90deg, #6b7280, #9ca3af);
   border: none;
   color: white;
   box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
   transition: all 0.2s ease;
  }

  .btn-gradient-secondary:hover {
   background: linear-gradient(90deg, #4b5563, #6b7280);
   transform: translateY(-1px);
   box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
  }

  .form-control {
   border-radius: 8px;
   border: 1px solid #d1d5db;
   transition: all 0.2s ease;
  }

  .form-control:focus {
   border-color: #4f46e5;
   box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
  }

  .text-ellipsis {
   overflow: hidden;
   text-overflow: ellipsis;
   white-space: nowrap;
  }

  .progress-bar.animate-pulse {
   animation: pulse 1.2s infinite;
  }

  @keyframes pulse {
   0% {
    opacity: 1;
   }

   50% {
    opacity: 0.6;
   }

   100% {
    opacity: 1;
   }
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
    transform: translateY(-4px);
   }
  }

  .breadcrumb-item+.breadcrumb-item::before {
   color: #333;
  }

  .rounded-full {
   border-radius: 9999px;
   padding: 0;
  }

  .cursor-pointer {
   cursor: pointer;
  }

  .w-9 {
   width: 2.25rem;
  }

  .h-9 {
   height: 2.25rem;
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
    else toastr.info(message, '', toastOptions);
   });

   Livewire.on('confirmCloseEditor', () => {
    Swal.fire({
     title: 'آیا می‌خواهید تغییرات را ذخیره کنید؟',
     icon: 'question',
     showCancelButton: true,
     confirmButtonText: 'بله، ذخیره کن',
     cancelButtonText: 'خیر، ببند',
     confirmButtonColor: '#10b981',
     cancelButtonColor: '#ef4444',
    }).then((result) => {
     if (result.isConfirmed) {
      @this.confirmClose(true);
     } else {
      @this.confirmClose(false);
     }
    });
   });
  });

  function confirmDelete(path) {
   Swal.fire({
    title: 'آیا مطمئن هستید؟',
    text: 'این آیتم حذف خواهد شد و قابل بازگشت نیست!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#d1d5db',
    confirmButtonText: 'بله، حذف کن',
    cancelButtonText: 'خیر',
   }).then((result) => {
    if (result.isConfirmed) {
     @this.deleteItem(path);
    }
   });
  }
 </script>
</div>

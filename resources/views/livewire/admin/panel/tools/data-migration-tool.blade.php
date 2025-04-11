<div class="container-fluid py-3" dir="rtl">
  <!-- هدر -->
  <header class="glass-header p-4 rounded-2xl mb-5 shadow-xl">
    <div class="d-flex align-items-center justify-content-between gap-4 flex-wrap">
      <div class="d-flex align-items-center gap-3">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"
          class="animate-bounce">
          <path d="M5 12h14M12 5v14" />
        </svg>
        <h4 class="mb-0 fw-semibold text-white tracking-tight">ابزار انتقال داده‌ها</h4>
      </div>
      <div class="text-white text-sm fw-medium bg-white/10 px-3 py-1 rounded-full hover:bg-white/20 transition-all">
        انتقال داده‌ها از CSV/Excel به دیتابیس
      </div>
    </div>
  </header>

  <!-- فرم انتخاب فایل و جدول -->
  <div class="bg-white p-4 rounded-2xl shadow-lg mb-5 hover:shadow-xl transition-shadow">
    <div class="row g-4">
      <div class="col-md-6">
        <div class="d-flex flex-column gap-3">
          <label class="form-label fw-semibold text-gray-700">فایل داده‌ها (CSV یا Excel)</label>
          <div class="input-group">
            <span class="input-group-text bg-transparent border-0 pe-2">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2"
                class="animate-pulse">
                <path d="M5 12h14M12 5v14" />
              </svg>
            </span>
            <input type="file" class="form-control input-modern border-0 shadow-sm" wire:model.live="file"
              accept=".csv,.xlsx,.xls">
          </div>
          @error('file')
            <span class="text-danger text-sm mt-1">{{ $message }}</span>
          @enderror
          <!-- پروگرس بار -->
          <div class="upload-progress mt-2"
            style="display: {{ $isUploading || $uploadProgress > 0 ? 'block' : 'none' }};direction:ltr;">
            <div class="progress-bar bg-primary text-white text-center"
              style="width: {{ $uploadProgress }}%; transition: width 0.3s ease-in-out;">
              {{ $uploadProgress }}%
            </div>
          </div>
          <!-- اندیکاتور لودینگ -->
          <div class="loading-indicator mt-2" style="display: {{ $isUploading ? 'flex' : 'none' }};">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden"></span>
            </div>
            <span class="ms-2 text-gray-700"></span>
          </div>
        </div>
      </div>

      <style>
        .upload-progress {
          background-color: #e9ecef;
          border-radius: 0.25rem;
          overflow: hidden;
        }

        .progress-bar {
          height: 1.5rem;
          line-height: 1.5rem;
        }

        .loading-indicator {
          display: flex;
          align-items: center;
          gap: 0.5rem;
        }

        .spinner-border {
          width: 1.5rem;
          height: 1.5rem;
          border-width: 0.2rem;
        }
      </style>
      <div class="col-md-6">
        <div class="d-flex flex-column gap-3">
          <label class="form-label fw-semibold text-gray-700">جدول مقصد (دیتابیس فعلی)</label>
          <div class="d-flex" wire:ignore>
            <span class="input-group-text bg-transparent border-0 pe-2">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2"
                class="animate-pulse">
                <path d="M5 12h14M12 5v14" />
              </svg>
            </span>
            <select class="form-control input-modern border-0 shadow-sm select2-table" id="newTableSelect"
              {{ $uploadProgress < 100 ? 'disabled' : '' }}>
              <option value="">انتخاب جدول مقصد</option>
              @forelse ($tables as $table)
                <option value="{{ $table }}" {{ $newTable === $table ? 'selected' : '' }}>{{ $table }}
                </option>
              @empty
                <option value="">هیچ جدولی یافت نشد</option>
              @endforelse
            </select>
          </div>
          @error('newTable')
            <span class="text-danger text-sm mt-1">{{ $message }}</span>
          @enderror
          <!-- اندیکاتور لودینگ -->
          <div wire:loading wire:target="newTable" class="loading-indicator mt-2" style="display: none;">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden"></span>
            </div>
            <span class="ms-2 text-gray-700"></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- نگاشت فیلدها -->
  @if (!empty($oldFields) && !empty($newFields))
    <div class="row g-4 mb-5">
      <div class="col-md-6">
        <div class="card border-0 rounded-2xl shadow-lg hover:shadow-xl transition-all">
          <div
            class="card-header glass-header text-white fw-semibold d-flex justify-content-between align-items-center px-4 py-3">
            <span>فیلدهای فایل ورودی</span>
            <span class="text-xs bg-white/10 px-2 py-1 rounded-full">{{ $file ? basename($file) : 'فایل' }}</span>
          </div>
          <div class="card-body p-4">
            <input type="text" class="form-control input-modern mb-4 shadow-sm" wire:model.live="searchOld"
              placeholder="جستجو در فیلدها...">
            <ul class="list-group">
              @foreach (array_filter($oldFields, fn($field) => str_contains(strtolower($field), strtolower($searchOld))) as $field)
                <li
                  class="list-group-item d-flex justify-content-between align-items-center py-3 hover:bg-gray-100 transition-colors">
                  <span class="text-gray-700 fw-medium">{{ $field }}</span>
                  <select class="form-select input-modern w-50 shadow-sm"
                    wire:model.live="fieldMapping.{{ $field }}">
                    <option value="">فیلد مقصد</option>
                    @foreach ($newFields as $newField)
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
        <div class="card border-0 rounded-2xl shadow-lg hover:shadow-xl transition-all">
          <div class="card-header glass-header text-white fw-semibold px-4 py-3">فیلدهای جدول مقصد: {{ $newTable }}
          </div>
          <div class="card-body p-4">
            <input type="text" class="form-control input-modern mb-4 shadow-sm" wire:model.live="searchNew"
              placeholder="جستجو در فیلدها...">
            <ul class="list-group">
              @foreach (array_filter($newFields, fn($field) => str_contains(strtolower($field), strtolower($searchNew))) as $field)
                <li class="list-group-item py-3 text-gray-700 fw-medium hover:bg-gray-100 transition-colors">
                  {{ $field }}</li>
              @endforeach
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- دکمه‌ها و پروگرس بار -->
    <!-- ... بقیه کد Blade همونه، فقط این بخش رو تغییر می‌دم ... -->

    <div class="d-flex justify-content-center gap-4 mb-5">
      <button wire:click="migrateData" class="btn btn-gradient px-5 py-2 shadow-md hover:shadow-xl transition-all"
        wire:loading.attr="disabled">
        <span wire:loading.remove>شروع انتقال</span>
        <span wire:loading>در حال انتقال...</span>
      </button>
      @if ($logFilePath)
        <a href="{{ route('admin.tools.data-migration.download-log', ['filename' => basename($logFilePath)]) }}"
          class="btn btn-outline-secondary px-5 py-2 shadow-md hover:shadow-xl transition-all">
          دانلود لاگ
        </a>
      @endif
    </div>

    <script>
      document.addEventListener('livewire:init', () => {


        const migrateButton = document.querySelector('[wire\\:click="migrateData"]');
        if (migrateButton) {
          migrateButton.addEventListener('click', () => {
            console.log('Migrate button clicked');
          });
        } else {
          console.error('Migrate button not found in DOM');
        }
      });
    </script>
    <div class="progress-container mb-5" style="display: {{ $isMigrating ? 'block' : 'none' }};">
      <div class="progress-bar" style="width: {{ $migrationProgress }}%;">{{ $migrationProgress }}%</div>
    </div>
  @endif

  <!-- استایل‌ها -->
  <style>
    .glass-header {
      background: linear-gradient(135deg, rgba(55, 65, 81, 0.95), rgba(75, 85, 99, 0.85));
      backdrop-filter: blur(15px);
      border: 1px solid rgba(255, 255, 255, 0.1);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
      transition: all 0.4s ease;
    }

    .glass-header:hover {
      box-shadow: 0 12px 35px rgba(0, 0, 0, 0.2);
      transform: translateY(-3px);
    }

    .bg-white {
      background: #ffffff;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
    }

    .input-modern {
      border: none;
      border-radius: 10px;
      background: #f9fafb;
      padding: 12px 16px;
      font-size: 0.95rem;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
    }

    .input-modern:hover {
      background: #f3f4f6;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .input-modern:focus {
      background: #ffffff;
      box-shadow: 0 0 0 4px rgba(55, 65, 81, 0.15);
      outline: none;
    }

    .btn-gradient {
      background: linear-gradient(90deg, #374151, #4b5563);
      border: none;
      color: white;
      font-weight: 600;
      border-radius: 10px;
      transition: all 0.3s ease;
    }

    .btn-gradient:hover {
      background: linear-gradient(90deg, #1f2937, #374151);
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(55, 65, 81, 0.25);
    }

    .btn-outline-secondary {
      border: 1px solid #6b7280;
      color: #6b7280;
      border-radius: 10px;
      transition: all 0.3s ease;
    }

    .btn-outline-secondary:hover {
      background: #6b7280;
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 10px 20px rgba(55, 65, 81, 0.25);
    }

    .upload-progress,
    .progress-container {
      width: 100%;
      height: 8px;
      background: #e5e7eb;
      border-radius: 9999px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .progress-bar {
      height: 100%;
      background: linear-gradient(90deg, #374151, #4b5563);
      color: white;
      text-align: center;
      font-size: 0.75rem;
      font-weight: 600;
      transition: width 0.6s ease-in-out;
    }

    .list-group-item {
      border: none;
      padding: 12px 0;
      border-bottom: 1px solid #f3f4f6;
    }

    .list-group-item:last-child {
      border-bottom: none;
    }

    .select2-container {
      width: 100% !important;
    }

    .select2-selection {
      border: none !important;
      border-radius: 10px !important;
      background: #f9fafb !important;
      padding: 8px 12px !important;
      font-size: 0.95rem !important;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05) !important;
      height: auto !important;
    }

    .select2-selection:hover {
      background: #f3f4f6 !important;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1) !important;
    }

    .select2-selection:focus {
      background: #ffffff !important;
      box-shadow: 0 0 0 4px rgba(55, 65, 81, 0.15) !important;
      outline: none !important;
    }

    .animate-bounce {
      animation: bounce 1.2s infinite ease-in-out;
    }

    @keyframes bounce {

      0%,
      100% {
        transform: translateY(0);
      }

      50% {
        transform: translateY(-6px);
      }
    }

    .animate-pulse {
      animation: pulse 1.8s infinite ease-in-out;
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

    @media (max-width: 991px) {
      .glass-header {
        padding: 1.5rem;
      }

      .bg-white {
        padding: 1.5rem;
      }

      .btn-gradient,
      .btn-outline-secondary {
        padding: 0.75rem 2rem;
        font-size: 0.9rem;
      }

      .input-modern {
        font-size: 0.9rem;
      }
    }

    @media (max-width: 767px) {
      .glass-header {
        padding: 1rem;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
      }

      .bg-white {
        padding: 1rem;
      }

      .card-body {
        padding: 1rem;
      }

      .btn-gradient,
      .btn-outline-secondary {
        width: 100%;
        padding: 0.75rem;
        font-size: 0.875rem;
      }

      .input-modern {
        font-size: 0.875rem;
        padding: 10px 12px;
      }

      .list-group-item {
        font-size: 0.875rem;
        padding: 10px 0;
      }

      .form-select.w-50 {
        width: 100% !important;
        margin-top: 0.5rem;
      }

      .form-label {
        font-size: 0.875rem;
      }
    }
  </style>
  <style>
    .loading-indicator {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .spinner-border {
      width: 1.5rem;
      height: 1.5rem;
      border-width: 0.2rem;
    }
  </style>

  <script>
    document.addEventListener('livewire:init', () => {
      const newTableSelect = $('#newTableSelect').select2({
        placeholder: 'انتخاب جدول مقصد',
        dir: 'rtl',
        width: '100%',
        minimumResultsForSearch: 10,
        dropdownAutoWidth: true
      }).val(@json($newTable)).trigger('change');

      if (@json($uploadProgress) < 100) {
        newTableSelect.prop('disabled', true);
      }

      $('#newTableSelect').on('change', function() {
        @this.set('newTable', $(this).val());
      });

      Livewire.on('toast', ({
        message,
        type
      }) => {
        toastr[type || 'info'](message, null, {
          timeOut: 4000,
          positionClass: 'toast-top-right',
          progressBar: true
        });
      });

      Livewire.on('upload-progress', ({
        progress
      }) => {
        console.log('Upload progress:', progress);
        const container = document.querySelector('.upload-progress');
        const bar = container.querySelector('.progress-bar');
        if (container && bar) {
          container.style.display = 'block';
          bar.style.width = `${progress}%`;
          bar.textContent = `${progress}%`;

          if (progress >= 100) {
            $('#newTableSelect').prop('disabled', false).select2();
            setTimeout(() => {
              container.style.display = 'none';
            }, 1500);
          } else {
            $('#newTableSelect').prop('disabled', true).select2();
          }
        } else {
          console.error('Upload progress container or bar not found');
        }
      });

      const fileInput = document.querySelector('input[wire\\:model\\.live="file"]');
      if (fileInput) {
        fileInput.addEventListener('livewire-upload-start', () => {
          console.log('Upload started');
          @this.isUploading = true;
          @this.uploadProgress = 0;
          @this.dispatch('upload-progress', {
            progress: 0
          });
        });

        fileInput.addEventListener('livewire-upload-progress', (event) => {
          const progress = Math.min(event.detail.progress, 90);
          console.log('Upload progress event:', progress);
          @this.uploadProgress = progress;
          @this.dispatch('upload-progress', {
            progress
          });
        });

        fileInput.addEventListener('livewire-upload-finish', () => {
          console.log('Upload finished');
          @this.uploadProgress = 100;
          @this.dispatch('upload-progress', {
            progress: 100
          });
        });

        fileInput.addEventListener('livewire-upload-error', (event) => {
          console.error('Upload error:', event.detail.message);
          @this.isUploading = false;
          @this.uploadProgress = 0;
          @this.dispatch('upload-progress', {
            progress: 0
          });
          @this.dispatch('toast', {
            message: 'خطا در آپلود فایل',
            type: 'error'
          });
        });
      } else {
        console.error('File input not found - Check selector');
      }

      Livewire.on('migration-progress', ({
        progress
      }) => {
        const container = document.querySelector('.progress-container');
        const bar = container.querySelector('.progress-bar');
        if (container && bar) {
          container.style.display = 'block';
          bar.style.width = `${progress}%`;
          bar.textContent = `${progress}%`;
        }
      });

      Livewire.on('start-loading', () => {
        const loadingIndicator = document.querySelector('.loading-indicator');
        if (loadingIndicator) {
          loadingIndicator.style.display = 'flex';
        }
      });

      Livewire.on('stop-loading', () => {
        const loadingIndicator = document.querySelector('.loading-indicator');
        if (loadingIndicator) {
          loadingIndicator.style.display = 'none';
        }
      });
    });
  </script>
</div>

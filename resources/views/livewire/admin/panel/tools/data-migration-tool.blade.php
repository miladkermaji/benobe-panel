<div class="container-fluid py-3" dir="rtl">
  <!-- هدر -->
  <header class="glass-header p-4 rounded-2xl mb-5">
    <div class="d-flex align-items-center justify-content-between gap-4 flex-wrap">
      <div class="d-flex align-items-center gap-3">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"
          class="custom-animate-bounce">
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
  <div class="bg-white p-4 rounded-2xl mb-5">
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
            <input type="file" class="form-control input-modern" wire:model.live="file" accept=".csv,.xlsx,.xls">
          </div>
          @error('file')
            <span class="text-danger text-sm mt-1">{{ $message }}</span>
          @enderror
          <!-- پروگرس بار -->
          <div class="upload-progress mt-2"
            style="display: {{ $isUploading || $uploadProgress > 0 ? 'block' : 'none' }};direction:ltr;">
            <div class="progress-bar text-white text-center" style="width: {{ $uploadProgress }}%;">
              {{ $uploadProgress }}%
            </div>
          </div>
          <!-- اندیکاتور لودینگ -->
          <div class="loading-indicator mt-2" style="display: {{ $isUploading ? 'flex' : 'none' }};">
            <div class="spinner-border" role="status">
              <span class="visually-hidden"></span>
            </div>
            <span class="ms-2 text-gray-700"></span>
          </div>
        </div>
      </div>

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
            <select class="form-control input-modern select2-table" id="newTableSelect"
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
            <div class="spinner-border" role="status">
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
        <div class="card">
          <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
            <span>فیلدهای فایل ورودی</span>
            <span class="text-xs bg-white/10 px-2 py-1 rounded-full">{{ $file ? basename($file) : 'فایل' }}</span>
          </div>
          <div class="card-body">
            <input type="text" class="form-control input-modern mb-4" wire:model.live="searchOld"
              placeholder="جستجو در فیلدها...">
            <ul class="list-group">
              @foreach (array_filter($oldFields, fn($field) => str_contains(strtolower($field), strtolower($searchOld))) as $field)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <span class="text-gray-700 fw-medium">{{ $field }}</span>
                  <select class="form-select input-modern w-50" wire:model.live="fieldMapping.{{ $field }}">
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
        <div class="card">
          <div class="card-header fw-semibold">فیلدهای جدول مقصد: {{ $newTable }}</div>
          <div class="card-body">
            <input type="text" class="form-control input-modern mb-4" wire:model.live="searchNew"
              placeholder="جستجو در فیلدها...">
            <ul class="list-group">
              @foreach (array_filter($newFields, fn($field) => str_contains(strtolower($field), strtolower($searchNew))) as $field)
                <li class="list-group-item text-gray-700 fw-medium">{{ $field }}</li>
              @endforeach
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- دکمه‌ها و پروگرس بار -->
    <div class="d-flex justify-content-center gap-4 mb-5">
      <button wire:click="migrateData" class="btn btn-gradient px-5 py-2" wire:loading.attr="disabled">
        <span wire:loading.remove>شروع انتقال</span>
        <span wire:loading>در حال انتقال...</span>
      </button>
      @if ($logFilePath)
        <a href="{{ route('admin.tools.data-migration.download-log', ['filename' => basename($logFilePath)]) }}"
          class="btn btn-outline-secondary px-5 py-2">
          دانلود لاگ
        </a>
      @endif
    </div>

    <div class="progress-container mb-5" style="display: {{ $isMigrating ? 'block' : 'none' }};">
      <div class="progress-bar" style="width: {{ $migrationProgress }}%;">{{ $migrationProgress }}%</div>
    </div>
  @endif

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

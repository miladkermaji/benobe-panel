<div class="container-fluid py-1" dir="rtl">
    <!-- هدر -->
    <header class="glass-header p-3 rounded-3 mb-4 shadow-lg">
        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
            <div class="d-flex align-items-center gap-2">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"
                    class="animate-bounce">
                    <path d="M12 2v20M2 12h20" />
                </svg>
                <h4 class="mb-0 fw-bold text-white">ابزار انتقال داده‌ها</h4>
            </div>
            <div class="text-white fw-medium">انتقال داده‌ها از جداول قدیمی به جدید</div>
        </div>
    </header>

    <!-- فرم انتخاب جداول -->
    <div class="bg-light p-3 rounded-3 shadow-sm mb-4">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-bold">فایل جدول قدیمی (CSV یا SQL)</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-0">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
                            <path d="M12 2v20M2 12h20" />
                        </svg>
                    </span>
                    <input type="file" class="form-control input-shiny border-0 shadow-none"
                        wire:model.defer="oldTableFile">
                </div>
                @error('oldTableFile') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
                <!-- پروگرس بار آپلود -->
                <div class="upload-progress-bar mt-2" style="display: none;">
                    <div class="progress-fill" style="width: 0%;">0%</div>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">جدول جدید (دیتابیس فعلی)</label>
                <div class="d-flex" wire:ignore>
                    <span class="input-group-text bg-white border-0">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
                            <path d="M12 2v20M2 12h20" />
                        </svg>
                    </span>
                    <select class="form-control input-shiny border-0 shadow-none select2-table" id="newTableSelect">
                        <option value="">جدول جدید را انتخاب کنید</option>
                        @foreach ($tables as $table)
                            <option value="{{ $table }}" {{ $newTable === $table ? 'selected' : '' }}>{{ $table }}</option>
                        @endforeach
                    </select>
                </div>
                @error('newTable') <span class="text-danger d-block mt-1">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    <!-- پیش‌نمایش و نگاشت فیلدها -->
    @if (!empty($oldTableFields) && !empty($newTableFields))
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header glass-header text-white d-flex justify-content-between align-items-center">
                        <span>فیلدهای جدول قدیمی</span>
                        <span>{{ $oldTableFile->getClientOriginalName() }}</span>
                    </div>
                    <div class="card-body">
                        <input type="text" class="form-control input-shiny mb-3" wire:model.live="searchOld"
                            placeholder="جستجو در فیلدها...">
                        <ul class="list-group">
                            @foreach (array_filter($oldTableFields, fn($field) => str_contains($field, $searchOld)) as $field)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $field }}
                                    <select class="form-select w-50" wire:model.live="fieldMapping.{{ $field }}">
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
                <div class="card shadow-sm border-0">
                    <div class="card-header glass-header text-white">فیلدهای جدول جدید: {{ $newTable }}</div>
                    <div class="card-body">
                        <input type="text" class="form-control input-shiny mb-3" wire:model.live="searchNew"
                            placeholder="جستجو در فیلدها...">
                        <ul class="list-group">
                            @foreach (array_filter($newTableFields, fn($field) => str_contains($field, $searchNew)) as $field)
                                <li class="list-group-item">{{ $field }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- دکمه انتقال و پروگرس بار انتقال -->
        <div class="d-flex justify-content-center gap-3 mb-4">
            <button wire:click="migrateData" class="btn btn-gradient-primary px-4" wire:loading.attr="disabled">
                <span wire:loading.remove>شروع انتقال</span>
                <span wire:loading>در حال انتقال...</span>
            </button>
        </div>
        <div class="migration-progress-bar mb-4" style="display: {{ $isMigrating || $progress > 0 ? 'block' : 'none' }};">
            <div class="progress-fill" style="width: {{ $progress }}%;">{{ $progress }}%</div>
        </div>
    @endif

    <!-- استایل‌ها -->
    <style>
        .glass-header {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.95), rgba(124, 58, 237, 0.85));
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .bg-light {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
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
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
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

        /* رنگ هاور برای آپشن‌های Select2 */
        .select2-container--default .select2-results__option--highlighted[aria-selected],
        .select2-container--default .select2-results__option:hover {
            background-color: #007bff !important;
            color: white !important;
        }

        .btn-gradient-primary {
            background: linear-gradient(90deg, #4f46e5, #7c3aed);
            border: none;
            color: white;
            transition: all 0.3s ease;
            height: 40px;
            font-weight: bold;
        }

        .btn-gradient-primary:hover {
            background: linear-gradient(90deg, #4338ca, #6b21a8);
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(0, 0, 0, 0.15);
        }

        /* استایل شیک برای پروگرس بارها */
        .upload-progress-bar,
        .migration-progress-bar {
            width: 100%;
            height: 20px;
            background: #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4f46e5, #7c3aed);
            color: white;
            text-align: center;
            line-height: 20px;
            font-size: 12px;
            font-weight: bold;
            transition: width 0.5s ease-in-out;
        }
    </style>

    <!-- اسکریپت‌ها -->
    <script>
        document.addEventListener('livewire:init', () => {
            // مقداردهی اولیه Select2
            $('#newTableSelect').select2({
                placeholder: 'جدول جدید را انتخاب کنید',
                dir: 'rtl',
                width: '100%'
            });

            // همگام‌سازی مقدار اولیه با Livewire
            $('#newTableSelect').val(@json($newTable)).trigger('change');

            // ارسال مقدار انتخاب‌شده به Livewire
            $('#newTableSelect').on('change', function () {
                const selectedValue = $(this).val();
                @this.set('newTable', selectedValue);
            });

            Livewire.on('toast', (message, options = {}) => {
                toastr[options.type || 'info'](message);
            });

            Livewire.on('uploadProgressUpdated', (event) => {
                // تأخیر کوچک برای اطمینان از رندر DOM
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
                            }, 10000); // 10 ثانیه بعد مخفی می‌شه
                        }
                    } else {
                        console.warn('Upload progress bar not found at ' + progress + '%');
                    }
                }, 100); // تأخیر 100 میلی‌ثانیه
            });

            Livewire.on('progressUpdated', (event) => {
                const progress = event.detail ? event.detail.progress : @this.progress;
                const migrationProgressBar = document.querySelector('.migration-progress-bar .progress-fill');
                if (migrationProgressBar) {
                    migrationProgressBar.style.width = `${progress}%`;
                    migrationProgressBar.innerText = `${progress}%`;
                    migrationProgressBar.closest('.migration-progress-bar').style.display = 'block';
                } else {
                    console.warn('Migration progress bar not found at ' + progress + '%');
                }
            });
        });
    </script>
</div>
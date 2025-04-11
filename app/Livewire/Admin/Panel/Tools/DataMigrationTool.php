<?php

namespace App\Livewire\Admin\Panel\Tools;

use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use App\Imports\DynamicImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Psr\SimpleCache\CacheInterface;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Settings;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class DataMigrationTool extends Component
{
    use WithFileUploads;

    public $file; // شیء UploadedFile
    public $filePath; // مسیر فایل ذخیره‌شده (رشته)
    public $newTable;
    public $fieldMapping = [];
    public $oldFields = [];
    public $newFields = [];
    public $tables = [];
    public $uploadProgress = 0;
    public $migrationProgress = 0;
    public $isUploading = false;
    public $isMigrating = false;
    public $searchOld = '';
    public $searchNew = '';
    public $validationErrors = [];
    public $logFilePath = null;
    public $totalRecords = 0;
    public $successRecords = 0;
    public $failedRecords = 0;
    public $duplicateAction = 'skip'; // 'skip', 'update', 'abort'

    public function rules()
    {
        return [
            'file' => 'required|file|mimes:csv,xlsx,xls|max:102400', // 100MB
            'filePath' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $fullPath = Storage::disk('local')->path($value);
                    if (!file_exists($fullPath)) {
                        $fail('فایل ذخیره‌شده پیدا نشد.');
                    }
                    $extension = pathinfo($fullPath, PATHINFO_EXTENSION);
                    if (!in_array(strtolower($extension), ['csv', 'xlsx', 'xls'])) {
                        $fail('فقط فایل‌های CSV یا Excel (XLSX, XLS) مجاز هستند.');
                    }
                    // بررسی سایز فایل
                    if (filesize($fullPath) > 102400 * 1024) { // 100MB
                        $fail('حجم فایل از 100 مگابایت بیشتر است.');
                    }
                },
            ],
            'newTable' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!Schema::hasTable($value)) {
                        $fail("جدول انتخاب‌شده در دیتابیس وجود ندارد.");
                    }
                },
        ],
            'fieldMapping' => 'required|array|min:1',
        ];
    }

    protected $messages = [
        'file.required' => 'لطفاً فایل داده‌ها را انتخاب کنید.',
        'file.file' => 'فایل انتخاب‌شده معتبر نیست.',
        'file.mimes' => 'فقط فایل‌های CSV یا Excel (XLSX, XLS) مجاز هستند.',
        'file.max' => 'حداکثر حجم فایل 100 مگابایت است.',
        'newTable.required' => 'لطفاً جدول مقصد را انتخاب کنید.',
        'fieldMapping.required' => 'حداقل یک نگاشت فیلد باید انتخاب شود.',
        'fieldMapping.min' => 'حداقل یک نگاشت فیلد باید انتخاب شود.',
    ];

public function mount()
{
    ini_set('max_execution_time', 600); // 10 دقیقه
    ini_set('memory_limit', '2048M');
    ini_set('upload_max_filesize', '100M');
    ini_set('post_max_size', '100M');
    $this->loadDatabaseTables();
    Log::info('Loaded tables in mount:', ['tables' => $this->tables]);
}

    protected function loadDatabaseTables()
    {
        try {
            $this->tables = collect(DB::select('SHOW TABLES'))
                ->map(function ($table) {
                    return array_values((array)$table)[0];
                })
                ->sort()
                ->values()
                ->toArray();

            if (empty($this->tables)) {
                Log::warning('No tables found in the database.');
                $this->dispatch('toast', message: 'هیچ جدولی در دیتابیس یافت نشد.', type: 'warning');
            }
        } catch (\Exception $e) {
            Log::error('Failed to load database tables: ' . $e->getMessage());
            $this->dispatch('toast', message: 'خطا در بارگذاری جداول دیتابیس: ' . $e->getMessage(), type: 'error');
            $this->tables = [];
        }
    }

public function updatedFile()
{
    Log::info('updatedFile called', ['file' => $this->file ? $this->file->getClientOriginalName() : null]);
    $this->resetErrorBag('file');
    $this->validateOnly('file');

    $this->isUploading = true;
    $this->uploadProgress = 0;
    $this->dispatch('upload-progress', progress: 0);

    try {
        Log::info('Starting file upload', ['file_size' => $this->file->getSize()]);
        $path = $this->file->store('temp-uploads', 'local');
        $this->filePath = $path;
        Log::info('File uploaded successfully', ['path' => $path]);

        // آپدیت پروگرس به 50% برای ذخیره‌سازی
        $this->uploadProgress = 50;
        $this->dispatch('upload-progress', progress: 50);

        // لود فیلدها
        $this->loadOldFields();

        // آپدیت پروگرس به 100% بعد از لود فیلدها
        $this->uploadProgress = 100;
        $this->dispatch('upload-progress', progress: 100);

        if ($this->newTable) {
            $this->loadNewFields();
            $this->autoMapFields();
        }
    } catch (\Exception $e) {
        Log::error('File upload failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        $this->addError('file', 'خطا در آپلود فایل: ' . $e->getMessage());
        $this->dispatch('toast', message: 'خطا در آپلود فایل: ' . $e->getMessage(), type: 'error');
        $this->uploadProgress = 0;
        $this->dispatch('upload-progress', progress: 0);
    } finally {
        $this->isUploading = false;
        Log::info('updatedFile finished', ['uploadProgress' => $this->uploadProgress]);
    }
}

    public $isLoadingFields = false;

    public function updatedNewTable()
    {
        $startTime = microtime(true);
        Log::info('updatedNewTable started', ['newTable' => $this->newTable]);

        $this->isLoadingFields = true;
        $this->dispatch('start-loading');

        try {
            $this->validateOnly('newTable');
            $this->loadNewFields();
            if (!empty($this->oldFields)) {
                $this->autoMapFields();
            }
        } catch (\Exception $e) {
            Log::error('Error in updatedNewTable: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->dispatch('toast', message: 'خطا در لود فیلدها: ' . $e->getMessage(), type: 'error');
        } finally {
            $this->isLoadingFields = false;
            $this->dispatch('stop-loading');
            Log::info('updatedNewTable finished', ['execution_time' => microtime(true) - $startTime]);
        }
    }

protected function loadOldFields()
{
    $startTime = microtime(true);
    Log::info('loadOldFields started');

    try {
        $filePath = Storage::disk('local')->path($this->filePath);
        $rows = Excel::toCollection(new class implements WithHeadingRow, WithChunkReading {
            public function chunkSize(): int
            {
                return 100; // پردازش 100 ردیف در هر چانک
            }
        }, $filePath)->first();

        $this->oldFields = $rows->first()->keys()->map('strval')->take(50)->toArray(); // محدود به 50 ستون
        Log::info('Loaded old fields:', ['oldFields' => $this->oldFields]);

        if (empty($this->oldFields)) {
            $this->dispatch('toast', message: 'هیچ فیلدی در فایل یافت نشد. لطفاً مطمئن شوید فایل CSV هدر دارد.', type: 'error');
        }
    } catch (\Exception $e) {
        Log::error('Error loading old fields: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        $this->dispatch('toast', message: 'خطا در خواندن فیلدها: ' . $e->getMessage(), type: 'error');
        $this->oldFields = [];
    } finally {
        Log::info('loadOldFields finished', ['execution_time' => microtime(true) - $startTime]);
    }
}


    protected function loadNewFields()
    {
        try {
            $this->newFields = collect(Schema::getColumnListing($this->newTable))
                ->take(100) // محدود کردن به 100 ستون
                ->toArray();
            Log::info('Loaded new fields:', ['newFields' => $this->newFields]);

            if (empty($this->newFields)) {
                $this->dispatch('toast', message: 'هیچ فیلدی در جدول مقصد یافت نشد.', type: 'warning');
            }
        } catch (\Exception $e) {
            Log::error('Error loading new fields: ' . $e->getMessage());
            $this->dispatch('toast', message: 'خطا در لود فیلدهای جدول: ' . $e->getMessage(), type: 'error');
            $this->newFields = [];
        }
    }

    protected function autoMapFields()
    {
        $this->fieldMapping = [];

        $limitedOldFields = collect($this->oldFields)->take(100); // محدود کردن به 100 فیلد
        foreach ($limitedOldFields as $oldField) {
            $bestMatch = collect($this->newFields)
                ->first(fn ($newField) => Str::lower($oldField) === Str::lower($newField));

            if ($bestMatch) {
                $this->fieldMapping[$oldField] = $bestMatch;
            }
        }

        Log::info('Auto-mapped fields:', ['fieldMapping' => $this->fieldMapping]);

        $this->dispatch('toast', message: !empty($this->fieldMapping) ? 'نگاشت خودکار انجام شد.' : 'هیچ نگاشتی یافت نشد.', type: !empty($this->fieldMapping) ? 'success' : 'info');
    }

    public function migrateData()
    {
        Log::info('migrateData method called - Start');

        try {
            $this->validate();
            Log::info('Validation passed');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed:', ['errors' => $e->errors()]);
            $this->dispatch('toast', message: 'خطا در اعتبارسنجی: ' . implode(', ', $e->errors()[array_key_first($e->errors())]), type: 'error');
            return;
        }

        $this->isMigrating = true;
        $this->migrationProgress = 0;
        $this->totalRecords = 0;
        $this->successRecords = 0;
        $this->failedRecords = 0;

        try {
            $filePath = Storage::disk('local')->path($this->filePath);

            if (!file_exists($filePath)) {
                Log::error('File does not exist:', ['file' => $filePath]);
                $this->dispatch('toast', message: 'فایل پیدا نشد!', type: 'error');
                $this->isMigrating = false;
                return;
            }

            Log::info('Starting data migration...', [
                'table' => $this->newTable,
                'file' => $filePath,
                'fieldMapping' => $this->fieldMapping,
                'duplicateAction' => $this->duplicateAction,
            ]);

            Excel::import(
                new \App\Imports\DynamicImport(
                    $this->newTable,
                    $this->fieldMapping,
                    $this->duplicateAction,
                    function ($processed, $total) {
                        $this->updateMigrationProgress($processed, $total);
                    }
                ),
                $filePath
            );

            Log::info('Migration completed successfully');

            if ($this->successRecords > 0) {
                $this->createMigrationLog();
                $this->dispatch('toast', message: "انتقال داده‌ها تکمیل شد. موفق: {$this->successRecords}, ناموفق: {$this->failedRecords}", type: 'success');
            } else {
                $this->dispatch('toast', message: "هیچ رکوردی منتقل نشد. ناموفق: {$this->failedRecords}", type: 'warning');
            }
        } catch (\Exception $e) {
            Log::error('Migration failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->dispatch('toast', message: 'خطا در انتقال داده‌ها: ' . $e->getMessage(), type: 'error');
        } finally {
            $this->isMigrating = false;
            // حذف فایل موقت
            if ($this->filePath && Storage::disk('local')->exists($this->filePath)) {
                Storage::disk('local')->delete($this->filePath);
                $this->filePath = null;
            }
            Log::info('migrateData method called - End');
        }
    }
    public function testButton()
    {
        Log::info('Test button clicked');
        $this->dispatch('toast', message: 'دکمه تست کار کرد!', type: 'success');
    }
    protected function updateMigrationProgress($processed, $total)
    {
        $this->totalRecords = $total;
        $this->successRecords = $processed['success'] ?? $processed;
        $this->failedRecords = $total - $this->successRecords;
        $this->migrationProgress = $total > 0 ? (int)(($this->successRecords / $total) * 100) : 0;

        Log::info('Migration progress updated:', [
            'processed' => $processed,
            'total' => $total,
            'success' => $this->successRecords,
            'failed' => $this->failedRecords,
            'progress' => $this->migrationProgress,
        ]);

        $this->dispatch('migration-progress', progress: $this->migrationProgress);
    }

    protected function createMigrationLog()
    {
        $logContent = "گزارش انتقال داده‌ها\n===================\n\n";
        $logContent .= "تاریخ: " . now()->format('Y-m-d H:i:s') . "\n";
        $logContent .= "فایل مبدا: " . basename($this->file) . "\n";
        $logContent .= "جدول مقصد: {$this->newTable}\n";
        $logContent .= "کل رکوردها: {$this->totalRecords}\n";
        $logContent .= "موفق: {$this->successRecords}\n";
        $logContent .= "ناموفق: {$this->failedRecords}\n";
        $logContent .= "نوع پردازش تکراری‌ها: {$this->duplicateAction}\n";
        $logContent .= "\nنگاشت فیلدها:\n" . json_encode($this->fieldMapping, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $logFileName = 'migration_log_' . now()->format('YmdHis') . '.txt';
        Storage::disk('local')->put("logs/{$logFileName}", $logContent);
        $this->logFilePath = "logs/{$logFileName}";
    }

    public function downloadLogFile()
    {
        if ($this->logFilePath && Storage::disk('local')->exists($this->logFilePath)) {
            return redirect()->route('admin.tools.data-migration.download-log', ['filename' => basename($this->logFilePath)]);
        }
        $this->dispatch('toast', message: 'فایل لاگ یافت نشد.', type: 'error');
    }

    public function render()
    {
        Log::info('Tables before render:', ['tables' => $this->tables]);
        return view('livewire.admin.panel.tools.data-migration-tool');
    }
}

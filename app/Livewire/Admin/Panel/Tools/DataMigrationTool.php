<?php

namespace App\Livewire\Admin\Panel\Tools;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Visibility;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DataMigrationTool extends Component
{
    use WithFileUploads;

    public $oldTableFile;
    public $newTable;
    public $oldTableFields = [];
    public $newTableFields = [];
    public $fieldMapping = [];
    public $tables = [];
    public $progress = 0;
    public $uploadProgress = 0;
    public $isMigrating = false;
    public $isUploading = false;
    public $searchOld = '';
    public $searchNew = '';
    public $validationErrors = [];
    public $logFilePath = null;
    public $showDuplicateConfirm = false;
    public $duplicateAction = 'skip'; // 'skip', 'update', 'abort'
    public $totalRecords = 0;
    public $successRecords = 0;
    public $failedRecords = 0;
    public $migrationSummary = [];
    public $uniqueConstraints = [];
    public $primaryKey = 'id';

    protected $rules = [
     'oldTableFile' => 'required|file|mimes:sql,csv,txt|max:51200',
     'newTable' => 'required|string',
     'fieldMapping' => 'required|array|min:1',
];

    public function rules()
    {
        return [
            'oldTableFile' => 'required|file|mimes:sql,csv,txt|max:51200',
            'newTable' => ['required', 'string', function ($attribute, $value, $fail) {
                try {
                    if (!Schema::hasTable($value)) {
                        $fail("جدول {$value} در دیتابیس وجود ندارد.");
                    }
                } catch (\Exception $e) {
                    $fail('خطا در بررسی وجود جدول: ' . $e->getMessage());
                }
            }],
            'fieldMapping' => 'required|array|min:1',
        ];
    }

    protected $messages = [
        'oldTableFile.required' => 'لطفاً فایل جدول قدیمی را انتخاب کنید.',
        'oldTableFile.file' => 'فایل انتخاب‌شده معتبر نیست.',
        'oldTableFile.mimes' => 'فقط فایل‌های SQL, CSV یا TXT مجاز هستند.',
        'oldTableFile.max' => 'حداکثر حجم فایل 10 مگابایت است.',
        'newTable.required' => 'لطفاً جدول جدید را انتخاب کنید.',
        'newTable.exists' => 'جدول انتخاب‌شده در دیتابیس وجود ندارد.',
        'fieldMapping.required' => 'حداقل یک نگاشت فیلد باید انتخاب شود.',
        'fieldMapping.min' => 'حداقل یک نگاشت فیلد باید انتخاب شود.',
    ];

    public function mount()
    {
        ini_set('max_execution_time', 300); // 5 دقیقه
        ini_set('memory_limit', '256M'); // افزایش حافظه
        $this->loadDatabaseTables();
    }

    protected function loadDatabaseTables()
    {
        // روش جایگزین با استفاده از query builder لاراول
        $this->tables = collect(DB::select('SHOW TABLES'))
            ->map(function ($table) {
                return array_values((array)$table)[0];
            })
            ->sort()
            ->toArray();
    }


  public function updatedOldTableFile()
{
    $this->resetErrorBag('oldTableFile');
    $this->validateOnly('oldTableFile');

    $this->isUploading = true;
    $this->uploadProgress = 0;

    try {
        if (!$this->oldTableFile instanceof \Illuminate\Http\UploadedFile) {
            throw new \Exception('فایل به درستی آپلود نشد.');
        }

        $fileSize = $this->oldTableFile->getSize();
        $path = $this->oldTableFile->store('temp-uploads', 'local');
        $fullPath = Storage::disk('local')->path($path);

        $this->oldTableFile = new \Illuminate\Http\UploadedFile(
            $fullPath,
            $this->oldTableFile->getClientOriginalName()
        );

        // نمایش پیشرفت آپلود
        $this->simulateUploadProgress($fileSize);

        $this->loadOldTableFields();

        if (!empty($this->newTable)) {
            $this->loadNewTableFields();
            $this->autoMapFields();
        }
    } catch (\Exception $e) {
        Log::error('Upload failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        $this->addError('oldTableFile', $this->formatErrorMessage($e->getMessage()));
    } finally {
        $this->isUploading = false;
    }
}

protected function simulateUploadProgress($fileSize)
{
    $chunkSize = 1024 * 1024; // 1MB
    $totalChunks = max(1, ceil($fileSize / $chunkSize));
    $handle = fopen($this->oldTableFile->getRealPath(), 'r');
    $processedBytes = 0;

    while (!feof($handle)) {
        $chunk = fread($handle, $chunkSize);
        $processedBytes += strlen($chunk);
        $progress = min(100, (int)(($processedBytes / $fileSize) * 100));
        
        $this->uploadProgress = $progress;
        $this->dispatch('uploadProgressUpdated', ['progress' => $progress]);
        usleep(50000); // تاخیر برای رندر بهتر
    }

    fclose($handle);
}

    protected function updateProgressBasedOnFile($fileSize)
    {
        $chunkSize = 1024 * 1024; // 1MB
        $totalChunks = max(1, ceil($fileSize / $chunkSize));
        $handle = fopen($this->oldTableFile->getRealPath(), 'r');
        $processedBytes = 0;

        while (!feof($handle)) {
            $chunk = fread($handle, $chunkSize);
            $processedBytes += strlen($chunk);
            $progress = min(100, (int)(($processedBytes / $fileSize) * 100));

            $this->uploadProgress = $progress;
            $this->dispatch('uploadProgressUpdated', ['progress' => $this->uploadProgress]);
            usleep(50000);
        }

        fclose($handle);
    }
   

    public function updatedNewTable($value)
    {
        $this->validateOnly('newTable');

        try {
            $this->loadNewTableFields();
            $this->detectTableConstraints();

            if (!empty($this->oldTableFields)) {
                $this->autoMapFields();
            }
        } catch (\Exception $e) {
            $this->addError('newTable', $this->formatErrorMessage($e->getMessage()));
        }
    }

    protected function detectTableConstraints()
    {
        $this->uniqueConstraints = [];
        $this->primaryKey = 'id';

        // دریافت کلید اصلی
        $primaryKey = DB::selectOne("
        SELECT COLUMN_NAME 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = ? 
        AND COLUMN_KEY = 'PRI'
    ", [$this->newTable]);

        if ($primaryKey) {
            $this->primaryKey = $primaryKey->COLUMN_NAME;
        }

        // دریافت محدودیت‌های یکتا
        $uniques = DB::select("
        SELECT COLUMN_NAME 
        FROM INFORMATION_SCHEMA.STATISTICS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = ? 
        AND NON_UNIQUE = 0
        AND INDEX_NAME != 'PRIMARY'
    ", [$this->newTable]);

        foreach ($uniques as $unique) {
            $this->uniqueConstraints[] = [$unique->COLUMN_NAME];
        }
    }

    public function loadOldTableFields()
    {
        if (!$this->oldTableFile) {
            return;
        }

        $file = $this->oldTableFile->getRealPath();
        $extension = strtolower($this->oldTableFile->getClientOriginalExtension());

        $this->oldTableFields = [];

        try {
            if ($extension === 'csv') {
                $this->loadFieldsFromCsv($file);
            } elseif ($extension === 'sql') {
                $this->loadFieldsFromSql($file);
            }
        } catch (\Exception $e) {
            $this->dispatch('toast', 'خطا در خواندن فایل: ' . $e->getMessage(), ['type' => 'error']);
            Log::error('Error loading old table fields: ' . $e->getMessage());
        }

        if (empty($this->oldTableFields)) {
            $this->dispatch('toast', 'هیچ فیلدی در فایل قدیمی یافت نشد. لطفاً فایل را بررسی کنید.', ['type' => 'warning']);
        }
    }

    protected function loadFieldsFromCsv($file)
    {
        $handle = fopen($file, 'r');
        if ($handle !== false) {
            $headers = fgetcsv($handle);
            if ($headers !== false) {
                $this->oldTableFields = array_map('trim', $headers);
            }
            fclose($handle);
        }
    }

    protected function loadFieldsFromSql($file)
    {
        $content = file_get_contents($file);

        // Try to extract CREATE TABLE statement
        if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(.*?)`?\s*\((.*?)\)\s*(?:ENGINE|;)/is', $content, $matches)) {
            $this->extractFieldsFromCreateTable($matches[2]);
        }
        // If no CREATE TABLE, look for INSERT statements
        elseif (preg_match('/INSERT\s+INTO\s+`?(.*?)`?\s*\((.*?)\)\s*VALUES/is', $content, $matches)) {
            $this->oldTableFields = array_map('trim', explode(',', str_replace(['`', "'", '"'], '', $matches[2])));
        }
    }

    protected function extractFieldsFromCreateTable($fieldsDefinition)
    {
        $fieldsRaw = preg_split('/,\s*(?![^()]*\))/', $fieldsDefinition);

        foreach ($fieldsRaw as $field) {
            $field = trim($field);
            if (preg_match('/^`?([^`\s]+)`?/', $field, $matches)) {
                $this->oldTableFields[] = $matches[1];
            }
        }
    }

    public function loadNewTableFields()
    {
        $this->newTableFields = Schema::getColumnListing($this->newTable);

        if (empty($this->newTableFields)) {
            $this->dispatch('toast', 'هیچ فیلدی در جدول مقصد یافت نشد.', ['type' => 'warning']);
        }
    }

    protected function autoMapFields()
    {
        $this->fieldMapping = [];
        $sampleData = $this->getSampleData();

        foreach ($this->oldTableFields as $oldField) {
            $bestMatch = $this->findBestFieldMatch($oldField, $sampleData[$oldField] ?? null);

            if ($bestMatch) {
                $this->fieldMapping[$oldField] = $bestMatch;
                $this->validateFieldMapping($oldField, $bestMatch);
            }
        }

        if (!empty($this->fieldMapping)) {
            $this->dispatch('toast', 'تطبیق خودکار فیلدها انجام شد.', ['type' => 'success']);
        } else {
            $this->dispatch('toast', 'هیچ تطابقی برای نگاشت خودکار یافت نشد.', ['type' => 'info']);
        }
    }

    protected function findBestFieldMatch($oldField, $sampleValue = null)
    {
        // First try exact name match
        foreach ($this->newTableFields as $newField) {
            if (strtolower($oldField) === strtolower($newField)) {
                return $newField;
            }
        }

        // Then try similar names
        $similarityThreshold = 70;
        $bestMatch = null;
        $highestSimilarity = 0;

        foreach ($this->newTableFields as $newField) {
            similar_text(strtolower($oldField), strtolower($newField), $similarity);

            if ($similarity > $highestSimilarity && $similarity >= $similarityThreshold) {
                $highestSimilarity = $similarity;
                $bestMatch = $newField;
            }
        }

        if ($bestMatch) {
            return $bestMatch;
        }

        // Finally try type matching if sample value is available
        if ($sampleValue !== null) {
            return $this->matchByType($oldField, $sampleValue);
        }

        return null;
    }

    protected function matchByType($oldField, $sampleValue)
    {
        $oldType = $this->guessFieldType($sampleValue);
        $bestMatch = null;
        $bestScore = 0;

        foreach ($this->newTableFields as $newField) {
            $newType = Schema::getColumnType($this->newTable, $newField);
            $score = $this->calculateTypeMatchScore($oldType, $newType);

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $newField;
            }
        }

        return $bestScore > 0 ? $bestMatch : null;
    }

    protected function calculateTypeMatchScore($oldType, $newType)
    {
        $typeGroups = [
            'numeric' => ['integer', 'bigint', 'smallint', 'tinyint', 'decimal', 'float', 'double'],
            'string' => ['string', 'varchar', 'char', 'text', 'mediumtext', 'longtext'],
            'date' => ['date', 'datetime', 'timestamp', 'time'],
            'boolean' => ['boolean', 'tinyint(1)']
        ];

        foreach ($typeGroups as $group => $types) {
            if (in_array($oldType, $types) && in_array($newType, $types)) {
                return 100; // Perfect match within group
            }
        }

        // Partial matches
        if (($oldType === 'integer' && $newType === 'boolean') ||
            ($oldType === 'boolean' && $newType === 'integer')) {
            return 50;
        }

        if (($oldType === 'string' && in_array($newType, ['date', 'datetime', 'timestamp'])) ||
            (in_array($oldType, ['date', 'datetime', 'timestamp']) && $newType === 'string')) {
            return 30;
        }

        return 0;
    }

    protected function guessFieldType($value)
    {
        if (is_null($value) || $value === '' || $value === 'NULL') {
            return 'unknown';
        }

        if (is_numeric($value)) {
            return (int)$value == $value ? 'integer' : 'float';
        }

        if (strtotime($value) !== false) {
            $date = date_parse($value);
            if ($date['error_count'] === 0) {
                return $date['hour'] === false ? 'date' : 'datetime';
            }
        }

        if (in_array(strtolower($value), ['true', 'false', '1', '0', 'yes', 'no', 'on', 'off'])) {
            return 'boolean';
        }

        if (is_string($value) && (str_starts_with($value, '{') || str_starts_with($value, '['))) {
            json_decode($value);
            return json_last_error() === JSON_ERROR_NONE ? 'json' : 'string';
        }

        return 'string';
    }

    public function updateFieldMapping($oldField, $newField)
    {
        if ($newField) {
            $this->fieldMapping[$oldField] = $newField;
            $this->validateFieldMapping($oldField, $newField);
        } else {
            unset($this->fieldMapping[$oldField]);
            unset($this->validationErrors[$oldField]);
        }
    }

    protected function validateFieldMapping($oldField, $newField)
    {
        $sampleData = $this->getSampleData();
        $sampleValue = $sampleData[$oldField] ?? null;

        if ($sampleValue === null) {
            return; // Can't validate without sample data
        }

        $newType = Schema::getColumnType($this->newTable, $newField);
        $columnInfo = $this->getColumnInfo($newField);

        $error = $this->validateValueAgainstColumn($sampleValue, $newType, $columnInfo);

        if ($error) {
            $this->validationErrors[$oldField] = $error;
            $this->dispatch('toast', "خطا در نگاشت فیلد {$oldField}: {$error}", ['type' => 'warning']);
        } else {
            unset($this->validationErrors[$oldField]);
        }
    }

    protected function getColumnInfo($field)
    {
        $result = DB::selectOne("SHOW COLUMNS FROM `{$this->newTable}` WHERE Field = ?", [$field]);

        $info = [
            'type' => $result->Type,
            'nullable' => $result->Null === 'YES',
            'default' => $result->Default,
            'key' => $result->Key,
            'extra' => $result->Extra,
        ];

        // Parse length and precision
        if (preg_match('/^(\w+)(?:\((\d+)(?:,(\d+))?\))?/', $result->Type, $matches)) {
            $info['base_type'] = $matches[1];
            $info['length'] = $matches[2] ?? null;
            $info['precision'] = $matches[3] ?? null;
        } else {
            $info['base_type'] = $result->Type;
        }

        // Parse enum values
        if (str_starts_with($result->Type, 'enum')) {
            preg_match_all("/'(.*?)'/", $result->Type, $matches);
            $info['enum_values'] = $matches[1] ?? [];
        }

        // For JSON columns, set default value if not exists
        if ($info['base_type'] === 'json' && $info['default'] === null && !$info['nullable']) {
            $info['default'] = '{}';
        }

        return $info;
    }

    protected function validateValueAgainstColumn($value, $type, $columnInfo)
    {
        $value = is_string($value) ? trim($value, "'\" \t\n\r\0\x0B") : $value;

        // Handle NULL values
        if ($value === null || $value === '' || $value === 'NULL') {
            return $columnInfo['nullable'] ? null : 'فیلد مقصد نمی‌تواند NULL باشد';
        }

        // Type-specific validation
        switch (strtolower($columnInfo['base_type'])) {
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'bigint':
                if (!is_numeric($value)) {
                    return 'مقدار باید عددی باشد';
                }
                break;

            case 'decimal':
            case 'float':
            case 'double':
                if (!is_numeric($value)) {
                    return 'مقدار باید عدد اعشاری باشد';
                }
                break;

            case 'char':
            case 'varchar':
                $maxLength = $columnInfo['length'] ?? 255;
                if (mb_strlen($value) > $maxLength) {
                    return "حداکثر طول مجاز {$maxLength} کاراکتر است";
                }
                break;

            case 'text':
            case 'mediumtext':
            case 'longtext':
                // No length validation for text types
                break;

            case 'enum':
                if (!in_array($value, $columnInfo['enum_values'])) {
                    $allowed = implode(', ', $columnInfo['enum_values']);
                    return "مقدار باید یکی از موارد زیر باشد: {$allowed}";
                }
                break;

            case 'date':
            case 'datetime':
            case 'timestamp':
                if (strtotime($value) === false) {
                    return 'فرمت تاریخ/زمان نامعتبر است';
                }
                break;

            case 'time':
                if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $value)) {
                    return 'فرمت زمان نامعتبر (HH:MM یا HH:MM:SS مورد انتظار است)';
                }
                break;

            case 'json':
                if (is_string($value)) {
                    json_decode($value);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $fixed = $this->attemptToFixJson($value);
                        if ($fixed === null) {
                            return 'مقدار JSON نامعتبر است';
                        }
                    }
                }
                break;

            case 'boolean':
                if (!in_array(strtolower($value), ['0', '1', 'true', 'false', 'yes', 'no', 'on', 'off'])) {
                    return 'مقدار باید بولین باشد (true/false/1/0)';
                }
                break;
        }

        return null;
    }

    protected function getSampleData()
    {
        if (!$this->oldTableFile) {
            return [];
        }

        $file = $this->oldTableFile->getRealPath();
        $extension = strtolower($this->oldTableFile->getClientOriginalExtension());

        try {
            if ($extension === 'csv') {
                return $this->getCsvSampleData($file);
            } elseif ($extension === 'sql') {
                return $this->getSqlSampleData($file);
            }
        } catch (\Exception $e) {
            Log::error('Error getting sample data: ' . $e->getMessage());
            return [];
        }

        return [];
    }

    protected function getCsvSampleData($file)
    {
        $handle = fopen($file, 'r');
        if ($handle === false) {
            return [];
        }

        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);
            return [];
        }

        $sample = [];
        $row = fgetcsv($handle);
        if ($row !== false && count($headers) === count($row)) {
            $sample = array_combine($headers, $row);
        }

        fclose($handle);
        return $sample;
    }

    protected function getSqlSampleData($file)
    {
        $content = file_get_contents($file);
        $sample = [];

        // Try to find INSERT statements
        if (preg_match('/INSERT\s+INTO\s+`?(.*?)`?\s*\((.*?)\)\s*VALUES\s*\((.*?)\)/is', $content, $matches)) {
            $fieldNames = array_map('trim', explode(',', str_replace(['`', "'", '"'], '', $matches[2])));
            $values = array_map('trim', str_getcsv(str_replace(["'", "(", ")"], "", $matches[3])));

            if (count($fieldNames) === count($values)) {
                $sample = array_combine($fieldNames, $values);
            }
        }

        return $sample;
    }

    public function confirmDuplicateAction($action)
    {
        $this->duplicateAction = $action;
        $this->showDuplicateConfirm = false;
        $this->migrateData();
    }

    public function migrateData()
    {
        $this->validate();
        $this->prepareForMigration();

        try {
            $records = $this->extractRecordsFromFile();
            $this->totalRecords = count($records);

            if ($this->hasPotentialDuplicates($records)) {
                $this->showDuplicateConfirm = true;
                return;
            }

            $this->processMigration($records);
        } catch (\Exception $e) {
            $this->handleMigrationError($e);
        } finally {
            $this->finalizeMigration();
        }
    }

    protected function prepareForMigration()
    {
        $this->isMigrating = true;
        $this->progress = 0;
        $this->successRecords = 0;
        $this->failedRecords = 0;
        $this->migrationSummary = [];
        $this->logFilePath = null;
    }

    protected function extractRecordsFromFile()
    {
        $file = $this->oldTableFile->getRealPath();
        $extension = strtolower($this->oldTableFile->getClientOriginalExtension());

        if ($extension === 'csv') {
            return $this->extractRecordsFromCsv($file);
        } elseif ($extension === 'sql') {
            return $this->extractRecordsFromSql($file);
        }

        return [];
    }

    protected function extractRecordsFromCsv($file)
    {
        $records = [];
        $handle = fopen($file, 'r');

        if ($handle === false) {
            throw new \Exception('خطا در باز کردن فایل CSV');
        }

        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);
            throw new \Exception('هدرهای فایل CSV نامعتبر است');
        }

        while (($row = fgetcsv($handle)) !== false) {
            if (count($headers) === count($row)) {
                $records[] = array_combine($headers, $row);
            }
        }

        fclose($handle);
        return $records;
    }

    protected function extractRecordsFromSql($file)
    {
        $content = file_get_contents($file);
        $records = [];

        // Extract all INSERT statements
        if (preg_match_all('/INSERT\s+INTO\s+`?(\w+)`?\s*\((.*?)\)\s*VALUES\s*(.*?);/is', $content, $insertMatches)) {
            $fieldNames = array_map('trim', explode(',', str_replace(['`', "'", '"'], '', $insertMatches[2][0])));

            // Extract all value sets
            preg_match_all('/\((.*?)\)/', $insertMatches[3][0], $valueMatches);

            foreach ($valueMatches[1] as $values) {
                // پردازش ویژه برای مقادیر JSON
                $values = preg_replace_callback('/\'(\{.*?\}|\[.*?\])\'/', function ($matches) {
                    return "'" . str_replace("'", "\\'", $matches[1]) . "'";
                }, $values);

                $row = str_getcsv($values, ',', "'");

                if (count($fieldNames) === count($row)) {
                    $record = [];
                    foreach ($fieldNames as $index => $field) {
                        $value = $row[$index];
                        // اگر فیلد JSON است، آن را decode کنیم
                        if (in_array($field, ['work_hours', 'appointment_settings'])) {
                            $value = $this->cleanJsonValue($value);
                        }
                        $record[$field] = $value;
                    }
                    $records[] = $record;
                }
            }
        }

        return $records;
    }

    protected function cleanJsonValue($value)
    {
        // حذف اسلش‌های اضافی
        $value = stripslashes($value);
        // حذف نقل قول‌های اضافی
        $value = trim($value, "'\"");
        // تبدیل نقل قول‌های تکی به دوتایی برای JSON معتبر
        $value = preg_replace('/\'([^"]+?)\'/', '"$1"', $value);
        return $value;
    }

    protected function hasPotentialDuplicates($records)
    {
        if (empty($this->uniqueConstraints) && empty($this->primaryKey)) {
            return false;
        }

        $sampleSize = min(10, count($records));
        $sampleRecords = array_slice($records, 0, $sampleSize);

        foreach ($sampleRecords as $record) {
            $mappedRecord = $this->mapRecordFields($record);

            // Check primary key conflicts
            if ($this->primaryKey && isset($mappedRecord[$this->primaryKey])) {
                $exists = DB::table($this->newTable)
                    ->where($this->primaryKey, $mappedRecord[$this->primaryKey])
                    ->exists();

                if ($exists) {
                    return true;
                }
            }

            // Check unique constraints
            foreach ($this->uniqueConstraints as $constraint) {
                $where = [];
                foreach ($constraint as $field) {
                    if (isset($mappedRecord[$field])) {
                        $where[$field] = $mappedRecord[$field];
                    }
                }

                if (!empty($where) && DB::table($this->newTable)->where($where)->exists()) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function processMigration($records)
    {
        DB::beginTransaction();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $batchSize = 100;
        $batches = array_chunk($records, $batchSize);
        $totalBatches = count($batches);

        $failedRecords = [];
        $columnInfoCache = [];

        Log::info('Starting migration process', [
            'total_records' => count($records),
            'batch_size' => $batchSize,
            'total_batches' => $totalBatches
        ]);

        foreach ($batches as $batchIndex => $batch) {
            $mappedBatch = [];

            foreach ($batch as $record) {
                try {
                    $mappedRecord = $this->mapRecordFields($record);
                    $validatedRecord = $this->validateAndConvertRecord($mappedRecord, $columnInfoCache);

                    if ($validatedRecord !== null) {
                        $mappedBatch[] = $validatedRecord;
                    }
                } catch (\Exception $e) {
                    Log::error('Record processing failed', [
                        'record' => $record,
                        'error' => $e->getMessage()
                    ]);
                    $failedRecords[] = [
                        'record' => $record,
                        'error' => $e->getMessage()
                    ];
                    $this->failedRecords++;
                }
            }

            try {
                if (!empty($mappedBatch)) {
                    $this->insertBatch($mappedBatch);
                    $this->successRecords += count($mappedBatch);
                    Log::info('Batch inserted successfully', [
                        'batch_index' => $batchIndex,
                        'records_inserted' => count($mappedBatch)
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Batch insert failed', [
                    'batch_index' => $batchIndex,
                    'error' => $e->getMessage()
                ]);
                $this->failedRecords += count($mappedBatch);
            }

            $this->progress = (int)(($batchIndex + 1) / $totalBatches * 100);
            $this->dispatch('progressUpdated', ['progress' => $this->progress]);
        }

        $this->createMigrationLog($failedRecords);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        DB::commit();

        Log::info('Migration completed', [
            'success_records' => $this->successRecords,
            'failed_records' => $this->failedRecords
        ]);
    }

    protected function mapRecordFields($record)
    {
        $mapped = [];

        foreach ($this->fieldMapping as $oldField => $newField) {
            if (array_key_exists($oldField, $record)) {
                $mapped[$newField] = $record[$oldField];
            }
        }

        return $mapped;
    }

    protected function validateAndConvertRecord($record, &$columnInfoCache)
    {
        foreach ($record as $field => $value) {
            if (!isset($columnInfoCache[$field])) {
                $columnInfoCache[$field] = $this->getColumnInfo($field);
            }

            $columnInfo = $columnInfoCache[$field];
            $convertedValue = $this->convertValueForColumn($value, $columnInfo);

            if ($convertedValue === null && !$columnInfo['nullable']) {
                throw new \Exception("فیلد {$field} نمی‌تواند NULL باشد");
            }

            $record[$field] = $convertedValue;
        }

        return $record;
    }

    protected function convertValueForColumn($value, $columnInfo)
    {
        $value = is_string($value) ? trim($value, "'\" \t\n\r\0\x0B") : $value;

        // Handle NULL values
        if ($value === null || $value === '' || $value === 'NULL') {
            return $columnInfo['nullable'] ? null : $columnInfo['default'];
        }

        // Type conversion
        switch (strtolower($columnInfo['base_type'])) {
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'bigint':
                return $this->convertToInteger($value, $columnInfo);

            case 'decimal':
            case 'float':
            case 'double':
                return $this->convertToFloat($value, $columnInfo);

            case 'char':
            case 'varchar':
                return $this->convertToString($value, $columnInfo);

            case 'text':
            case 'mediumtext':
            case 'longtext':
                return (string)$value;

            case 'enum':
                return $this->convertToEnum($value, $columnInfo);

            case 'date':
                return $this->convertToDate($value, 'Y-m-d');

            case 'datetime':
            case 'timestamp':
                return $this->convertToDate($value, 'Y-m-d H:i:s');

            case 'time':
                return $this->convertToDate($value, 'H:i:s');

            case 'json':
                return $this->convertToJson($value, $columnInfo);

            case 'boolean':
                return $this->convertToBoolean($value);

            default:
                return $value;
        }
    }

    protected function convertToInteger($value, $columnInfo)
    {
        if (is_numeric($value)) {
            return (int)$value;
        }

        if (is_string($value)) {
            $lower = strtolower($value);
            if (in_array($lower, ['true', 'yes', 'on'])) {
                return 1;
            } elseif (in_array($lower, ['false', 'no', 'off'])) {
                return 0;
            }
        }

        return $columnInfo['nullable'] ? null : 0;
    }

    protected function convertToFloat($value, $columnInfo)
    {
        if (is_numeric($value)) {
            return (float)$value;
        }

        return $columnInfo['nullable'] ? null : 0.0;
    }

    protected function convertToString($value, $columnInfo)
    {
        $value = (string)$value;
        $maxLength = $columnInfo['length'] ?? 255;

        if (mb_strlen($value) > $maxLength) {
            $value = mb_substr($value, 0, $maxLength);
        }

        return $value;
    }

    protected function convertToEnum($value, $columnInfo)
    {
        if (in_array($value, $columnInfo['enum_values'])) {
            return $value;
        }

        return $columnInfo['default'] ?? $columnInfo['enum_values'][0] ?? null;
    }

    protected function convertToDate($value, $format)
    {
        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return null;
        }

        return date($format, $timestamp);
    }

    protected function convertToJson($value, $columnInfo = null)
    {
        // اگر مقدار از قبل آرایه یا آبجکت است
        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        // اگر مقدار رشته JSON معتبر است
        if (is_string($value)) {
            // حذف اسلش‌های اضافی
            $value = stripslashes($value);
            // حذف نقل قول‌های اضافی
            $value = trim($value, "'\"");

            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return json_encode($decoded, JSON_UNESCAPED_UNICODE);
            }

            // سعی در اصلاح JSON نامعتبر
            $fixedValue = $this->fixInvalidJson($value);
            $decoded = json_decode($fixedValue, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return json_encode($decoded, JSON_UNESCAPED_UNICODE);
            }
        }

        // اگر مقدار scalar است (رشته، عدد، بولین)
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    protected function fixInvalidJson($value)
    {
        // حذف اسلش‌های اضافی
        $value = stripslashes($value);
        // حذف نقل قول‌های اضافی
        $value = trim($value, "'\"");
        // تبدیل کلیدهای بدون نقل قول
        $value = preg_replace('/(\w+):/', '"$1":', $value);
        // تبدیل مقادیر بدون نقل قول
        $value = preg_replace('/:\s*([^"\s{}\[\]]+?)([,}\]])/', ':"$1"$2', $value);
        return $value;
    }
    protected function attemptToFixJson($value)
    {
        try {
            // حذف اسلش‌های اضافی
            $value = stripslashes($value);

            // جایگزینی نقل قول‌های نامعتبر
            $value = str_replace("'", '"', $value);

            // تبدیل کلیدهای بدون نقل قول
            $value = preg_replace('/([{,])(\s*)([^"]+?)\s*:/', '$1"$3":', $value);

            // تبدیل مقادیر بدون نقل قول
            $value = preg_replace('/:\s*([^"\s{}\[\]]+?)([,}\]])/', ':"$1"$2', $value);

            // بررسی مجدد
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return json_encode($decoded, JSON_UNESCAPED_UNICODE);
            }
        } catch (\Exception $e) {
            Log::warning('JSON fix attempt failed: ' . $e->getMessage());
        }

        return null;
    }
    protected function convertToBoolean($value)
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        if (is_numeric($value)) {
            return $value != 0 ? 1 : 0;
        }

        if (is_string($value)) {
            $lower = strtolower($value);
            return in_array($lower, ['true', 'yes', 'on', '1']) ? 1 : 0;
        }

        return 0;
    }

    protected function insertBatch($records)
    {
        if (empty($records)) {
            Log::warning('Attempted to insert empty batch');
            return;
        }

        try {
            Log::info('Attempting to insert batch', ['count' => count($records), 'first_record' => $records[0]]);

            switch ($this->duplicateAction) {
                case 'skip':
                    $result = DB::table($this->newTable)->insertOrIgnore($records);
                    Log::info('InsertOrIgnore result', ['result' => $result]);
                    break;

                case 'update':
                    foreach ($records as $record) {
                        $where = [];
                        if ($this->primaryKey && isset($record[$this->primaryKey])) {
                            $where = [$this->primaryKey => $record[$this->primaryKey]];
                        }

                        if (!empty($where)) {
                            $result = DB::table($this->newTable)->updateOrInsert($where, $record);
                            Log::debug('UpdateOrInsert result', ['result' => $result]);
                        } else {
                            $result = DB::table($this->newTable)->insert($record);
                            Log::debug('Insert result', ['result' => $result]);
                        }
                    }
                    break;

                case 'abort':
                    $result = DB::table($this->newTable)->insert($records);
                    Log::info('Insert result', ['result' => $result]);
                    break;
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Batch insert failed', [
                'error' => $e->getMessage(),
                'first_record' => $records[0] ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    protected function createMigrationLog($failedRecords)
    {
        $summary = [
            'تاریخ انتقال' => now()->format('Y-m-d H:i:s'),
            'جدول مبدا' => $this->oldTableFile->getClientOriginalName(),
            'جدول مقصد' => $this->newTable,
            'تعداد کل رکوردها' => $this->totalRecords,
            'رکوردهای موفق' => $this->successRecords,
            'رکوردهای ناموفق' => $this->failedRecords,
            'نوع پردازش تکراری‌ها' => $this->getDuplicateActionName(),
        ];

        $logContent = "گزارش انتقال داده‌ها\n===================\n\n";
        $logContent .= "خلاصه:\n";
        foreach ($summary as $key => $value) {
            $logContent .= "{$key}: {$value}\n";
        }

        $logContent .= "\nنگاشت فیلدها:\n";
        foreach ($this->fieldMapping as $old => $new) {
            $logContent .= "{$old} => {$new}\n";
        }

        if (!empty($failedRecords)) {
            $logContent .= "\nخطاها:\n";
            foreach ($failedRecords as $error) {
                $logContent .= "رکورد: " . json_encode($error['record'], JSON_UNESCAPED_UNICODE) . "\n";
                $logContent .= "خطا: {$error['error']}\n\n";
            }
        } else {
            $logContent .= "\nهیچ خطایی در حین انتقال رخ نداد.\n";
        }

        try {
            $logFileName = 'migration_log_' . now()->format('Y-m-d_His') . '.txt';
            $logFilePath = 'logs/' . $logFileName;

            Storage::disk('local')->put($logFilePath, $logContent);
            $this->logFilePath = $logFilePath;

            Log::info('Migration log created', ['path' => $logFilePath]);
        } catch (\Exception $e) {
            Log::error('Failed to create migration log', ['error' => $e->getMessage()]);
            $this->logFilePath = null;
        }
    }

    protected function getDuplicateActionName()
    {
        switch ($this->duplicateAction) {
            case 'skip': return 'رد کردن رکوردهای تکراری';
            case 'update': return 'به‌روزرسانی رکوردهای تکراری';
            case 'abort': return 'توقف در صورت تکراری بودن';
            default: return 'نامشخص';
        }
    }

    protected function handleMigrationError(\Exception $e)
    {
        DB::rollBack();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $errorMessage = $this->formatErrorMessage($e->getMessage());
        Log::error('Migration error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());

        $this->dispatch('toast', $errorMessage, ['type' => 'error']);
        $this->isMigrating = false;
    }

    protected function finalizeMigration()
    {
        $this->isMigrating = false;

        $message = "انتقال داده‌ها تکمیل شد. ";
        $message .= "رکوردهای موفق: {$this->successRecords}, ";
        $message .= "رکوردهای ناموفق: {$this->failedRecords}";

        $this->dispatch('toast', $message, ['type' => 'success']);

        if ($this->logFilePath) {
            $this->dispatch('toast', 'فایل لاگ آماده دانلود است.', ['type' => 'info']);
        }
    }

    protected function formatErrorMessage($error)
    {
        $errors = [
            'SQLSTATE[23000]' => 'خطای یکتایی: داده تکراری وجود دارد.',
            'SQLSTATE[22001]' => 'خطای طول رشته: داده بیش از حد مجاز است.',
            'SQLSTATE[22003]' => 'خطای محدوده عددی: مقدار خارج از محدوده مجاز است.',
            'SQLSTATE[22007]' => 'خطای فرمت تاریخ/زمان: مقدار نامعتبر است.',
            'SQLSTATE[23001]' => 'خطای محدودیت کلید خارجی: مقدار ارجاعی وجود ندارد.',
            'SQLSTATE[HY000]' => 'خطای عمومی دیتابیس رخ داده است.',
        ];

        foreach ($errors as $code => $message) {
            if (str_contains($error, $code)) {
                return $message . ' ' . $this->extractSqlErrorDetails($error);
            }
        }

        return 'خطا: ' . $this->extractSqlErrorDetails($error);
    }

    protected function extractSqlErrorDetails($error)
    {
        if (preg_match('/SQLSTATE\[(\w+)\]: (.+?) \(.*?: (.+?)\)/', $error, $matches)) {
            return "جزئیات: {$matches[3]}";
        }

        return $error;
    }

    public function downloadLogFile()
    {
        try {
            if (!$this->logFilePath || !Storage::disk('local')->exists($this->logFilePath)) {
                $this->dispatch('toast', 'فایل لاگ یافت نشد.', ['type' => 'error']);
                Log::error('Log file not found', ['path' => $this->logFilePath]);
                return null;
            }

            $path = Storage::disk('local')->path($this->logFilePath);
            return response()->download($path, 'migration_log.txt', [
                'Content-Type' => 'text/plain; charset=UTF-8',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to download log file', ['error' => $e->getMessage()]);
            $this->dispatch('toast', 'خطا در دانلود فایل لاگ.', ['type' => 'error']);
            return null;
        }
    }

    public function render()
    {
        return view('livewire.admin.panel.tools.data-migration-tool');
    }
}

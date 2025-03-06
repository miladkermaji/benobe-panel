<?php

namespace App\Livewire\Admin\Panel\Tools;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Visibility;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataMigrationTool extends Component
{
    use WithFileUploads;

    public $oldTableFile;
    public $newTable;
    public $oldTableFields = [];
    public $newTableFields = [];
    public $fieldMapping = [];
    public $tables = [];
    public $progress = 0; // برای انتقال داده‌ها
    public $uploadProgress = 0; // برای آپلود فایل
    public $isMigrating = false;
    public $isUploading = false;
    public $searchOld = '';
    public $searchNew = '';
    public $validationErrors = [];
    public $logFilePath = null; // برای ذخیره مسیر فایل لاگ

    protected $rules = [
        'oldTableFile' => 'file|mimes:sql,csv', // فقط نوع فایل رو چک می‌کنیم
        'newTable' => 'required|string',
        'fieldMapping' => 'required|array|min:1',
    ];

    protected $messages = [
        'oldTableFile.file' => 'فایل انتخاب‌شده معتبر نیست.',
        'oldTableFile.mimes' => 'فقط فایل‌های SQL یا CSV مجاز هستند.',
        'newTable.required' => 'لطفاً جدول جدید را انتخاب کنید.',
        'fieldMapping.required' => 'حداقل یک نگاشت فیلد باید انتخاب شود.',
        'fieldMapping.array' => 'نگاشت فیلدها باید به‌صورت آرایه باشد.',
        'fieldMapping.min' => 'حداقل یک نگاشت فیلد باید انتخاب شود.',
    ];

    public function mount()
    {
        $this->tables = DB::select('SHOW TABLES');
        $this->tables = array_map(function ($table) {
            return array_values((array) $table)[0];
        }, $this->tables);
    }

    public function updatedOldTableFile()
    {
        if ($this->oldTableFile) {
            $this->resetErrorBag('oldTableFile');
            $this->validateOnly('oldTableFile');

            $this->isUploading = true;
            $this->uploadProgress = 0;

            $fileSize = $this->oldTableFile->getSize();
            $this->emitUploadProgress($fileSize);

            $this->loadOldTableFields();
            $this->isUploading = false;
        } else {
            $this->addError('oldTableFile', 'لطفاً فایل جدول قدیمی را انتخاب کنید.');
        }
    }

    protected function emitUploadProgress($fileSize)
    {
        for ($i = 0; $i <= 100; $i += 10) {
            $this->uploadProgress = $i;
            Log::info("Upload progress updated: {$this->uploadProgress}%");
            $this->dispatch('uploadProgressUpdated', ['progress' => $this->uploadProgress]);
            usleep(200000);
        }
    }

    public function updatedNewTable()
    {
        $this->validateOnly('newTable');
        $this->loadNewTableFields();
    }

    public function loadOldTableFields()
    {
        if (!$this->oldTableFile) {
            return;
        }

        $file = $this->oldTableFile->getRealPath();
        $extension = $this->oldTableFile->getClientOriginalExtension();

        $this->oldTableFields = [];

        if ($extension === 'csv') {
            $data = array_map('str_getcsv', file($file));
            if (!empty($data)) {
                $headers = array_shift($data);
                $this->oldTableFields = array_map('trim', $headers);
            }
        } elseif ($extension === 'sql') {
            $content = file_get_contents($file);
            preg_match('/CREATE TABLE `.*?` \((.*?)\)\s*(ENGINE|;)/s', $content, $matches);
            if (isset($matches[1])) {
                $fieldsRaw = preg_split('/,\s*(?![^()]*\))/', $matches[1]);
                $this->oldTableFields = array_filter(array_map(function ($field) {
                    $field = trim($field);
                    if (preg_match('/`([^`]+)`/', $field, $fieldMatch)) {
                        return $fieldMatch[1];
                    }
                    return null;
                }, $fieldsRaw));
            }
        }

        if (empty($this->oldTableFields)) {
            $this->dispatch('toast', 'هیچ فیلدی در فایل قدیمی یافت نشد. لطفاً فایل را بررسی کنید.', ['type' => 'error']);
        } else {
            $this->dispatch('fieldsUpdated');
        }
    }

    public function loadNewTableFields()
    {
        if ($this->newTable) {
            $this->newTableFields = Schema::getColumnListing($this->newTable);
            $this->dispatch('fieldsUpdated');
        }
    }

    public function updateFieldMapping($oldField, $newField)
    {
        if ($newField) {
            $this->fieldMapping[$oldField] = $newField;
            $this->validateFieldMapping($oldField, $newField);

            if (!empty($this->validationErrors[$oldField])) {
                $this->dispatch('toast', $this->validationErrors[$oldField], ['type' => 'warning']);
            }
        } else {
            unset($this->fieldMapping[$oldField]);
            unset($this->validationErrors[$oldField]);
        }
    }

    protected function validateFieldMapping($oldField, $newField)
    {
        $file = $this->oldTableFile->getRealPath();
        $extension = $this->oldTableFile->getClientOriginalExtension();
        $sampleRecords = [];

        if ($extension === 'csv') {
            $data = array_map('str_getcsv', file($file));
            $headers = array_shift($data);
            $sampleRecords = [array_combine($headers, $data[0] ?? [])];
        } elseif ($extension === 'sql') {
            $content = file_get_contents($file);
            preg_match_all("/INSERT INTO `.*?` \((.*?)\) VALUES\s*(.*?);/s", $content, $insertMatches);
            if (isset($insertMatches[1][0]) && isset($insertMatches[2][0])) {
                $fieldNames = array_map('trim', explode(',', str_replace('`', '', $insertMatches[1][0])));
                preg_match_all('/\((.*?)\)/', $insertMatches[2][0], $valueMatches);
                $sampleRecords = [array_combine($fieldNames, array_map('trim', str_getcsv($valueMatches[1][0])))];
            }
        }

        if (!empty($sampleRecords)) {
            $sampleValue = $sampleRecords[0][$oldField] ?? null;
            $newFieldType = Schema::getColumnType($this->newTable, $newField);

            switch ($newFieldType) {
                case 'integer':
                    if (!is_numeric($sampleValue)) {
                        $this->validationErrors[$oldField] = "مقدار '$sampleValue' برای '$newField' باید عدد باشد.";
                    }
                    break;
                case 'string':
                    $length = DB::selectOne("SHOW COLUMNS FROM `$this->newTable` WHERE Field = ?", [$newField])->Type;
                    preg_match('/varchar\((\d+)\)/', $length, $matches);
                    $maxLength = $matches[1] ?? 255;
                    if (mb_strlen($sampleValue) > $maxLength) {
                        $this->validationErrors[$oldField] = "مقدار '$sampleValue' برای '$newField' بیش از $maxLength کاراکتر است.";
                    }
                    break;
                case 'enum':
                    $enumValues = DB::selectOne("SHOW COLUMNS FROM `$this->newTable` WHERE Field = ?", [$newField])->Type;
                    preg_match("/enum\((.*?)\)/", $enumValues, $matches);
                    $allowedValues = array_map('trim', explode(',', str_replace("'", "", $matches[1])));
                    if (!in_array($sampleValue, $allowedValues)) {
                        $this->validationErrors[$oldField] = "مقدار '$sampleValue' برای '$newField' باید یکی از " . implode(', ', $allowedValues) . " باشد.";
                    }
                    break;
            }
        }
    }

    public function migrateData()
    {
        $this->validate();

        $this->isMigrating = true;
        $this->progress = 0;

        try {
            $file = $this->oldTableFile->getRealPath();
            $extension = $this->oldTableFile->getClientOriginalExtension();
            $records = [];

            if ($extension === 'csv') {
                $data = array_map('str_getcsv', file($file));
                $headers = array_shift($data);
                foreach ($data as $row) {
                    if (count($headers) === count($row)) {
                        $records[] = array_combine($headers, $row);
                    }
                }
            } elseif ($extension === 'sql') {
                $content = file_get_contents($file);
                preg_match_all("/INSERT INTO `.*?` \((.*?)\) VALUES\s*(.*?);/s", $content, $insertMatches);
                if (isset($insertMatches[1][0]) && isset($insertMatches[2][0])) {
                    $fieldNames = array_map('trim', explode(',', str_replace('`', '', $insertMatches[1][0])));
                    preg_match_all('/\((.*?)\)/', $insertMatches[2][0], $valueMatches);
                    foreach ($valueMatches[1] as $values) {
                        $row = array_map('trim', str_getcsv($values));
                        if (count($fieldNames) === count($row)) {
                            $records[] = array_combine($fieldNames, $row);
                        }
                    }
                }
            }

            if (empty($records)) {
                throw new \Exception('هیچ داده‌ای برای انتقال یافت نشد.');
            }

            $totalRecords = count($records);
            $batchSize = 100;
            $batches = ceil($totalRecords / $batchSize);
            $columnTypes = [];
            $columnLengths = [];
            foreach ($this->newTableFields as $field) {
                $columnTypes[$field] = Schema::getColumnType($this->newTable, $field);
                $columnInfo = DB::selectOne("SHOW COLUMNS FROM `$this->newTable` WHERE Field = ?", [$field]);
                $columnLengths[$field] = $this->getColumnLength($columnInfo->Type);
            }

            DB::beginTransaction();
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $failedRecords = [];

            foreach (array_chunk($records, $batchSize) as $index => $batch) {
                $mappedData = [];
                foreach ($batch as $record) {
                    $newRecord = [];
                    $hasError = false;

                    foreach ($this->fieldMapping as $oldField => $newField) {
                        if (array_key_exists($oldField, $record)) {
                            $value = $record[$oldField];
                            $type = $columnTypes[$newField] ?? 'string';
                            $maxLength = $columnLengths[$newField] ?? 255;
                            $castedValue = $this->castValueToType($value, $type, $newField, $oldField, $maxLength);

                            if ($castedValue === null) {
                                $hasError = true;
                                $failedRecords[] = "فیلد '$oldField' با مقدار '$value' برای '$newField' با نوع '$type' تطابق ندارد.";
                            } else {
                                $newRecord[$newField] = $castedValue;
                            }
                        }
                    }

                    if (!empty($newRecord) && !$hasError) {
                        $mappedData[] = $newRecord;
                    }
                }

                if (!empty($mappedData)) {
                    DB::table($this->newTable)->insert($mappedData);
                }

                $this->progress = round((($index + 1) / $batches) * 100);
                Log::info("Progress updated: {$this->progress}%");
                $this->dispatch('progressUpdated', ['progress' => $this->progress]);
                usleep(500000);
            }

            // ذخیره خطاها توی فایل متنی با UTF-8
            $logContent = implode("\n", array_merge(
                ['گزارش خطاها و لاگ انتقال داده‌ها - ' . now()->format('Y-m-d H:i:s')],
                !empty($failedRecords) ? $failedRecords : ['هیچ خطایی در حین انتقال رخ نداد.']
            ));

            $logFileName = 'migration_log_' . now()->format('Y-m-d_H-i-s') . '.txt';
            $logFilePath = 'public/' . $logFileName;
            $bom = "\xEF\xBB\xBF"; // BOM برای UTF-8
            Storage::put($logFilePath, $bom . $logContent, Visibility::PUBLIC );
            $this->logFilePath = $logFilePath; // مسیر خام رو ذخیره می‌کنیم، نه URL

            Log::info("Log file path set: " . Storage::url($this->logFilePath));

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            DB::commit();

            $this->dispatch('toast', "انتقال داده‌ها با موفقیت انجام شد. $totalRecords رکورد منتقل شدند. " . (count($failedRecords) > 0 ? count($failedRecords) . " رکورد به دلیل خطا رد شدند." : ""), ['type' => 'success']);
            $this->dispatch('toast', "فایل لاگ در حال دانلود است...", ['type' => 'info', 'duration' => 10000]);

            // دانلود مستقیم فایل
            return $this->downloadLogFile();
        } catch (\Exception $e) {
            DB::rollBack();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $errorMessage = $this->formatErrorMessage($e->getMessage());
            Log::error('Error migrating data: ' . $e->getMessage());
            $this->dispatch('toast', $errorMessage, ['type' => 'error']);
        } finally {
            $this->isMigrating = false;
        }
    }

    public function downloadLogFile()
    {
        if (!$this->logFilePath || !Storage::exists($this->logFilePath)) {
            Log::error("Log file not found at: {$this->logFilePath}");
            $this->dispatch('toast', "فایل لاگ یافت نشد. لطفاً دوباره تلاش کنید.", ['type' => 'error']);
            return null;
        }

        $fileName = basename($this->logFilePath);
        $filePath = storage_path('app/' . $this->logFilePath);

        return response()->download($filePath, $fileName, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    protected function getColumnLength($type)
    {
        if (preg_match('/varchar\((\d+)\)/', $type, $matches)) {
            return (int) $matches[1];
        } elseif (preg_match('/char\((\d+)\)/', $type, $matches)) {
            return (int) $matches[1];
        } elseif (preg_match('/text/', $type)) {
            return 65535;
        }
        return 255;
    }

    protected function castValueToType($value, $type, $newField, $oldField, $maxLength = 255)
    {
        if (is_string($value)) {
            $value = trim($value, "'\"");
        }

        $originalValue = $value;
        switch ($type) {
            case 'integer':
                if (!is_numeric($value)) {
                    return null;
                }
                return (int) $value;
            case 'string':
            case 'char':
            case 'varchar':
                $value = (string) $value;
                if (mb_strlen($value) > $maxLength) {
                    $this->dispatch('toast', "مقدار '$originalValue' برای فیلد '$newField' برش خورده شد (بیش از $maxLength کاراکتر).", ['type' => 'warning']);
                    return mb_substr($value, 0, $maxLength);
                }
                return $value;
            case 'enum':
                $enumInfo = DB::selectOne("SHOW COLUMNS FROM `$this->newTable` WHERE Field = ?", [$newField])->Type;
                preg_match("/enum\((.*?)\)/", $enumInfo, $matches);
                $allowedValues = array_map('trim', explode(',', str_replace("'", "", $matches[1])));
                if (!in_array($value, $allowedValues)) {
                    return null;
                }
                return (string) $value;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            case 'float':
            case 'double':
                if (!is_numeric($value)) {
                    return null;
                }
                return (float) $value;
            case 'date':
            case 'datetime':
                try {
                    return date('Y-m-d H:i:s', strtotime($value)) ?: null;
                } catch (\Exception $e) {
                    return null;
                }
            default:
                return (string) $value;
        }
    }

    protected function formatErrorMessage($error)
    {
        if (str_contains($error, 'SQLSTATE[23000]')) {
            return 'خطای کلید خارجی: لطفاً مطمئن شوید که مقادیر parent_id به idهای معتبر اشاره دارند.';
        } elseif (str_contains($error, 'SQLSTATE')) {
            return 'خطای دیتابیس رخ داده است. لطفاً داده‌ها را بررسی کنید.';
        }
        return 'خطا: ' . $error;
    }

    protected function formatSqlError($error)
    {
        if (preg_match('/SQLSTATE\[(\w+)\]: (.+?) \(.*?: (.+?)\)/', $error, $matches)) {
            $code = $matches[1];
            $description = $matches[2];
            $sql = $matches[3];
            return "کد خطا: $code - $description\nجزئیات: $sql";
        }
        return $error;
    }

    public function render()
    {
        return view('livewire.admin.panel.tools.data-migration-tool');
    }
}
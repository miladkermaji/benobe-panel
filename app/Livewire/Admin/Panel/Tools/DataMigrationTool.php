<?php

namespace App\Livewire\Admin\Panel\Tools;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

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
        // فقط وقتی فایل وجود داره، پردازش رو ادامه می‌دیم
        if ($this->oldTableFile) {
            $this->resetErrorBag('oldTableFile'); // پاک کردن خطاهای قبلی
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
            $this->dispatch('uploadProgressUpdated', ['progress' => $this->uploadProgress]);
            usleep(200); // تأخیر 0.2 ثانیه
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
            return; // اگه فایل وجود نداشته باشه، پردازش رو متوقف می‌کنیم
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
            foreach ($this->newTableFields as $field) {
                $columnTypes[$field] = Schema::getColumnType($this->newTable, $field);
            }

            // شروع تراکنش و غیرفعال کردن کلید خارجی
            DB::beginTransaction();
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            foreach (array_chunk($records, $batchSize) as $index => $batch) {
                $mappedData = [];
                foreach ($batch as $record) {
                    $newRecord = [];
                    foreach ($this->fieldMapping as $oldField => $newField) {
                        if (array_key_exists($oldField, $record)) {
                            $value = $record[$oldField];
                            $type = $columnTypes[$newField] ?? 'string';
                            $newRecord[$newField] = $this->castValueToType($value, $type, $newField);
                        }
                    }
                    if (!empty($newRecord)) {
                        $mappedData[] = $newRecord;
                    }
                }

                if (!empty($mappedData)) {
                    DB::table($this->newTable)->insert($mappedData);
                }

                $this->progress = round((($index + 1) / $batches) * 100);
                $this->dispatch('progressUpdated', ['progress' => $this->progress]);
                usleep(200); // تأخیر 0.5 ثانیه برای تست پروگرس بار
            }

            // فعال کردن دوباره کلید خارجی و پایان تراکنش
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            DB::commit();

            $this->dispatch('toast', "انتقال داده‌ها با موفقیت انجام شد. $totalRecords رکورد منتقل شدند.", ['type' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack(); // برگرداندن تغییرات در صورت خطا
            DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // مطمئن شدن از فعال بودن کلید خارجی
            $errorMessage = $this->formatErrorMessage($e->getMessage());
            $this->dispatch('toast', $errorMessage, ['type' => 'error']);
        } finally {
            $this->isMigrating = false;
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

    protected function castValueToType($value, $type, $newField)
    {
        switch ($type) {
            case 'integer':
                return is_numeric($value) ? (int) $value : 0;
            case 'string':
                $lengthInfo = DB::selectOne("SHOW COLUMNS FROM `$this->newTable` WHERE Field = ?", [$newField])->Type;
                preg_match('/varchar\((\d+)\)/', $lengthInfo, $matches);
                $maxLength = $matches[1] ?? 255;
                return mb_substr((string) $value, 0, $maxLength);
            case 'enum':
                $enumInfo = DB::selectOne("SHOW COLUMNS FROM `$this->newTable` WHERE Field = ?", [$newField])->Type;
                preg_match("/enum\((.*?)\)/", $enumInfo, $matches);
                $allowedValues = array_map('trim', explode(',', str_replace("'", "", $matches[1])));
                return in_array($value, $allowedValues) ? $value : $allowedValues[0];
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            case 'float':
            case 'double':
                return is_numeric($value) ? (float) $value : 0.0;
            default:
                return (string) $value;
        }
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
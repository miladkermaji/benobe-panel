<?php

namespace App\Imports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\Log;

class DynamicImport implements ToArray, WithHeadingRow, WithChunkReading
{
    protected $table;
    protected $fieldMapping;
    protected $duplicateAction;
    protected $progressCallback;

    public function __construct($table, $fieldMapping, $duplicateAction, $progressCallback)
    {
        $this->table = $table;
        $this->fieldMapping = $fieldMapping;
        $this->duplicateAction = $duplicateAction;
        $this->progressCallback = $progressCallback;
    }

    public function array(array $rows)
    {
        $total = count($rows);
        $processed = ['success' => 0, 'failed' => 0];

        foreach ($rows as $row) {
            try {
                $mappedRow = $this->mapRow($row);

                foreach ($mappedRow as $key => $value) {
                    if ($value === 'NULL' || $value === '') {
                        $mappedRow[$key] = null;
                    }
                }

                $exists = DB::table($this->table)->where('id', $mappedRow['id'] ?? null)->exists();

                if ($exists && $this->duplicateAction === 'skip') {
                    $processed['success']++;
                    continue;
                } elseif ($exists && $this->duplicateAction === 'update') {
                    DB::table($this->table)->where('id', $mappedRow['id'])->update($mappedRow);
                    $processed['success']++;
                } else {
                    DB::table($this->table)->insert($mappedRow);
                    $processed['success']++;
                }
            } catch (\Exception $e) {
                Log::error('Error inserting row:', [
                    'row' => $mappedRow,
                    'error' => $e->getMessage(),
                ]);
                $processed['failed']++;
            }

            call_user_func($this->progressCallback, $processed, $total);
        }
    }

    protected function mapRow($row)
    {
        $mapped = [];
        foreach ($this->fieldMapping as $oldField => $newField) {
            $mapped[$newField] = $row[$oldField] ?? null;
        }
        return $mapped;
    }

    public function chunkSize(): int
    {
        return 500; // کاهش اندازه چانک برای عملکرد بهتر
    }
}

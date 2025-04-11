<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ProcessMigration implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $filePath;
    protected $table;
    protected $fieldMapping;
    protected $duplicateAction;
    protected $jobId;

    public function __construct($filePath, $table, $fieldMapping, $duplicateAction, $jobId)
    {
        $this->filePath = $filePath;
        $this->table = $table;
        $this->fieldMapping = $fieldMapping;
        $this->duplicateAction = $duplicateAction;
        $this->jobId = $jobId;
    }

    public function handle()
    {
        Log::info('ProcessMigration job started', ['jobId' => $this->jobId]);

        $filePath = Storage::disk('local')->path($this->filePath);
        if (!file_exists($filePath)) {
            Log::error('File not found in job', ['filePath' => $filePath]);
            return;
        }

        Excel::import(new \App\Imports\DynamicImport(
            $this->table,
            $this->fieldMapping,
            $this->duplicateAction,
            function ($processed, $total) {
                $progress = $total > 0 ? (int)(($processed['success'] / $total) * 100) : 0;
                \Livewire\Livewire::dispatch('update-migration-progress', [
                    'success' => $processed['success'],
                    'failed' => $total - $processed['success'],
                    'progress' => $progress,
                ])->to(\App\Livewire\Admin\Panel\Tools\DataMigrationTool::class);
            }
        ), $filePath);

        Log::info('ProcessMigration job completed', ['jobId' => $this->jobId]);
    }
}

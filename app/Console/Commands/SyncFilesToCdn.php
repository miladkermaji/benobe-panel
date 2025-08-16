<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncFilesToCdn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cdn:sync-files {--table= : Specific table to sync} {--force : Force sync even if file exists on CDN} {--dry-run : Show what would be synced without actually syncing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync missing files from local storage to CDN';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Syncing Files to CDN...');
        $this->newLine();

        $table = $this->option('table');
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No files will actually be synced');
            $this->newLine();
        }

        if ($table) {
            $this->syncSpecificTable($table, $force, $dryRun);
        } else {
            $this->syncAllTables($force, $dryRun);
        }

        $this->newLine();
        $this->info('✅ CDN sync completed!');
    }

    /**
     * Sync a specific table
     */
    private function syncSpecificTable($table, $force, $dryRun)
    {
        $this->info("📋 Syncing table: {$table}");
        $this->newLine();

        switch ($table) {
            case 'doctors':
                $this->syncDoctorsTable($force, $dryRun);
                break;
            case 'users':
                $this->syncUsersTable($force, $dryRun);
                break;
            case 'medical_centers':
                $this->syncMedicalCentersTable($force, $dryRun);
                break;
            default:
                $this->error("Unknown table: {$table}");
                $this->info('Available tables: doctors, users, medical_centers');
        }
    }

    /**
     * Sync all relevant tables
     */
    private function syncAllTables($force, $dryRun)
    {
        $this->info('📋 Syncing all relevant tables...');
        $this->newLine();

        $this->syncDoctorsTable($force, $dryRun);
        $this->newLine();

        $this->syncUsersTable($force, $dryRun);
        $this->newLine();

        $this->syncMedicalCentersTable($force, $dryRun);
    }

    /**
     * Sync doctors table files
     */
    private function syncDoctorsTable($force, $dryRun)
    {
        $this->info('👨‍⚕️  Syncing Doctors Table:');

        $doctors = DB::table('doctors')
            ->whereNotNull('profile_photo_path')
            ->get(['id', 'profile_photo_path']);

        if ($doctors->isEmpty()) {
            $this->warn('   No doctors with profile photos found.');
            return;
        }

        $bar = $this->output->createProgressBar($doctors->count());
        $bar->start();

        $synced = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($doctors as $doctor) {
            $result = $this->syncFile($doctor->profile_photo_path, $force, $dryRun);

            switch ($result) {
                case 'synced':
                    $synced++;
                    break;
                case 'skipped':
                    $skipped++;
                    break;
                case 'failed':
                    $failed++;
                    break;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("   Results: {$synced} synced, {$skipped} skipped, {$failed} failed");
    }

    /**
     * Sync users table files
     */
    private function syncUsersTable($force, $dryRun)
    {
        $this->info('👤 Syncing Users Table:');

        $users = DB::table('users')
            ->whereNotNull('avatar')
            ->get(['id', 'avatar']);

        if ($users->isEmpty()) {
            $this->warn('   No users with avatars found.');
            return;
        }

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $synced = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($users as $user) {
            $result = $this->syncFile($user->avatar, $force, $dryRun);

            switch ($result) {
                case 'synced':
                    $synced++;
                    break;
                case 'skipped':
                    $skipped++;
                    break;
                case 'failed':
                    $failed++;
                    break;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("   Results: {$synced} synced, {$skipped} skipped, {$failed} failed");
    }

    /**
     * Sync medical centers table files
     */
    private function syncMedicalCentersTable($force, $dryRun)
    {
        $this->info('🏥 Syncing Medical Centers Table:');

        $medicalCenters = DB::table('medical_centers')
            ->whereNotNull('avatar')
            ->get(['id', 'avatar']);

        if ($medicalCenters->isEmpty()) {
            $this->warn('   No medical centers with avatars found.');
            return;
        }

        $bar = $this->output->createProgressBar($medicalCenters->count());
        $bar->start();

        $synced = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($medicalCenters as $mc) {
            $result = $this->syncFile($mc->avatar, $force, $dryRun);

            switch ($result) {
                case 'synced':
                    $synced++;
                    break;
                case 'skipped':
                    $skipped++;
                    break;
                case 'failed':
                    $failed++;
                    break;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("   Results: {$synced} synced, {$skipped} skipped, {$failed} failed");
    }

    /**
     * Sync a single file to CDN
     */
    private function syncFile($path, $force, $dryRun)
    {
        try {
            // Check if file exists locally
            if (!Storage::disk('public')->exists($path)) {
                Log::warning("File not found locally: {$path}");
                return 'failed';
            }

            // Check if file already exists on CDN (unless force is enabled)
            if (!$force) {
                $cdnUrl = env('FILES_PUBLIC_URL') . '/' . $path;
                if ($this->fileExistsOnCdn($cdnUrl)) {
                    return 'skipped';
                }
            }

            if ($dryRun) {
                return 'synced'; // Pretend it was synced in dry run mode
            }

            // Get local file content
            $localPath = Storage::disk('public')->path($path);
            $fileContent = file_get_contents($localPath);

            // Upload to CDN using FTP disk
            $ftpDisk = Storage::disk('ftp');
            $success = $ftpDisk->put($path, $fileContent);

            if ($success) {
                Log::info("File synced to CDN: {$path}");
                return 'synced';
            } else {
                Log::error("Failed to sync file to CDN: {$path}");
                return 'failed';
            }

        } catch (\Exception $e) {
            Log::error("Error syncing file to CDN: {$path}", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return 'failed';
        }
    }

    /**
     * Check if file exists on CDN
     */
    private function fileExistsOnCdn($url)
    {
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'user_agent' => 'CDN-Sync/1.0',
                    'ignore_errors' => true
                ]
            ]);

            $headers = @get_headers($url, 1, $context);

            if (!$headers) {
                return false;
            }

            $statusLine = $headers[0];
            return strpos($statusLine, '200') !== false;

        } catch (\Exception $e) {
            return false;
        }
    }
}

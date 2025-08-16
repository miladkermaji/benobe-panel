<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CheckCdnSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cdn:check-sync {--table= : Specific table to check} {--limit=10 : Number of records to check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if files are properly synced between local storage and CDN';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking CDN File Synchronization...');
        $this->newLine();

        $table = $this->option('table');
        $limit = (int) $this->option('limit');

        if ($table) {
            $this->checkSpecificTable($table, $limit);
        } else {
            $this->checkAllTables($limit);
        }

        $this->newLine();
        $this->info('✅ CDN sync check completed!');
    }

    /**
     * Check a specific table for file sync issues
     */
    private function checkSpecificTable($table, $limit)
    {
        $this->info("📋 Checking table: {$table}");
        $this->newLine();

        switch ($table) {
            case 'doctors':
                $this->checkDoctorsTable($limit);
                break;
            case 'users':
                $this->checkUsersTable($limit);
                break;
            case 'medical_centers':
                $this->checkMedicalCentersTable($limit);
                break;
            default:
                $this->error("Unknown table: {$table}");
                $this->info('Available tables: doctors, users, medical_centers');
        }
    }

    /**
     * Check all relevant tables
     */
    private function checkAllTables($limit)
    {
        $this->info('📋 Checking all relevant tables...');
        $this->newLine();

        $this->checkDoctorsTable($limit);
        $this->newLine();

        $this->checkUsersTable($limit);
        $this->newLine();

        $this->checkMedicalCentersTable($limit);
    }

    /**
     * Check doctors table for profile photo sync
     */
    private function checkDoctorsTable($limit)
    {
        $this->info('👨‍⚕️  Checking Doctors Table:');

        $doctors = DB::table('doctors')
            ->whereNotNull('profile_photo_path')
            ->limit($limit)
            ->get(['id', 'profile_photo_path']);

        if ($doctors->isEmpty()) {
            $this->warn('   No doctors with profile photos found.');
            return;
        }

        $this->table(
            ['ID', 'Local Path', 'Local Exists', 'CDN URL', 'CDN Status'],
            $doctors->map(function ($doctor) {
                $localExists = Storage::disk('public')->exists($doctor->profile_photo_path) ? '✅' : '❌';
                $cdnUrl = env('FILES_PUBLIC_URL') . '/' . $doctor->profile_photo_path;
                $cdnStatus = $this->checkCdnFile($cdnUrl);

                return [
                    $doctor->id,
                    $doctor->profile_photo_path,
                    $localExists,
                    $cdnUrl,
                    $cdnStatus
                ];
            })
        );
    }

    /**
     * Check users table for avatar sync
     */
    private function checkUsersTable($limit)
    {
        $this->info('👤 Checking Users Table:');

        $users = DB::table('users')
            ->whereNotNull('avatar')
            ->limit($limit)
            ->get(['id', 'avatar']);

        if ($users->isEmpty()) {
            $this->warn('   No users with avatars found.');
            return;
        }

        $this->table(
            ['ID', 'Local Path', 'Local Exists', 'CDN URL', 'CDN Status'],
            $users->map(function ($user) {
                $localExists = Storage::disk('public')->exists($user->avatar) ? '✅' : '❌';
                $cdnUrl = env('FILES_PUBLIC_URL') . '/' . $user->avatar;
                $cdnStatus = $this->checkCdnFile($cdnUrl);

                return [
                    $user->id,
                    $user->avatar,
                    $localExists,
                    $cdnUrl,
                    $cdnStatus
                ];
            })
        );
    }

    /**
     * Check medical centers table for avatar sync
     */
    private function checkMedicalCentersTable($limit)
    {
        $this->info('🏥 Checking Medical Centers Table:');

        $medicalCenters = DB::table('medical_centers')
            ->whereNotNull('avatar')
            ->limit($limit)
            ->get(['id', 'avatar']);

        if ($medicalCenters->isEmpty()) {
            $this->warn('   No medical centers with avatars found.');
            return;
        }

        $this->table(
            ['ID', 'Local Path', 'Local Exists', 'CDN URL', 'CDN Status'],
            $medicalCenters->map(function ($mc) {
                $localExists = Storage::disk('public')->exists($mc->avatar) ? '✅' : '❌';
                $cdnUrl = env('FILES_PUBLIC_URL') . '/' . $mc->avatar;
                $cdnStatus = $this->checkCdnFile($cdnUrl);

                return [
                    $mc->id,
                    $mc->avatar,
                    $localExists,
                    $cdnUrl,
                    $cdnStatus
                ];
            })
        );
    }

    /**
     * Check if a file exists on CDN
     */
    private function checkCdnFile($url)
    {
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'user_agent' => 'CDN-Sync-Check/1.0',
                    'ignore_errors' => true
                ]
            ]);

            $headers = @get_headers($url, 1, $context);

            if (!$headers) {
                return '❌ Connection Failed';
            }

            $statusLine = $headers[0];

            if (strpos($statusLine, '200') !== false) {
                return '✅ Exists';
            } elseif (strpos($statusLine, '404') !== false) {
                return '❌ Not Found';
            } elseif (strpos($statusLine, '403') !== false) {
                return '❌ Forbidden';
            } else {
                return '⚠️  ' . trim($statusLine);
            }
        } catch (\Exception $e) {
            return '❌ Error: ' . $e->getMessage();
        }
    }
}

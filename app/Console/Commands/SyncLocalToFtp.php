<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SyncLocalToFtp extends Command
{
    protected $signature = 'storage:sync-local-to-ftp {--dir= : Relative subdirectory under public to sync (default: all)} {--dry : Dry run without uploading/deleting}';

    protected $description = 'Sync files from local public storage (storage/app/public) to the configured FTP public disk';

    public function handle(): int
    {
        $subdir = trim((string) $this->option('dir'), '/');
        $dryRun = (bool) $this->option('dry');

        $localDisk = Storage::disk('local');
        $ftpDisk   = Storage::disk('public');

        $localRoot = 'public' . ($subdir ? '/' . $subdir : '');
        $remoteRoot = $subdir ? $subdir : '';

        $this->info("Scanning local: {$localRoot}");
        $localFiles = collect($localDisk->allFiles($localRoot));

        $this->info("Scanning remote (FTP): {$remoteRoot}");
        $remoteFiles = collect($ftpDisk->allFiles($remoteRoot));

        $normalize = function (string $path, string $prefix): string {
            if ($prefix === '' || $path === $prefix) {
                return '';
            }
            if (str_starts_with($path, $prefix . '/')) {
                return substr($path, strlen($prefix) + 1);
            }
            return $path;
        };

        $localRelative  = $localFiles->map(fn ($p) => $normalize($p, $localRoot))->filter(fn ($p) => $p !== '');
        $remoteRelative = $remoteFiles->map(fn ($p) => $normalize($p, $remoteRoot))->filter(fn ($p) => $p !== '');

        // Upload or update files that exist locally but not remotely (or changed)
        $uploads = 0;
        foreach ($localRelative as $relativePath) {
            $localPath = $localRoot . '/' . $relativePath;
            $remotePath = ($remoteRoot ? $remoteRoot . '/' : '') . $relativePath;

            $shouldUpload = false;
            if (! $ftpDisk->exists($remotePath)) {
                $shouldUpload = true;
            } else {
                // Compare sizes if possible
                $localSize = $localDisk->size($localPath);
                $remoteSize = null;
                try {
                    $remoteSize = $ftpDisk->size($remotePath);
                } catch (\Throwable $e) {
                    // Some FTP servers may not support size; fallback to upload
                    $remoteSize = -1;
                }
                if ($remoteSize !== $localSize) {
                    $shouldUpload = true;
                }
            }

            if ($shouldUpload) {
                $this->line("Upload: {$relativePath}");
                if (! $dryRun) {
                    $stream = $localDisk->readStream($localPath);
                    $ftpDisk->put($remotePath, $stream);
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                    $uploads++;
                }
            }
        }

        // Optionally delete remote files that no longer exist locally
        $deletions = 0;
        foreach ($remoteRelative as $relativePath) {
            if (! $localRelative->contains($relativePath)) {
                $remotePath = ($remoteRoot ? $remoteRoot . '/' : '') . $relativePath;
                $this->line("Delete: {$relativePath}");
                if (! $dryRun) {
                    $ftpDisk->delete($remotePath);
                    $deletions++;
                }
            }
        }

        $this->info("Done. Uploads: {$uploads}, Deletions: {$deletions}. Dry-run: " . ($dryRun ? 'yes' : 'no'));
        return Command::SUCCESS;
    }
}

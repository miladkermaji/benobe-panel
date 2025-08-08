<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;

class CleanupInvalidJwtTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jwt:cleanup-invalid-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired and invalid JWT tokens from the blacklist';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting JWT token cleanup...');

        try {
            // Get the blacklist manager
            $blacklist = JWTAuth::manager()->getBlacklist();

            if (!$blacklist) {
                $this->warn('Blacklist is not enabled or not available.');
                return 1;
            }

            // Clean up expired tokens from blacklist
            $cleanedCount = $blacklist->clean();

            $this->info("Successfully cleaned up {$cleanedCount} expired tokens from blacklist.");

            Log::info('JWT token cleanup completed', [
                'cleaned_count' => $cleanedCount,
                'timestamp' => now()
            ]);

            return 0;

        } catch (\Exception $e) {
            $this->error('Error during JWT token cleanup: ' . $e->getMessage());

            Log::error('JWT token cleanup failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return 1;
        }
    }
}

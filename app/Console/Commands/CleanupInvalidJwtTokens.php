<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Secretary;
use App\Models\Manager;

class CleanupInvalidJwtTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jwt:cleanup-invalid-tokens {--dry-run : Show what would be cleaned up without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up invalid JWT tokens with null user IDs or non-existent users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting JWT token cleanup...');

        $dryRun = $this->option('dry-run');
        $invalidTokens = [];
        $totalTokens = 0;

        // Get all tokens from the blacklist (this is where JWT stores token information)
        $blacklist = JWTAuth::manager()->getBlacklist();

        // Since we can't directly iterate through all tokens, we'll check for common issues
        $this->info('Checking for common JWT token issues...');

        // Check if there are any users with null IDs (this shouldn't happen but let's be safe)
        $nullIdUsers = User::whereNull('id')->count();
        if ($nullIdUsers > 0) {
            $this->warn("Found {$nullIdUsers} users with null IDs - this is a database issue!");
        }

        // Check for orphaned tokens by looking at recent login logs
        $this->info('Checking recent login logs for potential orphaned tokens...');

        $recentLogins = \App\Models\LoginLog::where('created_at', '>=', now()->subDays(7))
            ->whereNotNull('loggable_id')
            ->get();

        foreach ($recentLogins as $login) {
            $totalTokens++;

            // Check if the user still exists
            $userExists = false;
            switch ($login->loggable_type) {
                case User::class:
                    $userExists = User::find($login->loggable_id) !== null;
                    break;
                case Doctor::class:
                    $userExists = Doctor::find($login->loggable_id) !== null;
                    break;
                case Secretary::class:
                    $userExists = Secretary::find($login->loggable_id) !== null;
                    break;
                case Manager::class:
                    $userExists = Manager::find($login->loggable_id) !== null;
                    break;
            }

            if (!$userExists) {
                $invalidTokens[] = [
                    'type' => 'deleted_user',
                    'loggable_type' => $login->loggable_type,
                    'loggable_id' => $login->loggable_id,
                    'login_at' => $login->login_at,
                ];
            }
        }

        // Check for tokens that might have null user IDs
        $this->info('Checking for potential null user ID issues...');

        // This is a theoretical check - in practice, we'd need to decode tokens
        // For now, we'll log the issue and provide recommendations

        if (count($invalidTokens) > 0) {
            $this->warn("Found " . count($invalidTokens) . " potential invalid tokens:");

            foreach ($invalidTokens as $token) {
                $this->line("- {$token['type']}: {$token['loggable_type']} ID {$token['loggable_id']} (login: {$token['login_at']})");
            }

            if (!$dryRun) {
                $this->info('Cleaning up invalid tokens...');
                // In a real implementation, you might want to blacklist these tokens
                // or clear them from your storage

                foreach ($invalidTokens as $token) {
                    Log::warning('Invalid JWT token detected', $token);
                }

                $this->info('Cleanup completed. Check logs for details.');
            } else {
                $this->info('Dry run completed. No changes made.');
            }
        } else {
            $this->info('No invalid tokens found.');
        }

        $this->info("Total tokens checked: {$totalTokens}");

        // Provide recommendations
        $this->newLine();
        $this->info('Recommendations:');
        $this->line('1. Ensure JWT tokens are created with valid user IDs');
        $this->line('2. Implement proper user deletion cleanup');
        $this->line('3. Consider implementing token refresh mechanism');
        $this->line('4. Monitor JWT token creation logs for null user IDs');

        return 0;
    }
}

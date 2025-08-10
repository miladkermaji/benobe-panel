<?php

namespace App\Console\Commands;

use App\Models\Doctor;
use App\Models\Secretary;
use App\Services\DefaultPermissionsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ApplyDefaultPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:apply-defaults {--user-type= : Specific user type (doctor, secretary, or all)} {--user-id= : Specific user ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Apply default permissions to existing users who do not have permissions set';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userType = $this->option('user-type');
        $userId = $this->option('user-id');
        $defaultPermissionsService = new DefaultPermissionsService();

        $this->info('Starting to apply default permissions...');

        if ($userType === 'doctor' || $userType === 'all' || !$userType) {
            $this->applyToDoctors($defaultPermissionsService, $userId);
        }

        if ($userType === 'secretary' || $userType === 'all' || !$userType) {
            $this->applyToSecretaries($defaultPermissionsService, $userId);
        }

        $this->info('Default permissions application completed!');
    }

    /**
     * Apply default permissions to doctors
     */
    private function applyToDoctors(DefaultPermissionsService $service, $userId = null)
    {
        $this->info('Processing doctors...');

        $query = Doctor::query();

        if ($userId) {
            $query->where('id', $userId);
        }

        $doctors = $query->get();
        $appliedCount = 0;
        $skippedCount = 0;

        foreach ($doctors as $doctor) {
            $this->line("Processing doctor: {$doctor->first_name} {$doctor->last_name} (ID: {$doctor->id})");

            if ($service->applyDefaultPermissionsForDoctor($doctor)) {
                $this->info("✓ Default permissions applied for doctor {$doctor->id}");
                $appliedCount++;
            } else {
                $this->warn("- Doctor {$doctor->id} already has permissions, skipped");
                $skippedCount++;
            }
        }

        $this->info("Doctors processed: {$appliedCount} applied, {$skippedCount} skipped");
    }

    /**
     * Apply default permissions to secretaries
     */
    private function applyToSecretaries(DefaultPermissionsService $service, $userId = null)
    {
        $this->info('Processing secretaries...');

        $query = Secretary::query();

        if ($userId) {
            $query->where('id', $userId);
        }

        $secretaries = $query->get();
        $appliedCount = 0;
        $skippedCount = 0;

        foreach ($secretaries as $secretary) {
            $this->line("Processing secretary: {$secretary->first_name} {$secretary->last_name} (ID: {$secretary->id})");

            if ($service->applyDefaultPermissionsForSecretary($secretary)) {
                $this->info("✓ Default permissions applied for secretary {$secretary->id}");
                $appliedCount++;
            } else {
                $this->warn("- Secretary {$secretary->id} already has permissions, skipped");
                $skippedCount++;
            }
        }

        $this->info("Secretaries processed: {$appliedCount} applied, {$skippedCount} skipped");
    }
}

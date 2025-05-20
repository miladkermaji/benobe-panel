<?php

namespace Database\Seeders;

use App\Models\Clinic;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class ClinicSeeder extends Seeder
{
    public function run(): void
    {
        Log::info('Starting Clinic Seeding...');
        Clinic::factory()->count(100)->create();
        Log::info('Clinic Seeding Completed.');
    }
}

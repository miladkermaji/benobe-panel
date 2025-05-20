<?php

namespace Database\Seeders;

use App\Models\Hospital;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class HospitalSeeder extends Seeder
{
    public function run(): void
    {
        Log::info('Starting Hospital Seeding...');
        Hospital::factory()->count(100)->create();
        Log::info('Hospital Seeding Completed.');
    }
}

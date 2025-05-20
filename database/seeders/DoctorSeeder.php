<?php

namespace Database\Seeders;

use App\Models\Doctor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        Log::info('Starting Doctor Seeding...');
        Doctor::factory()->count(100)->create();
        Log::info('Doctor Seeding Completed.');
    }
}

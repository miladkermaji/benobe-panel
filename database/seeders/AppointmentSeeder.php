<?php


// database/seeders/AppointmentSeeder.php

namespace Database\Seeders;

use App\Models\Appointment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        Log::info('Starting Appointment Seeding...');
        $batchSize = 50; // تولید 50 نوبت در هر دسته
        for ($i = 0; $i < 6; $i++) { // 6 دسته 50 تایی = 300 نوبت
            Appointment::factory()->count($batchSize)->create();
            Log::info("Seeded batch " . ($i + 1) . " of $batchSize appointments.");
            gc_collect_cycles(); // جمع‌آوری زباله برای آزادسازی حافظه
        }
        Log::info('Appointment Seeding Completed.');
    }
}

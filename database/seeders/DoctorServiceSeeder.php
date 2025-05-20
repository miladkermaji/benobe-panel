<?php

namespace Database\Seeders;

use App\Models\DoctorService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DoctorServiceSeeder extends Seeder
{
    public function run(): void
    {
        Log::info('Starting DoctorService Seeding...');
        $batchSize = 50; // تولید 50 سرویس در هر دسته
        for ($i = 0; $i < 6; $i++) { // 6 دسته 50 تایی = 300 سرویس
            Schema::disableForeignKeyConstraints();
            DoctorService::factory()->count($batchSize)->create();
            Schema::enableForeignKeyConstraints();
            Log::info("Seeded batch " . ($i + 1) . " of $batchSize doctor services.");
            gc_collect_cycles(); // جمع‌آوری زباله برای آزادسازی حافظه
        }
        Log::info('DoctorService Seeding Completed.');
    }
}

<?php

namespace Database\Seeders;

use App\Models\Secretary;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SecretarySeeder extends Seeder
{
    public function run(): void
    {
        Log::info('Starting Secretary Seeding...');
        $batchSize = 50; // تولید 50 منشی در هر دسته
        for ($i = 0; $i < 6; $i++) { // 6 دسته 50 تایی = 300 منشی
            Schema::disableForeignKeyConstraints();
            Secretary::factory()->count($batchSize)->create();
            Schema::enableForeignKeyConstraints();
            Log::info("Seeded batch " . ($i + 1) . " of $batchSize secretaries.");
            gc_collect_cycles(); // جمع‌آوری زباله برای آزادسازی حافظه
        }
        Log::info('Secretary Seeding Completed.');
    }
}

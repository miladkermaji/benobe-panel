<?php

namespace Database\Seeders;

use App\Models\Appointment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        Log::info('Starting Appointment Seeding...');
        Appointment::factory()->count(300)->create();
        Log::info('Appointment Seeding Completed.');
    }
}

<?php
namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Admin\Manager;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Database\Seeder;

class DashboardSeeder extends Seeder
{
    public function run()
{
    $doctors = Doctor::factory()->count(5)->create();
    $users = User::factory()->count(10)->create(['user_type' => 0]);
    $secretaries = Secretary::factory()->count(3)->create();
  /*   $managers = Manager::factory()->count(2)->create(); */
    $clinics = Clinic::factory()->count(4)->create();

    Appointment::factory()->count(20)->create([
        'doctor_id' => fn() => $doctors->random()->id,
        'user_id' => fn() => $users->random()->id,
        'clinic_id' => fn() => $clinics->random()->id,
    ]);
}
}

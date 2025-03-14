<?php
namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition()
    {
        return [
            'doctor_id'        => Doctor::factory(),
            'user_id'          => User::factory(),
            'clinic_id'        => Clinic::factory(),
            'appointment_date' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'status'           => $this->faker->randomElement(['pending', 'confirmed', 'completed', 'cancelled']),
            'created_at'       => now(),
            'updated_at'       => now(),
        ];
    }
}

<?php
namespace Database\Factories;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition()
    {
        return [
            'first_name'         => $this->faker->name,
            'specialty_id' => $this->faker->numberBetween(1, 5), // Adjust based on your specialties table
            'email'        => $this->faker->unique()->safeEmail,
            'mobile'        => $this->faker->phoneNumber,
            'created_at'   => now(),
            'updated_at'   => now(),
        ];
    }
}

<?php
namespace Database\Factories;

use App\Models\Clinic;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClinicFactory extends Factory
{
    protected $model = Clinic::class;

    public function definition()
    {
        return [
            'name'       => $this->faker->company . ' Clinic',
            'doctor_id' => $this->faker->numberBetween(1, 3), // Adjust based on your specialties table

            'address'    => $this->faker->address,
            'phone_number'      => $this->faker->phoneNumber,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

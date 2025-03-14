<?php
namespace Database\Factories;

use App\Models\Secretary;
use Illuminate\Database\Eloquent\Factories\Factory;

class SecretaryFactory extends Factory
{
    protected $model = Secretary::class;

    public function definition()
    {
        return [
            'doctor_id' => $this->faker->numberBetween(1, 3), // Adjust based on your specialties table

            'first_name'       => $this->faker->name,
            'email'      => $this->faker->unique()->safeEmail,
            'mobile'      => $this->faker->phoneNumber,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

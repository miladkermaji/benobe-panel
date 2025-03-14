<?php
namespace Database\Factories;

use App\Models\Admin\Manager;
use Illuminate\Database\Eloquent\Factories\Factory;

class ManagerFactory extends Factory
{
    protected $model = Manager::class;

    public function definition()
    {
        return [
            'first_name'       => $this->faker->name,
            'email'      => $this->faker->unique()->safeEmail,
            'mobile'      => $this->faker->phoneNumber,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

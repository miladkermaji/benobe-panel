<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Doctors\OrderVisit;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class OrderVisitSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('fa_IR'); // برای داده‌های فارسی

        // گرفتن چند کاربر، پزشک و کلینیک تصادفی
        $users   = User::inRandomOrder()->limit(10)->get();
        $doctors = Doctor::inRandomOrder()->limit(5)->get();
        $clinics = Clinic::inRandomOrder()->limit(3)->get();

        foreach (range(1, 20) as $index) {
            $user          = $users->random();
            $doctor        = $doctors->random();
            $clinic        = $faker->boolean(50) ? $clinics->random() : null; // 50% شانس null بودن کلینیک
            $paymentMethod = $faker->randomElement(['online', 'manual', 'free']);
            $amount        = $paymentMethod === 'free' ? 0 : $faker->numberBetween(10000, 50000); // مبلغ تصادفی یا 0 برای رایگان

            OrderVisit::create([
                'user_id'          => $user->id,
                'doctor_id'        => $doctor->id,
                'medical_center_id'        => $clinic ? $clinic->id : null,
                'mobile'           => $user->mobile ?? $faker->numerify('09#########'), // موبایل کاربر یا تصادفی
                'payment_date'     => $faker->dateTimeBetween('-1 month', 'now'),
                'bank_ref_id'      => $paymentMethod === 'online' ? $faker->numerify('30##########') : null,
                'tracking_code'    => '03' . $faker->unique()->numerify('##########'),
                'payment_method'   => $paymentMethod,
                'amount'           => $amount,
                'appointment_date' => $faker->dateTimeBetween('now', '+1 month'),
                'appointment_time' => $faker->time('H:i'),
                'center_name'      => $clinic ? $clinic->name : $doctor->full_name,
                'visit_cost'       => $amount > 0 ? $faker->numberBetween(5000, $amount) : 0,
                'service_cost'     => $amount > 0 ? ($amount - $faker->numberBetween(5000, $amount)) : 0,
                'created_at'       => $faker->dateTimeBetween('-1 month', 'now'),
                'updated_at'       => now(),
            ]);
        }
    }
}

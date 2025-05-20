<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class HospitalFactory extends Factory
{
    protected $model = \App\Models\Hospital::class;

    public function definition(): array
    {
        $faker = Faker::create('fa_IR'); // تنظیم لوکال فارسی

        // لیست نمونه‌ای از نام‌های بیمارستان‌ها
        $hospitalNames = [
            'بیمارستان شریعتی', 'بیمارستان امام خمینی', 'بیمارستان میلاد', 'بیمارستان سینا',
            'بیمارستان فیروزگر', 'بیمارستان بهارلو', 'بیمارستان فارابی', 'بیمارستان لقمان',
            'بیمارستان شهدا', 'بیمارستان کودکان'
        ];

        // تولید slug منحصربه‌فرد
        $name = $faker->randomElement($hospitalNames) . ' ' . $faker->city;
        $slug = Str::slug($name . '-' . Str::random(5));

        // روزهای کاری به‌صورت JSON
        $workingDays = $faker->randomElements(['شنبه', 'یک‌شنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنج‌شنبه', 'جمعه'], rand(5, 7));

        // شماره‌های تماس به‌صورت JSON
        $phoneNumbers = [
            $faker->numerify('021########'),
            $faker->numerify('021########')
        ];

        // مدارک نمونه به‌صورت JSON
        $documents = [
            'license' => $faker->url,
            'certificate' => $faker->url
        ];

        return [
            'name' => $name,
            'address' => $faker->address,
            'secretary_phone' => $faker->numerify('021########'),
            'phone_number' => $faker->numerify('021########'),
            'postal_code' => $faker->numerify('##########'),
            'province_id' => $faker->numberBetween(1, 31), // فرض بر وجود 31 استان
            'city_id' => $faker->numberBetween(1, 100), // فرض بر وجود 100 شهر
            'slug' => $faker->unique()->slug,
            'is_main_center' => $faker->boolean(20), // 20% احتمال مرکز اصلی بودن
            'start_time' => $faker->time('H:i', '08:00'), // ساعت شروع تصادفی
            'end_time' => $faker->time('H:i', '20:00'), // ساعت پایان تصادفی
            'description' => $faker->realText(200),
            'latitude' => $faker->latitude(35.0, 36.0), // مختصات جغرافیایی ایران
            'longitude' => $faker->longitude(51.0, 52.0),
            'consultation_fee' => $faker->randomFloat(2, 100000, 1000000), // هزینه بین 100,000 تا 1,000,000 تومان
            'payment_methods' => $faker->randomElement(['cash', 'card', 'online']),
            'is_active' => $faker->boolean(80), // 80% احتمال فعال بودن
            'working_days' => json_encode($workingDays),
            'avatar' => $faker->imageUrl(400, 400, 'hospital'),
            'documents' => json_encode($documents),
            'phone_numbers' => json_encode($phoneNumbers),
            'location_confirmed' => $faker->boolean(70), // 70% احتمال تأیید مکان
            'created_at' => $faker->dateTimeThisYear(),
            'updated_at' => $faker->dateTimeThisYear(),
        ];
    }
}

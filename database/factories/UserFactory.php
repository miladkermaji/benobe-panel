<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class UserFactory extends Factory
{
    protected $model = \App\Models\User::class;

    public function definition(): array
    {
        $faker = Faker::create('fa_IR'); // تنظیم لوکال فارسی

        // لیست نمونه‌ای از نام‌های خانوادگی و نام‌های ایرانی
        $lastNames = ['احمدی', 'محمدی', 'رضایی', 'کاظمی', 'حسینی', 'رحیمی', 'کریمی', 'علوی', 'یزدانی', 'مرادی'];
        $firstNames = ['علی', 'محمد', 'رضا', 'حسین', 'مهدی', 'فاطمه', 'زهرا', 'مریم', 'سارا', 'نرگس'];

        // تولید کد ملی تصادفی (10 رقمی)
        $nationalCode = $faker->numerify('##########');

        // تولید شماره موبایل تصادفی (شروع با 09)
        $mobile = '09' . $faker->numerify('########');

        // تولید slug منحصربه‌فرد
        $slug = Str::slug($faker->firstName . '-' . $faker->lastName . '-' . Str::random(5));

        return [
            'email' => $faker->unique()->safeEmail,
            'mobile' => $faker->unique()->numerify('09#########'),
            'password' => Hash::make('password123'), // رمز عبور ثابت برای تست
            'national_code' => $faker->unique()->numerify('##########'),
            'first_name' => $faker->randomElement($firstNames),
            'last_name' => $faker->randomElement($lastNames),
            'zone_province_id' => $faker->numberBetween(1, 31), // فرض بر وجود 31 استان
            'zone_city_id' => $faker->numberBetween(1, 100), // فرض بر وجود 100 شهر
            'date_of_birth' => $faker->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'sex' => $faker->randomElement(['male', 'female']),
            'slug' => $faker->unique()->slug,
            'profile_photo_path' => $faker->imageUrl(200, 200, 'people'), // آدرس تصویر نمونه
            'address' => $faker->address,
            'email_verified_at' => $faker->optional()->dateTimeThisYear(),
            'mobile_verified_at' => $faker->optional()->dateTimeThisYear(),
            'activation' => $faker->boolean(80), // 80% احتمال فعال بودن
            'no_show_count' => $faker->numberBetween(0, 5),
            'activation_date' => $faker->optional()->dateTimeThisYear(),
            'user_type' => $faker->randomElement([0, 1]), // 0: کاربر عادی، 1: ادمین
            'status' => $faker->randomElement([0, 1]),
            'remember_token' => Str::random(10),
            'created_at' => $faker->dateTimeThisYear(),
            'updated_at' => $faker->dateTimeThisYear(),
        ];
    }
}

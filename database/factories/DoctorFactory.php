<?php

namespace Database\Factories;

use App\Models\Hospital;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class DoctorFactory extends Factory
{
    protected $model = \App\Models\Doctor::class;

    public function definition(): array
    {
        $faker = Faker::create('fa_IR'); // تنظیم لوکال فارسی

        // لیست نمونه‌ای از نام‌ها و نام‌های خانوادگی
        $firstNames = ['علی', 'محمد', 'رضا', 'حسین', 'مهدی', 'فاطمه', 'زهرا', 'مریم', 'سارا', 'نرگس'];
        $lastNames = ['احمدی', 'محمدی', 'رضایی', 'کاظمی', 'حسینی', 'رحیمی', 'کریمی', 'علوی', 'یزدانی', 'مرادی'];

        $firstName = $faker->randomElement($firstNames);
        $lastName = $faker->randomElement($lastNames);
        $displayName = "دکتر {$firstName} {$lastName}";

        // تولید slug منحصربه‌فرد
        $slug = Str::slug($displayName . '-' . Str::random(5));

        return [
            'hospital_id' => Hospital::inRandomOrder()->first()->id ?? null, // انتخاب تصادفی از بیمارستان‌ها
            'uuid' => null, // در مایگریشن به‌صورت خودکار تنظیم می‌شود
            'first_name' => $firstName,
            'last_name' => $lastName,
            'display_name' => $displayName,
            'date_of_birth' => $faker->dateTimeBetween('-60 years', '-25 years')->format('Y-m-d'),
            'sex' => $faker->randomElement(['male', 'female', 'other']),
            'mobile' => $faker->unique()->numerify('09#########'),
            'email' => $faker->unique()->safeEmail,
            'alternative_mobile' => $faker->optional()->numerify('09#########'),
            'national_code' => $faker->unique()->numerify('##########'),
            'password' => Hash::make('password123'), // رمز عبور ثابت برای تست
            'static_password_enabled' => $faker->boolean(20),
            'two_factor_secret' => null, // در صورت نیاز می‌توانید مقدار تولید کنید
            'two_factor_secret_enabled' => $faker->boolean(10),
            'two_factor_confirmed_at' => $faker->optional()->dateTimeThisYear(),
            'license_number' => $faker->unique()->numerify('MD#######'),
            'academic_degree_id' => $faker->numberBetween(1, 5), // فرض بر وجود 5 درجه تحصیلی
            'specialty_id' => $faker->numberBetween(1, 10), // فرض بر وجود 10 تخصص
            'medical_system_code_type_id' => $faker->numberBetween(1, 3), // فرض بر وجود 3 نوع کد نظام پزشکی
            'province_id' => $faker->numberBetween(1, 31), // فرض بر وجود 31 استان
            'city_id' => $faker->numberBetween(1, 100), // فرض بر وجود 100 شهر
            'address' => $faker->address,
            'postal_code' => $faker->numerify('##########'),
            'slug' => $faker->unique()->slug,
            'profile_photo_path' => $faker->imageUrl(200, 200, 'people'),
            'bio' => $faker->realText(150),
            'description' => $faker->realText(200),
            'is_active' => $faker->boolean(80), // 80% احتمال فعال بودن
            'is_verified' => $faker->boolean(50), // 50% احتمال تأیید
            'profile_completed' => $faker->boolean(70), // 70% احتمال تکمیل پروفایل
            'views_count' => $faker->numberBetween(0, 1000),
            'status' => $faker->randomElement([0, 1, 2]), // فرض بر وجود 3 وضعیت
            'api_token' => $faker->unique()->sha1,
            'remember_token' => Str::random(10),
            'mobile_verified_at' => $faker->optional()->dateTimeThisYear(),
            'email_verified_at' => $faker->optional()->dateTimeThisYear(),
            'last_login_at' => $faker->optional()->dateTimeThisYear(),
            'created_at' => $faker->dateTimeThisYear(),
            'updated_at' => $faker->dateTimeThisYear(),
        ];
    }
}

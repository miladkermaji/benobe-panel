<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Clinic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class SecretaryFactory extends Factory
{
    protected $model = \App\Models\Secretary::class;

    public function definition(): array
    {
        $faker = Faker::create('fa_IR'); // تنظیم لوکال فارسی

        // لیست نمونه‌ای از نام‌ها و نام‌های خانوادگی
        $firstNames = ['علی', 'محمد', 'رضا', 'حسین', 'مهدی', 'فاطمه', 'زهرا', 'مریم', 'سارا', 'نرگس'];
        $lastNames = ['احمدی', 'محمدی', 'رضایی', 'کاظمی', 'حسینی', 'رحیمی', 'کریمی', 'علوی', 'یزدانی', 'مرادی'];

        $firstName = $faker->randomElement($firstNames);
        $lastName = $faker->randomElement($lastNames);
        $displayName = "{$firstName} {$lastName}";

        // انتخاب تصادفی پزشک و مطب
        $doctor = Doctor::inRandomOrder()->value('id') ?? null;
        $clinic = Clinic::inRandomOrder()->value('id') ?? null;

        // تولید شماره موبایل و کد ملی منحصربه‌فرد برای ترکیب doctor_id و medical_center_id
        $mobile = $faker->unique()->numerify('09########');
        $nationalCode = $faker->unique()->numerify('##########');

        // تولید slug منحصربه‌فرد
        $slug = Str::slug($displayName . '-' . Str::random(5));

        return [
            'doctor_id' => $doctor,
            'medical_center_id' => $clinic,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'display_name' => $displayName,
            'date_of_birth' => $faker->dateTimeBetween('-50 years', '-20 years')->format('Y-m-d'),
            'gender' => $faker->randomElement(['male', 'female']),
            'mobile' => $mobile,
            'email' => $faker->unique()->safeEmail,
            'alternative_mobile' => $faker->optional()->numerify('09########'),
            'national_code' => $nationalCode,
            'password' => Hash::make('password123'), // رمز عبور ثابت برای تست
            'static_password_enabled' => $faker->boolean(20),
            'two_factor_secret' => null, // در صورت نیاز می‌توانید مقدار تولید کنید
            'two_factor_secret_enabled' => $faker->boolean(10),
            'two_factor_confirmed_at' => $faker->optional()->dateTimeThisYear(),
            'province_id' => $faker->numberBetween(1, 31), // فرض بر وجود 31 استان
            'city_id' => $faker->numberBetween(1, 100), // فرض بر وجود 100 شهر
            'address' => $faker->address,
            'postal_code' => $faker->numerify('##########'),
            'slug' => $faker->unique()->slug,
            'profile_photo_path' => $faker->imageUrl(200, 200, 'people'),
            'bio' => $faker->optional(0.3)->realText(100),
            'description' => $faker->optional(0.3)->realText(150),
            'is_active' => $faker->boolean(80), // 80% احتمال فعال بودن
            'is_verified' => $faker->boolean(50), // 50% احتمال تأیید
            'profile_completed' => $faker->boolean(70), // 70% احتمال تکمیل پروفایل
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

<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Clinic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;

class DoctorServiceFactory extends Factory
{
    protected $model = \App\Models\DoctorService::class;

    public function definition(): array
    {
        $faker = Faker::create('fa_IR'); // تنظیم لوکال فارسی

        // لیست نمونه‌ای از نام‌های خدماتی پزشکی
        $serviceNames = [
            'ویزیت عمومی', 'ویزیت تخصصی قلب', 'جراحی عمومی', 'آزمایش خون',
            'سونوگرافی', 'رادیولوژی', 'فیزیوتراپی', 'مشاوره تغذیه',
            'درمان دیابت', 'جراحی ارتوپدی', 'چکاپ کامل', 'درمان بیماری‌های داخلی'
        ];

        return [
            'doctor_id' => Doctor::inRandomOrder()->value('id') ?? null, // انتخاب تصادفی پزشک
            'clinic_id' => Clinic::inRandomOrder()->value('id') ?? null, // انتخاب تصادفی مطب
            'insurance_id' => $faker->optional(0.7)->numberBetween(1, 10), // فرض بر وجود 10 بیمه
            'name' => $faker->randomElement($serviceNames),
            'description' => $faker->optional(0.5)->realText(150), // توضیحات با 50% احتمال
            'duration' => $faker->numberBetween(15, 120), // مدت زمان بین 15 تا 120 دقیقه
            'price' => $faker->randomFloat(2, 50000, 2000000), // قیمت بین 50,000 تا 2,000,000 تومان
            'discount' => $faker->optional(0.3)->randomFloat(2, 10000, 500000), // تخفیف با 30% احتمال
            'status' => $faker->randomElement([0, 1, 2]), // فرض بر وجود 3 وضعیت
            'parent_id' => $faker->optional(0.2)->numberBetween(1, 50), // 20% احتمال داشتن خدمت مادر
            'created_at' => $faker->dateTimeThisYear(),
            'updated_at' => $faker->dateTimeThisYear(),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\User;
use App\Models\Clinic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class AppointmentFactory extends Factory
{
    protected $model = \App\Models\Appointment::class;

    public function definition(): array
    {
        $faker = Faker::create('fa_IR'); // تنظیم لوکال فارسی

        // انتخاب تصادفی از جداول مرتبط
        $doctor = Doctor::inRandomOrder()->first();
        $patient = User::inRandomOrder()->first();
        $clinic = Clinic::inRandomOrder()->first();

        // تولید کد رهگیری منحصربه‌فرد
        $trackingCode = 'APPT-' . Str::random(8);

        // تاریخ و زمان نوبت (در 6 ماه آینده)
        $appointmentDate = $faker->dateTimeBetween('now', '+6 months')->format('Y-m-d');
        $appointmentTime = $faker->time('H:i', '18:00'); // زمان تصادفی بین 8 صبح تا 6 عصر

        return [
            'doctor_id' => $doctor ? $doctor->id : null, // انتخاب تصادفی پزشک
            'patient_id' => $patient ? $patient->id : null, // انتخاب تصادفی بیمار
            'insurance_id' => $faker->optional(0.7)->numberBetween(1, 10), // فرض بر وجود 10 بیمه (70% احتمال)
            'clinic_id' => $clinic ? $clinic->id : null, // انتخاب تصادفی مطب
            'consultation_type' => $faker->randomElement(['general', 'specialized', 'emergency']),
            'priority' => $faker->randomElement(['low', 'medium', 'high']),
            'payment_status' => $faker->randomElement(['pending', 'paid', 'unpaid']),
            'appointment_type' => $faker->randomElement(['in_person', 'online', 'phone']),
            'appointment_date' => $appointmentDate,
            'appointment_time' => $appointmentTime,
            'reserved_at' => $faker->optional()->dateTimeThisYear(),
            'confirmed_at' => $faker->optional(0.8)->dateTimeThisYear(), // 80% احتمال تأیید
            'status' => $faker->randomElement(['scheduled', 'cancelled', 'attended', 'missed', 'pending_review']),
            'attendance_status' => $faker->optional()->randomElement(['attended', 'missed', 'cancelled']),
            'notes' => $faker->optional()->realText(100),
            'description' => $faker->optional()->realText(200),
            'title' => $faker->optional()->sentence(3),
            'tracking_code' => $faker->unique()->numerify('APPT-########'),
            'max_appointments' => $faker->optional()->numberBetween(1, 10),
            'fee' => $faker->randomFloat(2, 50000, 500000), // هزینه بین 50,000 تا 500,000 تومان
            'final_price' => $faker->randomFloat(2, 50000, 1000000), // قیمت نهایی بین 50,000 تا 1,000,000 تومان
            'appointment_category' => $faker->randomElement(['initial', 'follow_up']),
            'location' => $faker->optional()->address,
            'notification_sent' => $faker->boolean(60), // 60% احتمال ارسال اعلان
            'created_at' => $faker->dateTimeThisYear(),
            'updated_at' => $faker->dateTimeThisYear(),
        ];
    }
}

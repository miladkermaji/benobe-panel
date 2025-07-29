<?php

namespace Database\Seeders;

use App\Models\MedicalCenter;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class MedicalCenterSeeder extends Seeder
{
    public function run(): void
    {
        // دریافت اولین استان و شهر برای استفاده در نمونه
        $province = Zone::where('level', 1)->first();
        $city = Zone::where('level', 2)->first();

        // ایجاد یک مرکز درمانی نمونه با تایپ policlinic
        MedicalCenter::create([
            'name' => 'پلی‌کلینیک نمونه',
            'title' => 'پلی‌کلینیک نمونه',
            'address' => 'تهران، خیابان ولیعصر',
            'secretary_phone' => '02112345678',
            'phone_number' => '02112345678',
            'postal_code' => '1234567890',
            'province_id' => $province ? $province->id : null,
            'city_id' => $city ? $city->id : null,
            'is_main_center' => false,
            'start_time' => '08:00:00',
            'end_time' => '18:00:00',
            'description' => 'پلی‌کلینیک نمونه برای تست',
            'latitude' => 35.6892,
            'longitude' => 51.3890,
            'consultation_fee' => 500000,
            'payment_methods' => 'cash',
            'Center_tariff_type' => 'governmental',
            'Daycare_centers' => 'no',
            'type' => 'policlinic',
            'is_active' => true,
            'working_days' => ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday'],
            'specialty_ids' => [1, 2, 3], // نمونه تخصص‌ها
            'insurance_ids' => [1, 2], // نمونه بیمه‌ها
            'service_ids' => [1, 2, 3], // نمونه خدمات
            'location_confirmed' => true,
            'slug' => 'sample-policlinic',
            'average_rating' => 4.5,
            'reviews_count' => 10,
            'recommendation_percentage' => 85,
        ]);

        $this->command->info('Medical Center Seeder completed successfully!');
    }
}

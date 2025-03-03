<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DoctorsSeeder extends Seeder
{
    public function run(): void
    {
        $doctors = [
            [
                'uuid' => null, // بعد از درج با DB::statement پر می‌شه
                'first_name' => 'علی',
                'last_name' => 'رضایی',
                'display_name' => 'دکتر علی رضایی',
                'date_of_birth' => Carbon::create(1980, 5, 15)->toDateString(),
                'sex' => 'male',
                'mobile' => '09123456789',
                'email' => 'ali.rezaei@example.com',
                'alternative_mobile' => '09351234567',
                'national_code' => '1234567890',
                'password' => bcrypt('password123'),
                'static_password_enabled' => false,
                'two_factor_secret' => null,
                'two_factor_secret_enabled' => false,
                'two_factor_confirmed_at' => null,
                'license_number' => 'DR12345',
                'academic_degree_id' => 1,
                'specialty_id' => 1,
                'medical_system_code_type_id' => 1,
                'province_id' => 1,
                'city_id' => 2,
                'address' => 'تهران، خیابان آزادی، پلاک 100',
                'postal_code' => '1234567890',
                'slug' => Str::slug('دکتر-علی-رضایی'),
                'profile_photo_path' => null,
                'bio' => 'متخصص داخلی با 15 سال تجربه',
                'description' => 'دکتر علی رضایی با تجربه در زمینه داخلی',
                'is_active' => true,
                'is_verified' => true,
                'profile_completed' => true,
                'status' => 1,
                'api_token' => Str::random(80),
                'remember_token' => Str::random(10),
                'mobile_verified_at' => Carbon::now(),
                'email_verified_at' => Carbon::now(),
                'last_login_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'uuid' => null,
                'first_name' => 'مریم',
                'last_name' => 'احمدی',
                'display_name' => 'دکتر مریم احمدی',
                'date_of_birth' => Carbon::create(1975, 8, 22)->toDateString(),
                'sex' => 'female',
                'mobile' => '09198765432',
                'email' => 'maryam.ahmadi@example.com',
                'alternative_mobile' => null,
                'national_code' => '0987654321',
                'password' => bcrypt('doctor456'),
                'static_password_enabled' => false,
                'two_factor_secret' => null,
                'two_factor_secret_enabled' => false,
                'two_factor_confirmed_at' => null,
                'license_number' => 'DR67890',
                'academic_degree_id' => 2,
                'specialty_id' => 2,
                'medical_system_code_type_id' => 2,
                'province_id' => 1,
                'city_id' => 3,
                'address' => 'تهران، خیابان ولیعصر، پلاک 50',
                'postal_code' => '0987654321',
                'slug' => Str::slug('دکتر-مریم-احمدی'),
                'profile_photo_path' => null,
                'bio' => 'متخصص قلب و عروق با 20 سال تجربه',
                'description' => 'دکتر مریم احمدی متخصص قلب و عروق',
                'is_active' => true,
                'is_verified' => false,
                'profile_completed' => false,
                'status' => 0,
                'api_token' => Str::random(80),
                'remember_token' => Str::random(10),
                'mobile_verified_at' => null,
                'email_verified_at' => null,
                'last_login_at' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($doctors as $doctor) {
            DB::table('doctors')->insert($doctor);
        }

        DB::statement('UPDATE doctors SET uuid = CONCAT("DR-", id) WHERE uuid IS NULL');
    }
}
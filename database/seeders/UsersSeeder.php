<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'email' => 'user1@example.com',
                'mobile' => '09123456789',
                'password' => bcrypt('password123'),
                'national_code' => '1234567890',
                'first_name' => 'علی',
                'last_name' => 'محمدی',
                'date_of_birth' => Carbon::create(1990, 3, 10)->toDateString(),
                'sex' => 'male',
                'slug' => Str::slug('علی-محمدی'),
                'profile_photo_path' => null,
                'address' => 'تهران، خیابان انقلاب، پلاک 20',
                'email_verified_at' => Carbon::now(),
                'mobile_verified_at' => Carbon::now(),
                'activation' => 1,
                'no_show_count' => 0,
                'activation_date' => Carbon::now(),
                'user_type' => 0, // کاربر عادی
                'status' => 1,
                'current_team_id' => null,
                'remember_token' => Str::random(10),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'email' => 'user2@example.com',
                'mobile' => '09198765432',
                'password' => bcrypt('user456'),
                'national_code' => '0987654321',
                'first_name' => 'زهرا',
                'last_name' => 'احمدی',
                'date_of_birth' => Carbon::create(1985, 7, 25)->toDateString(),
                'sex' => 'female',
                'slug' => Str::slug('زهرا-احمدی'),
                'profile_photo_path' => null,
                'address' => 'تهران، خیابان ولیعصر، پلاک 150',
                'email_verified_at' => null,
                'mobile_verified_at' => null,
                'activation' => 0,
                'no_show_count' => 1,
                'activation_date' => null,
                'user_type' => 0, // کاربر عادی
                'status' => 0,
                'current_team_id' => null,
                'remember_token' => Str::random(10),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->insert($user);
        }
    }
}
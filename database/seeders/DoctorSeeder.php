<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        // خواندن محتوای فایل JSON
        $json = File::get(storage_path('app/doctors.json'));
        $data = json_decode($json, true);

        foreach ($data as $doctorData) {
            // بررسی اینکه آیا دکتر قبلاً وجود دارد یا نه
            $existingDoctor = DB::table('doctors')
                ->where('email', $doctorData['email'])
                ->orWhere('mobile', $doctorData['mobile'])
                ->first();

            if (!$existingDoctor) {
                // تبدیل جنسیت از فرمت قدیمی به جدید
                $sex = $this->convertSex($doctorData['sex'] ?? 'male');

                // ایجاد slug از نام نمایشی
                $slug = Str::slug($doctorData['display_name'] ?? $doctorData['first_name'] . ' ' . $doctorData['last_name']);

                // ایجاد UUID
                $uuid = 'DR-' . $doctorData['id'];

                // هش کردن پسورد
                $hashedPassword = Hash::make($doctorData['password'] ?? 'password123');

                DB::table('doctors')->insert([
                    'id' => $doctorData['id'],
                    'uuid' => $uuid,
                    'first_name' => $doctorData['first_name'] ?? null,
                    'last_name' => $doctorData['last_name'] ?? null,
                    'display_name' => $doctorData['display_name'] ?? null,
                    'sex' => $sex,
                    'mobile' => $doctorData['mobile'] ?? null,
                    'email' => $doctorData['email'] ?? null,
                    'national_code' => $doctorData['national_code'] ?? null,
                    'password' => $hashedPassword,
                    'address' => $doctorData['address'] ?? null,
                    'profile_photo_path' => $doctorData['profile_photo_path'] ?? null,
                    'slug' => $slug,
                    'is_active' => true,
                    'is_verified' => true,
                    'profile_completed' => true,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->command->info("Doctor {$doctorData['display_name']} created successfully.");
            } else {
                $this->command->info("Doctor {$doctorData['display_name']} already exists, skipping...");
            }
        }
    }

    /**
     * تبدیل جنسیت از فرمت قدیمی به جدید
     */
    private function convertSex($oldSex): string
    {
        return match ($oldSex) {
            'male' => 'male',
            'famale' => 'female', // تصحیح غلط املایی
            'female' => 'female',
            default => 'male',
        };
    }
}

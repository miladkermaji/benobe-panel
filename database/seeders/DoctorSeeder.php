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

        $totalDoctors = count($data);
        $createdCount = 0;
        $skippedCount = 0;
        $errorCount = 0;
        $skippedDoctors = [];

        $this->command->info("Total doctors in JSON: {$totalDoctors}");

        foreach ($data as $index => $doctorData) {
            try {
                // بررسی اینکه آیا دکتر قبلاً وجود دارد یا نه
                $existingDoctor = null;
                $skipReason = '';

                if (!empty($doctorData['email'])) {
                    $existingDoctor = DB::table('doctors')->where('email', $doctorData['email'])->first();
                    if ($existingDoctor) {
                        $skipReason = "Email already exists: {$doctorData['email']}";
                    }
                }

                if (!$existingDoctor && !empty($doctorData['mobile'])) {
                    $existingDoctor = DB::table('doctors')->where('mobile', $doctorData['mobile'])->first();
                    if ($existingDoctor) {
                        $skipReason = "Mobile already exists: {$doctorData['mobile']}";
                    }
                }

                if (!$existingDoctor) {
                    // تبدیل جنسیت از فرمت قدیمی به جدید
                    $sex = $this->convertSex($doctorData['sex'] ?? 'male');

                    // ایجاد slug از نام نمایشی
                    $displayName = $doctorData['display_name'] ?? $doctorData['first_name'] . ' ' . $doctorData['last_name'];
                    $slug = Str::slug($displayName ?: 'doctor-' . $doctorData['id']);

                    // ایجاد UUID
                    $uuid = 'DR-' . $doctorData['id'];

                    // هش کردن پسورد
                    $hashedPassword = Hash::make($doctorData['password'] ?? 'password123');

                    // ایجاد email منحصر به فرد اگر null باشد
                    $email = $doctorData['email'];
                    if (empty($email)) {
                        $email = 'doctor' . $doctorData['id'] . '@benobe.ir';
                    }

                    // ایجاد mobile منحصر به فرد اگر null باشد
                    $mobile = $doctorData['mobile'];
                    if (empty($mobile)) {
                        $mobile = '0910000' . str_pad($doctorData['id'], 4, '0', STR_PAD_LEFT);
                    }

                    DB::table('doctors')->insert([
                        'id' => $doctorData['id'],
                        'uuid' => $uuid,
                        'first_name' => $doctorData['first_name'] ?? null,
                        'last_name' => $doctorData['last_name'] ?? null,
                        'display_name' => $displayName,
                        'sex' => $sex,
                        'mobile' => $mobile,
                        'email' => $email,
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

                    $createdCount++;
                    if ($createdCount % 50 == 0) {
                        $this->command->info("Created {$createdCount} doctors so far...");
                    }
                } else {
                    $skippedCount++;
                    $skippedDoctors[] = [
                        'id' => $doctorData['id'],
                        'display_name' => $doctorData['display_name'] ?? 'Unknown',
                        'email' => $doctorData['email'] ?? 'null',
                        'mobile' => $doctorData['mobile'] ?? 'null',
                        'reason' => $skipReason
                    ];

                    if ($skippedCount % 50 == 0) {
                        $this->command->info("Skipped {$skippedCount} doctors so far...");
                    }
                }
            } catch (\Exception $e) {
                $errorCount++;
                $this->command->error("Error creating doctor ID {$doctorData['id']}: " . $e->getMessage());
            }
        }

        $this->command->info("Seeding completed!");
        $this->command->info("Total doctors in JSON: {$totalDoctors}");
        $this->command->info("Created: {$createdCount}");
        $this->command->info("Skipped (already exists): {$skippedCount}");
        $this->command->info("Errors: {$errorCount}");

        if ($skippedCount > 0) {
            $this->command->info("\nSkipped doctors details:");
            foreach (array_slice($skippedDoctors, 0, 10) as $skipped) {
                $this->command->info("ID: {$skipped['id']}, Name: {$skipped['display_name']}, Email: {$skipped['email']}, Mobile: {$skipped['mobile']}, Reason: {$skipped['reason']}");
            }
            if (count($skippedDoctors) > 10) {
                $this->command->info("... and " . (count($skippedDoctors) - 10) . " more");
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

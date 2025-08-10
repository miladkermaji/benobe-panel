<?php

namespace Database\Seeders;

use App\Models\Story;
use App\Models\User;
use App\Models\Doctor;
use App\Models\MedicalCenter;
use App\Models\Manager;
use Illuminate\Database\Seeder;

class StorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ایجاد پوشه‌های مورد نیاز
        $this->createStorageDirectories();

        // تولید استوری‌های نمونه برای پزشکان
        $this->createDoctorStories();

        // تولید استوری‌های نمونه برای مراکز درمانی
        $this->createMedicalCenterStories();

        // تولید استوری‌های نمونه برای مدیران
        $this->createManagerStories();

        // تولید استوری‌های نمونه برای کاربران
        $this->createUserStories();
    }

    /**
     * ایجاد پوشه‌های مورد نیاز برای ذخیره فایل‌ها
     */
    private function createStorageDirectories()
    {
        $directories = [
            'public/storage/stories/images',
            'public/storage/stories/videos',
            'public/storage/stories/thumbnails',
        ];

        foreach ($directories as $directory) {
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
        }
    }

    /**
     * تولید استوری‌های نمونه برای پزشکان
     */
    private function createDoctorStories()
    {
        $doctors = Doctor::active()->take(10)->get();

        foreach ($doctors as $doctor) {
            // تولید 2-5 استوری برای هر پزشک
            $storyCount = rand(2, 5);

            for ($i = 0; $i < $storyCount; $i++) {
                $isLive = rand(1, 10) === 1; // 10% احتمال زنده بودن

                Story::factory()->create([
                    'doctor_id' => $doctor->id,
                    'title' => $this->getDoctorStoryTitle($doctor),
                    'description' => $this->getDoctorStoryDescription($doctor),
                    'is_live' => $isLive,
                    'live_start_time' => $isLive ? now()->subMinutes(rand(10, 60)) : null,
                    'live_end_time' => $isLive ? now()->addHours(rand(1, 3)) : null,
                    'order' => $i,
                ]);
            }
        }
    }

    /**
     * تولید استوری‌های نمونه برای مراکز درمانی
     */
    private function createMedicalCenterStories()
    {
        $medicalCenters = MedicalCenter::active()->take(5)->get();

        foreach ($medicalCenters as $medicalCenter) {
            // تولید 3-6 استوری برای هر مرکز درمانی
            $storyCount = rand(3, 6);

            for ($i = 0; $i < $storyCount; $i++) {
                Story::factory()->create([
                    'medical_center_id' => $medicalCenter->id,
                    'title' => $this->getMedicalCenterStoryTitle($medicalCenter),
                    'description' => $this->getMedicalCenterStoryDescription($medicalCenter),
                    'order' => $i,
                ]);
            }
        }
    }

    /**
     * تولید استوری‌های نمونه برای مدیران
     */
    private function createManagerStories()
    {
        $managers = Manager::take(3)->get();

        foreach ($managers as $manager) {
            // تولید 1-3 استوری برای هر مدیر
            $storyCount = rand(1, 3);

            for ($i = 0; $i < $storyCount; $i++) {
                Story::factory()->create([
                    'manager_id' => $manager->id,
                    'title' => $this->getManagerStoryTitle($manager),
                    'description' => $this->getManagerStoryDescription($manager),
                    'order' => $i,
                ]);
            }
        }
    }

    /**
     * تولید استوری‌های نمونه برای کاربران
     */
    private function createUserStories()
    {
        $users = User::take(20)->get();

        foreach ($users as $user) {
            // تولید 1-2 استوری برای هر کاربر
            $storyCount = rand(1, 2);

            for ($i = 0; $i < $storyCount; $i++) {
                Story::factory()->create([
                    'user_id' => $user->id,
                    'title' => $this->getUserStoryTitle($user),
                    'description' => $this->getUserStoryDescription($user),
                    'order' => $i,
                ]);
            }
        }
    }

    /**
     * تولید عنوان استوری برای پزشک
     */
    private function getDoctorStoryTitle($doctor)
    {
        $titles = [
            "مشاوره رایگان با {$doctor->full_name}",
            "نکات مهم سلامتی از {$doctor->full_name}",
            "سوالات متداول بیماران",
            "توصیه‌های پزشکی",
            "آخرین اخبار پزشکی",
            "راهنمای سلامت",
        ];

        return $titles[array_rand($titles)];
    }

    /**
     * تولید توضیحات استوری برای پزشک
     */
    private function getDoctorStoryDescription($doctor)
    {
        $descriptions = [
            "در این ویدیو، {$doctor->full_name} نکات مهمی درباره سلامت ارائه می‌دهد.",
            "مشاوره رایگان با {$doctor->full_name} در مورد مسائل سلامتی.",
            "پاسخ به سوالات متداول بیماران توسط {$doctor->full_name}.",
            "توصیه‌های پزشکی مفید از {$doctor->full_name}.",
        ];

        return $descriptions[array_rand($descriptions)];
    }

    /**
     * تولید عنوان استوری برای مرکز درمانی
     */
    private function getMedicalCenterStoryTitle($medicalCenter)
    {
        $titles = [
            "معرفی خدمات {$medicalCenter->name}",
            "تجهیزات جدید {$medicalCenter->name}",
            "پرسنل متخصص {$medicalCenter->name}",
            "ساعات کاری {$medicalCenter->name}",
            "آدرس و مسیر {$medicalCenter->name}",
            "تخفیف‌های ویژه {$medicalCenter->name}",
        ];

        return $titles[array_rand($titles)];
    }

    /**
     * تولید توضیحات استوری برای مرکز درمانی
     */
    private function getMedicalCenterStoryDescription($medicalCenter)
    {
        $descriptions = [
            "معرفی کامل خدمات و امکانات {$medicalCenter->name}",
            "آشنایی با تجهیزات پیشرفته {$medicalCenter->name}",
            "معرفی پرسنل متخصص و با تجربه {$medicalCenter->name}",
            "اطلاعات کامل ساعات کاری و نوبت‌دهی {$medicalCenter->name}",
        ];

        return $descriptions[array_rand($descriptions)];
    }

    /**
     * تولید عنوان استوری برای مدیر
     */
    private function getManagerStoryTitle($manager)
    {
        $titles = [
            "پیام مدیر سیستم",
            "اخبار مهم سیستم",
            "تغییرات جدید",
            "راهنمای استفاده",
            "پشتیبانی و کمک",
        ];

        return $titles[array_rand($titles)];
    }

    /**
     * تولید توضیحات استوری برای مدیر
     */
    private function getManagerStoryDescription($manager)
    {
        $descriptions = [
            "پیام مهم از مدیریت سیستم برای کاربران گرامی.",
            "آخرین اخبار و تغییرات سیستم.",
            "راهنمای استفاده از امکانات جدید.",
            "اطلاعات پشتیبانی و راهنمایی.",
        ];

        return $descriptions[array_rand($descriptions)];
    }

    /**
     * تولید عنوان استوری برای کاربر
     */
    private function getUserStoryTitle($user)
    {
        $titles = [
            "تجربه من از درمان",
            "سفر سلامتی",
            "نکات مفید",
            "تجربه‌های شخصی",
            "راهنمای سلامت",
        ];

        return $titles[array_rand($titles)];
    }

    /**
     * تولید توضیحات استوری برای کاربر
     */
    private function getUserStoryDescription($user)
    {
        $descriptions = [
            "تجربه شخصی من از درمان و بهبودی.",
            "سفر سلامتی و نکات مفید.",
            "تجربه‌های شخصی در زمینه سلامت.",
            "راهنمای سلامت بر اساس تجربیات.",
        ];

        return $descriptions[array_rand($descriptions)];
    }
}

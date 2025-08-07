<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServicesSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = storage_path('app/services.txt');

        if (! file_exists($filePath)) {
            throw new \Exception("فایل services.txt در مسیر storage/app/ پیدا نشد.");
        }

        $jsonData = file_get_contents($filePath);
        $services = json_decode($jsonData, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! $services || ! is_array($services)) {
            throw new \Exception("خطا در خواندن یا فرمت JSON فایل services.txt: " . json_last_error_msg());
        }

        // آماده‌سازی داده‌ها برای درج گروهی
        $data = [];
        foreach ($services as $service) {
            $data[] = [
                'id'         => $service['value'], // استفاده از value به‌عنوان id
                'name'       => $service['label'], // استفاده از label به‌عنوان name
                'slug'       => Str::slug($service['label']), // ایجاد اسلاگ از نام
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // درج گروهی داده‌ها
        DB::table('services')->insert($data);
    }
}

<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InsurancesSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = storage_path('app/insurances.txt');

        if (! file_exists($filePath)) {
            throw new \Exception("فایل insurances.txt در مسیر storage/app/ پیدا نشد.");
        }

        $jsonData   = file_get_contents($filePath);
        $insurances = json_decode($jsonData, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! $insurances || ! is_array($insurances)) {
            throw new \Exception("خطا در خواندن یا فرمت JSON فایل insurances.txt: " . json_last_error_msg());
        }

        // آماده‌سازی داده‌ها برای درج گروهی
        $data = [];
        foreach ($insurances as $insurance) {
            $data[] = [
                'id'         => $insurance['value'],
                'name'       => $insurance['label'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // درج گروهی
        DB::table('insurances')->insert($data);
    }
}

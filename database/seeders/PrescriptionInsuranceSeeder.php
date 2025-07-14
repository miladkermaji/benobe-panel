<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrescriptionInsuranceSeeder extends Seeder
{
    public function run(): void
    {
        // بیمه نیروهای مسلح
        DB::table('prescription_insurances')->insert([
            'name' => 'بیمه نیروهای مسلح',
            'parent_id' => null,
        ]);
        // بیمه تامین اجتماعی
        DB::table('prescription_insurances')->insert([
            'name' => 'بیمه تامین اجتماعی',
            'parent_id' => null,
        ]);
        // بیمه خدمات درمانی
        $parentId = DB::table('prescription_insurances')->insertGetId([
            'name' => 'بیمه خدمات درمانی',
            'parent_id' => null,
        ]);
        $subs = [
            'روستایی',
            'کارمندی',
            'سلامت همگانی(ایرانیان)',
            'کمیته امداد',
            'سایر اقشار',
            'بهزیستی',
            'بنیاد شهید',
        ];
        foreach ($subs as $sub) {
            DB::table('prescription_insurances')->insert([
                'name' => $sub,
                'parent_id' => $parentId,
            ]);
        }
    }
}

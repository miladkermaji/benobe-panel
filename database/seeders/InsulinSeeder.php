<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InsulinSeeder extends Seeder
{
    public function run(): void
    {
        $insulins = [
            'لانتوس',
            'نووراپید',
            'اپیدرا',
            'نوومیکس',
            'لوومیر',
            'ویکتوزا',
            'ملیتاید',
            'رایزودک',
            'بازالین',
            'راپیدسولین',
            'رگولار',
            'Nph (ان پی اچ)',
        ];
        foreach ($insulins as $i => $name) {
            DB::table('insulins')->insert([
                'name' => $name,
                'sort_order' => $i + 1,
                'status' => true,
            ]);
        }
    }
}

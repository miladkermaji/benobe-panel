<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run()
    {
        // بررسی وجود خدمت "ویزیت"
        if (!Service::where('name', 'ویزیت')->exists()) {
            Service::create([
                'name' => 'ویزیت',
                'description' => 'ویزیت عمومی پزشک',
                'status' => true,
            ]);
        }
    }
}
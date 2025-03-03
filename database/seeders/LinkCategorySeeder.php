<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin\ContentManagement\Links\LinkCategory;

class LinkCategorySeeder extends Seeder
{
    public function run()
    {
        LinkCategory::create(['name' => 'پایین سایت']);
    }
}
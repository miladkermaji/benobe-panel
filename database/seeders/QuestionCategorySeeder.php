<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin\Question\QuestionCategory;

class QuestionCategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'گوش و حلق و بینی', 'alt_name' => 'ent'],
            ['name' => 'قلب و عروق', 'alt_name' => 'cardiology'],
            ['name' => 'واکسیناسیون', 'alt_name' => 'vaccination'],
        ];

        foreach ($categories as $category) {
            QuestionCategory::create($category);
        }
    }
}
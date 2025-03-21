<?php
namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\DoctorTag;
use Illuminate\Database\Seeder;

class DoctorTagSeeder extends Seeder
{
    public function run()
    {
        $doctors = Doctor::all();

        foreach ($doctors as $doctor) {
            $tags = [
                ['doctor_id' => $doctor->id, 'name' => 'کمترین معطلی', 'color' => 'green-100', 'text_color' => 'green-700'],
                ['doctor_id' => $doctor->id, 'name' => 'خوش برخورد', 'color' => 'orange-100', 'text_color' => 'orange-700'],
                ['doctor_id' => $doctor->id, 'name' => 'پوشش بیمه', 'color' => 'yellow-100', 'text_color' => 'yellow-700'],
            ];

            foreach ($tags as $tag) {
                DoctorTag::create($tag);
            }
        }
    }
}

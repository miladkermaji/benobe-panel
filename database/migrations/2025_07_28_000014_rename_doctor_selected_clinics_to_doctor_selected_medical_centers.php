<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::rename('doctor_selected_clinics', 'doctor_selected_medical_centers');
    }

    public function down(): void
    {
        Schema::rename('doctor_selected_medical_centers', 'doctor_selected_clinics');
    }
}; 
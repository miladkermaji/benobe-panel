<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::rename('clinic_deposit_settings', 'medical_center_deposit_settings');
    }

    public function down(): void
    {
        Schema::rename('medical_center_deposit_settings', 'clinic_deposit_settings');
    }
}; 
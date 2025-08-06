<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('medical_center_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_center_id')->constrained('medical_centers')->onDelete('cascade');
            $table->json('permissions')->nullable(); // ذخیره دسترسی‌ها به‌صورت JSON
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_center_permissions');
    }
};

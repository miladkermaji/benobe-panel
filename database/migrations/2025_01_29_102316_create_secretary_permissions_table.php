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
        Schema::create('secretary_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->foreignId('secretary_id')->constrained('secretaries')->onDelete('cascade');
            $table->foreignId('medical_center_id')->nullable()->constrained('medical_centers')->onDelete('cascade');

            $table->json('permissions')->nullable(); // ذخیره دسترسی‌ها به‌صورت JSON
            $table->boolean('has_access')->default(true); // دسترسی فعال یا غیرفعال

            $table->timestamps();

            // کلید‌های منحصربه‌فرد برای جلوگیری از تکرار
            $table->unique(['doctor_id', 'secretary_id', 'medical_center_id'], 'sec_perm_unique');

            // بهبود کارایی جستجو
            $table->index(['secretary_id', 'medical_center_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secretary_permissions');
    }
};

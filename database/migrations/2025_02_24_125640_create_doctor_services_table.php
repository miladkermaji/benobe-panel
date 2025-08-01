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
        Schema::create('doctor_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id')->comment('شناسه دکتر مربوط به خدمت');
            $table->unsignedBigInteger('medical_center_id')->nullable()->comment('شناسه مرکز درمانی مربوط به خدمت');
            $table->foreignId('insurance_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('service_id')->nullable()->constrained('services')->onDelete('set null');
            $table->string('name')->comment('نام خدمت');
            $table->text('description')->nullable()->comment('توضیحات خدمت');
            $table->integer('duration')->comment('مدت زمان خدمت (به دقیقه)');
            $table->decimal('price', 12, 2)->comment('قیمت خدمت');
            $table->decimal('discount', 8, 2)->nullable()->comment('تخفیف اختیاری');
            $table->tinyInteger('status')->default(0);
            $table->unsignedBigInteger('parent_id')->nullable()->comment('شناسه خدمت مادر (برای زیرگروه‌ها)');
            $table->timestamps();

            // تعریف کلیدهای خارجی
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreign('medical_center_id')->references('id')->on('medical_centers')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('doctor_services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_services');
    }
};

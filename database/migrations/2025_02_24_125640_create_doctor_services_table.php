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
            $table->unsignedBigInteger('doctor_id')->comment('شناسه دکتر مربوط به سرویس');
            $table->unsignedBigInteger('clinic_id')->nullable()->comment('شناسه کلینیک مربوط به سرویس');
            $table->foreignId('insurance_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name')->comment('نام سرویس');
            $table->text('description')->nullable()->comment('توضیحات سرویس');
            $table->integer('duration')->comment('مدت زمان خدمت (به دقیقه)');
            $table->decimal('price', 12, 2)->comment('قیمت سرویس');
            $table->decimal('discount', 8, 2)->nullable()->comment('تخفیف اختیاری');
            $table->tinyInteger('status')->default(0);
            $table->unsignedBigInteger('parent_id')->nullable()->comment('شناسه سرویس مادر (برای زیرگروه‌ها)');
            $table->timestamps();

            // تعریف کلیدهای خارجی
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
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

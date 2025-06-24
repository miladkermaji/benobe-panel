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
        Schema::create('medical_centers', function (Blueprint $table) {
            $table->id();
            // اطلاعات درمانگاه
            $table->string('name')->nullable();                    // نام درمانگاه
            $table->string('title')->nullable();                   // عنوان درمانگاه
            $table->string('address')->nullable();                 // آدرس درمانگاه
            $table->string('secretary_phone')->nullable();         // شماره منشی
            $table->string('phone_number')->nullable();            // شماره تماس درمانگاه
            $table->string('postal_code')->nullable();             // کد پستی
            $table->unsignedBigInteger('province_id')->nullable(); // کلید خارجی به جدول zone
            $table->unsignedBigInteger('city_id')->nullable();     // کلید خارجی به جدول zone

            // اطلاعات تکمیلی
            $table->boolean('is_main_center')->default(false); // آیا درمانگاه اصلی است
            $table->time('start_time')->nullable();            // ساعت شروع کار
            $table->time('end_time')->nullable();              // ساعت پایان کار
            $table->text('description')->nullable();           // توضیحات درمانگاه

            // مختصات جغرافیایی
            $table->decimal('latitude', 10, 7)->nullable();  // عرض جغرافیایی
            $table->decimal('longitude', 10, 7)->nullable(); // طول جغرافیایی

            // اطلاعات مالی
            $table->decimal('consultation_fee', 10, 2)->nullable();                  // هزینه خدمات
            $table->enum('payment_methods', ['cash', 'card', 'online'])->nullable(); // روش‌های پرداخت
            $table->enum('Center_tariff_type', ['governmental', 'special', 'else'])->nullable(); // نوع تعرفه مرکز 
            $table->enum('Daycare_centers', ['yes', 'no'])->nullable(); //مراکز شبانه روزی
            $table->enum('type', ['hospital', 'treatment_centers', 'clinic', 'imaging_center', 'laboratory', 'pharmacy','policlinic'])->nullable(); // نوع مراکز درمانی

            // وضعیت و تنظیمات
            $table->boolean('is_active')->default(false); // وضعیت فعال‌سازی
            $table->json('working_days')->nullable();     // روزهای کاری
            $table->json('specialty_ids')->nullable();    // تخصص‌ها
            $table->json('insurance_ids')->nullable();    // بیمه‌ها

            // فیلدهای جدید
            $table->text('avatar')->nullable();                   // تصویر اصلی درمانگاه
            $table->json('documents')->nullable();                // مدارک درمانگاه
            $table->json('galleries')->nullable();                // گالری درمانگاه
            $table->json('phone_numbers')->nullable();            // شماره‌های تماس درمانگاه
            $table->boolean('location_confirmed')->default(false); // تایید مکان روی نقشه

            $table->timestamps();

          
            $table->foreign('province_id')->references('id')->on('zone')->onDelete('set null');
            $table->foreign('city_id')->references('id')->on('zone')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_centers');
    }
};

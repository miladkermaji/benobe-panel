<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id');

                                                   // اطلاعات مطب
            $table->string('name')->nullable();    // نام مطب
            $table->string('address')->nullable(); // آدرس مطب
            $table->string('secretary_phone')->nullable();
            $table->string('phone_number')->nullable();            // شماره تماس مطب
            $table->string('postal_code')->nullable();             // کد پستی
            $table->unsignedBigInteger('province_id')->nullable(); // کلید خارجی به جدول zone
            $table->unsignedBigInteger('city_id')->nullable();     // کلید خارجی به جدول zone

                                                               // اطلاعات تکمیلی
            $table->boolean('is_main_clinic')->default(false); // آیا مطب اصلی است
            $table->time('start_time')->nullable();            // ساعت شروع کار
            $table->time('end_time')->nullable();              // ساعت پایان کار
            $table->text('description')->nullable();           // توضیحات مطب

                                                             // مختصات جغرافیایی
            $table->decimal('latitude', 10, 7)->nullable();  // عرض جغرافیایی
            $table->decimal('longitude', 10, 7)->nullable(); // طول جغرافیایی

                                                                                     // اطلاعات مالی
            $table->decimal('consultation_fee', 10, 2)->nullable();                  // هزینه ویزیت
            $table->enum('payment_methods', ['cash', 'card', 'online'])->nullable(); // روش‌های پرداخت

                                                          // وضعیت و تنظیمات
            $table->boolean('is_active')->default(false); // وضعیت فعال‌سازی
            $table->json('working_days')->nullable();     // روزهای کاری
            $table->json('gallery')->nullable();                   // گالری تصاویر مطب
            $table->json('documents')->nullable();                 // مدارک مطب
            $table->json('phone_numbers')->nullable();             // شماره‌های تماس مطب
            $table->boolean('location_confirmed')->default(false); // تایید مکان روی نقشه

            $table->timestamps();

            // کلید خارجی به جدول دکترها
            $table->foreign('doctor_id')
                ->references('id')
                ->on('doctors')
                ->onDelete('cascade');
            $table->foreign('province_id')->references('id')->on('zone')->onDelete('set null');
            $table->foreign('city_id')->references('id')->on('zone')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinics');
    }
};

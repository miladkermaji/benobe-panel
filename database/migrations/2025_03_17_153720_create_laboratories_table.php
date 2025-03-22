<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id');

                                                                   // اطلاعات درمانگاه
            $table->string('name')->nullable();                    // نام درمانگاه
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

                                                          // وضعیت و تنظیمات
            $table->boolean('is_active')->default(false); // وضعیت فعال‌سازی
            $table->json('working_days')->nullable();     // روزهای کاری

                                                                   // فیلدهای جدید
           $table->text('avatar')->nullable();                   // گالری تصاویر درمانگاه
            $table->json('documents')->nullable();                 // مدارک درمانگاه
            $table->json('phone_numbers')->nullable();             // شماره‌های تماس درمانگاه
            $table->boolean('location_confirmed')->default(false); // تایید مکان روی نقشه

            $table->timestamps();

            // کلیدهای خارجی
            $table->foreign('doctor_id')
                ->references('id')
                ->on('doctors')
                ->onDelete('cascade');
            $table->foreign('province_id')->references('id')->on('zone')->onDelete('set null');
            $table->foreign('city_id')->references('id')->on('zone')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratories');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('counseling_appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('insurance_id')->nullable();
            $table->unsignedBigInteger('clinic_id')->nullable();

            // فیلدهای مشابه نوبت‌ها
            $table->integer('duration')->nullable(); // مدت زمان مشاوره (دقیقه)
            $table->enum('consultation_type', ['general', 'specialized', 'emergency'])->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('payment_status', ['pending', 'paid', 'unpaid'])->default('pending');
            $table->enum('appointment_type', ['in_person', 'online', 'phone']);
            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time');

            // زمان دقیق رزرو و تأیید
            $table->timestamp('reserved_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();

            // وضعیت‌های مشاوره
            $table->enum('status', [
                'scheduled',          // در انتظار خدمت
                'cancelled',          // لغو شده
                'attended',           // حضور یافته
                'missed',             // غایب
                'pending_review',     // در انتظار بررسی و تماس
                'call_answered',      // تماس و پاسخ داده شده
                'call_completed',     // مکالمه انجام و پایان یافته است
                'refunded'
            ])->default('scheduled');
            $table->enum('attendance_status', ['attended', 'missed', 'cancelled'])->nullable();

            // توضیحات و اطلاعات تکمیلی
            $table->text('notes')->nullable();
            $table->text('title')->nullable();
            $table->string('tracking_code')->nullable()->unique();
            $table->integer('max_appointments')->nullable();
            $table->decimal('fee', 8, 2)->nullable();
            $table->enum('appointment_category', ['initial', 'follow_up'])->nullable();
            $table->string('location')->nullable();
            $table->boolean('notification_sent')->default(false);

            // زمان‌های ثبت و حذف نرم (soft delete)
            $table->timestamps();
            $table->softDeletes();

            // کلیدهای خارجی
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreign('patient_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('insurance_id')->references('id')->on('insurances')->onDelete('set null');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('set null');

            // ایندکس‌ها با نام کوتاه‌تر
            $table->index(['doctor_id', 'patient_id', 'appointment_date'], 'counseling_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counseling_appointments');
    }
};

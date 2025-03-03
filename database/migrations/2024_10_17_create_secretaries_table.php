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
        Schema::create('secretaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->unsignedBigInteger('clinic_id')->nullable();

            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('display_name')->nullable()->comment('نام نمایشی');
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('alternative_mobile')->nullable();
            $table->string('national_code')->nullable();
            $table->string('password')->nullable();
            $table->boolean('static_password_enabled')->default(false)->comment('آیا رمز عبور ثابت فعال است؟');
            $table->string('two_factor_secret')->nullable();
            $table->boolean('two_factor_secret_enabled')->default(false)->comment('آیا رمز عبور ثابت فعال است؟');
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->unsignedBigInteger('province_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->text('address')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('slug')->unique()->nullable();
            $table->text('profile_photo_path')->nullable();
            $table->text('bio')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->boolean('profile_completed')->default(false);
            $table->tinyInteger('status')->default(1)->comment('وضعیت حساب');
            $table->string('api_token', 80)->unique()->nullable();
            $table->rememberToken();
            $table->timestamp('mobile_verified_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // کلید خارجی
            $table->foreign('province_id')->references('id')->on('zone')->onDelete('set null');
            $table->foreign('city_id')->references('id')->on('zone')->onDelete('set null');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
        });

        // اضافه کردن ایندکس‌های ترکیبی برای شماره موبایل و کد ملی با توجه به شرط‌های اختصاصی
        Schema::table('secretaries', function (Blueprint $table) {
            // موبایل یکتا برای هر دکتر با کلینیک خاص
            $table->unique(['doctor_id', 'clinic_id', 'mobile'], 'unique_mobile_per_doctor_clinic');

            // کد ملی یکتا برای هر دکتر با کلینیک خاص
            $table->unique(['doctor_id', 'clinic_id', 'national_code'], 'unique_national_code_per_doctor_clinic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secretaries');
    }
};

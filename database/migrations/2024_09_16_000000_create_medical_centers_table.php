<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('medical_centers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('title')->nullable();
            $table->string('address')->nullable();
            $table->string('secretary_phone')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('siam_code')->nullable()->unique();
            $table->unsignedBigInteger('province_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->boolean('is_main_center')->default(false);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->text('description')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('consultation_fee', 10, 2)->nullable();
            $table->decimal('prescription_tariff', 10, 2)->nullable();
            $table->enum('payment_methods', ['cash', 'card', 'online'])->nullable();
            $table->enum('Center_tariff_type', ['governmental', 'special', 'else'])->nullable();
            $table->enum('Daycare_centers', ['yes', 'no'])->nullable();
            $table->enum('type', ['hospital', 'treatment_centers', 'clinic', 'imaging_center', 'laboratory', 'pharmacy', 'policlinic'])->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('working_days')->nullable();
            $table->json('specialty_ids')->nullable();
            $table->json('insurance_ids')->nullable();
            $table->json('service_ids')->nullable();
            $table->text('avatar')->nullable();
            $table->json('documents')->nullable();
            $table->json('galleries')->nullable();
            $table->json('phone_numbers')->nullable();
            $table->boolean('location_confirmed')->default(false);
            $table->string('slug')->unique()->nullable();
            $table->decimal('average_rating', 2, 1)->default(0.0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->unsignedInteger('recommendation_percentage')->default(0);
            $table->string('password')->nullable()->after('recommendation_percentage');
            $table->boolean('static_password_enabled')->default(false)->after('password');
            $table->boolean('two_factor_secret_enabled')->default(false)->after('static_password_enabled');
            $table->timestamp('mobile_verified_at')->nullable()->after('two_factor_secret_enabled');
            $table->rememberToken()->after('mobile_verified_at');
            $table->timestamps();
            $table->softDeletes();


            $table->foreign('province_id')->references('id')->on('zone')->onDelete('set null');
            $table->foreign('city_id')->references('id')->on('zone')->onDelete('set null');

            // افزودن ایندکس برای بهینه‌سازی
            $table->index('province_id');
            $table->index('city_id');
            $table->index('type');
            $table->index('is_active');
            $table->index('Center_tariff_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_centers');
    }
};

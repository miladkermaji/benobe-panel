<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('managers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth')->nullable();
            $table->string('email')->unique();
            $table->string('mobile')->nullable()->unique();
            $table->string('national_code')->nullable()->unique();
            $table->enum('sex', ['male', 'female'])->nullable();
            $table->tinyInteger('status')->default('0');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->text('avatar')->nullable();
            $table->text('address')->nullable();
            $table->integer('permission')->default('1');
            $table->boolean('static_password_enabled')->default(false)->comment('آیا رمز عبور ثابت فعال است؟');
            $table->string('two_factor_secret')->nullable();
            $table->boolean('two_factor_secret_enabled')->default(false)->comment('آیا رمز عبور دومرحله‌ای فعال است؟');
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->string('slug')->unique()->nullable();
            $table->text('bio')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->boolean('profile_completed')->default(false);
            $table->timestamp('mobile_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->softDeletes(); // قابلیت حذف نرم (soft delete)

            $table->rememberToken();
            $table->timestamps();
        });
        Artisan::call('db:seed', [
            '--class' => 'ManagersTableSeeder',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('managers');
    }
};

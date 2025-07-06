<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('managers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->date('date_of_birth')->nullable();
            $table->string('national_code', 10)->nullable()->unique();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('email')->unique()->index();
            $table->string('mobile', 15)->nullable()->unique()->index();
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('mobile_verified_at')->nullable();
            $table->string('two_factor_secret')->nullable();
            $table->boolean('two_factor_enabled')->default(false);
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->boolean('static_password_enabled')->default(false);
            $table->string('avatar')->nullable();
            $table->text('bio')->nullable();
            $table->text('address')->nullable();
            $table->string('slug')->unique()->nullable()->index();
            $table->unsignedTinyInteger('status')->default(0);
            $table->unsignedTinyInteger('permission_level')->default(1);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->boolean('profile_completed')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->unsignedInteger('login_count')->default(0);
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['first_name', 'last_name'], 'name_index');
            $table->index('status', 'status_index');
            $table->index('status');
            $table->index('permission_level');
            $table->index('is_active');
            $table->index('is_verified');
            $table->index('profile_completed');
            $table->index('last_login_at');
            $table->index(['status', 'is_active']);
            $table->index(['permission_level', 'is_active']);
        });

        Artisan::call('db:seed', [
            '--class' => 'ManagersTableSeeder',
            '--force' => true,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('managers');
    }
};

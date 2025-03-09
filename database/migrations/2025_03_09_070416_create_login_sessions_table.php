<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('login_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('token', 60)->unique()->index();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->unsignedBigInteger('secretary_id')->nullable();
            $table->unsignedTinyInteger('step')->default(1); // 1: ورود موبایل، 2: OTP، 3: رمز عبور، 4: دو عاملی
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->foreign('manager_id')->references('id')->on('managers')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreign(columns: 'secretary_id')->references('id')->on('secretaries')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_sessions');
    }
};
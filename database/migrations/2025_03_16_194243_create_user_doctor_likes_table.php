<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_doctor_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->timestamp('liked_at')->useCurrent();
            $table->timestamps();

            // اطمینان از یکتایی: هر کاربر فقط یک بار بتونه یه پزشک رو لایک کنه
            $table->unique(['user_id', 'doctor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_doctor_likes');
    }
};

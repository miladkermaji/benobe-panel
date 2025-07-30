<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('user_doctor_likes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('likeable_id')->nullable();
            $table->string('likeable_type')->nullable();
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->timestamp('liked_at')->useCurrent();
            $table->timestamps();

            // اطمینان از یکتایی: هر کاربر فقط یک بار بتونه یه پزشک رو لایک کنه
            $table->unique(['likeable_id', 'likeable_type', 'doctor_id']);
            $table->index(['likeable_id', 'likeable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_doctor_likes');
    }
};

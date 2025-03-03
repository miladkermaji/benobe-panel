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
        Schema::create('ticket_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade'); // ارتباط با تیکت
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->onDelete('cascade'); // پاسخ‌دهنده پزشک
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); // پاسخ‌دهنده کاربر
            $table->text('message'); // متن پاسخ
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_responses');
    }
};

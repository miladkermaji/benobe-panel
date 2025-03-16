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
        Schema::create('sub_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->onDelete('cascade'); // ارتباط با پزشک
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); // ارتباط با کاربر زیرمجموعه
            $table->enum('status', ['active', 'inactive'])->default('active'); // وضعیت کاربر
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_users');
    }
};

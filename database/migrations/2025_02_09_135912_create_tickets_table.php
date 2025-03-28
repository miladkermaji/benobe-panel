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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->onDelete('cascade'); // پزشک
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); // کاربر (در آینده استفاده می‌شود)
            $table->foreignId('manager_id')->nullable()->constrained('managers')->onDelete('cascade'); // کاربر (در آینده استفاده می‌شود)
            $table->string('title'); // عنوان تیکت
            $table->text('description'); // متن تیکت
            $table->enum('status', ['open', 'pending', 'closed','answered'])->default('open'); // وضعیت تیکت
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};

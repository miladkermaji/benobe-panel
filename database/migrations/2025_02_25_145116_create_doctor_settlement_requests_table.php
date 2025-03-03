<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('doctor_settlement_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->unsignedInteger('amount')->default(0); // مبلغ درخواستی
            $table->enum('status', ['pending', 'approved', 'paid'])->default('pending'); // وضعیت درخواست
            $table->timestamp('requested_at')->useCurrent(); // تاریخ درخواست
            $table->timestamp('processed_at')->nullable(); // تاریخ پردازش (تأیید یا پرداخت)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_settlement_requests');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('orderable_id')->nullable();
            $table->string('orderable_type')->nullable();
            $table->string('order_code')->unique();
            $table->unsignedBigInteger('total_amount');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->date('order_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['orderable_id', 'orderable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

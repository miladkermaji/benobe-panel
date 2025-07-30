<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sub_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->string('owner_type')->nullable();
            $table->unsignedBigInteger('subuserable_id')->nullable();
            $table->string('subuserable_type')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active'); // وضعیت کاربر
            $table->timestamps();

            $table->index(['owner_id', 'owner_type']);
            $table->index(['subuserable_id', 'subuserable_type']);
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

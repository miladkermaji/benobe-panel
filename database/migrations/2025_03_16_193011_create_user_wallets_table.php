<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('user_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('walletable_id')->nullable();
            $table->string('walletable_type')->nullable();
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['walletable_id', 'walletable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_wallets');
    }
};

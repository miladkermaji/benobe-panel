<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id();
            $table->morphs('attemptable'); // Creates attemptable_type and attemptable_id columns
            $table->string('mobile')->index();
            $table->integer('attempts')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('lockout_until')->nullable();
            $table->timestamps();

            // Add index for mobile and polymorphic relationship
            $table->index(['mobile', 'attemptable_type', 'attemptable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_attempts');
    }
};

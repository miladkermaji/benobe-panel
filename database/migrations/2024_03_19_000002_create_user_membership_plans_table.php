<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('user_membership_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->integer('duration_days');

            $table->enum('duration_type', ['day', 'week', 'month','year'])->nullable();

            $table->integer('appointment_count');
            $table->json('features')->nullable();
            $table->boolean('status')->default(true);

            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_membership_plans');
    }
};

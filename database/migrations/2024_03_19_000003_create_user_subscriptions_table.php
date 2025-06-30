<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscribable_id');
            $table->string('subscribable_type');
            $table->foreignId('plan_id')->constrained('user_membership_plans')->onDelete('cascade');
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->onDelete('set null');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('remaining_appointments');
            $table->boolean('status')->default(true);
            $table->foreignId('admin_id')->nullable()->constrained('managers')->onDelete('set null');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_subscriptions');
    }
};

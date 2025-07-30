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
        Schema::create('doctor_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('userable_id')->nullable();
            $table->string('userable_type')->nullable();
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->text('comment');
            $table->text('reply')->nullable();
            $table->boolean('status')->default(0); // 0 = غیرفعال، 1 = فعال
            $table->string('ip_address')->nullable();
            $table->enum('acquaintance', ['other', 'friend', 'social', 'ads'])->nullable();
            $table->tinyInteger('overall_score')->nullable();
            $table->boolean('recommend_doctor')->nullable();
            $table->tinyInteger('score_behavior')->nullable();
            $table->tinyInteger('score_explanation')->nullable();
            $table->tinyInteger('score_skill')->nullable();
            $table->tinyInteger('score_receptionist')->nullable();
            $table->tinyInteger('score_environment')->nullable();
            $table->string('waiting_time')->nullable();
            $table->string('visit_reason')->nullable();
            $table->text('receptionist_comment')->nullable();
            $table->text('experience_comment')->nullable();
            $table->timestamps();

            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('set null');
            $table->index(['userable_id', 'userable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_comments');
    }
};

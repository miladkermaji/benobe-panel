<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        // حذف فیلد acquaintance اگر وجود دارد
        if (Schema::hasColumn('doctor_comments', 'acquaintance')) {
            Schema::table('doctor_comments', function (Blueprint $table) {
                $table->dropColumn('acquaintance');
            });
        }
        // افزودن فیلد enum و سایر فیلدها
        Schema::table('doctor_comments', function (Blueprint $table) {
            $table->unsignedBigInteger('appointment_id')->nullable()->after('doctor_id');
            $table->enum('acquaintance', ['other', 'friend', 'social', 'ads'])->nullable()->after('appointment_id');
            $table->tinyInteger('overall_score')->nullable()->after('acquaintance');
            $table->boolean('recommend_doctor')->nullable()->after('overall_score');
            $table->tinyInteger('score_behavior')->nullable()->after('recommend_doctor');
            $table->tinyInteger('score_explanation')->nullable()->after('score_behavior');
            $table->tinyInteger('score_skill')->nullable()->after('score_explanation');
            $table->tinyInteger('score_receptionist')->nullable()->after('score_skill');
            $table->tinyInteger('score_environment')->nullable()->after('score_receptionist');
            $table->string('waiting_time')->nullable()->after('score_environment');
            $table->string('visit_reason')->nullable()->after('waiting_time');
            $table->text('receptionist_comment')->nullable()->after('visit_reason');
            $table->text('experience_comment')->nullable()->after('receptionist_comment');
            $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('doctor_comments', function (Blueprint $table) {
            $table->dropForeign(['appointment_id']);
            $table->dropColumn([
                'appointment_id',
                'acquaintance',
                'overall_score',
                'recommend_doctor',
                'score_behavior',
                'score_explanation',
                'score_skill',
                'score_receptionist',
                'score_environment',
                'waiting_time',
                'visit_reason',
                'receptionist_comment',
                'experience_comment',
            ]);
        });
    }
};

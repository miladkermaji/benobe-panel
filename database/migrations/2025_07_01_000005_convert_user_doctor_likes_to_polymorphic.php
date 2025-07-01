<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up()
    {
        Schema::table('user_doctor_likes', function (Blueprint $table) {
            $table->unsignedBigInteger('likeable_id')->nullable()->after('id');
            $table->string('likeable_type')->nullable()->after('likeable_id');
        });
        // Migrate existing user_id data to polymorphic fields
        DB::table('user_doctor_likes')->whereNotNull('user_id')->update([
            'likeable_id' => DB::raw('user_id'),
            'likeable_type' => 'App\\Models\\User',
        ]);
        Schema::table('user_doctor_likes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('user_doctor_likes', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'doctor_id']);
        });
        Schema::table('user_doctor_likes', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
        Schema::table('user_doctor_likes', function (Blueprint $table) {
            $table->unique(['likeable_id', 'likeable_type', 'doctor_id']);
            $table->index(['likeable_id', 'likeable_type']);
        });
    }

    public function down()
    {
        Schema::table('user_doctor_likes', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
        });
        DB::table('user_doctor_likes')->where('likeable_type', 'App\\Models\\User')->update([
            'user_id' => DB::raw('likeable_id'),
        ]);
        Schema::table('user_doctor_likes', function (Blueprint $table) {
            $table->dropUnique(['likeable_id', 'likeable_type', 'doctor_id']);
            $table->dropIndex(['likeable_id', 'likeable_type']);
            $table->dropColumn(['likeable_id', 'likeable_type']);
            $table->unique(['user_id', 'doctor_id']);
        });
    }
};

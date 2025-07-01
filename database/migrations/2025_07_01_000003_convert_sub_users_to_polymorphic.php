<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up()
    {
        Schema::table('sub_users', function (Blueprint $table) {
            $table->unsignedBigInteger('subuserable_id')->nullable()->after('doctor_id');
            $table->string('subuserable_type')->nullable()->after('subuserable_id');
        });
        // Migrate existing user_id data to polymorphic fields
        DB::table('sub_users')->whereNotNull('user_id')->update([
            'subuserable_id' => DB::raw('user_id'),
            'subuserable_type' => 'App\\Models\\User',
        ]);
        Schema::table('sub_users', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
        Schema::table('sub_users', function (Blueprint $table) {
            $table->index(['subuserable_id', 'subuserable_type']);
        });
    }

    public function down()
    {
        Schema::table('sub_users', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('doctor_id');
        });
        DB::table('sub_users')->where('subuserable_type', 'App\\Models\\User')->update([
            'user_id' => DB::raw('subuserable_id'),
        ]);
        Schema::table('sub_users', function (Blueprint $table) {
            $table->dropIndex(['subuserable_id', 'subuserable_type']);
            $table->dropColumn(['subuserable_id', 'subuserable_type']);
        });
    }
};

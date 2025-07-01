<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('orderable_id')->nullable()->after('id');
            $table->string('orderable_type')->nullable()->after('orderable_id');
        });
        // Migrate existing user_id data to polymorphic fields
        DB::table('orders')->whereNotNull('user_id')->update([
            'orderable_id' => DB::raw('user_id'),
            'orderable_type' => 'App\\Models\\User',
        ]);
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['orderable_id', 'orderable_type']);
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
        });
        DB::table('orders')->where('orderable_type', 'App\\Models\\User')->update([
            'user_id' => DB::raw('orderable_id'),
        ]);
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['orderable_id', 'orderable_type']);
            $table->dropColumn(['orderable_id', 'orderable_type']);
        });
    }
};

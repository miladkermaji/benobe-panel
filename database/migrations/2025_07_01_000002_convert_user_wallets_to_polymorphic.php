<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up()
    {
        // user_wallets
        Schema::table('user_wallets', function (Blueprint $table) {
            $table->unsignedBigInteger('walletable_id')->nullable()->after('id');
            $table->string('walletable_type')->nullable()->after('walletable_id');
        });
        DB::table('user_wallets')->whereNotNull('user_id')->update([
            'walletable_id' => DB::raw('user_id'),
            'walletable_type' => 'App\\Models\\User',
        ]);
        Schema::table('user_wallets', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
        Schema::table('user_wallets', function (Blueprint $table) {
            $table->unique(['walletable_id', 'walletable_type']);
        });

        // user_wallet_transactions
        Schema::table('user_wallet_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('walletable_id')->nullable()->after('id');
            $table->string('walletable_type')->nullable()->after('walletable_id');
        });
        DB::table('user_wallet_transactions')->whereNotNull('user_id')->update([
            'walletable_id' => DB::raw('user_id'),
            'walletable_type' => 'App\\Models\\User',
        ]);
        Schema::table('user_wallet_transactions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
        Schema::table('user_wallet_transactions', function (Blueprint $table) {
            $table->index(['walletable_id', 'walletable_type']);
        });
    }

    public function down()
    {
        // user_wallets
        Schema::table('user_wallets', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
        });
        DB::table('user_wallets')->where('walletable_type', 'App\\Models\\User')->update([
            'user_id' => DB::raw('walletable_id'),
        ]);
        Schema::table('user_wallets', function (Blueprint $table) {
            $table->dropUnique(['walletable_id', 'walletable_type']);
            $table->dropColumn(['walletable_id', 'walletable_type']);
        });

        // user_wallet_transactions
        Schema::table('user_wallet_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
        });
        DB::table('user_wallet_transactions')->where('walletable_type', 'App\\Models\\User')->update([
            'user_id' => DB::raw('walletable_id'),
        ]);
        Schema::table('user_wallet_transactions', function (Blueprint $table) {
            $table->dropIndex(['walletable_id', 'walletable_type']);
            $table->dropColumn(['walletable_id', 'walletable_type']);
        });
    }
};

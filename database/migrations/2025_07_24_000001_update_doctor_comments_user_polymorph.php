<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('doctor_comments', function (Blueprint $table) {
            $table->dropColumn(['user_name', 'user_phone']);
            $table->unsignedBigInteger('userable_id')->nullable()->after('doctor_id');
            $table->string('userable_type')->nullable()->after('userable_id');
            $table->index(['userable_id', 'userable_type']);
        });
    }

    public function down(): void
    {
        Schema::table('doctor_comments', function (Blueprint $table) {
            $table->dropIndex(['userable_id', 'userable_type']);
            $table->dropColumn(['userable_id', 'userable_type']);
            $table->string('user_name')->nullable();
            $table->string('user_phone')->nullable();
        });
    }
};

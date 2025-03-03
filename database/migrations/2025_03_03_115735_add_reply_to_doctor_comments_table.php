<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('doctor_comments', function (Blueprint $table) {
            $table->text('reply')->nullable()->after('comment'); // اضافه کردن ستون reply
        });
    }

    public function down(): void
    {
        Schema::table('doctor_comments', function (Blueprint $table) {
            $table->dropColumn('reply'); // حذف ستون در صورت rollback
        });
    }
};
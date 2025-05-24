<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            // حذف foreign key قبلی
            $table->dropForeign(['created_by']);

            // اضافه کردن foreign key جدید
            $table->foreign('created_by')
                  ->references('id')
                  ->on('managers')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            // حذف foreign key جدید
            $table->dropForeign(['created_by']);

            // برگرداندن foreign key قبلی
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }
};

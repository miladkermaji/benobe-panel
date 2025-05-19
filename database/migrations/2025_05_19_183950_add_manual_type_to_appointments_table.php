<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up()
    {
        // تغییر نوع ستون appointment_type به string موقتاً
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('appointment_type')->change();
        });

        // اضافه کردن مقدار جدید به enum
        DB::statement("ALTER TABLE appointments MODIFY COLUMN appointment_type ENUM('in_person', 'online', 'phone', 'manual')");
    }

    public function down()
    {
        // حذف مقدار manual از enum
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('appointment_type')->change();
        });

        DB::statement("ALTER TABLE appointments MODIFY COLUMN appointment_type ENUM('in_person', 'online', 'phone')");
    }
};

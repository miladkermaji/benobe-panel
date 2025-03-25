<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRecipientTypeToSmsTemplatesTable extends Migration
{
    public function up()
    {
        Schema::table('sms_templates', function (Blueprint $table) {
            $table->string('recipient_type')->nullable()->after('type'); // اضافه کردن ستون recipient_type
        });
    }

    public function down()
    {
        Schema::table('sms_templates', function (Blueprint $table) {
            $table->dropColumn('recipient_type');
        });
    }
}

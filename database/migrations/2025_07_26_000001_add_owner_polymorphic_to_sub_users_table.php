 <?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::table('sub_users', function (Blueprint $table) {
            $table->unsignedBigInteger('owner_id')->nullable()->after('id');
            $table->string('owner_type')->nullable()->after('owner_id');
            $table->index(['owner_id', 'owner_type']);
        });
        // اگر doctor_id وجود دارد حذف شود
        if (Schema::hasColumn('sub_users', 'doctor_id')) {
            Schema::table('sub_users', function (Blueprint $table) {
                $table->dropForeign(['doctor_id']);
                $table->dropColumn('doctor_id');
            });
        }
    }

    public function down()
    {
        Schema::table('sub_users', function (Blueprint $table) {
            $table->unsignedBigInteger('doctor_id')->nullable()->after('id');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->dropIndex(['owner_id', 'owner_type']);
            $table->dropColumn(['owner_id', 'owner_type']);
        });
    }
};

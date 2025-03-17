<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('best_doctors', function (Blueprint $table) {
            $table->decimal('star_rating', 2, 1)->default(0.0)->after('best_consultant'); // امتیاز ستاره (0.0 تا 5.0)
        });
    }

    public function down(): void
    {
        Schema::table('best_doctors', function (Blueprint $table) {
            $table->dropColumn('star_rating');
        });
    }
};

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('specialties', function (Blueprint $table) {
            $table->id(); // شناسه یکتا
            $table->string('name'); // نام تخصص
            $table->string('slug')->unique();
            $table->text('description')->nullable(); // توضیحات تخصص (اختیاری)
            $table->tinyInteger('status')->default(0);

            $table->timestamps(); // زمان ایجاد و به روز رسانی رکورد
        });
         // اجرای Seeder به صورت خودکار
         Artisan::call('db:seed', [
            '--class' => 'SpecialtiesSeeder',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('specialties');
        
    }
};

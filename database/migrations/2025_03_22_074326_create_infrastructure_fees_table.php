<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('infrastructure_fees', function (Blueprint $table) {
            $table->id();
            $table->enum('appointment_type', ['in_person', 'phone', 'text', 'video']);
            $table->decimal('fee', 8, 2)->default(0); // هزینه کارمزد و زیرساخت
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // اضافه کردن مقادیر پیش‌فرض
        \DB::table('infrastructure_fees')->insert([
            ['appointment_type' => 'in_person', 'fee' => 50000, 'is_active' => true],
            ['appointment_type' => 'phone', 'fee' => 30000, 'is_active' => true],
            ['appointment_type' => 'text', 'fee' => 20000, 'is_active' => true],
            ['appointment_type' => 'video', 'fee' => 40000, 'is_active' => true],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('infrastructure_fees');
    }
};

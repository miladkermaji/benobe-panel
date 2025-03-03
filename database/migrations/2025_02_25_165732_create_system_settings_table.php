<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // کلید تنظیم (مثلاً company_card_number)
            $table->string('value'); // مقدار (مثلاً شماره کارت)
            $table->string('description')->nullable(); // توضیحات (اختیاری)
            $table->timestamps();
        });

        // مقدار پیش‌فرض برای شماره کارت شرکت
        \DB::table('system_settings')->insert([
            'key' => 'company_card_number',
            'value' => '5892-1013-4092-8585', // فرمت‌شده برای تست
            'description' => 'شماره کارت مقصد برای شارژ کیف پول',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
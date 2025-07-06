<?php
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');                   // نام خدمت
            $table->text('description')->nullable();  // توضیحات (اختیاری)
            $table->boolean('status')->default(true); // وضعیت (فعال/غیرفعال)
            $table->timestamps();
        });

        try {
            Artisan::call('db:seed', [
                '--class' => 'ServicesSeeder',
            ]);
        } catch (\Exception $e) {
            Log::warning('اجرای Seeder با خطا مواجه شد: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};

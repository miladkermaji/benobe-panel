<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckDatabaseStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'بررسی وضعیت اتصال به پایگاه داده';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 در حال بررسی وضعیت اتصال به پایگاه داده...');

        try {
            // Try to connect to database
            $pdo = DB::connection()->getPdo();

            $this->info('✅ اتصال به پایگاه داده برقرار است!');
            $this->info('📊 اطلاعات اتصال:');
            $this->info('   - نوع پایگاه داده: ' . $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME));
            $this->info('   - نسخه سرور: ' . $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION));
            $this->info('   - نام پایگاه داده: ' . config('database.connections.mysql.database'));
            $this->info('   - میزبان: ' . config('database.connections.mysql.host'));
            $this->info('   - پورت: ' . config('database.connections.mysql.port'));

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ خطا در اتصال به پایگاه داده!');
            $this->error('پیام خطا: ' . $e->getMessage());

            $this->warn('🔧 راه‌حل‌های پیشنهادی:');
            $this->warn('   1. اطمینان حاصل کنید که سرور MySQL در حال اجرا است');
            $this->warn('   2. تنظیمات اتصال در فایل .env را بررسی کنید');
            $this->warn('   3. نام کاربری و رمز عبور را بررسی کنید');
            $this->warn('   4. پورت MySQL را بررسی کنید (معمولاً 3306)');

            return Command::FAILURE;
        }
    }
}

<?php
namespace Database\Seeders;

use App\Models\Admin\Doctors\DoctorManagements\DoctorComment;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DoctorCommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // گرفتن یا ایجاد چند پزشک تصادفی
        $doctors = Doctor::all();
        if ($doctors->isEmpty()) {
            // اگر پزشکی وجود نداشت، یه پزشک تست ایجاد کن
            $doctor = Doctor::factory()->create([
                'first_name' => 'کیوان',
                'last_name'  => 'فیاض مقدم',
                'mobile'     => '09123456789',
            ]);
            $doctors = collect([$doctor]);
        }

        // آرایه‌های داده‌های تست
        $userNames = [
            'آروان حسینی',
            'مهین کیومرثیان',
            'آنیتا حیدری',
            'مریم رحمانی فر',
            'چمن اصلانی اسلمرز',
            'غزل شکری',
            'همیلا حبیبی',
            'دلووان فتحی',
            'سمیه مرادی',
            'جلال حیدریان',
            'بشری روان بخش',
            'دریا مفاخری',
            'سحر لطیفی',
            'زانا محمدی',
            'صنعان امینی',
            'ارژنگ اردلانی',
            'شهریاری',
            'نیاز امجدی',
            'سجادقربانی',
            'حامد خالدیان',
        ];

        $comments = [
            'بسیار پزشک ماهر و باسوادی هستند',
            'بسیار عالی هستن',
            'خانم باشخصیت و در طبابت صبوری به خرج دادن.',
            'عدم دقت در بررسی مشکل بیمار و بیشتر به مانیتور داخل مطب توجه می‌کنه',
            'سلام برای نوزاد 20روزه رفتیم مطب اصلا توجه نکرد',
            'من خیلی راضی بودم واقعا نتیجه بخش بود',
            'چرا نوبت گرفتن برام ممکن نیست هر بار میخوام اینترنتی وقت بگیرم نوبتا پر شدن',
            'دکتر با اخلاق و با وجدان',
            'خیلی مهربون، کار بلد، آرام، و باتجربه هستند عالیند',
            'سلام لطفا راهنمایی کنید چطور نوبت بگیرم',
        ];

        // ایجاد 50 نظر تصادفی
        for ($i = 1; $i <= 50; $i++) {
            DoctorComment::create([
                'doctor_id'  => $doctors->random()->id,
                'user_name'  => $userNames[array_rand($userNames)],
                'user_phone' => '09' . Str::random(9), // شماره تلفن تصادفی
                'comment'    => $comments[array_rand($comments)],
                'status'     => rand(0, 1),                                     // 0 یا 1 برای غیرفعال/فعال
                'ip_address' => '192.168.' . rand(1, 255) . '.' . rand(1, 255), // IP تصادفی
                'created_at' => Carbon::now()->subDays(rand(1, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59)),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}

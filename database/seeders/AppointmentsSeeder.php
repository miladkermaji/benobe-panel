<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppointmentsSeeder extends Seeder
{
    public function run(): void
    {
        $appointments = [
            [
                'doctor_id' => 1,
                'patient_id' => 1,
                'insurance_id' => null,
                'clinic_id' => 1,
                'duration' => 20,
                'consultation_type' => 'general',
                'priority' => 'low',
                'payment_status' => 'paid',
                'appointment_type' => 'in_person',
                'appointment_date' => Carbon::today()->toDateString(),
                'start_time' => '09:00:00',
                'end_time' => '09:20:00',
                'reserved_at' => Carbon::now(),
                'confirmed_at' => Carbon::now(),
                'status' => 'scheduled',
                'attendance_status' => null,
                'notes' => 'نوبت اولیه برای ویزیت عمومی',
                'title' => 'ویزیت عمومی',
                'tracking_code' => 'AP-' . uniqid(),
                'max_appointments' => 1,
                'fee' => 100000.00,
                'appointment_category' => 'initial',
                'location' => 'کلینیک شماره یک',
                'notification_sent' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'doctor_id' => 2,
                'patient_id' => 2,
                'insurance_id' => null, // تغییر به null برای جلوگیری از خطا
                'clinic_id' => null,
                'duration' => 30,
                'consultation_type' => 'specialized',
                'priority' => 'medium',
                'payment_status' => 'unpaid',
                'appointment_type' => 'phone',
                'appointment_date' => Carbon::tomorrow()->toDateString(),
                'start_time' => '15:30:00',
                'end_time' => '16:00:00',
                'reserved_at' => Carbon::now(),
                'confirmed_at' => null,
                'status' => 'scheduled',
                'attendance_status' => null,
                'notes' => 'تماس تلفنی برای پیگیری وضعیت',
                'title' => 'پیگیری تلفنی',
                'tracking_code' => 'AP-' . uniqid(),
                'max_appointments' => 1,
                'fee' => 120000.00,
                'appointment_category' => 'follow_up',
                'location' => 'تماس تلفنی',
                'notification_sent' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($appointments as $appointment) {
            DB::table('appointments')->insert($appointment);
        }
    }
}
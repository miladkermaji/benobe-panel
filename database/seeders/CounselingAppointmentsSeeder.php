<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CounselingAppointmentsSeeder extends Seeder
{
    public function run(): void
    {
        $appointments = [
            [
                'doctor_id' => 1,
                'patient_id' => 1,
                'insurance_id' => null,
                'clinic_id' => 1,
                'duration' => 30,
                'consultation_type' => 'general',
                'priority' => 'medium',
                'payment_status' => 'paid',
                'appointment_type' => 'in_person',
                'appointment_date' => Carbon::today()->toDateString(),
                'start_time' => '10:00:00',
                'end_time' => '10:30:00',
                'reserved_at' => Carbon::now(),
                'confirmed_at' => Carbon::now(),
                'status' => 'scheduled',
                'attendance_status' => null,
                'notes' => 'مشاوره اولیه برای بررسی وضعیت بیمار',
                'title' => 'مشاوره عمومی',
                'tracking_code' => 'CA-' . uniqid(),
                'max_appointments' => 1,
                'fee' => 150000.00,
                'appointment_category' => 'initial',
                'location' => 'کلینیک مرکزی',
                'notification_sent' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'doctor_id' => 2,
                'patient_id' => 2,
                'insurance_id' => null,
                'clinic_id' => null,
                'duration' => 45,
                'consultation_type' => 'specialized',
                'priority' => 'high',
                'payment_status' => 'pending',
                'appointment_type' => 'online',
                'appointment_date' => Carbon::tomorrow()->toDateString(),
                'start_time' => '14:00:00',
                'end_time' => '14:45:00',
                'reserved_at' => Carbon::now(),
                'confirmed_at' => null,
                'status' => 'scheduled',
                'attendance_status' => null,
                'notes' => 'مشاوره تخصصی آنلاین',
                'title' => 'مشاوره تخصصی',
                'tracking_code' => 'CA-' . uniqid(),
                'max_appointments' => 1,
                'fee' => 200000.00,
                'appointment_category' => 'follow_up',
                'location' => 'آنلاین',
                'notification_sent' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($appointments as $appointment) {
            DB::table('counseling_appointments')->insert($appointment);
        }
    }
}
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MedicalCenterResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'title' => $this->title,
            'address' => $this->address,
            'type' => $this->type, // نوع مرکز (hospital, clinic, etc.)
            'center_tariff_type' => $this->Center_tariff_type, // نوع تعرفه (دولتی، خصوصی و غیره)
            'daycare_centers' => $this->Daycare_centers, // مراکز شبانه‌روزی
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'consultation_fee' => $this->consultation_fee,
            'payment_methods' => $this->payment_methods,
            'is_active' => $this->is_active,
            'location_confirmed' => $this->location_confirmed,
            'avatar' => $this->avatar ? asset('storage/' . $this->avatar) : null,
            'specialties' => $this->specialties->pluck('name'), // نام تخصص‌ها
            'insurances' => $this->insurances->pluck('name'), // نام بیمه‌ها
            'province' => $this->province ? $this->province->name : null,
            'city' => $this->city ? $this->city->name : null,
            'average_rating' => $this->average_rating ?? 0.0, // امتیاز متوسط
            'reviews_count' => $this->reviews_count ?? 0, // تعداد نظرات
            'recommendation_percentage' => $this->recommendation_percentage ?? 0, // درصد پیشنهاد کاربران
            'profile_url' => route('api.medical-centers.profile', ['slug' => $this->slug]),
            'doctors_count' => $this->doctors->count(), // تعداد پزشکان
            'services' => $this->services ?? [], // خدمات (در صورت وجود)
        ];
    }
}

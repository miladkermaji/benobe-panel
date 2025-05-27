<?php

namespace App\Livewire\Dr\Panel\Layouts\Partials;

use Livewire\Component;
use App\Models\DoctorWallet;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\NotificationRecipient;
use Illuminate\Database\Eloquent\Collection;

class HeaderComponent extends Component
{
    public $walletBalance = 0;
    public $notifications;
    public $unreadCount = 0;

    public function mount()
    {
        // مقداردهی اولیه به‌عنوان کالکشن خالی
        $this->notifications = new Collection();

        if (Auth::guard('doctor')->check()) {
            // کاربر پزشک است
            $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
            $doctorMobile = Auth::guard('doctor')->user()->mobile; // شماره موبایل پزشک

            $this->walletBalance = DoctorWallet::where('doctor_id', $doctorId)
                ->sum('balance');

            // لود اعلان‌ها برای پزشک (بر اساس recipient_type و recipient_id)
            $doctorNotifications = NotificationRecipient::where('recipient_type', 'App\\Models\\Doctor')
                ->where('recipient_id', $doctorId)
                ->where('is_read', false) // فقط اعلان‌های خوانده‌نشده
                ->with('notification')
                ->get();

            // لود اعلان‌های تکی که شماره موبایلشون با شماره موبایل پزشک برابر است
            $singleNotifications = NotificationRecipient::where('recipient_type', 'phone')
                ->where('phone_number', $doctorMobile)
                ->where('is_read', false) // فقط اعلان‌های خوانده‌نشده
                ->with('notification')
                ->get();

            // ادغام اعلان‌ها
            $this->notifications = $doctorNotifications->merge($singleNotifications);
        } elseif (Auth::guard('secretary')->check()) {
            // کاربر منشی است
            $secretary = Auth::guard('secretary')->user();
            $doctorId = $secretary->doctor_id; // آیدی پزشک مرتبط با منشی
            $secretaryMobile = $secretary->mobile; // شماره موبایل منشی

            if ($doctorId) {
                $this->walletBalance = DoctorWallet::where('doctor_id', $doctorId)
                    ->sum('balance');
            }

            // لود اعلان‌ها برای منشی (بر اساس recipient_type و recipient_id)
            $secretaryNotifications = NotificationRecipient::where('recipient_type', 'App\\Models\\Secretary')
                ->where('recipient_id', $secretary->id)
                ->where('is_read', false) // فقط اعلان‌های خوانده‌نشده
                ->with('notification')
                ->get();

            // لود اعلان‌های تکی که شماره موبایلشون با شماره موبایل منشی برابر است
            $singleNotifications = NotificationRecipient::where('recipient_type', 'phone')
                ->where('phone_number', $secretaryMobile)
                ->where('is_read', false) // فقط اعلان‌های خوانده‌نشده
                ->with('notification')
                ->get();

            // ادغام اعلان‌ها
            $this->notifications = $secretaryNotifications->merge($singleNotifications);
        }

        // محاسبه تعداد اعلان‌های خوانده‌نشده
        $this->unreadCount = $this->notifications->count();
    }

    // متد برای علامت‌گذاری اعلان به‌عنوان خوانده‌شده
    public function markAsRead($recipientId)
    {
        $recipient = NotificationRecipient::find($recipientId);
        if ($recipient) {
            $recipient->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

            // به‌روزرسانی لیست اعلان‌ها
            $this->notifications = $this->notifications->filter(fn ($item) => $item->id != $recipientId);
            $this->unreadCount = $this->notifications->count();
        }
    }

    public function render()
    {
        return view('livewire.dr.panel.layouts.partials.header-component');
    }
}

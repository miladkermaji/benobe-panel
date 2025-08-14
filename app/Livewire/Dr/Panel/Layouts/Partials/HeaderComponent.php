<?php

namespace App\Livewire\Dr\Panel\Layouts\Partials;

use Livewire\Component;
use App\Models\DoctorWallet;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\NotificationRecipient;
use Illuminate\Database\Eloquent\Collection;

class HeaderComponent extends Component
{
    public $walletBalance = 0;
    public $notifications;
    public $unreadCount = 0;
    public $selectedMedicalCenterId = null;
    public $selectedMedicalCenterName = 'مشاوره آنلاین به نوبه';
    public $medicalCenters;
    public $isDarkMode = false;

    public function mount()
    {
        try {
            $this->notifications = new Collection();
            $this->medicalCenters = new Collection(); // Initialize medicalCenters

            // Initialize dark mode state (will be updated by JavaScript)
            $this->isDarkMode = false;

            if (Auth::guard('doctor')->check()) {
                $doctor = Auth::guard('doctor')->user();
                $doctorId = $doctor->id;
                $doctorMobile = $doctor->mobile;

                // بارگذاری مراکز درمانی
                $this->loadMedicalCenters($doctor);
                // تنظیم مرکز درمانی انتخاب‌شده
                $this->setSelectedMedicalCenterFromDatabase($doctor);

                $this->walletBalance = DoctorWallet::where('doctor_id', $doctorId)
                    ->sum('balance');

                // لود اعلان‌ها برای پزشک
                $doctorNotifications = NotificationRecipient::where('recipient_type', 'App\\Models\\Doctor')
                    ->where('recipient_id', $doctorId)
                    ->where('is_read', false)
                    ->with('notification')
                    ->get();

                $singleNotifications = NotificationRecipient::where('recipient_type', 'phone')
                    ->where('phone_number', $doctorMobile)
                    ->where('is_read', false)
                    ->with('notification')
                    ->get();

                $this->notifications = $doctorNotifications->merge($singleNotifications);
            } elseif (Auth::guard('secretary')->check()) {
                $secretary = Auth::guard('secretary')->user();
                $doctorId = $secretary->doctor_id;
                $secretaryMobile = $secretary->mobile;

                // بارگذاری مراکز درمانی
                $this->loadMedicalCenters($secretary->doctor);

                // تنظیم مرکز درمانی انتخاب‌شده
                $this->setSelectedMedicalCenterFromDatabase($secretary->doctor);

                if ($doctorId) {
                    $this->walletBalance = DoctorWallet::where('doctor_id', $doctorId)
                        ->sum('balance');
                }

                $secretaryNotifications = NotificationRecipient::where('recipient_type', 'App\\Models\\Secretary')
                    ->where('recipient_id', $secretary->id)
                    ->where('is_read', false)
                    ->with('notification')
                    ->get();

                $singleNotifications = NotificationRecipient::where('recipient_type', 'phone')
                    ->where('phone_number', $secretaryMobile)
                    ->where('is_read', false)
                    ->with('notification')
                    ->get();

                $this->notifications = $secretaryNotifications->merge($singleNotifications);
            }

            $this->unreadCount = $this->notifications->count();
        } catch (\Exception $e) {
            // Log the error and set default values
            Log::error('Error in HeaderComponent mount method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Set default values to prevent further errors
            $this->selectedMedicalCenterId = null;
            $this->selectedMedicalCenterName = 'مشاوره آنلاین به نوبه';
            $this->notifications = new Collection();
            $this->unreadCount = 0;
            $this->walletBalance = 0;
        }
    }

    protected function loadMedicalCenters($doctor)
    {
        try {
            if ($doctor) {
                $this->medicalCenters = $doctor->medicalCenters()
                    ->select('medical_centers.*')
                    ->where('medical_centers.type', 'policlinic')
                    ->whereNull('medical_centers.deleted_at') // Exclude soft-deleted medical centers
                    ->with(['province', 'city'])
                    ->get();
            }
        } catch (\Exception $e) {
            // Log the error and set default values
            Log::error('Error in loadMedicalCenters', [
                'doctor_id' => $doctor?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Set default values to prevent further errors
            $this->medicalCenters = new Collection();
        }
    }

    protected function setSelectedMedicalCenterFromDatabase($doctor)
    {
        try {
            if ($doctor) {
                // Proactively clean up any invalid medical center selections
                $doctor->cleanupInvalidMedicalCenterSelection();

                // اگر دکتر مرکز درمانی انتخاب‌شده‌ای دارد، از آن استفاده کن
                if ($doctor->selectedMedicalCenter) {
                    if ($doctor->selectedMedicalCenter->medical_center_id) {
                        // Check if medicalCenter exists and is not null
                        if ($doctor->selectedMedicalCenter->hasValidMedicalCenter()) {
                            $this->selectedMedicalCenterId = $doctor->selectedMedicalCenter->medical_center_id;
                            $this->selectedMedicalCenterName = $doctor->selectedMedicalCenter->medicalCenter->name;
                        } else {
                            // Medical center doesn't exist (possibly deleted), reset to null
                            $this->selectedMedicalCenterId = null;
                            $this->selectedMedicalCenterName = 'مشاوره آنلاین به نوبه';

                            // Log detailed debugging information
                            Log::warning('Invalid medical center relationship detected', [
                                'doctor_id' => $doctor->id,
                                'medical_center_id' => $doctor->selectedMedicalCenter->medical_center_id,
                                'selected_medical_center_exists' => $doctor->selectedMedicalCenter ? 'yes' : 'no',
                                'medical_center_relationship_exists' => $doctor->selectedMedicalCenter->medicalCenter ? 'yes' : 'no',
                                'medical_center_trashed' => $doctor->selectedMedicalCenter->medicalCenter ? ($doctor->selectedMedicalCenter->medicalCenter->trashed() ? 'yes' : 'no') : 'n/a',
                                'action' => 'cleaned up invalid record'
                            ]);

                            // Clean up the invalid record using the new method
                            $doctor->cleanupInvalidMedicalCenterSelection();
                        }
                    } else {
                        // مشاوره آنلاین انتخاب شده
                        $this->selectedMedicalCenterId = null;
                        $this->selectedMedicalCenterName = 'مشاوره آنلاین به نوبه';
                    }
                    return;
                }

                // اگر رکوردی وجود ندارد، بررسی کن که آیا مرکز درمانی فعالی دارد یا نه
                $activeMedicalCenters = $doctor->medicalCenters()
                    ->where('medical_centers.type', 'policlinic')
                    ->whereNull('medical_centers.deleted_at')
                    ->get();

                if ($activeMedicalCenters->count() > 0) {
                    // اولین مرکز درمانی فعال را انتخاب کن
                    $firstActiveMedicalCenter = $activeMedicalCenters->first();
                    // مرکز درمانی انتخاب‌شده را در دیتابیس ذخیره کن
                    $doctor->setSelectedMedicalCenter($firstActiveMedicalCenter->id);
                    $doctor->refresh();

                    $this->selectedMedicalCenterId = $firstActiveMedicalCenter->id;
                    $this->selectedMedicalCenterName = $firstActiveMedicalCenter->name;
                } else {
                    // هیچ مرکز درمانی فعالی ندارد، روی مشاوره آنلاین بگذار
                    $this->selectedMedicalCenterId = null;
                    $this->selectedMedicalCenterName = 'مشاوره آنلاین به نوبه';

                    // در دیتابیس رکورد با medical_center_id = null ایجاد کن
                    $doctor->setSelectedMedicalCenter(null);
                }
            }
        } catch (\Exception $e) {
            // Log the error and set default values
            Log::error('Error in setSelectedMedicalCenterFromDatabase', [
                'doctor_id' => $doctor?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Set default values to prevent further errors
            $this->selectedMedicalCenterId = null;
            $this->selectedMedicalCenterName = 'مشاوره آنلاین به نوبه';
        }
    }

    protected function getDarkModePreference()
    {
        // Check if we're in a browser environment
        if (request()->hasHeader('X-Requested-With') && request()->header('X-Requested-With') === 'XMLHttpRequest') {
            // This is an AJAX request, try to get from session or default to false
            return session('dark_mode', false);
        }

        return false; // Default to light mode
    }

    public function toggleDarkMode()
    {
        $this->isDarkMode = !$this->isDarkMode;

        // Dispatch event to update the UI and localStorage
        $this->dispatch('darkModeToggled', ['isDark' => $this->isDarkMode]);
    }

    public function syncDarkMode($isDark)
    {
        $this->isDarkMode = $isDark;
    }

    public function selectMedicalCenter($medicalCenterId = null)
    {
        try {
            $doctor = Auth::guard('doctor')->check()
                ? Auth::guard('doctor')->user()
                : Auth::guard('secretary')->user()->doctor;

            if ($doctor) {
                // اعتبارسنجی مرکز درمانی - فقط کلینیک‌ها
                if ($medicalCenterId && !$doctor->medicalCenters()->where('medical_centers.id', $medicalCenterId)->where('medical_centers.type', 'policlinic')->whereNull('medical_centers.deleted_at')->exists()) {
                    $this->addError('medical_center', 'مرکز درمانی انتخاب‌شده معتبر نیست.');
                    return;
                }

                // ذخیره مرکز درمانی انتخاب‌شده
                $doctor->setSelectedMedicalCenter($medicalCenterId);

                // به‌روزرسانی مقادیر
                $this->selectedMedicalCenterId = $medicalCenterId;
                $this->selectedMedicalCenterName = $medicalCenterId
                    ? ($doctor->medicalCenters()->where('medical_centers.type', 'policlinic')->whereNull('medical_centers.deleted_at')->find($medicalCenterId)?->name ?? 'مرکز درمانی نامشخص')
                    : 'مشاوره آنلاین به نوبه';

                // اطلاع‌رسانی به سایر کامپوننت‌ها
                $this->dispatch('medicalCenterSelected', ['medicalCenterId' => $medicalCenterId]);

                // ارسال رویداد برای ریلود صفحه بعد از چند ثانیه
                $this->dispatch('reloadPageAfterDelay', ['delay' => 3000]); // 3 ثانیه تاخیر
            }
        } catch (\Exception $e) {
            // Log the error and set default values
            Log::error('Error in selectMedicalCenter', [
                'medical_center_id' => $medicalCenterId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Set default values to prevent further errors
            $this->selectedMedicalCenterId = null;
            $this->selectedMedicalCenterName = 'مشاوره آنلاین به نوبه';

            // Show error to user
            $this->addError('medical_center', 'خطا در انتخاب مرکز درمانی. لطفاً دوباره تلاش کنید.');
        }
    }

    public function markAsRead($recipientId)
    {
        $recipient = NotificationRecipient::find($recipientId);
        if ($recipient) {
            $recipient->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

            $this->notifications = $this->notifications->filter(fn ($item) => $item->id != $recipientId);
            $this->unreadCount = $this->notifications->count();
        }
    }

    public function render()
    {
        return view('livewire.dr.panel.layouts.partials.header-component');
    }
}

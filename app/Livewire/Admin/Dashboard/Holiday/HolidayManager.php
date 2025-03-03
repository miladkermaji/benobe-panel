<?php

namespace App\Livewire\Admin\Dashboard\Holiday;

use Carbon\Carbon;
use Livewire\Component;
use Morilog\Jalali\CalendarUtils;
use App\Models\Admin\Dashboard\Holiday\Holiday;

class HolidayManager extends Component
{
    public $selectedDate;
    public $title;
    public $holidays = [];

    public function mount()
    {
        $this->holidays = Holiday::pluck('date')->toArray();
    }

    public function addHoliday()
    {
        // بررسی اینکه مقدار `selectedDate` خالی نباشد
        if (empty($this->selectedDate)) {
            $this->dispatch('show-toastr', type: 'error', message: 'لطفاً تاریخ تعطیلی را انتخاب کنید.');
            return;
        }

        // تبدیل تاریخ جلالی به میلادی
        try {
            $gregorianDate = CalendarUtils::createDatetimeFromFormat('Y-m-d', $this->selectedDate)->format('Y-m-d');
        } catch (\Exception $e) {
            $this->dispatch('show-toastr', type: 'error', message: 'خطا در تبدیل تاریخ. لطفاً تاریخ معتبر انتخاب کنید.');
            return;
        }

        // بررسی تکراری نبودن تاریخ در دیتابیس
        if (Holiday::where('date', $gregorianDate)->exists()) {
            $this->dispatch('show-toastr', type: 'error', message: 'این تاریخ قبلاً ثبت شده است.');
            return;
        }

        $this->validate([
            'selectedDate' => 'required|string',
            'title' => 'nullable|string|max:255'
        ]);

        // ذخیره تاریخ میلادی در دیتابیس
        Holiday::create([
            'date' => $gregorianDate,
            'title' => $this->title
        ]);

        $this->holidays[] = $gregorianDate;
        $this->reset(['selectedDate', 'title']);

        $this->dispatch('show-toastr', type: 'success', message: 'تعطیلی ثبت شد.');
    }


    public function removeHoliday($date)
    {
        // بررسی مقدار ورودی
        if (!preg_match('/\d{4}-\d{2}-\d{2}/', $date)) {
            $date = CalendarUtils::createDatetimeFromFormat('Y-m-d', $date)->format('Y-m-d');
        }

        // حذف از دیتابیس
        $deleted = Holiday::where('date', $date)->delete();

        // بررسی اینکه حذف انجام شده یا نه
        if ($deleted) {
            $this->holidays = array_diff($this->holidays, [$date]);
            $this->dispatch('show-toastr', type: 'success', message: 'تعطیلی حذف شد.');
        } else {
            $this->dispatch('show-toastr', type: 'error', message: 'تعطیلی یافت نشد.');
        }
    }


    public function render()
    {
        return view('livewire.admin.dashboard.holiday.holiday-manager', [
            'holidaysList' => Holiday::all()
        ]);
    }
}


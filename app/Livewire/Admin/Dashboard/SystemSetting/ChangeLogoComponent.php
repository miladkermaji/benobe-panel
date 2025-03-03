<?php

namespace App\Livewire\Admin\Dashboard\SystemSetting;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Admin\Dashboard\SystemSetting\SiteLogo;

class ChangeLogoComponent extends Component
{
    use WithFileUploads;

    public $logo; // فایل آپلود شده
    public $currentLogo; // لوگوی فعلی

    public function mount()
    {
        $this->currentLogo = SiteLogo::where('is_active', true)->first();
        Log::info('ChangeLogoComponent mounted', ['currentLogo' => $this->currentLogo]);
    }

    public function saveLogo()
    {
        Log::info('saveLogo started', ['logo' => $this->logo]);

        // چک فایل آپلود شده
        if (!$this->logo) {
            Log::warning('No logo file uploaded');
            $this->dispatch('toast', 'لطفاً یک فایل لوگو انتخاب کنید.', [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
            return;
        }

        try {
            Log::info('Attempting validation');
            $this->validate([
                'logo' => 'image|mimes:png,jpg,jpeg,svg',
            ]);

            // ذخیره لوگوی جدید یا جایگزینی لوگوی قبلی
            $path = $this->logo->store('logos', 'public');

            // اگه لوگوی قبلی هست، جایگزینش کن
            $existingLogo = SiteLogo::where('is_active', true)->first();
            if ($existingLogo) {
                // حذف فایل قبلی از استوریج
                Storage::disk('public')->delete($existingLogo->path);
                

                // آپدیت لوگوی موجود
                $existingLogo->update([
                    'path' => $path,
                    'is_active' => true,
                ]);
            } else {
                // اگه لوگویی نیست، یه ردیف جدید بساز
                SiteLogo::create([
                    'path' => $path,
                    'is_active' => true,
                ]);
            }

            // آپدیت لوگوی فعلی
            $this->currentLogo = SiteLogo::where('is_active', true)->first();

            // توستر موفقیت
            $this->dispatch('toast', 'لوگو با موفقیت ذخیره شد.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
           
            $this->dispatch('toast', 'خطا در اعتبارسنجی: ' . implode(', ', $e->errors()['logo']), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        } catch (\Exception $e) {
           
            $this->dispatch('toast', 'خطا در ذخیره لوگو: ' . $e->getMessage(), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.dashboard.system-setting.change-logo-component');
    }
}
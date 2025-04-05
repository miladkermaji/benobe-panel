<?php

namespace App\Livewire\Admin\Panel\SystemSetting;

use Livewire\Component;
use Illuminate\Support\Facades\Log;
use App\Models\Admin\Dashboard\SystemSetting\AdminSystemSetting;

class SettingsComponent extends Component
{
    public $settings = [];
    public $activeTab = 'general';

    public function mount()
    {
        $this->loadSettings();
    }

    public function loadSettings()
    {
        $this->settings = AdminSystemSetting::all()->groupBy('group')->toArray();
        Log::info('Settings loaded:', ['settings' => $this->settings]);
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function saveSettings()
    {
        try {
            foreach ($this->settings as $group => $groupSettings) {
                foreach ($groupSettings as $setting) {
                    $key = $setting['key'];
                    $value = $setting['value'];
                    $type = $setting['type'];
                    $groupName = $setting['group'];
                    $description = $setting['description'];

                    // برای تاگل‌ها، مقدار رو به‌صورت صحیح تبدیل می‌کنیم
                    if ($type === 'boolean') {
                        $value = $value ? 1 : 0; // مطمئن می‌شیم 1 یا 0 ذخیره بشه
                    }

                    $existingSetting = AdminSystemSetting::where('key', $key)->first();
                    if ($existingSetting) {
                        $existingSetting->update([
                            'value' => is_array($value) ? json_encode($value) : $value,
                            'type' => $type,
                            'group' => $groupName,
                            'description' => $description,
                        ]);
                    }
                }
            }

            Log::info('Settings saved successfully');

            // ریلود تنظیمات بعد از ذخیره
            $this->loadSettings();

            $this->dispatch('toast', 'تنظیمات با موفقیت ذخیره شد.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving settings:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->dispatch('toast', 'خطا در ذخیره تنظیمات: ' . $e->getMessage(), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.panel.system-setting.settings-component');
    }
}
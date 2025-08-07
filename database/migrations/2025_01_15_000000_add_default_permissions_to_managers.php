<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Manager;
use App\Models\ManagerPermission;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // دسترسی‌های پیش‌فرض برای مدیران سطح 2
        $defaultPermissions = [
            'dashboard',
            'medical_centers',
            'support'
        ];

        // پیدا کردن تمام مدیران سطح 2 که هنوز دسترسی ندارند
        $managers = Manager::where('permission_level', 2)
            ->whereDoesntHave('permissions')
            ->get();

        foreach ($managers as $manager) {
            ManagerPermission::create([
                'manager_id' => $manager->id,
                'permissions' => $defaultPermissions
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // حذف تمام دسترسی‌های پیش‌فرض
        ManagerPermission::whereIn('permissions', [
            ['dashboard', 'medical_centers', 'support']
        ])->delete();
    }
};

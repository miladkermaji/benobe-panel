<?php

use Illuminate\Support\Facades\Route;
use Modules\SendOtp\App\Http\Controllers\SendOtpController;
use App\Http\Controllers\Dr\Panel\DrPanelController;

require __DIR__.'/admin.php';
require __DIR__.'/dr.php';
require __DIR__.'/mc.php';

// MC panel route


// Test route

Route::post('/send-message', [SendOtpController::class, 'sendMessage'])->name('send.message');
//  manager  routes
// end manager  routes
Route::middleware(['web', 'manager'])->prefix('admin/panel/tools')->group(function () {
    Route::get('/recipients-search', [\App\Http\Controllers\Admin\Panel\Tools\Notification\NotificationController::class, 'recipientsSearch']);
});
// Route for AJAX user search (for Select2 in subscription forms)
Route::get('/admin/api/users/search', [\App\Http\Controllers\Admin\UserSearchController::class, 'search']);
Route::get('/admin/api/doctors/search', [\App\Http\Controllers\Admin\DoctorSearchController::class, 'search']);

// Storage files route
Route::get('storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);

    if (file_exists($filePath)) {
        $file = new \Illuminate\Http\File($filePath);
        return response()->file($filePath);
    }

    abort(404);
})->where('path', '.*');

// System status route
Route::get('/system/status', function () {
    return view('errors.system-status');
})->name('system.status');

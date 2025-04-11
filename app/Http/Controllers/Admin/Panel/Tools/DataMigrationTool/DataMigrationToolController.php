<?php

namespace App\Http\Controllers\Admin\Panel\Tools\DataMigrationTool;

use App\Http\Controllers\Admin\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class DataMigrationToolController extends Controller
{
    public function index()
    {
        return view('admin.panel.tools.data-migration-tool.index');
    }

    public function downloadLog($filename)
    {
        $path = "logs/{$filename}";
        if (Storage::disk('local')->exists($path)) {
            /* return Storage::disk('local')->download($path, 'migration_log.txt'); */
            // یا می‌تونی اینجوری بنویسی:
             return response()->download(Storage::disk('local')->path($path), 'migration_log.txt');
        }
        return redirect()->back()->with('error', 'فایل لاگ یافت نشد.');
    }
}

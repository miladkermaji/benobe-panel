<?php

namespace App\Http\Controllers\Admin\Panel\Tools\DataMigrationTool;

use App\Http\Controllers\Admin\Controller;

class DataMigrationToolController extends Controller
{
    public function index()
    {
        return view('admin.panel.tools.data-migration-tool.index');
    }
}

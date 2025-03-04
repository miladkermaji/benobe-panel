<?php

namespace App\Http\Controllers\Admin\Panel\Tools\FileManager;

use App\Http\Controllers\Controller;
use Collator;

class FileManagerController extends Controller
{
 public function index()
 {
  return view('admin.panel.tools.file-manager.index');
 }
}
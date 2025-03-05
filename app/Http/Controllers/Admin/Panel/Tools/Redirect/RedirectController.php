<?php

namespace App\Http\Controllers\Admin\Panel\Tools\Redirect;

use App\Http\Controllers\Admin\Controller;

class RedirectController extends Controller
{
 public function index()
 {
  return view('admin.panel.tools.redirects.index');
 }
 public function create()
 {
  return view('admin.panel.tools.redirects.create');
 }
 public function edit($id)
 {
  return view('admin.panel.tools.redirects.edit', compact('id'));
 }

 public function update($id)
 {
  // منطق به Livewire منتقل شده
 }

 public function toggle()
 {
  // منطق به Livewire منتقل شده
 }

 public function destroy($id)
 {
  // منطق به Livewire منتقل شده
 }
}
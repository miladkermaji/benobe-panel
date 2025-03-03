<?php

namespace App\Http\Controllers\Admin\Hospitals\HospitalsManagement;

use App\Http\Controllers\Admin\Controller;

class HospitalsManagementController extends Controller
{
    public function index()
    {
        return view('admin.content.hospitals.hospitals-management.index');
    }

    public function create()
    {
        return view('admin.content.hospitals.hospitals-management.create');
    }

    public function edit($id)
    {
        return view('admin.content.hospitals.hospitals-management.edit', compact('id'));
    }

    // بقیه متدها (store, update, destroy) فعلاً لازم نیست چون با Livewire کار می‌کنیم
}
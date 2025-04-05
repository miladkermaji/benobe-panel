<?php

namespace App\Http\Controllers\Admin\Panel\DoctorWallet;

use App\Http\Controllers\Admin\Controller;

class DoctorWalletController extends Controller
{
    public function index()
    {
        return view('admin.panel.doctor-wallets.index');
    }

    public function create()
    {
        return view('admin.panel.doctor-wallets.create');
    }

    public function edit($id)
    {
        return view('admin.panel.doctor-wallets.edit', compact('id'));
    }
}
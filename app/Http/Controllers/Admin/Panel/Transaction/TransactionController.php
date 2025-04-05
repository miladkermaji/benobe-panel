<?php

namespace App\Http\Controllers\Admin\Panel\Transaction;

use App\Http\Controllers\Admin\Controller;

class TransactionController extends Controller
{
    public function index()
    {
        return view('admin.panel.transactions.index');
    }

    public function create()
    {
        return view('admin.panel.transactions.create');
    }

    public function edit($id)
    {
        return view('admin.panel.transactions.edit', compact('id'));
    }
}
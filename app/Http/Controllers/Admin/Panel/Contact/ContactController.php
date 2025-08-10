<?php

namespace App\Http\Controllers\Admin\Panel\Contact;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        return view('admin.panel.contact.index');
    }

    public function show($id)
    {
        return view('admin.panel.contact.show', compact('id'));
    }
} 
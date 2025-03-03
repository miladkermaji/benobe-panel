<?php

namespace App\Http\Controllers\Admin\Doctors\OrderVisit;

use App\Http\Controllers\Admin\Controller;
use Illuminate\Http\Request;

class OrderVisitController extends Controller
{
    public function index()
    {
        return view('admin.content.doctors.order-visit.index');
    }

    public function show($id)
    {
        return view('admin.content.doctors.order-visit.show', ['orderVisitId' => $id]);
    }
}
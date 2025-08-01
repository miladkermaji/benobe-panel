<?php

namespace App\Http\Controllers\Dr\Panel\DoctorComments;

use App\Http\Controllers\Dr\Controller;

class DoctorCommentController extends Controller
{
    public function index()
    {
        return view('dr.panel.doctor-comments.index');
    }
}
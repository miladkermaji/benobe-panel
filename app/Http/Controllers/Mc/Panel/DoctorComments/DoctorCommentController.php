<?php

namespace App\Http\Controllers\Mc\Panel\DoctorComments;

use App\Http\Controllers\Mc\Controller;

class DoctorCommentController extends Controller
{
    public function index()
    {
        return view('mc.panel.doctor-comments.index');
    }
}

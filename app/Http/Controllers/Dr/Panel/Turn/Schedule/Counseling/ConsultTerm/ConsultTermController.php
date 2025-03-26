<?php

namespace App\Http\Controllers\Dr\Panel\Turn\Schedule\Counseling\ConsultTerm;

use App\Http\Controllers\Dr\Controller;
use Illuminate\Http\Request;

class ConsultTermController extends Controller
{
    public function index()
    {
        return view('dr.panel.turn.schedule.Counseling.consult-term.index');
    }
}

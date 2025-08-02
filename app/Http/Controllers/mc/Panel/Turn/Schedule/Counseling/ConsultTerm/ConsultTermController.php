<?php

namespace App\Http\Controllers\Mc\Panel\Turn\Schedule\Counseling\ConsultTerm;

use App\Http\Controllers\Mc\Controller;
use Illuminate\Http\Request;

class ConsultTermController extends Controller
{
    public function index()
    {
        return view('mc.panel.turn.schedule.Counseling.consult-term.index');
    }
}

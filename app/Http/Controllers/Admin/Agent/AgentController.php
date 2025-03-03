<?php

namespace App\Http\Controllers\Admin\Agent;

use App\Http\Controllers\Admin\Controller;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function index()
    {
        return view('admin.content.agent.agent');
    }

    public function agentWallet()
    {
        return view('admin.content.agent.agent_wallet');
    }

    public function create()
    {
        return view('admin.content.agent.create');
    }

    public function edit($agentId)
    {
        return view('admin.content.agent.edit', ['agentId' => $agentId]);
    }
}
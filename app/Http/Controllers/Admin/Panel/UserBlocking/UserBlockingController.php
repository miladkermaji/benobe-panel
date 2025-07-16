<?php

namespace App\Http\Controllers\Admin\Panel\UserBlocking;

use App\Http\Controllers\Admin\Controller;

class UserBlockingController extends Controller
{
    public function index()
    {
        return view('admin.panel.user-blockings.index');
    }

    public function create()
    {
        return view('admin.panel.user-blockings.create');
    }

    public function edit($id)
    {
        return view('admin.panel.user-blockings.edit', compact('id'));
    }

    public function searchUsers(\Illuminate\Http\Request $request)
    {
        $type = $request->get('type', 'user');
        $term = $request->get('q', '');

        if ($type === 'user') {
            $query = \App\Models\User::query();
        } else {
            $query = \App\Models\Doctor::query();
        }

        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', "%$term%")
                  ->orWhere('last_name', 'like', "%$term%")
                  ->orWhere('mobile', 'like', "%$term%");
            });
        }

        $results = $query->select('id', 'first_name', 'last_name', 'mobile')->limit(20)->get();

        return response()->json([
            'results' => $results->map(function ($item) {
                return [
                    'id' => $item->id,
                    'text' => $item->first_name . ' ' . $item->last_name . ' (' . $item->mobile . ')'
                ];
            })->values()->all()
        ]);
    }
}

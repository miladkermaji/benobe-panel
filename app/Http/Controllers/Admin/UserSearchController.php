<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserSearchController extends Controller
{
    public function search(Request $request)
    {
        $q = $request->input('q');
        $users = User::query()
            ->where(function ($query) use ($q) {
                $query->where('first_name', 'like', "%$q%")
                      ->orWhere('last_name', 'like', "%$q%")
                      ->orWhere('mobile', 'like', "%$q%")
                      ->orWhere('id', $q);
            })
            ->select('id', 'first_name', 'last_name')
            ->limit(20)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'text' => $user->first_name . ' ' . $user->last_name
                ];
            });

        return response()->json(['results' => $users]);
    }
}

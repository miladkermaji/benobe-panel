<?php

namespace App\Http\Controllers\Admin\Panel\Stories;

use Illuminate\Routing\Controller;
use App\Models\User;
use App\Models\Doctor;
use App\Models\MedicalCenter;
use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StoriesController extends Controller
{
    public function index()
    {
        return view('admin.panel.stories.index');
    }

    public function create()
    {
        return view('admin.panel.stories.create');
    }

    public function edit($id)
    {
        return view('admin.panel.stories.edit', compact('id'));
    }

    public function analytics()
    {
        return view('admin.panel.stories.analytics');
    }

    /**
     * Get users for Ajax Select2
     */
    public function getUsers(Request $request): JsonResponse
    {
        $search = $request->get('search', '');
        $page = $request->get('page', 1);
        $perPage = 20;

        $query = User::select('id', 'first_name', 'last_name', 'mobile')
            ->where('status', true);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('first_name')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $results = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'text' => $user->first_name . ' ' . $user->last_name . ' (' . $user->mobile . ')'
            ];
        });

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => $users->count() === $perPage
            ]
        ]);
    }

    /**
     * Get doctors for Ajax Select2
     */
    public function getDoctors(Request $request): JsonResponse
    {
        $search = $request->get('search', '');
        $page = $request->get('page', 1);
        $perPage = 20;

        $query = Doctor::select('id', 'first_name', 'last_name', 'mobile')
            ->where('status', true);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $doctors = $query->orderBy('first_name')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $results = $doctors->map(function ($doctor) {
            return [
                'id' => $doctor->id,
                'text' => $doctor->first_name . ' ' . $doctor->last_name . ' (' . $doctor->mobile . ')'
            ];
        });

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => $doctors->count() === $perPage
            ]
        ]);
    }

    /**
     * Get medical centers for Ajax Select2
     */
    public function getMedicalCenters(Request $request): JsonResponse
    {
        $search = $request->get('search', '');
        $page = $request->get('page', 1);
        $perPage = 20;

        $query = MedicalCenter::select('id', 'name', 'title')
            ->where('is_active', true);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $medicalCenters = $query->orderBy('name')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $results = $medicalCenters->map(function ($center) {
            return [
                'id' => $center->id,
                'text' => $center->name . ' (' . $center->title . ')'
            ];
        });

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => $medicalCenters->count() === $perPage
            ]
        ]);
    }

    /**
     * Get managers for Ajax Select2
     */
    public function getManagers(Request $request): JsonResponse
    {
        $search = $request->get('search', '');
        $page = $request->get('page', 1);
        $perPage = 20;

        $query = Manager::select('id', 'first_name', 'last_name')
            ->where('is_active', true);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $managers = $query->orderBy('first_name')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $results = $managers->map(function ($manager) {
            return [
                'id' => $manager->id,
                'text' => $manager->first_name . ' ' . $manager->last_name
            ];
        });

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => $managers->count() === $perPage
            ]
        ]);
    }
}

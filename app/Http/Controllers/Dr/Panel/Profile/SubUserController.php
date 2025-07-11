<?php

namespace App\Http\Controllers\Dr\Panel\Profile;

use App\Http\Controllers\Dr\Controller;
use App\Models\SubUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SubUserController extends Controller
{
    public function index()
    {
        $doctorId = Auth::guard('doctor')->id();
        $subUsers = SubUser::with('subuserable')->where('doctor_id', $doctorId)->get();
        $users = User::paginate(50); // صفحه‌بندی کاربران، هر بار ۵۰ کاربر

        return view('dr.panel.profile.subuser', compact('subUsers', 'users'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ], [
            'user_id.required' => 'لطفاً یک کاربر را انتخاب کنید.',
            'user_id.exists'   => 'کاربر انتخاب‌شده در سیستم وجود ندارد.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $doctorId = Auth::guard('doctor')->id();

        $existingSubUser = SubUser::where('doctor_id', $doctorId)
            ->where('subuserable_id', $request->user_id)
            ->where('subuserable_type', User::class)
            ->first();

        if ($existingSubUser) {
            return response()->json(['error' => 'این کاربر قبلاً اضافه شده است!'], 422);
        }

        SubUser::create([
            'doctor_id'        => $doctorId,
            'subuserable_id'   => $request->user_id,
            'subuserable_type' => User::class,
        ]);

        return response()->json([
            'message'  => 'کاربر زیرمجموعه با موفقیت اضافه شد!',
            'subUsers' => SubUser::where('doctor_id', $doctorId)->with('subuserable')->get(),
            'users'    => User::all(),
        ]);
    }

    public function edit($id)
    {
        $subUser = SubUser::with('subuserable')->findOrFail($id);
        $user = User::find($subUser->subuserable_id);

        return response()->json([
            'id'      => $subUser->id,
            'user_id' => $subUser->subuserable_id,
            'user'    => $user,
        ]);
    }

    public function update(Request $request, $id)
    {
        $subUser = SubUser::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ], [
            'user_id.required' => 'لطفاً یک کاربر را انتخاب کنید.',
            'user_id.exists'   => 'کاربر انتخاب‌شده در سیستم وجود ندارد.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($subUser->subuserable_id == $request->user_id && $subUser->subuserable_type == User::class) {
            return response()->json(['message' => 'بدون تغییر! مقدار جدید همان مقدار قبلی است.']);
        }

        $existingSubUser = SubUser::where('doctor_id', $subUser->doctor_id)
            ->where('subuserable_id', $request->user_id)
            ->where('subuserable_type', User::class)
            ->first();

        if ($existingSubUser) {
            return response()->json(['error' => 'این کاربر قبلاً به لیست اضافه شده است!'], 422);
        }

        $subUser->subuserable_id = $request->user_id;
        $subUser->subuserable_type = User::class;
        $subUser->save();

        return response()->json([
            'message'  => 'کاربر زیرمجموعه با موفقیت ویرایش شد!',
            'subUsers' => SubUser::where('doctor_id', $subUser->doctor_id)->with('subuserable')->get(),
            'users'    => User::all(),
        ]);
    }

    public function destroy($id)
    {
        $subUser  = SubUser::findOrFail($id);
        $doctorId = $subUser->doctor_id;
        $subUser->delete();

        return response()->json([
            'message'  => 'کاربر زیرمجموعه حذف شد!',
            'subUsers' => SubUser::where('doctor_id', $doctorId)->with('subuserable')->get(),
        ]);
    }

    public function destroyMultiple(Request $request)
    {
        $doctorId = Auth::guard('doctor')->id();
        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['error' => 'هیچ کاربری انتخاب نشده است.'], 422);
        }
        SubUser::where('doctor_id', $doctorId)->whereIn('id', $ids)->delete();
        $subUsers = SubUser::where('doctor_id', $doctorId)->with('subuserable')->orderByDesc('id')->paginate(20);
        return response()->json([
            'message' => 'کاربران زیرمجموعه با موفقیت حذف شدند!',
            'subUsers' => $subUsers
        ]);
    }

    public function list(Request $request)
    {
        $doctorId = Auth::guard('doctor')->id();
        $query = SubUser::with('subuserable')->where('doctor_id', $doctorId);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('subuserable', function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                  ->orWhere('last_name', 'like', "%$search%")
                  ->orWhere('mobile', 'like', "%$search%")
                  ->orWhere('national_code', 'like', "%$search%");
            });
        }

        $perPage = $request->get('per_page', 20);
        $subUsers = $query->orderByDesc('id')->paginate($perPage);

        return response()->json($subUsers);
    }

    public function searchUsers(Request $request)
    {
        $query = $request->input('q');
        $users = User::query()
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('national_code', 'like', "%{$query}%")
                  ->orWhere('mobile', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get();

        return response()->json($users);
    }
}

<?php

namespace App\Http\Controllers\Mc\Panel\Profile;

use App\Http\Controllers\Mc\Controller;
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
        $subUsers = SubUser::with('subuserable')
            ->where('owner_id', $doctorId)
            ->where('owner_type', \App\Models\Doctor::class)
            ->get();
        $users = User::paginate(50); // صفحه‌بندی کاربران، هر بار ۵۰ کاربر

        return view('mc.panel.profile.subuser', compact('subUsers', 'users'));
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

        $existingSubUser = SubUser::where('owner_id', $doctorId)
            ->where('owner_type', \App\Models\Doctor::class)
            ->where('subuserable_id', $request->user_id)
            ->where('subuserable_type', User::class)
            ->first();

        if ($existingSubUser) {
            return response()->json(['error' => 'این کاربر قبلاً اضافه شده است!'], 422);
        }

        SubUser::create([
            'owner_id'        => $doctorId,
            'owner_type'      => \App\Models\Doctor::class,
            'subuserable_id'   => $request->user_id,
            'subuserable_type' => User::class,
        ]);

        return response()->json([
            'message'  => 'کاربر زیرمجموعه با موفقیت اضافه شد!',
            'subUsers' => SubUser::where('owner_id', $doctorId)->where('owner_type', \App\Models\Doctor::class)->with('subuserable')->get(),
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

        $doctorId = Auth::guard('doctor')->id();
        $existingSubUser = SubUser::where('owner_id', $doctorId)
            ->where('owner_type', \App\Models\Doctor::class)
            ->where('subuserable_id', $request->user_id)
            ->where('subuserable_type', User::class)
            ->where('id', '!=', $subUser->id)
            ->first();

        if ($existingSubUser) {
            return response()->json(['error' => 'این کاربر قبلاً به لیست اضافه شده است!'], 422);
        }

        $subUser->subuserable_id = $request->user_id;
        $subUser->subuserable_type = User::class;
        $subUser->save();

        return response()->json([
            'message'  => 'کاربر زیرمجموعه با موفقیت ویرایش شد!',
            'subUsers' => SubUser::where('owner_id', $doctorId)->where('owner_type', \App\Models\Doctor::class)->with('subuserable')->get(),
        ]);
    }

    public function destroy($id)
    {
        $subUser  = SubUser::find($id);
        if (!$subUser) {
            return response()->json([
                'error' => 'کاربر مورد نظر پیدا نشد یا قبلاً حذف شده است.'
            ], 404);
        }
        $doctorId = $subUser->owner_id;
        $subUser->delete();

        return response()->json([
            'message'  => 'کاربر زیرمجموعه حذف شد!',
            'subUsers' => SubUser::where('owner_id', $doctorId)->where('owner_type', \App\Models\Doctor::class)->with('subuserable')->get(),
        ]);
    }

    public function destroyMultiple(Request $request)
    {
        $doctorId = Auth::guard('doctor')->id();
        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['error' => 'هیچ کاربری انتخاب نشده است.'], 422);
        }
        SubUser::where('owner_id', $doctorId)->where('owner_type', \App\Models\Doctor::class)->whereIn('id', $ids)->delete();
        $subUsers = SubUser::where('owner_id', $doctorId)->where('owner_type', \App\Models\Doctor::class)->with('subuserable')->orderByDesc('id')->paginate(20);
        return response()->json([
            'message' => 'کاربران زیرمجموعه با موفقیت حذف شدند!',
            'subUsers' => $subUsers
        ]);
    }

    public function list(Request $request)
    {
        $doctorId = Auth::guard('doctor')->id();
        $query = SubUser::with('subuserable')->where('owner_id', $doctorId)->where('owner_type', \App\Models\Doctor::class);

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
        $users = User::query();
        if ($query) {
            $words = preg_split('/\s+/', trim($query));
            $users->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $q->where(function ($subQ) use ($word) {
                        $subQ->where('first_name', 'like', "%{$word}%")
                             ->orWhere('last_name', 'like', "%{$word}%");
                    });
                }
            });
            $users->orWhere('national_code', 'like', "%{$query}%")
                  ->orWhere('mobile', 'like', "%{$query}%");
        }
        $result = $users->limit(20)->get();
        return response()->json($result);
    }

    public function quickCreateUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'mobile'        => 'required|string|max:20|unique:users,mobile',
            'national_code' => 'required|string|max:20|unique:users,national_code',
        ], [
            'first_name.required'    => 'نام الزامی است.',
            'last_name.required'     => 'نام خانوادگی الزامی است.',
            'mobile.required'        => 'موبایل الزامی است.',
            'mobile.unique'          => 'این شماره موبایل قبلاً ثبت شده است.',
            'national_code.required' => 'کدملی الزامی است.',
            'national_code.unique'   => 'این کدملی قبلاً ثبت شده است.',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $user = new \App\Models\User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->mobile = $request->mobile;
        $user->national_code = $request->national_code;
        $user->save();

        return response()->json([
            'id' => $user->id,
            'full_name' => $user->first_name . ' ' . $user->last_name . ' (' . $user->national_code . ')',
        ]);
    }
}

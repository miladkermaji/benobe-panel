<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DoctorComment;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class DoctorCommentController extends Controller
{
    // لیست نظرات (با فیلتر doctor_id و status و paginate سفارشی)
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = DoctorComment::with(['doctor', 'appointment', 'userable']);
        if ($request->doctor_id) {
            $query->where('doctor_id', $request->doctor_id);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        } elseif (!$user || !($user->is_admin ?? false)) {
            // فقط نظرات فعال برای کاربران عادی
            $query->where('status', true);
        }
        $perPage = $request->input('per_page', 20);
        $comments = $query->latest()->paginate($perPage);
        return response()->json($comments);
    }

    // ثبت نظر جدید
    public function store(Request $request)
    {
        $user = Auth::user();
        $messages = [
            'doctor_id.required' => 'انتخاب پزشک الزامی است.',
            'doctor_id.exists' => 'پزشک انتخاب‌شده معتبر نیست.',
            'appointment_id.exists' => 'نوبت انتخاب‌شده معتبر نیست.',
            'comment.string' => 'متن نظر معتبر نیست.',
            'acquaintance.in' => 'طریقه آشنایی معتبر نیست.',
            'overall_score.integer' => 'امتیاز کلی باید عدد باشد.',
            'overall_score.min' => 'امتیاز کلی حداقل ۱ است.',
            'overall_score.max' => 'امتیاز کلی حداکثر ۵ است.',
            'recommend_doctor.boolean' => 'پیشنهاد به دیگران باید بله یا خیر باشد.',
            'score_behavior.integer' => 'امتیاز برخورد پزشک باید عدد باشد.',
            'score_behavior.min' => 'امتیاز برخورد پزشک حداقل ۱ است.',
            'score_behavior.max' => 'امتیاز برخورد پزشک حداکثر ۵ است.',
            'score_explanation.integer' => 'امتیاز توضیح پزشک باید عدد باشد.',
            'score_explanation.min' => 'امتیاز توضیح پزشک حداقل ۱ است.',
            'score_explanation.max' => 'امتیاز توضیح پزشک حداکثر ۵ است.',
            'score_skill.integer' => 'امتیاز مهارت پزشک باید عدد باشد.',
            'score_skill.min' => 'امتیاز مهارت پزشک حداقل ۱ است.',
            'score_skill.max' => 'امتیاز مهارت پزشک حداکثر ۵ است.',
            'score_receptionist.integer' => 'امتیاز منشی باید عدد باشد.',
            'score_receptionist.min' => 'امتیاز منشی حداقل ۱ است.',
            'score_receptionist.max' => 'امتیاز منشی حداکثر ۵ است.',
            'score_environment.integer' => 'امتیاز شرایط محیطی باید عدد باشد.',
            'score_environment.min' => 'امتیاز شرایط محیطی حداقل ۱ است.',
            'score_environment.max' => 'امتیاز شرایط محیطی حداکثر ۵ است.',
            'waiting_time.string' => 'مدت انتظار معتبر نیست.',
            'visit_reason.string' => 'علت مراجعه معتبر نیست.',
            'receptionist_comment.string' => 'نظر درباره منشی معتبر نیست.',
            'experience_comment.string' => 'تجربه کلی معتبر نیست.',
        ];
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'comment' => 'nullable|string',
            'acquaintance' => ['nullable', Rule::in(['other', 'friend', 'social', 'ads'])],
            'overall_score' => 'nullable|integer|min:1|max:5',
            'recommend_doctor' => 'nullable|boolean',
            'score_behavior' => 'nullable|integer|min:1|max:5',
            'score_explanation' => 'nullable|integer|min:1|max:5',
            'score_skill' => 'nullable|integer|min:1|max:5',
            'score_receptionist' => 'nullable|integer|min:1|max:5',
            'score_environment' => 'nullable|integer|min:1|max:5',
            'waiting_time' => 'nullable|string|max:32',
            'visit_reason' => 'nullable|string|max:255',
            'receptionist_comment' => 'nullable|string',
            'experience_comment' => 'nullable|string',
        ], $messages);

        // Map experience_comment to comment if comment is not provided
        if (empty($validated['comment']) && !empty($validated['experience_comment'])) {
            $validated['comment'] = $validated['experience_comment'];
        }

        // Ensure comment field is not empty (required by database)
        if (empty($validated['comment'])) {
            $validated['comment'] = 'نظر کاربر';
        }

        // مقداردهی userable
        if ($user) {
            $validated['userable_id'] = $user->id;
            $validated['userable_type'] = get_class($user);
        }
        $validated['status'] = 0;
        $validated['ip_address'] = $request->ip();
        $comment = DoctorComment::create($validated);
        return response()->json(['success' => true, 'data' => $comment->load(['doctor', 'appointment', 'userable'])], 201);
    }

    // نمایش یک نظر خاص
    public function show($id)
    {
        $comment = DoctorComment::with(['doctor', 'appointment', 'userable'])->findOrFail($id);
        return response()->json($comment);
    }

    // نمایش نظرات فعال یک پزشک خاص بدون نیاز به لاگین (public)
    public function publicDoctorComments(Request $request, $doctor_id)
    {
        $perPage = $request->input('per_page', 20);
        $comments = DoctorComment::with(['doctor', 'appointment', 'userable'])
            ->where('doctor_id', $doctor_id)
            ->where('status', true)
            ->latest()
            ->paginate($perPage);
        return response()->json($comments);
    }
}

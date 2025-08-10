<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactMessageController extends Controller
{
    /**
     * Store a new contact message
     */
    public function store(Request $request)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'fullName' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'countryCode' => 'required|string|max:5',
            'phone' => 'required|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ], [
            'fullName.required' => 'نام و نام خانوادگی الزامی است.',
            'fullName.string' => 'نام و نام خانوادگی باید متن باشد.',
            'fullName.max' => 'نام و نام خانوادگی نمی‌تواند بیشتر از 255 کاراکتر باشد.',
            'email.required' => 'ایمیل الزامی است.',
            'email.email' => 'فرمت ایمیل نامعتبر است.',
            'email.max' => 'ایمیل نمی‌تواند بیشتر از 255 کاراکتر باشد.',
            'countryCode.required' => 'کد کشور الزامی است.',
            'countryCode.string' => 'کد کشور باید متن باشد.',
            'countryCode.max' => 'کد کشور نمی‌تواند بیشتر از 5 کاراکتر باشد.',
            'phone.required' => 'شماره موبایل الزامی است.',
            'phone.string' => 'شماره موبایل باید متن باشد.',
            'phone.max' => 'شماره موبایل نمی‌تواند بیشتر از 20 کاراکتر باشد.',
            'subject.required' => 'موضوع الزامی است.',
            'subject.string' => 'موضوع باید متن باشد.',
            'subject.max' => 'موضوع نمی‌تواند بیشتر از 255 کاراکتر باشد.',
            'message.required' => 'پیام الزامی است.',
            'message.string' => 'پیام باید متن باشد.',
            'message.max' => 'پیام نمی‌تواند بیشتر از 2000 کاراکتر باشد.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطا در اعتبارسنجی داده‌ها',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create contact message
            $contactMessage = ContactMessage::create([
                'full_name' => $request->fullName,
                'email' => $request->email,
                'country_code' => $request->countryCode,
                'phone' => $request->phone,
                'subject' => $request->subject,
                'message' => $request->message,
                'status' => 'new',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'پیام شما با موفقیت ارسال شد. در اسرع وقت با شما تماس خواهیم گرفت.',
                'data' => [
                    'id' => $contactMessage->id,
                    'subject' => $contactMessage->subject,
                    'created_at' => $contactMessage->created_at->format('Y-m-d H:i:s'),
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطا در ارسال پیام. لطفاً دوباره تلاش کنید.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}

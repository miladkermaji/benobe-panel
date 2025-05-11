<?php

namespace App\Livewire\Dr\Panel\DoctorFaqs;

use Livewire\Component;
use Illuminate\Support\Facades\Validator;
use App\Models\DoctorFaq;
use Illuminate\Support\Facades\Auth;

class DoctorFaqsCreate extends Component
{
    // آرایه فرم برای ذخیره داده‌های ورودی
    public $form = [
        'question' => '',
        'answer' => '',
        'is_active' => true,
        'order' => 0,
    ];

    /**
     * ذخیره سوال متداول جدید
     */
    public function store()
    {
        // تعریف قوانین اعتبارسنجی
        $validator = Validator::make($this->form, [
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'is_active' => 'required|boolean',
            'order' => 'required|integer|min:0',
        ], [
            // پیام‌های فارسی برای خطاها
            'question.required' => 'فیلد سوال الزامی است.',
            'question.string' => 'سوال باید یک رشته متنی باشد.',
            'question.max' => 'سوال نمی‌تواند بیش از ۲۵۵ کاراکتر باشد.',
            'answer.required' => 'فیلد پاسخ الزامی است.',
            'answer.string' => 'پاسخ باید یک رشته متنی باشد.',
            'is_active.required' => 'وضعیت سوال الزامی است.',
            'is_active.boolean' => 'وضعیت باید فعال یا غیرفعال باشد.',
            'order.required' => 'فیلد ترتیب الزامی است.',
            'order.integer' => 'ترتیب باید یک عدد صحیح باشد.',
            'order.min' => 'ترتیب نمی‌تواند کمتر از ۰ باشد.',
        ]);

        // بررسی خطاهای اعتبارسنجی
        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        // ایجاد سوال متداول جدید با افزودن doctor_id
        DoctorFaq::create(array_merge($this->form, ['doctor_id' => Auth::guard('doctor')->user()->id]));

        // نمایش اعلان موفقیت و ریدایرکت
        $this->dispatch('show-alert', type: 'success', message: 'سوال متداول با موفقیت ایجاد شد!');
        return redirect()->route('dr.panel.doctor-faqs.index');
    }
public function mount()
{
    
if (!Auth::guard('doctor')->check()) {
    return redirect()->route('dr.auth.login-register-form');
}

}
    /**
     * رندر صفحه ایجاد
     */
    public function render()
    {

      

        return view('livewire.dr.panel.doctor-faqs.doctor-faqs-create');
    }
}

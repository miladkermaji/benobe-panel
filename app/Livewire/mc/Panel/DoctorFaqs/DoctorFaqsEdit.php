<?php

namespace App\Livewire\Mc\Panel\DoctorFaqs;

use Livewire\Component;
use Illuminate\Support\Facades\Validator;
use App\Models\DoctorFaq;
use Illuminate\Support\Facades\Auth;
use App\Traits\HasSelectedDoctor;

class DoctorFaqsEdit extends Component
{
    use HasSelectedDoctor;

    // متغیرها برای ذخیره مدل و فرم
    public $faq;
    public $form = [];

    /**
     * مقداردهی اولیه با آیدی سوال متداول
     */
    public function mount($id)
    {
        $doctor = $this->getSelectedDoctor();
        if (!$doctor) {
            $this->dispatch('show-alert', type: 'error', message: 'هیچ پزشکی انتخاب نشده است.');
            return redirect()->route('mc.panel.doctor-faqs.index');
        }

        $this->faq = DoctorFaq::where('doctor_id', $doctor->id)->findOrFail($id);
        $this->form = $this->faq->toArray();
    }

    /**
     * به‌روزرسانی سوال متداول
     */
    public function update()
    {
        $doctor = $this->getSelectedDoctor();
        if (!$doctor) {
            $this->dispatch('show-alert', type: 'error', message: 'هیچ پزشکی انتخاب نشده است.');
            return;
        }

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

        // به‌روزرسانی سوال متداول
        $this->faq->update($this->form);

        // نمایش اعلان موفقیت و ریدایرکت
        $this->dispatch('show-alert', type: 'success', message: 'سوال متداول با موفقیت به‌روزرسانی شد!');
        return redirect()->route('mc.panel.doctor-faqs.index');
    }

    /**
     * رندر صفحه ویرایش
     */
    public function render()
    {
        return view('livewire.mc.panel.doctor-faqs.doctor-faqs-edit');
    }
}

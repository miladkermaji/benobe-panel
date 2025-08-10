<?php

namespace App\Livewire\Admin\Panel\Faqs;

use App\Models\Faq;
use Livewire\Component;
use Livewire\WithFileUploads;

class FaqEdit extends Component
{
    use WithFileUploads;

    public $faq;
    public $question = '';
    public $answer = '';
    public $category = 'citizens';
    public $is_active = true;
    public $order = 0;

    protected $rules = [
        'question' => 'required|string|max:500',
        'answer' => 'required|string',
        'category' => 'required|in:citizens,doctors',
        'is_active' => 'boolean',
        'order' => 'integer|min:0',
    ];

    protected $messages = [
        'question.required' => 'سوال الزامی است.',
        'question.max' => 'سوال نمی‌تواند بیشتر از 500 کاراکتر باشد.',
        'answer.required' => 'پاسخ الزامی است.',
        'category.required' => 'دسته‌بندی الزامی است.',
        'category.in' => 'دسته‌بندی نامعتبر است.',
        'order.integer' => 'ترتیب باید عدد باشد.',
        'order.min' => 'ترتیب نمی‌تواند کمتر از 0 باشد.',
    ];

    public function mount($id)
    {
        $this->faq = Faq::findOrFail($id);
        $this->question = $this->faq->question;
        $this->answer = $this->faq->answer;
        $this->category = $this->faq->category;
        $this->is_active = $this->faq->is_active;
        $this->order = $this->faq->order;
    }

    public function save()
    {
        $this->validate();

        try {
            $this->faq->update([
                'question' => $this->question,
                'answer' => $this->answer,
                'category' => $this->category,
                'is_active' => $this->is_active,
                'order' => $this->order,
            ]);

            $this->dispatch('show-alert', type: 'success', message: 'سوال متداول با موفقیت بروزرسانی شد!');

            // ریدایرکت به لیست
            return redirect()->route('admin.panel.faqs.index');
        } catch (\Exception $e) {
            $this->dispatch('show-alert', type: 'error', message: 'خطا در بروزرسانی سوال متداول: ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        return redirect()->route('admin.panel.faqs.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.faqs.faq-edit');
    }
}

<?php

namespace App\Livewire\Admin\Questions;

use Livewire\Component;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin\Question\Question;
use App\Models\Admin\Question\QuestionCategory;

class QuestionShow extends Component
{
    public $questionId;
    public $category_id;
    public $title;
    public $question;
    public $asker_name;
    public $asker_phone;
    public $reply;
    public $replier_name;
    public $approve;
    public $persian_date;

    protected $rules = [
        'category_id' => 'required|exists:question_categories,id',
        'title' => 'required|string|max:255',
        'question' => 'required|string',
        'asker_name' => 'required|string|max:255',
        'asker_phone' => 'nullable|string|max:20',
        'reply' => 'nullable|string',
        'replier_name' => 'required|string|max:255', // این رو هم اجباری می‌کنیم چون همیشه باید پر باشه
        'approve' => 'boolean',
    ];

    public function mount($id)
    {
        $this->questionId = $id;
        $question = Question::findOrFail($id);
        $this->category_id = $question->category_id;
        $this->title = $question->title;
        $this->question = $question->question;
        $this->asker_name = $question->asker_name;
        $this->asker_phone = $question->asker_phone;
        $this->reply = $question->reply;
        // اسم کاربر لاگین‌شده رو از Auth می‌گیرم، اگه پاسخ‌دهنده قبلاً ثبت نشده باشه
        $this->replier_name = $question->replier_name ?? Auth::guard('manager')->user()->first_name . ' ' . Auth::guard('manager')->user()->last_name;
        $this->approve = $question->approve;
        $this->persian_date = Jalalian::fromDateTime($question->created_at)->format('Y/m/d H:i');
    }

    public function save()
    {
        $this->validate();

        try {
            $question = Question::findOrFail($this->questionId);
            $question->update([
                'category_id' => $this->category_id,
                'title' => $this->title,
                'question' => $this->question,
                'asker_name' => $this->asker_name,
                'asker_phone' => $this->asker_phone,
                'reply' => $this->reply,
                'replier_name' => $this->replier_name, // همیشه اسم کاربر فعلی ذخیره می‌شه
                'approve' => $this->approve,
            ]);

            $this->dispatch('toast', 'سوال و پاسخ با موفقیت ذخیره شد.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);

            return redirect()->route('admin.questions.question.index');
        } catch (\Exception $e) {
            Log::error('Error updating question:', ['message' => $e->getMessage()]);
            $this->dispatch('toast', 'خطا در ذخیره سوال: ' . $e->getMessage(), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function render()
    {
        $categories = QuestionCategory::all();
        return view('livewire.admin.questions.question-show', [
            'categories' => $categories,
        ]);
    }
}
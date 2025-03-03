<?php

namespace App\Livewire\Admin\Questions;

use Livewire\Component;
use Illuminate\Support\Facades\Log;
use App\Models\Admin\Question\QuestionCategory;

class QuestionCatCreate extends Component
{
    public $name;
    public $alt_name;

    protected $rules = [
        'name' => 'required|string|max:255',
        'alt_name' => 'nullable|string|max:255',
    ];

    public function save()
    {
        $this->validate();

        try {
            QuestionCategory::create([
                'name' => $this->name,
                'alt_name' => $this->alt_name,
            ]);

            $this->dispatch('toast', 'دسته‌بندی با موفقیت اضافه شد.', [
                'type' => 'success',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);

            return redirect()->route('admin.questions.question-cat.index');
        } catch (\Exception $e) {
            Log::error('Error creating category:', ['message' => $e->getMessage()]);
            $this->dispatch('toast', 'خطا در افزودن دسته‌بندی: ' . $e->getMessage(), [
                'type' => 'error',
                'position' => 'top-right',
                'timeOut' => 3000,
                'progressBar' => true,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.questions.question-cat-create');
    }
}
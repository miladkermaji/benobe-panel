<?php

namespace App\Livewire\Admin\Panel\Reviews;

use App\Models\Review;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithFileUploads;

class ReviewEdit extends Component
{
    use WithFileUploads;

    public $review;
    public $name;
    public $comment;
    public $image;
    public $rating;
    public $is_approved;
    public $current_image;

    public function mount($id)
    {
        $this->review = Review::findOrFail($id);
        $this->fill($this->review->toArray());
        $this->current_image = $this->review->image_url;
    }

    public function update()
    {
        $validator = Validator::make($this->all(), [
            'name'        => 'nullable|string|max:255',
            'comment'     => 'nullable|string|max:1000',
            'image'       => 'nullable|image|max:2048',
            'rating'      => 'required|integer|between:0,5',
            'is_approved' => 'boolean',
        ], [
            'name.string'     => 'نام باید متن باشد.',
            'name.max'        => 'نام نباید بیشتر از ۲۵۵ حرف باشد.',
            'comment.string'  => 'نظر باید متن باشد.',
            'comment.max'     => 'نظر نباید بیشتر از ۱۰۰۰ حرف باشد.',
            'image.image'     => 'فایل باید تصویر باشد.',
            'image.max'       => 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد.',
            'rating.required' => 'لطفاً امتیاز را وارد کنید.',
            'rating.integer'  => 'امتیاز باید عدد صحیح باشد.',
            'rating.between'  => 'امتیاز باید بین ۰ تا ۵ باشد.',
        ]);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        $data = $validator->validated();
        if ($this->image) {
            if ($this->review->image_path) {
                Storage::disk('public')->delete($this->review->image_path);
            }
            $data['image_path'] = $this->image->store('reviews', 'public');
        }

        $this->review->update($data);

        $this->dispatch('show-alert', type: 'success', message: 'نظر با موفقیت به‌روزرسانی شد!');
        return redirect()->route('admin.panel.reviews.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.reviews.review-edit');
    }
}

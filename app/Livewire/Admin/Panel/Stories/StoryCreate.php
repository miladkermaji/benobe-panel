<?php

namespace App\Livewire\Admin\Panel\Stories;

use App\Models\Story;
use App\Models\User;
use App\Models\Doctor;
use App\Models\MedicalCenter;
use App\Models\Manager;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoryCreate extends Component
{
    use WithFileUploads;

    public $title = '';
    public $description = '';
    public $type = 'image';
    public $status = 'active';
    public $is_live = false;
    public $live_start_time = '';
    public $live_end_time = '';
    public $duration = '';
    public $order = 0;
    public $media_file;
    public $thumbnail_file;

    // Owner selection
    public $owner_type = 'user';
    public $user_id = '';
    public $doctor_id = '';
    public $medical_center_id = '';
    public $manager_id = '';

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'type' => 'required|in:image,video',
        'status' => 'required|in:active,inactive,pending',
        'is_live' => 'boolean',
        'live_start_time' => 'nullable|string|required_if:is_live,true',
        'live_end_time' => 'nullable|string|required_if:is_live,true',
        'duration' => 'nullable|integer|min:1',
        'order' => 'nullable|integer|min:0',
        'media_file' => 'required|file|max:102400', // 100MB max
        'thumbnail_file' => 'nullable|image|max:5120', // 5MB max
        'owner_type' => 'required|in:user,doctor,medical_center,manager',
        'user_id' => 'nullable|exists:users,id|required_if:owner_type,user',
        'doctor_id' => 'nullable|exists:doctors,id|required_if:owner_type,doctor',
        'medical_center_id' => 'nullable|exists:medical_centers,id|required_if:owner_type,medical_center',
        'manager_id' => 'nullable|exists:managers,id|required_if:owner_type,manager',
    ];

    protected $messages = [
        'title.required' => 'عنوان استوری الزامی است.',
        'title.string' => 'عنوان استوری باید یک رشته متنی باشد.',
        'title.max' => 'عنوان استوری نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
        'description.string' => 'توضیحات باید یک رشته متنی باشد.',
        'type.required' => 'نوع استوری الزامی است.',
        'type.in' => 'نوع استوری باید یکی از گزینه‌های تصویر یا ویدیو باشد.',
        'status.required' => 'وضعیت استوری الزامی است.',
        'status.in' => 'وضعیت باید یکی از گزینه‌های فعال، غیرفعال یا در انتظار تأیید باشد.',
        'is_live.boolean' => 'وضعیت لایو باید یک مقدار بولین باشد.',
        'live_start_time.required_if' => 'زمان شروع لایو الزامی است.',
        'live_end_time.required_if' => 'زمان پایان لایو الزامی است.',
        'duration.integer' => 'مدت زمان باید یک عدد صحیح باشد.',
        'duration.min' => 'مدت زمان باید حداقل ۱ ثانیه باشد.',
        'order.integer' => 'ترتیب نمایش باید یک عدد صحیح باشد.',
        'order.min' => 'ترتیب نمایش نمی‌تواند منفی باشد.',
        'media_file.required' => 'فایل رسانه‌ای الزامی است.',
        'media_file.file' => 'فایل رسانه‌ای باید یک فایل معتبر باشد.',
        'media_file.max' => 'حجم فایل رسانه‌ای نمی‌تواند بیشتر از ۱۰۰ مگابایت باشد.',
        'thumbnail_file.image' => 'فایل بندانگشتی باید یک تصویر باشد.',
        'thumbnail_file.max' => 'حجم فایل بندانگشتی نمی‌تواند بیشتر از ۵ مگابایت باشد.',
        'owner_type.required' => 'نوع مالک الزامی است.',
        'owner_type.in' => 'نوع مالک باید یکی از گزینه‌های کاربر، پزشک، مرکز درمانی یا مدیر باشد.',
        'user_id.required_if' => 'انتخاب کاربر الزامی است.',
        'user_id.exists' => 'کاربر انتخاب‌شده معتبر نیست.',
        'doctor_id.required_if' => 'انتخاب پزشک الزامی است.',
        'doctor_id.exists' => 'پزشک انتخاب‌شده معتبر نیست.',
        'medical_center_id.required_if' => 'انتخاب مرکز درمانی الزامی است.',
        'medical_center_id.exists' => 'مرکز درمانی انتخاب‌شده معتبر نیست.',
        'manager_id.required_if' => 'انتخاب مدیر الزامی است.',
        'manager_id.exists' => 'مدیر انتخاب‌شده معتبر نیست.',
    ];

    public function mount()
    {
        $this->owner_type = 'user';
    }

    public function updatedOwnerType()
    {
        $this->user_id = '';
        $this->doctor_id = '';
        $this->medical_center_id = '';
        $this->manager_id = '';

        $this->dispatch('owner-type-changed');
    }

    public function updatedType()
    {
        $this->media_file = null;
        $this->thumbnail_file = null;
    }

    public function updatedIsLive($value)
    {
        if (!$value) {
            $this->live_start_time = '';
            $this->live_end_time = '';
        }
    }

    public function save()
    {
        $this->validate();

        try {
            $liveStartTime = $this->is_live && $this->live_start_time
                ? $this->convertJalaliToGregorian($this->live_start_time)
                : null;

            $liveEndTime = $this->is_live && $this->live_end_time
                ? $this->convertJalaliToGregorian($this->live_end_time)
                : null;

            $mediaPath = $this->media_file
                ? $this->uploadMediaFile($this->media_file, $this->type)
                : null;

            $thumbnailPath = $this->thumbnail_file
                ? $this->uploadThumbnailFile($this->thumbnail_file)
                : null;

            $storyData = [
                'title' => $this->title,
                'description' => $this->description,
                'type' => $this->type,
                'status' => $this->status,
                'is_live' => $this->is_live,
                'live_start_time' => $liveStartTime,
                'live_end_time' => $liveEndTime,
                'duration' => $this->duration ?: null,
                'order' => $this->order ?: 0,
                'media_path' => $mediaPath,
                'thumbnail_path' => $thumbnailPath,
                'metadata' => [
                    'created_by_admin' => true,
                    'created_at' => now()->toISOString(),
                ],
            ];

            switch ($this->owner_type) {
                case 'user':
                    $storyData['user_id'] = $this->user_id;
                    break;
                case 'doctor':
                    $storyData['doctor_id'] = $this->doctor_id;
                    break;
                case 'medical_center':
                    $storyData['medical_center_id'] = $this->medical_center_id;
                    break;
                case 'manager':
                    $storyData['manager_id'] = $this->manager_id;
                    break;
            }

            Story::create($storyData);

            $this->dispatch('show-alert', type: 'success', message: 'استوری با موفقیت ایجاد شد!');

            $this->resetForm();

            return redirect()->route('admin.panel.stories.index');
        } catch (\Exception $e) {
            $this->dispatch('show-alert', type: 'error', message: 'خطا در ایجاد استوری: ' . $e->getMessage());
        }
    }

    private function uploadMediaFile($file, $type)
    {
        $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = $type === 'image'
            ? $file->storeAs('stories/images', $fileName, 'public')
            : $file->storeAs('stories/videos', $fileName, 'public');

        return $path;
    }

    private function uploadThumbnailFile($file)
    {
        $fileName = 'thumb_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        return $file->storeAs('stories/thumbnails', $fileName, 'public');
    }

    private function resetForm()
    {
        $this->reset([
            'title',
            'description',
            'type',
            'status',
            'is_live',
            'live_start_time',
            'live_end_time',
            'duration',
            'order',
            'media_file',
            'thumbnail_file',
            'owner_type',
            'user_id',
            'doctor_id',
            'medical_center_id',
            'manager_id',
        ]);
        $this->owner_type = 'user';
    }

    private function convertJalaliToGregorian($jalaliDate)
    {
        if (empty($jalaliDate)) {
            return null;
        }

        try {
            $parts = explode(' ', $jalaliDate);
            $datePart = $parts[0];
            $timePart = $parts[1] ?? '00:00';

            $dateComponents = explode('/', $datePart);
            $timeComponents = explode(':', $timePart);

            if (count($dateComponents) !== 3) {
                return null;
            }

            $year = (int) $dateComponents[0];
            $month = (int) $dateComponents[1];
            $day = (int) $dateComponents[2];
            $hour = (int) ($timeComponents[0] ?? 0);
            $minute = (int) ($timeComponents[1] ?? 0);

            $jalalian = \Morilog\Jalali\Jalalian::fromFormat('Y/m/d H:i', $jalaliDate);
            return $jalalian->toCarbon();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function render()
    {
        return view('livewire.admin.panel.stories.story-create');
    }
}
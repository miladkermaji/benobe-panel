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
    public $owner_type = '';
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
        'title.max' => 'عنوان استوری نمی‌تواند بیشتر از 255 کاراکتر باشد.',
        'type.required' => 'نوع استوری الزامی است.',
        'type.in' => 'نوع استوری باید تصویر یا ویدیو باشد.',
        'status.required' => 'وضعیت استوری الزامی است.',
        'status.in' => 'وضعیت استوری نامعتبر است.',
        'live_start_time.required_if' => 'زمان شروع لایو الزامی است.',
        'live_end_time.required_if' => 'زمان پایان لایو الزامی است.',
        'live_end_time.after' => 'زمان پایان باید بعد از زمان شروع باشد.',
        'duration.min' => 'مدت زمان باید حداقل 1 ثانیه باشد.',
        'order.min' => 'ترتیب باید حداقل 0 باشد.',
        'media_file.required' => 'فایل رسانه الزامی است.',
        'media_file.max' => 'حجم فایل رسانه نمی‌تواند بیشتر از 100 مگابایت باشد.',
        'thumbnail_file.max' => 'حجم تصویر بندانگشتی نمی‌تواند بیشتر از 5 مگابایت باشد.',
        'user_id.required_if' => 'کاربر الزامی است.',
        'doctor_id.required_if' => 'پزشک الزامی است.',
        'medical_center_id.required_if' => 'مرکز درمانی الزامی است.',
        'manager_id.required_if' => 'مدیر الزامی است.',
    ];

    public function mount()
    {
        // Initialize with default values
    }

    public function updatedOwnerType()
    {
        // Reset owner IDs when owner type changes
        $this->user_id = '';
        $this->doctor_id = '';
        $this->medical_center_id = '';
        $this->manager_id = '';

        // Dispatch event to re-initialize Select2
        $this->dispatch('owner-type-changed');
    }

    public function updatedType()
    {
        // Reset files when type changes
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
            // Convert Jalali dates to Gregorian if provided
            $liveStartTime = null;
            $liveEndTime = null;

            if ($this->is_live && $this->live_start_time) {
                $liveStartTime = $this->convertJalaliToGregorian($this->live_start_time);
            }

            if ($this->is_live && $this->live_end_time) {
                $liveEndTime = $this->convertJalaliToGregorian($this->live_end_time);
            }

            // Handle media file upload
            $mediaPath = null;
            if ($this->media_file) {
                $mediaPath = $this->uploadMediaFile($this->media_file, $this->type);
            }

            // Handle thumbnail file upload
            $thumbnailPath = null;
            if ($this->thumbnail_file) {
                $thumbnailPath = $this->uploadThumbnailFile($this->thumbnail_file);
            }

            // Prepare story data
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

            // Set owner based on owner type
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

            // Create story
            Story::create($storyData);

            $this->dispatch('show-alert', type: 'success', message: 'استوری با موفقیت ایجاد شد!');

            // Reset form
            $this->resetForm();

            // Redirect to stories list
            return redirect()->route('admin.panel.stories.index');

        } catch (\Exception $e) {
            $this->dispatch('show-alert', type: 'error', message: 'خطا در ایجاد استوری: ' . $e->getMessage());
        }
    }

    private function uploadMediaFile($file, $type)
    {
        $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

        if ($type === 'image') {
            $path = $file->storeAs('stories/images', $fileName, 'public');
        } else {
            $path = $file->storeAs('stories/videos', $fileName, 'public');
        }

        return $path;
    }

    private function uploadThumbnailFile($file)
    {
        $fileName = 'thumb_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('stories/thumbnails', $fileName, 'public');

        return $path;
    }

    private function resetForm()
    {
        $this->title = '';
        $this->description = '';
        $this->type = 'image';
        $this->status = 'active';
        $this->is_live = false;
        $this->live_start_time = '';
        $this->live_end_time = '';
        $this->duration = '';
        $this->order = 0;
        $this->media_file = null;
        $this->thumbnail_file = null;
        $this->owner_type = 'user';
        $this->user_id = '';
        $this->doctor_id = '';
        $this->medical_center_id = '';
        $this->manager_id = '';
    }

    private function convertJalaliToGregorian($jalaliDate)
    {
        if (empty($jalaliDate)) {
            return null;
        }

        try {
            // Parse Jalali date string (assuming format like "1402/12/25 14:30")
            $parts = explode(' ', $jalaliDate);
            $datePart = $parts[0];
            $timePart = isset($parts[1]) ? $parts[1] : '00:00';

            $dateComponents = explode('/', $datePart);
            $timeComponents = explode(':', $timePart);

            if (count($dateComponents) !== 3) {
                return null;
            }

            $year = (int) $dateComponents[0];
            $month = (int) $dateComponents[1];
            $day = (int) $dateComponents[2];
            $hour = isset($timeComponents[0]) ? (int) $timeComponents[0] : 0;
            $minute = isset($timeComponents[1]) ? (int) $timeComponents[1] : 0;

            // Convert Jalali to Gregorian using Jalalian
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

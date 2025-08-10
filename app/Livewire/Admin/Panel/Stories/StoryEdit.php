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

class StoryEdit extends Component
{
    use WithFileUploads;

    public $storyId;
    public $story;

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

    // Current media paths
    public $current_media_path = '';
    public $current_thumbnail_path = '';

    // Selected owner data for Select2 initialization
    public $selected_owner = null;

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
        'media_file' => 'nullable|file|max:102400', // 100MB max
        'thumbnail_file' => 'nullable|image|max:5120', // 5MB max
        'owner_type' => 'required|in:user,doctor,medical_center,manager',
        'user_id' => 'nullable|exists:users,id|required_if:owner_type,user',
        'doctor_id' => 'nullable|exists:doctors,id|required_if:owner_type,doctor',
        'medical_center_id' => 'nullable|exists:medical_centers,id|required_if:owner_type,medical_center',
        'manager_id' => 'nullable|exists:managers,id|required_if:owner_type,manager',
    ];

    protected $messages = [
        'title.required' => 'عنوان استوری الزامی است.',
        'title.max' => 'عنوان استوری نباید بیشتر از 255 کاراکتر باشد.',
        'type.required' => 'نوع استوری الزامی است.',
        'type.in' => 'نوع استوری باید تصویر یا ویدیو باشد.',
        'status.required' => 'وضعیت استوری الزامی است.',
        'status.in' => 'وضعیت انتخاب شده معتبر نیست.',
        'live_start_time.required_if' => 'زمان شروع لایو برای استوری‌های لایو الزامی است.',
        'live_end_time.required_if' => 'زمان پایان لایو برای استوری‌های لایو الزامی است.',
        'duration.integer' => 'مدت زمان باید عدد صحیح باشد.',
        'duration.min' => 'مدت زمان باید حداقل 1 ثانیه باشد.',
        'order.integer' => 'ترتیب نمایش باید عدد صحیح باشد.',
        'order.min' => 'ترتیب نمایش نمی‌تواند منفی باشد.',
        'media_file.file' => 'فایل رسانه معتبر نیست.',
        'media_file.max' => 'حجم فایل رسانه نباید بیشتر از 100 مگابایت باشد.',
        'thumbnail_file.image' => 'تصویر بندانگشتی باید یک فایل تصویری باشد.',
        'thumbnail_file.max' => 'حجم تصویر بندانگشتی نباید بیشتر از 5 مگابایت باشد.',
        'owner_type.required' => 'نوع مالک الزامی است.',
        'owner_type.in' => 'نوع مالک انتخاب شده معتبر نیست.',
        'user_id.required_if' => 'انتخاب کاربر الزامی است.',
        'user_id.exists' => 'کاربر انتخاب شده معتبر نیست.',
        'doctor_id.required_if' => 'انتخاب پزشک الزامی است.',
        'doctor_id.exists' => 'پزشک انتخاب شده معتبر نیست.',
        'medical_center_id.required_if' => 'انتخاب مرکز درمانی الزامی است.',
        'medical_center_id.exists' => 'مرکز درمانی انتخاب شده معتبر نیست.',
        'manager_id.required_if' => 'انتخاب مدیر الزامی است.',
        'manager_id.exists' => 'مدیر انتخاب شده معتبر نیست.',
    ];

    public function mount($id)
    {
        $this->storyId = $id;
        $this->loadStory();
    }

    public function loadStory()
    {
        $this->story = Story::with(['user', 'doctor', 'medicalCenter', 'manager'])->findOrFail($this->storyId);

        $this->title = $this->story->title;
        $this->description = $this->story->description;
        $this->type = $this->story->type;
        $this->status = $this->story->status;
        $this->is_live = $this->story->is_live;
        $this->duration = $this->story->duration;
        $this->order = $this->story->order;
        $this->current_media_path = $this->story->media_path;
        $this->current_thumbnail_path = $this->story->thumbnail_path;

        // Convert Gregorian dates to Jalali for display
        if ($this->story->live_start_time) {
            $this->live_start_time = \Morilog\Jalali\Jalalian::fromDateTime($this->story->live_start_time)->format('Y/m/d H:i');
        }
        if ($this->story->live_end_time) {
            $this->live_end_time = \Morilog\Jalali\Jalalian::fromDateTime($this->story->live_end_time)->format('Y/m/d H:i');
        }

        // Set owner type and ID
        if ($this->story->user_id) {
            $this->owner_type = 'user';
            $this->user_id = $this->story->user_id;
            $this->selected_owner = [
                'id' => $this->story->user_id,
                'text' => $this->story->user->first_name . ' ' . $this->story->user->last_name . ' (' . $this->story->user->mobile . ')'
            ];
        } elseif ($this->story->doctor_id) {
            $this->owner_type = 'doctor';
            $this->doctor_id = $this->story->doctor_id;
            $this->selected_owner = [
                'id' => $this->story->doctor_id,
                'text' => $this->story->doctor->first_name . ' ' . $this->story->doctor->last_name . ' (' . $this->story->doctor->mobile . ')'
            ];
        } elseif ($this->story->medical_center_id) {
            $this->owner_type = 'medical_center';
            $this->medical_center_id = $this->story->medical_center_id;
            $this->selected_owner = [
                'id' => $this->story->medical_center_id,
                'text' => $this->story->medicalCenter->name . ' (' . $this->story->medicalCenter->title . ')'
            ];
        } elseif ($this->story->manager_id) {
            $this->owner_type = 'manager';
            $this->manager_id = $this->story->manager_id;
            $this->selected_owner = [
                'id' => $this->story->manager_id,
                'text' => $this->story->manager->first_name . ' ' . $this->story->manager->last_name
            ];
        }
    }

    public function updatedOwnerType()
    {
        // Reset owner IDs when owner type changes
        $this->user_id = '';
        $this->doctor_id = '';
        $this->medical_center_id = '';
        $this->manager_id = '';
        $this->selected_owner = null;
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
                'metadata' => array_merge($this->story->metadata ?? [], [
                    'updated_by_admin' => true,
                    'updated_at' => now()->toISOString(),
                ]),
            ];

            // Handle media file upload
            if ($this->media_file) {
                // Delete old media file
                if ($this->current_media_path && Storage::exists($this->current_media_path)) {
                    Storage::delete($this->current_media_path);
                }

                $storyData['media_path'] = $this->uploadMediaFile($this->media_file, $this->type);
            }

            // Handle thumbnail file upload
            if ($this->thumbnail_file) {
                // Delete old thumbnail file
                if ($this->current_thumbnail_path && Storage::exists($this->current_thumbnail_path)) {
                    Storage::delete($this->current_thumbnail_path);
                }

                $storyData['thumbnail_path'] = $this->uploadThumbnailFile($this->thumbnail_file);
            }

            // Reset owner fields
            $storyData['user_id'] = null;
            $storyData['doctor_id'] = null;
            $storyData['medical_center_id'] = null;
            $storyData['manager_id'] = null;

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

            // Update story
            $this->story->update($storyData);

            $this->dispatch('show-alert', type: 'success', message: 'استوری با موفقیت به‌روزرسانی شد!');

            // Redirect to stories list
            return redirect()->route('admin.panel.stories.index');
        } catch (\Exception $e) {
            $this->dispatch('show-alert', type: 'error', message: 'خطا در به‌روزرسانی استوری: ' . $e->getMessage());
        }
    }

    public function deleteMedia()
    {
        if ($this->current_media_path && Storage::exists($this->current_media_path)) {
            Storage::delete($this->current_media_path);
        }

        $this->story->update(['media_path' => null]);
        $this->current_media_path = '';

        $this->dispatch('show-alert', type: 'success', message: 'فایل رسانه حذف شد!');
    }

    public function deleteThumbnail()
    {
        if ($this->current_thumbnail_path && Storage::exists($this->current_thumbnail_path)) {
            Storage::delete($this->current_thumbnail_path);
        }

        $this->story->update(['thumbnail_path' => null]);
        $this->current_thumbnail_path = '';

        $this->dispatch('show-alert', type: 'success', message: 'تصویر بندانگشتی حذف شد!');
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

     private function convertJalaliToGregorian($jalaliDate)
    {
        if (empty($jalaliDate)) {
            return null;
        }

        try {
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

            $jalalian = \Morilog\Jalali\Jalalian::fromFormat('Y/m/d H:i', $jalaliDate);
            return $jalalian->toCarbon();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function render()
    {
        return view('livewire.admin.panel.stories.story-edit');
    }
}
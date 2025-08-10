<?php

namespace Database\Factories;

use App\Models\Story;
use App\Models\User;
use App\Models\Doctor;
use App\Models\MedicalCenter;
use App\Models\Manager;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Story::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $types = ['image', 'video'];
        $type = $this->faker->randomElement($types);

        $statuses = ['active', 'inactive', 'pending'];
        $status = $this->faker->randomElement($statuses);

        $isLive = $this->faker->boolean(20); // 20% احتمال زنده بودن

        $data = [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(2),
            'type' => $type,
            'media_path' => $type === 'image'
                ? 'stories/images/sample-image.jpg'
                : 'stories/videos/sample-video.mp4',
            'status' => $status,
            'is_live' => $isLive,
            'views_count' => $this->faker->numberBetween(0, 1000),
            'likes_count' => $this->faker->numberBetween(0, 500),
            'order' => $this->faker->numberBetween(0, 100),
            'metadata' => [
                'width' => $this->faker->numberBetween(320, 1920),
                'height' => $this->faker->numberBetween(240, 1080),
                'size' => $this->faker->numberBetween(100000, 50000000), // 100KB to 50MB
                'format' => $type === 'image' ? 'jpeg' : 'mp4',
            ],
        ];

        // اگر ویدیو است، thumbnail و duration اضافه کن
        if ($type === 'video') {
            $data['thumbnail_path'] = 'stories/thumbnails/sample-thumbnail.jpg';
            $data['duration'] = $this->faker->numberBetween(5, 300); // 5 تا 300 ثانیه
        }

        // اگر زنده است، زمان شروع و پایان اضافه کن
        if ($isLive) {
            $data['live_start_time'] = $this->faker->dateTimeBetween('-1 hour', '+1 hour');
            $data['live_end_time'] = $this->faker->dateTimeBetween('+1 hour', '+3 hours');
        }

        // انتخاب تصادفی صاحب استوری
        $ownerTypes = ['user', 'doctor', 'medical_center', 'manager'];
        $ownerType = $this->faker->randomElement($ownerTypes);

        switch ($ownerType) {
            case 'user':
                $data['user_id'] = User::inRandomOrder()->first()?->id;
                break;
            case 'doctor':
                $data['doctor_id'] = Doctor::inRandomOrder()->first()?->id;
                break;
            case 'medical_center':
                $data['medical_center_id'] = MedicalCenter::inRandomOrder()->first()?->id;
                break;
            case 'manager':
                $data['manager_id'] = Manager::inRandomOrder()->first()?->id;
                break;
        }

        return $data;
    }

    /**
     * استوری فعال
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'active',
            ];
        });
    }

    /**
     * استوری زنده
     */
    public function live()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_live' => true,
                'live_start_time' => now()->subMinutes(30),
                'live_end_time' => now()->addHours(2),
            ];
        });
    }

    /**
     * استوری تصویری
     */
    public function image()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'image',
                'media_path' => 'stories/images/sample-image.jpg',
                'metadata' => [
                    'width' => 640,
                    'height' => 480,
                    'size' => $this->faker->numberBetween(100000, 2000000), // 100KB to 2MB
                    'format' => 'jpeg',
                ],
            ];
        });
    }

    /**
     * استوری ویدیویی
     */
    public function video()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'video',
                'media_path' => 'stories/videos/sample-video.mp4',
                'thumbnail_path' => 'stories/thumbnails/sample-thumbnail.jpg',
                'duration' => $this->faker->numberBetween(5, 300),
                'metadata' => [
                    'width' => 1280,
                    'height' => 720,
                    'size' => $this->faker->numberBetween(5000000, 50000000), // 5MB to 50MB
                    'format' => 'mp4',
                ],
            ];
        });
    }

    /**
     * استوری با بازدید بالا
     */
    public function popular()
    {
        return $this->state(function (array $attributes) {
            return [
                'views_count' => $this->faker->numberBetween(1000, 10000),
                'likes_count' => $this->faker->numberBetween(500, 2000),
            ];
        });
    }
}

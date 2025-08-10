<?php

namespace Database\Seeders;

use App\Models\Story;
use App\Models\StoryView;
use App\Models\StoryLike;
use App\Models\User;
use App\Models\Doctor;
use App\Models\MedicalCenter;
use App\Models\Manager;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StoryAnalyticsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stories = Story::all();
        $users = User::where('status', true)->get();
        $doctors = Doctor::active()->get();
        $medicalCenters = MedicalCenter::active()->get();
        $managers = Manager::all();

        foreach ($stories as $story) {
            // Generate random views (10-100 per story)
            $viewsCount = rand(10, 100);
            $this->generateViews($story, $viewsCount, $users, $doctors, $medicalCenters, $managers);

            // Generate random likes (5-50 per story)
            $likesCount = rand(5, 50);
            $this->generateLikes($story, $likesCount, $users, $doctors, $medicalCenters, $managers);

            // Update story counts
            $story->update([
                'views_count' => $viewsCount,
                'likes_count' => $likesCount,
            ]);
        }
    }

    private function generateViews($story, $count, $users, $doctors, $medicalCenters, $managers)
    {
        $viewers = collect();
        $viewers = $viewers->merge($users->take(20));
        $viewers = $viewers->merge($doctors->take(10));
        $viewers = $viewers->merge($medicalCenters->take(5));
        $viewers = $viewers->merge($managers->take(3));

        for ($i = 0; $i < $count; $i++) {
            $viewer = $viewers->random();
            $viewedAt = Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59));

            DB::table('story_views')->insert([
                'story_id' => $story->id,
                'viewer_type' => get_class($viewer),
                'viewer_id' => $viewer->id,
                'ip_address' => '192.168.1.' . rand(1, 255),
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'session_id' => 'session_' . rand(1000, 9999),
                'viewed_at' => $viewedAt,
            ]);
        }
    }

    private function generateLikes($story, $count, $users, $doctors, $medicalCenters, $managers)
    {
        $likers = collect();
        $likers = $likers->merge($users->take(15));
        $likers = $likers->merge($doctors->take(8));
        $likers = $likers->merge($medicalCenters->take(4));
        $likers = $likers->merge($managers->take(2));

        $usedLikers = collect();

        for ($i = 0; $i < $count && $usedLikers->count() < $likers->count(); $i++) {
            $availableLikers = $likers->diff($usedLikers);

            if ($availableLikers->isEmpty()) {
                break;
            }

            $liker = $availableLikers->random();
            $usedLikers->push($liker);

            $createdAt = Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59));

            try {
                StoryLike::create([
                    'story_id' => $story->id,
                    'liker_type' => get_class($liker),
                    'liker_id' => $liker->id,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            } catch (\Exception $e) {
                // Skip if duplicate
                continue;
            }
        }
    }
}

<?php

namespace App\Livewire\Admin\Panel\Stories;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Story;
use App\Models\StoryView;
use App\Models\StoryLike;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StoryAnalytics extends Component
{
    use WithPagination;

    public $readyToLoad = false;
    public $selectedPeriod = '7'; // 7 days default
    public $selectedStory = null;
    public $stories = [];
    public $search = '';
    public $perPage = 10;

    // Analytics data
    public $totalStories = 0;
    public $totalViews = 0;
    public $totalLikes = 0;
    public $averageViewsPerStory = 0;
    public $averageLikesPerStory = 0;
    public $engagementRate = 0;

    // Charts data
    public $viewsChartData = [];
    public $likesChartData = [];
    public $storyPerformanceData = [];
    public $topStoriesData = [];
    public $viewsByTypeData = [];
    public $likesByTypeData = [];

    // Time periods
    public $periods = [
        '1' => '1 روز گذشته',
        '7' => '7 روز گذشته',
        '30' => '30 روز گذشته',
        '90' => '90 روز گذشته',
        '365' => '1 سال گذشته',
    ];

    protected $paginationTheme = 'tailwind';

    public function mount()
    {
        $this->loadStories();
    }

    public function loadAnalytics()
    {
        $this->readyToLoad = true;
        $this->calculateAnalytics();
    }

    public function loadStories()
    {
        $this->stories = Story::select('id', 'title', 'type', 'status')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function updatedSelectedPeriod()
    {
        $this->calculateAnalytics();
        $this->dispatch('analytics-updated');
    }

    public function updatedSelectedStory()
    {
        $this->calculateAnalytics();
        $this->dispatch('analytics-updated');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function calculateAnalytics()
    {
        $days = (int) $this->selectedPeriod;
        $startDate = Carbon::now()->subDays($days);

        // Base query for stories in the selected period
        $storiesQuery = Story::where('created_at', '>=', $startDate);

        if ($this->selectedStory) {
            $storiesQuery->where('id', $this->selectedStory);
        }

        $stories = $storiesQuery->get();
        $this->totalStories = $stories->count();

        // Calculate total views and likes
        $this->totalViews = $stories->sum('views_count');
        $this->totalLikes = $stories->sum('likes_count');

        // Calculate averages
        $this->averageViewsPerStory = $this->totalStories > 0 ? round($this->totalViews / $this->totalStories, 2) : 0;
        $this->averageLikesPerStory = $this->totalStories > 0 ? round($this->totalLikes / $this->totalStories, 2) : 0;

        // Calculate engagement rate (likes / views * 100)
        $this->engagementRate = $this->totalViews > 0 ? round(($this->totalLikes / $this->totalViews) * 100, 2) : 0;

        // Generate charts data
        $this->generateViewsChartData($startDate);
        $this->generateLikesChartData($startDate);
        $this->generateStoryPerformanceData($stories);
        $this->generateTopStoriesData($stories);
        $this->generateViewsByTypeData($stories);
        $this->generateLikesByTypeData($stories);
    }

    private function generateViewsChartData($startDate)
    {
        $viewsData = StoryView::where('viewed_at', '>=', $startDate)
            ->when($this->selectedStory, function ($query) {
                return $query->where('story_id', $this->selectedStory);
            })
            ->selectRaw('DATE(viewed_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $this->viewsChartData = $viewsData->map(function ($item) {
            return [
                'date' => Carbon::parse($item->date)->format('Y-m-d'),
                'count' => $item->count
            ];
        })->toArray();
    }

    private function generateLikesChartData($startDate)
    {
        $likesData = StoryLike::where('created_at', '>=', $startDate)
            ->when($this->selectedStory, function ($query) {
                return $query->where('story_id', $this->selectedStory);
            })
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $this->likesChartData = $likesData->map(function ($item) {
            return [
                'date' => Carbon::parse($item->date)->format('Y-m-d'),
                'count' => $item->count
            ];
        })->toArray();
    }

    private function generateStoryPerformanceData($stories)
    {
        $this->storyPerformanceData = $stories->map(function ($story) {
            $engagementRate = $story->views_count > 0 ? round(($story->likes_count / $story->views_count) * 100, 2) : 0;

            return [
                'id' => $story->id,
                'title' => $story->title,
                'type' => $story->type,
                'views' => $story->views_count,
                'likes' => $story->likes_count,
                'engagement_rate' => $engagementRate,
                'created_at' => $story->created_at->format('Y-m-d'),
                'status' => $story->status,
            ];
        })->sortByDesc('views')->values()->toArray();
    }

    private function generateTopStoriesData($stories)
    {
        $this->topStoriesData = $stories->sortByDesc('views_count')
            ->take(10)
            ->map(function ($story) {
                return [
                    'id' => $story->id,
                    'title' => $story->title,
                    'views' => $story->views_count,
                    'likes' => $story->likes_count,
                    'engagement_rate' => $story->views_count > 0 ? round(($story->likes_count / $story->views_count) * 100, 2) : 0,
                ];
            })->toArray();
    }

    private function generateViewsByTypeData($stories)
    {
        $viewsByType = StoryView::whereIn('story_id', $stories->pluck('id'))
            ->selectRaw('viewer_type, COUNT(*) as count')
            ->groupBy('viewer_type')
            ->get();

        $this->viewsByTypeData = $viewsByType->map(function ($item) {
            $typeName = $this->getTypeName($item->viewer_type);
            return [
                'type' => $typeName,
                'count' => $item->count,
                'percentage' => $this->totalViews > 0 ? round(($item->count / $this->totalViews) * 100, 2) : 0
            ];
        })->toArray();
    }

    private function generateLikesByTypeData($stories)
    {
        $likesByType = StoryLike::whereIn('story_id', $stories->pluck('id'))
            ->selectRaw('liker_type, COUNT(*) as count')
            ->groupBy('liker_type')
            ->get();

        $this->likesByTypeData = $likesByType->map(function ($item) {
            $typeName = $this->getTypeName($item->liker_type);
            return [
                'type' => $typeName,
                'count' => $item->count,
                'percentage' => $this->totalLikes > 0 ? round(($item->count / $this->totalLikes) * 100, 2) : 0
            ];
        })->toArray();
    }

    private function getTypeName($type)
    {
        $typeMap = [
            'App\Models\User' => 'کاربران',
            'App\Models\Doctor' => 'پزشکان',
            'App\Models\MedicalCenter' => 'مراکز درمانی',
            'App\Models\Manager' => 'مدیران',
        ];

        return $typeMap[$type] ?? 'نامشخص';
    }

    public function getPaginatedStoriesProperty()
    {
        $days = (int) $this->selectedPeriod;
        $startDate = Carbon::now()->subDays($days);

        $query = Story::where('created_at', '>=', $startDate);

        if ($this->selectedStory) {
            $query->where('id', $this->selectedStory);
        }

        if ($this->search) {
            $query->where('title', 'like', '%' . $this->search . '%');
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.admin.panel.stories.story-analytics');
    }
}

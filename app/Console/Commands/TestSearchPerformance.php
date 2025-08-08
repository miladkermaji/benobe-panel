<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\SearchController;
use Illuminate\Http\Request;

class TestSearchPerformance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:search-performance {term?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test search performance improvements';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $searchTerm = $this->argument('term') ?: 'دکتر';

        $this->info("Testing search performance for term: {$searchTerm}");
        $this->newLine();

        // Test without cache
        $this->info("Testing without cache:");
        $startTime = microtime(true);

        $request = new Request([
            'search_text' => $searchTerm,
            'province_id' => null,
            'city_id' => null
        ]);

        $controller = new SearchController();
        $result = $controller->search($request);

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $this->info("Execution time: {$executionTime} ms");
        $this->info("Results found:");
        $this->info("- Specialties: " . count($result->getData()->specialties));
        $this->info("- Doctors: " . count($result->getData()->doctors));
        $this->info("- Medical Centers: " . count($result->getData()->medical_centers));
        $this->info("- Services: " . count($result->getData()->services));
        $this->newLine();

        // Test with cache (second run)
        $this->info("Testing with cache (second run):");
        $startTime = microtime(true);

        $result2 = $controller->search($request);

        $endTime = microtime(true);
        $executionTime2 = ($endTime - $startTime) * 1000;

        $this->info("Execution time: {$executionTime2} ms");
        $this->info("Performance improvement: " . round((($executionTime - $executionTime2) / $executionTime) * 100, 2) . "%");
        $this->newLine();

        // Show database query count
        $this->info("Database queries executed:");
        $this->info("- Total queries: " . count(DB::getQueryLog()));

        $this->info("Search performance test completed!");
    }
}

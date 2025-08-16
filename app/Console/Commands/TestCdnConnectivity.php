<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ImageHelper;

class TestCdnConnectivity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cdn:test {--disk=public : The disk to test} {--url= : Specific URL to test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test CDN connectivity and image loading';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $disk = $this->option('disk');
        $specificUrl = $this->option('url');

        $this->info("Testing CDN connectivity for disk: {$disk}");
        $this->newLine();

        // Test disk configuration
        $this->testDiskConfiguration($disk);

        // Test connectivity
        $this->testConnectivity($disk);

        // Test specific URL if provided
        if ($specificUrl) {
            $this->testSpecificUrl($specificUrl);
        }

        // Test fallback images
        $this->testFallbackImages();

        $this->newLine();
        $this->info('CDN connectivity test completed!');
    }

    /**
     * Test disk configuration
     */
    private function testDiskConfiguration($disk)
    {
        $this->info('1. Testing disk configuration...');

        try {
            $config = config("filesystems.disks.{$disk}");

            if (!$config) {
                $this->error("Disk '{$disk}' not found in configuration!");
                return;
            }

            $this->line("   Driver: {$config['driver']}");
            $this->line("   Host: {$config['host']}");
            $this->line("   Port: {$config['port']}");
            $this->line("   Root: {$config['root']}");
            $this->line("   URL: {$config['url']}");

            if ($config['driver'] === 'ftp') {
                $this->line("   Username: {$config['username']}");
                $this->line("   SSL: " . ($config['ssl'] ? 'Yes' : 'No'));
                $this->line("   Passive: " . ($config['passive'] ? 'Yes' : 'No'));
            }

            $this->info('   ✓ Disk configuration is valid');
        } catch (\Exception $e) {
            $this->error("   ✗ Error reading disk configuration: " . $e->getMessage());
        }
    }

    /**
     * Test connectivity
     */
    private function testConnectivity($disk)
    {
        $this->newLine();
        $this->info('2. Testing connectivity...');

        try {
            $diskInstance = Storage::disk($disk);

            // Test if we can list files (basic connectivity test)
            $files = $diskInstance->allFiles();
            $this->line("   ✓ Successfully connected to disk");
            $this->line("   ✓ Found " . count($files) . " files");

            // Test URL generation
            if (count($files) > 0) {
                $testFile = $files[0];
                $this->line("   ✓ Found test file: " . $testFile);

                // Try to construct URL manually for FTP disks
                if ($disk === 'public' && config("filesystems.disks.{$disk}.url")) {
                    $baseUrl = config("filesystems.disks.{$disk}.url");
                    $url = rtrim($baseUrl, '/') . '/' . ltrim($testFile, '/');
                    $this->line("   ✓ Constructed URL: " . $url);

                    // Test if URL is accessible
                    $this->testUrlAccessibility($url);
                } else {
                    $this->line("   ⚠ URL generation not available for this disk type");
                }
            }
        } catch (\Exception $e) {
            $this->error("   ✗ Connection failed: " . $e->getMessage());
        }
    }

    /**
     * Test URL accessibility
     */
    private function testUrlAccessibility($url)
    {
        $this->line("   Testing URL accessibility...");

        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                ]
            ]);

            $headers = get_headers($url, 1, $context);

            if ($headers && isset($headers[0])) {
                $statusLine = $headers[0];
                if (strpos($statusLine, '200') !== false) {
                    $this->line("   ✓ URL accessible (HTTP 200)");
                } elseif (strpos($statusLine, '404') !== false) {
                    $this->warn("   ⚠ URL returns 404 (file not found)");
                } else {
                    $this->warn("   ⚠ URL returns: " . $statusLine);
                }
            } else {
                $this->warn("   ⚠ Could not determine URL status");
            }
        } catch (\Exception $e) {
            $this->warn("   ⚠ Could not test URL accessibility: " . $e->getMessage());
        }
    }

    /**
     * Test specific URL
     */
    private function testSpecificUrl($url)
    {
        $this->newLine();
        $this->info('3. Testing specific URL: ' . $url);

        $this->testUrlAccessibility($url);
    }

    /**
     * Test fallback images
     */
    private function testFallbackImages()
    {
        $this->newLine();
        $this->info('4. Testing fallback images...');

        $fallbackTypes = ['admin', 'doctor', 'medical_center', 'default'];

        foreach ($fallbackTypes as $type) {
            $fallbackUrl = ImageHelper::profilePhotoUrl(null, $type);
            $this->line("   Testing {$type} fallback: " . $fallbackUrl);

            $path = parse_url($fallbackUrl, PHP_URL_PATH);
            if ($path && file_exists(public_path($path))) {
                $this->line("   ✓ Fallback image exists");
            } else {
                $this->warn("   ⚠ Fallback image not found");
            }
        }
    }
}

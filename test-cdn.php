<?php

/**
 * CDN Connectivity Test Script
 * Run this script to test your CDN configuration
 */

// Configuration
$cdnUrl = 'https://2870351904.cloudydl.com';
$testImage = 'profile-photos/NTcNQsAVKJ6e7sk5C5cfFx3azXGA9s7Vdf8YXhx1.jpg';
$fullUrl = $cdnUrl . '/' . $testImage;

echo "🔍 CDN Connectivity Test\n";
echo "========================\n\n";

// Test 1: Basic HTTP Request
echo "1. Testing basic HTTP request...\n";
$context = stream_context_create([
    'http' => [
        'timeout' => 10,
        'user_agent' => 'CDN-Test/1.0',
        'ignore_errors' => true
    ]
]);

$headers = @get_headers($fullUrl, 1, $context);
if ($headers) {
    echo "   Status: " . $headers[0] . "\n";
    if (isset($headers['Content-Type'])) {
        echo "   Content-Type: " . $headers['Content-Type'] . "\n";
    }
    if (isset($headers['Content-Length'])) {
        echo "   Content-Length: " . $headers['Content-Length'] . "\n";
    }
} else {
    echo "   ❌ Failed to get headers\n";
}

// Test 2: CORS Preflight Test
echo "\n2. Testing CORS preflight...\n";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $fullUrl,
    CURLOPT_HEADER => true,
    CURLOPT_NOBODY => true,
    CURLOPT_CUSTOMREQUEST => 'OPTIONS',
    CURLOPT_HTTPHEADER => [
        'Origin: https://yourdomain.com',
        'Access-Control-Request-Method: GET',
        'Access-Control-Request-Headers: X-Requested-With'
    ],
    CURLOPT_TIMEOUT => 10,
    CURLOPT_RETURNTRANSFER => true
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP Code: " . $httpCode . "\n";
if (strpos($response, 'Access-Control-Allow-Origin') !== false) {
    echo "   ✅ CORS headers found\n";
} else {
    echo "   ❌ CORS headers missing\n";
}

// Test 3: File Existence Test
echo "\n3. Testing file existence...\n";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $fullUrl,
    CURLOPT_HEADER => true,
    CURLOPT_NOBODY => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_RETURNTRANSFER => true
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP Code: " . $httpCode . "\n";
if ($httpCode === 200) {
    echo "   ✅ File exists and accessible\n";
} elseif ($httpCode === 404) {
    echo "   ❌ File not found (404)\n";
} elseif ($httpCode === 403) {
    echo "   ❌ Access forbidden (403)\n";
} else {
    echo "   ⚠️  Unexpected status code\n";
}

// Test 4: CDN Domain Resolution
echo "\n4. Testing CDN domain resolution...\n";
$host = parse_url($cdnUrl, PHP_URL_HOST);
$ip = gethostbyname($host);
if ($ip && $ip !== $host) {
    echo "   ✅ Domain resolves to: " . $ip . "\n";
} else {
    echo "   ❌ Domain resolution failed\n";
}

// Test 5: SSL Certificate
echo "\n5. Testing SSL certificate...\n";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $cdnUrl,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_RETURNTRANSFER => true
]);

$response = curl_exec($ch);
$sslVerify = curl_getinfo($ch, CURLINFO_SSL_VERIFYRESULT);
curl_close($ch);

if ($sslVerify === 0) {
    echo "   ✅ SSL certificate is valid\n";
} else {
    echo "   ❌ SSL certificate issue (code: " . $sslVerify . ")\n";
}

echo "\n📋 Summary\n";
echo "==========\n";

if ($headers && strpos($headers[0], '200') !== false) {
    echo "✅ CDN is accessible and file exists\n";
} elseif ($headers && strpos($headers[0], '404') !== false) {
    echo "⚠️  CDN is accessible but file not found\n";
    echo "   - Check if file path is correct\n";
    echo "   - Verify file was uploaded to CDN\n";
} else {
    echo "❌ CDN connectivity issues detected\n";
    echo "   - Check CDN server status\n";
    echo "   - Verify domain configuration\n";
}

if (strpos($response, 'Access-Control-Allow-Origin') === false) {
    echo "❌ CORS headers missing\n";
    echo "   - Configure CORS on CDN server\n";
    echo "   - Add .htaccess or nginx configuration\n";
} else {
    echo "✅ CORS headers present\n";
}

echo "\n💡 Recommendations:\n";
echo "1. Fix CORS configuration on your CDN server\n";
echo "2. Verify file paths match between database and CDN\n";
echo "3. Check CDN file synchronization\n";
echo "4. Consider implementing the proxy endpoint\n";
echo "5. Use the enhanced fallback system in the meantime\n";

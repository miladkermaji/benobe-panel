<?php
// رمز مخفی
$secret = "Miladkermaji1996";

// گرفتن درخواست
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE'] ?? '';
$body      = file_get_contents('php://input');

// لاگ‌گذاری شروع
$log_file = '/var/www/panel/webhook_log.txt';
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Request received. Signature: $signature\n", FILE_APPEND);

// چک کردن امضا
if ($signature) {
    list($algo, $hash) = explode('=', $signature, 2);
    $computed_hash     = hash_hmac($algo, $body, $secret);

    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Computed hash: $computed_hash, Received hash: $hash\n", FILE_APPEND);

    if (hash_equals($hash, $computed_hash)) {
        // اجرای اسکریپت به صورت غیرهمزمان
        $output = shell_exec('/bin/bash /var/www/panel/update.sh > /dev/null 2>&1 &');
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Script triggered. Output: $output\n", FILE_APPEND);

        // پاسخ سریع به گیت‌هاب
        http_response_code(200);
        echo "Webhook received and update triggered!";
    } else {
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Invalid signature!\n", FILE_APPEND);
        http_response_code(403);
        echo "Invalid signature!";
    }
} else {
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - No signature provided!\n", FILE_APPEND);
    http_response_code(400);
    echo "No signature provided!";
}

<?php

// رمز مخفی
$secret = "Miladkermaji1996";

// مسیر فایل‌ها
$log_file    = '/var/www/panel/webhook_log.txt';
$script_path = '/var/www/panel/update.sh';

// گرفتن درخواست
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE'] ?? '';
$body      = file_get_contents('php://input');

// لاگ‌گذاری شروع
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Request received. Signature: $signature\n", FILE_APPEND);

// چک کردن امضا
if ($signature) {
    list($algo, $hash) = explode('=', $signature, 2);
    $computed_hash     = hash_hmac($algo, $body, $secret);

    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Computed hash: $computed_hash, Received hash: $hash\n", FILE_APPEND);

    if (hash_equals($hash, $computed_hash)) {
        // اجرای اسکریپت به‌صورت غیرهمزمان با بررسی وجود فایل
        if (file_exists($script_path)) {
            $command = "bash {$script_path} > /dev/null 2>&1 &";
            exec($command); // استفاده از exec برای اجرای غیرهمزمان
            file_put_contents($log_file, date('Y-m-d H:i:s') . " - Script triggered: $command\n", FILE_APPEND);
        } else {
            file_put_contents($log_file, date('Y-m-d H:i:s') . " - Script not found: $script_path\n", FILE_APPEND);
            http_response_code(500);
            echo "Script not found!";
            exit;
        }

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

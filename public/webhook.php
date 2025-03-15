<?php
// رمز مخفی که توی گیت‌هاب تنظیم کردی
$secret = "Miladkermaji1996";

// درخواست از گیت‌هاب رو بگیر
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE'] ?? '';
$body      = file_get_contents('php://input');

// لاگ‌گذاری شروع
file_put_contents('/var/www/panel/webhook_log.txt', date('Y-m-d H:i:s') . " - Request received. Signature: $signature\n", FILE_APPEND);

// چک کن که درخواست معتبر باشه
if ($signature) {
    list($algo, $hash) = explode('=', $signature, 2);
    $computed_hash     = hash_hmac($algo, $body, $secret);

    file_put_contents('/var/www/panel/webhook_log.txt', date('Y-m-d H:i:s') . " - Computed hash: $computed_hash, Received hash: $hash\n", FILE_APPEND);

    if (hash_equals($hash, $computed_hash)) {
        // اگه معتبر بود، اسکریپت رو اجرا کن
        $output = shell_exec('/var/www/panel/update.sh 2>&1');
        file_put_contents('/var/www/panel/webhook_log.txt', date('Y-m-d H:i:s') . " - Script executed. Output: $output\n", FILE_APPEND);
        http_response_code(200);
        echo "Success!";
    } else {
        file_put_contents('/var/www/panel/webhook_log.txt', date('Y-m-d H:i:s') . " - Invalid signature!\n", FILE_APPEND);
        http_response_code(403);
        echo "Invalid signature!";
    }
} else {
    file_put_contents('/var/www/panel/webhook_log.txt', date('Y-m-d H:i:s') . " - No signature provided!\n", FILE_APPEND);
    http_response_code(400);
    echo "No signature!";
}

<?php
// رمز مخفی که توی گیت‌هاب تنظیم کردی
$secret = "Miladkermaji1996";

// درخواست از گیت‌هاب رو بگیر
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE'] ?? '';
$body = file_get_contents('php://input');

// چک کن که درخواست معتبر باشه
if ($signature) {
    list($algo, $hash) = explode('=', $signature, 2);
    $computed_hash = hash_hmac($algo, $body, $secret);
    if (hash_equals($hash, $computed_hash)) {
        // اگه معتبر بود، اسکریپت رو اجرا کن
        shell_exec('/var/www/panel/update.sh');
        http_response_code(200);
        echo "Success!";
    } else {
        http_response_code(403);
        echo "Invalid signature!";
    }
} else {
    http_response_code(400);
    echo "No signature!";
}
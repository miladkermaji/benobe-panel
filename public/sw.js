self.addEventListener("push", function (event) {
    if (event.data) {
        const data = event.data.json();
<<<<<<< HEAD

        const options = {
            body: data.body,
            icon: "/images/logo.png", // مسیر لوگوی برنامه
            badge: "/images/badge.png", // مسیر آیکون اعلان
            data: data.data,
            vibrate: [100, 50, 100],
            requireInteraction: true,
=======
        const options = {
            body: data.body,
            icon: data.icon,
            badge: data.badge,
            tag: data.tag,
            requireInteraction: true,
            actions: data.actions,
            vibrate: [200, 100, 200],
>>>>>>> feature/recover-commits
        };

        event.waitUntil(
            self.registration.showNotification(data.title, options)
        );
    }
});

self.addEventListener("notificationclick", function (event) {
    event.notification.close();

<<<<<<< HEAD
    // اگر اعلان حاوی کد OTP است
    if (event.notification.data && event.notification.data.type === "otp") {
        const otpCode = event.notification.data.code;

        // باز کردن پنجره برنامه و ارسال کد OTP
=======
    if (event.action === "copy") {
>>>>>>> feature/recover-commits
        event.waitUntil(
            clients.matchAll({ type: "window" }).then(function (clientList) {
                if (clientList.length > 0) {
                    let client = clientList[0];
                    for (let i = 0; i < clientList.length; i++) {
                        if (clientList[i].focused) {
                            client = clientList[i];
                        }
                    }
                    return client.focus();
                }
                return clients.openWindow("/");
            })
        );
    }
});

class NotificationHandler {
    constructor() {
        this.initializeServiceWorker();
        this.setupNotificationClickHandler();
    }

    async initializeServiceWorker() {
        if ("serviceWorker" in navigator) {
            try {
                const registration = await navigator.serviceWorker.register(
                    "/sw.js"
                );
                console.log("ServiceWorker registration successful");

                // درخواست مجوز اعلان
                const permission = await Notification.requestPermission();
                if (permission === "granted") {
                    console.log("Notification permission granted");
                }
            } catch (error) {
                console.error("ServiceWorker registration failed:", error);
            }
        }
    }

    setupNotificationClickHandler() {
        // مدیریت کلیک روی اعلان
        self.addEventListener("notificationclick", (event) => {
            event.notification.close();

            // اگر اعلان حاوی کد OTP است
            if (
                event.notification.data &&
                event.notification.data.type === "otp"
            ) {
                const otpCode = event.notification.data.code;

                // پیدا کردن فیلدهای ورودی OTP
                const otpInputs = document.querySelectorAll(
                    'input[type="text"][maxlength="1"]'
                );
                if (otpInputs.length === 4) {
                    // پر کردن خودکار فیلدها
                    otpCode.split("").forEach((digit, index) => {
                        if (otpInputs[index]) {
                            otpInputs[index].value = digit;
                            // شبیه‌سازی رویداد input برای فعال کردن validation
                            otpInputs[index].dispatchEvent(
                                new Event("input", { bubbles: true })
                            );
                        }
                    });
                }
            }

            // باز کردن پنجره برنامه
            event.waitUntil(
                clients.matchAll({ type: "window" }).then((clientList) => {
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
        });
    }
}

export default NotificationHandler;

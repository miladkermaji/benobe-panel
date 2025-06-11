self.addEventListener("push", function (event) {
    if (event.data) {
        const data = event.data.json();
        const options = {
            body: data.body,
            icon: data.icon,
            badge: data.badge,
            tag: data.tag,
            requireInteraction: true,
            actions: data.actions,
            vibrate: [200, 100, 200],
        };

        event.waitUntil(
            self.registration.showNotification(data.title, options)
        );
    }
});

self.addEventListener("notificationclick", function (event) {
    event.notification.close();

    if (event.action === "copy") {
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

document.addEventListener("DOMContentLoaded", function () {
    let dropdownOpen = false;

    // باز و بسته کردن دراپ‌داون
    document.querySelectorAll(".dropdown-trigger").forEach((trigger) => {
        trigger.addEventListener("click", function (event) {
            event.stopPropagation();
            dropdownOpen = !dropdownOpen;
            this.classList.toggle("border");
            this.classList.toggle("border-primary");
            const dropdownMenu =
                this.closest(".dropdown").querySelector(".my-dropdown-menu");
            dropdownMenu.classList.toggle("d-none");
        });
    });

    // بستن دراپ‌داون با کلیک خارج
    document.addEventListener("click", function (event) {
        if (dropdownOpen) {
            
            const dropdowns = document.querySelectorAll(".dropdown");
            dropdowns.forEach((dropdown) => {
                if (!dropdown.contains(event.target)) {
                    dropdown
                        .querySelector(".dropdown-trigger")
                        .classList.remove("border", "border-primary");
                    dropdown
                        .querySelector(".my-dropdown-menu")
                        .classList.add("d-none");
                    dropdownOpen = false;
                }
            });
        }
    });

    // جلوگیری از بسته شدن دراپ‌داون با کلیک داخل آن
    document.querySelectorAll(".my-dropdown-menu").forEach((menu) => {
        menu.addEventListener("click", function (event) {
            /* location.reload(); */

            event.stopPropagation();
        });
    });

    // بررسی کلینیک‌های غیرفعال برای نمایش هشدار
    function checkInactiveClinics() {
        const hasInactiveClinics =
            document.querySelectorAll('.option-card[data-active="0"]').length >
            0;
        document.querySelectorAll(".dropdown-trigger").forEach((trigger) => {
            trigger.classList.toggle("warning", hasInactiveClinics);
        });
    }
    checkInactiveClinics();

    // گوش دادن به رویداد تغییر کلینیک از Livewire
    window.addEventListener("clinicSelected", function (event) {
        const clinicId = event.detail.clinicId;
        const optionCards = document.querySelectorAll(".option-card");
        optionCards.forEach((card) => {
            card.classList.toggle(
                "card-active",
                card.dataset.id == (clinicId || "default")
            );
        });
        checkInactiveClinics();
    });
});

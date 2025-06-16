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
        // Handle case where event.detail might be undefined
        const detail = event.detail || {};
        let clinicId = detail.clinicId;
        console.log('Clinic selected in clinic-selector:', clinicId);
        
        // If clinicId is undefined, use 'default'
        const selectedId = (clinicId === undefined || clinicId === null) ? 'default' : clinicId.toString();
        
        const optionCards = document.querySelectorAll(".option-card");
        let foundSelected = false;
        
        optionCards.forEach((card) => {
            const cardId = card.dataset.id || '';
            const isSelected = cardId === selectedId;
            
            card.classList.toggle("card-active", isSelected);
            
            // Update the selected clinic name in the dropdown trigger
            if (isSelected) {
                foundSelected = true;
                const clinicName = card.querySelector('.fw-bold.fs-15')?.textContent || 
                                 'مشاوره آنلاین به نوبه';
                const dropdownLabel = document.querySelector('.dropdown-label');
                if (dropdownLabel) {
                    dropdownLabel.textContent = clinicName;
                }
            }
        });
        
        // If no card was selected, try to select the default one
        if (!foundSelected) {
            const defaultCard = document.querySelector('.option-card[data-id="default"]');
            if (defaultCard) {
                defaultCard.classList.add('card-active');
                const dropdownLabel = document.querySelector('.dropdown-label');
                if (dropdownLabel) {
                    dropdownLabel.textContent = 'مشاوره آنلاین به نوبه';
                }
            }
        }
        
        checkInactiveClinics();
        
        // Close the dropdown after selection
        const dropdownMenu = document.querySelector('.my-dropdown-menu');
        const dropdownTrigger = document.querySelector('.dropdown-trigger');
        if (dropdownMenu && dropdownTrigger) {
            dropdownMenu.classList.add('d-none');
            dropdownTrigger.classList.remove('border', 'border-primary');
            dropdownOpen = false;
        }
    });
});

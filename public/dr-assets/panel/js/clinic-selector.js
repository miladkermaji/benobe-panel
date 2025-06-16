document.addEventListener("DOMContentLoaded", function () {
    let dropdownOpen = false;
    let lastProcessedClinicId = null;

    // Initialize clinic selection
    function initializeClinicSelection() {
        // Set initial clinic ID if not set
        if (!window.currentClinicId) {
            const defaultOption = document.querySelector(
                '.clinic-option[data-active="1"]'
            );
            if (defaultOption) {
                window.currentClinicId =
                    defaultOption.getAttribute("data-id") || "default";
            } else {
                window.currentClinicId = "default";
            }
        }
    }

    // Handle clinic option clicks
    function handleClinicOptionClick(event) {
        const option = event.currentTarget;
        const rawClinicId = option.getAttribute("data-id");
        const clinicId =
            rawClinicId === null || rawClinicId === "undefined"
                ? "default"
                : String(rawClinicId);

     

        // Only proceed if this is a different clinic
        if (clinicId === window.currentClinicId) {
            return;
        }

        // Update current clinic ID
        window.currentClinicId = clinicId;

        // Update UI
        updateClinicUI(option, clinicId);

        // Dispatch clinic selected event
        dispatchClinicSelectedEvent(clinicId);
    }

    // Update UI when clinic is selected
    function updateClinicUI(selectedOption, clinicId) {
        // Update button text
        const buttonText =
            selectedOption.querySelector(".clinic-name")?.textContent?.trim() ||
            "انتخاب کلینیک";
        const button = document.querySelector(".my-dropdown-toggle");
        if (button) {
            button.innerHTML = `
                <i class="fas fa-clinic-medical me-2"></i>
                ${buttonText}
                <i class="fas fa-chevron-down ms-2"></i>
            `;
        }

        // Update active states
        document.querySelectorAll(".clinic-option").forEach((card) => {
            const cardClinicId = card.getAttribute("data-id") || "default";
            card.classList.toggle(
                "active",
                String(cardClinicId) === String(clinicId)
            );
        });
    }

    // Dispatch clinic selected event
    function dispatchClinicSelectedEvent(clinicId) {
        const event = new CustomEvent("clinicSelected", {
            detail: { clinicId },
            bubbles: true,
            cancelable: true,
        });
    
        document.dispatchEvent(event);
    }

    // Handle clinic selection from external sources
    function handleExternalClinicSelection(event) {
        // Only process the event if it's not from our own re-dispatch
        if (event.detail && event.detail.isRedispatched) {
            return;
        }

        const detail = event.detail || {};
        let clinicId = detail.clinicId;


        // Normalize the clinicId
        clinicId =
            clinicId === undefined || clinicId === null || clinicId === ""
                ? "default"
                : String(clinicId);
        window.location.href =
            window.location.pathname + "";
        // Skip if this is the same clinic ID we just processed
        if (clinicId === lastProcessedClinicId) {
            return;
        }

        lastProcessedClinicId = clinicId;

        // Find the matching card
        const matchingCard = Array.from(
            document.querySelectorAll(".clinic-option")
        ).find((card) => {
            const cardClinicId = card.getAttribute("data-id") || "default";
            return String(cardClinicId) === String(clinicId);
        });

        if (matchingCard) {
            updateClinicUI(matchingCard, clinicId);
        } else if (clinicId !== "default") {
          
            const newEvent = new CustomEvent("clinicSelected", {
                detail: { clinicId: "default", isRedispatched: true },
                bubbles: true,
                cancelable: true,
            });
            document.dispatchEvent(newEvent);
        }
    }

    // Toggle dropdown
    function toggleDropdown(event) {
        event.stopPropagation();

        const dropdownMenu = this.nextElementSibling;
        const isOpen = !dropdownMenu.classList.contains("d-none");

        // Close all other dropdowns
        document
            .querySelectorAll(".my-dropdown-menu")
            .forEach((menu) => menu.classList.add("d-none"));
        document
            .querySelectorAll(".dropdown-trigger")
            .forEach((t) => t.classList.remove("border", "border-primary"));

        // Toggle this dropdown
        if (!isOpen) {
            dropdownMenu.classList.remove("d-none");
            this.classList.add("border", "border-primary");
        }

        dropdownOpen = !isOpen;
    }

    // Close dropdowns when clicking outside
    function handleOutsideClick(event) {
        if (dropdownOpen) {
            const dropdowns = document.querySelectorAll(".dropdown");
            let clickedInside = false;

            dropdowns.forEach((dropdown) => {
                if (dropdown.contains(event.target)) {
                    clickedInside = true;
                }
            });

            if (!clickedInside) {
                document
                    .querySelectorAll(".my-dropdown-menu")
                    .forEach((menu) => menu.classList.add("d-none"));
                document
                    .querySelectorAll(".dropdown-trigger")
                    .forEach((t) =>
                        t.classList.remove("border", "border-primary")
                    );
                dropdownOpen = false;
            }
        }
    }

    // Check for inactive clinics
    function checkInactiveClinics() {
        const hasInactiveClinics =
            document.querySelectorAll('.clinic-option[data-active="0"]')
                .length > 0;
        document.querySelectorAll(".dropdown-trigger").forEach((trigger) => {
            trigger.classList.toggle("warning", hasInactiveClinics);
        });
    }

    // Initialize everything
    function init() {
        initializeClinicSelection();
        checkInactiveClinics();

        // Add event listeners
        document.querySelectorAll(".clinic-option").forEach((option) => {
            option.addEventListener("click", handleClinicOptionClick);
        });

        document.querySelectorAll(".my-dropdown-menu").forEach((menu) => {
            menu.addEventListener("click", (event) => event.stopPropagation());
        });

        document.querySelectorAll(".dropdown-trigger").forEach((trigger) => {
            trigger.addEventListener("click", toggleDropdown);
        });

        document.addEventListener("click", handleOutsideClick);
        document.addEventListener(
            "clinicSelected",
            handleExternalClinicSelection
        );
    }

    // Start the initialization
    init();
});

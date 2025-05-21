// Add Livewire event listener for appointment counts
document.addEventListener("livewire:initialized", () => {
    // Show loading immediately
    showCalendarLoading();

    Livewire.on("appointments-count-updated", (data) => {
        requestAnimationFrame(() => {
            updateCalendarAppointments(data);
            hideCalendarLoading();
        });
    });
});

// Function to show loading state
function showCalendarLoading() {
    const loadingOverlay = document.getElementById("loading-overlay");
    if (loadingOverlay) {
        loadingOverlay.classList.remove("hidden");
    }
}

// Function to hide loading state
function hideCalendarLoading() {
    const loadingOverlay = document.getElementById("loading-overlay");
    if (loadingOverlay) {
        loadingOverlay.classList.add("hidden");
    }
}

// Function to update calendar with appointment counts
function updateCalendarAppointments(data) {
    const calendar = document.querySelector(".calendar");
    if (!calendar) return;

    // Create a document fragment for better performance
    const fragment = document.createDocumentFragment();
    const indicators = new Map();

    // Clear existing appointment indicators
    const existingIndicators = calendar.querySelectorAll(
        ".appointment-indicator"
    );
    existingIndicators.forEach((indicator) => indicator.remove());

    // Prepare all indicators
    data.forEach((item) => {
        const date = item.date;
        const count = item.count;
        const dateCell = calendar.querySelector(`[data-date="${date}"]`);

        if (dateCell) {
            const indicator = document.createElement("div");
            indicator.className = "appointment-indicator";
            indicator.textContent = count;
            indicators.set(dateCell, indicator);
        }
    });

    // Batch DOM updates
    requestAnimationFrame(() => {
        indicators.forEach((indicator, cell) => {
            cell.appendChild(indicator);
        });
    });
}

// Function to get appointment counts for a date
async function getAppointmentCount(date) {
    try {
        const count = await Livewire.dispatch("getAppointmentsCountWithCache", [
            date,
        ]);
        return count;
    } catch (error) {
        console.error("Error getting appointment count:", error);
        return 0;
    }
}

// Function to handle month change
async function handleMonthChange(year, month) {
    try {
        showCalendarLoading();
        const appointments = await Livewire.dispatch(
            "getAppointmentsCountForMonth",
            [year, month]
        );
        requestAnimationFrame(() => {
            updateCalendarAppointments(appointments);
            hideCalendarLoading();
        });
    } catch (error) {
        console.error("Error getting appointments for month:", error);
        hideCalendarLoading();
    }
}

// Add event listener for month change with debounce
let monthChangeTimeout;
document.addEventListener("DOMContentLoaded", () => {
    const monthSelect = document.querySelector(".month-select");
    if (monthSelect) {
        monthSelect.addEventListener("change", (e) => {
            clearTimeout(monthChangeTimeout);
            monthChangeTimeout = setTimeout(() => {
                const [year, month] = e.target.value.split("-");
                handleMonthChange(year, month);
            }, 100);
        });
    }

    // Show loading on initial load
    showCalendarLoading();
});

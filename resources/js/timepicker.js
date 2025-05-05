class TimePicker {
    constructor() {
        this.activePicker = null;
        this.init();
    }

    init() {
        document.querySelectorAll("input[data-timepicker]").forEach((input) => {
            input.addEventListener("click", (e) => this.showPicker(e.target));
        });
        document.addEventListener("click", (e) => this.handleOutsideClick(e));
    }

    showPicker(input) {
        if (this.activePicker) {
            this.activePicker.remove();
        }

        const picker = document.createElement("div");
        picker.classList.add("timepicker");
        picker.innerHTML = this.getPickerHTML();

        const rect = input.getBoundingClientRect();
        picker.style.top = `${rect.bottom + window.scrollY + 5}px`;
        picker.style.left = `${rect.left + window.scrollX}px`;

        const value = input.value.split(":");
        const hours = value[0] || "00";
        const minutes = value[1] || "00";

        document.body.appendChild(picker);
        this.activePicker = picker;

        picker.querySelector(".timepicker-hours").value = hours;
        picker.querySelector(".timepicker-minutes").value = minutes;

        this.bindArrowButtons(picker);

        picker
            .querySelector(".timepicker-confirm")
            .addEventListener("click", () => {
                const hours = picker
                    .querySelector(".timepicker-hours")
                    .value.padStart(2, "0");
                const minutes = picker
                    .querySelector(".timepicker-minutes")
                    .value.padStart(2, "0");
                input.value = `${hours}:${minutes}`;
                input.dispatchEvent(new Event("input"));
                this.closePicker();
            });

        picker
            .querySelector(".timepicker-cancel")
            .addEventListener("click", () => {
                this.closePicker();
            });
    }

    getPickerHTML() {
        return `
            <div class="timepicker-content">
                <div class="timepicker-time">
                    <div class="timepicker-section">
                        <button type="button" class="timepicker-arrow timepicker-up" data-type="hours">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2">
                                <path d="M12 19V5M5 12l7-7 7 7"/>
                            </svg>
                        </button>
                        <input type="text" class="timepicker-hours" value="00" readonly>
                        <button type="button" class="timepicker-arrow timepicker-down" data-type="hours">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2">
                                <path d="M12 5v14M19 12l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>
                    <span class="timepicker-divider">:</span>
                    <div class="timepicker-section">
                        <button type="button" class="timepicker-arrow timepicker-up" data-type="minutes">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2">
                                <path d="M12 19V5M5 12l7-7 7 7"/>
                            </svg>
                        </button>
                        <input type="text" class="timepicker-minutes" value="00" readonly>
                        <button type="button" class="timepicker-arrow timepicker-down" data-type="minutes">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2">
                                <path d="M12 5v14M19 12l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="timepicker-actions">
                    <button type="button" class="timepicker-confirm">تأیید</button>
                    <button type="button" class="timepicker-cancel">لغو</button>
                </div>
            </div>
        `;
    }

    bindArrowButtons(picker) {
        picker.querySelectorAll(".timepicker-arrow").forEach((button) => {
            button.addEventListener("click", () => {
                const type = button.dataset.type;
                const input = picker.querySelector(`.timepicker-${type}`);
                let value = parseInt(input.value) || 0;

                if (button.classList.contains("timepicker-up")) {
                    value =
                        type === "hours" ? (value + 1) % 24 : (value + 1) % 60;
                } else {
                    value =
                        type === "hours"
                            ? (value - 1 + 24) % 24
                            : (value - 1 + 60) % 60;
                }

                input.value = value.toString().padStart(2, "0");
            });
        });
    }

    handleOutsideClick(e) {
        if (
            this.activePicker &&
            !this.activePicker.contains(e.target) &&
            !e.target.hasAttribute("data-timepicker")
        ) {
            this.closePicker();
        }
    }

    closePicker() {
        if (this.activePicker) {
            this.activePicker.remove();
            this.activePicker = null;
        }
    }
}

document.addEventListener("DOMContentLoaded", () => new TimePicker());

class TimePicker {
    constructor() {
        this.activePicker = null;
        this.backdrop = null;
        this.container = null; // اضافه کردن container ثابت
        this.debounceBindInputs = this.debounce(
            this.bindInputs.bind(this),
            100
        ); // Debounce برای بهینه‌سازی
        this.init();
    }

    // تابع Debounce برای کاهش اجرای مکرر
    debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    init() {
        // ایجاد container ثابت
        this.container = document.createElement("div");
        document.body.appendChild(this.container);

        this.bindInputs();

        // مدیریت کلیک خارج
        document.addEventListener("click", (e) => this.handleOutsideClick(e));

        // گوش دادن به آپدیت‌های Livewire
        document.addEventListener("livewire:initialized", () => {
            Livewire.on("refresh-timepicker", () => this.debounceBindInputs());
            Livewire.on("component.updated", () => this.debounceBindInputs());
            Livewire.on("open-modal", ({ id }) => {
                if (id === "holiday-modal" || id === "scheduleModal") {
                    this.debounceBindInputs();
                }
            });
        });

        // مشاهده تغییرات DOM با بهینه‌سازی
        this.observer = new MutationObserver((mutations) => {
            if (
                mutations.some(
                    (m) => m.addedNodes.length || m.removedNodes.length
                )
            ) {
                this.debounceBindInputs();
            }
        });
        this.observer.observe(document.body, {
            childList: true,
            subtree: true,
        });
    }

    bindInputs() {
        // استفاده از event delegation برای بهینه‌سازی
        document.body.removeEventListener("click", this.handleInputClick, true);
        document.body.addEventListener(
            "click",
            this.handleInputClick.bind(this),
            true
        );

        // اطمینان از حذف listenerهای قدیمی
        document.querySelectorAll("input[data-timepicker]").forEach((input) => {
            input.removeEventListener("click", input._timePickerListener);
            input._timePickerListener = null;
        });
    }

    handleInputClick(e) {
        const input = e.target.closest("input[data-timepicker]");
        if (input) {
            e.stopPropagation();
            this.showPicker(input);
        }
    }

    showPicker(input) {
        if (
            !(input instanceof HTMLElement) ||
            !input.hasAttribute("data-timepicker")
        ) {
            console.warn("Invalid input element for TimePicker:", input);
            return;
        }

        if (this.activePicker) {
            this.closePicker();
        }

        // ایجاد بک‌دراپ
        this.backdrop = document.createElement("div");
        this.backdrop.classList.add("timepicker-backdrop");
        this.container.appendChild(this.backdrop);
        setTimeout(() => (this.backdrop.style.opacity = "1"), 0); // برای transition

        // ایجاد تایم‌پیکر
        const picker = document.createElement("div");
        picker.classList.add("timepicker");
        picker.innerHTML = this.getPickerHTML();
        this.container.appendChild(picker);
        this.activePicker = picker;

        // تنظیم موقعیت مرکزی
        picker.style.position = "fixed";
        picker.style.top = "50%";
        picker.style.left = "50%";
        picker.style.transform = "translate(-50%, -50%)";
        picker.style.zIndex = "10000";

        // خواندن مقدار اولیه یا تنظیم پیش‌فرض
        const value = input.value ? input.value.split(":") : ["07", "00"];
        const hours = value[0] || "07";
        const minutes = value[1] || "00";
        const hoursInput = picker.querySelector(".timepicker-hours");
        const minutesInput = picker.querySelector(".timepicker-minutes");
        hoursInput.value = hours;
        minutesInput.value = minutes;

        this.bindArrowButtons(picker);
        this.bindInputEvents(picker, input);

        // مدیریت دکمه تأیید
        picker
            .querySelector(".timepicker-confirm")
            .addEventListener("click", () => {
                const hours = hoursInput.value.padStart(2, "0");
                const minutes = minutesInput.value.padStart(2, "0");
                input.value = `${hours}:${minutes}`;
                input.dispatchEvent(new Event("input"));
                input.dispatchEvent(new Event("change"));
                this.closePicker();
            });

        // مدیریت دکمه لغو
        picker
            .querySelector(".timepicker-cancel")
            .addEventListener("click", () => this.closePicker());

        // فعال‌سازی انیمیشن
        setTimeout(
            () =>
                picker
                    .querySelector(".timepicker-content")
                    .classList.add("show"),
            0
        );
    }

    getPickerHTML() {
        return `
            <div class="timepicker-content">
                <div class="timepicker-time">
                    <div class="timepicker-section">
                        <button type="button" class="timepicker-arrow timepicker-up" data-type="hours">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 19V5M5 12l7-7 7 7"/>
                            </svg>
                        </button>
                        <input type="text" class="timepicker-hours" value="07">
                        <button type="button" class="timepicker-arrow timepicker-down" data-type="hours">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 5v14M19 12l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>
                    <span class="timepicker-divider">:</span>
                    <div class="timepicker-section">
                        <button type="button" class="timepicker-arrow timepicker-up" data-type="minutes">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 19V5M5 12l7-7 7 7"/>
                            </svg>
                        </button>
                        <input type="text" class="timepicker-minutes" value="00">
                        <button type="button" class="timepicker-arrow timepicker-down" data-type="minutes">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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

    bindInputEvents(picker, inputElement) {
        const hoursInput = picker.querySelector(".timepicker-hours");
        const minutesInput = picker.querySelector(".timepicker-minutes");
        [hoursInput, minutesInput].forEach((input) => {
            input.addEventListener("focus", () => {
                input.value = "";
            });

            input.addEventListener("input", (e) => {
                let value = e.target.value.replace(/[^0-9]/g, "");
                if (value.length > 2) {
                    value = value.slice(-2);
                }
                e.target.value = value;
            });

            input.addEventListener(
                "blur",
                () => {
                    let num = parseInt(input.value) || 0;
                    if (input.classList.contains("timepicker-hours")) {
                        num = Math.min(Math.max(num, 0), 23);
                    } else {
                        num = Math.min(Math.max(num, 0), 59);
                    }
                    input.value = num.toString().padStart(2, "0");
                },
                { once: true }
            );

            input.addEventListener("keydown", (e) => {
                if (e.key === "ArrowUp" || e.key === "ArrowDown") {
                    e.preventDefault();
                    let value = parseInt(input.value) || 0;
                    if (e.key === "ArrowUp") {
                        value = input.classList.contains("timepicker-hours")
                            ? (value + 1) % 24
                            : (value + 1) % 60;
                    } else {
                        value = input.classList.contains("timepicker-hours")
                            ? (value - 1 + 24) % 24
                            : (value - 1 + 60) % 60;
                    }
                    input.value = value.toString().padStart(2, "0");
                } else if (e.key === "Enter") {
                    const hours = hoursInput.value.padStart(2, "0");
                    const minutes = minutesInput.value.padStart(2, "0");
                    inputElement.value = `${hours}:${minutes}`;
                    inputElement.dispatchEvent(new Event("input"));
                    inputElement.dispatchEvent(new Event("change"));
                    this.closePicker();
                }
            });
        });
    }

    handleOutsideClick(e) {
        if (
            this.activePicker &&
            !this.activePicker.contains(e.target) &&
            !e.target.closest("input[data-timepicker]") &&
            !e.target.closest(".x-modal__content")
        ) {
            this.closePicker();
        }
    }

    closePicker() {
        if (this.activePicker) {
            this.activePicker.remove();
            this.activePicker = null;
        }
        if (this.backdrop) {
            this.backdrop.remove();
            this.backdrop = null;
        }
        // تمیز کردن container
        this.container.innerHTML = "";
    }

    destroy() {
        // تمیز کردن کامل هنگام تخریب
        this.observer.disconnect();
        document.removeEventListener("click", this.handleOutsideClick);
        document.body.removeEventListener("click", this.handleInputClick, true);
        this.closePicker();
        this.container.remove();
        window.timePickerInstance = null;
    }
}

document.addEventListener("DOMContentLoaded", () => {
    if (!window.timePickerInstance) {
        window.timePickerInstance = new TimePicker();
    }
});

class TimePicker {
    constructor() {
        this.activePicker = null;
        this.backdrop = null;
        this.showPickerBound = this.showPicker.bind(this);
        this.observer = null;
        this.init();
    }

    init() {
        this.bindInputs();

        // مدیریت کلیک خارج
        document.addEventListener("click", (e) => this.handleOutsideClick(e), {
            capture: true,
        });

        // گوش دادن به آپدیت‌های Livewire
        document.addEventListener("livewire:initialized", () => {
            Livewire.on("refresh-timepicker", () => this.bindInputs());
            Livewire.on("component.updated", () => this.bindInputs());
            Livewire.on("open-modal", ({ id }) => {
                if (id === "holiday-modal" || id === "scheduleModal") {
                    setTimeout(() => this.bindInputs(), 100);
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
                this.bindInputs();
            }
        });
        this.observer.observe(document.body, {
            childList: true,
            subtree: true,
        });
    }

    bindInputs() {
        // حذف listenerهای قبلی
        document.querySelectorAll("input[data-timepicker]").forEach((input) => {
            if (input._timePickerListener) {
                input.removeEventListener("click", input._timePickerListener);
            }
        });

        // اضافه کردن listenerهای جدید
        document.querySelectorAll("input[data-timepicker]").forEach((input) => {
            const listener = (e) => {
                e.stopPropagation();
                this.showPickerBound(input);
            };
            input._timePickerListener = listener;
            input.addEventListener("click", listener, { capture: true });
        });
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
        document.body.appendChild(this.backdrop);

        // ایجاد تایم‌پیکر
        const picker = document.createElement("div");
        picker.classList.add("timepicker");
        picker.innerHTML = this.getPickerHTML();
        document.body.appendChild(picker);
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
            // پاک کردن مقدار قبلی هنگام فوکوس
            input.addEventListener("focus", () => {
                input.value = "";
            });

            // مدیریت تایپ
            input.addEventListener("input", (e) => {
                let value = e.target.value.replace(/[^0-9]/g, "");
                if (value.length > 2) {
                    value = value.slice(-2);
                }
                e.target.value = value;

                // اعتبارسنجی هنگام خروج از فوکوس
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
            });

            // مدیریت کلیدهای فلش بالا و پایین
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
            !e.target.hasAttribute("data-timepicker") &&
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
    }
}

document.addEventListener("DOMContentLoaded", () => {
    // اطمینان از اجرای تنها یک نمونه
    if (!window.timePickerInstance) {
        window.timePickerInstance = new TimePicker();
    }
});

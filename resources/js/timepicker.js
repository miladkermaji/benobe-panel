class TimePicker {
    constructor() {
        this.activePicker = null;
        this.backdrop = null;
        this.showPickerBound = this.showPicker.bind(this);
        this.init();
    }

    init() {
        this.bindInputs();

        // مدیریت کلیک خارج
        document.addEventListener("click", (e) => this.handleOutsideClick(e));

        // گوش دادن به آپدیت‌های Livewire
        document.addEventListener("livewire:initialized", () => {
            Livewire.on("refresh-timepicker", () => this.bindInputs());
            Livewire.on("component.updated", () => this.bindInputs());
            Livewire.on("open-modal", ({ id }) => {
                if (id === "holiday-modal" || id === "scheduleModal") {
                    setTimeout(() => this.bindInputs(), 100); // تأخیر برای اطمینان از رندر DOM
                }
            });
        });

        // مشاهده تغییرات DOM برای ورودی‌های دینامیک
        const observer = new MutationObserver(() => {
            this.bindInputs();
        });
        observer.observe(document.body, {
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
                e.stopPropagation(); // جلوگیری از تداخل با رویدادهای دیگر
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
        picker.style.zIndex = "10000"; // افزایش z-index

        // خواندن مقدار اولیه
        const value = input.value ? input.value.split(":") : ["00", "00"];
        const hours = value[0] || "00";
        const minutes = value[1] || "00";

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
                input.dispatchEvent(new Event("change"));
                this.closePicker();
            });

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
                        <input type="text" class="timepicker-hours" value="00" readonly>
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
                        <input type="text" class="timepicker-minutes" value="00" readonly>
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

document.addEventListener("DOMContentLoaded", () => new TimePicker());

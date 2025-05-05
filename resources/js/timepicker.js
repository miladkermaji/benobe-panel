class TimePicker {
    constructor() {
        this.activePicker = null;
        this.showPickerBound = null;
        this.init();
    }

    init() {
        // مقداردهی اولیه با تأخیر
        setTimeout(() => {
            this.bindInputs();
        }, 200);

        // مدیریت کلیک خارج
        document.addEventListener("click", (e) => this.handleOutsideClick(e));

        // گوش دادن به آپدیت‌های Livewire
        document.addEventListener("livewire:initialized", () => {
            Livewire.on("refresh-timepicker", () => {
                setTimeout(() => {
                    this.bindInputs();
                }, 200);
            });
            Livewire.on("component.updated", () => {
                setTimeout(() => {
                    this.bindInputs();
                }, 200);
            });
        });

        // مشاهده تغییرات DOM
        const observer = new MutationObserver((mutations) => {
            if (mutations.some((mutation) => mutation.addedNodes.length)) {
                this.bindInputs();
            }
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
        this.showPickerBound = this.showPicker.bind(this);
        document.querySelectorAll("input[data-timepicker]").forEach((input) => {
            const listener = (e) => {
                if (input instanceof HTMLElement) {
                    this.showPickerBound(input);
                }
            };
            input._timePickerListener = listener;
            input.addEventListener("click", listener, { capture: true });
        });
    }

    showPicker(input) {
        // اطمینان از معتبر بودن input
        if (
            !(input instanceof HTMLElement) ||
            !input.hasAttribute("data-timepicker")
        ) {
            console.warn("Invalid input element for TimePicker:", input);
            return;
        }

        if (this.activePicker) {
            this.activePicker.remove();
        }

        const picker = document.createElement("div");
        picker.classList.add("timepicker");
        picker.innerHTML = this.getPickerHTML();

        // محاسبه موقعیت تایم‌پیکر
        try {
            const rect = input.getBoundingClientRect();
            const pickerWidth = 320; // عرض تقریبی تایم‌پیکر (بر اساس CSS)
            const pickerHeight = 240; // ارتفاع تقریبی تایم‌پیکر
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;

            // تنظیم موقعیت افقی
            let left = rect.left + window.scrollX;
            if (left + pickerWidth > viewportWidth - 10) {
                left = viewportWidth - pickerWidth - 10; // 10px حاشیه
            }
            if (left < 10) {
                left = 10;
            }

            // تنظیم موقعیت عمودی (اولویت زیر اینپوت)
            let top = rect.bottom + window.scrollY + 5;
            if (top + pickerHeight > viewportHeight - 10) {
                // اگه زیر اینپوت جا نشد، بالای اینپوت
                top = rect.top + window.scrollY - pickerHeight - 5;
            }
            if (top < 10) {
                top = 10;
            }

            picker.style.position = "fixed";
            picker.style.top = `${top}px`;
            picker.style.left = `${left}px`;
        } catch (e) {
            console.error("Error positioning timepicker:", e);
            return;
        }

        // خواندن مقدار اولیه
        const value = input.value ? input.value.split(":") : ["00", "00"];
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
                input.dispatchEvent(new Event("change"));
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
    }
}

document.addEventListener("DOMContentLoaded", () => new TimePicker());

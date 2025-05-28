// تبدیل اعداد فارسی به انگلیسی
const persianNumbers = ["۰", "۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹"];
const englishNumbers = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9"];

// تبدیل اعداد فارسی به انگلیسی
export function convertPersianToEnglish(str) {
    if (!str) return str;
    return str
        .toString()
        .split("")
        .map((char) => {
            const index = persianNumbers.indexOf(char);
            return index !== -1 ? englishNumbers[index] : char;
        })
        .join("");
}

// تبدیل اعداد انگلیسی به فارسی
export function convertEnglishToPersian(str) {
    if (!str) return str;
    return str
        .toString()
        .split("")
        .map((char) => {
            const index = englishNumbers.indexOf(char);
            return index !== -1 ? persianNumbers[index] : char;
        })
        .join("");
}

// اعمال تبدیل اعداد به تمام input های عددی
document.addEventListener("DOMContentLoaded", function () {
    // انتخاب تمام input های عددی
    const numberInputs = document.querySelectorAll(
        'input[type="number"], input[type="tel"], input[type="text"]'
    );

    numberInputs.forEach((input) => {
        // تبدیل اعداد فارسی به انگلیسی هنگام ورود
        input.addEventListener("input", function (e) {
            const value = e.target.value;
            const englishValue = convertPersianToEnglish(value);
            if (value !== englishValue) {
                e.target.value = englishValue;
            }
        });

        // تبدیل اعداد انگلیسی به فارسی هنگام نمایش
        input.addEventListener("blur", function (e) {
            const value = e.target.value;
            const persianValue = convertEnglishToPersian(value);
            if (value !== persianValue) {
                e.target.value = persianValue;
            }
        });
    });
});

// اضافه کردن توابع به window برای دسترسی در سایر فایل‌ها
window.convertPersianToEnglish = convertPersianToEnglish;
window.convertEnglishToPersian = convertEnglishToPersian;

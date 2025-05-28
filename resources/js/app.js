import Sortable from "sortablejs";
window.Sortable = Sortable;
import "@popperjs/core";
import "../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js";
import "./persian-number-converter";

// مدیریت تغییر تم
document.addEventListener("DOMContentLoaded", function () {
    console.log("DOM Content Loaded");

    const themeToggle = document.getElementById("theme-toggle");
    const themeToggleLightIcon = document.getElementById(
        "theme-toggle-light-icon"
    );
    const themeToggleDarkIcon = document.getElementById(
        "theme-toggle-dark-icon"
    );

    console.log("Theme elements:", {
        themeToggle,
        themeToggleLightIcon,
        themeToggleDarkIcon,
    });

    if (!themeToggle || !themeToggleLightIcon || !themeToggleDarkIcon) {
        console.error("Theme toggle elements not found");
        return;
    }

    // تنظیم تم اولیه
    let isDarkMode =
        localStorage.getItem("color-theme") === "dark" ||
        (!("color-theme" in localStorage) &&
            window.matchMedia("(prefers-color-scheme: dark)").matches);

    console.log("Initial theme:", isDarkMode ? "dark" : "light");

    function setTheme(isDark) {
        const html = document.documentElement;

        if (isDark) {
            html.classList.add("dark");
            themeToggleDarkIcon.classList.remove("hidden");
            themeToggleLightIcon.classList.add("hidden");
            localStorage.setItem("color-theme", "dark");
            isDarkMode = true;

            // اعمال استایل‌های دارک مود
            document.body.style.backgroundColor = "#0f172a"; // رنگ پس‌زمینه اصلی
            document.body.style.color = "#e2e8f0"; // رنگ متن اصلی

            // اعمال استایل به تمام المان‌ها
            document.querySelectorAll("*").forEach((el) => {
                // تغییر رنگ پس‌زمینه المان‌های مختلف
                if (el.classList.contains("bg-white")) {
                    el.style.backgroundColor = "#1e293b";
                }
                if (
                    el.classList.contains("bg-gray-50") ||
                    el.classList.contains("bg-gray-100")
                ) {
                    el.style.backgroundColor = "#1e293b";
                }
                if (el.classList.contains("bg-gray-200")) {
                    el.style.backgroundColor = "#334155";
                }
                if (
                    el.classList.contains("bg-blue-50") ||
                    el.classList.contains("bg-blue-100")
                ) {
                    el.style.backgroundColor = "#1e3a8a";
                }

                // تغییر رنگ متن
                if (
                    el.classList.contains("text-gray-600") ||
                    el.classList.contains("text-gray-700")
                ) {
                    el.style.color = "#cbd5e1";
                }
                if (
                    el.classList.contains("text-gray-800") ||
                    el.classList.contains("text-gray-900")
                ) {
                    el.style.color = "#f8fafc";
                }

                // تغییر رنگ بوردر
                if (el.classList.contains("border-gray-200")) {
                    el.style.borderColor = "#334155";
                }
            });

            // اعمال استایل به سایدبار
            const sidebar = document.querySelector(".sidebar");
            if (sidebar) {
                sidebar.style.backgroundColor = "#1e293b";
                sidebar.style.color = "#e2e8f0";
                sidebar.style.borderColor = "#334155";
            }

            // اعمال استایل به هدر
            const header = document.querySelector("header");
            if (header) {
                header.style.backgroundColor = "#1e293b";
                header.style.borderColor = "#334155";
            }

            // اعمال استایل به کارت‌ها
            document.querySelectorAll(".card").forEach((el) => {
                el.style.backgroundColor = "#1e293b";
                el.style.borderColor = "#334155";
            });

            // اعمال استایل به جداول
            document.querySelectorAll("table").forEach((el) => {
                el.style.backgroundColor = "#1e293b";
                el.style.borderColor = "#334155";
            });

            // اعمال استایل به آیکون‌ها
            document.querySelectorAll("svg").forEach((el) => {
                el.style.color = "#e2e8f0";
            });

            // اعمال استایل به دکمه‌ها
            document.querySelectorAll("button").forEach((el) => {
                if (!el.classList.contains("btn-primary")) {
                    el.style.backgroundColor = "#334155";
                    el.style.borderColor = "#475569";
                    el.style.color = "#e2e8f0";
                }
            });

            // اعمال استایل به input‌ها
            document
                .querySelectorAll("input, select, textarea")
                .forEach((el) => {
                    el.style.backgroundColor = "#1e293b";
                    el.style.borderColor = "#334155";
                    el.style.color = "#e2e8f0";
                });
        } else {
            html.classList.remove("dark");
            themeToggleLightIcon.classList.remove("hidden");
            themeToggleDarkIcon.classList.add("hidden");
            localStorage.setItem("color-theme", "light");
            isDarkMode = false;

            // بازگرداندن استایل‌های لایت مود
            document.body.style.backgroundColor = "";
            document.body.style.color = "";

            // بازگرداندن استایل تمام المان‌ها
            document.querySelectorAll("*").forEach((el) => {
                el.style.backgroundColor = "";
                el.style.color = "";
                el.style.borderColor = "";
            });
        }
    }

    // اعمال تم اولیه
    setTheme(isDarkMode);

    // تغییر تم با کلیک روی دکمه
    themeToggle.addEventListener("click", function (e) {
        e.preventDefault();
        console.log("Theme toggle clicked");
        console.log("Current isDarkMode:", isDarkMode);

        // تغییر تم
        setTheme(!isDarkMode);

        console.log("New isDarkMode:", !isDarkMode);
    });
});

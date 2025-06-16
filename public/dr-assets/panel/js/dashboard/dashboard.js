document.addEventListener("DOMContentLoaded", function () {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has("showModal")) {
        $("#activation-modal").modal("show");
    }

    // متغیر جهانی برای clinic_id، مقدار اولیه از Livewire
    let selectedClinicId = window.selectedClinicId || "default";

    // گوش دادن به رویداد تغییر کلینیک از Livewire
    window.addEventListener("clinicSelected", function (event) {
        selectedClinicId = event.detail.clinicId || "default";
        loadCharts(); // به‌روزرسانی نمودارها پس از تغییر کلینیک
    });

    // تابع بارگذاری نمودارها
    function loadCharts() {
        $("#chart-container").html(
            '<div class="loader">در حال بارگذاری...</div>'
        );
        $.ajax({
            url: chartUrl,
            method: "GET",
            data: {
                clinic_id:
                    selectedClinicId === "default" ? null : selectedClinicId,
                _t: new Date().getTime(),
            },
            success: function (response) {
                $("#chart-container .loader").remove();
                // تبدیل هفته‌ها به فارسی
                const persianWeeks =
                    response.appointments?.map((item) => {
                        const [year, week] = item.month.split("-");
                        const date = moment()
                            .year(year)
                            .week(week)
                            .startOf("week");
                        return date.locale("fa").format("jYYYY/jMM/jDD");
                    }) || [];
                // داده‌های پیش‌فرض
                const defaultData = [
                    {
                        month: persianWeeks[0] || "هفته قبل",
                        scheduled: 0,
                        attended: 0,
                        missed: 0,
                        cancelled: 0,
                        paid: 0,
                        unpaid: 0,
                        count: 0,
                        total: 0,
                    },
                ];
                // تبدیل داده‌ها به فرمت مناسب
                const appointments =
                    response.appointments?.map((item) => ({
                        month: moment()
                            .year(item.month.split("-")[0])
                            .week(item.month.split("-")[1])
                            .startOf("week")
                            .locale("fa")
                            .format("jYYYY/jMM/jDD"),
                        scheduled: item.scheduled || 0,
                        attended: item.attended || 0,
                        missed: item.missed || 0,
                        cancelled: item.cancelled || 0,
                    })) || defaultData;
                const monthlyIncome =
                    response.monthlyIncome?.map((item) => ({
                        month: moment(item.month + "-01")
                            .locale("fa")
                            .format("jYYYY/jMM"),
                        paid: item.paid || 0,
                        unpaid: item.unpaid || 0,
                    })) || defaultData;
                const newPatients =
                    response.newPatients?.map((item) => ({
                        month: moment()
                            .year(item.month.split("-")[0])
                            .week(item.month.split("-")[1])
                            .startOf("week")
                            .locale("fa")
                            .format("jYYYY/jMM/jDD"),
                        count: item.count || 0,
                    })) || defaultData;
                const appointmentTypes =
                    response.appointmentTypes?.map((item) => ({
                        month: moment()
                            .year(item.month.split("-")[0])
                            .week(item.month.split("-")[1])
                            .startOf("week")
                            .locale("fa")
                            .format("jYYYY/jMM/jDD"),
                        in_person: item.in_person || 0,
                        online: item.online || 0,
                        phone: item.phone || 0,
                        video: item.video || 0,
                        text: item.text || 0,
                    })) || defaultData;
                const counselingAppointments =
                    response.counselingAppointments?.map((item) => ({
                        month: moment()
                            .year(item.month.split("-")[0])
                            .week(item.month.split("-")[1])
                            .startOf("week")
                            .locale("fa")
                            .format("jYYYY/jMM/jDD"),
                        scheduled: item.scheduled || 0,
                        attended: item.attended || 0,
                        missed: item.missed || 0,
                        cancelled: item.cancelled || 0,
                    })) || defaultData;
                const manualAppointments =
                    response.manualAppointments?.map((item) => ({
                        month: moment()
                            .year(item.month.split("-")[0])
                            .week(item.month.split("-")[1])
                            .startOf("week")
                            .locale("fa")
                            .format("jYYYY/jMM/jDD"),
                        scheduled: item.scheduled || 0,
                        confirmed: item.confirmed || 0,
                    })) || defaultData;
                const totalIncome =
                    response.totalIncome?.map((item) => ({
                        month: moment(item.month + "-01")
                            .locale("fa")
                            .format("jYYYY/jMM"),
                        total: item.total || 0,
                    })) || defaultData;
                // رندر نمودارها
                renderPerformanceChart(appointments);
                renderIncomeChart(monthlyIncome);
                renderPatientChart(newPatients);
                renderAppointmentTypesChart(appointmentTypes);
                renderStatusPieChart(appointments);
                renderPatientTrendChart(newPatients);
                renderCounselingChart(counselingAppointments);
                renderManualChart(manualAppointments);
                renderTotalIncomeChart(totalIncome);
                $("#chart-container").hide().show();
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", status, error);
                $("#chart-container .loader").remove();
                $("#chart-container").html("<p>خطا در دریافت اطلاعات</p>");
                toastr.error("خطا در دریافت اطلاعات نمودارها");
            },
        });
    }

    // 📊 نمودار تعداد ویزیت‌ها - نمودار میله‌ای برای مقایسه بهتر
    function renderPerformanceChart(data) {
        let ctx = document
            .getElementById("doctor-performance-chart")
            .getContext("2d");
        if (window.performanceChart) {
            window.performanceChart.destroy();
        }
        if (!data || data.length === 0) {
            ctx.canvas.parentNode.innerHTML =
                "<p>داده‌ای برای نمایش وجود ندارد</p>";
            return;
        }
        let labels = data.map((item) => item.month);
        window.performanceChart = new Chart(ctx, {
            type: "bar",
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "ویزیت شده",
                        data: data.map((item) => item.scheduled || 0),
                        backgroundColor: "#2e86c1",
                        borderColor: "#2e86c1",
                        borderWidth: 1,
                    },
                    {
                        label: "انجام‌شده",
                        data: data.map((item) => item.attended || 0),
                        backgroundColor: "#34d399",
                        borderColor: "#34d399",
                        borderWidth: 1,
                    },
                    {
                        label: "غیبت",
                        data: data.map((item) => item.missed || 0),
                        backgroundColor: "#f87171",
                        borderColor: "#f87171",
                        borderWidth: 1,
                    },
                    {
                        label: "لغو‌شده",
                        data: data.map((item) => item.cancelled || 0),
                        backgroundColor: "#fbbf24",
                        borderColor: "#fbbf24",
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                ...commonOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: "rgba(0, 0, 0, 0.05)",
                        },
                        ticks: {
                            font: {
                                size: 10,
                            },
                        },
                    },
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            font: {
                                size: 10,
                            },
                            maxRotation: 0,
                            minRotation: 0,
                        },
                    },
                },
            },
        });
    }

    // 💰 نمودار درآمد ماهانه - نمودار خطی برای نمایش روند
    function renderIncomeChart(data) {
        let ctx = document
            .getElementById("doctor-income-chart")
            .getContext("2d");
        if (window.incomeChart) {
            window.incomeChart.destroy();
        }
        if (!data || data.length === 0) {
            ctx.canvas.parentNode.innerHTML =
                "<p>داده‌ای برای نمایش وجود ندارد</p>";
            return;
        }
        let labels = data.map((item) => item.month);
        window.incomeChart = new Chart(ctx, {
            type: "line",
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "پرداخت‌شده",
                        data: data.map((item) => item.paid || 0),
                        borderColor: "#10b981",
                        backgroundColor: "rgba(16, 185, 129, 0.2)",
                        fill: true,
                        tension: 0.4,
                    },
                    {
                        label: "پرداخت‌نشده",
                        data: data.map((item) => item.unpaid || 0),
                        borderColor: "#ef4444",
                        backgroundColor: "rgba(239, 68, 68, 0.2)",
                        fill: true,
                        tension: 0.4,
                    },
                ],
            },
            options: {
                ...commonOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: "rgba(0, 0, 0, 0.05)",
                        },
                        ticks: {
                            font: {
                                size: 10,
                            },
                            callback: function (value) {
                                return value.toLocaleString() + " تومان";
                            },
                        },
                    },
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            font: {
                                size: 10,
                            },
                            maxRotation: 0,
                            minRotation: 0,
                        },
                    },
                },
            },
        });
    }

    // 👨‍⚕️ نمودار تعداد بیماران جدید - نمودار خطی برای نمایش روند
    function renderPatientChart(data) {
        let ctx = document
            .getElementById("doctor-patient-chart")
            .getContext("2d");
        if (window.patientChart) {
            window.patientChart.destroy();
        }
        if (!data || data.length === 0) {
            ctx.canvas.parentNode.innerHTML =
                "<p>داده‌ای برای نمایش وجود ندارد</p>";
            return;
        }
        let labels = data.map((item) => item.month);
        window.patientChart = new Chart(ctx, {
            type: "line",
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "بیماران جدید",
                        data: data.map((item) => item.count || 0),
                        borderColor: "#f59e0b",
                        backgroundColor: "rgba(245, 158, 11, 0.2)",
                        fill: true,
                        tension: 0.4,
                    },
                ],
            },
            options: {
                ...commonOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: "rgba(0, 0, 0, 0.05)",
                        },
                        ticks: {
                            font: {
                                size: 10,
                            },
                        },
                    },
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            font: {
                                size: 10,
                            },
                            maxRotation: 0,
                            minRotation: 0,
                        },
                    },
                },
            },
        });
    }

    // 📈 نمودار انواع نوبت‌ها - نمودار میله‌ای گروهی
    function renderAppointmentTypesChart(data) {
        let ctx = document
            .getElementById("doctor-status-chart")
            .getContext("2d");
        if (window.statusChart) {
            window.statusChart.destroy();
        }
        if (!data || data.length === 0) {
            ctx.canvas.parentNode.innerHTML =
                "<p>داده‌ای برای نمایش وجود ندارد</p>";
            return;
        }
        let labels = data.map((item) => item.month);
        window.statusChart = new Chart(ctx, {
            type: "bar",
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "حضوری",
                        data: data.map((item) => item.in_person || 0),
                        backgroundColor: "#2e86c1",
                        borderColor: "#2e86c1",
                        borderWidth: 1,
                    },
                    {
                        label: "آنلاین",
                        data: data.map((item) => item.online || 0),
                        backgroundColor: "#34d399",
                        borderColor: "#34d399",
                        borderWidth: 1,
                    },
                    {
                        label: "تلفنی",
                        data: data.map((item) => item.phone || 0),
                        backgroundColor: "#f87171",
                        borderColor: "#f87171",
                        borderWidth: 1,
                    },
                    {
                        label: "ویدیویی",
                        data: data.map((item) => item.video || 0),
                        backgroundColor: "#8b5cf6",
                        borderColor: "#8b5cf6",
                        borderWidth: 1,
                    },
                    {
                        label: "متنی",
                        data: data.map((item) => item.text || 0),
                        backgroundColor: "#fbbf24",
                        borderColor: "#fbbf24",
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                ...commonOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: "rgba(0, 0, 0, 0.05)",
                        },
                        ticks: {
                            font: {
                                size: 10,
                            },
                        },
                    },
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            font: {
                                size: 10,
                            },
                            maxRotation: 0,
                            minRotation: 0,
                        },
                    },
                },
            },
        });
    }

    // 🥧 نمودار درصد نوبت‌ها - نمودار دایره‌ای
    function renderStatusPieChart(data) {
        let ctx = document
            .getElementById("doctor-status-pie-chart")
            .getContext("2d");
        if (window.statusPieChart) {
            window.statusPieChart.destroy();
        }
        if (!data || data.length === 0) {
            ctx.canvas.parentNode.innerHTML =
                "<p>داده‌ای برای نمایش وجود ندارد</p>";
            return;
        }
        const lastMonth = data[data.length - 1];
        const total =
            (lastMonth.scheduled || 0) +
            (lastMonth.attended || 0) +
            (lastMonth.missed || 0) +
            (lastMonth.cancelled || 0);
        window.statusPieChart = new Chart(ctx, {
            type: "pie",
            data: {
                labels: ["ویزیت شده", "انجام‌شده", "غیبت", "لغو‌شده"],
                datasets: [
                    {
                        data: [
                            lastMonth.scheduled || 0,
                            lastMonth.attended || 0,
                            lastMonth.missed || 0,
                            lastMonth.cancelled || 0,
                        ],
                        backgroundColor: [
                            "#2e86c1",
                            "#34d399",
                            "#f87171",
                            "#fbbf24",
                        ],
                        borderColor: "#ffffff",
                        borderWidth: 2,
                    },
                ],
            },
            options: {
                ...commonOptions,
                plugins: {
                    ...commonOptions.plugins,
                    tooltip: {
                        ...commonOptions.plugins.tooltip,
                        callbacks: {
                            label: function (context) {
                                const value = context.raw;
                                const percentage =
                                    total > 0
                                        ? ((value / total) * 100).toFixed(1)
                                        : 0;
                                return `${context.label}: ${value} (${percentage}%)`;
                            },
                        },
                    },
                },
            },
        });
    }

    // 📉 نمودار روند بیماران - نمودار خطی با ناحیه
    function renderPatientTrendChart(data) {
        let ctx = document
            .getElementById("doctor-patient-trend-chart")
            .getContext("2d");
        if (window.patientTrendChart) {
            window.patientTrendChart.destroy();
        }
        if (!data || data.length === 0) {
            ctx.canvas.parentNode.innerHTML =
                "<p>داده‌ای برای نمایش وجود ندارد</p>";
            return;
        }
        let labels = data.map((item) => item.month);
        window.patientTrendChart = new Chart(ctx, {
            type: "line",
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "بیماران جدید",
                        data: data.map((item) => item.count || 0),
                        borderColor: "#f97316",
                        backgroundColor: "rgba(249, 115, 22, 0.2)",
                        fill: true,
                        tension: 0.4,
                    },
                ],
            },
            options: {
                ...commonOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: "rgba(0, 0, 0, 0.05)",
                        },
                        ticks: {
                            font: {
                                size: 10,
                            },
                        },
                    },
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            font: {
                                size: 10,
                            },
                            maxRotation: 0,
                            minRotation: 0,
                        },
                    },
                },
            },
        });
    }

    // 🗣️ نمودار نوبت‌های مشاوره - نمودار میله‌ای گروهی
    function renderCounselingChart(data) {
        let ctx = document
            .getElementById("doctor-counseling-chart")
            .getContext("2d");
        if (window.counselingChart) {
            window.counselingChart.destroy();
        }
        if (!data || data.length === 0) {
            ctx.canvas.parentNode.innerHTML =
                "<p>داده‌ای برای نمایش وجود ندارد</p>";
            return;
        }
        let labels = data.map((item) => item.month);
        window.counselingChart = new Chart(ctx, {
            type: "bar",
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "ویزیت شده",
                        data: data.map((item) => item.scheduled || 0),
                        backgroundColor: "#8b5cf6",
                        borderColor: "#8b5cf6",
                        borderWidth: 1,
                    },
                    {
                        label: "انجام‌شده",
                        data: data.map((item) => item.attended || 0),
                        backgroundColor: "#3b82f6",
                        borderColor: "#3b82f6",
                        borderWidth: 1,
                    },
                    {
                        label: "غیبت",
                        data: data.map((item) => item.missed || 0),
                        backgroundColor: "#ec4899",
                        borderColor: "#ec4899",
                        borderWidth: 1,
                    },
                    {
                        label: "لغو‌شده",
                        data: data.map((item) => item.cancelled || 0),
                        backgroundColor: "#facc15",
                        borderColor: "#facc15",
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                ...commonOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: "rgba(0, 0, 0, 0.05)",
                        },
                        ticks: {
                            font: {
                                size: 10,
                            },
                        },
                    },
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            font: {
                                size: 10,
                            },
                            maxRotation: 0,
                            minRotation: 0,
                        },
                    },
                },
            },
        });
    }

    // ✍️ نمودار نوبت‌های دستی - نمودار میله‌ای گروهی
    function renderManualChart(data) {
        let ctx = document
            .getElementById("doctor-manual-chart")
            .getContext("2d");
        if (window.manualChart) {
            window.manualChart.destroy();
        }
        if (!data || data.length === 0) {
            ctx.canvas.parentNode.innerHTML =
                "<p>داده‌ای برای نمایش وجود ندارد</p>";
            return;
        }
        let labels = data.map((item) => item.month);
        window.manualChart = new Chart(ctx, {
            type: "bar",
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "ویزیت شده",
                        data: data.map((item) => item.scheduled || 0),
                        backgroundColor: "#14b8a6",
                        borderColor: "#14b8a6",
                        borderWidth: 1,
                    },
                    {
                        label: "تأیید‌شده",
                        data: data.map((item) => item.confirmed || 0),
                        backgroundColor: "#6366f1",
                        borderColor: "#6366f1",
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                ...commonOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: "rgba(0, 0, 0, 0.05)",
                        },
                        ticks: {
                            font: {
                                size: 10,
                            },
                        },
                    },
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            font: {
                                size: 10,
                            },
                            maxRotation: 0,
                            minRotation: 0,
                        },
                    },
                },
            },
        });
    }

    // 💸 نمودار درآمد کلی - نمودار خطی با ناحیه
    function renderTotalIncomeChart(data) {
        let ctx = document
            .getElementById("doctor-total-income-chart")
            .getContext("2d");
        if (window.totalIncomeChart) {
            window.totalIncomeChart.destroy();
        }
        if (!data || data.length === 0) {
            ctx.canvas.parentNode.innerHTML =
                "<p>داده‌ای برای نمایش وجود ندارد</p>";
            return;
        }
        let labels = data.map((item) => item.month);
        window.totalIncomeChart = new Chart(ctx, {
            type: "line",
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "درآمد کلی",
                        data: data.map((item) => item.total || 0),
                        borderColor: "#d946ef",
                        backgroundColor: "rgba(217, 70, 239, 0.2)",
                        fill: true,
                        tension: 0.4,
                    },
                ],
            },
            options: {
                ...commonOptions,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: "rgba(0, 0, 0, 0.05)",
                        },
                        ticks: {
                            font: {
                                size: 10,
                            },
                            callback: function (value) {
                                return value.toLocaleString() + " تومان";
                            },
                        },
                    },
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            font: {
                                size: 10,
                            },
                            maxRotation: 0,
                            minRotation: 0,
                        },
                    },
                },
            },
        });
    }

    // تنظیمات مشترک برای نمودارها
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: "bottom",
                labels: {
                    font: {
                        family: "IRANSans",
                        size: 11,
                        weight: "500",
                    },
                    padding: 8,
                    color: "#1e293b",
                    boxWidth: 10,
                    usePointStyle: true,
                },
            },
            tooltip: {
                enabled: true,
                backgroundColor: "rgba(30, 41, 59, 0.9)",
                titleFont: {
                    family: "IRANSans",
                    size: 12,
                },
                bodyFont: {
                    family: "IRANSans",
                    size: 11,
                },
                padding: 8,
                cornerRadius: 6,
                borderColor: "rgba(255, 255, 255, 0.2)",
                borderWidth: 1,
            },
        },
        animation: {
            duration: 800,
            easing: "easeOutQuart",
        },
        hover: {
            mode: "nearest",
            intersect: true,
            animationDuration: 200,
        },
        layout: {
            padding: {
                top: 10,
                right: 10,
                bottom: 10,
                left: 10,
            },
        },
    };

    // بارگذاری اولیه نمودارها
    loadCharts();
});

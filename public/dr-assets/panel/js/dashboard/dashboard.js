function initializeDashboard() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has("showModal")) {
        $("#activation-modal").modal("show");
    }

    // متغیر جهانی برای medical_center_id، مقدار اولیه از Livewire
    let selectedClinicId = window.selectedClinicId || "default";
    // Ensure the value is always a string
    if (selectedClinicId === null || selectedClinicId === undefined) {
        selectedClinicId = "default";
    } else {
        selectedClinicId = String(selectedClinicId);
    }
    console.log("Initial selectedClinicId:", selectedClinicId);

    // تابع تشخیص دارک مود
    function isDarkMode() {
        return (
            document.documentElement.classList.contains("dark") ||
            document.body.classList.contains("dark-mode") ||
            localStorage.getItem("darkMode") === "true"
        );
    }

    // تابع به‌روزرسانی رنگ‌های نمودار بر اساس دارک مود
    function getChartColors() {
        if (isDarkMode()) {
            return {
                grid: "rgba(255, 255, 255, 0.15)",
                text: "#f9fafb",
                textSecondary: "#d1d5db",
                border: "#4b5563",
                background: "rgba(55, 65, 81, 0.1)",
            };
        } else {
            return {
                grid: "rgba(0, 0, 0, 0.2)",
                text: "#111827",
                textSecondary: "#374151",
                border: "#9ca3af",
                background: "rgba(0, 0, 0, 0.1)",
            };
        }
    }

    // تابع به‌روزرسانی تنظیمات مشترک نمودارها
    function getCommonOptions() {
        const colors = getChartColors();
        return {
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
                        color: colors.text,
                        boxWidth: 10,
                        usePointStyle: true,
                    },
                },
                tooltip: {
                    enabled: true,
                    backgroundColor: isDarkMode()
                        ? "rgba(0, 0, 0, 0.9)"
                        : "rgba(30, 41, 59, 0.95)",
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
                    borderColor: colors.border,
                    borderWidth: 1,
                    titleColor: isDarkMode() ? colors.text : "#ffffff",
                    bodyColor: isDarkMode() ? colors.text : "#ffffff",
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
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: colors.grid,
                    },
                    ticks: {
                        font: {
                            size: 10,
                        },
                        color: colors.textSecondary,
                    },
                    border: {
                        color: colors.border,
                    },
                },
                x: {
                    grid: {
                        color: colors.grid,
                    },
                    ticks: {
                        font: {
                            size: 10,
                        },
                        color: colors.textSecondary,
                        maxRotation: 0,
                        minRotation: 0,
                    },
                    border: {
                        color: colors.border,
                    },
                },
            },
        };
    }

    // گوش دادن به رویداد تغییر کلینیک از Livewire
    window.addEventListener("clinicSelected", function (event) {
        // Ensure we have a valid detail object
        const detail = event.detail || {};
        let newClinicId = detail.clinicId;

        // Log the raw clinicId for debugging
        console.log("Dashboard received clinicId:", newClinicId);

        // Normalize the clinicId - treat null/undefined as 'default'
        newClinicId =
            newClinicId === null || newClinicId === undefined
                ? "default"
                : newClinicId.toString();

        // Only proceed if the clinic ID has actually changed
        if (newClinicId !== selectedClinicId) {
            selectedClinicId = newClinicId;
            console.log("Clinic changed to:", selectedClinicId);

            // Store the previous clinic ID to prevent unnecessary reloads
            window.previousClinicId = selectedClinicId;

            // Load charts with the new clinic ID
            loadCharts(); // به‌روزرسانی نمودارها پس از تغییر کلینیک
        } else {
            console.log("Clinic selection unchanged, skipping chart reload");
        }
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
                medical_center_id: selectedClinicId, // Always send the value as is
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
                // Wait for the next tick to ensure DOM is ready
                setTimeout(() => {
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
                }, 100);
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
        const chartElement = document.getElementById(
            "doctor-performance-chart"
        );
        if (!chartElement) {
            console.log(
                "Performance chart container not found, skipping rendering"
            );
            return;
        }

        let ctx = chartElement.getContext("2d");
        if (window.performanceChart) {
            window.performanceChart.destroy();
        }
        if (!data || data.length === 0) {
            chartElement.parentNode.innerHTML =
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
                ...getCommonOptions(),
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: getChartColors().grid,
                        },
                        ticks: {
                            font: {
                                size: 10,
                            },
                            color: getChartColors().textSecondary,
                        },
                        border: {
                            color: getChartColors().border,
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
                            color: getChartColors().textSecondary,
                            maxRotation: 0,
                            minRotation: 0,
                        },
                        border: {
                            color: getChartColors().border,
                        },
                    },
                },
            },
        });
    }

    // 💰 نمودار درآمد ماهانه - نمودار خطی برای نمایش روند
    function renderIncomeChart(data) {
        const chartElement = document.getElementById("doctor-income-chart");
        if (!chartElement) {
            console.log("Income chart container not found, skipping rendering");
            return;
        }

        let ctx = chartElement.getContext("2d");
        if (window.incomeChart) {
            window.incomeChart.destroy();
        }
        if (!data || data.length === 0) {
            chartElement.parentNode.innerHTML =
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
                ...getCommonOptions(),
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: getChartColors().grid,
                        },
                        ticks: {
                            font: {
                                size: 10,
                            },
                            callback: function (value) {
                                return value.toLocaleString() + " تومان";
                            },
                            color: getChartColors().textSecondary,
                        },
                        border: {
                            color: getChartColors().border,
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
                            color: getChartColors().textSecondary,
                            maxRotation: 0,
                            minRotation: 0,
                        },
                        border: {
                            color: getChartColors().border,
                        },
                    },
                },
            },
        });
    }

    // ��‍⚕️ نمودار تعداد بیماران جدید - نمودار خطی برای نمایش روند
    function renderPatientChart(data) {
        const chartElement = document.getElementById("doctor-patient-chart");
        if (!chartElement) {
            console.log(
                "Patient chart container not found, skipping rendering"
            );
            return;
        }

        let ctx = chartElement.getContext("2d");
        if (window.patientChart) {
            window.patientChart.destroy();
        }
        if (!data || data.length === 0) {
            chartElement.parentNode.innerHTML =
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
                ...getCommonOptions(),
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: getChartColors().grid,
                        },
                        ticks: {
                            font: {
                                size: 10,
                            },
                            color: getChartColors().textSecondary,
                        },
                        border: {
                            color: getChartColors().border,
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
                            color: getChartColors().textSecondary,
                            maxRotation: 0,
                            minRotation: 0,
                        },
                        border: {
                            color: getChartColors().border,
                        },
                    },
                },
            },
        });
    }

    // 📈 نمودار انواع نوبت‌ها - نمودار میله‌ای گروهی
    function renderAppointmentTypesChart(data) {
        const chartElement = document.getElementById("doctor-status-chart");
        if (!chartElement) {
            console.log(
                "Appointment types chart container not found, skipping rendering"
            );
            return;
        }

        let ctx = chartElement.getContext("2d");
        if (window.statusChart) {
            window.statusChart.destroy();
        }
        if (!data || data.length === 0) {
            chartElement.parentNode.innerHTML =
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
                ...getCommonOptions(),
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: getChartColors().grid,
                        },
                        ticks: {
                            font: {
                                size: 10,
                            },
                            color: getChartColors().textSecondary,
                        },
                        border: {
                            color: getChartColors().border,
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
                            color: getChartColors().textSecondary,
                            maxRotation: 0,
                            minRotation: 0,
                        },
                        border: {
                            color: getChartColors().border,
                        },
                    },
                },
            },
        });
    }

    // 🥧 نمودار درصد نوبت‌ها - نمودار دایره‌ای
    function renderStatusPieChart(data) {
        const chartElement = document.getElementById("doctor-status-pie-chart");
        if (!chartElement) {
            console.log(
                "Status pie chart container not found, skipping rendering"
            );
            return;
        }

        let ctx = chartElement.getContext("2d");
        if (window.statusPieChart) {
            window.statusPieChart.destroy();
        }
        if (!data || data.length === 0) {
            chartElement.parentNode.innerHTML =
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
                ...getCommonOptions(),
                plugins: {
                    ...getCommonOptions().plugins,
                    tooltip: {
                        ...getCommonOptions().plugins.tooltip,
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
        const chartElement = document.getElementById(
            "doctor-patient-trend-chart"
        );
        if (!chartElement) {
            console.log(
                "Patient trend chart container not found, skipping rendering"
            );
            return;
        }

        let ctx = chartElement.getContext("2d");
        if (window.patientTrendChart) {
            window.patientTrendChart.destroy();
        }
        if (!data || data.length === 0) {
            chartElement.parentNode.innerHTML =
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
                ...getCommonOptions(),
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: getChartColors().grid,
                        },
                        ticks: {
                            font: {
                                size: 10,
                            },
                            color: getChartColors().textSecondary,
                        },
                        border: {
                            color: getChartColors().border,
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
                            color: getChartColors().textSecondary,
                            maxRotation: 0,
                            minRotation: 0,
                        },
                        border: {
                            color: getChartColors().border,
                        },
                    },
                },
            },
        });
    }

    // 🗣️ نمودار نوبت‌های مشاوره - نمودار میله‌ای گروهی
    function renderCounselingChart(data) {
        const chartElement = document.getElementById("doctor-counseling-chart");
        if (!chartElement) {
            console.log(
                "Counseling chart container not found, skipping rendering"
            );
            return;
        }

        let ctx = chartElement.getContext("2d");
        if (window.counselingChart) {
            window.counselingChart.destroy();
        }
        if (!data || data.length === 0) {
            chartElement.parentNode.innerHTML =
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
                ...getCommonOptions(),
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: getChartColors().grid,
                        },
                        ticks: {
                            font: {
                                size: 10,
                            },
                            color: getChartColors().textSecondary,
                        },
                        border: {
                            color: getChartColors().border,
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
                            color: getChartColors().textSecondary,
                            maxRotation: 0,
                            minRotation: 0,
                        },
                        border: {
                            color: getChartColors().border,
                        },
                    },
                },
            },
        });
    }

    // ✍️ نمودار نوبت‌های دستی - نمودار میله‌ای گروهی
    function renderManualChart(data) {
        const chartElement = document.getElementById("doctor-manual-chart");
        if (!chartElement) {
            console.log("Manual chart container not found, skipping rendering");
            return;
        }

        let ctx = chartElement.getContext("2d");
        if (window.manualChart) {
            window.manualChart.destroy();
        }
        if (!data || data.length === 0) {
            chartElement.parentNode.innerHTML =
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
                ...getCommonOptions(),
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: getChartColors().grid,
                        },
                        ticks: {
                            font: {
                                size: 10,
                            },
                            color: getChartColors().textSecondary,
                        },
                        border: {
                            color: getChartColors().border,
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
                            color: getChartColors().textSecondary,
                            maxRotation: 0,
                            minRotation: 0,
                        },
                        border: {
                            color: getChartColors().border,
                        },
                    },
                },
            },
        });
    }

    // 💸 نمودار درآمد کلی - نمودار خطی با ناحیه
    function renderTotalIncomeChart(data) {
        const chartElement = document.getElementById(
            "doctor-total-income-chart"
        );
        if (!chartElement) {
            console.log(
                "Total income chart container not found, skipping rendering"
            );
            return;
        }

        let ctx = chartElement.getContext("2d");
        if (window.totalIncomeChart) {
            window.totalIncomeChart.destroy();
        }
        if (!data || data.length === 0) {
            chartElement.parentNode.innerHTML =
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
                ...getCommonOptions(),
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: getChartColors().grid,
                        },
                        ticks: {
                            font: {
                                size: 10,
                            },
                            callback: function (value) {
                                return value.toLocaleString() + " تومان";
                            },
                            color: getChartColors().textSecondary,
                        },
                        border: {
                            color: getChartColors().border,
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
                            color: getChartColors().textSecondary,
                            maxRotation: 0,
                            minRotation: 0,
                        },
                        border: {
                            color: getChartColors().border,
                        },
                    },
                },
            },
        });
    }

    // بارگذاری اولیه نمودارها
    loadCharts();

    // گوش دادن به تغییرات دارک مود
    function handleDarkModeChange() {
        // به‌روزرسانی نمودارها با رنگ‌های جدید
        if (window.performanceChart) {
            window.performanceChart.update();
        }
        if (window.incomeChart) {
            window.incomeChart.update();
        }
        if (window.patientChart) {
            window.patientChart.update();
        }
        if (window.statusChart) {
            window.statusChart.update();
        }
        if (window.statusPieChart) {
            window.statusPieChart.update();
        }
        if (window.patientTrendChart) {
            window.patientTrendChart.update();
        }
        if (window.counselingChart) {
            window.counselingChart.update();
        }
        if (window.manualChart) {
            window.manualChart.update();
        }
        if (window.totalIncomeChart) {
            window.totalIncomeChart.update();
        }
    }

    // گوش دادن به رویداد تغییر دارک مود
    window.addEventListener("darkModeToggled", handleDarkModeChange);

    // گوش دادن به تغییرات در localStorage
    window.addEventListener("storage", function (e) {
        if (e.key === "darkMode") {
            handleDarkModeChange();
        }
    });

    // بررسی تغییرات دارک مود هر 1 ثانیه (برای تغییرات خارجی)
    setInterval(() => {
        const currentDarkMode = isDarkMode();
        if (window.lastDarkModeState !== currentDarkMode) {
            window.lastDarkModeState = currentDarkMode;
            handleDarkModeChange();
        }
    }, 1000);
}

// Initialize when DOM is fully loaded
document.addEventListener("DOMContentLoaded", initializeDashboard);

// Also initialize when Livewire finishes loading
document.addEventListener("livewire:load", initializeDashboard);

document.addEventListener("DOMContentLoaded", function () {
    const persianMonths = [
        "فروردین",
        "اردیبهشت",
        "خرداد",
        "تیر",
        "مرداد",
        "شهریور",
        "مهر",
        "آبان",
        "آذر",
        "دی",
        "بهمن",
        "اسفند",
    ];
    const persianDays = [
        "دوشنبه",
        "سه‌شنبه",
        "چهارشنبه",
        "پنج‌شنبه",
        "جمعه",
        "شنبه",
        "یک‌شنبه",
    ];
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: "bottom",
                align: "center",
                labels: {
                    font: {
                        family: "Vazir",
                        size: 14,
                    },
                    padding: 20,
                    usePointStyle: true,
                    pointStyle: "circle",
                },
            },
        },
    };
    const pieChartOptions = {
        ...commonOptions,
        plugins: {
            legend: {
                display: true,
                position: "bottom",
                align: "center",
                labels: {
                    font: {
                        family: "Vazir",
                        size: 14,
                    },
                    padding: 20,
                    usePointStyle: true,
                    pointStyle: "circle",
                },
            },
            tooltip: {
                callbacks: {
                    label: function (context) {
                        return context.label + ": " + context.raw;
                    },
                },
            },
        },
        layout: {
            padding: {
                bottom: 40,
            },
        },
        cutout: "50%",
    };
    // نمودار نوبت‌ها در هر ماه
    const appointmentsByMonthData = chartData.appointmentsByMonth;
    const monthLabels = [];
    const monthData = [];
    for (let i = 1; i <= 12; i++) {
        if (appointmentsByMonthData[i] !== undefined) {
            monthLabels.push(persianMonths[i - 1]);
            monthData.push(appointmentsByMonthData[i]);
        }
    }
    new Chart(document.getElementById("appointmentsByMonthChart"), {
        type: "bar",
        data: {
            labels: monthLabels,
            datasets: [
                {
                    label: "تعداد نوبت‌ها",
                    data: monthData,
                    backgroundColor: "rgb(75, 192, 192)",
                    borderWidth: 0,
                },
            ],
        },
        options: {
            ...commonOptions,
            plugins: {
                ...commonOptions.plugins,
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return "تعداد نوبت‌ها: " + context.raw;
                        },
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                    },
                },
            },
        },
    });
    // نمودار وضعیت نوبت‌ها
    const appointmentStatusesData = chartData.appointmentStatuses;
    new Chart(document.getElementById("appointmentStatusesChart"), {
        type: "pie",
        data: {
            labels: Object.keys(appointmentStatusesData),
            datasets: [
                {
                    data: Object.values(appointmentStatusesData),
                    backgroundColor: [
                        "rgb(75, 192, 192)",
                        "rgb(255, 99, 132)",
                        "rgb(255, 205, 86)",
                        "rgb(54, 162, 235)",
                    ],
                },
            ],
        },
        options: pieChartOptions,
    });
    // نمودار نوبت‌ها در روزهای هفته
    const appointmentsByDayOfWeekData = chartData.appointmentsByDayOfWeek;
    new Chart(document.getElementById("appointmentsByDayOfWeekChart"), {
        type: "bar",
        data: {
            labels: persianDays,
            datasets: [
                {
                    label: "تعداد نوبت‌ها",
                    data: Object.values(appointmentsByDayOfWeekData),
                    backgroundColor: "rgb(153, 102, 255)",
                },
            ],
        },
        options: commonOptions,
    });
    // نمودار توزیع تخصص‌های پزشکان
    const doctorSpecialtiesData = chartData.doctorSpecialties;
    const topSpecialties = Object.entries(doctorSpecialtiesData)
        .sort(([, a], [, b]) => b - a)
        .slice(0, 5)
        .reduce(
            (r, [k, v]) => ({
                ...r,
                [k]: v,
            }),
            {}
        );
    new Chart(document.getElementById("doctorSpecialtiesChart"), {
        type: "doughnut",
        data: {
            labels: Object.keys(topSpecialties),
            datasets: [
                {
                    data: Object.values(topSpecialties),
                    backgroundColor: [
                        "rgb(255, 99, 132)",
                        "rgb(54, 162, 235)",
                        "rgb(255, 205, 86)",
                        "rgb(75, 192, 192)",
                        "rgb(153, 102, 255)",
                    ],
                },
            ],
        },
        options: pieChartOptions,
    });
    // نمودار روند نوبت‌ها
    const appointmentsTrendData = chartData.appointmentsTrend;
    const jalaliAppointmentsTrend = convertWeeksToJalali(appointmentsTrendData);
    new Chart(document.getElementById("appointmentsTrendChart"), {
        type: "line",
        data: {
            labels: Object.keys(jalaliAppointmentsTrend),
            datasets: [
                {
                    label: "تعداد نوبت‌ها",
                    data: Object.values(jalaliAppointmentsTrend),
                    borderColor: "rgb(153, 102, 255)",
                    tension: 0.1,
                },
            ],
        },
        options: commonOptions,
    });
    // نمودار مقایسه کلینیک‌ها
    const clinicComparisonData = chartData.clinicComparison;
    new Chart(document.getElementById("medicalCenterComparisonChart"), {
        type: "bar",
        data: {
            labels: Object.keys(clinicComparisonData),
            datasets: [
                {
                    label: "حاضر شده",
                    data: Object.values(clinicComparisonData).map(
                        (item) => item["حاضر شده"]
                    ),
                    backgroundColor: "rgb(75, 192, 192)",
                },
                {
                    label: "لغو شده",
                    data: Object.values(clinicComparisonData).map(
                        (item) => item["لغو شده"]
                    ),
                    backgroundColor: "rgb(255, 99, 132)",
                },
                {
                    label: "غایب",
                    data: Object.values(clinicComparisonData).map(
                        (item) => item["غایب"]
                    ),
                    backgroundColor: "rgb(255, 205, 86)",
                },
            ],
        },
        options: commonOptions,
    });
    // نمودار وضعیت پرداخت‌ها
    const paymentStatusData = chartData.paymentStatus;
    new Chart(document.getElementById("paymentStatusChart"), {
        type: "pie",
        data: {
            labels: Object.keys(paymentStatusData),
            datasets: [
                {
                    data: Object.values(paymentStatusData),
                    backgroundColor: [
                        "rgb(75, 192, 192)",
                        "rgb(255, 99, 132)",
                        "rgb(255, 205, 86)",
                    ],
                },
            ],
        },
        options: pieChartOptions,
    });
    // نمودار آمار بازدید
    const visitorStatsData = chartData.visitorStats;
    const persianVisitorLabels = {
        today: "امروز",
        yesterday: "دیروز",
        this_week: "این هفته",
        last_week: "هفته گذشته",
        this_month: "این ماه",
    };
    const persianVisitorData = {};
    Object.keys(visitorStatsData).forEach((key) => {
        persianVisitorData[persianVisitorLabels[key]] = visitorStatsData[key];
    });
    new Chart(document.getElementById("visitorStatsChart"), {
        type: "bar",
        data: {
            labels: Object.keys(persianVisitorData),
            datasets: [
                {
                    label: "تعداد بازدید",
                    data: Object.values(persianVisitorData),
                    backgroundColor: "rgb(54, 162, 235)",
                },
            ],
        },
        options: commonOptions,
    });
    // نمودار درآمد ماهانه
    const monthlyRevenueData = chartData.monthlyRevenue;
    const jalaliMonthlyRevenue = convertMonthsToJalali(monthlyRevenueData);
    new Chart(document.getElementById("monthlyRevenueChart"), {
        type: "line",
        data: {
            labels: Object.keys(jalaliMonthlyRevenue),
            datasets: [
                {
                    label: "درآمد ماهانه",
                    data: Object.values(jalaliMonthlyRevenue),
                    borderColor: "rgb(75, 192, 192)",
                    tension: 0.1,
                },
            ],
        },
        options: {
            ...commonOptions,
            plugins: {
                ...commonOptions.plugins,
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return "درآمد: " + context.raw + " تومان";
                        },
                    },
                },
            },
        },
    });
    // نمودار توزیع بیمه‌ها
    const insuranceDistributionData = chartData.insuranceDistribution;
    const topInsurances = Object.entries(insuranceDistributionData)
        .sort(([, a], [, b]) => b - a)
        .slice(0, 5)
        .reduce(
            (r, [k, v]) => ({
                ...r,
                [k]: v,
            }),
            {}
        );
    new Chart(document.getElementById("insuranceDistributionChart"), {
        type: "doughnut",
        data: {
            labels: Object.keys(topInsurances),
            datasets: [
                {
                    data: Object.values(topInsurances),
                    backgroundColor: [
                        "rgb(255, 99, 132)",
                        "rgb(54, 162, 235)",
                        "rgb(255, 205, 86)",
                        "rgb(75, 192, 192)",
                        "rgb(153, 102, 255)",
                    ],
                },
            ],
        },
        options: pieChartOptions,
    });
    // نمودار روند رشد کاربران
    const userGrowthData = chartData.userGrowth;
    const jalaliUserGrowth = convertMonthsToJalali(userGrowthData);
    new Chart(document.getElementById("userGrowthChart"), {
        type: "line",
        data: {
            labels: Object.keys(jalaliUserGrowth),
            datasets: [
                {
                    label: "تعداد کاربران جدید",
                    data: Object.values(jalaliUserGrowth),
                    borderColor: "rgb(255, 99, 132)",
                    tension: 0.1,
                },
            ],
        },
        options: {
            ...commonOptions,
            plugins: {
                ...commonOptions.plugins,
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return "تعداد کاربران: " + context.raw;
                        },
                    },
                },
            },
        },
    });
    // نمودار توزیع جنسیت بیماران
    const patientGenderData = chartData.patientGenderDistribution;
    new Chart(document.getElementById("patientGenderDistributionChart"), {
        type: "pie",
        data: {
            labels: Object.keys(patientGenderData),
            datasets: [
                {
                    data: Object.values(patientGenderData),
                    backgroundColor: ["rgb(54, 162, 235)", "rgb(255, 99, 132)"],
                },
            ],
        },
        options: pieChartOptions,
    });
    // نمودار توزیع سنی بیماران
    const patientAgeData = chartData.patientAgeDistribution;
    new Chart(document.getElementById("patientAgeDistributionChart"), {
        type: "bar",
        data: {
            labels: Object.keys(patientAgeData),
            datasets: [
                {
                    label: "تعداد بیماران",
                    data: Object.values(patientAgeData),
                    backgroundColor: "rgb(75, 192, 192)",
                },
            ],
        },
        options: commonOptions,
    });
    // نمودار توزیع جغرافیایی بیماران
    const geographicData = chartData.patientGeographicDistribution;
    const topGeographic = Object.entries(geographicData)
        .sort(([, a], [, b]) => b - a)
        .slice(0, 10)
        .reduce(
            (r, [k, v]) => ({
                ...r,
                [k]: v,
            }),
            {}
        );
    new Chart(document.getElementById("patientGeographicDistributionChart"), {
        type: "bar",
        data: {
            labels: Object.keys(topGeographic),
            datasets: [
                {
                    label: "تعداد بیماران",
                    data: Object.values(topGeographic),
                    backgroundColor: "rgb(75, 192, 192)",
                },
            ],
        },
        options: commonOptions,
    });
    // تبدیل ماه‌های میلادی به شمسی برای نمودارها
    function convertMonthsToJalali(data) {
        const jalaliMonths = {
            "01": "فروردین",
            "02": "اردیبهشت",
            "03": "خرداد",
            "04": "تیر",
            "05": "مرداد",
            "06": "شهریور",
            "07": "مهر",
            "08": "آبان",
            "09": "آذر",
            10: "دی",
            11: "بهمن",
            12: "اسفند",
        };
        const result = {};
        Object.keys(data).forEach((key) => {
            if (key && data[key] !== undefined) {
                const [year, month] = key.split("-");
                if (month && jalaliMonths[month]) {
                    const jalaliMonth = jalaliMonths[month];
                    result[jalaliMonth] = data[key];
                }
            }
        });
        return result;
    }
    // تبدیل هفته‌های میلادی به شمسی برای نمودارها
    function convertWeeksToJalali(data) {
        const result = {};
        Object.keys(data).forEach((key) => {
            const [year, week] = key.split("-");
            const date = moment().year(year).week(week);
            result[toJalaliWeek(date)] = data[key];
        });
        return result;
    }
    // تبدیل تاریخ میلادی به شمسی
    function toJalali(date) {
        return moment(date).format("jYYYY/jMM/jDD");
    }
    // تبدیل هفته میلادی به شمسی
    function toJalaliWeek(date) {
        return moment(date).format("jYYYY/jMM/jDD");
    }
});

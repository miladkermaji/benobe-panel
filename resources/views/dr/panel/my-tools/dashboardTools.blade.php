<script>
  /* drop select option */

  $(document).ready(function() {
    let dropdownOpen = false;

    let selectedClinic = localStorage.getItem('selectedClinic');
    let selectedClinicId = localStorage.getItem('selectedClinicId');

    if (selectedClinic && selectedClinicId) {
      $('.dropdown-label').text(selectedClinic);
      $('.option-card').each(function() {
        if ($(this).attr('data-id') === selectedClinicId) {
          $('.option-card').removeClass('card-active');
          $(this).addClass('card-active');
        }
      });
    } else {
      localStorage.setItem('selectedClinic', 'ویزیت آنلاین به نوبه');
      localStorage.setItem('selectedClinicId', 'default');
    }

    // **بررسی کلینیک‌های غیرفعال و اضافه کردن افکت هشدار**
    function checkInactiveClinics() {
      var hasInactiveClinics = $('.option-card[data-active="0"]').length > 0;
      if (hasInactiveClinics) {
        $('.dropdown-trigger').addClass('warning');
      } else {
        $('.dropdown-trigger').removeClass('warning');
      }
    }

    checkInactiveClinics(); // اجرای بررسی هنگام بارگذاری صفحه

    // باز و بسته کردن دراپ‌داون
    $('.dropdown-trigger').on('click', function(event) {

      event.stopPropagation();
      dropdownOpen = !dropdownOpen;
      $(this).toggleClass('border border-primary');
      $('.my-dropdown-menu').toggleClass('d-none');

      setTimeout(() => {
        dropdownOpen = $('.my-dropdown-menu').is(':visible');
      }, 100);
    });

    // بستن دراپ‌داون هنگام کلیک بیرون
    $(document).on('click', function() {
      if (dropdownOpen) {
        $('.dropdown-trigger').removeClass('border border-primary');
        $('.my-dropdown-menu').addClass('d-none');
        dropdownOpen = false;
      }
    });

    // جلوگیری از بسته شدن هنگام کلیک روی منوی دراپ‌داون
    $('.my-dropdown-menu').on('click', function(event) {
      event.stopPropagation();
    });

    $('.option-card').on('click', function() {
      let currentDate = moment().format('YYYY-MM-DD');
      let persianDate = moment(currentDate, 'YYYY-MM-DD').locale('fa').format('jYYYY/jMM/jDD');


      var selectedText = $(this).find('.font-weight-bold.d-block.fs-15').text().trim();
      var selectedId = $(this).attr('data-id');

      $('.option-card').removeClass('card-active');
      $(this).addClass('card-active');

      $('.dropdown-label').text(selectedText);
      // Update local storage
      localStorage.setItem('selectedClinic', selectedText);
      localStorage.setItem('selectedClinicId', selectedId);

      checkInactiveClinics();
      handleDateSelection(persianDate, selectedId);
      loadAppointments(persianDate, selectedId)
      $('.dropdown-trigger').removeClass('border border-primary');
      $('.my-dropdown-menu').addClass('d-none');
      dropdownOpen = false;
    });
  });
  /* drop select option */

  const appointmentsTableBody = $('.table tbody'); // بخش <tbody> جدول
  // لودینگ به جدول اضافه کنیم
  let loadingIndicator = `<tr id="loading-row w-100">
                                <td colspan="10" class="text-center py-3">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">در حال بارگذاری...</span>
                                    </div>
                                </td>
                            </tr>`;

  function getPrescriptionStatus(status) {
    switch (status) {
      case 'scheduled':
        return '<span class="font-weight-bold text-warning">در انتظار</span>';
      case 'cancelled':
        return '<span class="font-weight-bold text-danger">لغو شده</span>';
      case 'attended':
        return '<span class="font-weight-bold text-primary">ویزیت شده</span>';
      case 'missed':
        return '<span class="font-weight-bold text-muted">ویزیت نشده</span>';
      default:
        return '<span class="font-weight-bold text-dark">نامشخص</span>';
    }
  }

  function getPaymentStatus(status) {
    switch (status) {
      case 'pending':
        return '<span class="font-weight-bold text-warning">در حال پرداخت</span>';
      case 'paid':
        return '<span class="font-weight-bold text-success">پرداخت شده</span>';
      case 'unpaid':
        return '<span class="font-weight-bold text-danger">پرداخت نشده</span>';
      default:
        return '<span class="font-weight-bold text-dark">نامشخص</span>';
    }
  }
  let currentDate = moment().format('YYYY-MM-DD');
  const days = 14;
  const calendar = $('#calendar');
  // مخفی کردن لودینگ بعد از دریافت داده‌ها
  function loadCalendar(date) {
    calendar.empty();
    for (let i = 0; i < days; i++) {
      const current = moment(date).add(i, 'days');
      const persianDate = current.locale('fa').format('dddd');
      const persianFormattedDate = current.locale('fa').format('YYYY-MM-DD'); // فرمت استاندارد برای ارسال به سرور
      const isActive = current.isSame(moment(), 'day') ? 'my-active' : '';
      const card =
        ` <div class="calendar-card btn btn-light ${isActive}" data-date="${persianFormattedDate}"> 
         <div class="day-name">${persianDate}</div> 
         <div class="date">${current.locale('fa').format('D MMMM YYYY')}</div> 
       </div>`;
      calendar.append(card);
    }
    // اضافه کردن رویداد کلیک به تاریخ‌های تقویم

  }

  function animateAndLoadCalendar(direction) {
    const animation = {
      left: direction === 'next' ? '-100%' : '100%',
      opacity: 0
    };
    calendar.animate(animation, 300, function() {
      if (direction === 'next') {
        currentDate = moment(currentDate).add(days, 'days').format('YYYY-MM-DD');
      } else {
        currentDate = moment(currentDate).subtract(days, 'days').format('YYYY-MM-DD');
      }
      loadCalendar(currentDate);
      calendar.css({
        left: direction === 'next' ? '100%' : '-100%',
        opacity: 0
      });
      calendar.animate({
        left: '0%',
        opacity: 1
      }, 300);
    });
  }
  // تابع بارگذاری نوبت‌ها با شناسه کلینیک جدید
  function loadAppointments(selectedDate, clinicId) {
    let persianDate = moment(selectedDate, 'YYYY-MM-DD').locale('fa').format('jYYYY/jMM/jDD');
    $.ajax({
      url: "{{ route('doctor.appointments.by-date') }}",
      method: 'GET',
      data: {
        date: selectedDate,
        selectedClinicId: clinicId
      },
      success: function(response) {
        appointmentsTableBody.html('');
        if (response.appointments.length > 0) {
          response.appointments.forEach(function(appointment) {
            const nationalCode = appointment.patient.national_code ?
              appointment.patient.national_code : 'نامشخص';
            const isAttended = appointment.status === 'attended';
            const buttonDisabled = isAttended ? 'disabled' : '';
            const dropdownItemDisabled = isAttended ? 'disabled' : ''; // برای غیرفعال کردن آیتم‌های دراپ‌داون
            const appointmentHTML = `
    <tr data-appointment-id="${appointment.id}">
        <td><input type="checkbox" class="row-checkbox"></td>
        <td>${appointment.patient.first_name} ${appointment.patient.last_name}</td>
        <td>${appointment.patient.mobile}</td>
        <td>${nationalCode}</td> 
        <td>${getPrescriptionStatus(appointment.status)}</td>
        <td>${getPaymentStatus(appointment.payment_status)}</td>
        <td>${appointment.insurance ? appointment.insurance.name : 'ندارد'}</td>
        <td>${moment(appointment.appointment_date).locale('fa').format('jYYYY/jMM/jDD')}</td>
        <td>${appointment.appointment_time}</td>
        <td>
            <button class="btn btn-outline-info btn-end-visit ${
                (appointment.status === 'attended' || appointment.status === 'cancelled') ? 'disabled' : ''
            }" data-appointment-id="${appointment.id}">
                پایان ویزیت
            </button>
        </td>
        <td class="text-center">
            <div class="dropdown d-inline-block position-relative">
                <button class="btn btn-light p-1 btn-sm dropdown-toggle custom-dropdown-trigger" type="button">
                    <img src="{{ asset('dr-assets/icons/dots-vertical-svgrepo-com.svg') }}" width="20" height="20">
                </button>
                <ul class="dropdown-menu dropdown-menu-end my-drp-left-0">
                    <li class=" ${
                (appointment.status === 'attended' || appointment.status === 'cancelled') ? 'disabled' : ''
            }">
                        <a class="dropdown-item text-dark cancel-appointment" href="#" data-id="${appointment.id}">
                            لغو نوبت
                        </a>
                    </li>
                    <li class="${(appointment.status === 'attended' || appointment.status === 'cancelled') ? 'disabled' : ''}">
                        <a class="dropdown-item text-dark move-appointment" href="#" data-date="${appointment.appointment_date}" data-id="${appointment.id}">
                            جابجایی نوبت
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item text-dark block-user" href="#" data-id="${appointment.id}" data-mobile="${appointment.patient.mobile}" data-user-id="${appointment.patient.id}" data-user-name="${appointment.patient.first_name + ' ' + appointment.patient.last_name}">
                            مسدود کردن کاربر
                        </a>
                    </li>
                </ul>
            </div>
        </td>
    </tr>`;
            appointmentsTableBody.append(appointmentHTML);
          });
        } else {
          appointmentsTableBody.html(`
                    <tr>
                        <td colspan="10" class="text-center py-3">هیچ نوبتی برای این تاریخ وجود ندارد.</td>
                    </tr>
                `);
        }
      },
      error: function() {
        appointmentsTableBody.html(`
                <tr>
                    <td colspan="10" class="text-center py-3 text-danger">خطا در دریافت نوبت‌ها.</td>
                </tr>
            `);
      }
    });
  }
  $(document).ready(function() {

    $('#next').click(function() {
      animateAndLoadCalendar('next');
    });
    $('#prev').click(function() {
      animateAndLoadCalendar('prev');
    });



    loadCalendar(currentDate); // بارگذاری اولیه تقویم
  });
  $(document).on("click", ".cancel-appointment", function() {
    let appointmentId = $(this).closest("tr").data("id");
  });

  $(document).on("click", ".move-appointment", function() {
    let appointmentId = $(this).closest("tr").data("id");
  });

  $(document).on("click", ".block-user", function() {
    let userId = $(this).closest("tr").data("user-id");
  });

  function handleDateSelection(selectedDate, selectedClinicId) {
    showLoading();
    $.ajax({
      url: "{{ route('doctor.appointments.by-date') }}",
      method: 'GET',
      data: {
        date: selectedDate,
        selectedClinicId: selectedClinicId
      },
      success: function(response) {
        hideLoading();
        appointmentsTableBody.html('');
        if (response.appointments.length > 0) {
          response.appointments.forEach(function(appointment) {
            const nationalCode = appointment.patient.national_code ?
              appointment.patient.national_code : 'نامشخص';
            const isAttended = appointment.status === 'attended';
            const buttonDisabled = isAttended ? 'disabled' : '';
            const dropdownItemDisabled = isAttended ? 'disabled' : ''; // برای غیرفعال کردن آیتم‌های دراپ‌داون
            const appointmentHTML = `
    <tr data-appointment-id="${appointment.id}">
        <td><input type="checkbox" class="row-checkbox"></td>
        <td>${appointment.patient.first_name} ${appointment.patient.last_name}</td>
        <td>${appointment.patient.mobile}</td>
        <td>${nationalCode}</td> 
        <td>${getPrescriptionStatus(appointment.status)}</td>
                <td>${getPaymentStatus(appointment.payment_status)}</td>

        <td>${appointment.insurance ? appointment.insurance.name : 'ندارد'}</td>
        <td>${moment(appointment.appointment_date).locale('fa').format('jYYYY/jMM/jDD')}</td>
        <td>${appointment.appointment_time}</td>
        <td>
            <button class="btn btn-outline-info btn-end-visit ${
                (appointment.status === 'attended' || appointment.status === 'cancelled') ? 'disabled' : ''
            }" data-appointment-id="${appointment.id}">
                پایان ویزیت
            </button>
        </td>
        <td class="text-center">
            <div class="dropdown d-inline-block position-relative">
                <button class="btn btn-light p-1 btn-sm dropdown-toggle custom-dropdown-trigger" type="button">
                    <img src="{{ asset('dr-assets/icons/dots-vertical-svgrepo-com.svg') }}" width="20" height="20">
                </button>
                <ul class="dropdown-menu dropdown-menu-end my-drp-left-0">
                    <li class=" ${
                (appointment.status === 'attended' || appointment.status === 'cancelled') ? 'disabled' : ''
            }">
                        <a class="dropdown-item text-dark cancel-appointment" href="#" data-id="${appointment.id}">
                            لغو نوبت
                        </a>
                    </li>
                    <li class="${(appointment.status === 'attended' || appointment.status === 'cancelled') ? 'disabled' : ''}">
                        <a class="dropdown-item text-dark move-appointment" href="#" data-date="${appointment.appointment_date}" data-id="${appointment.id}">
                            جابجایی نوبت
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item text-dark block-user" href="#" data-id="${appointment.id}" data-mobile="${appointment.patient.mobile}" data-user-id="${appointment.patient.id}" data-user-name="${appointment.patient.first_name + ' ' + appointment.patient.last_name}">
                            مسدود کردن کاربر
                        </a>
                    </li>
                </ul>
            </div>
        </td>
    </tr>`;
            appointmentsTableBody.append(appointmentHTML);
          });
        } else {
          appointmentsTableBody.html(`
                    <tr>
                        <td colspan="10" class="text-center py-3">هیچ نوبتی برای این تاریخ وجود ندارد.</td>
                    </tr>`);
        }
      },
      error: function() {
        hideLoading();
        appointmentsTableBody.html(`
                <tr>
                    <td colspan="10" class="text-center py-3 text-danger">خطا در دریافت نوبت‌ها.</td>
                </tr>`);
      }
    });
  }

  function showLoading() {
    appointmentsTableBody.html(`
            <tr id="loading-row w-100">
                <td colspan="10" class="text-center py-3 w-100">
                    <div class="spinner-border text-primary " role="status">
                        <span class="sr-only w-100">در حال بارگذاری...</span>
                    </div>
                </td>
        `);
  }

  function hideLoading() {
    $("#loading-row").remove();
  }

  function loadCalendar(date) {
    calendar.empty();
    let todayExists = false;
    $('.calendar-card').removeClass('my-active'); // حذف انتخاب قبلی
    for (let i = 0; i < days; i++) {
      const current = moment(date).add(i, 'days');
      const persianDate = current.locale('fa').format('dddd');
      const persianFormattedDate = current.locale('fa').format('YYYY-MM-DD');
      const isActive = current.isSame(moment(), 'day') ? 'my-active' : '';
      if (isActive) todayExists = persianFormattedDate; // ذخیره تاریخ امروز برای انتخاب پیش‌فرض
      const card = `
                <div class="calendar-card btn btn-light ${isActive}" data-date="${persianFormattedDate}">
                    <div class="day-name">${persianDate}</div>
                    <div class="date">${current.locale('fa').format('D MMMM YYYY')}</div>
                </div>`;
      calendar.append(card);
    }
    // افزودن رویداد کلیک به کارت‌های تقویم
    $('.calendar-card').on('click', function() {
      const selectedDate = $(this).attr('data-date');
      selectedClinicId = localStorage.getItem('selectedClinicId')
      $('.calendar-card').removeClass('my-active');
      $(this).addClass('my-active');
      handleDateSelection(selectedDate, selectedClinicId);
      loadAppointments(selectedDate, selectedClinicId)
    });
    // در اولین لود صفحه، داده‌های امروز را نمایش دهیم
    if (todayExists) {
      $('.calendar-card[data-date="' + todayExists + '"]').addClass('my-active');
      handleDateSelection(todayExists, localStorage.getItem('selectedClinicId'));
    }
  }

  function animateAndLoadCalendar(direction) {
    const animation = {
      left: direction === 'next' ? '-100%' : '100%',
      opacity: 0
    };
    calendar.animate(animation, 300, function() {
      currentDate = moment(currentDate).add(direction === 'next' ? days : -days, 'days').format('YYYY-MM-DD');
      loadCalendar(currentDate);
      calendar.css({
        left: direction === 'next' ? '100%' : '-100%',
        opacity: 0
      });
      calendar.animate({
        left: '0%',
        opacity: 1
      }, 300);
    });
  }
  // نمایش لودینگ قبل از ارسال درخواست AJAX
  $(document).ready(function() {
    let currentDate = moment().format('YYYY-MM-DD');
    const days = 14;
    const calendar = $('#calendar');
    const appointmentsTableBody = $('.table tbody'); // بخش <tbody> جدول









    $('#next').click(() => animateAndLoadCalendar('next'));
    $('#prev').click(() => animateAndLoadCalendar('prev'));
    loadCalendar(currentDate); // بارگذاری اولیه تقویم
  });
  $(document).ready(function() {
    let currentDate = moment().format('YYYY-MM-DD'); // مقدار پیش‌فرض (امروز)
    let persianDate = moment(currentDate, 'YYYY-MM-DD').locale('fa').format('jYYYY/jMM/jDD');

    let isInitialLoad = true; // بررسی اولین بارگذاری صفحه
    function searchPatients(query) {
      let selectedDate = currentDate;
      let spanTextDate = $('.selectDate_datepicker__xkZeS span').text();

      let requestData = {
        date: spanTextDate,
        selectedClinicId: localStorage.getItem('selectedClinicId')
      };
      if (query !== "") {
        requestData.query = query;
      }
      $.ajax({
        url: "{{ route('search.patients') }}",
        method: "GET",
        data: requestData,
        beforeSend: function() {
          if (!isInitialLoad) {
            $(".table tbody").html(`
                    <tr>
                        <td colspan="10" class="text-center py-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">در حال جستجو...</span>
                            </div>
                        </td>
                    </tr>
                `);
          }
        },
        success: function(response) {
          let appointmentsTableBody = $(".table tbody");
          appointmentsTableBody.html("");
          if (response.patients.length > 0) {
            response.patients.forEach(function(appointment) {
              let patient = appointment.patient || {};
              let insurance = appointment.insurance ? appointment.insurance.name : 'ندارد';
              let appointmentDate = appointment.appointment_date ?
                moment(appointment.appointment_date).locale('fa').format('jYYYY/jMM/jDD') :
                'نامشخص';
              const isAttended = appointment.status === 'attended';
              const buttonDisabled = isAttended ? 'disabled' : '';
              const dropdownItemDisabled = (appointment.status === 'attended' || appointment.status ===
                'cancelled') ? 'disabled' : '';

              let appointmentHTML = `
                        <tr>
                            <td><input type="checkbox" class="row-checkbox"></td>
                            <td>${patient.first_name ? patient.first_name : 'نامشخص'} 
                                ${patient.last_name ? patient.last_name : ''}</td>
                            <td>${patient.mobile ? patient.mobile : 'نامشخص'}</td>
                            <td>${patient.national_code ? patient.national_code : 'نامشخص'}</td>
                            <td>${getPrescriptionStatus(appointment.status)}</td>
        <td>${getPaymentStatus(appointment.payment_status)}</td>

                            <td>${insurance}</td>
                            <td>${appointmentDate}</td>
                            <td>${appointment.appointment_time}</td>
                            <td>
                                <button class="btn btn-outline-info btn-end-visit ${buttonDisabled}" data-appointment-id="${appointment.id}" ${buttonDisabled}  ${
                (appointment.status === 'attended' || appointment.status === 'cancelled') ? 'disabled' : ''
            }>
                                    پایان ویزیت
                                </button>
                            </td>
                            <td class="text-center">
                                <div class="dropdown d-inline-block position-relative">
                                    <button class="btn btn-light p-1 btn-sm dropdown-toggle custom-dropdown-trigger" type="button">
                                        <img src="{{ asset('dr-assets/icons/dots-vertical-svgrepo-com.svg') }}" width="20" height="20">
                                    </button>
                                    
                                    <ul class="dropdown-menu dropdown-menu-end my-drp-left-0">
                                        <li class="${dropdownItemDisabled}"><a class="dropdown-item text-dark cancel-appointment" href="#" data-id="${appointment.id}">لغو نوبت</a></li>
                                        <li class="${dropdownItemDisabled}"><a class="dropdown-item text-dark move-appointment" data-date="${appointment.appointment_date}" href="#" data-id="${appointment.id}">جابجایی نوبت</a></li>
                                        <li><a class="dropdown-item text-dark block-user" href="#" data-id="${appointment.id}" data-mobile="${appointment.patient.mobile}" data-user-id="${appointment.patient.id}" data-user-name="${appointment.patient.first_name + ' ' + appointment.patient.last_name}">مسدود کردن کاربر</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>`;
              appointmentsTableBody.append(appointmentHTML);
            });
          } else if (!isInitialLoad) {
            appointmentsTableBody.html(`
                    <tr>
                        <td colspan="10" class="text-center py-3 text-muted">
                            هیچ نتیجه‌ای یافت نشد.
                        </td>
                    </tr>
                `);
          }
          isInitialLoad = false;
        },
        error: function() {
          $(".table tbody").html(`
                <tr>
                    <td colspan="10" class="text-center py-3 text-danger">
                        خطا در دریافت اطلاعات
                    </td>
                </tr>
            `);
        }
      });
    }

    // 📌 **وقتی در اینپوت جستجو تایپ شد**
    $(".my-form-control").on("input", function() {
      let searchText = $(this).val().trim();

      searchPatients(searchText);
    });
    // 📌 **بارگذاری اولیه لیست نوبت‌های امروز**
    searchPatients("");
  });

  /*  manage appointment cansle reschedule blockusers */
  $(document).on("click", ".cancel-appointment", function(e) {
    e.preventDefault();

    let appointmentId = $(this).data("id"); // دریافت ID نوبت
    let row = $(this).closest("tr"); // گرفتن ردیف مربوط به نوبت

    Swal.fire({
      title: "آیا از لغو این نوبت اطمینان دارید؟",
      text: "این نوبت لغو شده اما حذف نخواهد شد.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      cancelButtonColor: "#3085d6",
      confirmButtonText: "بله، لغو شود",
      cancelButtonText: "انصراف"
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: updateStatusAppointmentUrl.replace(":id", appointmentId), // جایگزینی ID در URL
          type: "POST",
          data: {
            _token: $('meta[name="csrf-token"]').attr("content"), // ارسال توکن CSRF
            status: "cancelled",
            selectedClinicId: localStorage.getItem('selectedClinicId')
          },
          beforeSend: function() {
            Swal.fire({
              title: "در حال پردازش...",
              text: "لطفا منتظر بمانید",
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });
          },
          success: function(response) {
            Swal.fire({
              title: "موفقیت‌آمیز!", // ✅ عنوان درست شد
              text: response.message,
              icon: "success", // ✅ اینجا باید "success" باشد
              confirmButtonColor: "#3085d6"
            });

            // حذف ردیف از جدول (بدون حذف از دیتابیس)
            row.fadeOut(300, function() {
              row.remove();
            });
          },
          error: function() {
            Swal.fire({
              title: "خطا!",
              text: "مشکلی در ارتباط با سرور رخ داده است.",
              icon: "error", // ⛔ خطا در این حالت
              confirmButtonColor: "#d33"
            });
          }
        });
      }
    });
  });
  $(document).on("click", ".custom-dropdown-trigger", function(e) {
    e.preventDefault();
    e.stopPropagation();

    let dropdownMenu = $(this).siblings(".dropdown-menu");

    // بستن همه منوهای دیگر
    $(".dropdown-menu").not(dropdownMenu).removeClass("show");

    // نمایش یا مخفی کردن منوی مربوط به دکمه کلیک شده
    dropdownMenu.toggleClass("show");
  });
  $(document).on("click", function() {
    $(".dropdown-menu").removeClass("show").css({
      position: "",
      top: "",
      left: ""
    });
  });
</script>
<script>
  $(document).on('click', '.move-appointment', function() {
    let appointmentId = $(this).data('id'); // دریافت ID نوبت
    let oldDate = $(this).data('date'); // دریافت تاریخ نوبت از data-date

    if (!appointmentId || !oldDate) {
      Swal.fire("خطا", "امکان دریافت اطلاعات نوبت وجود ندارد.", "error");
      return;
    }
    // مقدار را در مودال ذخیره کن
    $("#rescheduleModal").attr("data-appointment-id", appointmentId);
    $("#rescheduleModal").attr("data-old-date", oldDate);

    // مقداردهی لیست سال و ماه
    let year = moment(oldDate, 'YYYY-MM-DD').jYear();
    let month = moment(oldDate, 'YYYY-MM-DD').jMonth() + 1;

    // نمایش مودال و تولید تقویم
    $('#rescheduleModal').modal('show');
    generateRescheduleCalendar(year, month);
    populateRescheduleSelectBoxes();
  });
</script>
@endsection

<script>
  let selectedDay = null;


  function updateWorkhours() {
    let selectedDate = $("#selectedDate").val();

    let workHours = [];

    $(".work-hour-slot").each(function() {
      let start = $(this).find(".work-start-time").val();
      let end = $(this).find(".work-end-time").val();
      let maxAppointments = $(this).find(".work-max-appointments").val();

      workHours.push({
        start,
        end,
        max_appointments: maxAppointments
      });
    });

    $.ajax({
      url: "{{ route('doctor.update_work_schedule') }}",
      method: "POST",
      data: {
        date: $("#selectedDate").val(),
        work_hours: JSON.stringify(workHours),
        _token: $("meta[name='csrf-token']").attr("content"),
        selectedClinicId: localStorage.getItem('selectedClinicId')
      },
      success: function(response) {
        if (response.status) {
          Swal.fire("موفقیت", "ساعات کاری بروزرسانی شد.", "success");
        } else {
          Swal.fire("خطا", "بروزرسانی انجام نشد!", "error");
        }
      },
      error: function() {
        Swal.fire("خطا", "مشکلی در ذخیره تغییرات وجود دارد.", "error");
      }
    });

  }

  function attachDayClickEvents() {
    $('.calendar-day').not('.empty').off('click').on('click', function() {
      const selectedDayElement = $(this);
      const persianDate = selectedDayElement.data('date');
      const gregorianDate = moment(persianDate, 'jYYYY-jMM-jDD').format('YYYY-MM-DD');

      // پاک کردن محتوای قبلی مودال
      $('#dateModal').find('.modal-body').html(
        '<div class="text-center py-3"><span>در حال بارگذاری...</span></div>');
      $('#dateModal').data('selectedDayElement', selectedDayElement);
      $('#dateModal').data('selectedDate', gregorianDate);

      $.ajax({
        url: "{{ route('doctor.get_holiday_status') }}",
        method: 'POST',
        data: {
          date: gregorianDate,
          _token: '{{ csrf_token() }}'
        },
        success: function(response) {
          updateModalContent(response); // به‌روزرسانی محتوای مودال
        },
        error: function() {
          Swal.fire('خطا', 'مشکلی در ارتباط با سرور وجود دارد.', 'error');
        }
      });

      $('#dateModal').modal('show');
    });
  }


  function populateSelectBoxes() {
    const yearSelect = $('#year');
    const monthSelect = $('#month');
    const currentYear = moment().jYear();
    const currentMonth = moment().jMonth() + 1;
    for (let year = currentYear - 10; year <= currentYear + 10; year++) {
      yearSelect.append(new Option(year, year));
    }
    const persianMonths = ["فروردین", "اردیبهشت", "خرداد", "تیر", "مرداد", "شهریور", "مهر", "آبان", "آذر", "دی",
      "بهمن", "اسفند"
    ];
    for (let month = 1; month <= 12; month++) {
      monthSelect.append(new Option(persianMonths[month - 1], month));
    }
    yearSelect.val(currentYear);
    monthSelect.val(currentMonth);
    yearSelect.change(function() {
      generateCalendar(yearSelect.val(), monthSelect.val());
    });
    monthSelect.change(function() {
      generateCalendar(yearSelect.val(), monthSelect.val());
    });
  }

  function populateRescheduleSelectBoxes() {
    const yearSelect = $('#year-reschedule');
    const monthSelect = $('#month-reschedule');
    const currentYear = moment().jYear();
    const currentMonth = moment().jMonth() + 1;
    yearSelect.empty();
    monthSelect.empty();
    // پر کردن سال‌ها
    for (let year = currentYear - 10; year <= currentYear + 10; year++) {
      yearSelect.append(new Option(year, year));
    }
    // پر کردن ماه‌ها
    const persianMonths = ["فروردین", "اردیبهشت", "خرداد", "تیر", "مرداد", "شهریور", "مهر", "آبان", "آذر", "دی",
      "بهمن", "اسفند"
    ];
    for (let month = 1; month <= 12; month++) {
      monthSelect.append(new Option(persianMonths[month - 1], month));
    }
    yearSelect.val(currentYear);
    monthSelect.val(currentMonth);
    // تغییرات سال و ماه
    yearSelect.off('change').on('change', function() {
      generateRescheduleCalendar(yearSelect.val(), monthSelect.val());
    });
    monthSelect.off('change').on('change', function() {
      generateRescheduleCalendar(yearSelect.val(), monthSelect.val());
    });
  }

  function generateRescheduleCalendar(year, month) {
    const rescheduleCalendarBody = $('#calendar-reschedule');
    rescheduleCalendarBody.empty();

    const today = moment().startOf('day').locale('fa');
    const firstDayOfMonth = moment(`${year}-${month}-01`, 'jYYYY-jMM-jDD').locale('fa').startOf('month');
    const daysInMonth = firstDayOfMonth.jDaysInMonth();
    const firstDayWeekday = firstDayOfMonth.weekday();

    // افزودن روزهای خالی
    for (let i = 0; i < firstDayWeekday; i++) {
      rescheduleCalendarBody.append('<div class="calendar-day empty"></div>');
    }

    // ایجاد روزهای ماه
    for (let day = 1; day <= daysInMonth; day++) {
      const currentDay = firstDayOfMonth.clone().add(day - 1, 'days');
      const isToday = currentDay.isSame(today, 'day');
      const dayClass = `calendar-day ${isToday ? 'active' : ''}`;
      rescheduleCalendarBody.append(`
            <div class="${dayClass} position-relative" data-date="${currentDay.format('jYYYY-jMM-jDD')}">
                <span>${currentDay.format('jD')}</span>
            </div>
        `);
    }

    attachRescheduleDayClickEvents();
  }

  function attachRescheduleDayClickEvents() {
    $('#calendar-reschedule .calendar-day').not('.empty').click(function() {
      const selectedDate = $(this).data('date');
      const gregorianDate = moment(selectedDate, 'jYYYY-jMM-jDD').format('YYYY-MM-DD');
      const today = moment().format('YYYY-MM-DD');
      const isHoliday = $(this).hasClass('holiday');

      $('#calendar-reschedule .calendar-day').removeClass('active');
      $(this).addClass('active');

      const hasAppointment = $(this).find('.my-badge-success').length > 0;

      if (gregorianDate < today) {
        Swal.fire('خطا', 'نمی‌توانید نوبت‌ها را به تاریخ‌های گذشته منتقل کنید.', 'error');
      } else if (isHoliday) {
        Swal.fire('خطا', 'این روز تعطیل است و امکان جابجایی نوبت به این روز وجود ندارد.', 'error');
      } else if (hasAppointment) {
        Swal.fire('خطا', 'این روز دارای نوبت فعال است و امکان جابجایی نوبت به این روز وجود ندارد.', 'error');
      } else {
        Swal.fire({
          title: 'تایید جابجایی نوبت',
          text: `آیا می‌خواهید نوبت‌ها به تاریخ ${moment(selectedDate, 'jYYYY-jMM-jDD').locale('fa').format('jD jMMMM jYYYY')} منتقل شوند؟`,
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'بله، جابجا کن',
          cancelButtonText: 'لغو',
        }).then((result) => {
          if (result.isConfirmed) {
            let oldDate = $('#dateModal').data('selectedDate'); // مقدار از `dateModal`

            if (!oldDate) {
              // اگر `dateModal` مقدار نداشت، از `rescheduleModal` بگیر
              oldDate = $("#rescheduleModal").data("old-date");
            }

            if (!oldDate) {
              Swal.fire("خطا", "تاریخ نوبت قبلی یافت نشد!", "error");
              return;
            }

            $.ajax({
              url: "{{ route('doctor.reschedule_appointment') }}",
              method: 'POST',
              data: {
                old_date: oldDate,
                new_date: gregorianDate,
                _token: '{{ csrf_token() }}',
              },
              success: function(response) {
                if (response.status) {
                  Swal.fire('موفقیت', response.message, 'success');
                  loadAppointmentsCount(); // بروزرسانی نوبت‌ها
                  loadHolidayStyles(); // بروزرسانی استایل تعطیلات
                } else {
                  Swal.fire('خطا', response.message, 'error');
                }
              },
              error: function(xhr) {
                let errorMessage = 'مشکلی در ارتباط با سرور رخ داده است.';
                if (xhr.status === 400 && xhr.responseJSON && xhr.responseJSON.message) {
                  errorMessage = xhr.responseJSON.message; // دریافت پیام خطای سرور
                }

                Swal.fire('خطا', errorMessage, 'error');
              }
            });
          }
        });
      }
    });

  }


  const appointmentsCountUrl = "{{ route('appointments.count') }}";

  function loadAppointmentsCount() {
    $.ajax({
      url: "{{ route('appointments.count') }}",
      method: 'GET',
      data: {
        selectedClinicId: localStorage.getItem('selectedClinicId')
      },
      success: function(response) {
        if (response.status) {
          $('.calendar-day').each(function() {
            const persianDate = $(this).data('date');
            const gregorianDate = moment(persianDate, 'jYYYY-jMM-jDD').format('YYYY-MM-DD');
            const appointment = response.data.find(a => a.appointment_date === gregorianDate);
            $(this).find('.my-badge-success').remove();

          });
        }
      }
    });
  }

  function generateRescheduleCalendar(year, month) {
    const rescheduleCalendarBody = $('#calendar-reschedule');
    rescheduleCalendarBody.empty();
    const today = moment().startOf('day').locale('fa');
    const firstDayOfMonth = moment(`${year}-${month}-01`, 'jYYYY-jMM-jDD').locale('fa').startOf('month');
    const daysInMonth = firstDayOfMonth.jDaysInMonth();
    const firstDayWeekday = firstDayOfMonth.weekday();
    // افزودن روزهای خالی اول ماه
    for (let i = 0; i < firstDayWeekday; i++) {
      rescheduleCalendarBody.append('<div class="calendar-day empty"></div>');
    }
    // ایجاد روزهای ماه
    for (let day = 1; day <= daysInMonth; day++) {
      const currentDay = firstDayOfMonth.clone().add(day - 1, 'days');
      const isToday = currentDay.isSame(today, 'day');
      const dayClass = `calendar-day ${isToday ? 'active' : ''}`;
      const dayElement = `
            <div class="${dayClass} position-relative" data-date="${currentDay.format('jYYYY-jMM-jDD')}">
                <span>${currentDay.format('jD')}</span>
            </div>`;
      rescheduleCalendarBody.append(dayElement);
    }
    loadAppointmentsCountInReschedule();
    loadHolidayStylesInReschedule();
    attachRescheduleDayClickEvents();
  }

  function loadAppointmentsCountInReschedule() {
    $.ajax({
      url: "{{ route('appointments.count') }}",
      method: 'GET',
      data: {
        selectedClinicId: localStorage.getItem('selectedClinicId')
      },
      success: function(response) {
        if (response.status) {
          $('#calendar-reschedule .calendar-day').each(function() {
            const persianDate = $(this).data('date');
            const gregorianDate = moment(persianDate, 'jYYYY-jMM-jDD').format('YYYY-MM-DD');
            const appointment = response.data.find(a => a.appointment_date === gregorianDate);
            // حذف استایل قبلی
            $(this).find('.my-badge-success').remove();
            if (appointment) {
              $(this).append(`<span class="my-badge-success">${appointment.appointment_count}</span>`);
            }
          });
        }
      }
    });
  }

  function loadHolidayStylesInReschedule() {
    $.ajax({
      url: "{{ route('doctor.get_holidays') }}",
      method: 'GET',
      data: {
        selectedClinicId: localStorage.getItem('selectedClinicId')
      },
      success: function(response) {
        if (response.status) {
          const holidays = response.holidays;
          $('#calendar-reschedule .calendar-day').each(function() {
            const persianDate = $(this).data('date');
            const gregorianDate = moment(persianDate, 'jYYYY-jMM-jDD').format('YYYY-MM-DD');
            if (holidays.includes(gregorianDate)) {
              $(this).addClass('holiday');
            } else {
              $(this).removeClass('holiday');
            }
          });
        }
      }
    });
  }

  function updateModalContent(response) {
    // ابتدا پاک کردن محتوای مودال
    const modalBody = $('#dateModal .modal-body');
    modalBody.empty();

    if (!response || !response.status) {
      modalBody.html('<div class="alert alert-danger">خطایی در دریافت اطلاعات رخ داده است.</div>');
      return;
    }

    // بررسی وضعیت تعطیلی
    if (response.is_holiday) {
      modalBody.html(`
            <div class="alert alert-info">
                این روز تعطیل است. آیا می‌خواهید آن را از حالت تعطیلی خارج کنید؟
            </div>
            <div class="d-flex justify-content-between mt-3 gap-4">
                <button id="confirmUnHolidayButton" class="btn btn-primary h-50 w-100 me-2">بله</button>
                <button class="btn btn-danger h-50 w-100 close-modal" data-bs-dismiss="modal" aria-label="Close">خیر</button>
            </div>
        `);
    }
    // بررسی وجود نوبت
    else if (response.data && response.data.length > 0) {
      modalBody.html(`
            <div class="alert alert-info">
                شما برای این روز نوبت فعال دارید.
            </div>
           <div id="workHoursContainer">
            </div>
            <button id="updateWorkHours" onclick="updateWorkhours()" class="btn btn-primary w-100 h-50 mt-3" style="display: none;">
              بروزرسانی ساعات کاری
             </button>
            <div class="d-flex justify-content-between mt-3 gap-4">
                <button class="btn btn-danger h-50 w-100 close-modal me-2 cancle-btn-appointment">لغو نوبت‌ها</button>
                <button class="btn btn-secondary w-100 btn-reschedule h-50">جابجایی نوبت‌ها</button>
            </div>
        `);
    }
    // روز بدون نوبت و بدون تعطیلی
    else {
      modalBody.html(`
            <div class="alert alert-info">
                شما برای این روز نوبت فعالی ندارید. آیا می‌خواهید این روز را تعطیل کنید؟
            </div>
               <div id="workHoursContainer">

            </div>
            <button id="updateWorkHours" onclick="updateWorkhours()" class="btn btn-primary w-100 h-50 mt-3" style="display: none;">
              بروزرسانی ساعات کاری
             </button>
            <div class="d-flex justify-content-between mt-3 gap-4">
                <button id="confirmHolidayButton" class="btn btn-primary h-50 w-100 me-2">بله</button>
               <button class="btn btn-danger h-50 w-100 close-modal" data-bs-dismiss="modal" aria-label="Close">خیر</button>
            </div>
        `);
    }
  }

  const toggleHolidayUrl = "{{ route('doctor.toggle_holiday') }}";
  const getHolidaysUrl = "{{ route('doctor.get_holidays') }}";
  // بارگذاری استایل روزهای تعطیل هنگام لود صفحه
  function loadHolidayStyles() {
    $.ajax({
      url: "{{ route('doctor.get_holidays') }}",
      method: 'GET',
      data: {
        selectedClinicId: localStorage.getItem('selectedClinicId')
      },
      success: function(response) {
        if (response.status) {
          const holidays = response.holidays;
          $('.calendar-day').each(function() {
            const persianDate = $(this).data('date');
            const gregorianDate = moment(persianDate, 'jYYYY-jMM-jDD').format('YYYY-MM-DD');
            if (holidays.includes(gregorianDate)) {
              $(this).addClass('holiday');
            } else {
              $(this).removeClass('holiday');
            }
          });
        }
      }
    });
  }

  function findNextAvailableAppointment() {
    $.ajax({
      url: "{{ route('doctor.get_next_available_date') }}",
      method: 'GET',
      data: {
        selectedClinicId: localStorage.getItem('selectedClinicId')
      },

      success: function(response) {
        if (response.status) {
          const nextAvailableDate = response.date;

          Swal.fire({
            title: 'اولین نوبت خالی',
            html: `آیا می‌خواهید به تاریخ ${moment(nextAvailableDate, 'YYYY-MM-DD').locale('fa').format('jD jMMMM jYYYY')} منتقل شوید؟`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'بله',
            cancelButtonText: 'خیر'
          }).then((result) => {
            if (result.isConfirmed) {
              // آپدیت تاریخ اولین نوبت در دیتابیس
              $.ajax({
                url: "{{ route('doctor.update_first_available_appointment') }}",
                method: 'POST',
                data: {
                  date: nextAvailableDate,
                  _token: '{{ csrf_token() }}',
                  selectedClinicId: localStorage.getItem('selectedClinicId')
                },
                success: function(updateResponse) {
                  if (updateResponse.status) {
                    Swal.fire({
                      title: 'موفقیت',
                      text: `نوبت به تاریخ ${moment(nextAvailableDate, 'YYYY-MM-DD').locale('fa').format('jD jMMMM jYYYY')} منتقل شد.`,
                      icon: 'success'
                    });

                    // بروزرسانی تقویم
                    loadAppointmentsCount();
                    loadHolidayStyles();
                  } else {
                    Swal.fire('خطا', updateResponse.message, 'error');
                  }
                },
                error: function(xhr) {

                  Swal.fire('خطا', 'مشکلی در ارتباط با سرور وجود دارد.', 'error');
                }
              });
            }
          });
        } else {
          Swal.fire('اطلاع', response.message, 'info');
        }
      },
      error: function() {
        Swal.fire('خطا', 'مشکلی در ارتباط با سرور وجود دارد.', 'error');
      }
    });
  }


  // اضافه کردن event listener به دکمه
  function getWorkHours(selectedDate) {
    $.ajax({
      url: "{{ route('doctor.get_default_schedule') }}",
      method: "GET",
      data: {
        date: selectedDate,
        selectedClinicId: localStorage.getItem('selectedClinicId')
      },
      success: function(response) {
        $("#workHoursContainer").empty();

        if (response.status && response.work_hours.length > 0) {
          response.work_hours.forEach((slot, index) => {
            $("#workHoursContainer").append(`
                        <h6 class="font-weight-bold">برنامه کاری</h6>
                        <div class="p-3 border mt-2">
                          <input type="hidden" id="selectedDate" value="${selectedDate}">

                            <div class="work-hour-slot d-flex justify-content-center gap-4">
                                <div class="position-relative">
                                    <label class="label-top-input-special-takhasos">شروع:</label>
                                    <input type="text" class="form-control h-50 work-start-time" value="${slot.start}" data-index="${index}" />
                                </div>
                                <div class="position-relative">
                                    <label class="label-top-input-special-takhasos">پایان:</label>
                                    <input type="text" class="form-control h-50 work-end-time" value="${slot.end}" data-index="${index}" />
                                </div>
                                <div class="position-relative">
                                    <label class="label-top-input-special-takhasos">حداکثر نوبت:</label>
                                    <input type="number" class="form-control h-50 work-max-appointments" value="${slot.max_appointments}" data-index="${index}" />
                                </div>
                            </div>
                        </div>
                    `);
          });

          $("#updateWorkHours").show();
        } else {
          $("#workHoursContainer").append(
            `<p class="text-center text-danger font-weight-bold">هیچ ساعات کاری برای این روز تعریف نشده است.</p>`
          );
          $("#updateWorkHours").hide();
        }
      },
      error: function() {
        Swal.fire("خطا", "مشکلی در دریافت ساعات کاری وجود دارد.", "error");
      }
    });
  }

  $('#goToFirstAvailableDashboard').on('click', function() {
    $.ajax({
      url: "{{ route('doctor.get_next_available_date') }}",
      method: 'GET',
      data: {
        selectedClinicId: localStorage.getItem('selectedClinicId')
      },
      success: function(response) {
        if (response.status) {
          const nextAvailableDate = response.date;
          let oldDates = [];

          // دریافت تاریخ‌ها از آرایه selectedAppointments
          let selected = getSelectedAppointments();
          if (selected.length > 0) {
            oldDates = [...new Set(selected.map(item => item.date))];
          } else {
            let oldDate = $('#dateModal').data('selectedDate') || $("#rescheduleModal").data("old-date");
            if (oldDate) oldDates.push(oldDate);
          }

          Swal.fire({
            title: `اولین نوبت خالی (${moment(nextAvailableDate, 'YYYY-MM-DD').locale('fa').format('jD jMMMM jYYYY')})`,
            text: `آیا می‌خواهید نوبت‌ها از تاریخ(های) ${oldDates.map(date => moment(date, 'YYYY-MM-DD').locale('fa').format('jD jMMMM jYYYY')).join(', ')} به تاریخ ${moment(nextAvailableDate, 'YYYY-MM-DD').locale('fa').format('jD jMMMM jYYYY')} منتقل شوند؟`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'بله، جابجا کن',
            cancelButtonText: 'لغو',
          }).then((result) => {
            if (result.isConfirmed) {
              oldDates.forEach(oldDate => {
                $.ajax({
                  url: "{{ route('doctor.update_first_available_appointment') }}",
                  method: 'POST',


                  data: {
                    old_date: oldDate,
                    new_date: nextAvailableDate,
                    _token: '{{ csrf_token() }}',
                    selectedClinicId: localStorage.getItem('selectedClinicId')
                  },
                  success: function(updateResponse) {
                    if (updateResponse.status) {
                      Swal.fire('موفقیت', updateResponse.message, 'success');
                      loadAppointmentsCount();
                      loadHolidayStyles();
                    }
                  },
                  error: function(xhr) {
                    Swal.fire('خطا', xhr.responseJSON.message, 'error');
                  },
                });
              });
            }
          });
        } else {
          Swal.fire('اطلاع', response.message, 'info');
        }
      },
      error: function() {
        Swal.fire('خطا', 'مشکلی در ارتباط با سرور وجود دارد.', 'error');
      },
    });
  });

  $(document).ready(function() {

    loadAppointmentsCount();
    $('#prev-month').click(function() {
      const yearSelect = $('#year');
      const monthSelect = $('#month');
      const currentMonth = parseInt(monthSelect.val());
      if (currentMonth === 1) {
        yearSelect.val(parseInt(yearSelect.val()) - 1).change();
        monthSelect.val(12).change();
      } else {
        monthSelect.val(currentMonth - 1).change();
      }
    });
    $('#next-month').click(function() {
      const yearSelect = $('#year');
      const monthSelect = $('#month');
      const currentMonth = parseInt(monthSelect.val());
      if (currentMonth === 12) {
        yearSelect.val(parseInt(yearSelect.val()) + 1).change();
        monthSelect.val(1).change();
      } else {
        monthSelect.val(currentMonth + 1).change();
      }
    });
    populateSelectBoxes();
    $(document).on('click', '.cancle-btn-appointment', function() {
      const selectedDate = $('#dateModal').data('selectedDate');
      Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: "تمام نوبت‌های این روز لغو خواهند شد.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'بله، لغو کن!',
        cancelButtonText: 'لغو'
      }).then(result => {
        if (result.isConfirmed) {
          $.ajax({
            url: "{{ route('doctor.cancel_appointments') }}",
            method: 'POST',
            data: {
              date: selectedDate,
              _token: '{{ csrf_token() }}',
              selectedClinicId: localStorage.getItem('selectedClinicId')
            },
            success: function(response) {
              if (response.status) {
                Swal.fire('موفقیت', response.message, 'success');
                $('#dateModal').modal('hide'); // بستن مودال
                loadAppointmentsCount(); // بروزرسانی تقویم
              } else {
                Swal.fire('خطا', response.message, 'error');
              }
            },
            error: function() {
              Swal.fire('خطا', 'مشکلی در ارتباط با سرور وجود دارد.', 'error');
            }
          });
        }
      });
    });

    // Modal for Appointment Reschedule
    $(document).on('click', '#confirmReschedule', function() {
      const oldDate = $('#dateModal').data('selectedDate');
      const newDate = $('#calendar-reschedule .calendar-day.active').data('date');

      if (!newDate) {
        Swal.fire('خطا', 'لطفاً یک روز جدید انتخاب کنید.', 'error');
        return;
      }

      $.ajax({
        url: "{{ route('doctor.reschedule_appointment') }}",
        method: 'POST',
        data: {
          old_date: oldDate,
          new_date: moment(newDate, 'jYYYY-jMM-jDD').format('YYYY-MM-DD'),
          _token: '{{ csrf_token() }}',
          selectedClinicId: localStorage.getItem('selectedClinicId')
        },
        success: function(response) {
          if (response.status) {
            Swal.fire('موفقیت', response.message, 'success');
            $('#rescheduleModal').modal('hide');
            loadAppointmentsCount(); // بروزرسانی نوبت‌ها
            loadHolidayStyles(); // بروزرسانی استایل تعطیلات
          } else {
            Swal.fire('خطا', response.message, 'error');
          }
        },
        error: function() {
          Swal.fire('خطا', 'مشکلی در ارتباط با سرور وجود دارد.', 'error');
        }
      });
    });

    $(document).on('click', '.btn-reschedule', function() {
      const selectedDate = $('#dateModal').data('selectedDate');
      $('#rescheduleModal').modal('show'); // باز کردن مودال جابجایی نوبت‌ها

      // تولید تقویم برای جابجایی
      const year = moment(selectedDate, 'YYYY-MM-DD').jYear();
      const month = moment(selectedDate, 'YYYY-MM-DD').jMonth() + 1;

      generateRescheduleCalendar(year, month);
      populateRescheduleSelectBoxes();
    });

    $('.btn-reschedule').on('click', function() {


      $('#rescheduleModal').modal('show');
      const selectedDate = $('#dateModal').data('selectedDate');
      const year = moment(selectedDate, 'YYYY-MM-DD').jYear();
      const month = moment(selectedDate, 'YYYY-MM-DD').jMonth() + 1;
      generateRescheduleCalendar(year, month);
      populateRescheduleSelectBoxes();
      // اضافه کردن رویداد کلیک به روزهای تقویم جابجایی
      attachRescheduleDayClickEvents();
      // تولید تقویم جابجایی با همان داده‌های اصلی
      generateCalendar(year, month);
      // اضافه کردن رویداد کلیک برای روزهای تقویم جابجایی
      $('#calendar-reschedule .calendar-day').not('.empty').click(function() {
        const targetDate = $(this).data('date');
        const isHoliday = $(this).hasClass('holiday');
        const hasAppointment = $(this).find('.my-badge-success').length > 0;
        if (isHoliday) {
          Swal.fire('اخطار', 'نمی‌توانید نوبت‌ها را به یک روز تعطیل منتقل کنید.', 'error');
        } else if (hasAppointment) {
          Swal.fire('اخطار', 'برای این روز نوبت فعال دارید. نمی‌توانید نوبت‌ها را جابجا کنید.', 'error');
        } else {
          Swal.fire({
            title: 'تأیید جابجایی',
            text: `آیا می‌خواهید نوبت‌ها را به تاریخ ${moment(targetDate, 'jYYYY-jMM-jDD').locale('fa').format('jD jMMMM jYYYY')} منتقل کنید؟`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'بله',
            cancelButtonText: 'خیر',
          }).then(result => {
            if (result.isConfirmed) {
              // ارسال درخواست برای جابجایی نوبت
              const oldDate = $('#dateModal').data('selectedDate');
              $.ajax({
                url: "{{ route('doctor.reschedule_appointment') }}",
                method: 'POST',
                data: {
                  old_date: selectedDate,
                  new_date: moment(targetDate, 'jYYYY-jMM-jDD').format(
                    'YYYY-MM-DD'), // تبدیل به فرمت میلادی
                  _token: '{{ csrf_token() }}',
                  selectedClinicId: localStorage.getItem('selectedClinicId')
                },
                success: function(response) {
                  if (response.status) {
                    Swal.fire('موفقیت', 'نوبت‌ها با موفقیت جابجا شدند.', 'success');
                    $('#rescheduleModal').modal('hide');
                    // به‌روزرسانی تقویم اصلی
                    generateCalendar(moment().jYear(), moment().jMonth() + 1);
                    loadAppointmentsCount(); // بروزرسانی نوبت‌ها
                    loadHolidayStyles(); // بروزرسانی استایل تعطیلات
                  } else {
                    Swal.fire('خطا', response.message, 'error');
                  }
                },
                error: function(xhr) {
                  // پیام خطای سفارشی
                  let errorMessage = 'مشکلی در ارتباط با سرور رخ داده است.';

                  if (xhr.status === 400) {
                    // متن ثابت برای خطای 400
                    errorMessage = 'امکان جابجایی نوبت‌ها به گذشته وجود ندارد.';
                  }

                  // نمایش پیام خطا در سوئیت الرت
                  Swal.fire('خطا', errorMessage, 'error');
                }
              });
            }
          });
        }
      });
    });
    $('#prev-month-reschedule, #next-month-reschedule').off('click').on('click', function() {
      const yearSelect = $('#year-reschedule');
      const monthSelect = $('#month-reschedule');
      const currentMonth = parseInt(monthSelect.val());

      if (this.id === 'prev-month-reschedule' && currentMonth === 1) {
        yearSelect.val(parseInt(yearSelect.val()) - 1).change();
        monthSelect.val(12).change();
      } else if (this.id === 'next-month-reschedule' && currentMonth === 12) {
        yearSelect.val(parseInt(yearSelect.val()) + 1).change();
        monthSelect.val(1).change();
      } else {
        monthSelect.val(this.id === 'prev-month-reschedule' ? currentMonth - 1 : currentMonth + 1).change();
      }

      // همگام‌سازی سلکت باکس‌ها با تقویم
      const newMonth = parseInt(monthSelect.val());
      const newYear = parseInt(yearSelect.val());
      generateRescheduleCalendar(newYear, newMonth);

      // تنظیم مقدار انتخاب‌شده در سلکت باکس
      monthSelect.val(newMonth);
      yearSelect.val(newYear);
    });

    $('.calendar-day').not('.empty').on('click', function() {
      const selectedDayElement = $(this);
      const persianDate = selectedDayElement.data('date');
      const gregorianDate = moment(persianDate, 'jYYYY-jMM-jDD').format('YYYY-MM-DD');
      $('#dateModal').data('selectedDayElement', selectedDayElement);
      $('#dateModal').data('selectedDate', gregorianDate);
      $('#dateModalLabel').text(
        `نوبت‌های ${moment(persianDate, 'jYYYY-jMM-jDD').locale('fa').format('jD jMMMM jYYYY')}`
      );
      // پاک کردن محتوای قبلی
      $('.not-appointment').addClass('d-none');
      $('.having-nobat-for-this-day').addClass('d-none');
      $.ajax({
        url: "{{ route('doctor.get_holiday_status') }}",
        method: 'POST',
        data: {
          date: gregorianDate,
          _token: '{{ csrf_token() }}',
          selectedClinicId: localStorage.getItem('selectedClinicId')
        },
        success: function(response) {
          // حالت اول: روز تعطیل
          if (response.is_holiday) {
            $('.not-appointment').removeClass('d-none');
            $('.not-appointment .alert').html(`
            این روز قبلاً تعطیل شده است. 
            <div class="w-100 d-flex justify-content-between gap-4 mt-3">
              <div class="w-100">
                <button type="button" id="confirmUnHolidayButton" class="btn btn-primary h-50 w-100">بله</button>
              </div>
              <div class="w-100">
                <button type="button" class="btn btn-danger h-50 w-100 close-modal" data-dismiss="modal" aria-label="Close">خیر</button>
              </div>
            </div>
          `);
          }
          // حالت دوم: روز با نوبت فعال
          else if (response.data.length > 0) {
            $('.having-nobat-for-this-day').removeClass('d-none');
            // نمایش اطلاعات نوبت‌ها

            $('.having-nobat-for-this-day .alert').html(`
            پزشک گرامی شما برای این روز نوبت فعال دارید.
            <div class="w-100 d-flex justify-content-between gap-4 mt-3">
              <div class="w-100">
                <button class="btn btn-danger cancle-btn-appointment h-50 w-100">لغو نوبت ها</button>
              </div>
              <div class="w-100">
                <button class="btn btn-secondary btn-reschedule h-50 w-100">جابجایی نوبت ها</button>
              </div>
            </div>
          `);
          }
          // حالت سوم: روز بدون نوبت
          else {
            $('.not-appointment').removeClass('d-none');
            $('.not-appointment .alert').html(`
            پزشک گرامی شما برای این روز نوبت فعالی ندارید. 
            آیا می‌خواهید این روز را تعطیل کنید؟
            <div class="w-100 d-flex justify-content-between gap-4 mt-3">
              <div class="w-100">
                <button type="button" id="confirmHolidayButton" class="btn btn-primary h-50 w-100">بله</button>
              </div>
              <div class="w-100">
                <button type="button" class="btn btn-danger h-50 w-100 close-modal" data-dismiss="modal" aria-label="Close">خیر</button>
              </div>
            </div>
          `);
          }
          $(document).on('click', '.close-modal', function() {
            $('#dateModal').modal('hide');
          });

          // اضافه کردن event listener برای دکمه‌ها
          $(document).off('click', '#confirmHolidayButton, #confirmUnHolidayButton');
          $(document).on('click', '#confirmHolidayButton, #confirmUnHolidayButton', function() {
            const selectedDate = $('#dateModal').data('selectedDate');
            const selectedDayElement = $('#dateModal').data('selectedDayElement');

            $.ajax({
              url: "{{ route('doctor.toggle_holiday') }}",
              method: 'POST',
              data: {
                date: selectedDate,
                selectedClinicId: localStorage.getItem('selectedClinicId'),
                _token: '{{ csrf_token() }}'
              },
              success: function(response) {
                if (response.status) {
                  if (response.is_holiday) {
                    selectedDayElement.addClass('holiday');
                  } else {
                    selectedDayElement.removeClass('holiday');
                  }
                  $('#dateModal').modal('hide');
                  Swal.fire({
                    icon: 'success',
                    title: response.message,
                    confirmButtonText: 'باشه'
                  });
                } else {
                  Swal.fire('خطا', response.message, 'error');
                }
              },
              error: function() {
                Swal.fire('خطا', 'مشکلی در ارتباط با سرور رخ داده است.', 'error');
              }
            });
          });

        },
        error: function() {
          Swal.fire('خطا', 'مشکلی در ارتباط با سرور وجود دارد.', 'error');
        }
      });
      $('#dateModal').modal('show');
    });

    // تابع برای بروزرسانی محتوای مودال
    // فراخوانی هنگام بارگذاری صفحه
    loadHolidayStyles();
  });
  $(document).ready(function() {
    $(".calendar-day").on("click", function() {
      let persianDate = $(this).data("date"); // دریافت تاریخ شمسی
      let gregorianDate = moment(persianDate, 'jYYYY-jMM-jDD').format('YYYY-MM-DD'); // تبدیل به میلادی
      $("#selectedDate").val(gregorianDate); // ذخیره تاریخ میلادی در فیلد مخفی
      $("#selectedDate").val(gregorianDate); // ذخیره تاریخ میلادی در فیلد مخفی
      handleDateSelection(persianDate, localStorage.getItem('selectedClinicId'));
      // بررسی تعطیل بودن روز
      $.ajax({
        url: "{{ route('doctor.get_holiday_status') }}",
        method: "POST",
        data: {
          date: gregorianDate,
          selectedClinicId: localStorage.getItem('selectedClinicId'),
          _token: '{{ csrf_token() }}'
        },
        success: function(response) {
          if (response.is_holiday) {
            // اگر روز تعطیل بود، فقط پیام تعطیلی را نمایش بدهد
            $(".not-appointment").removeClass("d-none");
            $(".having-nobat-for-this-day").addClass("d-none");
            $("#workHoursContainer").empty(); // حذف ساعات کاری
            $("#updateWorkHours").hide();
          } else {
            // اگر روز تعطیل نبود، ساعات کاری را دریافت کند
            getWorkHours(gregorianDate);
          }
          $(".selectDate_datepicker__xkZeS span.mx-1").text(persianDate);
          $('#calendarModal').modal('hide'); // بستن مودال
          // اجرای جستجو با تاریخ جدید
          $('.my-form-control').val('')
        }
      });
    });


    $(document).on("click", ".block-user", function(e) {
      e.preventDefault();

      let row = $(this).closest("tr"); // گرفتن ردیف مربوطه
      let userId = $(this).data("user-id"); // دریافت ID کاربر
      let mobile = $(this).data("mobile"); // دریافت ID کاربر
      let userName = $(this).data("user-name"); // دریافت نام کاربر

      if (!userId) {
        Swal.fire("خطا!", "شناسه کاربر نامعتبر است.", "error");
        return;
      }

      Swal.fire({
        title: "مسدود کردن کاربر",
        text: `آیا مطمئن هستید که می‌خواهید کاربر "${userName}" را مسدود کنید؟`,
        icon: "warning",
        input: "textarea",
        inputPlaceholder: "لطفاً دلیل مسدودیت را وارد کنید...",
        showCancelButton: true,
        confirmButtonText: "بله، مسدود کن",
        cancelButtonText: "لغو",
        preConfirm: (reason) => {
          if (!reason) {
            Swal.showValidationMessage("لطفاً دلیل مسدودیت را وارد کنید.");
          }
          return reason;
        }
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "{{ route('doctor-blocking-users.store') }}",
            method: "POST",
            data: {
              _token: "{{ csrf_token() }}",
              user_id: userId,
              mobile: mobile,
              reason: result.value, // دلیل مسدودیت
              blocked_at: moment().format('YYYY-MM-DD'), // تاریخ شروع مسدودیت
              unblocked_at: null, // نامحدود تا زمان آزادسازی
            },
            beforeSend: function() {
              Swal.fire({
                title: "در حال پردازش...",
                text: "لطفاً صبر کنید",
                allowOutsideClick: false,
                didOpen: () => {
                  Swal.showLoading();
                }
              });
            },
            success: function(response) {
              Swal.fire("موفقیت!", response.message, "success");
            },
            error: function(xhr) {

              let errorMessage = "مشکلی در ارتباط با سرور رخ داده است.";
              if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
              }
              Swal.fire("خطا!", errorMessage, "error");
            }
          });
        }
      });
    });
  });
  $(document).ready(function() {
    $(document).ready(function() {
      $(".dropdown-item").click(function(e) {
        e.preventDefault();

        let statusFilter = ""; // مقدار فیلتر `status`
        let filterType = $(this).attr("id"); // دریافت ID گزینه‌ی انتخاب‌شده

        // بررسی مقدار `filterType` و تعیین مقدار `statusFilter`
        if (filterType === "scheduled-appointments") {
          statusFilter = "scheduled";
        } else if (filterType === "cancelled-appointments") {
          statusFilter = "cancelled";
        } else if (filterType === "attended-appointments") {
          statusFilter = "attended";
        } else if (filterType === "missed-appointments") {
          statusFilter = "missed";
        }

        // نمایش لودینگ در جدول
        let appointmentsTableBody = $(".table tbody");
        appointmentsTableBody.html(`
            <tr>
                <td colspan="10" class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">در حال بارگذاری...</span>
                    </div>
                </td>
            </tr>
        `);

        // ارسال درخواست AJAX برای دریافت نوبت‌های فیلتر شده
        $.ajax({
          url: "{{ route('doctor.appointments.filter') }}",
          method: "GET",
          data: {
            status: statusFilter,
            selectedClinicId: localStorage.getItem('selectedClinicId') // فقط مقدار `status` ارسال شود
          },
          success: function(response) {
            appointmentsTableBody.html("");

            if (response.appointments.length > 0) {
              response.appointments.forEach(function(appointment) {
                let patient = appointment.patient || {};
                let insurance = appointment.insurance ? appointment.insurance.name : 'ندارد';
                let appointmentDate = appointment.appointment_date ?
                  moment(appointment.appointment_date).locale('fa').format('jYYYY/jMM/jDD') :
                  'نامشخص';

                let appointmentHTML = `
                            <tr>
                             <td><input type="checkbox" class="row-checkbox"></td>
                                <td>${patient.first_name ? patient.first_name : 'نامشخص'} 
                                    ${patient.last_name ? patient.last_name : ''}</td>
                                <td>${patient.mobile ? patient.mobile : 'نامشخص'}</td>
                                <td>${patient.national_code ? patient.national_code : 'نامشخص'}</td>
                                <td>${getPrescriptionStatus(appointment.status)}</td>
        <td>${getPaymentStatus(appointment.payment_status)}</td>

                                <td>${insurance}</td>
                                <td>${appointmentDate}</td>
                                <td>${appointment.appointment_time}</td>
                                <td>
    <button class="btn btn-outline-info btn-end-visit" data-appointment-id="${appointment.id}"  ${
                (appointment.status === 'attended' || appointment.status === 'cancelled') ? 'disabled' : ''
            }>
        پایان ویزیت
    </button>
</td>
                                <td class="text-center">
                                    <div class="dropdown d-inline-block position-relative">
                                        <button class="btn btn-light p-1 btn-sm dropdown-toggle custom-dropdown-trigger" type="button">
                                            <img src="{{ asset('dr-assets/icons/dots-vertical-svgrepo-com.svg') }}" width="20" height="20">
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end my-drp-left-0">
                                            <li class="${
                (appointment.status === 'attended' || appointment.status === 'cancelled') ? 'disabled' : ''
            }" href="#" data-id="${appointment.id}"><a class="dropdown-item text-dark cancel-appointment">لغو نوبت</a></li>
                                            
                                            <li class=" ${
                (appointment.status === 'attended' || appointment.status === 'cancelled') ? 'disabled' : ''
            }"><a class="dropdown-item text-dark move-appointment" data-date="${appointment.appointment_date}" href="#" data-id="${appointment.id}">جابجایی نوبت</a></li>
                                            <li><a class="dropdown-item text-dark block-user" href="#" data-id="${appointment.id}" data-mobile="${appointment.patient.mobile}" data-user-id="${appointment.patient.id}" data-user-name="${appointment.patient.first_name + ' ' + appointment.patient.last_name }">مسدود کردن کاربر</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>`;

                appointmentsTableBody.append(appointmentHTML);
              });
            } else {
              appointmentsTableBody.html(`
                        <tr>
                            <td colspan="10" class="text-center py-3">هیچ نوبتی برای این فیلتر وجود ندارد.</td>
                        </tr>
                    `);
            }
          },
          error: function() {
            appointmentsTableBody.html(`
                    <tr>
                        <td colspan="10" class="text-center py-3 text-danger">خطا در دریافت نوبت‌ها.</td>
                    </tr>
                `);
          }
        });
      });
    });
  });

  function getSelectedAppointments() {
    let selected = [];
    let checkboxes = $('.row-checkbox:checked');

    checkboxes.each(function() {
      let checkbox = $(this);
      let row = checkbox.closest('tr');
      let appointmentId = row.find('.btn-end-visit').attr('data-appointment-id'); // ID از دکمه



      if (!appointmentId) {
        console.error('No data-appointment-id found in row or button:', row);
      }



      selected.push({
        id: appointmentId,
        status: row.find('td:nth-child(5)').text().trim(),
        date: row.find('td:nth-child(7)').text().trim(),
        mobile: row.find('td:nth-child(3)').text().trim(),
        row: row
      });
    });
    return selected;
  }

  $(document).ready(function() {
    const selectAllCheckbox = $('#select-all');
    const rowCheckboxes = $('.row-checkbox');
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    // ✅ انتخاب همه چک‌باکس‌ها
    $('#select-all').click(function(e) {
      e.stopPropagation();
      $('.row-checkbox').prop('checked', $(this).prop('checked'));
    });




    // ✅ لغو نوبت گروهی
    $('#cancel-appointments-btn').click(function() {
      let selected = getSelectedAppointments();


      if (!Array.isArray(selected) || !selected.length) {
        Swal.fire('هشدار', 'نوبتی انتخاب نشده!', 'warning');
        return; // اینجا return رو جدا گذاشتم برای وضوح
      }

      const hasAttended = selected.some(appointment => appointment.status === 'attended');
      if (hasAttended) {
        return Swal.fire('خطا', 'نمی‌توانید نوبت‌های ویزیت‌شده را لغو کنید!', 'error');
      }

      const date = selected[0].date;
      const appointmentIds = selected
        .map(app => app.id)
        .filter(id => id !== undefined && id !== null && Number.isInteger(Number(id)));

      if (!appointmentIds.length) {
        return Swal.fire('خطا', 'هیچ شناسه نوبتی معتبر انتخاب نشده است!', 'error');
      }

      Swal.fire({
        title: 'لغو نوبت‌ها؟',
        text: `${appointmentIds.length} نوبت لغو می‌شود.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'بله',
        cancelButtonText: 'لغو'
      }).then((result) => {
        if (result.isConfirmed) {
          let data = {
            _token: '{{ csrf_token() }}',
            date: date,
            selectedClinicId: localStorage.getItem('selectedClinicId'),
            appointment_ids: appointmentIds
          };
          $.ajax({
            url: "{{ route('doctor.cancel_appointments') }}",
            method: 'POST',
            data: data,
            success: function(response) {
              if (response.status) {
                Swal.fire('موفقیت', response.message, 'success');
                selected.forEach(app => {
                  app.row.find('td:nth-child(5)').html(
                    '<span class="font-weight-bold text-danger">لغو شده</span>');
                  app.row.find('.row-checkbox').prop('checked', false); // تیک رو بردار
                });
              } else {
                Swal.fire('خطا', response.message, 'error');
              }
            },
            error: function(xhr) {
              let errorMessage = xhr.responseJSON || xhr.responseJSON.message ?
                xhr.responseJSON.message :
                'مشکلی در لغو نوبت‌ها رخ داد.';
              Swal.fire('خطا', errorMessage, 'error');
            }
          });
        }
      });
    });

    // ✅ جابجایی نوبت گروهی با استفاده از مودال تقویم شما
    $('#move-appointments-btn').click(function() {
      let selected = getSelectedAppointments();
      if (!selected.length) {
        return Swal.fire('هشدار', 'نوبتی انتخاب نشده!', 'warning');
      }

      // چک کردن اینکه آیا نوبت ویزیت‌شده وجود داره
      const hasAttended = selected.some(appointment => appointment.status === 'attended');
      if (hasAttended) {
        return Swal.fire('خطا', 'نمی‌توانید نوبت‌های ویزیت‌شده را جابجا کنید!', 'error');
      }

      $('#rescheduleModal').modal('show');
      generateRescheduleCalendar(moment().jYear(), moment().jMonth() + 1);
      populateRescheduleSelectBoxes();

      $('#calendar-reschedule .calendar-day').not('.empty').off('click').on('click', function() {
        const newDate = $(this).data('date');
        const gregorianDate = moment(newDate, 'jYYYY-jMM-jDD').format('YYYY-MM-DD');
        const today = moment().format('YYYY-MM-DD');
        if (gregorianDate < today || $(this).hasClass('holiday') || $(this).find('.my-badge-success')
          .length > 0) {
          Swal.fire('خطا', 'امکان جابجایی نوبت به این تاریخ وجود ندارد.', 'error');
          return;
        }

        Swal.fire({
          title: `جابجایی نوبت‌ها به ${moment(newDate, 'jYYYY-jMM-jDD').locale('fa').format('jD jMMMM jYYYY')}؟`,
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'بله',
          cancelButtonText: 'لغو'
        }).then((result) => {
          if (result.isConfirmed) {
            let oldDates = selected.map(item => item.date);

            oldDates.forEach(oldDate => {
              $.ajax({
                url: "{{ route('doctor.reschedule_appointment') }}",
                method: 'POST',
                data: {
                  old_date: oldDate,
                  selectedClinicId: localStorage.getItem('selectedClinicId'),
                  new_date: gregorianDate,
                  _token: '{{ csrf_token() }}',
                },
                success: function(response) {
                  if (response.status) {
                    Swal.fire('موفقیت', response.message, 'success');
                    loadAppointmentsCount();
                    loadHolidayStyles();
                    selected.forEach(app => app.row.remove()); // حذف ردیف‌ها بعد از جابجایی
                  } else {
                    Swal.fire('خطا', response.message, 'error');
                  }
                },
                error: function(xhr) {
                  let errorMessage = 'مشکلی در ارتباط با سرور رخ داده است.';
                  if (xhr.status === 400 && xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                  }
                  Swal.fire('خطا', errorMessage, 'error');
                }
              });
            });
          }
        });
      });
    });


    // ✅ مسدود کردن گروهی کاربران
    $('#block-users-btn').click(function() {
      let selected = getSelectedAppointments();
      if (!selected.length) return Swal.fire('هشدار', 'کاربری انتخاب نشده!', 'warning');

      Swal.fire({
        title: 'مسدود کردن کاربران؟',
        input: 'textarea',
        inputPlaceholder: 'دلیل مسدودیت را وارد کنید...',
        showCancelButton: true,
        confirmButtonText: 'بله',
        cancelButtonText: 'لغو'
      }).then((result) => {
        if (result.isConfirmed) {
          let mobiles = selected.map(a => a.mobile);
          $.post("{{ route('doctor-blocking-users.store-multiple') }}", {
            _token: csrfToken,
            mobiles: mobiles,
            blocked_at: moment().format('YYYY-MM-DD'),
            reason: result.value,
            selectedClinicId: localStorage.getItem('selectedClinicId') // اضافه کردن برای هماهنگی
          }, function(response) {
            if (response.success) {
              Swal.fire('موفقیت', response.message, 'success');
              // اگه نیاز داری ردیف‌ها رو از جدول حذف کنی، اینجا کدش رو اضافه کن
            } else {
              Swal.fire('خطا', response.message, 'error');
            }
          }).fail(function(xhr) {

            Swal.fire('خطا', xhr.responseJSON.message, 'error');

          });
        }
      });
    });
  });
  $(document).ready(function() {
    $(document).on('click', '.btn-end-visit', function(e) {
      e.preventDefault();
      const appointmentId = $(this).data('appointment-id');
      $('#endVisitModalCenter').data('appointment-id', appointmentId);
      $('#endVisitModalCenter').modal('show');
    });

    $('#endVisitModalCenter .btn-primary').on('click', function(e) {
      e.preventDefault();

      const appointmentId = $('#endVisitModalCenter').data('appointment-id');
      const description = $('#endVisitModalCenter textarea').val();
      const csrfToken = $('meta[name="csrf-token"]').attr('content');

      $.ajax({
        url: "{{ route('doctor.end-visit', ':id') }}".replace(':id', appointmentId),
        method: 'POST',
        data: {
          _token: csrfToken,
          description: description
        },
        beforeSend: function() {
          Swal.fire({
            title: 'در حال پردازش...',
            allowOutsideClick: false,
            didOpen: () => {
              Swal.showLoading();
            }
          });
        },
        success: function(response) {
          Swal.close();
          if (response.success) {
            const endVisitButton = $(`.btn-end-visit[data-appointment-id="${appointmentId}"]`);
            if (endVisitButton.length === 0) {
              console.error('دکمه با این ID پیدا نشد:', appointmentId);
              Swal.fire('خطا', 'دکمه پایان ویزیت پیدا نشد.', 'error');
              return;
            }

            const row = endVisitButton.closest('tr');
            if (row.length === 0) {
              console.error('ردیف والد دکمه پیدا نشد:', appointmentId);
              Swal.fire('خطا', 'ردیف جدول پیدا نشد.', 'error');
              return;
            }

            // به‌روزرسانی وضعیت در جدول
            row.find('td:nth-child(5)').html(
              '<span class="font-weight-bold text-primary">ویزیت شده</span>');

            // غیرفعال کردن دکمه "پایان ویزیت"
            endVisitButton.prop('disabled', true).addClass('disabled');

            // غیرفعال کردن گزینه‌های "لغو نوبت" و "جابجایی نوبت" در دراپ‌داون
            row.find('.cancel-appointment').closest('li').addClass('disabled');
            row.find('.move-appointment').closest('li').addClass('disabled');

            // بستن مودال
            $('#endVisitModalCenter').modal('hide');

            // پاک کردن textarea
            $('#endVisitModalCenter textarea').val('');

            // نمایش پیام موفقیت
            Swal.fire('موفقیت', response.message, 'success');
          } else {
            Swal.fire('خطا', response.message, 'error');
          }
        },
        error: function(xhr) {
          Swal.close();
          Swal.fire('خطا', 'مشکلی در ارتباط با سرور رخ داد: ' + xhr.status, 'error');
          console.error('خطای AJAX:', xhr.responseText);
        }
      });
    });
  });
</script>

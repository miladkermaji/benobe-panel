<script>
  function showLoading() {
    appointmentsContainer.html(loadingIndicator);
  }
  document.addEventListener("DOMContentLoaded", function() {
    const calendarBody = document.getElementById("calendar-body");
    const today = moment().startOf("day").locale("fa").format("jYYYY/jMM/jDD");
    const selectedDateSpan = document.querySelector(
      ".turning_selectDate__MLRSb span:first-child"
    );
    const calendarButton = document.querySelector(
      ".selectDate_datepicker__xkZeS"
    );
    const miniCalendarModal = document.getElementById("miniCalendarModal");
    calendarButton.onclick = null;
    let modalInstance = null;
    calendarButton.removeEventListener("click", handleCalendarButtonClick);

    function handleCalendarButtonClick(e) {
      e.preventDefault();
      e.stopPropagation();

      if (!modalInstance) {
        modalInstance = new bootstrap.Modal(miniCalendarModal, {
          backdrop: "static",
          keyboard: false,
        });
      }

      const existingBackdrops = document.querySelectorAll(".modal-backdrop");
      existingBackdrops.forEach((backdrop) => backdrop.remove());
      document.body.classList.remove("modal-open");

      modalInstance.show();
    }

    calendarButton.addEventListener("click", handleCalendarButtonClick);
    selectedDateSpan.textContent = today;

    function generateCalendar(year, month) {
      calendarBody.innerHTML = "";

      const firstDayOfMonth = moment(
        `${year}/${month}/01`,
        "jYYYY/jMM/jDD"
      ).locale("fa");
      const daysInMonth = firstDayOfMonth.jDaysInMonth();
      let firstDayWeekday = firstDayOfMonth.weekday();
      const today = moment().locale("fa");

      for (let i = 0; i < firstDayWeekday; i++) {
        const emptyDay = document.createElement("div");
        emptyDay.classList.add("calendar-day", "empty");
        calendarBody.appendChild(emptyDay);
      }

      for (let day = 1; day <= daysInMonth; day++) {
        const currentDay = firstDayOfMonth.clone().add(day - 1, "days");
        const dayElement = document.createElement("div");

        dayElement.classList.add("calendar-day");
        dayElement.setAttribute(
          "data-date",
          currentDay.format("jYYYY/jMM/jDD")
        );

        if (currentDay.day() === 5) {
          dayElement.classList.add("friday");
        }
        if (currentDay.isSame(today, "day")) {
          dayElement.classList.add("active");
        }

        dayElement.innerHTML = `<span>${currentDay.format("jD")}</span>`;

        dayElement.addEventListener("click", function() {
          const selectedDate = this.getAttribute("data-date");
          if (modalInstance) {
            modalInstance.hide();
          }
          $("#miniCalendarModal").modal("hide");

          selectedDateSpan.textContent = selectedDate;

          setTimeout(() => {
            const existingBackdrops =
              document.querySelectorAll(".modal-backdrop");
            existingBackdrops.forEach((backdrop) => backdrop.remove());
            document.body.classList.remove("modal-open");
          }, 300);
        });

        calendarBody.appendChild(dayElement);
      }
    }

    function populateSelectBoxes() {
      const yearSelect = document.getElementById("year");
      const monthSelect = document.getElementById("month");

      const currentYear = moment().jYear();
      const currentMonth = moment().jMonth() + 1;

      yearSelect.innerHTML = "";
      for (let year = currentYear - 10; year <= currentYear + 10; year++) {
        const option = document.createElement("option");
        option.value = year;
        option.textContent = year;
        yearSelect.appendChild(option);
      }

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

      monthSelect.innerHTML = "";
      for (let month = 1; month <= 12; month++) {
        const option = document.createElement("option");
        option.value = month;
        option.textContent = persianMonths[month - 1];
        monthSelect.appendChild(option);
      }

      yearSelect.value = currentYear;
      monthSelect.value = currentMonth;

      yearSelect.addEventListener("change", function() {
        generateCalendar(
          parseInt(yearSelect.value),
          parseInt(monthSelect.value)
        );
      });

      monthSelect.addEventListener("change", function() {
        generateCalendar(
          parseInt(yearSelect.value),
          parseInt(monthSelect.value)
        );
      });
    }


    document
      .getElementById("prev-month")
      .addEventListener("click", function() {
        const yearSelect = document.getElementById("year");
        const monthSelect = document.getElementById("month");
        let currentMonth = parseInt(monthSelect.value);
        let currentYear = parseInt(yearSelect.value);

        if (currentMonth === 1) {
          currentYear -= 1;
          currentMonth = 12;
        } else {
          currentMonth -= 1;
        }

        yearSelect.value = currentYear;
        monthSelect.value = currentMonth;
        generateCalendar(currentYear, currentMonth);
      });

    // دکمه ماه بعد
    document
      .getElementById("next-month")
      .addEventListener("click", function() {
        const yearSelect = document.getElementById("year");
        const monthSelect = document.getElementById("month");
        let currentMonth = parseInt(monthSelect.value);
        let currentYear = parseInt(yearSelect.value);

        if (currentMonth === 12) {
          currentYear += 1;
          currentMonth = 1;
        } else {
          currentMonth += 1;
        }

        yearSelect.value = currentYear;
        monthSelect.value = currentMonth;
        generateCalendar(currentYear, currentMonth);
      });

    // اجرای اولیه
    populateSelectBoxes();
    generateCalendar(moment().jYear(), moment().jMonth() + 1);
  });


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

    // بررسی کلینیک‌های غیرفعال
    function checkInactiveClinics() {
      var hasInactiveClinics = $('.option-card[data-active="0"]').length > 0;
      if (hasInactiveClinics) {
        $('.dropdown-trigger').addClass('warning');
      } else {
        $('.dropdown-trigger').removeClass('warning');
      }
    }
    checkInactiveClinics();

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

    $(document).on('click', function() {
      if (dropdownOpen) {
        $('.dropdown-trigger').removeClass('border border-primary');
        $('.my-dropdown-menu').addClass('d-none');
        dropdownOpen = false;
      }
    });

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
      localStorage.setItem('selectedClinic', selectedText);
      localStorage.setItem('selectedClinicId', selectedId);
      loadAppointments(selectedId, $('.btn-filter-appointment-toggle').text().trim());
      checkInactiveClinics();
      handleDateSelection(persianDate, selectedId, $('.btn-filter-appointment-toggle').text().trim());
      $('.dropdown-trigger').removeClass('border border-primary');
      $('.my-dropdown-menu').addClass('d-none');
      dropdownOpen = false;
    });

    // لود اولیه نوبت‌ها
    loadAppointments(localStorage.getItem('selectedClinicId'), $('.btn-filter-appointment-toggle').text().trim());
  });
  let loadingIndicator = `<div id="loading-row" class="w-100">
    <div  class="text-center py-4">
        <div class="spinner-custom" role="status">
            <span class="visually-hidden">در حال بارگذاری...</span>
        </div>
    </div>
</div>`;


  // لغو نوبت
  $(document).on("click", ".cancel-appointment", function(e) {
    e.preventDefault();

    let appointmentId = $(this).data("id"); // دریافت ID نوبت
    let row = $(this).closest("tr"); // گرفتن ردیف (اگه از جدول استفاده می‌کنی)
    let card = $(this).closest('.my-appointments-lists-card'); // گرفتن کارت (اگه از کارت استفاده می‌کنی)

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
          url: "{{ route('doctor.cancel_appointments') }}", // استفاده از مسیر جدید
          type: "POST",
          data: {
            _token: $('meta[name="csrf-token"]').attr("content"),
            appointment_ids: [appointmentId], // ارسال ID به‌صورت آرایه
            date: $('#datepicker').text().trim(), // تاریخ انتخاب‌شده
            selectedClinicId: localStorage.getItem('selectedClinicId') // کلینیک انتخاب‌شده
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
            if (response.status) {
              Swal.fire({
                title: "موفقیت!",
                text: response.message,
                icon: "success",
                confirmButtonColor: "#3085d6"
              });
              // حذف ردیف یا کارت از UI
              if (row.length) {
                row.fadeOut(300, function() {
                  row.remove();
                });
              } else if (card.length) {
                card.fadeOut(300, function() {
                  card.remove();
                });
              }
              $("#userInfoModalCenter").modal('hide');
              // رفرش نوبت‌ها
              let currentDate = moment().format('YYYY-MM-DD');
              let persianDate = moment(currentDate, 'YYYY-MM-DD').locale('fa').format('jYYYY/jMM/jDD');
              handleDateSelection(persianDate, localStorage.getItem('selectedClinicId'), $(
                '.btn-filter-appointment-toggle').text().trim());
              loadAppointments(localStorage.getItem('selectedClinicId'), $(
                '.btn-filter-appointment-toggle').text().trim());
            } else {
              Swal.fire({
                title: "خطا!",
                text: response.message,
                icon: "error",
                confirmButtonColor: "#d33"
              });
            }
          },
          error: function(xhr) {
            Swal.fire({
              title: "خطا!",
              text: xhr.responseJSON?.message || "مشکلی در ارتباط با سرور رخ داده است.",
              icon: "error",
              confirmButtonColor: "#d33"
            });
          }
        });
      }
    });
  });

  // مدیریت دراپ‌داون فیلتر
  $(document).ready(function() {
    $('.btn-filter-appointment-toggle').on('click', function() {
      $(this).toggleClass('active');
      $('.appointments-filter-drop-toggle').toggleClass('show');
    });

    $(document).on('click', function(event) {
      if (!$(event.target).closest('.dropdown-container').length) {
        $('.btn-filter-appointment-toggle').removeClass('active');
        $('.appointments-filter-drop-toggle').removeClass('show');
      }
    });

    $('.appointments-filter-drop-toggle li:not(:last-child)').on('click', function() {
      $('.appointments-filter-drop-toggle li:not(:last-child)').removeClass('bg-light-blue');
      $(this).addClass('bg-light-blue');
    });
  });

  // پر کردن مودال اطلاعات بیمار
  $(document).ready(function() {
    $('#userInfoModalCenter').on('show.bs.modal', function(event) {
      var button = $(event.relatedTarget);
      var time = button.data('time');
      var id = button.data('id');
      var date = button.data('date');
      var fullname = button.data('fullname');
      var mobile = button.data('mobile');
      var tracking_code = button.data('tracking-code');
      var nationalCode = button.data('national-code');
      var paymentStatus = button.data('payment-status');
      var appointmentType = button.data('appointment-type');
      var centerName = button.data('center-name');
      var modal = $(this);

      modal.find('.time-card .text-black').text(time);
      modal.find('.date-card .text-black').text(date);
      modal.find('.fullname').text(fullname);
      modal.find('.mobile').text(mobile);
      modal.find('.national-code').text(nationalCode);
      modal.find('.payment-status').text(paymentStatus);
      modal.find('.appointment-type').text(appointmentType);
      modal.find('.center-name').text(centerName);
      modal.find('.tracking-code').text(tracking_code);
      modal.find('.cancel-appointment').attr('data-id', id);
    });
  });

  // تابع لود نوبت‌ها
  function loadAppointments(selectedClinicId, type) {
    let filterType = type === 'کل نوبت ها' ? '' : (type === 'نوبت های مطب' ? 'in_person' : 'online');
    $.ajax({
      type: "GET",
      url: "{{ route('dr-appointments') }}",
      data: {
        selectedClinicId: selectedClinicId,
        type: filterType
      },
      dataType: "json",
      success: function(response) {
        $('.my-appointments-lists-cards').empty();
        let appointmentHtml = '';
        if (response.appointments.length > 0) {
          response.appointments.forEach(function(appointment) {
            appointmentHtml += createAppointmentHTML(appointment);
          });
        } else {
          appointmentHtml = `
                    <div class="container-fluid h-50 d-flex justify-content-center align-items-center align-self-center">
                        <div class="text-center">
                            <p class="font-weight-bold">برای تاریخی که انتخاب کردید، در مرکز موردنظر هیچ نوبتی موجود نیست.</p>
                        </div>
                    </div>
                `;
        }
        $('.my-appointments-lists-cards').append(appointmentHtml);
      },
      error: function() {
        $('.my-appointments-lists-cards').empty().append(`
                <div class="container-fluid h-50 d-flex justify-content-center align-items-center align-self-center">
                    <div class="text-center">
                        <p class="font-weight-bold text-danger">خطا در بارگذاری نوبت‌ها. لطفا دوباره تلاش کنید.</p>
                    </div>
                </div>
            `);
      }
    });
  }

  // توابع کمکی برای تبدیل وضعیت‌ها
  function getPaymentStatus(status) {
    switch (status) {
      case 'pending':
        return 'درحال پرداخت';
      case 'paid':
        return 'پرداخت شده';
      case 'unpaid':
        return 'پرداخت نشده';
      default:
        return '';
    }
  }

  function getPaymentStatus(status) {
    switch (status) {
      case 'pending':
        return 'درحال پرداخت';
      case 'paid':
        return 'پرداخت شده';
      case 'unpaid':
        return 'پرداخت نشده';
      default:
        return '';
    }
  }

  function getAppointmentType(type) {
    switch (type) {
      case 'online':
        return 'آنلاین';
      case 'in_person':
        return 'حضوری';
      case 'phone':
        return 'تلفنی';
      default:
        return '';
    }
  }

  // تنظیم تاریخ اولیه
  const datepickerSpan = document.getElementById('datepicker');
  const appointmentsContainer = document.querySelector('.my-appointments-lists-cards');
  const initialDate = moment().locale('fa').format('jYYYY/jMM/jDD');
  datepickerSpan.textContent = initialDate;

  function handleDateSelection(selectedDate, selectedClinicId, filter_text) {
    console.log('Selected date:', selectedDate);
    console.log('Selected clinic ID:', selectedClinicId);
    console.log('Filter text:', filter_text);

    // آپدیت متن تاریخ توی صفحه
    $('#datepicker').text(selectedDate);

    // تبدیل فیلتر به نوع درست
    let filterType = filter_text === 'کل نوبت ها' ? '' : (filter_text === 'نوبت های مطب' ? 'in_person' : 'online');
    const appointmentsContainer = $('#appointment-lists-container');

    // پاک کردن UI و نمایش لودینگ
    appointmentsContainer.empty().html(loadingIndicator);

    $.ajax({
      url: "{{ route('dr.turn.my-appointments.by-date') }}",
      method: 'GET',
      data: {
        date: selectedDate,
        selectedClinicId: selectedClinicId,
        type: filterType
      },
      success: function(response) {
        console.log('Response from server:', response);
        appointmentsContainer.empty(); // مطمئن می‌شیم UI پاک بشه

        if (response.appointments && response.appointments.length > 0) {
          response.appointments.forEach(function(appointment) {
            const appointmentHTML = createAppointmentHTML(appointment);
            appointmentsContainer.append(appointmentHTML);
          });
        } else {
          appointmentsContainer.html(`
                    <div class="text-center w-100">
                        <p class="font-weight-bold">برای این تاریخ هیچ نوبتی یافت نشد.</p>
                    </div>
                `);
        }
      },
      error: function(xhr) {
        console.error('AJAX error:', xhr.responseText);
        appointmentsContainer.html(`
                <div class="text-center w-100 text-danger">
                    <p class="font-weight-bold">خطا در بارگذاری نوبت‌ها: ${xhr.status}</p>
                </div>
            `);
      }
    });
  }

  // تابع مشترک برای ایجاد HTML نوبت‌ها
  function createAppointmentHTML(appointment) {
    const appointment_date = moment(appointment.appointment_date).locale('fa');
    const formattedDate = appointment_date.format('dddd، jD jMMMM');
    const formattedTime = appointment.appointment_time || 'نامشخص';
    const isAttended = appointment.status === 'attended';
    const isCancelled = appointment.status === 'cancelled';
    let buttonText = 'پایان ویزیت';
    let buttonClass = 'btn-outline-info';

    if (isAttended) {
      buttonText = 'ویزیت شده';
    } else if (isCancelled) {
      buttonText = 'لغو شده';
      buttonClass = 'btn-outline-danger'; // قرمز کردن دکمه
    }

    return `
        <div class="my-appointments-lists-card w-100 d-flex justify-content-between align-items-center p-3 my-border">
            <div class="d-flex align-items-center gap-10 cursor-pointer" 
                data-toggle="modal" 
                data-target="#userInfoModalCenter"
                data-id="${appointment.id}"
                data-time="${formattedTime}"
                data-tracking-code="${appointment.tracking_code || ''}"
                data-date="${formattedDate}"
                data-fullname="${appointment.patient.first_name + ' ' + appointment.patient.last_name}"
                data-mobile="${appointment.patient.mobile}"
                data-national-code="${appointment.patient.national_code}"
                data-payment-status="${getPaymentStatus(appointment.payment_status)}"
                data-appointment-type="${getAppointmentType(appointment.appointment_type)}"
                data-center-name="${appointment.clinic ? appointment.clinic.name : ''}">
                <button class="btn h-50 border border-success bg-light-success d-flex justify-content-center align-items-center">
                    ${formattedTime}
                </button>
                <div class="d-flex flex-column gap-10">
                    <span class="font-weight-bold">
                        ${appointment.patient.first_name + ' ' + appointment.patient.last_name}
                    </span>
                    <span class="font-weight-light font-size-13">
                        ${appointment.patient.mobile}
                    </span>
                    <span class="font-weight-light text-danger font-size-13">
                        ${getPaymentStatus(appointment.payment_status)}
                    </span>
                </div>
            </div>
            <div>
                <span class="font-size-13 font-weight-bold">
                    ${appointment.patient.national_code}
                </span>
            </div>
            <div>
                <button class="btn ${buttonClass} btn-end-visit" 
                        data-appointment-id="${appointment.id}" 
                        ${isAttended || isCancelled ? 'disabled' : ''}>
                    ${buttonText}
                </button>
            </div>
        </div>
    `;
  }

  // رویداد کلیک روی تقویم
  document.addEventListener('DOMContentLoaded', function() {
    const calendarBody = document.getElementById('calendar-body');
    calendarBody.addEventListener('click', function(e) {
      const target = e.target.closest('.calendar-day');
      if (target && !target.classList.contains('empty')) {
        const selectedDate = target.getAttribute('data-date');
        handleDateSelection(selectedDate, localStorage.getItem('selectedClinicId'), $(
          '.btn-filter-appointment-toggle').text().trim());
        $('#miniCalendarModal').modal('hide');
      }
    });
  });

  // فیلتر نوبت‌ها
  let selectedCurrentTextDropToggle = "";
  // فیلتر نوبت‌ها
  $(document).ready(function() {
    let selectedDate = $('#datepicker').text().trim();
    const appointmentsContainer = $('#appointment-lists-container');

    // هندل کردن کلیک روی فیلترها
    $('.appointments-filter-drop-toggle li').on('click', function() {
      let filterTypeText = $(this).text().trim();
      if ($(this).find('span').text().includes('فعالسازی نوبت دهی مطب')) {
        window.location.href = 'آدرس_مشخص_شده_توسط_کاربر';
        return;
      }

      // آپدیت متن دکمه فیلتر
      $('.btn-filter-appointment-toggle span.text-btn-425').text(filterTypeText);

      // گرفتن تاریخ فعلی
      let selectedDate = $('#datepicker').text().trim();
      let selectedClinicId = localStorage.getItem('selectedClinicId');

      // پاک کردن UI و نمایش لودینگ
      appointmentsContainer.empty().html(loadingIndicator);

      $.ajax({
        url: "{{ route('dr.turn.filter-appointments') }}",
        type: "GET",
        data: {
          type: filterTypeText === 'کل نوبت ها' ? '' : (filterTypeText === 'نوبت های مطب' ? 'in_person' :
            'online'),
          date: selectedDate,
          selectedClinicId: selectedClinicId
        },
        success: function(response) {
          console.log('Filter response:', response);
          appointmentsContainer.empty();

          if (response.appointments && response.appointments.length > 0) {
            response.appointments.forEach(function(appointment) {
              const appointmentHTML = createAppointmentHTML(appointment);
              appointmentsContainer.append(appointmentHTML);
            });
          } else {
            appointmentsContainer.html(`
                        <div class="text-center w-100">
                            <p class="font-weight-bold">نوبتی با این فیلتر یافت نشد.</p>
                        </div>
                    `);
          }
        },
        error: function(xhr) {
          console.error('Filter AJAX error:', xhr.responseText);
          appointmentsContainer.html(`
                    <div class="text-center w-100 text-danger">
                        <p class="font-weight-bold">خطا در فیلتر کردن نوبت‌ها.</p>
                    </div>
                `);
        },
        complete: function() {
          $('.appointments-filter-drop-toggle').removeClass('show');
        }
      });
    });

    // هندل کردن کلیک روی تقویم
    $('#calendar-body').on('click', '.calendar-day', function() {
      if (!$(this).hasClass('empty')) {
        let selectedDate = $(this).data('date');
        let filterText = $('.btn-filter-appointment-toggle span.text-btn-425').text().trim();
        handleDateSelection(selectedDate, localStorage.getItem('selectedClinicId'), filterText);
      }
    });
  });

  // جستجوی زنده
  $(document).ready(function() {
    const searchInput = $('input[placeholder="جستجو بیمار ....."]');
    const appointmentsContainer = $('#appointment-lists-container');
    let searchTimeout;

    // جستجوی زنده با تأخیر 300 میلی‌ثانیه
    searchInput.on('input', function() {
      clearTimeout(searchTimeout);
      const query = $(this).val().trim();
      const selectedClinicId = localStorage.getItem('selectedClinicId');
      const filterTypeText = $('.btn-filter-appointment-toggle span.text-btn-425').text().trim();
      const selectedDate = $('#datepicker').text().trim();

      if (!selectedDate) {
        console.error('No date selected!');
        appointmentsContainer.html(
          '<div class="text-center w-100 text-danger">لطفاً ابتدا یک تاریخ انتخاب کنید.</div>');
        return;
      }

      searchTimeout = setTimeout(function() {
        searchAppointments(query, selectedClinicId, filterTypeText, selectedDate);
      }, 300);
    });

    function searchAppointments(query, selectedClinicId, filterText, date) {
      console.log('Search query:', query);
      console.log('Selected clinic ID:', selectedClinicId);
      console.log('Filter text:', filterText);
      console.log('Date:', date);

      const filterType = filterText === 'کل نوبت ها' ? '' : (filterText === 'نوبت های مطب' ? 'in_person' :
        'online');

      // پاک کردن UI و نمایش لودینگ
      appointmentsContainer.empty().html(loadingIndicator);

      $.ajax({
        url: "{{ route('dr.search.appointments') }}",
        type: 'GET',
        data: {
          query: query,
          selectedClinicId: selectedClinicId,
          type: filterType,
          date: date
        },
        success: function(response) {
          console.log('Search response:', response);
          appointmentsContainer.empty();

          if (response.appointments && response.appointments.length > 0) {
            response.appointments.forEach(function(appointment) {
              const appointmentHTML = createAppointmentHTML(appointment);
              appointmentsContainer.append(appointmentHTML);
            });
          } else {
            appointmentsContainer.html(`
                        <div class="text-center w-100">
                            <p class="font-weight-bold">نوبتی با این مشخصات یافت نشد.</p>
                        </div>
                    `);
          }
        },
        error: function(xhr) {
          console.error('Search AJAX error:', xhr.responseText);
          appointmentsContainer.html(`
                    <div class="text-center w-100 text-danger">
                        <p class="font-weight-bold">خطا در جستجو: ${xhr.status}</p>
                    </div>
                `);
        }
      });
    }
  });

  $(document).ready(function() {
    $(document).on('click', '.btn-end-visit', function(e) {
      e.preventDefault();
      const appointmentId = $(this).data('appointment-id');
      $('#endVisitModalCenter').data('appointment-id', appointmentId);
      $('#endVisitModalCenter').modal('show');
    });

    $('#endVisitModalCenter .my-btn-primary').on('click', function(e) {
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

            const card = endVisitButton.closest('.my-appointments-lists-card');
            if (card.length === 0) {
              console.error('کارت والد دکمه پیدا نشد:', appointmentId);
              Swal.fire('خطا', 'کارت نوبت پیدا نشد.', 'error');
              return;
            }

            // غیرفعال کردن دکمه و تغییر متن
            endVisitButton.prop('disabled', true).addClass('disabled').text('ویزیت شده');

            // حذف کارت از UI با انیمیشن و رفرش لیست
            card.fadeOut(300, function() {
              card.remove();
              loadAppointments(localStorage.getItem('selectedClinicId'), $(
                '.btn-filter-appointment-toggle').text().trim());
            });

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

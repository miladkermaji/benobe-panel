//resources\views\dr\panel\turn\schedule\option\appointments.blade.php
<script>
  /* drop select option */

  $(document).ready(function() {
    let currentDate = moment().format('YYYY-MM-DD');
    let persianDate = moment(currentDate, 'YYYY-MM-DD').locale('fa').format('jYYYY/jMM/jDD');
    let selectedClinicId = localStorage.getItem('selectedClinicId') || 'default';
    $('.selectDate_datepicker__xkZeS span.mx-1').text(persianDate);
    showLoading();
    loadCalendar(currentDate);
    loadAppointments(persianDate, selectedClinicId);
  });
  /* drop select option */
  const appointmentsTableBody = $('.table tbody');
  let loadingIndicator = `<tr id="loading-row" class="w-100">
    <td colspan="12" class="text-center py-4">
        <div class="spinner-custom" role="status">
            <span class="visually-hidden">در حال بارگذاری...</span>
        </div>
    </td>
</tr>`;

  function getPrescriptionStatus(status) {
    switch (status) {
      case 'scheduled':
        return '<span class="fw-bold text-warning">در انتظار</span>';
      case 'cancelled':
        return '<span class="fw-bold text-danger">لغو شده</span>';
      case 'attended':
        return '<span class="fw-bold text-primary">ویزیت شده</span>';
      case 'missed':
        return '<span class="fw-bold text-muted">ویزیت نشده</span>';
      default:
        return '<span class="fw-bold text-dark">نامشخص</span>';
    }
  }

  function getPaymentStatus(status) {
    switch (status) {
      case 'pending':
        return '<span class="fw-bold text-warning">در حال پرداخت</span>';
      case 'paid':
        return '<span class="fw-bold text-success">پرداخت شده</span>';
      case 'unpaid':
        return '<span class="fw-bold text-danger">پرداخت نشده</span>';
      default:
        return '<span class="fw-bold text-dark">نامشخص</span>';
    }
  }
  let currentDate = moment().format('YYYY-MM-DD');
  const days = 18;

  function renderPagination(pagination, callback) {
    const paginationLinks = $('#pagination-links');
    paginationLinks.empty();
    if (!pagination || !pagination.current_page || !pagination.last_page) {
      paginationLinks.html('<li class="page-item disabled"><span class="page-link">بدون داده</span></li>');
      return;
    }
    const prevDisabled = pagination.current_page === 1 ? 'disabled' : '';
    paginationLinks.append(`
        <li class="page-item ${prevDisabled}">
            <a class="page-link" href="#" data-page="${pagination.current_page - 1}" aria-label="Previous">
                <span aria-hidden="true">«</span>
            </a>
        </li>
    `);
    for (let i = 1; i <= pagination.last_page; i++) {
      const activeClass = i === pagination.current_page ? 'active' : '';
      paginationLinks.append(`
            <li class="page-item ${activeClass}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>
        `);
    }
    const nextDisabled = pagination.current_page === pagination.last_page ? 'disabled' : '';
    paginationLinks.append(`
        <li class="page-item ${nextDisabled}">
            <a class="page-link" href="#" data-page="${pagination.current_page + 1}" aria-label="Next">
                <span aria-hidden="true">»</span>
            </a>
        </li>
    `);
    paginationLinks.find('.page-link').off('click').on('click', function(e) {
      e.preventDefault();
      const page = $(this).data('page');
      if (page && !$(this).parent().hasClass('disabled') && !$(this).parent().hasClass('active')) {
        callback(page);
      }
    });
  }

  function searchPatients(query, page = 1) {
    let selectedDate = $('.selectDate_datepicker__xkZeS span').text();
    let requestData = {
      date: selectedDate,
      selectedClinicId: localStorage.getItem('selectedClinicId') || 'default',
      page: page
    };
    if (query !== "") {
      requestData.query = query;
    }
    $.ajax({
      url: "{{ route('search.patients') }}",
      method: "GET",
      data: requestData,
      beforeSend: function() {
        showLoading();
      },
      success: function(response) {
        hideLoading();
        let appointmentsTableBody = $(".table tbody");
        appointmentsTableBody.html("");
        if (response.success && response.patients && response.patients.length > 0) {
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
                            <td>${patient.first_name || 'نامشخص'} ${patient.last_name || ''}</td>
                            <td>${patient.mobile || 'نامشخص'}</td>
                            <td>${patient.national_code || 'نامشخص'}</td>
                            <td>${getPrescriptionStatus(appointment.status)}</td>
                            <td>${getPaymentStatus(appointment.payment_status)}</td>
                            <td>${insurance}</td>
                            <td>${appointmentDate}</td>
                            <td>${appointment.appointment_time}</td>
                            <td>
                                <button class="${isAttended ? 'text-primary' : 'btn-end-visit'}" 
                                        data-appointment-id="${appointment.id}" ${buttonDisabled}>
                                    ${isAttended ? 'ویزیت شده' : 'پایان ویزیت'}
                                </button>
                            </td>
                            <td class="text-center">
                                <div class="dropdown d-inline-block position-relative">
                                    <button class="flex items-center justify-center bg-white border border-gray-300 rounded-sm hover:bg-gray-100 transition-colors p-1 focus:outline-none dropdown-toggle custom-dropdown-trigger" type="button">
                                        <img src="{{ asset('dr-assets/icons/dots-vertical-svgrepo-com.svg') }}" width="20" height="20" alt="More options">
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end my-drp-left-0">
                                        <li class="${dropdownItemDisabled}">
                                            <a class="dropdown-item text-dark cancel-appointment" href="#" data-id="${appointment.id}">
                                                لغو نوبت
                                            </a>
                                        </li>
                                        <li class="${dropdownItemDisabled}">
                                            <a class="dropdown-item text-dark move-appointment" href="#" data-date="${appointment.appointment_date}" data-id="${appointment.id}">
                                                جابجایی نوبت
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-dark block-user" href="#" data-id="${appointment.id}" data-mobile="${patient.mobile}" data-user-id="${patient.id}" data-user-name="${patient.first_name + ' ' + patient.last_name}">
                                                مسدود کردن کاربر
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>`;
            appointmentsTableBody.append(appointmentHTML);
          });
          renderPagination(response.pagination, function(newPage) {
            searchPatients(query, newPage);
          });
        } else {
          appointmentsTableBody.html(`
                    <tr>
                        <td colspan="12" class="text-center py-3 text-muted">
                            هیچ نتیجه‌ای یافت نشد.
                        </td>
                    </tr>
                `);
          renderPagination({
            current_page: 1,
            last_page: 1,
            per_page: 10,
            total: 0
          });
        }
      },
      error: function() {
        hideLoading();
        toastr.error('خطا در دریافت اطلاعات');
      }
    });
  }

  function handleDateSelection(selectedDate, selectedClinicId, page = 1) {
    showLoading();
    $.ajax({
      url: "{{ route('doctor.appointments.by-date') }}",
      method: 'GET',
      data: {
        date: selectedDate,
        selectedClinicId: selectedClinicId,
        page: page
      },
      success: function(response) {
        hideLoading();
        appointmentsTableBody.html('');
        if (response.success && response.appointments && response.appointments.length > 0) {
          response.appointments.forEach(function(appointment) {
            const nationalCode = appointment.patient.national_code ?
              appointment.patient.national_code : 'نامشخص';
            const isAttended = appointment.status === 'attended';
            const buttonDisabled = isAttended ? 'disabled' : '';
            const dropdownItemDisabled = isAttended || appointment.status === 'cancelled' ? 'disabled' : '';
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
                                <button class="${isAttended ? 'text-primary' : 'btn-end-visit'}" 
                                        data-appointment-id="${appointment.id}" ${buttonDisabled}>
                                    ${isAttended ? 'ویزیت شده' : 'پایان ویزیت'}
                                </button>
                            </td>
                            <td class="text-center">
                                <div class="dropdown d-inline-block position-relative">
                                    <button class="flex items-center justify-center bg-white border border-gray-300 rounded-sm hover:bg-gray-100 transition-colors p-1 focus:outline-none dropdown-toggle custom-dropdown-trigger" type="button">
                                        <img src="{{ asset('dr-assets/icons/dots-vertical-svgrepo-com.svg') }}" width="20" height="20" alt="More options">
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end my-drp-left-0">
                                        <li class="${dropdownItemDisabled}">
                                            <a class="dropdown-item text-dark cancel-appointment" href="#" data-id="${appointment.id}">
                                                لغو نوبت
                                            </a>
                                        </li>
                                        <li class="${dropdownItemDisabled}">
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
          renderPagination(response.pagination, function(newPage) {
            handleDateSelection(selectedDate, selectedClinicId, newPage);
          });
        } else {
          appointmentsTableBody.html(`
                    <tr>
                        <td colspan="12" class="text-center py-3">هیچ نوبتی برای این تاریخ وجود ندارد.</td>
                    </tr>
                `);
          renderPagination({
            current_page: 1,
            last_page: 1,
            per_page: 10,
            total: 0
          }, function() {});
        }
      },
      error: function() {
        hideLoading();
        appointmentsTableBody.html(`
                <tr>
                    <td colspan="12" class="text-center py-3 text-danger">خطا در دریافت نوبت‌ها.</td>
                </tr>
            `);
      }
    });
  }

  function showLoading() {
    appointmentsTableBody.html(loadingIndicator);
  }

  function hideLoading() {
    $("#loading-row").remove();
  }

  function loadCalendar(date) {
    calendar.empty();
    let todayExists = false;
    $('.calendar-card').removeClass('my-active');
    for (let i = 0; i < days; i++) {
      const current = moment(date).add(i, 'days');
      const persianDate = current.locale('fa').format('dddd');
      const persianFormattedDate = current.locale('fa').format('YYYY-MM-DD');
      const isActive = current.isSame(moment(), 'day') ? 'my-active' : '';
      if (isActive) todayExists = persianFormattedDate;
      const card = `
            <div class="calendar-card btn btn-light ${isActive}" data-date="${persianFormattedDate}">
                <div class="day-name">${persianDate}</div>
                <div class="date">${current.locale('fa').format('D MMMM YYYY')}</div>
            </div>`;
      calendar.append(card);
    }
    calendar.on('click', '.calendar-card', function() {
      const selectedDate = $(this).attr('data-date');
      selectedClinicId = localStorage.getItem('selectedClinicId') || 'default';
      $('.calendar-card').removeClass('my-active');
      $(this).addClass('my-active');
      handleDateSelection(selectedDate, selectedClinicId);
      loadAppointments(selectedDate, selectedClinicId);
      $('#miniCalendarModal').modal('hide');
    });
  }

  function loadAppointments(selectedDate, clinicId, page = 1) {
    showLoading();
    $.ajax({
      url: "{{ route('doctor.appointments.by-date') }}",
      method: 'GET',
      data: {
        date: selectedDate,
        selectedClinicId: clinicId,
        page: page
      },
      beforeSend: function() {
        appointmentsTableBody.html(loadingIndicator);
      },
      success: function(response) {
        hideLoading();
        appointmentsTableBody.html('');
        if (response.success && response.appointments && response.appointments.length > 0) {
          response.appointments.forEach(function(appointment) {
            const nationalCode = appointment.patient.national_code ?
              appointment.patient.national_code : 'نامشخص';
            const isAttended = appointment.status === 'attended';
            const buttonDisabled = isAttended ? 'disabled' : '';
            const dropdownItemDisabled = isAttended || appointment.status === 'cancelled' ? 'disabled' : '';
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
                                <button class="${isAttended ? 'text-primary' : 'btn-end-visit'}" 
                                        data-appointment-id="${appointment.id}" ${buttonDisabled}>
                                    ${isAttended ? 'ویزیت شده' : 'پایان ویزیت'}
                                </button>
                            </td>
                            <td class="text-center">
                                <div class="dropdown d-inline-block position-relative">
                                    <button class="flex items-center justify-center bg-white border border-gray-300 rounded-sm hover:bg-gray-100 transition-colors p-1 focus:outline-none dropdown-toggle custom-dropdown-trigger" type="button">
                                        <img src="{{ asset('dr-assets/icons/dots-vertical-svgrepo-com.svg') }}" width="20" height="20" alt="More options">
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end my-drp-left-0">
                                        <li class="${dropdownItemDisabled}">
                                            <a class="dropdown-item text-dark cancel-appointment" href="#" data-id="${appointment.id}">
                                                لغو نوبت
                                            </a>
                                        </li>
                                        <li class="${dropdownItemDisabled}">
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
          renderPagination(response.pagination, function(newPage) {
            loadAppointments(selectedDate, clinicId, newPage);
          });
        } else {
          appointmentsTableBody.html(`
                    <tr>
                        <td colspan="12" class="text-center py-3">هیچ نوبتی برای این تاریخ وجود ندارد.</td>
                    </tr>
                `);
          renderPagination({
            current_page: 1,
            last_page: 1,
            per_page: 10,
            total: 0
          }, function() {});
        }
      },
      error: function() {
        hideLoading();
        appointmentsTableBody.html(`
                <tr>
                    <td colspan="12" class="text-center py-3 text-danger">خطا در دریافت نوبت‌ها.</td>
                </tr>
            `);
      }
    });
  }
  $(document).ready(function() {
    $(".my-form-control").on("input", function() {
      let searchText = $(this).val().trim();
      searchPatients(searchText);
    });
    searchPatients("");
  });
  /* manage appointment cancel reschedule blockusers */
  $(document).on("click", ".cancel-appointment", function(e) {
    e.preventDefault();
    let appointmentId = $(this).data("id");
    let row = $(this).closest("tr");
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
          url: updateStatusAppointmentUrl.replace(":id", appointmentId),
          type: "POST",
          data: {
            _token: $('meta[name="csrf-token"]').attr("content"),
            status: "cancelled",
            selectedClinicId: localStorage.getItem('selectedClinicId')
          },
          beforeSend: function() {
            Swal.fire({
              title: "در حال پردازش...",
              text: "لطفاً منتظر بمانید",
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });
          },
          success: function(response) {
            Swal.fire({
              title: "موفقیت‌آمیز!",
              text: response.message,
              icon: "success",
              confirmButtonColor: "#3085d6"
            });
            // به‌روزرسانی ردیف در جدول
            row.find('td:nth-child(5)').html(
              '<span class="fw-bold text-danger">لغو شده</span>'); // تغییر وضعیت به "لغو شده"
            row.find('.btn-end-visit').prop('disabled', true).addClass(
              'text-muted'); // غیرفعال کردن دکمه پایان ویزیت
            row.find('.cancel-appointment').closest('li').addClass(
              'disabled'); // غیرفعال کردن گزینه لغو نوبت
            row.find('.move-appointment').closest('li').addClass(
              'disabled'); // غیرفعال کردن گزینه جابجایی نوبت
            row.fadeOut(300, function() {
              row.remove();
            });
            fetchAppointmentsCount()
          },
          error: function() {
            Swal.fire({
              title: "خطا!",
              text: "مشکلی در ارتباط با سرور رخ داده است.",
              icon: "error",
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
    $(".dropdown-menu").not(dropdownMenu).removeClass("show");
    dropdownMenu.toggleClass("show");
  });
  $(document).on("click", function() {
    $(".dropdown-menu").removeClass("show").css({
      position: "",
      top: "",
      left: ""
    });
  });

  function loadCalendarRow() {
    calendar.empty();
    let badgeCount = 0;
    let displayedDays = 0;
    let current = moment(currentDate).locale('en');
    let i = 0;

    // Add today's date
    const persianDateToday = today.locale('fa').format('dddd');
    const persianFormattedDateToday = today.locale('fa').format('D MMMM YYYY');
    const appointmentDateToday = today.locale('en').format('YYYY-MM-DD');
    const dayOfWeekToday = today.format('dddd').toLowerCase();

    const appointmentToday = appointmentsData.find(appt => {
      const apptDate = moment(appt.appointment_date).locale('en').format('YYYY-MM-DD');
      return apptDate === appointmentDateToday;
    });

    // Appointment count for today
    let appointmentCountToday = 0;
    if (workingDays.includes(dayOfWeekToday) && appointmentToday) {
      appointmentCountToday = appointmentToday.appointment_count;
    }

    const badgeHtmlToday = appointmentCountToday > 0 ?
      `<span class="appointment-badge">${appointmentCountToday}</span>` : '';
    if (appointmentCountToday > 0) badgeCount++;

    const cardToday = `
        <div class="calendar-card btn btn-light my-active" data-date="${appointmentDateToday}" style="--delay: ${displayedDays}">
            ${badgeHtmlToday}
            <div class="day-name">${persianDateToday}</div>
            <div class="date">${persianFormattedDateToday}</div>
            <div class="current-day-icon"></div>
        </div>`;
    calendar.append(cardToday);
    displayedDays++;

    // Continue for other days
    while (displayedDays < calendarDays && i < calendarDays * 2) {
      if (!current.isSame(today, 'day')) { // Prevent today's repetition
        const dayOfWeek = current.format('dddd').toLowerCase();
        if (workingDays.includes(dayOfWeek)) {
          const persianDate = current.locale('fa').format('dddd');
          const persianFormattedDate = current.locale('fa').format('D MMMM YYYY');
          const appointmentDate = current.locale('en').format('YYYY-MM-DD');

          const appointment = appointmentsData.find(appt => {
            const apptDate = moment(appt.appointment_date).locale('en').format('YYYY-MM-DD');
            return apptDate === appointmentDate;
          });

          // Appointment count for working days
          let appointmentCount = 0;
          if (workingDays.includes(dayOfWeek) && current.isSameOrAfter(today, 'day') && appointment) {
            appointmentCount = appointment.appointment_count;
          }

          const badgeHtml = appointmentCount > 0 ? `<span class="appointment-badge">${appointmentCount}</span>` : '';
          if (appointmentCount > 0) badgeCount++;

          const card = `
                    <div class="calendar-card btn btn-light" data-date="${appointmentDate}" style="--delay: ${displayedDays}">
                        ${badgeHtml}
                        <div class="day-name">${persianDate}</div>
                        <div class="date">${persianFormattedDate}</div>
                    </div>`;
          calendar.append(card);
          displayedDays++;
        }
      }
      current.add(1, 'days');
      i++;
    }

    updateButtonState();
  }
  const calendar = $('#calendar');
  let isAnimating = false;
  const minDate = moment().locale('en').subtract(30, 'days').format('YYYY-MM-DD');
  const maxDate = moment().locale('en').add(30, 'days').format('YYYY-MM-DD');
  let appointmentsData = [];

  function updateButtonState() {
    const prevButton = $('#prevRow');
    const nextButton = $('#nextRow');
    const firstDate = moment(currentDate).locale('en');
    const lastDate = moment(currentDate).locale('en').add(calendarDays - 1, 'days');

    prevButton.prop('disabled', firstDate.isSameOrBefore(today, 'day'));
    nextButton.prop('disabled', lastDate.isSameOrAfter(moment().add(calendarDays, 'days'), 'day'));
  }
  const loadingOverlay = $('#calendar-loading');

  function fetchAppointmentsCount() {
    loadingOverlay.show();
    calendar.hide();
    $.ajax({
      url: "{{ route('appointments.count') }}",
      method: 'GET',
      data: {
        selectedClinicId: localStorage.getItem('selectedClinicId')
      },
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(response) {
        if (response.status) {
          appointmentsData = response.data;
          workingDays = response.working_days || [];
          calendarDays = response.calendar_days || 30;
          appointmentSettings = response.appointment_settings || [];
          $('#calendar-error').hide();
          loadCalendar();
          loadingOverlay.hide();
          calendar.show();
        } else {
          console.error('Error fetching appointments:', response.message);
          $('#calendar-error').show();
          loadingOverlay.hide();
        }
      },
      error: function(xhr, status, error) {
        console.error('AJAX error:', error);
        $('#calendar-error').show();
        loadingOverlay.hide();
      }
    });
  }
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
  $('#move-appointments-btn').click(function() {
    let selected = getSelectedAppointments();
    if (!selected.length) {
      return Swal.fire('هشدار', 'نوبتی انتخاب نشده!', 'warning');
    }
    const hasAttended = selected.some(appointment => appointment.status === 'ویزیت شده');
    const hasCancled = selected.some(appointment => appointment.status === 'لغو شده');
    if (hasAttended) {
      return Swal.fire('خطا', 'نمی‌توانید نوبت‌های ویزیت‌شده را جابجا کنید!', 'error');
    }
    if (hasCancled) {
      return Swal.fire('خطا', 'نمی‌توانید نوبت‌های لغو شده را جابجا کنید!', 'error');
    }

    // ذخیره oldDates در rescheduleModal
    const oldDates = [...new Set(selected.map(item => item.date))];
    $("#rescheduleModal").data("old-dates", oldDates); // ذخیره تاریخ‌های قدیمی

    $('#rescheduleModal').modal('show');
    generateRescheduleCalendar(moment().jYear(), moment().jMonth() + 1);
    populateRescheduleSelectBoxes();

    // رویداد کلیک برای روزهای تقویم جابجایی
    $('#calendar-reschedule .calendar-day').not('.empty').off('click').on('click', function() {
      const newDate = $(this).data('date');
      const gregorianDate = moment(newDate, 'jYYYY-jMM-jDD').format('YYYY-MM-DD');
      const today = moment().format('YYYY-MM-DD');
      if (gregorianDate < today || $(this).hasClass('holiday') || $(this).find('.my-badge-success').length >
        0) {
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
          // بازیابی oldDates از rescheduleModal
          const storedOldDates = $("#rescheduleModal").data("old-dates") || [];
          if (!storedOldDates.length) {
            Swal.fire('خطا', 'تاریخ‌های نوبت‌های قبلی یافت نشدند!', 'error');
            return;
          }

          // ارسال درخواست برای هر oldDate
          storedOldDates.forEach(oldDate => {
            $.ajax({
              url: "{{ route('doctor.reschedule_appointment') }}",
              method: 'POST',
              data: {
                old_date: oldDate, // تاریخ میلادی
                new_date: gregorianDate, // تاریخ میلادی
                selectedClinicId: localStorage.getItem('selectedClinicId'),
                _token: '{{ csrf_token() }}'
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
                if (response.status) {
                  Swal.fire('موفقیت', response.message, 'success');
                  loadAppointmentsCount();
                  loadHolidayStyles();
                  fetchAppointmentsCount();
                  loadAppointments(gregorianDate, localStorage.getItem('selectedClinicId'));
                  selected.forEach(app => app.row.remove());
                  $('#rescheduleModal').modal('hide');
                } else {
                  Swal.fire('خطا', response.message, 'error');
                }
              },
              error: function(xhr) {
                Swal.close();
                let errorMessage = 'مشکلی در ارتباط با سرور رخ داده است.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
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
  $(document).on('click', '.move-appointment', function() {
    let appointmentId = $(this).data('id');
    let oldDate = $(this).data('date');

    if (!appointmentId || !oldDate) {
      Swal.fire("خطا", "امکان دریافت اطلاعات نوبت وجود ندارد.", "error");
      return;
    }

    // ذخیره oldDate و appointmentId در rescheduleModal
    $("#rescheduleModal").attr("data-appointment-id", appointmentId);
    $("#rescheduleModal").attr("data-old-date", oldDate); // اطمینان از ذخیره oldDate
    $('#rescheduleModal').modal('show');

    let year = moment(oldDate, 'YYYY-MM-DD').jYear();
    let month = moment(oldDate, 'YYYY-MM-DD').jMonth() + 1;
    generateRescheduleCalendar(year, month);
    populateRescheduleSelectBoxes();
    fetchAppointmentsCount();
  });
</script>
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
          updateModalContent(response);
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
    const persianMonths = ["فروردین", "اردیبهشت", "خرداد", "تیر", "مرداد", "شهریور", "مهر", "آبان", "آذر", "دی", "بهمن",
      "اسفند"
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

  function generateCalendar(year, month) {
    const calendarBody = $('#calendar-body');
    calendarBody.empty();
    loadHolidayStyles();
    attachRescheduleDayClickEvents()
    loadAppointmentsCount()
    loadAppointmentsCountInReschedule()
    const firstDayOfMonth = moment(`${year}/${month}/01`, 'jYYYY/jMM/jDD').locale('fa');
    const daysInMonth = firstDayOfMonth.jDaysInMonth();
    let firstDayWeekday = firstDayOfMonth.weekday();
    const today = moment().locale('fa');
    for (let i = 0; i < firstDayWeekday; i++) {
      calendarBody.append('<div class="calendar-day empty"></div>');
    }
    for (let day = 1; day <= daysInMonth; day++) {
      const currentDay = firstDayOfMonth.clone().add(day - 1, 'days');
      const persianDate = currentDay.format('jYYYY/jMM/jDD');
      const gregorianDate = currentDay.format('YYYY-MM-DD');
      const isToday = currentDay.isSame(today, 'day');
      const dayClass = `calendar-day ${isToday ? 'active' : ''}`;
      const dayElement = `
            <div class="${dayClass}" data-date="${persianDate}" data-gregorian-date="${gregorianDate}">
                <span>${currentDay.format('jD')}</span>
            </div>`;
      calendarBody.append(dayElement);
    }
    $('.calendar-day').not('.empty').off('click').on('click', function() {
      const persianDate = $(this).data('date');
      const gregorianDate = $(this).data('gregorian-date');
      $('#selectedDate').val(gregorianDate);
      $('.selectDate_datepicker__xkZeS span.mx-1').text(persianDate);
      closeModal();
      $.ajax({
        url: "{{ route('doctor.get_holiday_status') }}",
        method: "POST",
        data: {
          date: gregorianDate,
          _token: '{{ csrf_token() }}',
          selectedClinicId: localStorage.getItem('selectedClinicId')
        },
        success: function(response) {
          if (!response.is_holiday) {
            getWorkHours(gregorianDate);
          } else {
            $("#workHoursContainer").empty();
            $("#updateWorkHours").hide();
            $(".not-appointment").removeClass("d-none");
            $(".having-nobat-for-this-day").addClass("d-none");
          }
          handleDateSelection(persianDate, localStorage.getItem('selectedClinicId'));
          $('.my-form-control').val('');
        },
        error: function() {
          Swal.fire('خطا', 'مشکلی در ارتباط وجود دارد.', 'error');
        }
      });
    });
    loadHolidayStyles();
  }

  function closeModal() {
    const $modal = $('#miniCalendarModal');
    $modal.modal('hide');
    setTimeout(() => {
      $('.modal-backdrop').remove();
      $('body').removeClass('modal-open');
      $('body').css('padding-right', '');
    }, 300);
    $(document).on('click', '.modal-backdrop', function() {
      closeModal();
    });
    $modal.off('hidden.bs.modal').on('hidden.bs.modal', function() {
      $('.modal-backdrop').remove();
      $('body').removeClass('modal-open');
      $('body').css('padding-right', '');
    });
  }
  $('.selectDate_datepicker__xkZeS').on('click', function() {
    $('#miniCalendarModal').modal({
      backdrop: true,
      keyboard: true
    });
  });

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
  $(document).ready(function() {
    $('#next').click(() => animateAndLoadCalendar('next'));
    $('#prev').click(() => animateAndLoadCalendar('prev'));
    loadCalendar(currentDate);
  });

  function populateRescheduleSelectBoxes() {
    const yearSelect = $('#year-reschedule');
    const monthSelect = $('#month-reschedule');
    const currentYear = moment().jYear();
    const currentMonth = moment().jMonth() + 1;
    yearSelect.empty();
    monthSelect.empty();
    for (let year = currentYear - 10; year <= currentYear + 10; year++) {
      yearSelect.append(new Option(year, year));
    }
    const persianMonths = ["فروردین", "اردیبهشت", "خرداد", "تیر", "مرداد", "شهریور", "مهر", "آبان", "آذر", "دی", "بهمن",
      "اسفند"
    ];
    for (let month = 1; month <= 12; month++) {
      monthSelect.append(new Option(persianMonths[month - 1], month));
    }
    yearSelect.val(currentYear);
    monthSelect.val(currentMonth);
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
    for (let i = 0; i < firstDayWeekday; i++) {
      rescheduleCalendarBody.append('<div class="calendar-day empty"></div>');
    }
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
    loadHolidayStyles();
    loadAppointmentsCountInReschedule()
    attachRescheduleDayClickEvents();
  }

  function attachRescheduleDayClickEvents() {
    $('#calendar-reschedule .calendar-day').not('.empty').off('click').on('click', function() {
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
            // بررسی وجود old-dates (برای جابجایی گروهی)
            let oldDates = $("#rescheduleModal").data("old-dates") || [];
            let oldDate = $("#rescheduleModal").attr("data-old-date") || $('#dateModal').data('selectedDate');

            // اگر oldDates وجود داره (جابجایی گروهی)
            if (oldDates.length) {
              oldDates.forEach(oldDate => {
                $.ajax({
                  url: "{{ route('doctor.reschedule_appointment') }}",
                  method: 'POST',
                  data: {
                    old_date: oldDate,
                    new_date: gregorianDate,
                    _token: '{{ csrf_token() }}',
                    selectedClinicId: localStorage.getItem('selectedClinicId')
                  },
                  success: function(response) {
                    if (response.status) {
                      Swal.fire('موفقیت', response.message, 'success');
                      loadAppointmentsCount();
                      loadHolidayStyles();
                      fetchAppointmentsCount();
                      loadAppointments(gregorianDate, localStorage.getItem('selectedClinicId'));
                      $('#rescheduleModal').modal('hide');
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
            } else if (oldDate) {
              // جابجایی تکی
              if (oldDate.includes('/')) {
                oldDate = moment(oldDate, 'jYYYY/jMM/jDD').format('YYYY-MM-DD');
              }
              $.ajax({
                url: "{{ route('doctor.reschedule_appointment') }}",
                method: 'POST',
                data: {
                  old_date: oldDate,
                  new_date: gregorianDate,
                  _token: '{{ csrf_token() }}',
                  selectedClinicId: localStorage.getItem('selectedClinicId')
                },
                success: function(response) {
                  if (response.status) {
                    Swal.fire('موفقیت', response.message, 'success');
                    loadAppointmentsCount();
                    loadHolidayStyles();
                    fetchAppointmentsCount();
                    loadAppointments(gregorianDate, localStorage.getItem('selectedClinicId'));
                    $('#rescheduleModal').modal('hide');
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
            } else {
              Swal.fire("خطا", "تاریخ نوبت قبلی یافت نشد! لطفاً دوباره تلاش کنید.", "error");
            }
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
            $(this).removeClass('has-appointment');
            $(this).find('.my-badge-success').remove();
            if (appointment) {
              $(this).addClass('has-appointment');
            }
          });
        }
      }
    });
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
            $(this).removeClass('has-appointment');
            $(this).find('.my-badge-success').remove();
            if (appointment) {
              $(this).addClass('has-appointment');
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
    const modalBody = $('#dateModal .modal-body');
    modalBody.empty();
    if (!response || !response.status) {
      modalBody.html('<div class="alert alert-danger">خطایی در دریافت اطلاعات رخ داده است.</div>');
      return;
    }
    if (response.is_holiday) {
      modalBody.html(`
            <div class="alert alert-info">
                این روز تعطیل است. آیا می‌خواهید آن را از حالت تعطیلی خارج کنید؟
            </div>
            <div class="d-flex justify-content-between mt-3 gap-4">
                <button id="confirmUnHolidayButton" class="btn my-btn-primary h-50 w-100 me-2">بله</button>
                <button class="btn btn-danger h-50 w-100 close-modal" data-bs-dismiss="modal" aria-label="Close">خیر</button>
            </div>
        `);
    } else if (response.data && response.data.length > 0) {
      modalBody.html(`
            <div class="alert alert-info">
                شما برای این روز نوبت فعال دارید.
            </div>
            <div id="workHoursContainer">
            </div>
            <button id="updateWorkHours" onclick="updateWorkhours()" class="btn my-btn-primary w-100 h-50 mt-3" style="display: none;">
                بروزرسانی ساعات کاری
            </button>
            <div class="d-flex justify-content-between mt-3 gap-4">
                <button class="btn btn-danger h-50 w-100 close-modal me-2 cancle-btn-appointment">لغو نوبت‌ها</button>
                <button class="btn btn-secondary w-100 btn-reschedule h-50">جابجایی نوبت‌ها</button>
            </div>
        `);
    } else {
      modalBody.html(`
            <div class="alert alert-info">
                شما برای این روز نوبت فعالی ندارید. آیا می‌خواهید این روز را تعطیل کنید؟
            </div>
            <div id="workHoursContainer">
            </div>
            <button id="updateWorkHours" onclick="updateWorkhours()" class="btn my-btn-primary w-100 h-50 mt-3" style="display: none;">
                بروزرسانی ساعات کاری
            </button>
            <div class="d-flex justify-content-between mt-3 gap-4">
                <button id="confirmHolidayButton" class="btn my-btn-primary h-50 w-100 me-2">بله</button>
                <button class="btn btn-danger h-50 w-100 close-modal" data-bs-dismiss="modal" aria-label="Close">خیر</button>
            </div>
        `);
    }
  }
  const toggleHolidayUrl = "{{ route('doctor.toggle_holiday') }}";
  const getHolidaysUrl = "{{ route('doctor.get_holidays') }}";

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
                        <h6 class="fw-bold">برنامه کاری</h6>
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
            `<p class="text-center text-danger fw-bold">هیچ ساعات کاری برای این روز تعریف نشده است.</p>`
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
          if (!moment(nextAvailableDate, 'YYYY-MM-DD', true).isValid()) {
            Swal.fire('خطا', 'تاریخ دریافت‌شده معتبر نیست.', 'error');
            return;
          }
          let oldDates = [];
          let selected = getSelectedAppointments();
          if (selected.length > 0) {
            oldDates = [...new Set(selected.map(item => item.date))];
          } else {
            let oldDate = $('#dateModal').data('selectedDate') || $("#rescheduleModal").data("old-date");
            if (oldDate) oldDates.push(oldDate);
          }
          oldDates = oldDates
            .map(date => {
              const normalizedDate = date.replace(/\//g, '-');
              if (/^14\d{2}-\d{2}-\d{2}$/.test(normalizedDate)) {
                const jalaliMoment = moment.from(normalizedDate, 'fa', 'jYYYY-jMM-jDD');
                if (jalaliMoment.isValid()) {
                  return jalaliMoment.clone().locale('en').format('YYYY-MM-DD');
                }
              }
              if (moment(normalizedDate, 'YYYY-MM-DD', true).isValid()) {
                return normalizedDate;
              }
              return null;
            })
            .filter(date => date !== null);
          if (oldDates.length === 0) {
            Swal.fire('خطا', 'هیچ تاریخ معتبری برای جابجایی یافت نشد.', 'error');
            return;
          }
          const formattedNextDate = moment(nextAvailableDate, 'YYYY-MM-DD').locale('fa').format(
            'jD jMMMM jYYYY');
          const formattedOldDates = oldDates.map(date => {
            return moment(date, 'YYYY-MM-DD').locale('fa').format('jD jMMMM jYYYY');
          });
          Swal.fire({
            title: `اولین نوبت خالی (${formattedNextDate})`,
            text: `آیا می‌خواهید نوبت‌ها از تاریخ(های) ${formattedOldDates.join('، ')} به تاریخ ${formattedNextDate} منتقل شوند؟`,
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
          Swal.fire('اطلاع', response.message || 'هیچ نوبت خالی یافت نشد.', 'info');
        }
      },
      error: function() {
        Swal.fire('خطا', 'مشکلی در ارتباط با سرور وجود دارد.', 'error');
      },
    });
  });
  $(document).ready(function() {
    loadAppointmentsCount();
    const currentYear = moment().jYear();
    const currentMonth = moment().jMonth() + 1;
    populateSelectBoxes();
    generateCalendar(currentYear, currentMonth);
    $('#prev-month').off('click').on('click', function() {
      const yearSelect = $('#year');
      const monthSelect = $('#month');
      let currentMonth = parseInt(monthSelect.val());
      let currentYear = parseInt(yearSelect.val());
      if (currentMonth === 1) {
        currentMonth = 12;
        currentYear -= 1;
      } else {
        currentMonth -= 1;
      }
      yearSelect.val(currentYear);
      monthSelect.val(currentMonth);
      generateCalendar(currentYear, currentMonth);
    });
    $('#next-month').off('click').on('click', function() {
      const yearSelect = $('#year');
      const monthSelect = $('#month');
      let currentMonth = parseInt(monthSelect.val());
      let currentYear = parseInt(yearSelect.val());
      if (currentMonth === 12) {
        currentMonth = 1;
        currentYear += 1;
      } else {
        currentMonth += 1;
      }
      yearSelect.val(currentYear);
      monthSelect.val(currentMonth);
      generateCalendar(currentYear, currentMonth);
    });
    $('#year, #month').off('change').on('change', function() {
      const year = parseInt($('#year').val());
      const month = parseInt($('#month').val());
      generateCalendar(year, month);
    });
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
                $('#dateModal').modal('hide');
                loadAppointmentsCount();
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
    $(document).on('click', '.btn-reschedule', function() {
      const selectedDate = $('#dateModal').data('selectedDate');
      $("#rescheduleModal").data("old-dates", [selectedDate]); // ذخیره به‌صورت آرایه برای سازگاری
      $('#rescheduleModal').modal('show');
      const year = moment(selectedDate, 'YYYY-MM-DD').jYear();
      const month = moment(selectedDate, 'YYYY-MM-DD').jMonth() + 1;
      generateRescheduleCalendar(year, month);
      populateRescheduleSelectBoxes();
    });

  });
  $(document).ready(function() {
    $(".calendar-day").on("click", function() {
      let persianDate = $(this).data("date");
      let gregorianDate = moment(persianDate, 'jYYYY-jMM-jDD').format('YYYY-MM-DD');
      $("#selectedDate").val(gregorianDate);
      handleDateSelection(persianDate, localStorage.getItem('selectedClinicId'));
      $('#miniCalendarModal').modal('hide');
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
            $(".not-appointment").removeClass("d-none");
            $(".having-nobat-for-this-day").addClass("d-none");
            $("#workHoursContainer").empty();
            $("#updateWorkHours").hide();
          } else {
            getWorkHours(gregorianDate);
          }
          $(".selectDate_datepicker__xkZeS span.mx-1").text(persianDate);
          $('#miniCalendarModal').modal('hide');
          $('.my-form-control').val('');
        }
      });
    });
    $(document).on("click", ".block-user", function(e) {
      e.preventDefault();
      let row = $(this).closest("tr");
      let userId = $(this).data("user-id");
      let mobile = $(this).data("mobile");
      let userName = $(this).data("user-name");
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
              reason: result.value,
              blocked_at: moment().format('YYYY-MM-DD'),
              unblocked_at: null,
              selectedClinicId: localStorage.getItem('selectedClinicId')
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
    $(".dropdown-item").click(function(e) {
      e.preventDefault();
      let statusFilter = "";
      let filterType = $(this).attr("id");
      if (filterType === "scheduled-appointments") {
        statusFilter = "scheduled";
      } else if (filterType === "cancelled-appointments") {
        statusFilter = "cancelled";
      } else if (filterType === "attended-appointments") {
        statusFilter = "attended";
      } else if (filterType === "missed-appointments") {
        statusFilter = "missed";
      } else if (filterType === "all-appointments") {
        statusFilter = "";
      }
      showLoading();
      $.ajax({
        url: "{{ route('doctor.appointments.filter') }}",
        method: "GET",
        data: {
          status: statusFilter,
          selectedClinicId: localStorage.getItem('selectedClinicId'),
          page: 1
        },
        success: function(response) {
          hideLoading();
          let appointmentsTableBody = $(".table tbody");
          appointmentsTableBody.html("");
          if (response.success && response.appointments && response.appointments.length > 0) {
            response.appointments.forEach(function(appointment) {
              let patient = appointment.patient || {};
              let insurance = appointment.insurance ? appointment.insurance.name : 'ندارد';
              let appointmentDate = appointment.appointment_date ?
                moment(appointment.appointment_date).locale('fa').format('jYYYY/jMM/jDD') :
                'نامشخص';
              let appointmentHTML = `
                            <tr>
                                <td><input type="checkbox" class="row-checkbox"></td>
                                <td>${patient.first_name || 'نامشخص'} ${patient.last_name || ''}</td>
                                <td>${patient.mobile || 'نامشخص'}</td>
                                <td>${patient.national_code || 'نامشخص'}</td>
                                <td>${getPrescriptionStatus(appointment.status)}</td>
                                <td>${getPaymentStatus(appointment.payment_status)}</td>
                                <td>${insurance}</td>
                                <td>${appointmentDate}</td>
                                <td>${appointment.appointment_time}</td>
                                <td>
                                    <button class="${appointment.status === 'attended' ? 'text-primary' : 'btn-end-visit'}" 
                                            data-appointment-id="${appointment.id}" 
                                            ${appointment.status === 'attended' || appointment.status === 'cancelled' ? 'disabled' : ''}>
                                        ${appointment.status === 'attended' ? 'ویزیت شده' : 'پایان ویزیت'}
                                    </button>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown d-inline-block position-relative">
                                        <button class="flex items-center justify-center bg-white border border-gray-300 rounded-sm hover:bg-gray-100 transition-colors p-1 focus:outline-none dropdown-toggle custom-dropdown-trigger" type="button">
                                            <img src="{{ asset('dr-assets/icons/dots-vertical-svgrepo-com.svg') }}" width="20" height="20" alt="More options">
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end my-drp-left-0">
                                            <li class="${appointment.status === 'attended' || appointment.status === 'cancelled' ? 'disabled' : ''}">
                                                <a class="dropdown-item text-dark cancel-appointment" href="#" data-id="${appointment.id}">
                                                    لغو نوبت
                                                </a>
                                            </li>
                                            <li class="${appointment.status === 'attended' || appointment.status === 'cancelled' ? 'disabled' : ''}">
                                                <a class="dropdown-item text-dark move-appointment" href="#" data-date="${appointment.appointment_date}" data-id="${appointment.id}">
                                                    جابجایی نوبت
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-dark block-user" href="#" data-id="${appointment.id}" data-mobile="${patient.mobile}" data-user-id="${patient.id}" data-user-name="${patient.first_name + ' ' + patient.last_name}">
                                                    مسدود کردن کاربر
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>`;
              appointmentsTableBody.append(appointmentHTML);
            });
            renderPagination(response.pagination, function(newPage) {
              filterAppointments(statusFilter, newPage);
            });
          } else {
            appointmentsTableBody.html(`
                        <tr>
                                                      <td colspan="12" class="text-center py-3 text-muted">
                                هیچ نوبتی برای این فیلتر یافت نشد.
                            </td>
                        </tr>
                    `);
            renderPagination({
              current_page: 1,
              last_page: 1,
              per_page: 10,
              total: 0
            }, function() {});
          }
        },
        error: function(xhr) {
          hideLoading();
          appointmentsTableBody.html(`
                    <tr>
                        <td colspan="12" class="text-center py-3 text-danger">
                            خطا در دریافت نوبت‌ها: ${xhr.responseJSON?.error || 'خطای ناشناخته'}
                        </td>
                    </tr>
                `);
        }
      });
    });
  });

  function getSelectedAppointments() {
    let selected = [];
    let checkboxes = $('.row-checkbox:checked');
    checkboxes.each(function() {
      let checkbox = $(this);
      let row = checkbox.closest('tr');
      let button = row.find('[data-appointment-id]');
      let appointmentId = button.attr('data-appointment-id');
      if (!appointmentId) {
        console.error('No data-appointment-id found in row:', row);
        return;
      }
      // دریافت تاریخ شمسی و تبدیل به میلادی
      let persianDate = row.find('td:nth-child(8)').text().trim(); // فرمت: jYYYY/jMM/jDD
      let gregorianDate = moment.from(persianDate, 'fa', 'jYYYY/jMM/jDD').format('YYYY-MM-DD');
      if (!moment(gregorianDate, 'YYYY-MM-DD', true).isValid()) {
        console.error('Invalid date format for row:', persianDate);
        return;
      }
      selected.push({
        id: appointmentId,
        status: row.find('td:nth-child(5)').text().trim(),
        date: gregorianDate, // تاریخ به فرمت میلادی
        mobile: row.find('td:nth-child(3)').text().trim(),
        row: row
      });
    });
    return selected;
  }

  function filterAppointments(status, page = 1) {
    showLoading();
    $.ajax({
      url: "{{ route('doctor.appointments.filter') }}",
      method: "GET",
      data: {
        status: status,
        selectedClinicId: localStorage.getItem('selectedClinicId') || 'default',
        page: page
      },
      success: function(response) {
        hideLoading();
        let appointmentsTableBody = $(".table tbody");
        appointmentsTableBody.html("");
        if (response.success && response.appointments && response.appointments.length > 0) {
          response.appointments.forEach(function(appointment) {
            let patient = appointment.patient || {};
            let insurance = appointment.insurance ? appointment.insurance.name : 'ندارد';
            let appointmentDate = appointment.appointment_date ?
              moment(appointment.appointment_date).locale('fa').format('jYYYY/jMM/jDD') :
              'نامشخص';
            let appointmentHTML = `
                        <tr>
                            <td><input type="checkbox" class="row-checkbox"></td>
                            <td>${patient.first_name || 'نامشخص'} ${patient.last_name || ''}</td>
                            <td>${patient.mobile || 'نامشخص'}</td>
                            <td>${patient.national_code || 'نامشخص'}</td>
                            <td>${getPrescriptionStatus(appointment.status)}</td>
                            <td>${getPaymentStatus(appointment.payment_status)}</td>
                            <td>${insurance}</td>
                            <td>${appointmentDate}</td>
                            <td>${appointment.appointment_time}</td>
                            <td>
                                <button class="${appointment.status === 'attended' ? 'text-primary' : 'btn-end-visit'}" 
                                        data-appointment-id="${appointment.id}" 
                                        ${appointment.status === 'attended' || appointment.status === 'cancelled' ? 'disabled' : ''}>
                                    ${appointment.status === 'attended' ? 'ویزیت شده' : 'پایان ویزیت'}
                                </button>
                            </td>
                            <td class="text-center">
                                <div class="dropdown d-inline-block position-relative">
                                    <button class="flex items-center justify-center bg-white border border-gray-300 rounded-sm hover:bg-gray-100 transition-colors p-1 focus:outline-none dropdown-toggle custom-dropdown-trigger" type="button">
                                        <img src="{{ asset('dr-assets/icons/dots-vertical-svgrepo-com.svg') }}" width="20" height="20" alt="More options">
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end my-drp-left-0">
                                        <li class="${appointment.status === 'attended' || appointment.status === 'cancelled' ? 'disabled' : ''}">
                                            <a class="dropdown-item text-dark cancel-appointment" href="#" data-id="${appointment.id}">
                                                لغو نوبت
                                            </a>
                                        </li>
                                        <li class="${appointment.status === 'attended' || appointment.status === 'cancelled' ? 'disabled' : ''}">
                                            <a class="dropdown-item text-dark move-appointment" href="#" data-date="${appointment.appointment_date}" data-id="${appointment.id}">
                                                جابجایی نوبت
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-dark block-user" href="#" data-id="${appointment.id}" data-mobile="${patient.mobile}" data-user-id="${patient.id}" data-user-name="${patient.first_name + ' ' + patient.last_name}">
                                                مسدود کردن کاربر
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>`;
            appointmentsTableBody.append(appointmentHTML);
          });
          renderPagination(response.pagination, function(newPage) {
            filterAppointments(status, newPage);
          });
        } else {
          appointmentsTableBody.html(`
                    <tr>
                        <td colspan="12" class="text-center py-3 text-muted">
                            هیچ نوبتی برای این فیلتر یافت نشد.
                        </td>
                    </tr>
                `);
          renderPagination({
            current_page: 1,
            last_page: 1,
            per_page: 10,
            total: 0
          }, function() {});
        }
      },
      error: function(xhr) {
        hideLoading();
        appointmentsTableBody.html(`
                <tr>
                    <td colspan="12" class="text-center py-3 text-danger">
                        خطا در دریافت نوبت‌ها: ${xhr.responseJSON?.error || 'خطای ناشناخته'}
                    </td>
                </tr>
            `);
      }
    });
  }
  $('#block-users-btn').click(function() {
    let selected = getSelectedAppointments();
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
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
  $(document).on('click', '.btn-end-visit', function(e) {
    e.preventDefault();
    const appointmentId = $(this).data('appointment-id');
    $('#endVisitModalCenter').data('appointment-id', appointmentId);
    $('#endVisitModalCenter').modal('show');
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
          const row = endVisitButton.closest('tr');
          if (row.length === 0) {
            console.error('ردیف والد دکمه پیدا نشد:', appointmentId);
            Swal.fire('خطا', 'ردیف جدول پیدا نشد.', 'error');
            return;
          }
          // به‌روزرسانی وضعیت در جدول
          row.find('td:nth-child(5)').html(
            '<span class="fw-bold text-primary">ویزیت شده</span>');
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
              fetchAppointmentsCount()
              selected.forEach(app => {
                let row = app.row;
                row.find('td:nth-child(5)').html(
                  '<span class="fw-bold text-danger">لغو شده</span>'); // تغییر وضعیت
                row.find('.btn-end-visit').prop('disabled', true).addClass(
                  'text-muted'); // غیرفعال کردن دکمه پایان ویزیت
                row.find('.cancel-appointment').closest('li').addClass(
                  'disabled'); // غیرفعال کردن گزینه لغو نوبت
                row.find('.move-appointment').closest('li').addClass(
                  'disabled'); // غیرفعال کردن گزینه جابجایی نوبت
                row.find('.row-checkbox').prop('checked', false); // حذف تیک چک‌باکس
              });
              $('#select-all').prop('checked', false);
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
</script>

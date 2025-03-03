<script>
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

     $('.option-card').on('click', function () {
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
 });
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
      $("#userInfoModalCenter").modal('hide')
      let currentDate = moment().format('YYYY-MM-DD');
      let persianDate = moment(currentDate, 'YYYY-MM-DD').locale('fa').format('jYYYY/jMM/jDD');
      handleDateSelection(persianDate, localStorage.getItem('selectedClinicId'), $('.btn-filter-appointment-toggle').text().trim())
       loadAppointments(localStorage.getItem('selectedClinicId'), $('.btn-filter-appointment-toggle').text().trim())
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
 // با jQuery
 $(document).ready(function() {
    loadAppointments(localStorage.getItem('selectedClinicId'),$('.btn-filter-appointment-toggle').text().trim())
  $('.btn-filter-appointment-toggle').on('click', function() {
   $(this).toggleClass('active');
   $('.appointments-filter-drop-toggle').toggleClass('show');
  });
  // بستن دراپ با کلیک خارج از المنت
  $(document).on('click', function(event) {
   if (!$(event.target).closest('.dropdown-container').length) {
    $('.btn-filter-appointment-toggle').removeClass('active');
    $('.appointments-filter-drop-toggle').removeClass('show');
   }
  });
  // انتخاب آیتم‌های فیلتر (به جز آیتم آخر)
  $('.appointments-filter-drop-toggle li:not(:last-child)').on('click', function() {
   // حذف کلاس bg-light-blue از همه آیتم‌ها
   $('.appointments-filter-drop-toggle li:not(:last-child)').removeClass('bg-light-blue');
   // اضافه کردن کلاس bg-light-blue به آیتم کلیک شده
   $(this).addClass('bg-light-blue');
  });
 });
 $(document).ready(function() {
  $('#userInfoModalCenter').on('show.bs.modal', function(event) {
   var button = $(event.relatedTarget); // دکمه‌ای که مودال را باز کرده است

   var time = button.data('time'); // زمان
   var id = button.data('id'); // زمان
   var date = button.data('date'); // تاریخ
   var fullname = button.data('fullname'); // نام و نام خانوادگی
   var mobile = button.data('mobile'); // موبایل
   var tracking_code = button.data('tracking-code'); // موبایل
   var nationalCode = button.data('national-code'); // کد ملی
   var paymentStatus = button.data('payment-status'); // وضعیت پرداخت
   var appointmentType = button.data('appointment-type'); // نوع نوبت
   var centerName = button.data('center-name'); // نام مرکز
   var modal = $(this);

   modal.find('.time-card .text-black').text(time); // زمان
   modal.find('.date-card .text-black').text(date); // تاریخ
   modal.find('.fullname').text(fullname); // نام و نام خانوادگی
   modal.find('.mobile').text(mobile); // موبایل
   modal.find('.national-code').text(nationalCode); // کد ملی
   modal.find('.payment-status').text(paymentStatus); // وضعیت پرداخت
   modal.find('.appointment-type').text(appointmentType); // نوع نوبت
   modal.find('.center-name').text(centerName); // نام مرکز
   modal.find('.tracking-code').text(tracking_code); // نام مرکز
   modal.find('.cancel-appointment').attr('data-id', id); // نام مرکز
  });
 });
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
            success: function (response) {
                $('.my-appointments-lists-cards').empty();
                let appointmentHtml = '';
                if (response.appointments.length > 0) {
                    response.appointments.forEach(function (appointment) {
                        let reservedAt = appointment.reserved_at;
                        let formattedTime = 'نامشخص';
                        let formattedDate = 'نامشخص';
                        if (reservedAt && moment(reservedAt).isValid()) {
                            formattedTime = moment(reservedAt).format('HH:mm');
                            formattedDate = moment(reservedAt).locale('fa').format('dddd، jD jMMMM');
                        }
                        appointmentHtml += `
                    <div class="my-appointments-lists-card w-100 d-flex justify-content-between align-items-center p-3 my-border">
                        <div class="d-flex align-items-center gap-10 cursor-pointer" 
                             data-toggle="modal" 
                             data-target="#userInfoModalCenter"
                             data-id="${appointment.id}"
                             data-time="${formattedTime}"
                             data-tracking-code="${appointment.tracking_code}"
                             data-date="${formattedDate}"
                             data-fullname="${appointment.patient.first_name} ${appointment.patient.last_name}"
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
                                    ${appointment.patient.first_name} ${appointment.patient.last_name}
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
                            <span class="font-size-13 font-weight-bold">${appointment.patient.national_code}</span>
                        </div>
                        <div>
                            <button class="btn btn-outline-info" data-toggle="modal" data-target="#endVisitModalCenter">پایان ویزیت</button>
                        </div>
                    </div>
                `;
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
            error: function () {
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

 const datepickerSpan = document.getElementById('datepicker');
 const appointmentsContainer = document.querySelector('.my-appointments-lists-cards');
 // تنظیم تاریخ اولیه
 const initialDate = moment().locale('fa').format('jYYYY/jMM/jDD');
 datepickerSpan.textContent = initialDate;

  function handleDateSelection(selectedDate, selectedClinicId, filter_text) {
        datepickerSpan.textContent = selectedDate;
        let filterType = filter_text === 'کل نوبت ها' ? '' : (filter_text === 'نوبت های مطب' ? 'in_person' : 'online');
        $.ajax({
            url: "{{ route('dr.turn.my-appointments.by-date') }}",
            method: 'GET',
            data: {
                date: selectedDate,
                selectedClinicId: selectedClinicId,
                type: filterType,
            },
            success: function (response) {
                appointmentsContainer.innerHTML = '';
                if (response.appointments.length > 0) {
                    response.appointments.forEach(function (appointment) {
                        const appointmentHTML = createAppointmentHTML(appointment);
                        appointmentsContainer.insertAdjacentHTML('beforeend', appointmentHTML);
                    });
                } else {
                    appointmentsContainer.innerHTML = `
                <div class="container-fluid h-50 d-flex justify-content-center align-items-center align-self-center">
                    <div class="text-center">
                        <p class="font-weight-bold">
                            برای تاریخی که انتخاب کردید، در مرکز موردنظر هیچ نوبتی موجود نیست.
                        </p>
                    </div>
                </div>
            `;
                }
            },
            error: function () {
                alert('خطا در بارگذاری نوبت‌ها');
            }
        });
    }

    // تابع برای ایجاد HTML هر قرارملاقات
    function createAppointmentHTML(appointment) {
        const appointment_date = moment(appointment.appointment_date);
        const formattedTime = appointment_date.format('HH:mm');
        const formattedDate = appointment_date.locale('fa').format('dddd، jD jMMMM');
        return `
        <div class="my-appointments-lists-card w-100 d-flex justify-content-between align-items-center p-3 my-border">
            <div class="d-flex align-items-center gap-10 cursor-pointer" 
                data-toggle="modal" 
                data-target="#userInfoModalCenter"
                data-id="${appointment.id}"
                data-time="${formattedTime}"
                data-tracking-code="${appointment.tracking_code}"
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
                <button class="btn btn-outline-info" data-toggle="modal" data-target="#endVisitModalCenter">
                    پایان ویزیت
                </button>
            </div>
        </div>
    `;
    }
 document.addEventListener('DOMContentLoaded', function() {
  // توابع کمکی برای تبدیل وضعیت‌ها

  // اضافه کردن رویداد به روزهای تقویم
  const calendarBody = document.getElementById('calendar-body');
  // رویداد کلیک سراسری روی calendarBody
  calendarBody.addEventListener('click', function(e) {
   const target = e.target.closest('.calendar-day'); // پیدا کردن والد calendar-day
   if (target && !target.classList.contains('empty')) {
    const selectedDate = target.getAttribute('data-date');
    handleDateSelection(selectedDate, localStorage.getItem('selectedClinicId'), $('.btn-filter-appointment-toggle').text().trim());
    $('#calendarModal').modal('hide');
   }
  });
 });
let selectedCurrentTextDropToggle = "";
 $(document).ready(function() {
  let selectedDate = $('#datepicker').text().trim();

  $('.appointments-filter-drop-toggle li').on('click', function() {
   let filterType = $(this).text().trim();
    selectedCurrentTextDropToggle = filterType
   if ($(this).find('span').text().includes('فعالسازی نوبت دهی مطب')) {
    window.location.href = 'آدرس_مشخص_شده_توسط_کاربر';
    return;
   }

   let appointmentsContainer = $('#appointment-lists-container');
   let loadingSpinner = $(
    '<div class="text-center w-100"><div class="spinner-border text-primary" role="status"><span class="sr-only">در حال بارگذاری...</span></div></div>'
   );

   appointmentsContainer.empty().append(loadingSpinner); // نمایش لودینگ

   $('.btn-filter-appointment-toggle span.text-btn-425').text(filterType);

   $.ajax({
    url: "{{ route('dr.turn.filter-appointments') }}",
    type: "GET",
    data: {
     type: filterType === 'کل نوبت ها' ? '' : (filterType === 'نوبت های مطب' ? 'in_person' : 'online'),
     date: selectedDate,
     selectedClinicId: localStorage.getItem('selectedClinicId')
    },

    success: function(response) {


     appointmentsContainer.empty(); // پاک کردن لودینگ بعد از دریافت داده‌ها

     if (response.appointments.length > 0) {
      // ایجاد HTML برای هر نوبت
      response.appointments.forEach(function(appointment) {
       // تبدیل تاریخ با احتیاط
       const appointment_date = moment(appointment.appointment_date);
       const formattedTime = appointment_date.format('HH:mm');
       const formattedDate = appointment_date.locale('fa').format('dddd، jD jMMMM');
       let appointmentHTML = `
                  <div class="my-appointments-lists-card w-100 d-flex justify-content-between align-items-center p-3 my-border">
                                <div class="d-flex align-items-center gap-10 cursor-pointer"
                                    data-toggle="modal"
                                    data-target="#userInfoModalCenter"
                                    data-time="${formattedTime}"
                                    data-tracking-code="${appointment.tracking_code}"
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
                                    <button class="btn btn-outline-info" data-toggle="modal" data-target="#endVisitModalCenter">
                                        پایان ویزیت
                                    </button>
                                </div>
                            </div>`;

       appointmentsContainer.append(appointmentHTML);

      });
     } else {
      appointmentsContainer.html('<div class="text-center w-100">نوبتی یافت نشد.</div>');
     }
    },
    complete: function() {
     $('.appointments-filter-drop-toggle').removeClass('show'); // بستن منوی فیلتر
    },
    error: function() {
     appointmentsContainer.html('<div class="text-center w-100 text-danger">خطا در بارگذاری نوبت‌ها.</div>');
    }
   });
  });

  $('#calendar-body').on('click', '.calendar-day', function() {
   if (!$(this).hasClass('empty')) {
    selectedDate = $(this).data('date');
    $('#datepicker').text(selectedDate);
   }
  });
 });
</script>

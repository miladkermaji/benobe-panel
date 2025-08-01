@extends('mc.panel.layouts.master')
@section('styles')

  <link type="text/css" href="{{ asset('mc-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('mc-assets/panel/css/turn/schedule/scheduleSetting/vacation.css') }}"
    rel="stylesheet" />
@endsection
@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection
@section('content')
@section('bread-crumb-title', ' اعلام مرخصی')
@include('mc.panel.my-tools.loader-btn')
<div class="vacation-content w-100 d-flex justify-content-center mt-4">
  <div class="vacation-wrapper-content p-3">
    <div class="">
      <div class="muirtl-60hgb7">
        <div class="" role="alert">
          <div class="">
            <p class="fw-bold text-dark">ثبت مرخصی</p>
            <p class="alert alert-warning font-size-13 fw-bold mt-2">شما می‌توانید برای ساعاتی که طبق ساعت کاری
              خود حضور ندارید، مرخصی اعمال کنید.</p>
            <div class="w-100">
              <button data-bs-toggle="modal" data-bs-target="#exampleModalCenterAddVacation"
                class="h-50 w-100 btn btn-outline-primary" tabindex="0" type="button" id=":r18:">اضافه کردن
                مرخصی<span class=""></span>
              </button>
              <div class="modal fade " id="exampleModalCenterAddVacation" tabindex="-1" role="dialog"
                aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered " role="document">
                  <div class="modal-content border-radius-6">
                    <div class="modal-header">
                      <h5 class="modal-title" id="exampleModalLongTitle"> ثبت مرخصی </h5>
                      <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      <form id="add-vacation-form" method="POST">
                        @csrf
                        <div class="position-relative">
                          <label class="label-top-input-special-takhasos">تاریخ :</label>
                          <input id="vacation-date" data-jdp="" type="text" name="date"
                            class="form-control h-50" placeholder="1403/05/02">
                        </div>
                        <div class="d-flex justify-content-between gap-4 mt-3">
                          <div class="mt-3 position-relative timepicker-ui w-100">
                            <label class="label-top-input-special-takhasos"> از ساعت:</label>
                            <input data-timepicker type="text" name="start_time" id="start-time"
                              class="form-control w-100 h-50 timepicker-ui-input" style="width: 100% !important">
                          </div>
                          <div class="mt-3 position-relative timepicker-ui w-100">
                            <label class="label-top-input-special-takhasos"> تا ساعت:</label>
                            <input data-timepicker type="text" name="end_time" id="end-time"
                              class="form-control w-100 h-50 timepicker-ui-input" style="width: 100% !important">
                          </div>
                        </div>
                        <div class="form-check mt-3">
                          <input type="checkbox" class="form-check-input" id="full-day-vacation" name="is_full_day"
                            value="1">
                          <label class="form-check-label" for="full-day-vacation">ثبت مرخصی برای تمام روز</label>
                        </div>

                        <div class="w-100">
                          <button type="submit"
                            class="btn my-btn-primary w-100 h-50 mt-3 d-flex justify-content-center align-items-center">
                            <span class="button_text">ثبت مرخصی</span>
                            <div class="loader"></div>
                          </button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="">
        <div class="d-flex flex-column w-100 gap-4 mt-3">
          <div class="d-flex align-items-center justify-content-between w-100 gap-4">
            <div>
              <span class="text-dark fw-bold"> مرخصی‌های ثبت‌شده: </span>
            </div>
            <div>
              <form action="" method="post">
                <div class="d-flex gap-4">
                  <div>
                    <select name="year" id="filter-year" class="form-control h-50">
                      <option value="1403">1403</option>
                      <option value="1404">1404</option>
                      <option value="1405">1405</option>
                      <option value="1406">1406</option>
                    </select>
                  </div>
                  <div>
                    <select id="filter-month" class="form-control h-50">
                      <option value="01">فروردین</option>
                      <option value="02">اردیبهشت</option>
                      <option value="03">خرداد</option>
                      <option value="04">تیر</option>
                      <option value="05">مرداد</option>
                      <option value="06">شهریور</option>
                      <option value="07">مهر</option>
                      <option value="08">آبان</option>
                      <option value="09">آذر</option>
                      <option value="10">دی</option>
                      <option value="11">بهمن</option>
                      <option value="12">اسفند</option>
                    </select>
                  </div>
                </div>
              </form>
            </div>
          </div>
          <div class="vacation-list d-flex justify-content-center flex-wrap ">
            <div class="nothing-vacation w-100 d-flex justify-content-center ">
              <div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="exampleModalCenterEditVacation" tabindex="-1" role="dialog"
  aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-radius-6">
      <div class="modal-header">
        <h5 class="modal-title">ویرایش مرخصی</h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="edit-vacation-form" method="POST">
          @csrf
          <input type="hidden" id="edit-vacation-id" name="id">
          <div class="mt-3 position-relative">
            <label class="label-top-input-special-takhasos">تاریخ:</label>
            <input id="edit-date" type="text" name="date" class="form-control h-50" placeholder="1403/05/02"
              data-jdp>
          </div>
          <div class="d-flex justify-content-between gap-4 mt-3">
            <div class="mt-3 position-relative timepicker-ui w-100">
              <label class="label-top-input-special-takhasos">از ساعت:</label>
              <input data-timepicker id="edit-start-time" type="text" name="start_time"
                class="form-control w-100 h-50" style="width: 100% !important">
            </div>
            <div class="mt-3 position-relative timepicker-ui w-100">
              <label class="label-top-input-special-takhasos">تا ساعت:</label>
              <input data-timepicker id="edit-end-time" type="text" name="end_time"
                class="form-control w-100 h-50" style="width: 100% !important">
            </div>
          </div>
          <div class="form-check mt-3">
            <input type="checkbox" id="edit-full-day-vacation" value="1" name="is_full_day"
              class="form-check-input">
            <label class="form-check-label" for="edit-full-day-vacation">تمام روز</label>
          </div>
          <div class="w-100">
            <button type="submit"
              class="btn my-btn-primary w-100 h-50 mt-3 d-flex justify-content-center align-items-center">
              <span class="button_text">ذخیره</span>
              <div class="loader"></div>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script src="{{ asset('mc-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
<script src="{{ asset('mc-assets/panel/js/mc-panel.js') }}"></script>
<script src="{{ asset('mc-assets/panel/js/turn/scehedule/sheduleSetting/workhours/workhours.js') }}"></script>
<script src="{{ asset('mc-assets/panel/js/turn/scehedule/sheduleSetting/vacation/vacation.js') }}"></script>
<script>
  var appointmentsSearchUrl = "{{ route('search.appointments') }}";
  var updateStatusAppointmentUrl = "{{ route('updateStatusAppointment', ':id') }}";
</script>
<script>
  $(document).ready(function() {


    const now = new Date();
    const jalaliMoment = moment(now).locale('fa');
    const currentYear = jalaliMoment.jYear().toString();
    const currentMonth = (jalaliMoment.jMonth() + 1).toString().padStart(2, '0');

    $('#filter-year').val(currentYear);
    $('#filter-month').val(currentMonth);

    loadVacations(currentYear, currentMonth);

    $('#filter-year, #filter-month').on('change', function() {
      const selectedYear = $('#filter-year').val();
      const selectedMonth = $('#filter-month').val();
      loadVacations(selectedYear, selectedMonth);
    });
  });

  function loadVacations(year, month) {
    const data = {
      selectedClinicId: localStorage.getItem('selectedClinicId') ?? 'default',
      year: year,
      month: month
    };

    $.ajax({
      url: "{{ route('mc-vacation') }}",
      method: "GET",
      data: data,
      success: function(response) {
        let html = '';
        if (response.vacations.length > 0) {
          response.vacations.forEach((vacation) => {
            const duration = calculateDuration(vacation.start_time, vacation.end_time);
            const jalaliDate = moment(vacation.date, 'YYYY-MM-DD').locale('fa').format('jYYYY/jMM/jDD');
            html += `
              <div class="has-list-vacation w-100 bg-white d-flex justify-content-between align-items-center p-2 h-24">
                <div class="d-flex flex-column gap-4">
                  <span class="text-dark fw-bold d-block font-size-13">
                    ${jalaliDate} - ${vacation.start_time || '00:00'} الی ${vacation.end_time || '23:59'}
                  </span>
                  <span class="text-start text-black font-size-13">مدت زمان: ${duration}</span>
                </div>
                <div class="d-flex flex-column align-items-center gap-4">
                  <button class="btn btn-light btn-sm rounded-circle edit-vacation-btn" data-id="${vacation.id}">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"
                      class="cursor-pointer w-5 h-[1.1rem] text-[#000000]">
                      <path
                        d="M11.0002 2.99999L4.9003 9.0998C4.12168 9.87841 3.73237 10.2677 3.46447 10.7409C3.19657 11.214 3.06304 11.7482 2.79598 12.8164L2.66675 13.3333L3.1837 13.2041C4.25194 12.937 4.78606 12.8035 5.25921 12.5356C5.73237 12.2677 6.12167 11.8784 6.90027 11.0998L6.90027 11.0998L13.0001 4.99996C13.5524 4.44769 13.5524 3.5523 13.0001 3.00002C12.4479 2.44773 11.5525 2.44771 11.0002 2.99999Z"
                        fill="#E9CF4A"></path>
                      <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M11.5305 3.53023C11.7899 3.27084 12.2104 3.27085 12.4698 3.53024C12.7292 3.78962 12.7292 4.21015 12.4698 4.46953L12.0074 4.93197C11.5074 4.89584 11.1042 4.49263 11.068 3.9927L11.5305 3.53023ZM9.89585 5.16487L5.43064 9.63004C4.62387 10.4368 4.32328 10.7462 4.11714 11.1103C3.96155 11.3851 3.86035 11.69 3.7011 12.2989C4.31001 12.1396 4.61491 12.0384 4.8897 11.8829C5.25379 11.6767 5.5632 11.3761 6.36996 10.5694L10.8352 6.10417C10.445 5.88042 10.1196 5.55501 9.89585 5.16487ZM13.5305 2.4696C12.6853 1.62441 11.315 1.62439 10.4699 2.46956L4.36998 8.56937L4.29644 8.6429C3.58808 9.35097 3.1306 9.80827 2.81184 10.3712C2.49308 10.9342 2.33633 11.5618 2.0936 12.5335L2.06839 12.6344L1.93916 13.1513C1.87526 13.4069 1.95015 13.6773 2.13643 13.8636C2.32272 14.0498 2.59308 14.1247 2.84867 14.0608L3.36562 13.9316L3.46651 13.9064C4.43822 13.6637 5.06578 13.5069 5.62875 13.1882C6.19172 12.8694 6.64902 12.4119 7.35709 11.7036L7.35709 11.7036L7.43062 11.63L13.5305 5.53019C14.3756 4.68503 14.3756 3.31478 13.5305 2.4696Z"
                        fill="#22282F"></path>
                    </svg>
                  </button>
                  <button class="btn btn-light btn-sm rounded-circle delete-vacation-btn" data-id="${vacation.id}">
                    <svg width="23" height="23" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                      class="cursor-pointer w-5 hover:!text-red-500">
                      <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M10.4062 2.25L10.4415 2.25H13.5585L13.5938 2.25C13.9112 2.24996 14.2092 2.24992 14.459 2.27844C14.7371 2.31019 15.0296 2.38361 15.3025 2.58033C15.5754 2.77704 15.7375 3.03124 15.8556 3.28508C15.9616 3.51299 16.0559 3.79574 16.1562 4.09685L16.1562 4.09687L16.1562 4.0969L16.1674 4.13037L16.5406 5.25H19H21C21.4142 5.25 21.75 5.58579 21.75 6C21.75 6.41421 21.4142 6.75 21 6.75H19.7017L19.1217 15.449L19.1182 15.5016C19.0327 16.7844 18.9637 17.8205 18.8017 18.6336C18.6333 19.4789 18.3469 20.185 17.7553 20.7384C17.1637 21.2919 16.4401 21.5307 15.5855 21.6425C14.7634 21.75 13.725 21.75 12.4394 21.75H12.3867H11.6133H11.5606C10.275 21.75 9.23655 21.75 8.41451 21.6425C7.55986 21.5307 6.83631 21.2919 6.24472 20.7384C5.65312 20.185 5.3667 19.4789 5.19831 18.6336C5.03633 17.8205 4.96727 16.7844 4.88178 15.5016L4.87827 15.449L4.29834 6.75H3C2.58579 6.75 2.25 6.41421 2.25 6C2.25 5.58579 2.58579 5.25 3 5.25H5H7.45943L7.83264 4.13037L7.8438 4.09688L7.84381 4.09686C7.94414 3.79575 8.03835 3.51299 8.14438 3.28508C8.26246 3.03124 8.42459 2.77704 8.69752 2.58033C8.97045 2.38361 9.26287 2.31019 9.54102 2.27844C9.79077 2.24992 10.0888 2.24996 10.4062 2.25ZM9.04057 5.25H14.9594L14.7443 4.60472C14.6289 4.25832 14.5611 4.05863 14.4956 3.91778C14.466 3.85423 14.4457 3.82281 14.4348 3.80824C14.4298 3.80149 14.427 3.79862 14.4264 3.79801L14.4254 3.79719L14.4243 3.79654C14.4236 3.79616 14.42 3.79439 14.412 3.79174C14.3947 3.78604 14.3585 3.7767 14.2888 3.76875C14.1345 3.75113 13.9236 3.75 13.5585 3.75H10.4415C10.0764 3.75 9.86551 3.75113 9.71117 3.76875C9.64154 3.7767 9.60531 3.78604 9.58804 3.79174C9.58005 3.79439 9.57643 3.79616 9.57566 3.79654L9.57458 3.79719L9.57363 3.79801C9.57302 3.79862 9.57019 3.80149 9.56516 3.80824C9.55428 3.82281 9.53397 3.85423 9.50441 3.91778C9.43889 4.05863 9.37113 4.25832 9.25566 4.60472L9.04057 5.25ZM5.80166 6.75L6.37495 15.3492C6.4648 16.6971 6.52883 17.6349 6.6694 18.3405C6.80575 19.025 6.99608 19.3873 7.2695 19.6431C7.54291 19.8988 7.91707 20.0647 8.60907 20.1552C9.32247 20.2485 10.2625 20.25 11.6133 20.25H12.3867C13.7375 20.25 14.6775 20.2485 15.3909 20.1552C16.0829 20.0647 16.4571 19.8988 16.7305 19.6431C17.0039 19.3873 17.1943 19.025 17.3306 18.3405C17.4712 17.6349 17.5352 16.6971 17.6251 15.3492L18.1983 6.75H16H8H5.80166ZM10 9.25C10.4142 9.25 10.75 9.58579 10.75 10V17C10.75 17.4142 10.4142 17.75 10 17.75C9.58579 17.75 9.25 17.4142 9.25 17V10C9.25 9.58579 9.58579 9.25 10 9.25ZM14.75that's right75 10C14.75 9.58579 14.4142 9.25 14 9.25C13.5858 9.25 13.25 9.58579 13.25 10V14C13.25 14.4142 13.5858 14.75 14 14.75C14.4142 14.75 14.75 14.4142 14.75 14V10Z"
                        fill="#000"></path>
                    </svg>
                  </button>
                </div>
              </div>`;
          });
        } else {
          html = '<span class="fw-bold text-dark mt-2">مرخصی وجود ندارد</span>';
        }
        $('.vacation-list').html(html);
      },
      error: function(xhr) {
        toastr.error("خطا در بارگذاری مرخصی‌ها!");
      }
    });
  }

  $('#full-day-vacation').on('change', function() {
    if ($(this).is(':checked')) {
      $('#start-time, #end-time').prop('disabled', true).val('');
      $(this).val(1);
    } else {
      $('#start-time, #end-time').prop('disabled', false);
      $(this).val(0);
    }
  });

  $('#edit-full-day-vacation').on('change', function() {
    if ($(this).is(':checked')) {
      $('#edit-start-time, #edit-end-time').prop('disabled', true).val('');
      $(this).val(1);
    } else {
      $('#edit-start-time, #edit-end-time').prop('disabled', false);
      $(this).val(0);
    }
  });

  const modal = $('#exampleModalCenterAddVacation');
  modal.on('show.bs.modal', function() {
    $("#full-day-vacation").prop('checked', false);
    $('#start-time, #end-time').prop('disabled', false);
  });

  $('#add-vacation-form').on('submit', function(e) {
    e.preventDefault();
    const formData = $(this).serializeArray();
    const button = $(this).find('button[type="submit"]');
    const loader = button.find('.loader');
    const buttonText = button.find('.button_text');

    buttonText.css({
      'display': 'none'
    });
    loader.css({
      'display': 'block'
    });

    formData.push({
      name: 'selectedClinicId',
      value: localStorage.getItem('selectedClinicId') ?? 'default'
    });

    $.ajax({
      url: "{{ route('doctor.vacation.store') }}",
      method: "POST",
      data: $.param(formData),
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
      },
      success: function(response) {
        toastr.success(response.message);
        $('#exampleModalCenterAddVacation').modal('hide');
        loadVacations($('#filter-year').val(), $('#filter-month').val());
      },
      error: function(xhr) {
        const response = xhr.responseJSON;
        if (xhr.status === 422 && response.errors) {
          for (const field in response.errors) {
            toastr.error(response.errors[field][0]);
          }
        } else if (response && response.message) {
          toastr.error(response.message); // نمایش پیام خطای سرور مثل "تداخل زمانی"
        } else {
          toastr.error("خطا در ثبت اطلاعات!");
        }
      },
      complete: function() {
        buttonText.css({
          'display': 'block'
        });
        loader.css({
          'display': 'none'
        });
      }
    });
  });

  $(document).on('click', '.edit-vacation-btn', function() {
    const vacationId = $(this).data('id');
    $.ajax({
      url: "{{ route('doctor.vacation.edit', ':id') }}".replace(':id', vacationId),
      method: "GET",
      data: {
        selectedClinicId: localStorage.getItem('selectedClinicId')
      },
      success: function(response) {
        const vacation = response.vacation;
        const jalaliDate = moment(vacation.date, 'YYYY-MM-DD').locale('fa').format('jYYYY/jMM/jDD');

        $('#edit-vacation-id').val(vacation.id);
        $('#edit-date').val(jalaliDate);
        $('#edit-start-time').val(vacation.start_time);
        $('#edit-end-time').val(vacation.end_time);
        $('#edit-full-day-vacation').prop('checked', vacation.is_full_day);
        if (vacation.is_full_day) {
          $('#edit-start-time, #edit-end-time').prop('disabled', true);
        } else {
          $('#edit-start-time, #edit-end-time').prop('disabled', false);
        }
        $('#exampleModalCenterEditVacation').modal('show');
      },
      error: function(xhr) {
        toastr.error("خطا در بارگذاری اطلاعات!");
      }
    });
  });

  $('#edit-vacation-form').on('submit', function(e) {
    e.preventDefault();
    const vacationId = $('#edit-vacation-id').val();
    const formData = $(this).serializeArray();

    formData.push({
      name: 'selectedClinicId',
      value: localStorage.getItem('selectedClinicId') ?? 'default'
    });

    const button = $(this).find('button[type="submit"]');
    const loader = button.find('.loader');
    const buttonText = button.find('.button_text');

    buttonText.hide();
    loader.show();

    $.ajax({
      url: "{{ route('doctor.vacation.update', ':id') }}".replace(':id', vacationId),
      method: "POST",
      data: $.param(formData),
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(response) {
        if (response.success) {
          toastr.success(response.message);
          $('#exampleModalCenterEditVacation').modal('hide');
          loadVacations($('#filter-year').val(), $('#filter-month').val());
        } else {
          toastr.error(response.message);
        }
      },
      error: function(xhr) {
        const response = xhr.responseJSON;
        if (xhr.status === 422 && response.errors) {
          for (const field in response.errors) {
            toastr.error(response.errors[field][0]);
          }
        } else if (response && response.message) {
          toastr.error(response.message); // نمایش پیام خطای سرور مثل "تداخل زمانی"
        } else {
          toastr.error("خطا در ذخیره تغییرات!");
        }
      },
      complete: function() {
        buttonText.show();
        loader.hide();
      }
    });
  });

  $(document).on('click', '.delete-vacation-btn', function() {
    const vacationId = $(this).data('id');
    const selectedClinicId = localStorage.getItem('selectedClinicId') ?? 'default';

    Swal.fire({
      title: "آیا مطمئن هستید؟",
      text: "این عمل قابل بازگشت نیست!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "بله، حذف کن!",
      cancelButtonText: "لغو",
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: "{{ route('doctor.vacation.destroy', ':id') }}".replace(':id', vacationId),
          method: "DELETE",
          data: {
            selectedClinicId: selectedClinicId
          },
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
          },
          success: function(response) {
            if (response.success) {
              toastr.success(response.message);
              loadVacations($('#filter-year').val(), $('#filter-month').val());
            } else {
              toastr.error(response.message);
            }
          },
          error: function(xhr) {
            const response = xhr.responseJSON;
            if (response && response.message) {
              toastr.error(response.message);
            } else {
              toastr.error("خطا در حذف مرخصی!");
            }
          }
        });
      }
    });
  });

  function calculateDuration(start, end) {
    if (!start || !end) return '---';

    const [startHours, startMinutes] = start.split(':').map(Number);
    const [endHours, endMinutes] = end.split(':').map(Number);

    const startDate = new Date(0, 0, 0, startHours, startMinutes);
    const endDate = new Date(0, 0, 0, endHours, endMinutes);

    const diffMs = endDate - startDate;
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
    const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));

    if (diffHours > 0 && diffMinutes === 0) {
      return `${diffHours} ساعت`;
    } else if (diffHours === 0 && diffMinutes > 0) {
      return `${diffMinutes} دقیقه`;
    } else {
      return `${diffHours} ساعت و ${diffMinutes} دقیقه`;
    }
  }
</script>
@endsection

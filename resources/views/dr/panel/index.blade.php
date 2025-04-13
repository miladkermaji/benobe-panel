@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />

@endsection
@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection
@section('content')
@section('bread-crumb-title', 'لیست بیماران')
<div class="d-flex flex-column justify-content-center p-3 top-panel-bg mt-2">
  <div class="top-details-sicks-cards">
    <div class="d-flex  justify-content-center  gap-20 top-s-a-wrapper">
      <div class="rounded-lg d-flex justify-content-center align-items-center p-3 bg-light-blue">
        <img src="{{ asset('dr-assets/icons/count.svg') }}" alt="" srcset="">
        <span class="font-weight-bold mr-2 ml-2 text-dark">تعداد بیماران امروز</span><span
          class="font-medium">{{ $totalPatientsToday }} بیمار</span>
      </div>
      <div class="rounded-lg d-flex justify-content-center align-items-center p-3 bg-light-blue">
        <img src="{{ asset('dr-assets/icons/dashboard-tick.svg') }}" alt="" srcset="">
        <span class="font-weight-bold mr-2 ml-2 text-dark">بیماران ویزیت شده</span><span
          class="font-medium">{{ $visitedPatients }} بیمار</span>
      </div>
      <div class="rounded-lg d-flex justify-content-center align-items-center p-3 bg-light-blue">
        <img src="{{ asset('dr-assets/icons/dashboard-timer.svg') }}" alt="" srcset="">
        <span class="font-weight-bold mr-2 ml-2 text-dark">بیماران باقی مانده</span><span
          class="font-medium">{{ $remainingPatients }} بیمار</span>
      </div>
    </div>
  </div>
  <div class="d-flex justify-content-center top-s-wrapper">
    <div class="calendar-and-add-sick-section p-3">
      <div class="d-flex justify-content-between gap-10 align-items-center c-a-wrapper">
        <div>
          <div class="turning_selectDate__MLRSb">
            <button
              class="selectDate_datepicker__xkZeS cursor-pointer text-center h-50 bg-light-blue d-flex justify-content-center align-items-center"
              data-toggle="modal" data-target="#miniCalendarModal">
              <div class="d-flex align-items-center">
                <span class="mx-1"></span>
                <img src="{{ asset('dr-assets/icons/calendar.svg') }}" alt="" srcset="">
              </div>
            </button>
            <div class="modal fade " id="miniCalendarModal" tabindex="-1" role="dialog"
              aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered " role="document">
                <div class="modal-content border-radius-6">
                  <div class="my-modal-header">
                    <div>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                  </div>
                  <div class="modal-body">
                    <x-jalali-calendar />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div>
          <div class="turning_filterWrapper__2cOOi">
            <div class="turning_search-wrapper__loGVc">
              <input type="text" class="my-form-control" placeholder="نام بیمار، شماره موبایل یا کد ملی ...">
            </div>
          </div>
        </div>
        <div class="btn-425-left">
          <button class="btn btn-primary h-50 fs-13" data-toggle="modal" data-target="#exampleModalCenterAddSick">افزودن
            بیمار</button>
          </button>
          <!-- Modal -->
          <div class="modal fade " id="exampleModalCenterAddSick" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered " role="document">
              <div class="modal-content border-radius-6">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLongTitle">افزودن بیمار </h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <div>
                    <form action="" method="post">
                      <input type="text" class="my-form-control-light w-100" placeholder="کدملی/کداتباع">
                      <div class="mt-2">
                        <a class="text-decoration-none text-primary font-bold" href="#" data-toggle="modal"
                          data-target="#exampleModalCenterPaziresh">پذیرش
                          از مسیر ارجاع</a>
                      </div>
                      <div class="d-flex mt-2 gap-20">
                        <button class="btn btn-primary w-50 h-50">تجویز نسخه</button>
                        <button class="btn btn-outline-info w-50 h-50">ثبت ویزیت</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal fade " id="exampleModalCenterPaziresh" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered " role="document">
              <div class="modal-content border-radius-6">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLongTitle"> ارجاع </h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                  <div>
                    <form action="" method="post">
                      <input type="text" class="my-form-control-light w-100" placeholder="کدملی/کداتباع بیمار">
                      <input type="text" class="my-form-control-light w-100 mt-3" placeholder="کد پیگیری">
                      <div class="mt-3">
                        <button class="btn btn-primary w-100 h-50">ثبت</button>
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
  </div>
</div>
{{-- here put calender --}}
<x-jalali-calendar-row />
<div class="sicks-content h-100 mt-2 w-100  position-relative border">
  <div>
    <div class="table-responsive position-relative top-table w-100">
      <table class="table w-100 text-sm text-center table-bordered">
        <thead class="">
          <tr>
            <th>
              <input type="checkbox" id="select-all-row" disabled>
            </th>
            <th scope="col" class="px-6 py-3">نام بیمار</th>
            <th scope="col" class="px-6 py-3">شماره‌موبایل</th>
            <th scope="col" class="px-6 py-3">کد ملی</th>
            <th scope="col" class="px-6 py-3">وضعیت نوبت</th>
            <th scope="col" class="px-6 py-3">وضعیت پرداخت</th>
            <th scope="col" class="px-6 py-3">بیمه</th>
            <th scope="col" class="px-6 py-3">تاریخ نوبت</th>
            <th scope="col" class="px-6 py-3">زمان نوبت</th>
            <th scope="col" class="px-6 py-3">پایان ویزیت</th>
            <th scope="col" class="px-6 py-3">عملیات</th>
          </tr>
        </thead>
        <tbody></tbody>

      </table>
    </div>
  </div>
  <div class="d-flex justify-content-start gap-10 nobat-option">
    <div class="d-flex align-items-center m-2 gap-4">
      <div class="turning_filterWrapper__2cOOi">
        <div class="dropdown">
          <button
            class="flex items-center justify-center bg-transparent border border-gray-300 rounded-sm hover:bg-gray-100 transition-colors bg-light w-12 h-8 focus:outline-none dropdown-toggle"
            type="button" id="dropdownMenuButton" aria-haspopup="true" aria-expanded="true">
            <!-- چک‌باکس -->
            <input class="w-4 h-4 text-blue-600 border-none focus:ring-0 bg-transparent cursor-pointer"
              type="checkbox" value="" id="select-all">
            <!-- فاصله بین چک‌باکس و آیکون -->
            <span class="w-2"></span>
          </button>
          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <ul class="dropdown-list" style="list-style-type: none; padding: 0; margin: 0;">
              <li>
                <a href="#" id="all-appointments" class="dropdown-item">همه نوبت‌ها</a>
              </li>
              <li>
                <a href="#" id="scheduled-appointments" class="dropdown-item">در انتظار</a>
              </li>
              <li>
                <a href="#" id="cancelled-appointments" class="dropdown-item">لغو شده</a>
              </li>
              <li>
                <a href="#" id="attended-appointments" class="dropdown-item">ویزیت شده</a>
              </li>


            </ul>

          </div>
        </div>
      </div>
      <script>
        const dropdownButton = document.getElementById('dropdownMenuButton');
        const dropdownMenu = document.querySelector('.dropdown-menu');
        const dropdownItems = document.querySelectorAll('.dropdown-item');
        dropdownButton.addEventListener('click', function() {
          dropdownMenu.classList.toggle('show');
        });
        const checkBox = document.getElementById("select-all").addEventListener('click', function() {
          event.stopPropagation();

        })
        dropdownItems.forEach(item => {
          item.addEventListener('click', function(event) {
            event.stopPropagation();
            dropdownMenu.classList.remove('show');
          });
        });
        document.addEventListener('click', function(event) {
          if (!dropdownButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
            dropdownMenu.classList.remove('show');
          }
        });
      </script>
      <button id="cancel-appointments-btn"
        class="btn btn-light h-50 fs-13 d-flex align-items-center justify-content-center">

        <img src="{{ asset('dr-assets/icons/cancle-appointment.svg') }}" alt="" srcset="">
        <span class="d-none d-md-block mx-1">لغو </span>
      </button>
      <button id="move-appointments-btn"
        class="btn btn-light h-50 fs-13 d-flex align-items-center justify-content-center">

        <img src="{{ asset('dr-assets/icons/rescheule-appointment.svg') }}" alt="" srcset="">

        <span class="d-none d-md-block mx-1">جابجایی </span>
      </button>
      <button id="block-users-btn" class="btn btn-light h-50 fs-13 d-flex align-items-center justify-content-center">

        <img src="{{ asset('dr-assets/icons/block-user.svg') }}" alt="" srcset="">

        <span class="d-none d-md-block mx-1">مسدود کردن </span>
      </button>
    </div>
  </div>
</div>
<!-- بخش پیجینیشن -->
  <div class="pagination-container mt-3 d-flex justify-content-center">
    <nav aria-label="Page navigation">
      <ul class="pagination" id="pagination-links"></ul>
    </nav>
  </div>
</div>
<div class="modal fade" id="activation-modal" tabindex="-1" role="dialog"
  aria-labelledby="activation-modal-label" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-radius-6">
      <div class="modal-header">
        <h5 class="modal-title" id="activation-modal-label">فعالسازی نوبت دهی</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="flex flex-col"><span>پزشک گرامی</span>
          <span>
            با فعال سازی امکان برقراری تماس امن، علاوه بر فراهم آوردن یک ویزیت
            پیوسته و با کیفیت، زمان انتظار پاسخگویی به بیماران را نیز کاهش دهید.
          </span>
          <span>
            پاسخ‌دهی به موقع برای برآورده کردن انتظارات بیماران بسیار مهم است.
          </span>
        </div>
      </div>
      <div class="p-3">
        <a href="" data-toggle="modal" data-target="#contact-modal"
          class="btn btn-primary w-100 h-50 d-flex align-items-center text-white justify-content-center">
          فعالسازی تماس امن </a>
        <a href="" class="btn btn-light mt-3 w-100 h-50 d-flex align-items-center  justify-content-center"
          onclick="$('#activation-modal').modal('hide'); window.history.pushState({}, '', 'panel');"> فعلا نه
          بعدا فعال میکنم </a>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="contact-modal" tabindex="-1" role="dialog" aria-labelledby="contact-modal-label"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-radius-6">
      <div class="modal-header">
        <h5 class="modal-title" id="activation-modal-label">تماس امن</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body my-modal-body">
        <div class="d-flex flex-column align-items-center w-100">
          <ul class="mx-4text-sm font-medium list-disc d-flex flex-column">
            <li>در پنل پزشک، در مقابل اسم هر بیمار، دکمه تماس وجود دارد.</li>
            <li>پزشک قادر است در هر زمان با بیمار تماس برقرار کند.</li>
            <li>بیمار در قبض نوبت و در نوبت‌های من، دکمه برقراری تماس را دارد.</li>
            <li>بیمار تنها در ساعت کاری پزشک، قادر به تماس با پزشک است.</li>
            <li>بیمار از زمان نوبت تا ۳ روز بعد از زمان نوبت، دکمه تماس را در اختیار دارد.</li>
            <li>تماس امن همراه با پیام‌رسان است و در صورت نیاز، شما و یا بیمار می‌توانید از هر یک از دو
              سرویس استفاده کنید.</li>
          </ul>
        </div>
      </div>
      <div class="p-3">
        <a href="#"
          class="btn btn-primary w-100 h-50 d-flex align-items-center text-white justify-content-center"
          onclick="$('#activation-modal').modal('hide'); $('#contact-modal').modal('hide'); window.history.pushState({}, '', 'panel'); toastr.success('تماس امن با موفقیت فعال شد');">
          شرایط برقراری تماس امن را
          مطالعه کردم. </a>
      </div>
    </div>
  </div>
</div>
<div class="modal  fade" id="rescheduleModal" tabindex="-1" aria-labelledby="rescheduleModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-radius-6">
      <div class="modal-header">
        <h6 class="modal-title" id="rescheduleModalLabel">جابجایی نوبت</h6>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="calendar-header w-100 d-flex justify-content-between align-items-center">
          <div class="">
            <button id="prev-month-reschedule" class="btn btn-light">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                fill="none">
                <g id="Arrow / Chevron_Right_MD">
                  <path id="Vector" d="M10 8L14 12L10 16" stroke="#000000" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" />
                </g>
              </svg>
            </button>
          </div>
          <div class="w-100">
            <select id="year-reschedule" class="form-select w-100  border-0"></select>
          </div>
          <div class="w-100">
            <select id="month-reschedule" class="form-select w-100  border-0"></select>
          </div>
          <div class="">
            <button id="next-month-reschedule" class="btn btn-light"><svg xmlns="http://www.w3.org/2000/svg"
                width="24" height="24" viewBox="0 0 24 24" fill="none">
                <g id="Arrow / Chevron_Left_MD">
                  <path id="Vector" d="M14 16L10 12L14 8" stroke="#000000" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" />
                </g>
              </svg>
            </button>
          </div>
        </div>
        <div class="w-100 d-flex justify-content-end">
          <button id="goToFirstAvailableDashboard" class="btn btn-light w-100 border">برو به اولین نوبت خالی</button>
        </div>
        <div class="calendar-body calendar-body-g-425 mt-2"> <!-- عناوین روزهای هفته -->
          <div class="calendar-day-name text-center">شنبه</div>
          <div class="calendar-day-name text-center">یک‌شنبه</div>
          <div class="calendar-day-name text-center">دوشنبه</div>
          <div class="calendar-day-name text-center">سه‌شنبه</div>
          <div class="calendar-day-name text-center">چهارشنبه</div>
          <div class="calendar-day-name text-center">پنج‌شنبه</div>
          <div class="calendar-day-name text-center">جمعه</div>
        </div>
        <div class="calendar-body-425 d-none p-2"> <!-- عناوین روزهای هفته -->
          <div class="calendar-day-name text-center">ش</div>
          <div class="calendar-day-name text-center">ی</div>
          <div class="calendar-day-name text-center">د</div>
          <div class="calendar-day-name text-center">س</div>
          <div class="calendar-day-name text-center">چ</div>
          <div class="calendar-day-name text-center">پ</div>
          <div class="calendar-day-name text-center">ج</div>
        </div>
        <div id="calendar-reschedule" class="calendar-body mt-3"></div>
      </div>
    </div>
  </div>
</div>
<div class="modal  fade" id="endVisitModalCenter" tabindex="-1" role="dialog"
  aria-labelledby="endVisitModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-radius-6">
      <div class="modal-header">
        <h6 class="modal-title font-weight-bold" id="exampleModalCenterTitle"> توضیحات درمان</h6>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div>
          <span class="font-weight-bold">پزشک گرامی</span>
          <br>
          <p class="mt-2 font-size-14">
            لطفا در صورتی که برای بیمار نسخه الکترونیک ثبت کرده اید <span class="font-weight-bold">“کد پیگیری
              نسخه”</span> و در صورت نیاز <span class="font-weight-bold">“توضیحات درمان”</span> خود را
            یادداشت نمایید.
          </p>
          <span class="mt-2">
            (این توضیحات در قسمت "نوبت‌های من" بیمار ذخیره می‌شود.)

          </span>
        </div>
        <div class="mt-3">
          <form action="">

            <textarea name="" id="" placeholder="توضیحات خود را وارد کنید" class="my-form-control-light w-100"></textarea>
            <button class="h-50 w-100 btn btn-primary mt-3">ثبت</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')
@include('dr.panel.my-tools.dashboardTools')
  <script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>



  <script>
    var appointmentsSearchUrl = "{{ route('search.appointments') }}";
    var updateStatusAppointmentUrl =
      "{{ route('updateStatusAppointment', ':id') }}";
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.has('showModal')) {
        // فرض کنید ID مودال شما "activation-modal" است
        $('#activation-modal').modal('show');
      }
    });
  </script>
  <script>
    $('#rescheduleModal').on('show.bs.modal', function() {
      // Check if stylesheet is already loaded
      if (!$('#rescheduleModalStyles').length) {
        $('<link>', {
          id: 'rescheduleModalStyles',
          rel: 'stylesheet',
          type: 'text/css',
          href: '{{ asset('dr-assets/panel/css/reschedule.css') }}'
        }).appendTo('head');
      }
    });

    $('#rescheduleModal').on('hidden.bs.modal', function() {
      // Optionally remove the stylesheet when modal is closed
      $('#rescheduleModalStyles').remove();
    });
  </script>
  
@endsection

@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/turn/schedule/appointments.css') }}" rel="stylesheet" />
@endsection
@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection
@section('content')
@section('bread-crumb-title', 'لیست نوبت ها')
<div class="appointments-content w-100 d-flex justify-content-center">
  <div class="appointments-content-wrapper position-relative">
    <div class="top-appointment">
      <div class="w-100 d-flex justify-content-between"> 
        <div class="position-relative">
          <div class="turning_selectDate__MLRSb">
            <button
              class="selectDate_datepicker__xkZeS cursor-pointer text-center h-50 bg-light-blue d-flex justify-content-center align-items-center"
              data-toggle="modal" data-target="#miniCalendarModal">
              <span id="datepicker" class="mx-1"></span>
              <img src="{{ asset('dr-assets/icons/calendar.svg') }}" alt="" srcset="">
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
         <input id="my-appointment-search" type="text"
              class="col-lg-6 col-md-6 col-sm-12 col-xs-12 h-50 form-control" placeholder="جستجو بیمار .....">
        </div>
            <div class="dropdown-container">
              <button class="btn btn-light h-50 btn-filter-appointment-toggle">
                <span class="text-btn-425">کل نوبت ها</span>
               <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" height="1em"
            class="dropdown-icon" role="img">
            <path fill-rule="evenodd" clip-rule="evenodd"
              d="M12.53 15.53a.75.75 0 01-1.06 0l-6-6a.75.75 0 011.06-1.06L12 13.94l5.47-5.47a.75.75 0 111.06 1.06l-6 6z"
              fill="currentColor"></path>
          </svg>
        </button>
        <div class="appointments-filter-drop-toggle">
          <ul class="d-flex flex-column align-items-center justify-content-center w-100 gap-10">
            <li class="btn border w-100 bg-light-blue border-radius-4"><span>کل نوبت ها</span></li>
            <li class="btn border w-100 border-radius-4"><span>نوبت های مطب</span></li>
            <li class="btn border w-100 border-radius-4"><span>نوبت های آنلاین</span></li>
          </ul>
        </div>
      </div>
      </div>
    </div>
  </div>
</div>
<div class="my-appointments-list w-100 mt-3" id="appointment-lists-container">
  <div class="my-appointments-lists-cards d-flex gap-10 w-100 flex-wrap position-relative">
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
            <button class="h-50 w-100 btn my-btn-primary mt-3">ثبت</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal  fade" id="userInfoModalCenter" tabindex="-1" role="dialog"
  aria-labelledby="userInfoModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content border-radius-6">
      <div class="modal-header">
        <h6 class="modal-title font-weight-bold" id="exampleModalCenterTitle">اطلاعات بیمار</h6>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="">
          <div class="w-100 d-flex">
            <div class="bg-light-success top-user-modal-info time-card">
              <div class="text-black font-weight-bold"></div> <!-- زمان -->
            </div>
            <div class="bg-light-success border border-success top-user-modal-info date-card">
              <div class="text-black font-weight-bold"></div> <!-- تاریخ -->
            </div>
          </div>
          <div class="w-100 mt-2">
            <div class="d-flex justify-content-between align-items-center bg-light-blue p-2 h-40" dir="rtl">
              <div class="text-dark font-weight-bold font-size-13">نام و نام خانوادگی</div>
              <div class="text-dark font-weight-bold font-size-13 fullname"></div>
            </div>
            <div class="d-flex justify-content-between align-items-center bg-light p-2 h-40 mt-2" dir="rtl">
              <div class="text-dark font-weight-bold font-size-13">موبایل</div>
              <div class=" text-dark font-weight-bold font-size-13 mobile"></div>
            </div>
            <div class="d-flex justify-content-between align-items-center bg-light-blue p-2 h-40 mt-2" dir="rtl">
              <div class="text-dark font-weight-bold font-size-13">کدملی</div>
              <div class="text-dark font-weight-bold font-size-13 national-code"></div>
            </div>
            <div class="d-flex justify-content-between align-items-center bg-light p-2 h-40 mt-2" dir="rtl">
              <div class="text-dark font-weight-bold font-size-13">کد پیگیری</div>
              <div class="text-dark font-weight-bold font-size-13 tracking-code"></div>
            </div>
            <div class="d-flex justify-content-between align-items-center bg-light-blue p-2 h-40 mt-2" dir="rtl">
              <div class="text-dark font-weight-bold font-size-13">وضعیت پرداخت</div>
              <div class="text-dark font-weight-bold font-size-13 payment-status"> </div>
            </div>
            <div class="d-flex justify-content-between align-items-center bg-light p-2 h-40 mt-2" dir="rtl">
              <div class="text-dark font-weight-bold font-size-13">نوع نوبت</div>
              <div class="text-dark font-weight-bold font-size-13 appointment-type"></div>
            </div>
            <div class="d-flex justify-content-between align-items-center bg-light-blue p-2 h-40 mt-2" dir="rtl">
              <div class="text-dark font-weight-bold font-size-13">نام مرکز</div>
              <div class="text-dark font-weight-bold font-size-13 center-name"></div>
            </div>
          </div>
          <button class="btn btn-outline-danger h-50 w-100 mt-3 cancel-appointment" type="button" data-id="">
            لغو نوبت
          </button>
          <button class="btn my-btn-primary h-50 w-100 mt-3" type="button"
            onclick="location.href='{{ route('prescription.create') }}'">
            تجویز نسخه
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('scripts')
<script src="{{ asset('dr-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
<script>
  var appointmentsSearchUrl = "{{ route('search.appointments') }}";
  var updateStatusAppointmentUrl =
    "{{ route('updateStatusAppointment', ':id') }}";
</script>
@include('dr.panel.turn.schedule.option.appointments')
@endsection

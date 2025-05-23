@extends('dr.panel.layouts.master')
@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/turn/schedule/scheduleSetting/vacation.css') }}"
    rel="stylesheet" />
@endsection
@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection
@section('content')
@section('bread-crumb-title', ' اعلام مرخصی')
@include('dr.panel.my-tools.loader-btn')
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
<script src="{{ asset('dr-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/turn/scehedule/sheduleSetting/workhours/workhours.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/turn/scehedule/sheduleSetting/vacation/vacation.js') }}"></script>
<script>
  var appointmentsSearchUrl = "{{ route('search.appointments') }}";
  var updateStatusAppointmentUrl = "{{ route('updateStatusAppointment', ':id') }}";
</script>
@endsection

@extends('dr.panel.layouts.master')
@section('styles')
 <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
 <link type="text/css" href="{{ asset('dr-assets/panel/profile/edit-profile.css') }}" rel="stylesheet" />
 <link type="text/css" href="{{ asset('dr-assets/panel/css/profile/subuser.css') }}" rel="stylesheet" />
 <style>
  .myPanelOption{
    display: none;
  }
 </style>
@endsection
@section('site-header')
 {{ 'به نوبه | پنل دکتر' }}
@endsection
@section('content')
@section('bread-crumb-title', ' مدیریت مطب ')
<div class="modal fade" id="clinicEditModal" tabindex="-1" role="dialog" aria-labelledby="clinicEditModalLabel"
 aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered" role="document">
  <div class="modal-content border-radius-6">
   <div class="modal-header">
    <h5 class="modal-title" id="clinicEditModalLabel">ویرایش مطب</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
     <span aria-hidden="true">×</span>
    </button>
   </div>
   <div class="modal-body">
    <form id="edit-clinic-form">
     @csrf
     <input type="hidden" name="id" id="edit-clinic-id">
     <div class="d-flex flex-column mt-2">
      <div class="position-relative d-flex gap-4 w-100">
       <!-- فیلد نام مطب -->
       <div class="position-relative w-100">
        <label class="label-top-input-special-takhasos" for="edit-clinic-name">نام مطب: </label>
        <input type="text" class="form-control h-50 w-100" id="edit-clinic-name" name="name">
        <small class="text-danger error-name"></small>
       </div>
      </div>
     </div>
     <div>
      <div class="w-100 position-relative mt-4">
       <label class="label-top-input-special-takhasos" for="edit-clinic-province">استان: </label>
       <select class="form-control h-50 w-100" id="edit-clinic-province" name="province_id">
        <option value="">انتخاب استان</option>
        @foreach ($provinces as $province)
         <option value="{{ $province->id }}">{{ $province->name }}</option>
        @endforeach
       </select>
       <small class="text-danger error-province_id"></small>
      </div>
      <div class="w-100 position-relative mt-4">
       <label class="label-top-input-special-takhasos" for="edit-clinic-city">شهر: </label>
       <select class="form-control h-50 w-100" id="edit-clinic-city" name="city_id" disabled>
        <option value="">انتخاب شهر</option>
       </select>
       <small class="text-danger error-city_id"></small>
      </div>
     </div>
     <!-- فیلد شماره موبایل -->
     <div class="w-100 position-relative mt-4">
      <label class="label-top-input-special-takhasos" for="edit-clinic-phone">شماره موبایل: </label>
      <input type="text" class="form-control h-50 w-100" id="edit-clinic-phone" name="phone_numbers[]">
     </div>
     <small class="text-danger error-phone_numbers"></small>
     <!-- فیلد آدرس -->
     <div class="w-100 position-relative mt-4">
      <label class="label-top-input-special-takhasos" for="edit-clinic-address">آدرس: </label>
      <textarea class="form-control h-50 w-100" id="edit-clinic-address" name="address" placeholder="آدرس" rows="3"></textarea>
      <small class="text-danger error-address"></small>
     </div>
     <!-- فیلد توضیحات -->
     <div class="w-100 position-relative mt-4">
      <label class="label-top-input-special-takhasos" for="edit-clinic-description">توضیحات: </label>
      <textarea class="form-control h-50 w-100" id="edit-clinic-description" name="description" placeholder="توضیحات"
       rows="3"></textarea>
      <small class="text-danger error-description"></small>
     </div>
     <!-- دکمه ذخیره -->
     <div class="w-100 mt-4">
      <button type="submit" class="btn btn-primary w-100 d-flex justify-content-center align-items-center h-50">
       <span class="button_text">ذخیره تغییرات</span>
       <div class="loader" style="display: none;"></div>
      </button>
     </div>
    </form>
   </div>
  </div>
 </div>
</div>
<div class="modal fade " id="clinicModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
 aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered " role="document">
  <div class="modal-content border-radius-6">
   <div class="modal-header">
    <h5 class="modal-title" id="exampleModalLongTitle"> افزودن مطب </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
     <span aria-hidden="true">×</span>
    </button>
   </div>
   <div class="modal-body">
    <form id="add-clinic-form">
     @csrf
     <div class="d-flex flex-column mt-2">
      <!-- فیلد نام مطب -->
      <div class="position-relative w-100">
       <label class="label-top-input-special-takhasos" for="clinic-name">نام مطب: </label>
       <input type="text" class="form-control h-50 w-100" id="clinic-name" name="name">
       <small class="text-danger error-name"></small>
      </div>
     </div>
     <!-- فیلد استان -->
     <div class="w-100 position-relative mt-4">
      <label class="label-top-input-special-takhasos" for="clinic-province">استان: </label>
      <select class="form-control h-50 w-100" id="clinic-province" name="province_id">
       <option value="">انتخاب استان</option>
       @foreach ($provinces as $province)
        <option value="{{ $province->id }}">{{ $province->name }}</option>
       @endforeach
      </select>
      <small class="text-danger error-province_id"></small>
     </div>
     <!-- فیلد شهر -->
     <div class="w-100 position-relative mt-4">
      <label class="label-top-input-special-takhasos" for="clinic-city">شهر: </label>
      <select class="form-control h-50 w-100" id="clinic-city" name="city_id" disabled>
       <option value="">انتخاب شهر</option>
      </select>
      <small class="text-danger error-city_id"></small>
     </div>
     <!-- فیلد شماره موبایل -->
     <div class="w-100 position-relative mt-4">
      <label class="label-top-input-special-takhasos" for="clinic-phone">شماره موبایل: </label>
      <input type="text" class="form-control h-50 w-100" id="clinic-phone" name="phone_numbers[]">
     </div>
     <small class="text-danger error-phone_numbers"></small>
     <!-- فیلد آدرس -->
     <div class="w-100 position-relative mt-4">
      <label class="label-top-input-special-takhasos" for="clinic-address"> آدرس: </label>
      <textarea class="form-control h-50 w-100" placeholder="آدرس" id="clinic-address" name="address" cols="30"
       rows="3"></textarea>
      <small class="text-danger error-address"></small>
     </div>
     <!-- فیلد توضیحات -->
     <div class="w-100 position-relative mt-4">
      <label class="label-top-input-special-takhasos" for="clinic-description">توضیحات: </label>
      <textarea name="description" class="form-control h-50 w-100" placeholder="توضیحات" id="clinic-description"
       cols="30" rows="3"></textarea>
      <small class="text-danger error-description"></small>
     </div>
     <!-- دکمه ارسال -->
     <div class="w-100 mt-4">
      <button type="submit" class="btn btn-primary w-100 d-flex justify-content-center align-items-center h-50">
       <span class="button_text">ذخیره</span>
       <div class="loader" style="display: none;"></div>
      </button>
     </div>
    </form>
   </div>
  </div>
 </div>
</div>
<div class="subuser-content w-100 d-flex justify-content-center mt-4">
 <div class="subuser-content-wrapper p-3">
  <div class="w-100 d-flex justify-content-end">
   <button class="btn btn-primary h-50" id="add-clinic-btn">افزودن مطب
    جدید</button>
  </div>
  <div class="p-3">
   <h4 class="text-dark font-weight-bold">لیست مطب های من</h4>
  </div>
  <div class="subuser-cards mt-2" id="clinic-list">
   @forelse ($clinics as $clinic)
    <div class="subuser-card p-3 w-100 d-flex justify-content-between align-items-end">
     <div>
      <span class="d-block font-weight-bold text-dark"> {{ $clinic->name }}</span>
      <span class="font-size-13 font-weight-bold">
       شهر: <span>{{ $clinic->city->name }}</span>
      </span>
      <span class="font-size-13 mx-2 font-weight-bold">
       آدرس:{{ $clinic->address ?? 'نامشخص' }}
      </span>
     </div>
     <div>
      <div class="d-flex  gap-4">
       <button class="btn btn-light btn-sm rounded-circle edit-btn" data-id="{{ $clinic->id }}">
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
       <button class="btn btn-light btn-sm rounded-circle delete-btn" data-id="{{ $clinic->id }}">
        <svg width="23" height="23" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
         class="cursor-pointer w-5 hover:!text-red-500">
         <path fill-rule="evenodd" clip-rule="evenodd"
          d="M10.4062 2.25L10.4415 2.25H13.5585L13.5938 2.25C13.9112 2.24996 14.2092 2.24992 14.459 2.27844C14.7371 2.31019 15.0296 2.38361 15.3025 2.58033C15.5754 2.77704 15.7375 3.03124 15.8556 3.28508C15.9616 3.51299 16.0559 3.79574 16.1562 4.09685L16.1562 4.09687L16.1562 4.0969L16.1674 4.13037L16.5406 5.25H19H21C21.4142 5.25 21.75 5.58579 21.75 6C21.75 6.41421 21.4142 6.75 21 6.75H19.7017L19.1217 15.449L19.1182 15.5016C19.0327 16.7844 18.9637 17.8205 18.8017 18.6336C18.6333 19.4789 18.3469 20.185 17.7553 20.7384C17.1637 21.2919 16.4401 21.5307 15.5855 21.6425C14.7634 21.75 13.725 21.75 12.4394 21.75H12.3867H11.6133H11.5606C10.275 21.75 9.23655 21.75 8.41451 21.6425C7.55986 21.5307 6.83631 21.2919 6.24472 20.7384C5.65312 20.185 5.3667 19.4789 5.19831 18.6336C5.03633 17.8205 4.96727 16.7844 4.88178 15.5016L4.87827 15.449L4.29834 6.75H3C2.58579 6.75 2.25 6.41421 2.25 6C2.25 5.58579 2.58579 5.25 3 5.25H5H7.45943L7.83264 4.13037L7.8438 4.09688L7.84381 4.09686C7.94414 3.79575 8.03835 3.51299 8.14438 3.28508C8.26246 3.03124 8.42459 2.77704 8.69752 2.58033C8.97045 2.38361 9.26287 2.31019 9.54102 2.27844C9.79077 2.24992 10.0888 2.24996 10.4062 2.25ZM9.04057 5.25H14.9594L14.7443 4.60472C14.6289 4.25832 14.5611 4.05863 14.4956 3.91778C14.466 3.85423 14.4457 3.82281 14.4348 3.80824C14.4298 3.80149 14.427 3.79862 14.4264 3.79801L14.4254 3.79719L14.4243 3.79654C14.4236 3.79616 14.42 3.79439 14.412 3.79174C14.3947 3.78604 14.3585 3.7767 14.2888 3.76875C14.1345 3.75113 13.9236 3.75 13.5585 3.75H10.4415C10.0764 3.75 9.86551 3.75113 9.71117 3.76875C9.64154 3.7767 9.60531 3.78604 9.58804 3.79174C9.58005 3.79439 9.57643 3.79616 9.57566 3.79654L9.57458 3.79719L9.57363 3.79801C9.57302 3.79862 9.57019 3.80149 9.56516 3.80824C9.55428 3.82281 9.53397 3.85423 9.50441 3.91778C9.43889 4.05863 9.37113 4.25832 9.25566 4.60472L9.04057 5.25ZM5.80166 6.75L6.37495 15.3492C6.4648 16.6971 6.52883 17.6349 6.6694 18.3405C6.80575 19.025 6.99608 19.3873 7.2695 19.6431C7.54291 19.8988 7.91707 20.0647 8.60907 20.1552C9.32247 20.2485 10.2625 20.25 11.6133 20.25H12.3867C13.7375 20.25 14.6775 20.2485 15.3909 20.1552C16.0829 20.0647 16.4571 19.8988 16.7305 19.6431C17.0039 19.3873 17.1943 19.025 17.3306 18.3405C17.4712 17.6349 17.5352 16.6971 17.6251 15.3492L18.1983 6.75H16H8H5.80166ZM10 9.25C10.4142 9.25 10.75 9.58579 10.75 10V17C10.75 17.4142 10.4142 17.75 10 17.75C9.58579 17.75 9.25 17.4142 9.25 17V10C9.25 9.58579 9.58579 9.25 10 9.25ZM14.75 10C14.75 9.58579 14.4142 9.25 14 9.25C13.5858 9.25 13.25 9.58579 13.25 10V14C13.25 14.4142 13.5858 14.75 14 14.75C14.4142 14.75 14.75 14.4142 14.75 14V10Z"
          fill="#000"></path>
        </svg>
       </button>
      </div>
     </div>
    </div>
   @empty
    <div class="alert alert-info w-100 d-flex justify-content-center align-items-center">هیچ مطبی یافت نشد</div>
   @endforelse
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
<script>
 const cities = @json($cities);

 function handleValidationErrors(xhr, form) {
  if (xhr.status === 422) {
   $('.label-top-input-special-takhasos').css({
    'position': '',
    'bottom': ''
   });
   const errors = xhr.responseJSON.errors;
   form.find('.text-danger').text(''); // پاک کردن پیام‌های قبلی
   Object.keys(errors).forEach(function(key) {
    const fieldKey = key.replace(/\.\d+$/, '');
    form.find(`.error-${fieldKey}`).text(errors[key][0]);
    const relatedLabel = form.find(`input[name="${key}"], select[name="${key}"]`).siblings(
     '.label-top-input-special-takhasos');
    if (relatedLabel.length > 0) {
     // اعمال استایل برای خطاها
     relatedLabel.css({
      'position': 'absolute',
      'bottom': relatedLabel.hasClass('password-label') ? '32px' : '56px'
     });
    }
   });
  }
 }

 function loadClinics() {
  $.get("{{ route('dr-clinic-management') }}", function(response) {
   const container = $('#clinic-list');
   container.empty();
   if (response.clinics.length === 0) {
    container.append('<div class="alert alert-info w-100 text-center">مطب فعالی ندارید.</div>');
   } else {
    response.clinics.forEach(function(clinic) {
     const clinicCard = `
                        <div class="clinic-card p-3 w-100 d-flex justify-content-between align-items-end">
                            <div>
                                <span class="d-block font-weight-bold text-dark">${clinic.name}</span>
                                <span class="font-size-13 font-weight-bold">شهر: ${clinic.city.name}</span>
                                <span class="font-size-13 mx-2 font-weight-bold">آدرس: ${clinic.address ?? 'نامشخص'}</span>
                            </div>
                            <div>
                                <button class="btn btn-light btn-sm rounded-circle edit-btn" data-id="${clinic.id}">
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
                                <button class="btn btn-light btn-sm rounded-circle delete-btn" data-id="${clinic.id}">
                                   <svg width="23" height="23" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
         class="cursor-pointer w-5 hover:!text-red-500">
         <path fill-rule="evenodd" clip-rule="evenodd"
          d="M10.4062 2.25L10.4415 2.25H13.5585L13.5938 2.25C13.9112 2.24996 14.2092 2.24992 14.459 2.27844C14.7371 2.31019 15.0296 2.38361 15.3025 2.58033C15.5754 2.77704 15.7375 3.03124 15.8556 3.28508C15.9616 3.51299 16.0559 3.79574 16.1562 4.09685L16.1562 4.09687L16.1562 4.0969L16.1674 4.13037L16.5406 5.25H19H21C21.4142 5.25 21.75 5.58579 21.75 6C21.75 6.41421 21.4142 6.75 21 6.75H19.7017L19.1217 15.449L19.1182 15.5016C19.0327 16.7844 18.9637 17.8205 18.8017 18.6336C18.6333 19.4789 18.3469 20.185 17.7553 20.7384C17.1637 21.2919 16.4401 21.5307 15.5855 21.6425C14.7634 21.75 13.725 21.75 12.4394 21.75H12.3867H11.6133H11.5606C10.275 21.75 9.23655 21.75 8.41451 21.6425C7.55986 21.5307 6.83631 21.2919 6.24472 20.7384C5.65312 20.185 5.3667 19.4789 5.19831 18.6336C5.03633 17.8205 4.96727 16.7844 4.88178 15.5016L4.87827 15.449L4.29834 6.75H3C2.58579 6.75 2.25 6.41421 2.25 6C2.25 5.58579 2.58579 5.25 3 5.25H5H7.45943L7.83264 4.13037L7.8438 4.09688L7.84381 4.09686C7.94414 3.79575 8.03835 3.51299 8.14438 3.28508C8.26246 3.03124 8.42459 2.77704 8.69752 2.58033C8.97045 2.38361 9.26287 2.31019 9.54102 2.27844C9.79077 2.24992 10.0888 2.24996 10.4062 2.25ZM9.04057 5.25H14.9594L14.7443 4.60472C14.6289 4.25832 14.5611 4.05863 14.4956 3.91778C14.466 3.85423 14.4457 3.82281 14.4348 3.80824C14.4298 3.80149 14.427 3.79862 14.4264 3.79801L14.4254 3.79719L14.4243 3.79654C14.4236 3.79616 14.42 3.79439 14.412 3.79174C14.3947 3.78604 14.3585 3.7767 14.2888 3.76875C14.1345 3.75113 13.9236 3.75 13.5585 3.75H10.4415C10.0764 3.75 9.86551 3.75113 9.71117 3.76875C9.64154 3.7767 9.60531 3.78604 9.58804 3.79174C9.58005 3.79439 9.57643 3.79616 9.57566 3.79654L9.57458 3.79719L9.57363 3.79801C9.57302 3.79862 9.57019 3.80149 9.56516 3.80824C9.55428 3.82281 9.53397 3.85423 9.50441 3.91778C9.43889 4.05863 9.37113 4.25832 9.25566 4.60472L9.04057 5.25ZM5.80166 6.75L6.37495 15.3492C6.4648 16.6971 6.52883 17.6349 6.6694 18.3405C6.80575 19.025 6.99608 19.3873 7.2695 19.6431C7.54291 19.8988 7.91707 20.0647 8.60907 20.1552C9.32247 20.2485 10.2625 20.25 11.6133 20.25H12.3867C13.7375 20.25 14.6775 20.2485 15.3909 20.1552C16.0829 20.0647 16.4571 19.8988 16.7305 19.6431C17.0039 19.3873 17.1943 19.025 17.3306 18.3405C17.4712 17.6349 17.5352 16.6971 17.6251 15.3492L18.1983 6.75H16H8H5.80166ZM10 9.25C10.4142 9.25 10.75 9.58579 10.75 10V17C10.75 17.4142 10.4142 17.75 10 17.75C9.58579 17.75 9.25 17.4142 9.25 17V10C9.25 9.58579 9.58579 9.25 10 9.25ZM14.75 10C14.75 9.58579 14.4142 9.25 14 9.25C13.5858 9.25 13.25 9.58579 13.25 10V14C13.25 14.4142 13.5858 14.75 14 14.75C14.4142 14.75 14.75 14.4142 14.75 14V10Z"
          fill="#000"></path>
        </svg>
                                  </button>
                            </div>
                        </div>`;
     container.append(clinicCard);
    });
   }
  });
 }
 // انتخاب شهر بر اساس استان
 function populateCities(provinceId, citySelect, selectedCityId = null) {
  citySelect.empty().append('<option value="">انتخاب شهر</option>');
  if (provinceId && cities[provinceId]) {
   cities[provinceId].forEach(function(city) {
    const isSelected = selectedCityId === city.id ? 'selected' : '';
    citySelect.append(`<option value="${city.id}" ${isSelected}>${city.name}</option>`);
   });
   citySelect.prop('disabled', false);
  } else {
   citySelect.prop('disabled', true);
  }
 }
 $(document).ready(function() {
  $('#clinic-province').on('change', function() {
   const provinceId = $(this).val();
   const citySelect = $('#clinic-city');
   citySelect.empty().append('<option value="">انتخاب شهر</option>');
   if (provinceId && cities[provinceId]) {
    cities[provinceId].forEach(function(city) {
     citySelect.append(`<option value="${city.id}">${city.name}</option>`);
    });
    citySelect.prop('disabled', false);
   } else {
    citySelect.prop('disabled', true);
   }
  });
  // نمایش مودال افزودن مطب
  $('#add-clinic-btn').on('click', function() {
   $('#clinicModal').modal('show');
   if ($('#clinic-form').length) {
    $('#clinic-form').trigger('reset');
   } // پاک کردن فرم
   $('#clinic-id').val(''); // تنظیم مقدار پیش‌فرض برای فیلد ID
   $('#clinic-city').empty().append('<option value="">انتخاب شهر</option>').prop('disabled', true);
  });
  $(document).on('click', '.edit-btn', function() {
   const clinicId = $(this).data('id');
   $.ajax({
    url: "{{ route('dr-clinic-edit', ':id') }}".replace(':id', clinicId),
    method: 'GET',
    headers: {
     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    success: function(response) {
     $('#clinicEditModal').modal('show');
     $('#edit-clinic-id').val(clinicId);
     $('#edit-clinic-name').val(response.name);
     $('#edit-clinic-address').val(response.address);
     $('#edit-clinic-description').val(response.description);
     // مقداردهی استان و شهر
     $('#edit-clinic-province').val(response.province_id);
     populateCities(response.province_id, $('#edit-clinic-city'), response.city_id);
     // مقداردهی شماره موبایل
     const phoneContainer = $('#edit-clinic-phone').parent();
     phoneContainer.empty(); // پاک کردن فیلدهای قبلی
     if (response.phone_numbers && response.phone_numbers.length > 0) {
      response.phone_numbers.forEach((phone, index) => {
       phoneContainer.append(`
                        <div class="w-100 position-relative mt-2">
                            <label class="label-top-input-special-takhasos">شماره موبایل ${index + 1}:</label>
                            <input type="text" class="form-control h-50 w-100" name="phone_numbers[]" value="${phone}">
                        </div>
                    `);
      });
     } else {
      // اگر شماره موبایلی وجود نداشت، یک فیلد خالی ایجاد کنید
      phoneContainer.append(`
                    <div class="w-100 position-relative mt-2">
                        <label class="label-top-input-special-takhasos">شماره موبایل:</label>
                        <input type="text" class="form-control h-50 w-100" name="phone_numbers[]">
                    </div>
                `);
     }
    },
    error: function() {
        toastr.error('خطا در دریافت اطلاعات مطب!');
    },
   });
  });
  // تغییر استان در فرم ویرایش
  $('#edit-clinic-province').on('change', function() {
   const provinceId = $(this).val();
   populateCities(provinceId, $('#edit-clinic-city'));
  });
  $('#add-clinic-form').on('submit', function(e) {
   e.preventDefault();
   const form = $(this);
   const submitButton = form.find('button[type="submit"]');
   const loader = submitButton.find('.loader');
   const buttonText = submitButton.find('.button_text');
   // نمایش لودینگ و مخفی کردن متن دکمه
   buttonText.hide();
   loader.show();
   $.ajax({
    url: "{{ route('dr-clinic-store') }}",
    method: 'POST',
    data: form.serialize(),
    success: function() {
        toastr.success('مطب با موفقیت اضافه شد!');

    
     $('#clinicModal').modal('hide');
     loadClinics(); // بروزرسانی لیست مطب‌ها
    },
    error: function(xhr) {
     handleValidationErrors(xhr, form);
        toastr.error('خطا در ذخیره اطلاعات!');
    },
    complete: function() {
     buttonText.show();
     loader.hide();
    },
   });
  });
  $('#edit-clinic-form').on('submit', function(e) {
   e.preventDefault();
   const form = $(this);
   const submitButton = form.find('button[type="submit"]');
   const loader = submitButton.find('.loader');
   const buttonText = submitButton.find('.button_text');
   const clinicId = $('#edit-clinic-id').val();
   buttonText.hide();
   loader.show();
   $.ajax({
    url: "{{ route('dr-clinic-update', ':id') }}".replace(':id', clinicId),
    method: 'POST',
    data: form.serialize(),
    success: function() {
        toastr.success('مطب با موفقیت ویرایش شد!');

     $('#clinicEditModal').modal('hide');
     loadClinics();
    },
    error: function(xhr) {
     handleValidationErrors(xhr, form);
        toastr.error('خطا در ذخیره اطلاعات!');
    },
    complete: function() {
     buttonText.show();
     loader.hide();
    }
   });
  });
  $(document).on('click', '.delete-btn', function() {
   const clinicId = $(this).data('id');
   Swal.fire({
    title: 'حذف مطب',
    text: 'آیا از حذف این مطب اطمینان دارید؟',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'بله، حذف کن',
    cancelButtonText: 'لغو',
   }).then((result) => {
    if (result.isConfirmed) {
     $.ajax({
      url: "{{ route('dr-clinic-delete', ':id') }}".replace(':id', clinicId),
      method: 'DELETE',
      data: {
       _token: $('meta[name="csrf-token"]').attr('content'),
      },
      success: function() {
        toastr.success('مطب با موفقیت حذف شد!');
       loadClinics();
      },
      error: function() {
        toastr.error('خطا در حذف مطب!');
      },
     });
    }
   });
  });
  // بارگذاری لیست مطب‌ها
  // بارگذاری اولیه مطب‌ها
  loadClinics();
 });
</script>
@endsection

<!DOCTYPE html>
<html lang="fa">

<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>محل مطب من</title>
 @include('dr.panel.layouts.partials.head-tags')
 <link rel="stylesheet" href="{{ asset('dr-assets/panel/css/bootstrap.min.css') }}">
 <link rel="stylesheet" href="{{ asset('dr-assets/panel/css/style.css') }}">
 <link rel="stylesheet" href="{{ asset('dr-assets/panel/css/panel.css') }}">
 <link rel="stylesheet" href="{{ asset('dr-assets/panel/profile/edit-profile.css') }}">
 <link rel="stylesheet" href="{{ asset('dr-assets/panel/css/doctors-clininc/activation/index.css') }}">
 <link rel="stylesheet" href="{{ asset('dr-assets/panel/css/toastify/toastify.min.css') }}">
 <link rel="stylesheet" href="{{ asset('dr-assets/panel/css/toastr/toastr.min.css') }}">
 <link rel="stylesheet" href="{{ asset('dr-assets/panel/css/leaflet/leaflet.css') }}">
 @include('dr.panel.my-tools.loader-btn')
</head>

<body>
 <!-- لودینگ کلی سایت -->
 <div id="global-loader">
  <div class="loader-backdrop"></div> <!-- بک‌دراپ -->
  <div class="loader-content">
   <div class="spinner"></div> <!-- انیمیشن لودینگ -->
   <p>لطفا منتظر بمانید...</p>
  </div>
 </div>
 <header class="bg-light text-dark p-3 my-shodow w-100 d-flex align-items-center">
  <div class="back w-50">
   <a href="{{ route('dr-panel') }}" class="btn btn-light">
    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none">
     <g id="Arrow / Chevron_Right_MD">
      <path id="Vector" d="M10 8L14 12L10 16" stroke="#000000" stroke-width="2" stroke-linecap="round"
       stroke-linejoin="round"></path>
     </g>
    </svg>
    <span class="font-weight-bold">بازگشت</span>

   </a>
  </div>
  <div class="w-50">
   <h5 class="font-weight-bold title-header">محل مطب من</h5>
  </div>
 </header>

 <div class="d-flex w-100 justify-content-center align-items-center flex-column">
  <div class="roadmap-container mt-3">
   <div class="step completed">
    <span class="step-title">شروع</span>
    <svg class="icon" viewBox="0 0 36 36" fill="none">
     <circle cx="18" cy="18" r="16" stroke="#0d6efd" stroke-width="2" fill="#0d6efd" />
     <path d="M12 18l4 4l8-8" stroke="#fff" stroke-width="2" fill="none" />
    </svg>
   </div>
   <div class="line completed"></div>
   <div class="step ">
    <span class="step-title">آدرس</span>
    <svg class="icon" viewBox="0 0 36 36" fill="none">
     <circle cx="18" cy="18" r="16" stroke="#0d6efd" stroke-width="2" fill="#fff" />
    </svg>
   </div>
   <div class="line"></div>
   <div class="step">
    <span class="step-title"> بیعانه</span>
    <svg class="icon" viewBox="0 0 36 36" fill="none">
     <circle cx="18" cy="18" r="16" stroke="#ccc" stroke-width="2" fill="#f0f0f0" />
    </svg>
   </div>
   <div class="line"></div>
   <div class="step">
    <span class="step-title">ساعت کاری</span>
    <svg class="icon" viewBox="0 0 36 36" fill="none">
     <circle cx="18" cy="18" r="16" stroke="#ccc" stroke-width="2" fill="#f0f0f0" />
    </svg>
   </div>
   <div class="line"></div>
   <div class="step">
    <span class="step-title">پایان</span>
    <svg class="icon" viewBox="0 0 36 36" fill="none">
     <circle cx="18" cy="18" r="16" stroke="#ccc" stroke-width="2" fill="#f0f0f0" />
    </svg>
   </div>
  </div>



  <div class="my-container-fluid  border-radius-8 d-flex w-100 justify-content-center">
   <div class="row d-flex w-100 justify-content-center">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 border-radius-8">
     <div class="card  shadow">
      <div class="card-body">
       <div id="searchContainer" class="text-center">
        <input id="searchInput" type="text" placeholder="جستجوی مکان...">
        <div id="searchResults" class="search-results"></div>
       </div>
       <div id="map" style="height: 280px; width: 100%;"></div>
       <p class="text-start font-weight-bold mt-3">محل مطب خود را از روی نقشه انتخاب کنید:</p>
       <div class="alert alert-secondary">
        <span class="font-weight-bold font-size-13">برای ویرایش آدرس بر آدرس زیر کلیک کنید 👇 </span>
       </div>
       <div class="input-group mt-2">
        <input type="text" value="{{ $clinic->address ?? '' }}" class="my-form-control w-100"
         placeholder="آدرس شما" readonly data-toggle="modal" data-target="#addressModalCenter">
        <div class="modal fade" id="addressModalCenter" tabindex="-1" role="dialog"
         aria-labelledby="addressModalCenterLabel" aria-hidden="true">
         <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content border-radius-6">
           <div class="modal-header">
            <h5 class="modal-title" id="addressModalCenterLabel">ثبت آدرس</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
             <span aria-hidden="true">&times;</span>
            </button>
           </div>
           <div class="modal-body">
            <form id="addressForm">
             @csrf
             <input type="hidden" name="latitude" id="latitude" value="">
             <input type="hidden" name="longitude" id="longitude" value="">
             <textarea style="height: 90px !important" placeholder="تهران,آزادی" name="address" id="address" cols="1"
              rows="1" class="my-form-control-light w-100"></textarea>
             <div class="w-100">
              <button type="submit"
               class="w-100 btn btn-primary h-50 border-radius-4 d-flex justify-content-center align-items-center">
               <span class="button_text">ذخیره تغییرات</span>
               <div class="loader"></div>
              </button>
             </div>
            </form>

           </div>
          </div>
         </div>
        </div>

        <div class="mt-3 w-100">
         <button class="btn btn-primary h-50 w-100 " type="button" data-toggle="modal"
          data-target="#doneModal">انجام
          شد</button>
        </div>
       </div>

      </div>
     </div>
    </div>
   </div>
  </div>
 </div>
 <div class="modal fade" id="doneModal" tabindex="-1" role="dialog" aria-labelledby="doneModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
   <div class="modal-content border-radius-6">
    <div class="modal-header">
     <h5 class="modal-title fs-6 font-weight-bold" id="doneModalLabel">اطلاعات تماس مطب</h5>
     <button type="button" class="close" data-dismiss="modal" aria-label="Close">
      <span aria-hidden="true">&times;</span>
     </button>
    </div>
    <div class="modal-body">
     <form id="phoneForm">
      @csrf
      <div id="phoneInputs">

       <!-- شماره‌های تماس موجود اینجا نمایش داده می‌شوند -->
      </div>
      <div class="form-group mt-3">
       <a href="#" class="font-size-13 text-decoration-none font-weight-bold text-primary" id="addPhoneLink"
        onclick="addPhoneField()">افزودن شماره تماس</a>
      </div>
      <div class="alert alert-info w-100 mt-2">
       <span class="font-weight-bold font-size-13">
        لطفا برای اطلاع رسانی نوبت های مطب شماره موبایل منشی خود را وارد نمایید.
       </span>
      </div>
      <div class="mt-3">
       <button type="submit" class="btn btn-primary w-100 h-50 d-flex justify-content-center align-items-center">
        <span class="button_text">ذخیره</span>
        <div class="loader" style="display: none;"></div>
       </button>
      </div>
     </form>
    </div>
   </div>
  </div>
 </div>

 @include('dr.panel.layouts.partials.scripts')
 <script src="{{ asset('dr-assets/panel/js/toastify/toastify.min.js') }}"></script>
 <script src="{{ asset('dr-assets/panel/js/sweetalert2/sweetalert2.js') }}"></script>
 <script src="{{ asset('dr-assets/panel/js/leaflet/leaflet.js') }}"></script>
 <script src="{{ asset('dr-assets/panel/js/leaflet/leaflet-control-geocoder/dist/Control.Geocoder.js') }}"></script>
 <script src="{{ asset('dr-assets/panel/js/toastr/toastr.min.js') }}"></script>

 <script>
  const clinicId = {{ $clinic->id }};
  const updateAddressUrl = "{{ route('doctors.clinic.update.address', ['id' => $clinic->id]) }}";

  let phoneCount = 0;

  // افزودن شماره تماس به فرم
  function addPhoneField(phone = '', index = null, showTrashIcon = true) {
   phoneCount++;
   const trashIcon = showTrashIcon ?
    `<div class="input-group-append">
                <button class="btn btn-danger" type="button" onclick="deletePhone(${phoneCount}, ${index})">
                    <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="حذف">
                </button>
           </div>` :
    ''; // عدم نمایش آیکون حذف

   const phoneInput = `
        <div class="form-group position-relative" id="phoneGroup${phoneCount}">
            <label class="label-top-input-special-takhasos" for="clinicPhone${phoneCount}">شماره تلفن مطب ${phoneCount}</label>
            <div class="input-group mt-4">
                <input type="text" class="form-control h-50 border-radius-4" id="clinicPhone${phoneCount}" name="phones[]" value="${phone}" placeholder="شماره تلفن مطب ${phoneCount}">
                ${trashIcon}
            </div>
        </div>`;
   $('#phoneInputs').append(phoneInput);

   // بررسی تعداد شماره‌ها برای غیرفعال کردن دکمه
   toggleAddPhoneButton();
  }

  function toggleAddPhoneButton() {
   const addPhoneButton = document.querySelector('#addPhoneLink');
   if (!addPhoneButton) {
    return;
   }

   if (phoneCount >= 3) {
    addPhoneButton.classList.add('disabled');
    addPhoneButton.style.pointerEvents = 'none';
    addPhoneButton.style.opacity = '0.5';
   } else {
    addPhoneButton.classList.remove('disabled');
    addPhoneButton.style.pointerEvents = 'auto'; // فعال کردن pointer events
    addPhoneButton.style.opacity = '1'; // بازگرداندن opacity به حالت عادی
   }
  }



  // حذف شماره تماس از فرم و دیتابیس
  // حذف شماره تماس از فرم و دیتابیس
  function deletePhone(phoneCount, index) {
   if (index === null) {
    toggleAddPhoneButton();
    document.getElementById("addPhoneLink").removeAttribute('style');

   }
   if (index !== null) {
    Swal.fire({
     title: 'آیا مطمئن هستید؟',
     text: "این شماره تماس حذف خواهد شد!",
     icon: 'warning',
     showCancelButton: true,
     confirmButtonColor: '#d33',
     cancelButtonColor: '#3085d6',
     confirmButtonText: 'بله، حذف کن!',
     cancelButtonText: 'لغو'
    }).then((result) => {
     if (result.isConfirmed) {
      $.ajax({
       url: "{{ route('doctors.clinic.delete.phone', ['id' => $clinic->id]) }}",
       type: 'POST',
       data: {
        _token: '{{ csrf_token() }}',
        phone_index: index
       },
       success: function(response) {
        $(`#phoneGroup${phoneCount}`).remove();
        phoneCount--; // کاهش تعداد شماره‌ها
        toggleAddPhoneButton(); // به‌روزرسانی وضعیت دکمه
        toastr.success('شماره تماس با موفقیت حذف شد.');
        document.getElementById("addPhoneLink").removeAttribute('style');
       },
       error: function() {
        toastr.error('خطا در حذف شماره تماس.');

       }
      });
     }
    });
   } else {
    $(`#phoneGroup${phoneCount}`).remove();
    phoneCount--; // کاهش تعداد شماره‌ها
    document.getElementById("addPhoneLink").removeAttribute('style');

    toggleAddPhoneButton(); // به‌روزرسانی وضعیت دکمه
   }
  }
  $('#doneModal').on('hidden.bs.modal', function() {
   $('body').removeClass('modal-open'); // حذف کلاس اسکرول
   $('.modal-backdrop').remove(); // حذف بک‌دراپ
  });

  $('#doneModal').on('show.bs.modal', function() {
   // نمایش لودینگ
   const loadingHtml = `
        <div class="d-flex justify-content-center align-items-center" style="height: 200px;">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">در حال بارگذاری...</span>
            </div>
        </div>
    `;
   $('#phoneInputs').html(loadingHtml);

   // خالی کردن ورودی‌ها
   phoneCount = 0;

   // بارگذاری شماره‌های تماس از دیتابیس
   $.ajax({
    url: "{{ route('doctors.clinic.get.phones', ['id' => $clinic->id]) }}",
    type: 'GET',
    success: function(response) {
     // خالی کردن محتوای مودال
     $('#phoneInputs').empty();

     const phones = response.phones;

     // اگر شماره‌ای وجود نداشته باشد، یک ورودی بدون آیکون حذف اضافه می‌کنیم
     if (phones.length === 0) {
      addPhoneField('', null, false); // ورودی بدون آیکون حذف
     } else {
      phones.forEach((phone, index) => {
       addPhoneField(phone, index, true); // ورودی با آیکون حذف
      });
     }

     // اضافه کردن اینپوت شماره موبایل منشی
     const secretaryPhone = response.secretary_phone || ''; // اگر شماره‌ای وجود نداشته باشد، خالی می‌ماند
     const secretaryInput = `
                <div class="form-group position-relative mt-4" id="secretaryPhoneGroup">
                    <label class="label-top-input-special-takhasos" for="secretaryPhone">شماره موبایل منشی</label>
                    <input type="text" class="form-control h-50 border-radius-4" id="secretaryPhone" name="secretary_phone" value="${secretaryPhone}" placeholder="شماره موبایل منشی">
                </div>`;
     $('#phoneInputs').append(secretaryInput);

     // تنظیم وضعیت دکمه افزودن شماره تماس
     toggleAddPhoneButton();
    },
    error: function() {
     // در صورت خطا، پیام خطا را نمایش می‌دهیم
     $('#phoneInputs').html(`
                <div class="alert alert-danger text-center" role="alert">
                    خطا در بارگذاری اطلاعات. لطفاً دوباره تلاش کنید.
                </div>
            `);
    }
   });
  });




  // ذخیره شماره‌ها
  $('#phoneForm').on('submit', function(e) {
   e.preventDefault();
   var form = $(this);
   var submitButton = form.find('button[type="submit"]');
   var loader = submitButton.find('.loader');
   var buttonText = submitButton.find('.button_text');

   buttonText.hide();
   loader.show(); // غیرفعال کردن دکمه

   const formData = form.serialize();
   $.ajax({
    url: "{{ route('doctors.clinic.update.phones', ['id' => $clinic->id]) }}",
    type: 'POST',
    data: formData,
    success: function(response) {
     buttonText.show();
     loader.hide();
     toastr.success('شماره‌های تماس با موفقیت ذخیره شدند.');

     $('#doneModal').modal('hide'); // بستن مودال
     $('body').removeClass('modal-open'); // جلوگیری از اسکرول مودال
     $('.modal-backdrop').remove(); // حذف overlay
     location.href = "{{ route('doctors.clinic.cost', $clinic->id) }}"
    },
    error: function() {
     buttonText.show();
     loader.hide();
     toastr.error('خطا در ذخیره شماره‌ها. دوباره تلاش کنید.');

    },
    complete: function() {
     buttonText.show();
     loader.hide();
     submitButton.prop('disabled', false); // فعال کردن دوباره دکمه
    }
   });
  });
  $('#doneModal').on('hidden.bs.modal', function() {
   $('body').removeClass('modal-open'); // حذف کلاس اسکرول
   $('.modal-backdrop').remove(); // حذف overlay
  });



  // تغییر در افزودن شماره تماس برای حذف مستقیم
 </script>

 <script>
  function addPhone() {
   if (phoneCount >= 3) {
    Swal.fire({
     icon: 'warning',
     title: 'حداکثر تعداد شماره تلفن',
     text: 'شما نمی‌توانید بیشتر از ۳ شماره تلفن مطب اضافه کنید.'
    });
    return;
   }
   phoneCount++;
   const phoneInput = `
        <div class="form-group" id="phoneGroup${phoneCount}">
          <div class="input-group position-relative">
          <label class="label-top-input-special-takhasos" for="clinicPhone${phoneCount}">شماره تلفن مطب ${phoneCount}</label>
            <input type="text" class="form-control h-50 border-radius-4" id="clinicPhone${phoneCount}" placeholder="شماره تلفن مطب ${phoneCount}">
            <div class="input-group-append">
              <button class="btn btn-danger" type="button" onclick="removePhone(${phoneCount})" id="removeButton${phoneCount}">
                        <img src="{{ asset('dr-assets/icons/trash.svg') }}" alt="">
  </button>
            </div>
          </div>
        </div>
      `;
   document.getElementById('phoneInputs').insertAdjacentHTML('beforeend', phoneInput);
   updateRemoveButtonVisibility();
  }

  function removePhone(index) {
   Swal.fire({
    title: 'آیا مطمئن هستید؟',
    text: "این شماره تلفن حذف خواهد شد!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'بله، حذف کن!'
   }).then((result) => {
    if (result.isConfirmed) {
     const phoneInputGroup = document.getElementById(`phoneGroup${index}`);
     phoneInputGroup.remove();
     document.getElementById("addPhoneLink").removeAttribute('style');

     phoneCount--;
     updateRemoveButtonVisibility();
    }
   });
  }

  function updateRemoveButtonVisibility() {
   // هیچ دکمه حذف برای ورودی اصلی وجود ندارد
   if (phoneCount === 1) {
    document.getElementById('removeButton1').style.display = 'none';
   } else {
    for (let i = 2; i <= phoneCount; i++) {
     document.getElementById(`removeButton${i}`).style.display = 'inline-block';
    }
   }
   // نمایش یا پنهان کردن لینک افزودن شماره تلفن
   const addPhoneLink = document.getElementById('addPhoneLink');
   if (phoneCount >= 3) {
    addPhoneLink.style.display = 'none'; // پنهان کردن لینک افزودن
   } else {
    addPhoneLink.style.display = 'block'; // نمایش لینک افزودن
   }
  }
 </script>
 <script>
  document.addEventListener("DOMContentLoaded", function() {
   // مقداردهی اولیه نقشه
   var map = L.map('map').setView([35.6892, 51.3890], 13);
   L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap'
   }).addTo(map);

   var marker = L.marker([35.6892, 51.3890], {
    draggable: true
   }).addTo(map);

   // جستجوی آدرس با استفاده از Nominatim
   var searchInput = document.getElementById('searchInput');
   var searchResults = document.getElementById('searchResults');

   searchInput.addEventListener('input', function() {
    var query = this.value;
    if (query.length > 2) {
     fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${query}`)
      .then(response => response.json())
      .then(data => {
       searchResults.innerHTML = '';
       data.forEach(result => {
        var li = document.createElement('li');
        li.className = 'list-group-item';
        li.textContent = result.display_name;
        li.addEventListener('click', function() {
         var lat = parseFloat(result.lat);
         var lon = parseFloat(result.lon);
         marker.setLatLng([lat, lon]);
         map.setView([lat, lon], 15);
         document.querySelector('.my-form-control').value = result.display_name;
         searchResults.innerHTML = '';
        });
        searchResults.appendChild(li);
       });
      });
    } else {
     searchResults.innerHTML = '';
    }
   });

   // کلیک روی نقشه برای تغییر مکان مارکر
   map.on('click', function(e) {
    var lat = e.latlng.lat;
    var lng = e.latlng.lng;
    marker.setLatLng([lat, lng]);
    // به‌روزرسانی فیلدهای latitude و longitude
    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
     .then(response => response.json())
     .then(data => {
      document.querySelector('.my-form-control').value = data.display_name;
     });
   });
   marker.on('moveend', function(e) {
    var lat = e.target.getLatLng().lat;
    var lng = e.target.getLatLng().lng;

    // به‌روزرسانی فیلدهای latitude و longitude
    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;

    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
     .then(response => response.json())
     .then(data => {
      document.querySelector('.my-form-control').value = data.display_name;
     });
   });
   // انتقال مقدار به مودال
   $('#addressModalCenter').on('show.bs.modal', function() {
    var address = document.querySelector('.my-form-control').value;
    $(this).find('textarea').val(address);
   });

   // ارسال به‌روزرسانی به سرور با AJAX
   $('#addressForm').on('submit', function(e) {
    e.preventDefault();
    var form = $(this);
    var submitButton = form.find('button[type="submit"]');
    var loader = submitButton.find('.loader');
    var buttonText = submitButton.find('.button_text');
    var address = $('#address').val();
    var latitude = $('#latitude').val();
    var longitude = $('#longitude').val();

    buttonText.hide();
    loader.show();

    $.ajax({
     url: updateAddressUrl,
     type: 'POST',
     data: {
      address: address,
      latitude: latitude,
      longitude: longitude,
      _token: '{{ csrf_token() }}'
     },
     success: function(response) {
      $('#addressModalCenter').modal('hide');
      $('body').removeClass('modal-open');
      $('.modal-backdrop').remove();
      toastr.success('آدرس شما با موفقیت به‌روزرسانی شد.');

      document.querySelector('.my-form-control').value = address;
     },
     error: function() {

      toastr.error('مشکلی پیش آمد. دوباره تلاش کنید.');

     },
     complete: function() {
      buttonText.show();
      loader.hide();
     }
    });
   });


  });
 </script>

</body>

</html>

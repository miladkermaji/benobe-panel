@extends('mc.panel.doctors-clinic.layouts.master')
@section('styles')
  <link rel="stylesheet" href="{{ asset('mc-assets/panel/css/doctors-clininc/activation/index.css') }}">
  <link rel="stylesheet" href="{{ asset('mc-assets/panel/css/leaflet/leaflet.css') }}">
@endsection


@section('headerTitle')
  محل مطب من
@endsection

@section('backUrl')
  {{ route('mc-panel') }}
@endsection
@section('content')
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
              <p class="text-start fw-bold mt-3">محل مطب خود را از روی نقشه انتخاب کنید:</p>
              <div class="alert alert-secondary">
                <span class="fw-bold font-size-13">برای ویرایش آدرس بر آدرس زیر کلیک کنید 👇 </span>
              </div>
              <div class="input-group mt-2">
                <input type="text" value="{{ $clinic->address ?? '' }}" class="my-form-control w-100"
                  placeholder="آدرس شما" readonly data-bs-toggle="modal" data-bs-target="#addressModalCenter">
                <div class="modal fade" id="addressModalCenter" tabindex="-1" role="dialog"
                  aria-labelledby="addressModalCenterLabel" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content border-radius-6">
                      <div class="modal-header">
                        <h5 class="modal-title" id="addressModalCenterLabel">ثبت آدرس</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
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
                              class="w-100 btn my-btn-primary h-50 border-radius-4 d-flex justify-content-center align-items-center">
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
                  <button class="btn my-btn-primary h-50 w-100 " type="button" data-bs-toggle="modal"
                    data-bs-target="#doneModal">انجام
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
          <h5 class="modal-title fs-6 fw-bold" id="doneModalLabel">اطلاعات تماس مطب</h5>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
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
              <a href="#" class="font-size-13 text-decoration-none fw-bold text-primary" id="addPhoneLink"
                onclick="addPhoneField()">افزودن شماره تماس</a>
            </div>
            <div class="alert alert-info w-100 mt-2">
              <span class="fw-bold font-size-13">
                لطفا برای اطلاع رسانی نوبت های مطب شماره موبایل منشی خود را وارد نمایید.
              </span>
            </div>
            <div class="mt-3">
              <button type="submit"
                class="btn my-btn-primary w-100 h-50 d-flex justify-content-center align-items-center">
                <span class="button_text">ذخیره</span>
                <div class="loader" style="display: none;"></div>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection


@section('scripts')
  <script src="{{ asset('mc-assets/panel/js/leaflet/leaflet.js') }}"></script>
  <script src="{{ asset('mc-assets/panel/js/leaflet/leaflet-control-geocoder/dist/Control.Geocoder.js') }}"></script>

  <script>
    const clinicId = {{ $clinic->id }};
    const updateAddressUrl = "{{ route('doctors.clinic.update.address', ['id' => $clinic->id]) }}";

    let phoneCount = 0;

    // افزودن شماره تماس به فرم
    function addPhoneField(phone = '', index = 'null', showTrashIcon = true) {
      phoneCount++;
      const trashIcon = showTrashIcon ?
        `<div class="input-group-append">
                <button class="btn btn-danger" type="button" onclick="deletePhone(${phoneCount}, ${index})">
                    <img src="{{ asset('mc-assets/icons/trash.svg') }}" alt="حذف">
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
            addPhoneField('', 'null', false); // ورودی بدون آیکون حذف
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
                        <img src="{{ asset('mc-assets/icons/trash.svg') }}" alt="">
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
      // مقداردهی اولیه نقشه با مختصات پیش‌فرض
      var map = L.map('map').setView([35.6892, 51.3890], 13);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
      }).addTo(map);

      // ایجاد مارکر با مختصات پیش‌فرض
      var marker = L.marker([35.6892, 51.3890], {
        draggable: true
      }).addTo(map);

      // اگر شهر و استان وجود داشته باشند، موقعیت را پیدا کن
      @if ($clinic->city && $clinic->province)
        var cityName = "{{ $clinic->city->name ?? '' }}";
        var provinceName = "{{ $clinic->province->name ?? '' }}";

        if (cityName && provinceName) {
          // جستجوی موقعیت با استفاده از نام شهر و استان
          fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${cityName},${provinceName},Iran`)
            .then(response => response.json())
            .then(data => {
              if (data && data.length > 0) {
                var lat = parseFloat(data[0].lat);
                var lon = parseFloat(data[0].lon);

                // به‌روزرسانی نقشه و مارکر
                map.setView([lat, lon], 13);
                marker.setLatLng([lat, lon]);

                // به‌روزرسانی فیلدهای مخفی
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lon;

                // به‌روزرسانی آدرس
                document.querySelector('.my-form-control').value = data[0].display_name;
              }
            });
        }
      @endif

      // اگر آدرس کلینیک وجود دارد، آن را در فیلد آدرس نمایش بده
      @if ($clinic->address)
        document.querySelector('.my-form-control').value = "{{ $clinic->address }}";
      @endif

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
@endsection

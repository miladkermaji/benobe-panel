{{-- resources/views/mc/panel/profile/option/profile-option.blade.php --}}
<script>
  // تابع کمکی برای مقداردهی اولیه Tom Select
  function initializeTomSelect(elementId, options = {}) {
    const selectElement = document.getElementById(elementId);
    if (selectElement && !selectElement.tomSelect) {
      return new TomSelect(`#${elementId}`, {
        direction: 'rtl',
        placeholder: options.placeholder || 'انتخاب کنید',
        allowEmptyOption: true,
        maxOptions: null,
        ...options
      });
    }
    return selectElement ? selectElement.tomSelect : null;
  }
  document.addEventListener('DOMContentLoaded', function() {
    // مقداردهی اولیه Tom Select برای استان و شهر
    const provinceTomSelect = initializeTomSelect('province_id', {
      placeholder: 'انتخاب استان'
    });
    const cityTomSelect = initializeTomSelect('city_id', {
      placeholder: 'انتخاب شهر',
    });
    if (!provinceTomSelect || !cityTomSelect) {
      console.error('خطا در مقداردهی Tom Select برای استان یا شهر');
      return;
    }
    const provinceSelect = document.getElementById('province_id');
    const doctorProvinceId =
      '@if (Auth::guard('doctor')->check()){{ Auth::guard('doctor')->user()->province_id }}@elseif (Auth::guard('secretary')->check()){{ Auth::guard('secretary')->user()->doctor->province_id }}@endif';
    const doctorCityId =
      '@if (Auth::guard('doctor')->check()){{ Auth::guard('doctor')->user()->city_id }}@elseif (Auth::guard('secretary')->check()){{ Auth::guard('secretary')->user()->doctor->city_id }}@endif';
    // مدیریت تغییر در انتخاب استان
    provinceSelect.addEventListener('change', function() {
      const provinceId = this.value;
      if (provinceId) {
        fetch(`{{ route('mc-get-cities') }}?province_id=${provinceId}`, {
            method: 'GET',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'Accept': 'application/json'
            }
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              cityTomSelect.clear();
              cityTomSelect.clearOptions();
              cityTomSelect.addOption({
                value: '',
                text: 'انتخاب شهر'
              });
              data.cities.forEach(city => {
                cityTomSelect.addOption({
                  value: city.id,
                  text: city.name
                });
              });
              cityTomSelect.enable();
              cityTomSelect.refreshOptions();
              // اگر تغییر دستی بود، شهر رو خالی کن
              if (provinceId !== doctorProvinceId) {
                cityTomSelect.setValue('');
              }
            } else {
              toastr.error(data.message || 'خطا در بارگذاری شهرها');
              cityTomSelect.clear();
              cityTomSelect.clearOptions();
              cityTomSelect.addOption({
                value: '',
                text: 'خطا در بارگذاری'
              });
              cityTomSelect.disable();
            }
          })
          .catch(error => {
            toastr.error('خطا در ارتباط با سرور');
            cityTomSelect.clear();
            cityTomSelect.clearOptions();
            cityTomSelect.addOption({
              value: '',
              text: 'خطا در بارگذاری'
            });
            cityTomSelect.disable();
          });
      } else {
        cityTomSelect.clear();
        cityTomSelect.clearOptions();
        cityTomSelect.addOption({
          value: '',
          text: 'ابتدا یک استان انتخاب کنید'
        });
        cityTomSelect.disable();
      }
    });
    // لود اولیه شهرها بر اساس استان دکتر
    if (doctorProvinceId) {
      fetch(`{{ route('mc-get-cities') }}?province_id=${doctorProvinceId}`, {
          method: 'GET',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            cityTomSelect.clear();
            cityTomSelect.clearOptions();
            cityTomSelect.addOption({
              value: '',
              text: 'انتخاب شهر'
            });
            data.cities.forEach(city => {
              cityTomSelect.addOption({
                value: city.id,
                text: city.name
              });
            });
            cityTomSelect.enable();
            cityTomSelect.refreshOptions();
            // تنظیم شهر پیش‌فرض بر اساس دیتابیس
            if (doctorCityId) {
              cityTomSelect.setValue(doctorCityId);
              // جلوگیری از باز شدن خودکار سلکت شهر
              setTimeout(() => {
                cityTomSelect.blur && cityTomSelect.blur();
                document.activeElement && document.activeElement.blur && document.activeElement.blur();
              }, 100);
            }
          }
        })
        .catch(error => {
          console.error('خطا در لود اولیه شهرها:', error);
        });
    }
  });

  function updateAlert() {
    fetch("{{ route('mc-check-profile-completeness') }}", {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => response.json())
      .then(profileData => {
        // حذف هشدارهای قبلی
        const existingAlert = document.querySelector('.alert-warning');
        if (existingAlert) {
          existingAlert.remove();
        }
        // اگر پروفایل کامل نیست
        if (!profileData.profile_completed) {
          // ایجاد هشدار
          const alertHtml = `
                <div class="alert alert-warning text-center">
                    <span class="fw-bold">
                        پروفایل شما کامل نیست. لطفا بخش‌های زیر را تکمیل کنید:
                        <ul class="w-100 d-flex gap-4">
                            ${profileData.incomplete_sections.map(section => 
                                `<li class="badge badge-danger p-2">${section}</li>`
                            ).join('')}
                        </ul>
                    </span>
                </div>
            `;
          // اضافه کردن هشدار به بالای محتوا
          const mainContent = document.querySelector('.main-content');
          mainContent.insertAdjacentHTML('afterbegin', alertHtml);
        }
      })
      .catch(error => {
        console.error('خطا در بررسی وضعیت پروفایل:', error);
      });
  }
  document.addEventListener('DOMContentLoaded', function() {
    new TomSelect('#academic_degree_id', {
      plugins: ['clear_button'],
      placeholder: 'انتخاب درجه علمی',
      searchField: ['text'],
      noResultsText: 'نتیجه‌ای یافت نشد',
      render: {
        option: function(data, escape) {
          return '<div class="d-flex justify-content-between">' +
            '<span>' + escape(data.text) + '</span>' +
            '</div>';
        },
        item: function(data, escape) {
          return '<div>' + escape(data.text) + '</div>';
        }
      },
      // برای فارسی
      locale: 'fa',
      // تنظیمات بیشتر
      sortField: {
        field: 'text'
      }
    });
    new TomSelect('#specialties_list', {
      valueField: 'id',
      plugins: ['clear_button'],
      noResultsText: 'نتیجه‌ای یافت نشد',
      labelField: 'name',
      searchField: ['name'],
      maxOptions: 1000,
      placeholder: 'انتخاب تخصص...',
      render: {
        option: function(item, escape) {
          return `<div>
        ${escape(item.name)}
        ${item.category ? `<small class="text-muted">(${escape(item.category)})</small>` : ''}
     </div>`;
        }
      }
    });
  });
  document.addEventListener('DOMContentLoaded', function() {
    const addButton = document.getElementById('addButton');
    const additionalInputs = document.getElementById('additionalInputs');
    let inputCount = 0; // شمارش ورودی‌های اضافی
    addButton.addEventListener('click', () => {
      // بررسی اینکه آیا ردیف آخر پر شده است یا نه
      const lastInputGroup = additionalInputs.querySelector('.specialty-item:last-child');
      if (lastInputGroup) {
        const degreeSelect = lastInputGroup.querySelector('select[name^="degrees"]');
        const specialtySelect = lastInputGroup.querySelector('select[name^="specialties"]');
        const titleInput = lastInputGroup.querySelector('input[name^="titles"]');
        if (!degreeSelect.value || !specialtySelect.value || !titleInput.value.trim()) {
          Swal.fire({
            title: 'لطفاً ردیف فعلی را کامل کنید',
            text: 'تمام فیلدها (درجه علمی، تخصص و عنوان تخصص) باید پر شوند.',

          });
          return; // اگر ردیف آخر پر نشده، از ادامه جلوگیری می‌کنیم
        }
      }
      if (inputCount < 3) {
        inputCount++;
        const newInputGroup = document.createElement('div');
        newInputGroup.classList.add('w-100', 'mt-3', 'specialty-item');
        newInputGroup.innerHTML = `
        <div>
          <div class="text-left mt-3 remove-form-item" onclick="removeInput(this)">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" clip-rule="evenodd" d="M4.46967 4.46967C4.76256 4.17678 5.23744 4.17678 5.53033 4.46967L10 8.93934L14.4697 4.46967C14.7626 4.17678 15.2374 4.17678 15.5303 4.46967C15.8232 4.76256 15.8232 5.23744 15.5303 5.53033L11.0607 10L15.5303 14.4697C15.8232 14.7626 15.8232 15.2374 15.5303 15.5303C15.2374 15.8232 14.7626 15.8232 14.4697 15.5303L10 11.0607L5.53033 15.5303C5.23744 15.8232 4.76256 15.8232 4.46967 15.5303C4.17678 15.2374 4.17678 14.7626 4.46967 14.4697L8.93934 10L4.46967 5.53033C4.17678 5.23744 4.17678 4.76256 4.46967 4.46967Z" fill="#000"></path>
            </svg>
          </div>
          <div>
            <div class="mt-2">
              <div class="d-flex justify-content-between gap-4">
                <div class="w-100">
                  <label for="degree${inputCount}" class="label-top-input">درجه علمی</label>
                  <select name="degrees[${inputCount}]" id="degree${inputCount}" class="form-control h-50 w-100 border-radius-6 mt-3 col-12 position-relative daraje">
                    @foreach ($academic_degrees as $academic_degree)
                      <option value="{{ $academic_degree->id }}">{{ $academic_degree->title }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="w-100">
                  <label for="specialty${inputCount}" class="label-top-input">تخصص</label>
                  <select name="specialties[${inputCount}]" id="specialty${inputCount}" class="form-control h-50 w-100 border-radius-6 mt-3 col-12 position-relative takhasos-input">
                    @foreach ($specialties as $specialtyOption)
                      <option value="{{ $specialtyOption->id }}">{{ $specialtyOption->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div>
                <div class="form-group position-relative mt-2">
                  <label for="title${inputCount}" class="label-top-input-special-takhasos-elem-create">عنوان تخصص</label>
                  <input type="text" name="titles[${inputCount-1}]" id="title${inputCount}" class="form-control h-50 w-100 border-radius-6 mt-3">
                </div>
              </div>
            </div>
          </div>
        </div>
      `;
        additionalInputs.appendChild(newInputGroup);
        // راه‌اندازی TomSelect برای ردیف جدید
        new TomSelect(`#degree${inputCount}`, {
          plugins: ['clear_button'],
          placeholder: 'انتخاب درجه علمی',
          searchField: ['text'],
          noResultsText: 'نتیجه‌ای یافت نشد',
          locale: 'fa',
          sortField: {
            field: 'text'
          }
        });
        new TomSelect(`#specialty${inputCount}`, {
          valueField: 'id',
          plugins: ['clear_button'],
          noResultsText: 'نتیجه‌ای یافت نشد',
          labelField: 'name',
          searchField: ['name'],
          placeholder: 'انتخاب تخصص...',
        });
        // به‌روزرسانی وضعیت دکمه
        updateAddButtonState();
      } else {
        Swal.fire({
          title: 'حداکثر تخصص برای هر دکتر 3 تخصص می‌باشد',
          icon: 'error',
        });
      }
    });
  });

  function updateAddButtonState() {
    const addButton = document.getElementById('addButton');
    const additionalInputs = document.getElementById('additionalInputs');
    const existingSpecialtiesCount = additionalInputs.querySelectorAll('.specialty-item').length;
    // اگر تعداد تخصص‌ها به حداکثر رسیده، دکمه غیرفعال بشه
    if (existingSpecialtiesCount >= 2) { // حداکثر 2 تخصص اضافی
      addButton.disabled = true;
      return;
    }
    // بررسی ردیف آخر
    const lastInputGroup = additionalInputs.querySelector('.specialty-item:last-child');
    if (lastInputGroup) {
      const degreeSelect = lastInputGroup.querySelector('select[name^="degrees"]');
      const specialtySelect = lastInputGroup.querySelector('select[name^="specialties"]');
      const titleInput = lastInputGroup.querySelector('input[name^="titles"]');
      // اگر ردیف آخر ناقص باشه، دکمه غیرفعال می‌شه
      if (!degreeSelect.value || !specialtySelect.value || !titleInput.value.trim()) {
        addButton.disabled = true;
      } else {
        addButton.disabled = false;
      }
    } else {
      // اگر هیچ ردیفی وجود نداره، دکمه فعال باشه
      addButton.disabled = false;
    }
  }
  // تابع بررسی وضعیت پروفایل در زمان بارگذاری اولیه
  function checkInitialProfileCompleteness() {
    fetch("{{ route('mc-check-profile-completeness') }}", {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => response.json())
      .then(profileData => {
        // حذف هشدارهای قبلی
        const existingAlert = document.querySelector('.alert-warning');
        if (existingAlert) {
          existingAlert.remove();
        }
        // اگر پروفایل کامل نیست
        if (!profileData.profile_completed) {
          // ایجاد هشدار
          const alertHtml = `
                <div class="alert alert-warning text-center">
                    <span class="fw-bold">
                        پروفایل شما کامل نیست. لطفا بخش‌های زیر را تکمیل کنید:
                        <ul class="w-100 d-flex gap-4">
                            ${profileData.incomplete_sections.map(section => 
                                `<li class="badge badge-danger p-2">${section}</li>`
                            ).join('')}
                        </ul>
                    </span>
                </div>
            `;
          // اضافه کردن هشدار به بالای محتوا
          const mainContent = document.querySelector('.main-content');
          mainContent.insertAdjacentHTML('afterbegin', alertHtml);
          // نقشه‌بندی بخش‌ها
          const sectionMappings = {
            'نام': '#personal-data',
            'نام خانوادگی': '#personal-data',
            'کد ملی': '#personal-data',
            'شماره نظام پزشکی': '#personal-data',
            'تخصص و درجه علمی': '#specialty-section',
            'آیدی': '#uuid-section',
            'پیام‌رسان‌ها': '#messengers-section'
          };
          // حذف کلاس‌های قبلی
          Object.values(sectionMappings).forEach(selector => {
            const section = document.querySelector(selector);
            if (section) {
              section.classList.remove('border', 'border-warning');
            }
          });
          // اضافه کردن کلاس به بخش‌های ناقص
          profileData.incomplete_sections.forEach(section => {
            const selector = sectionMappings[section];
            if (selector) {
              const sectionElement = document.querySelector(selector);
              if (sectionElement) {
                sectionElement.classList.add('border', 'border-warning');
              }
            }
          });
        }
      })
      .catch(error => {
        console.error('خطا در بررسی وضعیت پروفایل:', error);
      });
  }
  // اجرای تابع در زمان بارگذاری اولیه صفحه
  document.addEventListener('DOMContentLoaded', function() {
    checkInitialProfileCompleteness();
  });
  const deleteSpecialtyRoute = "{{ route('mc-delete-specialty', ['id' => '__ID__']) }}";

  function removeInput(button) {
    // پیدا کردن المان والد که ممکنه دارای data-specialty-id باشه
    const inputGroup = button.closest('.specialty-item');
    const specialtyId = inputGroup.getAttribute('data-specialty-id');
    // اگر specialtyId وجود نداره (ردیف جدیده و توی دیتابیس نیست)
    if (!specialtyId) {
      Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: 'این ردیف حذف خواهد شد.',

        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'بله، حذف کن!',
        cancelButtonText: 'خیر، انصراف',
      }).then((result) => {
        if (result.isConfirmed) {
          // فقط از DOM حذف کن
          inputGroup.remove();
          Swal.fire('حذف شد!', 'ردیف با موفقیت حذف شد.', 'success');
          updateAddButtonState(); // به‌روزرسانی وضعیت دکمه اضافه کردن
        }
      });
      return; // از تابع خارج شو
    }
    // اگر specialtyId وجود داره (توی دیتابیس ذخیره شده)
    Swal.fire({
      title: 'آیا مطمئن هستید؟',
      text: 'این عمل قابل بازگشت نیست!',

      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'بله، حذف کن!',
      cancelButtonText: 'خیر، انصراف',
    }).then((result) => {
      if (result.isConfirmed) {
        const url = deleteSpecialtyRoute.replace('__ID__', specialtyId);
        fetch(url, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            }
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              inputGroup.remove();
              Swal.fire('حذف شد!', 'تخصص با موفقیت حذف شد.', 'success');
              updateAddButtonState();
            } else {
              Swal.fire('خطا!', 'خطا در حذف تخصص.', 'error');
            }
          })
          .catch(error => {
            console.error('خطا در برقراری ارتباط با سرور:', error);
            Swal.fire('خطا!', 'خطا در برقراری ارتباط با سرور.', 'error');
          });
      }
    });
  }
  // فراخوانی این تابع پس از هر بار اضافه کردن تخصص جدید
  $(document).ready(function() {
    updateAddButtonState();
  });
  // تابع اولیه برای تام سلکت
  function initTomSelect(selector, options = {}) {
    return new TomSelect(selector, {
      plugins: ['clear_button'],
      searchField: ['text', 'name'],
      placeholder: options.placeholder || 'انتخاب کنید',
      maxItems: options.maxItems || 1,
      render: {
        option: function(data, escape) {
          return '<div class="d-flex justify-content-between">' +
            '<span>' + escape(data.text || data.name) + '</span>' +
            '</div>';
        },
        item: function(data, escape) {
          return '<div>' + escape(data.text || data.name) + '</div>';
        }
      },
      locale: 'fa',
      ...options
    });
  }

  function initAllTomSelects() {
    // درجه علمی
    initTomSelect('#academic_degree_id', {
      placeholder: 'انتخاب درجه علمی'
    });
    // تخصص اصلی
    initTomSelect('#specialties_list', {
      placeholder: 'انتخاب تخصص',
      valueField: 'id',
      labelField: 'name',
      searchField: ['name'],
    });
    // درجه علمی برای تخصص‌های اضافی از دیتابیس
    document.querySelectorAll('[id^="degree"]').forEach(el => {
      if (el.id !== 'academic_degree_id') {
        initTomSelect(`#${el.id}`, {
          placeholder: 'انتخاب درجه علمی'
        });
      }
    });
    // تخصص‌های اضافی از دیتابیس
    document.querySelectorAll('[id^="specialty"]').forEach(el => {
      if (el.id !== 'specialties_list') {
        initTomSelect(`#${el.id}`, {
          placeholder: 'انتخاب تخصص',
          valueField: 'id',
          labelField: 'name',
          searchField: ['name'],
        });
      }
    });
  }
  // اصلاح تابع اضافه کردن ورودی جدید
  function addNewSpecialtyInput() {
    const additionalInputs = document.getElementById('additionalInputs');
    const inputCount = document.querySelectorAll('#additionalInputs .specialty-item').length;
    if (inputCount < 2) { // حداکثر 2 ورودی اضافی
      const newInputGroup = document.createElement('div');
      newInputGroup.classList.add('w-100', 'mt-3', 'specialty-item'); // اضافه کردن کلاس specialty-item
      newInputGroup.innerHTML = `
            <div>
                <div class="text-left mt-3 remove-form-item" onclick="removeInput(this)">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M4.46967 4.46967C4.76256 4.17678 5.23744 4.17678 5.53033 4.46967L10 8.93934L14.4697 4.46967C14.7626 4.17678 15.2374 4.17678 15.5303 4.46967C15.8232 4.76256 15.8232 5.23744 15.5303 5.53033L11.0607 10L15.5303 14.4697C15.8232 14.7626 15.8232 15.2374 15.5303 15.5303C15.2374 15.8232 14.7626 15.8232 14.4697 15.5303L10 11.0607L5.53033 15.5303C5.23744 15.8232 4.76256 15.8232 4.46967 15.5303C4.17678 15.2374 4.17678 14.7626 4.46967 14.4697L8.93934 10L4.46967 5.53033C4.17678 5.23744 4.17678 4.76256 4.46967 4.46967Z" fill="#000"></path>
                    </svg>
                </div>
                <div>
                    <div class="mt-2">
                        <div class="d-flex justify-content-between gap-4">
                            <div class="w-100">
                                <label for="degree${inputCount + 1}" class="label-top-input">درجه علمی</label>
                                <select name="degrees[${inputCount}]" id="degree${inputCount + 1}" class="form-control h-50 w-100 border-radius-6 mt-3 col-12 position-relative daraje">
                                    @foreach ($academic_degrees as $academic_degree)
                                        <option value="{{ $academic_degree->id }}">{{ $academic_degree->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-100">
                                <label for="specialty${inputCount + 1}" class="label-top-input">تخصص</label>
                                <select name="specialties[${inputCount}]" id="specialty${inputCount + 1}" class="form-control h-50 w-100 border-radius-6 mt-3 col-12 position-relative takhasos-input">
                                    @foreach ($specialties as $specialtyOption)
                                        <option value="{{ $specialtyOption->id }}">{{ $specialtyOption->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <div class="form-group position-relative mt-2">
                                <label for="title${inputCount + 1}" class="label-top-input-special-takhasos-elem-create">عنوان تخصص</label>
                                <input type="text" name="titles[${inputCount}]" id="title${inputCount + 1}" class="form-control h-50 w-100 border-radius-6 mt-3">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
      additionalInputs.appendChild(newInputGroup);
      initTomSelect(`#degree${inputCount + 1}`, {
        placeholder: 'انتخاب درجه علمی'
      });
      initTomSelect(`#specialty${inputCount + 1}`, {
        placeholder: 'انتخاب تخصص'
      });
      // به‌روزرسانی وضعیت دکمه "اضافه کردن تخصص"
      updateAddButtonState();
    } else {
      Swal.fire({
        title: 'حداکثر تخصص برای هر دکتر 3 تخصص میباشد',
        icon: 'error',
      });
    }
  }
  // اجرا در زمان بارگذاری
  // بررسی زمان آخرین درخواست
  document.getElementById("profileEdit").addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const submitButton = form.querySelector('button[type="submit"]');
    const loader = submitButton.querySelector('.loader');
    const buttonText = submitButton.querySelector('.button_text');
    // مخفی کردن متن دکمه و نمایش لودینگ
    buttonText.style.display = 'none';
    loader.style.display = 'block';
    fetch(form.action, {
        method: form.method,
        body: new FormData(form),
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => {
        // بازگردانی دکمه به حالت اولیه
        buttonText.style.display = 'block';
        loader.style.display = 'none';
        if (!response.ok) {
          return response.json().then(errorData => {
            throw new Error(errorData.message || 'خطای نامشخص');
          });
        }
        return response.json();
      })
      .then(data => {
        // بازگردانی دکمه به حالت اولیه
        buttonText.style.display = 'block';
        loader.style.display = 'none';
        if (data.success) {
          toastr.success(data.message || "تخصص با موفقیت به‌روز شد");
          updateAlert();
          callCheckProfileCompleteness();
          updateProfileSections(data);
          // بروزرسانی لحظه‌ای سایدبار
          if (data.update_sidebar) {
            updateSidebarSpecialty();
          }
          // بروزرسانی بخش تخصص در پروفایل
          updateProfileSpecialtySection(data);
        } else {
          toastr.error(data.message || "خطا در به‌روزرسانی تخصص");
        }
      })
      .catch(error => {
        // بازگردانی دکمه به حالت اولیه
        buttonText.style.display = 'block';
        loader.style.display = 'none';
        toastr.error(error.message || 'خطا در برقراری ارتباط با سرور');
      });
  });
  // --- AUTO-SAVE FOR ADDITIONAL SPECIALTIES ---
  function showSpecialtySaveStatus(id, status) {
    const el = document.querySelector(`#specialty-save-status-${id}`);
    if (el) {
      el.innerHTML = status === 'saving' ? '<span class="spinner-border spinner-border-sm text-primary"></span>' :
        (status === 'saved' ? '<span class="text-success">ذخیره شد</span>' : '');
      if (status === 'saved') {
        setTimeout(() => {
          el.innerHTML = '';
        }, 1500);
      }
    }
  }

  function autoSaveSpecialty(id) {
    const container = document.querySelector(`[data-specialty-id='${id}']`);
    if (!container) return;
    const degree = container.querySelector('select[name^="degrees"]')?.value;
    const specialty = container.querySelector('select[name^="specialties"]')?.value;
    const title = container.querySelector('input[name^="titles"]')?.value;
    showSpecialtySaveStatus(id, 'saving');
    // استفاده از route برای ارسال به روت اصلی
    fetch("{{ route('mc-specialty-update') }}", {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          specialty_id: id,
          academic_degree_id: degree,
          specialty_id_value: specialty,
          specialty_title: title,
          auto_save: true
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          showSpecialtySaveStatus(id, 'saved');
          // بروزرسانی لحظه‌ای سایدبار
          if (data.update_sidebar) {
            updateSidebarSpecialty();
          }
          // بروزرسانی بخش تخصص در پروفایل
          updateProfileSpecialtySection(data);
        } else {
          showSpecialtySaveStatus(id, '');
          toastr.error(data.message || 'خطا در ذخیره تخصص');
        }
      })
      .catch(() => {
        showSpecialtySaveStatus(id, '');
        toastr.error('خطا در ذخیره تخصص');
      });
  }
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.specialty-item[data-specialty-id]').forEach(function(item) {
      const id = item.getAttribute('data-specialty-id');
      item.querySelectorAll('select, input').forEach(function(input) {
        input.removeAttribute('disabled');
        input.addEventListener('change', function() {
          autoSaveSpecialty(id);
        });
      });
      // Add save status span if not exists
      if (!item.querySelector(`#specialty-save-status-${id}`)) {
        const statusSpan = document.createElement('span');
        statusSpan.id = `specialty-save-status-${id}`;
        statusSpan.className = 'mx-2';
        item.querySelector('input[name^="titles"]').after(statusSpan);
      }
    });
    // TomSelect for all degree and specialty selects (main and additional)
    document.querySelectorAll('select[id^="degree"]').forEach(function(sel) {
      if (!sel.tomselect) new TomSelect(sel, {
        plugins: ['clear_button'],
        placeholder: 'انتخاب درجه علمی'
      });
    });
    document.querySelectorAll('select[id^="specialty"]').forEach(function(sel) {
      if (!sel.tomselect) new TomSelect(sel, {
        plugins: ['clear_button'],
        placeholder: 'انتخاب تخصص'
      });
    });
    if (document.getElementById('academic_degree_id') && !document.getElementById('academic_degree_id').tomselect) {
      new TomSelect('#academic_degree_id', {
        plugins: ['clear_button'],
        placeholder: 'انتخاب درجه علمی'
      });
    }
    if (document.getElementById('specialties_list') && !document.getElementById('specialties_list').tomselect) {
      new TomSelect('#specialties_list', {
        plugins: ['clear_button'],
        placeholder: 'انتخاب تخصص'
      });
    }
  });
  // --- END AUTO-SAVE ---
  document.getElementById("specialtyEdit").addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const submitButton = form.querySelector('button[type="submit"]');
    const loader = submitButton.querySelector('.loader');
    const buttonText = submitButton.querySelector('.button_text');
    buttonText.style.display = 'none';
    loader.style.display = 'block';
    // ذخیره تخصص‌های اضافی (غیراصلی) به صورت جداگانه
    const additionalSpecialties = document.querySelectorAll('.specialty-item[data-specialty-id]');
    additionalSpecialties.forEach(function(item) {
      const id = item.getAttribute('data-specialty-id');
      autoSaveSpecialty(id);
    });
    fetch(form.action, {
        method: form.method,
        body: new FormData(form),
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => {
        buttonText.style.display = 'block';
        loader.style.display = 'none';
        if (!response.ok) {
          return response.json().then(errorData => {
            throw new Error(errorData.message || 'خطای نامشخص');
          });
        }
        return response.json();
      })
      .then(data => {
        buttonText.style.display = 'block';
        loader.style.display = 'none';
        if (data.success) {
          toastr.success(data.message || "تخصص با موفقیت به‌روز شد");
          updateAlert();
          callCheckProfileCompleteness();
          updateProfileSections(data);
        } else {
          toastr.error(data.message || "خطا در به‌روزرسانی تخصص");
        }
      })
      .catch(error => {
        buttonText.style.display = 'block';
        loader.style.display = 'none';
        toastr.error(error.message || 'خطا در برقراری ارتباط با سرور');
      });
  });
  document.addEventListener('DOMContentLoaded', function() {
    const additionalInputs = document.getElementById('additionalInputs');
    // اضافه کردن رویداد به ورودی‌ها برای بررسی لحظه‌ای
    additionalInputs.addEventListener('input', function(e) {
      if (e.target.matches('select[name^="degrees"], select[name^="specialties"], input[name^="titles"]')) {
        updateAddButtonState();
      }
    });
    additionalInputs.addEventListener('change', function(e) {
      if (e.target.matches('select[name^="degrees"], select[name^="specialties"]')) {
        updateAddButtonState();
      }
    });
  });

  function updateSpecialties(specialties) {
    if (!specialties || !Array.isArray(specialties)) {
      console.error('داده‌های تخصص‌ها نامعتبر است:', specialties);
      return;
    }
    const additionalInputs = document.getElementById('additionalInputs');
    additionalInputs.innerHTML = ''; // پاک کردن تخصص‌های قبلی
    specialties.forEach((specialty, index) => {
      if (!specialty.is_main) { // فقط تخصص‌های غیراصلی
        const newInputGroup = document.createElement('div');
        newInputGroup.classList.add('w-100', 'mt-3', 'specialty-item');
        newInputGroup.setAttribute('data-specialty-id', specialty.id);
        // اطمینان از اینکه مقدار عنوان تخصص به درستی تنظیم شود
        const titleValue = specialty.speciality_title || specialty.specialty_title ||
          ''; // پشتیبانی از هر دو نام فیلد
        newInputGroup.innerHTML = `
        <div>
          <div class="text-left mt-3 remove-form-item" onclick="removeInput(this)">
            <img src="http://127.0.0.1:8000/mc-assets/icons/times.svg" alt="" srcset="">
          </div>
          <div>
            <div class="mt-2">
              <div class="d-flex justify-content-between gap-4">
                <div class="w-100">
                  <label for="degree${index + 1}" class="label-top-input">درجه علمی</label>
                  <select name="degrees[${index}]" id="degree${index + 1}" class="form-control h-50 w-100 border-radius-6 mt-3 col-12 position-relative daraje">
                    @foreach ($academic_degrees as $academic_degree)
                      <option value="{{ $academic_degree->id }}" ${specialty.academic_degree_id == {{ $academic_degree->id }} ? 'selected' : ''}>{{ $academic_degree->title }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="w-100">
                  <label for="specialty${index + 1}" class="label-top-input">تخصص</label>
                  <select name="specialties[${index}]" id="specialty${index + 1}" class="form-control h-50 w-100 border-radius-6 mt-3 col-12 position-relative takhasos-input">
                    @foreach ($specialties as $specialtyOption)
                      <option value="{{ $specialtyOption->id }}" ${specialty.specialty_id == {{ $specialtyOption->id }} ? 'selected' : ''}>{{ $specialtyOption->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div>
                <div class="form-group position-relative mt-2">
                  <label for="title${index + 1}" class="label-top-input-special-takhasos-elem-create">عنوان تخصص</label>
                  <input type="text" name="titles[${index}]" id="title${index + 1}" class="form-control h-50 w-100 border-radius-6 mt-3" value="${titleValue}">
                </div>
              </div>
            </div>
          </div>
        </div>
      `;
        additionalInputs.appendChild(newInputGroup);
        initTomSelect(`#degree${index + 1}`, {
          placeholder: 'انتخاب درجه علمی'
        });
        initTomSelect(`#specialty${index + 1}`, {
          placeholder: 'انتخاب تخصص'
        });
      }
    });
    updateAddButtonState();
  }
  document.getElementById("uuid-form").addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const submitButton = form.querySelector('button[type="submit"]');
    const loader = submitButton.querySelector('.loader');
    const buttonText = submitButton.querySelector('.button_text');
    // مخفی کردن متن دکمه و نمایش لودینگ
    buttonText.style.display = 'none';
    loader.style.display = 'block';
    fetch(form.action, {
        method: form.method,
        body: new FormData(form),
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => {
        // بازگردانی دکمه به حالت اولیه
        buttonText.style.display = 'block';
        loader.style.display = 'none';
        if (!response.ok) {
          return response.json().then(errorData => {
            throw new Error(errorData.message || 'خطای نامشخص');
          });
        }
        return response.json();
      })
      .then(data => {
        // بازگردانی دکمه به حالت اولیه
        buttonText.style.display = 'block';
        loader.style.display = 'none';
        if (data.success) {
          toastr.success(data.message || "آیدی شما با موفقیت به‌روز شد");
          updateAlert();
          callCheckProfileCompleteness();
          updateProfileSections(data);
        } else {
          toastr.error(data.message || "خطا در به‌روزرسانی آیدی");
        }
      })
      .catch(error => {
        // بازگردانی دکمه به حالت اولیه
        buttonText.style.display = 'block';
        loader.style.display = 'none';
        // نمایش توست خطا
        toastr.error(error.message || 'خطا در برقراری ارتباط با سرور');
      });
  });
  // تابع نمایش خطاهای اعتبارسنجی
  function handleValidationErrors(errors) {
    // پاک کردن خطاهای قبلی
    clearPreviousErrors();
    // نمایش خطاها
    Object.keys(errors).forEach(field => {
      const inputElement = document.querySelector(`[name="${field}"]`);
      if (inputElement) {
        // ایجاد المان خطا
        const errorElement = document.createElement('div');
        errorElement.className = 'text-danger validation-error mt-1 font-size-13';
        // نمایش تمام خطاهای مربوط به فیلد
        errorElement.textContent = errors[field][0];
        // اضافه کردن کلاس خطا به اینپوت
        inputElement.classList.add('is-invalid');
        // قرار دادن المان خطا بعد از اینپوت
        inputElement.parentNode.insertBefore(errorElement, inputElement.nextSibling);
      }
    });
    // نمایش توست خطا
    toastr.error(data.message || "خطا در به‌روزرسانی آیدی");
  }
  // تابع پاک کردن خطاهای قبلی
  function clearPreviousErrors() {
    // حذف خطاهای قبلی
    document.querySelectorAll('.validation-error').forEach(el => el.remove());
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
  }
  /*  edit mobile */
  // متغیرهای سراسری
  // متغیرهای سراسری
  let otpToken = null;
  let resendTimer = null;
  // تابع ارسال کد OTP
  function sendOtpCode() {
    const newMobile = document.getElementById('newMobileNumber').value;
    const sendButton = document.querySelector('#mobileInputStep1 button');
    const loader = sendButton.querySelector('.loader');
    const buttonText = sendButton.querySelector('.button_text');
    // مخفی کردن متن دکمه و نمایش لودینگ
    buttonText.style.display = 'none';
    loader.style.display = 'block';
    // اعتبارسنجی شماره موبایل با Regex دقیق
    // تبدیل اعداد فارسی به انگلیسی
    const persianNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    const englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    let convertedMobile = newMobile;
    for (let i = 0; i < persianNumbers.length; i++) {
      convertedMobile = convertedMobile.replace(new RegExp(persianNumbers[i], 'g'), englishNumbers[i]);
    }

    const mobileRegex =
      /^(?!09{1}(\d)\1{8}$)09(?:01|02|03|12|13|14|15|16|18|19|20|21|22|30|33|35|36|38|39|90|91|92|93|94)\d{7}$/;
    if (!mobileRegex.test(convertedMobile)) {
      toastr.error("شماره موبایل نامعتبر است");
      // بازگردانی دکمه به حالت اولیه
      buttonText.style.display = 'block';
      loader.style.display = 'none';
      return;
    }

    // استفاده از شماره تبدیل شده برای ارسال به سرور
    $.ajax({
      url: "{{ route('mc-send-mobile-otp') }}",
      method: 'POST',
      data: {
        mobile: convertedMobile
      },
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(response) {
        otpToken = response.token;
        $('#mobileInputStep1').hide();
        $('#otpInputStep').show();
        // ریست کردن اینپوت‌های OTP
        document.querySelectorAll('.otp-input').forEach(input => input.value = '');
        // فوکوس روی اولین اینپوت
        document.querySelector('.otp-input').focus();
        startResendTimer();
        toastr.success("کد تایید ارسال شد");
      },
      error: function(xhr) {
        toastr.error(xhr.responseJSON.message || "خطا در ارسال کد");
      },
      complete: function() {
        // بازگردانی دکمه به حالت اولیه
        buttonText.style.display = 'block';
        loader.style.display = 'none';
      }
    });
  }
  // تابع تایید کد OTP
  function verifyOtpCode() {
    const otpInputs = document.querySelectorAll('.otp-input');

    // تبدیل اعداد فارسی به انگلیسی
    const persianNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    const englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

    // جمع‌آوری مقادیر از input fields با تبدیل اعداد فارسی
    const otpValues = Array.from(otpInputs).map(input => {
      let value = input.value.trim();

      // تبدیل اعداد فارسی به انگلیسی
      for (let i = 0; i < persianNumbers.length; i++) {
        value = value.replace(new RegExp(persianNumbers[i], 'g'), englishNumbers[i]);
      }

      // بررسی اینکه مقدار خالی نباشد
      if (value === '' || value === null || value === undefined) {
        return null;
      }

      // بررسی اینکه فقط اعداد انگلیسی باشد
      if (!/^[0-9]$/.test(value)) {
        return null;
      }

      return value;
    });

    // بررسی اینکه تمام مقادیر وارد شده‌اند
    if (otpValues.some(value => value === null)) {
      toastr.error("لطفاً تمام ارقام کد را وارد کنید");
      return;
    }

    const otpCode = otpValues.join('');
    const newMobile = $('#newMobileNumber').val();
    const verifyButton = document.querySelector('#otpInputStep button');
    const loader = verifyButton.querySelector('.loader');
    const buttonText = verifyButton.querySelector('.button_text');

    // بررسی کامل بودن کد
    if (otpCode.length !== 4) {
      toastr.error("لطفاً تمام ارقام کد را وارد کنید");
      return;
    }

    // بررسی اینکه تمام ارقام عددی هستند
    if (!/^\d{4}$/.test(otpCode)) {
      toastr.error("کد تأیید باید فقط شامل اعداد باشد");
      return;
    }

    // اطمینان از وجود otpToken
    if (!otpToken) {
      toastr.error("توکن OTP در دسترس نیست. لطفاً دوباره کد را درخواست کنید.");
      return;
    }

    // تبدیل اعداد فارسی به انگلیسی برای موبایل
    let convertedMobile = newMobile;
    for (let i = 0; i < persianNumbers.length; i++) {
      convertedMobile = convertedMobile.replace(new RegExp(persianNumbers[i], 'g'), englishNumbers[i]);
    }

    // تبدیل کد OTP به آرایه‌ای از اعداد صحیح
    const otpArray = otpCode.split('').map(digit => {
      const num = parseInt(digit, 10);
      return isNaN(num) ? null : num;
    }).filter(digit => digit !== null);

    // بررسی اینکه آرایه OTP معتبر است
    if (otpArray.length !== 4 || otpArray.some(digit => digit < 0 || digit > 9)) {
      toastr.error("کد تأیید نامعتبر است. لطفاً تمام ارقام را وارد کنید.");
      return;
    }

    // Debug: نمایش داده‌های ارسالی
    console.log('Original OTP Values:', Array.from(otpInputs).map(input => input.value));
    console.log('Converted OTP Values:', otpValues);
    console.log('OTP Code:', otpCode);
    console.log('OTP Array:', otpArray);
    console.log('Mobile:', convertedMobile);

    // مخفی کردن متن دکمه و نمایش لودینگ
    buttonText.style.display = 'none';
    loader.style.display = 'block';

    // تولید URL با استفاده از route و otpToken
    fetch("{{ route('mc-mobile-confirm', ':token') }}".replace(':token', otpToken), {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
          otp: otpArray,
          mobile: convertedMobile
        })
      })
      .then(response => {
        if (!response.ok) {
          return response.json().then(errorData => {
            throw new Error(errorData.message || 'خطای نامشخص');
          });
        }
        return response.json();
      })
      .then(response => {
        // بررسی دقیق پاسخ موفقیت
        if (response.success) {
          toastr.success(response.message);
          // به‌روزرسانی المان‌های موبایل در صفحه
          $('input[name="mobile"]').val(response.mobile);
          // بستن مودال
          closeXModal('mobileEditModal');
          // رفرش صفحه برای اطمینان
          setTimeout(() => {
            location.reload();
          }, 1000);
        } else {
          toastr.error(response.message || "خطا در تغییر شماره موبایل");
        }
      })
      .catch(error => {
        // مدیریت خطاهای سرور
        let errorMessage = "خطا در تایید کد";
        if (error.message) {
          errorMessage = error.message;
        }
        toastr.error(errorMessage);
      })
      .finally(() => {
        // بازگردانی دکمه به حالت اولیه
        buttonText.style.display = 'block';
        loader.style.display = 'none';
      });
  }
  // تابع شروع تایمر ارسال مجدد
  function startResendTimer() {
    let seconds = 120;
    const timerElement = document.getElementById('resendOtpTimer');
    clearInterval(resendTimer);
    resendTimer = setInterval(() => {
      if (seconds > 0) {
        timerElement.innerHTML = `ارسال مجدد کد تا ${seconds} ثانیه دیگر`;
        seconds--;
      } else {
        clearInterval(resendTimer);
        timerElement.innerHTML = '<a href="#" onclick="sendOtpCode()">ارسال مجدد کد</a>';
      }
    }, 1000);
  }
  // اجرای اسکریپت پس از بارگذاری کامل DOM
  document.addEventListener('DOMContentLoaded', function() {
    const otpInputs = document.querySelectorAll('.otp-input');
    // فوکوس روی اولین اینپوت از سمت چپ در مرحله OTP
    $('#mobileEditModal').on('shown.bs.modal', function() {
      otpInputs[0].focus();
    });
    otpInputs.forEach((input, index) => {
      input.addEventListener('input', function() {
        // تبدیل اعداد فارسی به انگلیسی
        const persianNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        const englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        let value = this.value;

        // تبدیل اعداد فارسی به انگلیسی
        for (let i = 0; i < persianNumbers.length; i++) {
          value = value.replace(new RegExp(persianNumbers[i], 'g'), englishNumbers[i]);
        }

        // محدود کردن به یک کاراکتر عددی انگلیسی
        value = value.replace(/[^0-9]/g, '');

        // محدود کردن به یک کاراکتر
        this.value = value.substring(0, 1);

        // حرکت از چپ به راست برای RTL
        if (this.value.length === 1 && index < otpInputs.length - 1) {
          otpInputs[index + 1].focus();
        }
      });

      input.addEventListener('keydown', function(e) {
        // اجازه دادن به کلیدهای Backspace, Delete, Tab, Arrow keys
        if ([8, 9, 37, 38, 39, 40, 46].includes(e.keyCode)) {
          return;
        }

        // اجازه دادن فقط به اعداد 0-9
        if (e.keyCode >= 48 && e.keyCode <= 57) {
          return;
        }

        // اجازه دادن به اعداد از numpad
        if (e.keyCode >= 96 && e.keyCode <= 105) {
          return;
        }

        // جلوگیری از سایر کلیدها
        e.preventDefault();
      });

      // مدیریت Backspace برای حرکت به اینپوت قبلی
      input.addEventListener('keyup', function(e) {
        if (e.key === 'Backspace' && this.value.length === 0 && index > 0) {
          otpInputs[index - 1].focus();
        }
      });

      input.addEventListener('paste', function(e) {
        e.preventDefault();
        const pastedText = (e.clipboardData || window.clipboardData).getData('text');
        const numbers = pastedText.replace(/[^0-9]/g, '').split('');

        // پر کردن اینپوت‌ها با اعداد paste شده
        otpInputs.forEach((input, i) => {
          if (numbers[i]) {
            input.value = numbers[i];
          }
        });

        // فوکوس روی آخرین اینپوت پر شده یا اولین اینپوت خالی
        const lastFilledIndex = Math.min(numbers.length - 1, otpInputs.length - 1);
        if (lastFilledIndex < otpInputs.length - 1) {
          otpInputs[lastFilledIndex + 1].focus();
        }
      });
    });
  });
  /*  edit mobile */
  document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('messengersForm');
    const submitButton = form.querySelector('button[type="submit"]');
    const buttonText = submitButton.querySelector('.button_text');
    const loader = submitButton.querySelector('.loader');
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      // نمایش لودینگ و مخفی کردن متن دکمه
      buttonText.style.display = 'none';
      loader.style.display = 'block';
      // ارسال درخواست Ajax
      fetch("{{ route('mc-messengers-update') }}", {
          method: 'PUT',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            ita_phone: form.querySelector('input[name="ita_phone"]').value,
            ita_username: form.querySelector('input[name="ita_username"]').value,
            telegram_phone: form.querySelector('input[name="telegram_phone"]').value,
            telegram_username: form.querySelector('input[name="telegram_username"]').value,
            instagram_username: form.querySelector('input[name="instagram_username"]').value,
            secure_call: form.querySelector('input[name="secure_call"]').checked ? 1 : 0,
          }),
        })
        .then(response => response.json())
        .then(data => {
          // بازگرداندن دکمه به حالت اولیه
          buttonText.style.display = 'block';
          loader.style.display = 'none';
          // نمایش پیام موفقیت یا خطا
          if (data.success) {
            toastr.success(data.message);
            updateAlert();
            callCheckProfileCompleteness();
            updateProfileSections(data);
          } else {
            toastr.error(data.message || "خطا در به‌روزرسانی اطلاعات");
          }
        })
        .catch(error => {
          // بازگرداندن دکمه به حالت اولیه
          buttonText.style.display = 'block';
          loader.style.display = 'none';
          // نمایش خطا
          toastr.error("خطا در برقراری ارتباط با سرور");
        });
    });
  });
  // مدیریت ارسال فرم
  document.getElementById("staticPasswordForm").addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const submitButton = form.querySelector('button[type="submit"]');
    const loader = submitButton.querySelector('.loader');
    const buttonText = submitButton.querySelector('.button_text');
    buttonText.style.display = 'none';
    loader.style.display = 'block';
    fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'X-Requested-With': 'XMLHttpRequest',
        },
      })
      .then(response => {
        if (!response.ok) {
          return response.json().then(errorData => {
            throw {
              status: response.status,
              data: errorData
            };
          });
        }
        return response.json();
      })
      .then(data => {
        buttonText.style.display = 'block';
        loader.style.display = 'none';
        if (data.success) {
          toastr.success(data.message || 'تنظیمات رمز عبور ثابت با موفقیت به‌روزرسانی شد.');
          passwordInput.value = '';
          confirmPasswordInput.value = '';
          passwordInput.placeholder = 'رمز عبور تنظیم شده است';
          confirmPasswordInput.placeholder = 'رمز عبور تنظیم شده است';
          clearPreviousErrors();
        } else {
          toastr.error(data.message || 'خطا در به‌روزرسانی تنظیمات');
        }
      })
      .catch(error => {
        buttonText.style.display = 'block';
        loader.style.display = 'none';
        if (error.status === 422 && error.data.errors) {
          handleValidationErrors(error.data.errors);
        }
      });
  });
  // تابع مدیریت خطاهای اعتبارسنجی
  function handleValidationErrors(errors) {
    clearPreviousErrors();
    Object.keys(errors).forEach(field => {
      const inputElement = document.querySelector(`[name="${field}"]`);
      if (inputElement) {
        // پیدا کردن div.validation-error با رفتن به parent بالاتر
        const errorElement = inputElement.closest('.position-relative').querySelector('.validation-error');
        if (errorElement) {
          errorElement.textContent = errors[field][0];
          inputElement.classList.add('is-invalid');
        }
      }
      // اگر خطا برای password باشه و به password_confirmation مربوط باشه
      if (field === 'password' && errors[field][0].includes('تکرار رمز عبور')) {
        const confirmInput = document.querySelector('[name="password_confirmation"]');
        const confirmErrorElement = confirmInput.closest('.position-relative').querySelector('.validation-error');
        if (confirmErrorElement) {
          confirmErrorElement.textContent = errors[field][0];
          confirmInput.classList.add('is-invalid');
        }
      }
    });
    toastr.error('لطفاً خطاهای فرم را بررسی کنید.');
  }
  // تابع پاک کردن خطاهای قبلی
  function clearPreviousErrors() {
    document.querySelectorAll('.validation-error').forEach(el => el.textContent = '');
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
  }
  // تنظیم اولیه وضعیت اینپوت‌ها بر اساس تاگل
  document.addEventListener('DOMContentLoaded', function() {
    const toggleSwitch = document.querySelector('input[name="static_password_enabled"]');
    const passwordInput = document.querySelector('input[name="password"]');
    const confirmPasswordInput = document.querySelector('input[name="password_confirmation"]');
    const saveButton = document.getElementById('btn-save-pass');
    const statusText = document.querySelector('#static_password_status');
    // تابع آپدیت متن وضعیت
    function updateStatusText(isEnabled) {
      statusText.textContent = isEnabled ? 'رمز عبور ثابت فعال است' : 'رمز عبور ثابت غیرفعال است';
    }
    // تابع مدیریت وضعیت اینپوت‌ها
    function updateInputsState(isEnabled) {
      if (isEnabled) {
        passwordInput.removeAttribute('disabled');
        confirmPasswordInput.removeAttribute('disabled');
        saveButton.removeAttribute('disabled');
        passwordInput.placeholder = 'رمز عبور جدید را وارد کنید';
        confirmPasswordInput.placeholder = 'تکرار رمز عبور جدید';
      } else {
        passwordInput.setAttribute('disabled', 'disabled');
        confirmPasswordInput.setAttribute('disabled', 'disabled');
        saveButton.setAttribute('disabled', 'disabled');
        passwordInput.value = '';
        confirmPasswordInput.value = '';
        passwordInput.placeholder = 'رمز عبور';
        confirmPasswordInput.placeholder = 'تکرار رمز عبور';
        clearPreviousErrors();
      }
    }
    // مدیریت تغییر تاگل
    toggleSwitch.addEventListener('change', function() {
      const isEnabled = this.checked;
      updateStatusText(isEnabled);
      updateInputsState(isEnabled);
      // اگر تاگل غیرفعال شد، درخواست به سرور برای غیرفعال کردن رمز عبور
      if (!isEnabled) {
        const loader = document.createElement('div');
        loader.className = 'loader';
        loader.style.display = 'block';
        this.parentElement.appendChild(loader);
        fetch("{{ route('mc-static-password-update') }}", {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'Accept': 'application/json',
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              static_password_enabled: false,
            }),
          })
          .then(response => response.json())
          .then(data => {
            loader.remove();
            if (data.success) {
              toastr.success(data.message || 'رمز عبور ثابت با موفقیت غیرفعال شد.');
              updateStatusText(false);
              updateInputsState(false);
            } else {
              toastr.error(data.message || 'خطا در غیرفعال کردن رمز عبور ثابت');
              toggleSwitch.checked = true;
              updateStatusText(true);
              updateInputsState(true);
            }
          })
          .catch(error => {
            loader.remove();
            toastr.error('خطا در برقراری ارتباط با سرور');
            toggleSwitch.checked = true;
            updateStatusText(true);
            updateInputsState(true);
          });
      }
    });
    updateStatusText(toggleSwitch.checked);
    updateInputsState(toggleSwitch.checked);
  });
  document.addEventListener('DOMContentLoaded', function() {
    const toggleSwitch = document.querySelector('input[name="two_factor_secret_enabled"]');
    const secretInput = document.querySelector('input[name="two_factor_secret"]');
    const saveButton = document.getElementById('btn-save-two-factor');
    const statusText = document.querySelector('#two_factor_status');
    // تابع آپدیت متن وضعیت
    function updateStatusText(isEnabled) {
      statusText.textContent = isEnabled ? 'گذرواژه دو مرحله‌ای فعال است' : 'گذرواژه دو مرحله‌ای غیرفعال است';
    }
    toggleSwitch.addEventListener('change', function() {
      const isEnabled = this.checked;
      updateStatusText(isEnabled);
      const loader = document.createElement('div');
      loader.className = 'loader';
      loader.style.display = 'block';
      this.parentElement.appendChild(loader);
      fetch("{{ route('mc-two-factor-update') }}", {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            two_factor_secret_enabled: isEnabled ? 1 : 0,
          }),
        })
        .then(response => response.json())
        .then(data => {
          loader.remove();
          if (data.success) {
            toastr.success(data.message || 'تنظیمات گذرواژه دو مرحله‌ای با موفقیت به‌روزرسانی شد.');
            updateStatusText(isEnabled);
          } else {
            toastr.error(data.message || 'خطا در به‌روزرسانی تنظیمات');
            // بازگرداندن تاگل به حالت قبلی در صورت خطا
            toggleSwitch.checked = !isEnabled;
            updateStatusText(!isEnabled);
          }
        })
        .catch(error => {
          loader.remove();
          toastr.error('خطا در برقراری ارتباط با سرور');
          // بازگرداندن تاگل به حالت قبلی
          toggleSwitch.checked = !isEnabled;
          updateStatusText(!isEnabled);
        });
    });
  });
  document.addEventListener('DOMContentLoaded', function() {
    const incompleteSections = @json($doctor->getIncompleteProfileSections());
    if (incompleteSections.includes('پیام‌رسان‌ها')) {
      document.getElementById('messengers-section').classList.add('border-warning');
    }
    // همین‌طور برای سایر بخش‌ها
  });

  function checkProfileCompleteness() {
    fetch("{{ route('mc-check-profile-completeness') }}", {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => {
        // اگر پاسخ موفقیت‌آمیز نبود
        if (!response.ok) {
          // چاپ متن پاسخ برای دیباگ
          return response.text().then(text => {
            console.error('Raw response:', text);
            throw new Error('Server response was not ok');
          });
        }
        // پارس کردن JSON
        return response.json();
      })
      .then(data => {
        // کدهای قبلی...
      })
      .catch(error => {
        toastr.error("خطا در دریافت اطلاعات پروفایل");
      });
  }

  function updateProfileSections(data) {
    console.log('Update Profile Sections Called');
    fetch("{{ route('mc-check-profile-completeness') }}", {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => {
        if (!response.ok) {
          return response.text().then(text => {
            console.error('Raw response:', text);
            throw new Error('Server response was not ok');
          });
        }
        return response.json();
      })
      .then(profileData => {
        // نقشه‌بندی بخش‌ها
        const sectionMappings = {
          'نام': '#personal-data',
          'نام خانوادگی': '#personal-data',
          'کد ملی': '#personal-data',
          'شماره نظام پزشکی': '#personal-data',
          'تخصص و درجه علمی': '#specialty-section',
          'آیدی': '#uuid-section',
          'پیام‌رسان‌ها': '#messengers-section'
        };
        // حذف کلاس‌های قبلی
        Object.values(sectionMappings).forEach(selector => {
          const section = document.querySelector(selector);
          if (section) {
            section.classList.remove('border', 'border-warning');
          }
        });
        // اضافه کردن کلاس به بخش‌های ناقص
        profileData.incomplete_sections.forEach(section => {
          const selector = sectionMappings[section];
          if (selector) {
            const sectionElement = document.querySelector(selector);
            if (sectionElement) {
              sectionElement.classList.add('border', 'border-warning');
            }
          }
        });
      })
      .catch(error => {
        console.error('خطا در بررسی وضعیت پروفایل:', error);
      });
  }
  // فراخوانی تابع پس از هر به‌روزرسانی پروفایل
  function callCheckProfileCompleteness() {
    checkProfileCompleteness();
  }
  // فراخوانی در زمان بارگذاری اولیه صفحه
  document.addEventListener('DOMContentLoaded', checkProfileCompleteness);

  function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.parentElement.querySelector('.show-pass');
    if (input.type === 'password') {
      input.type = 'text';
      icon.src = "{{ asset('mc-assets/icons/show-pass.svg') }}"; // آیکون نمایش
    } else {
      input.type = 'password';
      icon.src = "{{ asset('mc-assets/icons/hide-pass.svg') }}"; // آیکون مخفی
    }
  }
  // تابع حذف تخصص بدون رفرش
  function removeSpecialty(element, specialtyId) {
    const deleteSpecialtyRoute = "{{ route('mc-delete-specialty', ['id' => '__ID__']) }}";
    const url = deleteSpecialtyRoute.replace('__ID__', specialtyId);
    fetch(url, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json',
        },
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          element.closest('.specialty-item').remove();
          toastr.success(data.message);
          updateAddButtonState();
        } else {
          toastr.error(data.message);
        }
      })
      .catch(error => {
        toastr.error('خطا در حذف تخصص');
      });
  }
  // تابع بروزرسانی لحظه‌ای سایدبار
  function updateSidebarSpecialty() {
    fetch("{{ route('mc-get-current-specialty') }}", {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // بروزرسانی سایدبار
          const sidebarSpecialtyElement = document.querySelector('#takhasos-txt');
          if (sidebarSpecialtyElement) {
            sidebarSpecialtyElement.textContent = data.specialty_name;
          }
          // بروزرسانی بخش پروفایل
          updateProfileSpecialtySection(data);
        }
      })
      .catch(error => {
        console.error('خطا در بروزرسانی سایدبار:', error);
      });
  }
  // تابع بروزرسانی بخش تخصص در پروفایل
  function updateProfileSpecialtySection(data) {
    console.log('Updating profile specialty section with data:', data);
    if (data.specialty_title) {
      // بروزرسانی عنوان تخصص
      const specialtyTitleInput = document.querySelector('input[name="specialty_title"]');
      if (specialtyTitleInput) {
        specialtyTitleInput.value = data.specialty_title;
        console.log('Updated specialty title to:', data.specialty_title);
      }
    }
    if (data.academic_degree_id) {
      // بروزرسانی درجه علمی
      const academicDegreeSelect = document.querySelector('select[name="academic_degree_id"]');
      if (academicDegreeSelect) {
        academicDegreeSelect.value = data.academic_degree_id;
        // بروزرسانی TomSelect اگر استفاده می‌شود
        if (academicDegreeSelect.tomselect) {
          academicDegreeSelect.tomselect.setValue(data.academic_degree_id);
        }
        console.log('Updated academic degree to:', data.academic_degree_id);
      }
    }
    if (data.specialty_id) {
      // بروزرسانی تخصص
      const specialtySelect = document.querySelector('select[name="specialty_id"]');
      if (specialtySelect) {
        specialtySelect.value = data.specialty_id;
        // بروزرسانی TomSelect اگر استفاده می‌شود
        if (specialtySelect.tomselect) {
          specialtySelect.tomselect.setValue(data.specialty_id);
        }
        console.log('Updated specialty to:', data.specialty_id);
      }
    }
    // بروزرسانی نام تخصص در بالای پروفایل
    if (data.specialty_name) {
      const profileSpecialtyBadge = document.querySelector('.profile-header-name + .badge');
      if (profileSpecialtyBadge) {
        profileSpecialtyBadge.textContent = data.specialty_name;
        console.log('Updated profile header specialty to:', data.specialty_name);
      }
    }
    // بروزرسانی تخصص‌های اضافی اگر وجود داشته باشند
    if (data.additional_specialties && Array.isArray(data.additional_specialties)) {
      updateSpecialties(data.additional_specialties);
      console.log('Updated additional specialties');
    }
    // بروزرسانی وضعیت دکمه اضافه کردن
    updateAddButtonState();
  }
  // ==================== سوالات متداول ====================
  // فرم افزودن سوال متداول
  document.addEventListener('DOMContentLoaded', function() {
    const faqForm = document.getElementById('faqForm');
    if (faqForm) {
      faqForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(faqForm);
        const button = faqForm.querySelector('button[type="submit"]');
        const buttonText = button.querySelector('.button_text');
        const loader = button.querySelector('.loader');
        // نمایش لودینگ
        buttonText.style.display = 'none';
        loader.style.display = 'block';
        fetch("{{ route('mc-faqs-store') }}", {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'Accept': 'application/json',
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              question: formData.get('question'),
              answer: formData.get('answer'),
              order: formData.get('order') || 0,
              is_active: true
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              toastr.success(data.message);
              refreshFaqList(); // بروزرسانی کامل لیست از سرور
              faqForm.reset();
            } else {
              toastr.error(data.message || 'خطا در افزودن سوال متداول');
            }
          })
          .catch(error => {
            toastr.error('خطا در برقراری ارتباط با سرور');
          })
          .finally(() => {
            buttonText.style.display = 'block';
            loader.style.display = 'none';
          });
      });
    }
  });
  // افزودن سوال جدید به لیست
  function addFaqToList(faq) {
    const faqsList = document.getElementById('faqsList');
    if (!faqsList) return;

    // حذف پیام خالی اگر وجود دارد
    const emptyMessage = faqsList.querySelector('.text-center');
    if (emptyMessage) {
      emptyMessage.remove();
    }

    const faqHtml = `
      <div class="faq-item" data-faq-id="${faq.id}">
        <div class="faq-item-header">
          <div class="faq-item-status">
            <span class="status-badge ${faq.is_active ? 'active' : 'inactive'}">
              ${faq.is_active ? 'فعال' : 'غیرفعال'}
            </span>
            <span class="order-badge">ترتیب: ${faq.order}</span>
          </div>
          <div class="faq-item-actions">
            <button type="button" class="action-btn edit-btn edit-faq-btn" 
                    data-faq-id="${faq.id}" title="ویرایش">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M11 4H4C3.46957 4 2.96086 4.21071 2.58579 4.58579C2.21071 4.96086 2 5.46957 2 6V20C2 20.5304 2.21071 21.0391 2.58579 21.4142C2.96086 21.7893 3.46957 22 4 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V13" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M18.5 2.50023C18.8978 2.10297 19.4374 1.87891 20 1.87891C20.5626 1.87891 21.1022 2.10297 21.5 2.50023C21.8978 2.89749 22.1219 3.43705 22.1219 3.99973C22.1219 4.56241 21.8978 5.10197 21.5 5.49923L12 14.9992L8 15.9992L9 11.9992L18.5 2.50023Z" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
            <button type="button" class="action-btn delete-btn delete-faq-btn" 
                    data-faq-id="${faq.id}" title="حذف">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M3 6H5H21" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M8 6V4C8 3.46957 8.21071 2.96086 8.58579 2.58579C8.96086 2.21071 9.46957 2 10 2H14C14.5304 2 15.0391 2.21071 15.4142 2.58579C15.7893 2.96086 16 3.46957 16 4V6M19 6V20C19 20.5304 18.7893 21.0391 18.4142 21.4142C18.0391 21.7893 17.5304 22 17 22H7C6.46957 22 5.96086 21.7893 5.58579 21.4142C5.21071 21.0391 5 20.5304 5 20V6H19Z" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
          </div>
        </div>
        <div class="faq-item-content">
          <h6 class="faq-question">${faq.question}</h6>
          <p class="faq-answer">${faq.answer.length > 150 ? faq.answer.substring(0, 150) + '...' : faq.answer}</p>
        </div>
      </div>
    `;
    faqsList.insertAdjacentHTML('beforeend', faqHtml);
  }
  // حذف سوال متداول
  document.addEventListener('click', function(e) {
    if (e.target.closest('.delete-faq-btn')) {
      const faqId = e.target.closest('.delete-faq-btn').getAttribute('data-faq-id');
      Swal.fire({
        title: 'حذف سوال متداول',
        text: 'آیا مطمئن هستید که می‌خواهید این سوال متداول را حذف کنید؟',

        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'بله، حذف کن',
        cancelButtonText: 'خیر'
      }).then((result) => {
        if (result.isConfirmed) {
          deleteFaq(faqId);
        }
      });
    }
  });

  function deleteFaq(faqId) {
    fetch(`{{ route('mc-faqs-delete', ['id' => '__ID__']) }}`.replace('__ID__', faqId), {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json',
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          toastr.success(data.message);
          refreshFaqList(); // بروزرسانی کامل لیست از سرور
        } else {
          toastr.error(data.message || 'خطا در حذف سوال متداول');
        }
      })
      .catch(error => {
        console.error('Error deleting FAQ:', error);
        toastr.error('خطا در برقراری ارتباط با سرور');
      });
  }
  // ویرایش سوال متداول
  document.addEventListener('click', function(e) {
    if (e.target.closest('.edit-faq-btn')) {
      const faqId = e.target.closest('.edit-faq-btn').getAttribute('data-faq-id');
      editFaq(faqId);
    }
  });

  function editFaq(faqId) {
    fetch(`{{ route('mc-faqs-get', ['id' => '__ID__']) }}`.replace('__ID__', faqId), {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.json();
      })
      .then(data => {
        if (data.success) {
          showEditFaqModal(data.faq);
        } else {
          toastr.error(data.message || 'خطا در دریافت اطلاعات سوال متداول');
        }
      })
      .catch(error => {
        console.error('Error fetching FAQ:', error);
        toastr.error('خطا در برقراری ارتباط با سرور');
      });
  }

  function showEditFaqModal(faq) {
    // حذف modal قبلی اگر وجود دارد
    const existingModal = document.getElementById('editFaqModal');
    if (existingModal) {
      existingModal.remove();
    }

    const modalHtml = `
      <div class="modal-overlay" id="editFaqModal">
        <div class="modal-container">
          <div class="modal-header">
            <h5 class="modal-title">ویرایش سوال متداول</h5>
            <button type="button" class="modal-close" onclick="closeEditFaqModal()">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M18 6L6 18M6 6L18 18" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
          </div>
          <div class="modal-body">
            <form id="editFaqForm">
              <input type="hidden" id="edit_faq_id" value="${faq.id}">
              <div class="form-row">
                <div class="form-group">
                  <label for="edit_question" class="form-label">سوال</label>
                  <input type="text" id="edit_question" name="question" class="form-control" 
                         value="${faq.question}" maxlength="255">
                  <div class="validation-error"></div>
                </div>
                <div class="form-group">
                  <label for="edit_order" class="form-label">ترتیب نمایش</label>
                  <input type="number" id="edit_order" name="order" class="form-control" 
                         value="${faq.order}" min="0">
                  <div class="validation-error"></div>
                </div>
              </div>
              <div class="form-group">
                <label for="edit_answer" class="form-label">پاسخ</label>
                <textarea id="edit_answer" name="answer" class="form-control" rows="3">${faq.answer}</textarea>
                <div class="validation-error"></div>
              </div>
              <div class="form-group">
                <label class="checkbox-label">
                  <input type="checkbox" id="edit_is_active" name="is_active" ${faq.is_active ? 'checked' : ''}>
                  <span class="checkmark"></span>
                  فعال
                </label>
              </div>
              <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeEditFaqModal()">انصراف</button>
                <button type="submit" class="btn btn-primary">
                  <span class="button_text">ذخیره</span>
                  <div class="loader"></div>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    `;

    // اضافه کردن modal جدید
    document.body.insertAdjacentHTML('beforeend', modalHtml);

    // نمایش modal
    const modalElement = document.getElementById('editFaqModal');
    modalElement.classList.add('show');

    // اضافه کردن event listener برای فرم ویرایش
    document.getElementById('editFaqForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      const button = this.querySelector('button[type="submit"]');
      const buttonText = button.querySelector('.button_text');
      const loader = button.querySelector('.loader');
      const faqId = document.getElementById('edit_faq_id').value;

      // نمایش لودینگ
      buttonText.style.display = 'none';
      loader.style.display = 'block';

      fetch(`{{ route('mc-faqs-update', ['id' => '__ID__']) }}`.replace('__ID__', faqId), {
          method: 'PUT',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            question: formData.get('question'),
            answer: formData.get('answer'),
            order: formData.get('order') || 0,
            is_active: document.getElementById('edit_is_active').checked
          })
        })
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(data => {
          if (data.success) {
            toastr.success(data.message);
            refreshFaqList(); // بروزرسانی کامل لیست از سرور
            closeEditFaqModal();
          } else {
            toastr.error(data.message || 'خطا در ویرایش سوال متداول');
          }
        })
        .catch(error => {
          console.error('Error updating FAQ:', error);
          toastr.error('خطا در برقراری ارتباط با سرور');
        })
        .finally(() => {
          buttonText.style.display = 'block';
          loader.style.display = 'none';
        });
    });
  }

  function closeEditFaqModal() {
    const modalElement = document.getElementById('editFaqModal');
    if (modalElement) {
      modalElement.classList.remove('show');
      setTimeout(() => {
        modalElement.remove();
      }, 300);
    }
  }

  function updateFaqInList(faq) {
    const faqItem = document.querySelector(`[data-faq-id="${faq.id}"]`);
    if (faqItem) {
      const statusBadge = faqItem.querySelector('.status-badge');
      const orderBadge = faqItem.querySelector('.order-badge');
      const question = faqItem.querySelector('.faq-question');
      const answer = faqItem.querySelector('.faq-answer');

      if (statusBadge) {
        statusBadge.className = `status-badge ${faq.is_active ? 'active' : 'inactive'}`;
        statusBadge.textContent = faq.is_active ? 'فعال' : 'غیرفعال';
      }

      if (orderBadge) {
        orderBadge.textContent = `ترتیب: ${faq.order}`;
      }

      if (question) {
        question.textContent = faq.question;
      }

      if (answer) {
        answer.textContent = faq.answer.length > 150 ? faq.answer.substring(0, 150) + '...' : faq.answer;
      }
    }
  }

  // ==================== سوالات متداول ====================

  // تابع مدیریت باز و بسته شدن بخش FAQ
  document.addEventListener('DOMContentLoaded', function() {
    const faqSection = document.getElementById('faq-section');
    const faqHeader = faqSection?.querySelector('.faq-section-clicked');
    const faqContent = faqSection?.querySelector('.faq-section-drop-toggle');

    if (faqHeader && faqContent) {
      faqHeader.addEventListener('click', function() {
        // تغییر وضعیت نمایش محتوا
        if (faqContent.classList.contains('show')) {
          faqContent.classList.remove('show');
          // تغییر آیکون caret
          const caretIcon = faqHeader.querySelector('img[src*="caret"]');
          if (caretIcon) {
            caretIcon.style.transform = 'rotate(0deg)';
          }
        } else {
          faqContent.classList.add('show');
          // تغییر آیکون caret
          const caretIcon = faqHeader.querySelector('img[src*="caret"]');
          if (caretIcon) {
            caretIcon.style.transform = 'rotate(180deg)';
          }
        }
      });

      // تنظیم اولیه آیکون caret
      const caretIcon = faqHeader.querySelector('img[src*="caret"]');
      if (caretIcon) {
        caretIcon.style.transition = 'transform 0.3s ease';
        caretIcon.style.transform = 'rotate(0deg)';
      }
    }
  });

  // فرم افزودن سوال متداول
  // تابع بروزرسانی لیست سوالات متداول از سرور
  function refreshFaqList() {
    const faqsList = document.getElementById('faqsList');
    if (!faqsList) return;

    // نمایش لودینگ
    faqsList.innerHTML = `
      <div class="text-center py-4">
        <div class="loading-spinner"></div>
        <p class="text-muted mt-2">در حال بارگذاری...</p>
      </div>
    `;

    // دریافت لیست جدید از سرور
    fetch("{{ route('mc-faqs-index') }}", {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success && data.faqs) {
          renderFaqList(data.faqs);
        } else {
          showEmptyFaqList();
        }
      })
      .catch(error => {
        console.error('Error refreshing FAQ list:', error);
        showEmptyFaqList();
      });
  }

  // تابع نمایش لیست سوالات متداول
  function renderFaqList(faqs) {
    const faqsList = document.getElementById('faqsList');
    if (!faqsList) return;

    if (!faqs || faqs.length === 0) {
      showEmptyFaqList();
      return;
    }

    faqsList.innerHTML = '';

    faqs.forEach(faq => {
      const faqHtml = `
        <div class="faq-item-compact" data-faq-id="${faq.id}">
          <div class="faq-item-header-compact">
            <div class="faq-item-main">
              <div class="faq-item-title">
                <h6 class="faq-question-text">${faq.question}</h6>
                <div class="faq-item-badges">
                  <span class="badge ${faq.is_active ? 'bg-success' : 'bg-secondary'} badge-sm">
                    ${faq.is_active ? 'فعال' : 'غیرفعال'}
                  </span>
                  <span class="badge bg-info badge-sm">ترتیب: ${faq.order}</span>
                </div>
              </div>
              <div class="faq-item-actions-compact">
                <button type="button" class="btn btn-sm btn-light edit-faq-btn" 
                        data-faq-id="${faq.id}" title="ویرایش">
                  <img src="{{ asset('mc-assets/icons/edit.svg') }}" alt="ویرایش" style="width: 14px; height: 14px;">
                </button>
                <button type="button" class="btn btn-sm btn-light delete-faq-btn" 
                        data-faq-id="${faq.id}" title="حذف">
                  <img src="{{ asset('mc-assets/icons/trash.svg') }}" alt="حذف" style="width: 14px; height: 14px;">
                </button>
              </div>
            </div>
            <div class="faq-item-preview">
              <p class="faq-answer-preview">${faq.answer.length > 80 ? faq.answer.substring(0, 80) + '...' : faq.answer}</p>
            </div>
          </div>
        </div>
      `;
      faqsList.insertAdjacentHTML('beforeend', faqHtml);
    });
  }

  // تابع نمایش لیست خالی
  function showEmptyFaqList() {
    const faqsList = document.getElementById('faqsList');
    if (!faqsList) return;

    faqsList.innerHTML = `
      <div class="faq-empty-state">
        <img src="{{ asset('mc-assets/icons/help.svg') }}" alt="" class="faq-empty-icon">
        <p class="faq-empty-text">هنوز سوال متداولی اضافه نکرده‌اید.</p>
      </div>
    `;
  }

  // افزودن سوال جدید به لیست

  // ==================== مدیریت Dropdown افزودن FAQ ====================
  document.addEventListener('DOMContentLoaded', function() {
    const faqAddToggle = document.querySelector('.faq-add-toggle');
    const faqAddForm = document.querySelector('.faq-add-form');
    const faqAddIcon = document.querySelector('.faq-add-icon svg');
    const faqAddCancel = document.querySelector('.faq-add-cancel');

    if (faqAddToggle && faqAddForm) {
      // کلیک روی دکمه افزودن
      faqAddToggle.addEventListener('click', function(event) {
        event.stopPropagation(); // جلوگیری از انتشار رویداد به والدین

        const isOpen = faqAddForm.classList.contains('open');

        if (isOpen) {
          // بستن فرم
          faqAddForm.classList.remove('open');
          faqAddIcon.style.transform = 'rotate(0deg)';
          faqAddToggle.style.background = '#f8fafc';
          faqAddToggle.style.borderColor = '#e5e7eb';
        } else {
          // باز کردن فرم
          faqAddForm.classList.add('open');
          faqAddIcon.style.transform = 'rotate(180deg)';
          faqAddToggle.style.background = '#eff6ff';
          faqAddToggle.style.borderColor = '#3b82f6';
        }
      });

      // دکمه انصراف
      if (faqAddCancel) {
        faqAddCancel.addEventListener('click', function(event) {
          event.stopPropagation(); // جلوگیری از انتشار رویداد به والدین

          faqAddForm.classList.remove('open');
          faqAddIcon.style.transform = 'rotate(0deg)';
          faqAddToggle.style.background = '#f8fafc';
          faqAddToggle.style.borderColor = '#e5e7eb';

          // پاک کردن فرم
          const form = document.getElementById('faqForm');
          if (form) {
            form.reset();
            // پاک کردن پیام‌های خطا
            const errorElements = form.querySelectorAll('.validation-error');
            errorElements.forEach(el => el.textContent = '');
          }
        });
      }

      // بستن فرم بعد از افزودن موفق
      const faqForm = document.getElementById('faqForm');
      if (faqForm) {
        // جلوگیری از انتشار کلیک‌های داخل فرم به والدین
        faqForm.addEventListener('click', function(event) {
          event.stopPropagation();
        });

        const originalSubmitHandler = faqForm.onsubmit;
        faqForm.addEventListener('submit', function(e) {
          // اگر فرم قبلاً submit handler داشته، آن را حفظ کن
          if (originalSubmitHandler) {
            originalSubmitHandler.call(this, e);
          }

          // بعد از submit موفق، فرم را ببند
          setTimeout(() => {
            faqAddForm.classList.remove('open');
            faqAddIcon.style.transform = 'rotate(0deg)';
            faqAddToggle.style.background = '#f8fafc';
            faqAddToggle.style.borderColor = '#e5e7eb';
          }, 1000); // کمی تاخیر برای نمایش پیام موفقیت
        });
      }
    }
  });

  // فوکوس روی اولین اینپوت از سمت چپ در مرحله OTP
  document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('mobileEditModal');
    if (modal) {
      modal.addEventListener('x-modal-opened', function() {
        otpInputs[0].focus();
      });
    }
  });
</script>

{{-- resources/views/dr/panel/profile/option/profile-option.blade.php --}}
<script>
  function updateAlert() {
    fetch("{{ route('dr-check-profile-completeness') }}", {
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
            icon: 'warning',
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
                <label for="title${inputCount}" class="label-top-input-special-takhasos-elem-create">عنوان تخصص</label>
                <input type="text" name="titles[${inputCount}]" id="title${inputCount}" class="form-control h-50 w-100 border-radius-6 mt-3">
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
    fetch("{{ route('dr-check-profile-completeness') }}", {
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
  const deleteSpecialtyRoute = "{{ route('dr-delete-specialty', ['id' => '__ID__']) }}";

  function removeInput(button) {
    // پیدا کردن المان والد که ممکنه دارای data-specialty-id باشه
    const inputGroup = button.closest('.specialty-item');
    const specialtyId = inputGroup.getAttribute('data-specialty-id');

    // اگر specialtyId وجود نداره (ردیف جدیده و توی دیتابیس نیست)
    if (!specialtyId) {
      Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: 'این ردیف حذف خواهد شد.',
        icon: 'warning',
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
      icon: 'warning',
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
                            <label for="title${inputCount + 1}" class="label-top-input-special-takhasos-elem-create">عنوان تخصص</label>
                            <input type="text" name="titles[${inputCount}]" id="title${inputCount + 1}" class="form-control h-50 w-100 border-radius-6 mt-3">
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
  document.getElementById("specialtyEdit").addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const submitButton = form.querySelector('button[type="submit"]');
    const loader = submitButton.querySelector('.loader');
    const buttonText = submitButton.querySelector('.button_text');

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

          // به‌روزرسانی هشدارها و بخش‌های پروفایل
          updateAlert();
          callCheckProfileCompleteness();
          updateProfileSections(data);

          // به‌روزرسانی تخصص‌ها با داده‌های جدید از سرور
          if (data.specialties) {
            updateSpecialties(data.specialties); // این تابع باید تخصص جدید را با ID اضافه کند
          }

          // اطمینان از اینکه دکمه حذف برای تخصص جدید فعال باشد
          const newSpecialtyId = data.new_specialty_id; // فرض می‌کنیم سرور ID تخصص جدید را برمی‌گرداند
          if (newSpecialtyId) {
            const latestInputGroup = document.querySelector('#additionalInputs .specialty-item:last-child');
            if (latestInputGroup) {
              latestInputGroup.setAttribute('data-specialty-id', newSpecialtyId);
              const removeButton = latestInputGroup.querySelector('.remove-form-item');
              removeButton.setAttribute('onclick', `removeInput(this)`); // تنظیم تابع حذف
            }
          }
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
            <img src="http://127.0.0.1:8000/dr-assets/icons/times.svg" alt="" srcset="">
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
                <label for="title${index + 1}" class="label-top-input-special-takhasos-elem-create">عنوان تخصص</label>
                <input type="text" name="titles[${index}]" id="title${index + 1}" class="form-control h-50 w-100 border-radius-6 mt-3" value="${titleValue}">
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
    const mobileRegex =
      /^(?!09{1}(\d)\1{8}$)09(?:01|02|03|12|13|14|15|16|18|19|20|21|22|30|33|35|36|38|39|90|91|92|93|94)\d{7}$/;
    if (!mobileRegex.test(newMobile)) {
      toastr.error("شماره موبایل نامعتبر است");

      // بازگردانی دکمه به حالت اولیه
      buttonText.style.display = 'block';
      loader.style.display = 'none';
      return;
    }
    $.ajax({
      url: "{{ route('dr-send-mobile-otp') }}",
      method: 'POST',
      data: {
        mobile: newMobile
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
    const otpCode = Array.from(otpInputs).map(input => input.value).join('');
    const newMobile = $('#newMobileNumber').val();
    const verifyButton = document.querySelector('#otpInputStep button');
    const loader = verifyButton.querySelector('.loader');
    const buttonText = verifyButton.querySelector('.button_text');
    // بررسی کامل بودن کد
    if (otpCode.length !== 4) {
      toastr.error("لطفاً تمام ارقام کد را وارد کنید");

      return;
    }
    // مخفی کردن متن دکمه و نمایش لودینگ
    buttonText.style.display = 'none';
    loader.style.display = 'block';
    // ادامه عملیات تایید کد
    $.ajax({
      url: `{{ route('dr-mobile-confirm', '') }}/${otpToken}`,
      method: 'POST',
      data: {
        otp: otpCode.split('').map(Number),
        mobile: newMobile
      },
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(response) {
        // بررسی دقیق پاسخ موفقیت
        if (response.success) {
          toastr.success(response.message);

          // به‌روزرسانی المان‌های موبایل در صفحه
          $('input[name="mobile"]').val(response.mobile);
          // بستن مودال
          $('#mobileEditModal').modal('hide');
          // رفرش صفحه برای اطمینان
          setTimeout(() => {
            location.reload();
          }, 1000);
        } else {
          toastr.error(response.message || "خطا در تغییر شماره موبایل");
        }
      },
      error: function(xhr) {
        // مدیریت خطاهای سرور
        let errorMessage = "خطا در تایید کد";
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errorMessage = xhr.responseJSON.message;
        }
        toastr.error(errorMessage);
      },
      complete: function() {
        // بازگردانی دکمه به حالت اولیه
        buttonText.style.display = 'block';
        loader.style.display = 'none';
      }
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
        // محدود کردن به یک کاراکتر عددی
        this.value = this.value.replace(/[^0-9]/g, '');
        // حرکت از چپ به راست برای RTL
        if (this.value.length === 1 && index < otpInputs.length - 1) {
          otpInputs[index + 1].focus();
        }
      });
      input.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && this.value.length === 0 && index > 0) {
          otpInputs[index - 1].focus();
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
      fetch("{{ route('dr-messengers-update') }}", {
          method: 'PUT',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            ita_phone: form.querySelector('input[name="ita_phone"]').value,
            ita_username: form.querySelector('input[name="ita_username"]').value,
            whatsapp_phone: form.querySelector('input[name="whatsapp_phone"]').value,
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
  document.getElementById("staticPasswordForm").addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const submitButton = form.querySelector('button[type="submit"]');
    const loader = submitButton.querySelector('.loader');
    const buttonText = submitButton.querySelector('.button_text');

    buttonText.style.display = 'none';
    loader.style.display = 'block';

    fetch(form.action, {
        method: form.method,
        body: new FormData(form),
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => {
        return response.json().then(data => {
          if (!response.ok) {
            throw {
              status: response.status,
              data: data
            };
          }
          return data;
        });
      })
      .then(data => {
        buttonText.style.display = 'block';
        loader.style.display = 'none';
        if (data.success) {
          toastr.success(data.message || "تنظیمات با موفقیت به‌روزرسانی شد");
        } else {
          toastr.error(data.message || "خطا در به‌روزرسانی تنظیمات");
        }
      })
      .catch(error => {
        buttonText.style.display = 'block';
        loader.style.display = 'none';
        if (error.status === 422 && error.data.errors) {
          handleValidationErrors(error.data.errors, error.data.message);
        } else {
          toastr.error(error.data?.message || 'خطا در برقراری ارتباط با سرور');
        }
      });
  });

  function handleValidationErrors(errors, message) {
    clearPreviousErrors();
    Object.keys(errors).forEach(field => {
      const inputElement = document.querySelector(`[name="${field}"]`);
      if (inputElement) {
        const errorElement = document.createElement('div');
        errorElement.className = 'text-danger validation-error mt-1 font-size-13';
        errorElement.textContent = errors[field][0];
        inputElement.classList.add('is-invalid');
        inputElement.parentNode.insertBefore(errorElement, inputElement.nextSibling);
      }
    });
    toastr.error(message || "لطفاً خطاهای فرم را بررسی کنید.");
  }

  function clearPreviousErrors() {
    document.querySelectorAll('.validation-error').forEach(el => el.remove());
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
  }
  document.addEventListener('DOMContentLoaded', function() {
    const toggleSwitch = document.querySelector('input[name="static_password_enabled"]');
    const passwordInput = document.querySelector('input[name="password"]');
    const confirmPasswordInput = document.querySelector('input[name="password_confirmation"]');
    const saveButton = document.getElementById('btn-save-pass');
    toggleSwitch.addEventListener('change', function() {
      if (this.checked) {
        passwordInput.removeAttribute('disabled');
        confirmPasswordInput.removeAttribute('disabled');
        saveButton.removeAttribute('disabled');
      } else {
        passwordInput.setAttribute('disabled', 'disabled');
        confirmPasswordInput.setAttribute('disabled', 'disabled');
        saveButton.setAttribute('disabled', 'disabled');
        // ارسال درخواست Ajax برای غیرفعال کردن رمز عبور ثابت
        fetch("{{ route('dr-static-password-update') }}", {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'Accept': 'application/json',
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              static_password_enabled: false
            }),
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              toastr.success(data.message);
            } else {
              toastr.error(data.message || "خطا در به‌روزرسانی تنظیمات");
            }
          })
          .catch(error => {
            toastr.error("خطا در برقراری ارتباط با سرور");
          });
      }
    });
  });
  document.addEventListener('DOMContentLoaded', function() {
    const toggleSwitch = document.querySelector('input[name="two_factor_enabled"]');
    const secretInput = document.querySelector('input[name="two_factor_secret"]');
    const saveButton = document.getElementById('btn-save-two-factor');
    toggleSwitch.addEventListener('change', function() {
      if (this.checked) {
        secretInput.removeAttribute('disabled');
        saveButton.removeAttribute('disabled');
      } else {
        secretInput.setAttribute('disabled', 'disabled');
        saveButton.setAttribute('disabled', 'disabled');
        // ارسال درخواست Ajax برای غیرفعال کردن گذرواژه دو مرحله‌ای
        fetch("{{ route('dr-two-factor-update') }}", {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'Accept': 'application/json',
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              two_factor_enabled: 0, // غیرفعال کردن
              two_factor_secret: null // ارسال null به جای رشته خالی
            }),
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              toastr.success(data.message);

            } else {
              toastr.error(data.message || "خطا در به‌روزرسانی تنظیمات");
            }
          })
          .catch(error => {
            toastr.error("خطا در برقراری ارتباط با سرور");
          });
      }
    });
    // ارسال فرم گذرواژه دو مرحله‌ای
    document.getElementById("twoFactorForm").addEventListener('submit', function(e) {
      e.preventDefault();
      const form = this;
      const submitButton = form.querySelector('button[type="submit"]');
      const loader = submitButton.querySelector('.loader');
      const buttonText = submitButton.querySelector('.button_text');
      // Show loading state
      buttonText.style.display = 'none';
      loader.style.display = 'block';
      // اعتبارسنجی کلید مخفی اگر تاگل فعال باشد
      if (toggleSwitch.checked && !secretInput.value) {
        toastr.error("لطفاً کلید مخفی را وارد کنید");

        buttonText.style.display = 'block';
        loader.style.display = 'none';
        return;
      }
      fetch(form.action, {
          method: form.method,
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            two_factor_enabled: toggleSwitch.checked ? 1 : 0,
            two_factor_secret: secretInput.value
          }),
        })
        .then(response => response.json())
        .then(data => {
          // Reset button to initial state
          buttonText.style.display = 'block';
          loader.style.display = 'none';
          if (data.success) {
            // Show success toast
            toastr.success(data.message || "تنظیمات با موفقیت به‌روزرسانی شد");

          } else {
            // Show error toast
            toastr.error(data.message || "خطا در به‌روزرسانی تنظیمات");
          }
        })
        .catch(error => {
          // Reset button to initial state
          buttonText.style.display = 'block';
          loader.style.display = 'none';
          // نمایش خطاهای اعتبارسنجی
          if (error.errors) {
            handleValidationErrors(error.errors);
          } else {
            toastr.error(error.message || 'خطا در برقراری ارتباط با سرور');

          }
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
    fetch("{{ route('dr-check-profile-completeness') }}", {
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

    fetch("{{ route('dr-check-profile-completeness') }}", {
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
    const icon = input.nextElementSibling; // آیکون کنار ورودی
    if (input.type === 'password') {
      input.type = 'text';
      icon.src = 'http://127.0.0.1:8000/dr-assets/icons/hide-pass.svg'; // تغییر به آیکون "نمایش"
    } else {
      input.type = 'password';
      icon.src = 'http://127.0.0.1:8000/dr-assets/icons/show-pass.svg'; // تغییر به آیکون "مخفی"
    }
  }


  // تابع حذف تخصص بدون رفرش
  function removeSpecialty(element, specialtyId) {
    const deleteSpecialtyRoute = "{{ route('dr-delete-specialty', ['id' => '__ID__']) }}";

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
</script>

@extends('dr.panel.doctors-clinic.layouts.master')
@section('styles')
  <link rel="stylesheet" href="{{ asset('dr-assets/panel/css/doctors-clininc/activation/index.css') }}">
  <link rel="stylesheet" href="{{ asset('dr-assets/panel/css/doctors-clinic/duration/duration.css') }}">
@endsection


@section('headerTitle')
  مدت زمان نوبت ها
@endsection

@section('backUrl')
  {{ route('doctors.clinic.cost', $clinicId) }}
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
      <div class="step completed">
        <span class="step-title">آدرس</span>
        <svg class="icon" viewBox="0 0 36 36" fill="none">
          <circle cx="18" cy="18" r="16" stroke="#0d6efd" stroke-width="2" fill="#0d6efd" />
          <path d="M12 18l4 4l8-8" stroke="#fff" stroke-width="2" fill="none" />
        </svg>
      </div>
      <div class="line"></div>
      <div class="step completed">
        <span class="step-title"> بیعانه</span>
        <svg class="icon" viewBox="0 0 36 36" fill="none">
          <circle cx="18" cy="18" r="16" stroke="#0d6efd" stroke-width="2" fill="#0d6efd" />
          <path d="M12 18l4 4l8-8" stroke="#fff" stroke-width="2" fill="none" />
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
    <div class="my-container-fluid mt-2 border-radius-8 d-flex justify-content-center">
      <div class="row justify-content-center">
        <div class="">
          <div class="card p-4">
            <h5 class="text-start fw-bold">مدت زمان هر نوبت بیمار در مطب شما چقدر است؟</h5>
            <div class="d-flex flex-wrap mt-4 gap-10 justify-content-end my-768px-styles-day-and-times">
              <div class="" tabindex="0" role="button">
                <span class="badge-time-styles-plus">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round"
                    class="plasmic-default__svg plasmic_all__FLoMj PlasmicDuration_svg__l9OeP__cvsVD lucide lucide-plus"
                    viewBox="0 0 24 24" height="20px" width="20px" role="img" type="button" aria-haspopup="dialog"
                    aria-expanded="false" aria-controls="radix-:r7:" data-state="closed">
                    <path d="M5 12h14m-7-7v14"></path>
                  </svg>
                </span>
                <span class=""></span>
              </div>
              <div class="" tabindex="0" role="button"><span class="badge-time-styles">5 دقیقه</span>
                <span class=""></span>
              </div>
              <div class="" tabindex="0" role="button">
                <span class="badge-time-styles">
                  10 دقیقه

                </span>
                <span class=""></span>
              </div>
              <div class="" tabindex="0" role="button">
                <span class="badge-time-styles">15 دقیقه</span>
                <span class="">
                </span>
              </div>
              <div class="" tabindex="0" role="button">
                <span class="badge-time-styles">20 دقیقه</span>
                <span class="">
                </span>
              </div>
              <div class="" tabindex="0" role="button">
                <span class="badge-time-styles">30 دقیقه</span>
                <span class="">
                </span>
              </div>
              <div class="" tabindex="0" role="button">
                <span class="badge-time-styles">60 دقیقه</span>
                <span class="">
                </span>
              </div>
            </div>
            <div class="alert alert-secondary text-center mt-3">
              پزشکان حرفه‌ای به‌طور معمول هر ویزیتشان حدود ۱۵ دقیقه طول می‌کشد. اگر در مطب پروسیجرهای درمان انجام
              می‌دهید
              این زمان را طولانی‌تر کنید.
            </div>
            <form id="workingHoursForm">
              @csrf
              <input type="hidden" name="medical_center_id" value="{{ $clinicId }}">
              <input type="hidden" name="doctor_id" value="{{ $doctorId }}">
              <input type="hidden" id="appointment_duration" name="appointment_duration" value="">


              <h6 class="mt-4">آیا با سایت‌های دیگر نوبت‌دهی اینترنتی همکاری دارید؟*</h6>
              <div class="form-check mt-2">
                <input class="form-check-input" type="radio" name="collaboration" id="yesOption" value="1">
                <label class="form-check-label" for="yesOption">
                  بله، و می‌خواهم زمان نوبت‌های به نوبه با آن‌ها تداخل نداشته باشد.
                </label>
              </div>
              <div class="form-check mt-2">
                <input class="form-check-input" type="radio" name="collaboration" id="noOption" value="0"
                  checked>
                <label class="form-check-label" for="noOption">
                  نه، فقط از طریق به نوبه نوبت‌دهی را انجام می‌دهیم.
                </label>
              </div>

              <div class="text-center mt-4">
                <button id="saveButton" type="submit" class="btn my-btn-primary w-100 h-50">ثبت ساعت کاری</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- مودال برای افزودن زمان جدید -->
  <div class="modal fade" id="addDurationModal" tabindex="-1" aria-labelledby="addDurationModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addDurationModalLabel">انتخاب مدت زمان نوبت</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body d-flex justify-content-center align-items-center">
          <button type="button" class="btn btn-outline-secondary mx-2" id="decreaseDuration">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#333"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="feather feather-minus">
              <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
          </button>
          <span id="durationValue" class="mx-3 fw-bold">15</span>
          <button type="button" class="btn btn-outline-secondary mx-2" id="increaseDuration">
            <svg class="feather feather-plus" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" height="20px"
              width="20px" role="img" type="button" aria-haspopup="dialog" aria-expanded="false"
              aria-controls="radix-:r7:" data-state="closed">
              <path d="M5 12h14m-7-7v14"></path>
            </svg>
          </button>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn my-btn-primary w-100" id="addDurationButton">افزودن</button>
        </div>
      </div>
    </div>
  </div>
@endsection


@section('scripts')
  <script src="{{ asset('dr-assets/panel/js/turn/scehedule/sheduleSetting/workhours/workhours.js') }}"></script>
  <script src="{{ asset('dr-assets/panel/js/toastify/toastify.min.js') }}"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const appointmentInput = document.getElementById("appointment_duration");
      const saveButton = document.getElementById("saveButton");
      const plusButton = document.querySelector(".badge-time-styles-plus");
      const durationContainer = document.querySelector(".my-768px-styles-day-and-times");

      // متغیر برای ذخیره مقدار زمان در مودال
      let durationValue = 15; // مقدار اولیه

      // تابع برای تبدیل دقیقه به ساعت و دقیقه
      const formatDuration = (minutes) => {
        const hours = Math.floor(minutes / 60);
        const remainingMinutes = minutes % 60;
        if (hours > 0 && remainingMinutes > 0) {
          return `${hours} ساعت و ${remainingMinutes} دقیقه`;
        } else if (hours > 0) {
          return `${hours} ساعت`;
        }
        return `${minutes} دقیقه`;
      };

      // تابع برای استخراج مقدار عددی از متن (برای مقایسه)
      const parseDuration = (text) => {
        return parseInt(text.replace(/[^0-9]/g, ""), 10);
      };

      // تابع برای ساخت SVG
      const createCheckmarkSVG = () => {
        const svgWrapper = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        svgWrapper.setAttribute("width", "16");
        svgWrapper.setAttribute("height", "16");
        svgWrapper.setAttribute("viewBox", "0 0 16 16");
        svgWrapper.setAttribute("fill", "#7c82fc");
        const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
        path.setAttribute("fill-rule", "evenodd");
        path.setAttribute("clip-rule", "evenodd");
        path.setAttribute(
          "d",
          "M13.8405 3.44714C14.1458 3.72703 14.1664 4.20146 13.8865 4.5068L6.55319 12.5068C6.41496 12.6576 6.22113 12.7454 6.01662 12.7498C5.8121 12.7543 5.61464 12.675 5.47 12.5303L2.13666 9.197C1.84377 8.90411 1.84377 8.42923 2.13666 8.13634C2.42956 7.84345 2.90443 7.84345 3.19732 8.13634L5.97677 10.9158L12.7808 3.49321C13.0607 3.18787 13.5351 3.16724 13.8405 3.44714Z"
        );
        svgWrapper.appendChild(path);
        return svgWrapper;
      };

      // تابع برای مدیریت انتخاب زمان
      const handleBadgeClick = (badge) => {
        const selectedDurationText = badge.textContent.trim();
        const selectedDuration = parseDuration(selectedDurationText); // مقدار عددی (دقیقه)

        // بررسی اینکه این گزینه قبلاً انتخاب شده یا نه
        if (parseInt(appointmentInput.value) === selectedDuration) {
          appointmentInput.value = ""; // حذف انتخاب
          badge.classList.remove("selected"); // استایل غیرفعال کردن
          badge.classList.remove("active-hours"); // حذف کلاس active-hours
          const existingSVG = badge.querySelector("svg"); // بررسی وجود SVG
          if (existingSVG) {
            existingSVG.remove(); // حذف SVG
          }
        } else {
          appointmentInput.value = selectedDuration; // ذخیره مقدار به‌صورت دقیقه

          // حذف کلاس انتخاب و SVG از بقیه گزینه‌ها
          document.querySelectorAll(".badge-time-styles").forEach((el) => {
            el.classList.remove("selected");
            el.classList.remove("active-hours");
            const existingSVG = el.querySelector("svg");
            if (existingSVG) {
              existingSVG.remove();
            }
          });

          // اضافه کردن کلاس و SVG به گزینه انتخاب‌شده
          badge.classList.add("selected");
          badge.classList.add("active-hours");
          badge.appendChild(createCheckmarkSVG());
        }
      };

      // استفاده از Event Delegation برای مدیریت کلیک روی زمان‌ها
      durationContainer.addEventListener("click", function(e) {
        const badge = e.target.closest(".badge-time-styles");
        if (badge) {
          handleBadgeClick(badge);
        }
      });

      // نمایش مودال هنگام کلیک روی دکمه پلاس
      plusButton.addEventListener("click", function() {
        // باز کردن مودال با استفاده از jQuery
        $("#addDurationModal").modal("show");

        // ریست کردن مقدار زمان به مقدار اولیه (اختیاری)
        durationValue = 15;
        document.getElementById("durationValue").textContent = formatDuration(durationValue);
      });

      // افزایش و کاهش زمان در مودال
      document.getElementById("increaseDuration").addEventListener("click", function() {
        durationValue += 5; // افزایش با گام 5 دقیقه
        if (durationValue > 120) durationValue = 120; // حداکثر 120 دقیقه
        document.getElementById("durationValue").textContent = formatDuration(durationValue);
      });

      document.getElementById("decreaseDuration").addEventListener("click", function() {
        durationValue -= 5; // کاهش با گام 5 دقیقه
        if (durationValue < 5) durationValue = 5; // حداقل 5 دقیقه
        document.getElementById("durationValue").textContent = formatDuration(durationValue);
      });

      // افزودن زمان جدید به لیست هنگام کلیک روی دکمه افزودن
      document.getElementById("addDurationButton").addEventListener("click", function() {
        const newDuration = durationValue;

        // بررسی اینکه آیا این زمان قبلاً وجود دارد یا نه
        const existingBadges = Array.from(document.querySelectorAll(".badge-time-styles")).map((badge) =>
          parseDuration(badge.textContent.trim())
        );
        if (existingBadges.includes(newDuration)) {
          toastr.warning("این مدت زمان قبلاً وجود دارد.");
          return;
        }

        // ایجاد یک badge جدید
        const newBadgeDiv = document.createElement("div");
        newBadgeDiv.setAttribute("tabindex", "0");
        newBadgeDiv.setAttribute("role", "button");
        newBadgeDiv.innerHTML = `
        <span class="badge-time-styles">${formatDuration(newDuration)}</span>
        <span></span>
      `;

        // اضافه کردن badge جدید به لیست
        durationContainer.appendChild(newBadgeDiv);

        // بستن مودال با jQuery
        $("#addDurationModal").modal("hide");

        // نمایش پیام موفقیت
        toastr.success("مدت زمان جدید با موفقیت اضافه شد.");
      });

      // ثبت فرم و بررسی خطا در انتخاب نوبت
      document.getElementById("workingHoursForm").addEventListener("submit", async (e) => {
        e.preventDefault();

        if (!appointmentInput.value) {
          toastr.error("لطفاً یک مدت زمان برای نوبت انتخاب کنید.");
          return;
        }

        const form = e.target;
        const formData = new FormData(form);

        saveButton.disabled = true;
        saveButton.innerHTML = `
        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        در حال ثبت...
      `;

        try {
          const response = await fetch("{{ route('duration.store') }}", {
            method: "POST",
            headers: {
              "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
            },
            body: formData,
          });

          const data = await response.json();


          if (data.success) {
            toastr.success(data.message);
            location.href = "{{ route('activation.workhours.index', $clinicId) }}";
          } else {
            toastr.error(data.message || "مشکلی در ذخیره اطلاعات رخ داد.");
          }
        } catch (error) {
          toastr.error("خطا در ارتباط با سرور.");
        } finally {
          saveButton.disabled = false;
          saveButton.innerHTML = "ثبت ساعت کاری";
        }
      });
    });
  </script>
@endsection

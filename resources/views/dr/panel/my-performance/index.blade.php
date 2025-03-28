@extends('dr.panel.layouts.master')

@section('styles')
  <link type="text/css" href="{{ asset('dr-assets/panel/css/panel.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/profile/edit-profile.css') }}" rel="stylesheet" />
  <link type="text/css" href="{{ asset('dr-assets/panel/css/my-performance/my-performance.css') }}" rel="stylesheet" />
  <style>

  </style>
@endsection

@section('site-header')
  {{ 'به نوبه | پنل دکتر' }}
@endsection

@section('content')
@section('bread-crumb-title', 'عملکرد من')

<div class="main-content mb-5">
  <div class="d-flex justify-content-center align-items-center flex-column col-12">
    <!-- هدر پروفایل -->
    <div class="top-profile-info p-3 col-xs-12 col-sm-12 col-md-12 col-lg-8 d-flex">
      <div class="w-100 d-flex justify-content-start">
        <h5 class="font-weight-bold text-dark">
          شاخص‌های عملکرد <b id="doctor-name">در حال بارگذاری...</b> 👋
          <div class="mt-4">
            🚀 <strong style="font-size: 24px;">امتیاز عملکرد شما: <span id="performance-score"
                class="performance-score">در حال بارگذاری...</span> از 100</strong><br>
            برای رشد رتبه و افزایش تعداد مراجعین خود نکات زیر را مد نظر قرار دهید.
          </div>
        </h5>
      </div>
    </div>

    <!-- کارت‌ها -->
    <div id="performance-cards" class="col-xs-12 col-sm-12 col-md-12 col-lg-8">
      <!-- کارت‌ها به‌صورت داینامیک اینجا لود می‌شن -->
      <div class="loading-spinner"></div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('dr-assets/panel/jalali-datepicker/run-jalali.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/dr-panel.js') }}"></script>
<script src="{{ asset('dr-assets/panel/js/sweetalert2/sweetalert2.js') }}"></script>
<script src="{{ asset('dr-assets/js/select2/select2.js') }}"></script>
<script>
  var appointmentsSearchUrl = "{{ route('search.appointments') }}";

  $(document).ready(function() {
    $('.select2').select2({
      placeholder: "انتخاب کنید",
      allowClear: true
    });

    // لود داده‌ها با AJAX
    loadPerformanceData();

    // مدیریت کلیک روی کارت‌ها برای باز و بسته کردن
    $(document).on('click', '.personal-data-clicked', function() {
      const dropToggle = $(this).next('.drop-toggle-styles');
      dropToggle.slideToggle();
      const caret = $(this).find('img[src*="caret"]');
      caret.css('transform', dropToggle.is(':visible') ? 'rotate(180deg)' : 'rotate(0deg)');
    });
  });

  function loadPerformanceData() {
    $.ajax({
      url: "{{ route('dr-my-performance-data') }}",
      type: 'GET',
      success: function(response) {

        // پر کردن هدر
        $('#doctor-name').text(response.doctor_name);
        $('#performance-score').text(response.performance_score);

        // پر کردن کارت‌ها
        const cardsContainer = $('#performance-cards');
        cardsContainer.empty(); // پاک کردن لودینگ
        
        const cards = [{
            icon: response.city_status ? 'tick' : 'cancle',
            title: `شهر محل طبابت شما ${response.city.name} تشخیص داده شده است.`,
            content: `
              تنظیم صحیح شهر و استان محل طبابت، منجر به افزایش اولویت نمایش شما به بیماران آن شهر می‌شود.
              <br>
              <a href="{{ route('dr-edit-profile') }}" class="mt-2 text-primary font-weight-bold">
                برای مشاهده و اصلاح شهر محل طبابت خود کلیک کنید.
              </a>
            `
          },
          {
            icon: response.online_visit_enabled ? 'tick' : 'cancle',
            title: response.online_visit_enabled ? 'ویزیت آنلاین شما فعال است.' :
              'ویزیت آنلاین خود را فعال کنید',
            content: `
              📈 یکی از <b>مهم‌ترین شاخص‌های رشد رتبه در به نوبه</b> فعال بودن ویزیت آنلاین درمانگران است.<br>
              با ارائه یک راه ارتباطی گفتگوی آنلاین، به بیماران فرصت مشورت و ویزیت غیر حضوری را بدهید.<br><br>
              بسیاری از مشاوره‌های پزشکی برای شروع یا پیگیری روند درمان نیازی به مراجعه حضوری ندارند.<br>
              👈 <a class="mt-2 text-primary" href="{{ route('activation.consult.rules') }}" target="_blank" style="font-weight: bold; text-decoration: underline; font-size: 1.2em;">
                برای فعالسازی ویزیت آنلاین خود کلیک کنید.
              </a><br><br>
              امکان غیر فعال کردن ویزیت آنلاین هرزمان که تمایل داشتید از طریق همین پنل میسر هست.
            `
          },
          {
            icon: response.has_enough_reviews ? 'tick' : 'cancle',
            title: response.has_enough_reviews ?
              `تعداد نظرات دریافتی شما (${response.reviews_count}) کافی است.` :
              'تعداد نظرات دریافتی شما کم است.',
            content: `
              تعداد نظر دریافتی شما برای جلب اعتماد بیماران بسیار اهمیت دارد.<br>
              برای مثال میانگین فعلی تعداد نظر در گروه زنان بالای 150 نظر است. روند دریافت این نظرات باید طبیعی باشد. در صورت مشاهده یا گزارش هر رفتار مشکوک، احتمال ثبت جریمه روی صفحه شما وجود دارد.<br>
              از جمله روش‌های صحیح برای افزایش تعداد نظرات دریافتی، ثبت نوبت مراجعین حضوری در به نوبه است. به نوبه از این بیماران نظرسنجی کرده و نظر آنها در صفحه شما ثبت خواهد شد.<br>
              <a href="https://formafzar.com/form/w01s9" class="mt-2 text-primary font-weight-bold">
                برای ثبت درخواست فعال‌سازی ثبت نوبت مراجعین حضوری، کلیک کنید.
              </a>
            `
          },
          {
            icon: response.is_online ? 'tick' : 'cancle',
            title: response.is_online ? 'شما آنلاین هستید!' : 'شما آنلاین نیستید!',
            content: `
              ساعاتی که برای ویزیت آنلاین خود انتخاب می‌کنید فوق‌العاده در شانس شما برای دیده شدن اهمیت دارند.<br>
              در این مورد، معیار موتور جستجوی به نوبه آنلاین بودن شما در همان لحظه جستجوی بیمار است.<br>
              نکته: اگر به‌تازگی حساب خود در به نوبه را باز کرده‌اید، پیشنهاد می‌کنیم علاوه بر ساعات شلوغ، در ساعات کم‌ترافیک که معمولاً پزشکان آنلاین کمتری هستند هم آنلاین باشید (مثل نیمه‌شب، صبح زود و روزهای تعطیل). این ساعات کم رقابت فرصت رشد شما را در به نوبه فراهم خواهند کرد.<br>
              <a href="{{ route('dr-workhours') }}" class="mt-3 font-weight-bold text-primary">
                برای تنظیم ساعات کاری ویزیت (حضوری و آنلاین) خود کلیک کنید.
              </a>
            `
          },
          {
            icon: response.has_in_person_appointments_today ? 'tick' : 'cancle',
            title: response.has_in_person_appointments_today ?
              'نوبت‌دهی حضوری شما برای امروز در دسترس بیماران هست.' :
              'نوبت‌دهی حضوری شما برای امروز فعال نیست.',
            content: `
              نوبت‌های حضوری شما برای امروز تعریف شده‌اند. این امر برای جذب بیماران بسیار مؤثر است.<br>
              تعریف نوبت‌های حضوری نزدیک‌تر می‌تواند تأثیر قابل‌توجهی در جذب بیماران داشته باشد.<br>
              مثلاً اگر نوبت دهی عصر شنبه شما در سایت فعال بوده و زمان نوبت خالی در آن دارید، برای بیماری که عصر شنبه به دنبال دکتر می‌گردد، شما اولویت بالاتری نسبت به سایر پزشکانی که نوبتشان در این روز فعال نیست دارید.<br>
              <a href="{{ route('dr-workhours') }}" class="mt-3 font-weight-bold text-primary">
                برای تنظیم ساعات کاری ویزیت حضوری خود کلیک کنید.
              </a>
            `
          },
          {
            icon: response.has_clear_address ? 'tick' : 'help-p',
            title: 'ثبت آدرس واضح',
            content: `
              آدرس واضح و کاملی که بیمار بدون پرس و جو بتواند به مرکز درمانی شما هدایت شود ثبت کنید.<br>
              آدرس کامل و دقیق بدون شماره تلفن ثبت کنید.<br>
              <a href="{{ route('dr-edit-profile') }}" class="text-primary font-weight-bold mt-2">
                برای مشاهده و اصلاح پروفایل خود کلیک کنید.
              </a>
            `
          },
          {
            icon: response.has_phone_in_address ? 'cancle' : 'help-p',
            title: 'عدم ثبت شماره تلفن در بخش آدرس',
            content: `
              در قسمت آدرس به هیچ وجه تلفن ثبت نکنید.<br>
              در قسمت آدرس فقط اطلاعات مربوط به موقعیت مکانی را وارد کنید.
            `
          },
          {
            icon: response.has_valid_office_phone ? 'tick' : 'help-p',
            title: 'اطمینان از صحت تلفن مطب',
            content: `
              بخش تلفن مطب در صفحه خود را بازبینی کنید و مطمئن شوید که فرمت صحیحی نوشته شده و بیمار با کلیک روی آن، می‌تواند با مطب تماس بگیرد.<br>
              <a href="{{ route('dr-edit-profile') }}" class="font-weight-bold text-primary mt-3">
                از شماره‌های صحیح استفاده کنید. برای مشاهده و اصلاح پروفایل خود کلیک کنید.
              </a>
            `
          },
          {
            icon: response.has_clinic_location_set ? 'tick' : 'help-p',
            title: 'تنظیم موقعیت مطب',
            content: `
    موقعیت جغرافیایی مطب را از طریق ابزار نقشه، بصورت صحیح تنظیم کنید.<br>
    توجه کنید که در جستجوی بیماران، بر اساس موقعیت جغرافیایی بیمار اولویت مشاهده دارید.<br>
    <span class="font-weight-bold text-success mt-3 d-block">
      برای تنظیم موقعیت دقیق مطب خود روی مطب‌های خود کلیک کرده و از روی نقشه آن‌ها را فعال کنید.
    </span>
    <span class="d-block">مطب‌های شما</span>
    ${response.clinics.map(clinic => `
      <a href="${clinic.url}" class="mt-2 text-primary">
        ${clinic.name}
      </a>
    `).join('')}
  `
          },
          {
            icon: response.has_specialties ? 'tick' : 'help-p',
            title: 'درجه علمی و تخصص‌ها',
            content: `
              درجه علمی و تخصص‌هایی که برای خود تنظیم کرده‌اید چیست؟<br>
              دسته‌بندی شما در نتایج جستجو، بر اساس درجه علمی و تخصص انتخابی شماست.<br>
              دقت کنید که در اینجا منظور عنوان مستعار تخصص نیست.<br>
              <a href="{{ route('dr-edit-profile') }}" class="text-primary font-weight-bold mt-3">
                برای اصلاح پروفایل خود کلیک کنید.
              </a>
            `
          },
          {
            icon: response.has_irrelevant_specialty ? 'cancle' : 'help-p',
            title: 'عدم استفاده از عنوان بی‌ربط در بخش تخصص',
            content: `
              در بخش عنوان تخصص، عبارت بی‌ربط مثل تلفن تماس ثبت نکنید.<br>
              از عبارات مرتبط با تخصص خود استفاده کنید. ثبت اطلاعات اشتباه، تاثیر منفی در رتبه شما دارد.
            `
          },
          {
            icon: response.has_lower_degrees ? 'tick' : 'help-p',
            title: 'فرصت درجه‌های علمی پایین‌تر',
            content: `
              معمولاً درمانگران، آخرین مدرک تحصیلی خود را ثبت می‌کنند. ولی این فرصت را دارید تا درجه علمی و تخصص‌های پایین‌تر را هم ثبت کنید.<br>
              مثلاً کاردانی، کارشناسی، پزشک عمومی و تخصص به همراه عنوان مستعار مرتبط با آن‌ها. این موضوع به بیمارانی که آن تخصص‌ها را جستجو می‌کنند کمک می‌کند تا شما راحت‌تر پیدا کنند.<br>
              <a href="{{ route('dr-edit-profile') }}" class="font-weight-bold text-primary mt-3">
                برای اصلاح تخصص خود در بخش پروفایل کلیک کنید.
              </a>
            `
          },
          {
            icon: response.has_proper_specialty_title ? 'tick' : 'help-p',
            title: 'در بخش تخصص از عنوان مناسب استفاده کنید',
            content: `
              برای عنوان مستعار تخصص، ابتدا بالاترین درجه علمی را بگذارید.<br>
              از کلمات پرتکرار مرتبط با رشته خودتان هم می‌توانید استفاده کنید.<br>
              <a href="{{ route('dr-edit-profile') }}" class="font-weight-bold text-primary mt-3">
                برای اصلاح تخصص خود در بخش پروفایل کلیک کنید.
              </a>
            `
          },
          {
            icon: response.has_realistic_titles ? 'tick' : 'cancle',
            title: 'عناوین غیر واقعی استفاده نکنید',
            content: `
              دقت کنید که عنوان مستعار تخصص انتخابی شما، غیر واقعی و فریب‌دهنده بیماران نباشد.<br>
              استفاده از درجه علمی و تخصص‌هایی که معادل مدرک درمانی ندارید، مصداق فریب بیمار است. در صورت مشاهده و یا گزارش کاربران، حتی ممکن است منجر به جریمه برای شما شود.
            `
          },
          {
            icon: response.satisfaction_rate >= 80 ? 'tick' : 'help-p',
            title: 'برای بهبود نرخ رضایت بیماران تلاش کنید.',
            content: `
              نرخ رضایت بیماران فوق‌العاده در به نوبه اهمیت دارد.<br>
              برای افزایش رضایت بیماران، صرفاً درمان عالی اهمیت ندارد و حسی که بیمار از شرح حال‌گیری، صبوری، تجویز، درمان، فالوآپ و پیگیری درمان شما دریافت می‌کند اهمیت دارد.<br>
              نرخ رضایت باید به‌صورت طبیعی رشد کند و هرگونه دستکاری در نرخ و تعداد نظر بیماران خلاف قوانین است.
            `
          },
          {
            icon: response.has_manipulated_reviews ? 'cancle' : 'help-p',
            title: 'عدم دستکاری نظرات',
            content: `
              هرگونه رفتاری که مصداق دستکاری در نرخ و تعداد نظر بیماران باشد، خلاف قوانین است.<br>
              این موضوع به شدت روی رتبه شما اثر منفی می‌گذارد.
            `
          },
          {
            icon: response.has_profile_picture ? 'tick' : 'help-p',
            title: 'تصویر پروفایل',
            content: `
              تصویر مناسبی برای پروفایل خود انتخاب کنید.<br>
              این تصویر اولین مواجهه بیمار با صفحه شماست و در جلب اعتماد وی تاثیر دارد. توصیه می‌کنیم عکس واضح با رزولوشن مناسب و تمام‌رخ انتخاب کنید.<br>
              <a href="{{ route('dr-edit-profile') }}" class="font-weight-bold text-primary mt-3">
                برای اصلاح تصویر در بخش پروفایل خود کلیک کنید.
              </a>
            `
          },
          {
            icon: response.has_clinic_gallery ? 'tick' : 'help-p',
            title: 'گالری تصاویر مطب',
            content: `
              گالری تصاویر مطب روی جلب اعتماد بیمار نسبت به مواجهه حضوری یا آنلاین وی اهمیت زیادی دارد.<br>
              توصیه می‌کنیم تصاویری از محیط معاینه، سالن انتظار و سایر تصاویری که به افزایش اعتماد بیماران کمک می‌کند با حفظ حریم خصوصی بیماران برای صفحه خود درج کنید.<br>
              اگر در مجتمع‌های پزشکان یا درمانگاه‌ها هستید، تصاویر ورودی، طبقات و کروکی (برای راهیابی بهتر بیماران) فوق‌العاده اهمیت دارد.<br>
              تصویر گواهی دوره‌های درمانی، مدارک و جوایز هم می‌توانند در گالری قرار بگیرند.<br>
              تصاویر باید با کیفیت و رزولوشن مناسب باشند. برای تغییر تصاویر گالری باید در زمانی که با حساب خود وارد شده‌اید، به صفحه عمومی پروفایل خود مراجعه کنید.
            `
          },
          {
            icon: response.has_facility_images ? 'tick' : 'help-p',
            title: 'تصاویر امکانات مطب',
            content: `
              اگر در مطب خود، امکانات رفاهی دارید تصاویر مرتبط با آن را ثبت کنید.<br>
              مثلاً آب سرد‌کن، نمازخانه، مبلمان انتظار، اسباب‌بازی برای کودکان.
            `
          },
          {
            icon: response.has_biography ? 'tick' : 'help-p',
            title: 'متن بیوگرافی',
            content: `
              راهگشا بودن متن بیوگرافی برای بیماران، اهمیت دارد.<br>
              پیشنهاد می‌کنیم پاسخ ابهام‌های کلی و سوالات پرتکرار بیماران بازدیدکننده از صفحه خود را ثبت کنید.<br>
              لیست و هزینه خدمات درمانی حضوری خود را ثبت کنید.<br>
              <a href="{{ route('dr-edit-profile') }}" class="font-weight-bold text-primary mt-3">
                برای مشاهده و اصلاح بیوگرافی خود در پروفایل کلیک کنید.
              </a>
            `
          },
          {
            icon: response.has_keywords_in_biography ? 'tick' : 'help-p',
            title: 'کلمات کلیدی مناسبی در بیوگرافی استفاده کنید',
            content: `
              در بیوگرافی از مهارت و تجربه درمان بیماری‌های مختلفی که داشته‌اید صحبت کنید.<br>
              از عبارات مشخص‌کننده خدمات درمانی که ارائه می‌کنید استفاده کنید. این کلمات می‌تواند علل مراجعه بیمارانتان، علائم، بیماری‌ها، پروسیجرها و ... باشد.
            `
          },
          {
            icon: response.has_multiple_messengers ? 'tick' : 'help-p',
            title: 'تنوع در پیام‌رسان',
            content: `
              هرچه مسیرهای دسترسی به شما بیشتر باشد امکان انتخاب شما توسط بیماران بیشتر خواهد بود.<br>
              برای مثال در قسمت تنظیمات پیام‌رسان ویزیت آنلاین واتساپ و ایتا را همزمان فعال کرده و در هر دو پاسخگو باشید.
            `
          },
          {
            icon: response.has_secure_call ? 'tick' : 'help-p',
            title: 'تماس امن',
            content: `
              در قسمت تنظیمات پیام‌رسان ویزیت آنلاین، تماس امن را هم فعال کنید.<br>
              بیماران بتوانند با شما گفتگو کنند.
            `
          },
          {
            icon: response.has_missed_reports ? 'cancle' : 'help-p',
            title: 'گزارش عدم مراجعه',
            content: `
              مراقب گزارش عدم مراجعه و کنسلی نوبت‌ها و یا ثبت شکایت عدم مراجعه باشید.<br>
              این موارد می‌تواند روی رتبه شما تاثیر منفی بگذارد.
            `
          },
          {
            icon: 'help-p',
            title: 'دعوت از پزشکان',
            content: `
              برای افزایش مراجعه بیماران، از سایر پزشکان در شهر خود دعوت کنید تا به به نوبه بپیوندند.<br>
              با افزایش کاربران به نوبه، مراجعه مجدد بیماران به پلتفرم، موجب افزایش بازدید صفحه شما خواهد شد.
            `
          },
          {
            icon: 'help-p',
            title: 'رقابت نکنید، متمایز باشید',
            content: `
              به نوبه روزانه محل مراجعه میلیون‌ها بیمار هست. این مراجعات مملو از فرصت‌های کشف نشده‌اند و حل مشکلات این بیماران کلید گسترش دامنه مراجعین وفادار شماست.<br>
              با تمرکز روی حل مشکلات گروهی از بیماران، از آن‌ها جامعه‌ای از کاربران بسازید که مجدداً به شما مراجعه کنند.
            `
          },
          {
            icon: 'help-p',
            title: 'تعامل با بیماران',
            content: `
              با بیمارانتان در صفحه تعامل کنید.<br>
              با حساب کاربری رسمی خود در به نوبه به نظرات بیماران پاسخ دهید.
            `
          },
          {
            icon: 'help-p',
            title: 'اشتراک‌گذاری لینک صفحه',
            content: `
              با به اشتراک‌گذاری لینک صفحه به نوبه خود در حساب کاربری سایر شبکه‌های اجتماعی‌تان، بازدید صفحه خود را افزایش دهید.<br>
              این موضوع به تقویت ثبت نظرات در صفحه شما هم کمک می‌کند. برای مثال در بیو اینستاگرام خود لینک صفحه خود را قرار بدهید.
            `
          }
        ];

        cards.forEach(card => {
          const cardHtml = `
            <div class="option-card-box-shodow p-3 col-xs-12 col-sm-12 col-md-12 col-lg-12">
              <div class="d-flex justify-content-between align-items-center personal-data-clicked">
                <div>
                  <img src="{{ asset('dr-assets/icons') }}/${card.icon}.svg" alt="">
                  <span class="txt-card-span mx-1 font-weight-bold text-dark">
                    ${card.title}
                  </span>
                </div>
                <div>
                  <img src="{{ asset('dr-assets/icons/caret.svg') }}" alt="">
                </div>
              </div>
              <div class="drop-toggle-styles personal-data-drop-toggle">
                <div class="p-3 w-100">
                  <p>${card.content}</p>
                </div>
              </div>
            </div>
          `;
          cardsContainer.append(cardHtml);
        });
      },
      error: function() {
        Swal.fire({
          icon: 'error',
          title: 'خطا',
          text: 'خطایی در بارگذاری داده‌ها رخ داد. لطفاً دوباره تلاش کنید.',
        });
      }
    });
  }
</script>
@endsection

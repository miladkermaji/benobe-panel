@props(['isChecked' => false, 'id' => ''])
<div class="toggle-appointment-row">
  <div class="toggle-appointment-label">
    <span class="toggle-appointment-title">نوبت‌دهی</span>
    @component('components.custom-tooltip', [
        'title' =>
            'در حالت «آنلاین + دستی»، بیماران می‌توانند به صورت آنلاین نوبت رزرو کنند و همچنین منشی یا پزشک می‌تواند به صورت دستی نوبت ثبت کند.در حالت «دستی»، امکان رزرو آنلاین برای بیماران غیرفعال می‌شود و فقط منشی یا پزشک می‌تواند نوبت‌ها را به صورت دستی ثبت کند.',
        'placement' => 'top',
        'trigger' => 'hover',
    ])
      <span class="toggle-appointment-help" tabindex="0">&#9432;</span>
    @endcomponent
  </div>
  <div class="segmented-toggle {{ $attributes->get('class') }}" dir="ltr" x-data="{
      isChecked: {{ $isChecked ? 'true' : 'false' }},
      tempChecked: {{ $isChecked ? 'true' : 'false' }}
  }">
    <div class="toggle-wrapper">
      <input {{ $isChecked ? 'checked' : '' }} class="toggle-input" id="{{ $id }}" type="checkbox"
        x-on:change="
          if (!$event.target.checked) {
            $event.preventDefault();
            Swal.fire({
              title: 'تغییر به حالت نوبت دهی دستی',
              text: 'آیا مایلید نوبت‌دهی را به حالت دستی تغییر دهید؟ با این تغییر، نوبت‌دهی آنلاین شما غیرفعال شده و تمامی نوبت‌ها بر اساس ساعت کاری تعریف شده و به صورت دستی توسط منشی یا پزشک ثبت خواهد شد.',
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: 'بله، تغییر به دستی',
              cancelButtonText: 'انصراف',
              reverseButtons: true,
              customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary'
              }
            }).then((result) => {
              if (result.isConfirmed) {
                tempChecked = false;
                $wire.set('autoScheduling', false);
                $wire.call('updateAutoScheduling').then(() => {
                  Swal.fire({
                    title: 'تغییر به حالت دستی',
                    text: 'نوبت‌دهی با موفقیت به حالت دستی تغییر یافت.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false,
                  });
                  $dispatch('auto-scheduling-changed', { isEnabled: false });
                });
              } else {
                $event.target.checked = true;
                tempChecked = true;
              }
            });
          } else {
            tempChecked = true;
            $wire.set('autoScheduling', true);
            $wire.call('updateAutoScheduling').then(() => {
              Swal.fire({
                title: 'تغییر به حالت آنلاین + دستی',
                text: 'نوبت‌دهی با موفقیت به حالت آنلاین + دستی تغییر یافت.',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false,
              });
              $dispatch('auto-scheduling-changed', { isEnabled: true });
            });
          }
        "
        :checked="tempChecked" aria-label="تغییر حالت نوبت‌دهی بین آنلاین + دستی و دستی"
        {{ $attributes->except(['class', 'wire:model.live']) }}>
      <div class="toggle-segments">
        <label class="toggle-segment toggle-segment-off" :class="{ 'active': !$wire.autoScheduling }"
          for="{{ $id }}" data-value="false">
          دستی
        </label>
        <label class="toggle-segment toggle-segment-on" :class="{ 'active': $wire.autoScheduling }"
          for="{{ $id }}" data-value="true">
          آنلاین + دستی
        </label>
      </div>
    </div>
  </div>
</div>
<style>
  :root {
    /* پالت رنگی اصلی */
    --primary: #2E86C1;
    /* آبی اصلی - استفاده در دکمه‌ها و لینک‌ها */
    --primary-light: #84CAF9;
    /* آبی روشن - در گرادیان‌ها */
    --secondary: #1DEB3C;
    /* سبز - برای دکمه‌های ثانویه */
    --secondary-hover: #15802A;
    /* سبز تیره‌تر برای هاور */
    --background-light: #F0F8FF;
    /* آبی بسیار روشن - پس‌زمینه بخش‌ها */
    --background-footer: #D4ECFD;
    /* آبی روشن‌تر - فوتر */
    --background-card: #FFFFFF;
    /* سفید - کارت‌ها */
    --text-primary: #000000;
    /* مشکی - متن اصلی */
    --text-secondary: #707070;
    /* خاکستری - متن ثانویه */
    --text-discount: #008000;
    /* سبز - قیمت با تخفیف */
    --text-original: #FF0000;
    /* قرمز - قیمت اولیه */
    --border-neutral: #E5E7EB;
    /* خاکستری روشن - حاشیه‌ها */
    --shadow: rgba(0, 0, 0, 0.35);
    /* سایه‌ها */
    --gradient-instagram-from: #F92CA7;
    /* گرادیان اینستاگرام - شروع */
    --gradient-instagram-to: #6B1A93;
    /* گرادیان اینستاگرام - پایان */
    --button-mobile: #4F9ACD;
    /* آبی متوسط - دکمه‌های موبایل */
    --button-mobile-light: #A2CDEB;
    /* آبی روشن‌تر - دکمه‌های موبایل */
    --support-section: #2E86C1;
    /* آبی - بخش پشتیبانی */
    --support-text: #084D7C;
    /* آبی تیره - متن پشتیبانی */
    --gradient-primary: linear-gradient(90deg,
        var(--primary-light) 0%,
        var(--primary) 100%) !important;
    /* شعاع گوشه‌ها (border-radius) */
    --radius-button: 0.5rem;
    /* 8px - دکمه‌های کوچک */
    --radius-button-large: 1rem;
    /* 16px - دکمه‌های بزرگ */
    --radius-button-xl: 1.25rem;
    /* 20px - دکمه‌های خیلی بزرگ */
    --radius-card: 1.125rem;
    /* 18px - کارت‌ها */
    --radius-footer: 1.875rem;
    /* 30px - فوتر و برخی بخش‌ها */
    --radius-nav: 1.25rem;
    /* 20px - نوار ناوبری */
    --radius-circle: 9999px;
    /* دایره کامل - برای آیکون‌ها */
  }

  .segmented-toggle {
    --bs-toggle-width: 240px;
    /* عرض بزرگ‌تر برای حس پریمیوم */
    --bs-toggle-height: 48px;
    /* ارتفاع مدرن */
    --bs-toggle-font-size: 0.95rem;
    /* فونت خوانا و شیک */
    --bs-toggle-border-radius: 14px;
    /* گوشه‌های گرد نرم */
    --bs-toggle-bg: rgba(255, 255, 255, 0.85);
    /* پس‌زمینه شیشه‌ای */
    --bs-toggle-active-bg: var(--gradient-primary);
    /* گرادیان بنفش-صورتی نئونی */
    --bs-toggle-color: #475569;
    /* رنگ متن غیرفعال */
    --bs-toggle-active-color: #ffffff;
    /* رنگ متن فعال */
    --bs-toggle-shadow: 0 4px 15px rgba(0, 0, 0, 0.1), 0 2px 8px rgba(0, 0, 0, 0.05);
    /* سایه‌های چندلایه */
    --bs-toggle-backdrop-blur: blur(12px);
    /* افکت بلور شیشه‌ای */
    --bs-toggle-transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    /* انیمیشن فنری و پویا */
  }

  .segmented-toggle .toggle-wrapper {
    position: relative;
    width: var(--bs-toggle-width);
    height: var(--bs-toggle-height);
    padding: 4px;
    background: rgba(255, 255, 255, 0.1);
    /* لایه پایه شیشه‌ای */
    border-radius: var(--bs-toggle-border-radius);
    backdrop-filter: var(--bs-toggle-backdrop-blur);
    /* افکت بلور */
    box-shadow: var(--bs-toggle-shadow);
  }

  .segmented-toggle .toggle-input {
    display: none;
  }

  .segmented-toggle .toggle-segments {
    display: flex;
    width: 100%;
    height: 100%;
    background: var(--bs-toggle-bg);
    border-radius: var(--bs-toggle-border-radius);
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.2);
    /* حاشیه ظریف */
    transition: var(--bs-toggle-transition);
  }

  .segmented-toggle .toggle-segment {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--bs-toggle-font-size);
    font-weight: 600;
    /* فونت کمی ضخیم برای حس مدرن */
    color: var(--bs-toggle-color);
    cursor: pointer;
    transition: var(--bs-toggle-transition);
    text-align: center;
    padding: 0 12px;
    white-space: nowrap;
    position: relative;
    z-index: 1;
    user-select: none;
  }

  .segmented-toggle .toggle-segment.active {
    background: var(--bs-toggle-active-bg);
    color: var(--bs-toggle-active-color);
    box-shadow: 0 3px 10px rgba(168, 85, 247, 0.4);
    /* سایه بنفش ملایم */
    transform: translateY(-2px) scale(1.03);
    /* حس بالا آمدن و بزرگ‌نمایی */
  }

  .segmented-toggle .toggle-segment:hover {
    background: rgba(241, 245, 249, 0.8);
    /* هاور شیشه‌ای ملایم */
    color: #1e293b;
    transform: scale(1.01);
    /* میکرواینتراکشن هاور */
  }

  .segmented-toggle .toggle-segment.active:hover {
    background: var(--bs-toggle-active-bg);
    filter: brightness(1.15);
    /* روشن‌تر شدن در هاور */
  }

  .segmented-toggle .toggle-input:focus+.toggle-segments .toggle-segment.active {
    outline: 2px solid #a855f7;
    /* فکوس بنفش برای دسترسی‌پذیری */
    outline-offset: 2px;
    border-radius: var(--bs-toggle-border-radius);
  }

  /* جداکننده بین بخش‌ها */
  .segmented-toggle .toggle-segment-on::before {
    content: '';
    position: absolute;
    left: 0;
    top: 8px;
    bottom: 8px;
    width: 1px;
    background: rgba(71, 85, 105, 0.25);
    /* جداکننده ظریف */
    transition: var(--bs-toggle-transition);
  }

  /* افکت نئونی برای حالت فعال */
  .segmented-toggle .toggle-segment.active::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: var(--bs-toggle-border-radius);
    box-shadow: 0 0 12px rgba(168, 85, 247, 0.5);
    /* درخشش نئونی */
    z-index: -1;
    opacity: 0.6;
    transition: var(--bs-toggle-transition);
  }

  /* انیمیشن برای تغییر حالت */
  .segmented-toggle .toggle-segment {
    transition: background 0.4s ease, color 0.4s ease, transform 0.3s ease, box-shadow 0.4s ease;
  }

  /* استایل برای بخش‌های مشروط */
  .conditional-section {
    transition: opacity 0.5s ease, max-height 0.5s ease;
    overflow: hidden;
  }

  .conditional-section.hidden {
    opacity: 0;
    max-height: 0;
    margin: 0;
    padding: 0;
  }

  /* پاسخ‌گویی برای موبایل */
  @media (max-width: 640px) {
    .segmented-toggle {
      --bs-toggle-width: 180px;
      --bs-toggle-height: 40px;
      --bs-toggle-font-size: 0.9rem;
    }

    .segmented-toggle .toggle-wrapper {
      padding: 3px;
    }
  }

  .toggle-appointment-row {
    display: flex;
    flex-direction: row-reverse;
    align-items: center;
    gap: 1.1rem;
    background: #f8fbff;
    border-radius: 1.1rem;
    box-shadow: 0 2px 12px 0 rgba(30, 40, 60, 0.07);
    padding: 0.7rem 1.1rem;
    margin-bottom: 1.2rem;
    width: fit-content;
    min-width: 220px;
    max-width: 100%;
  }

  .toggle-appointment-label {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    white-space: nowrap;
    font-size: 1.01rem;
    font-weight: 700;
    color: #2E86C1;
    letter-spacing: 0.2px;
    padding-right: 2px;
    flex-shrink: 0;
  }

  .toggle-appointment-title {
    display: inline-block;
    vertical-align: middle;
  }

  .toggle-appointment-help {
    display: inline-block;
    width: 18px;
    height: 18px;
    background: #e3f2fd;
    color: #1976d2;
    border-radius: 50%;
    text-align: center;
    font-size: 15px;
    line-height: 18px;
    cursor: pointer;
    border: 1px solid #b3d6f7;
    transition: background 0.2s;
    margin-right: 2px;
  }

  .toggle-appointment-help:focus,
  .toggle-appointment-help:hover {
    background: #bbdefb;
    outline: none;
  }

  @media (max-width: 640px) {
    .toggle-appointment-row {
      flex-direction: column;
      align-items: stretch;
      gap: 0.5rem;
      padding: 0.6rem 0.5rem;
      min-width: 0;
    }

    .toggle-appointment-label {
      justify-content: center;
      padding-right: 0;
      text-align: center;
    }
  }
</style>

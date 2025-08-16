@props(['isChecked' => false, 'id' => ''])
<div class="button-toggle {{ $attributes->get('class') }}" dir="ltr" x-data="{
    isChecked: {{ $isChecked ? 'true' : 'false' }},
    tempChecked: {{ $isChecked ? 'true' : 'false' }}
}">
  <div class="toggle-wrapper">
    <input {{ $isChecked ? 'checked' : '' }} class="toggle-input" id="{{ $id }}" type="checkbox"
      x-on:change="
                if (!$event.target.checked) {
                    $event.preventDefault();
                    Swal.fire({
                        title: 'تغییر به حالت نوبت‌دهی دستی',
                        text: 'آیا مایلید نوبت‌دهی را به حالت دستی تغییر دهید؟ با این تغییر، نوبت‌دهی آنلاین شما غیرفعال شده و تمامی نوبت‌ها بر اساس ساعت کاری تعریف شده و به صورت دستی توسط منشی یا پزشک ثبت خواهد شد.',
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
    <div class="toggle-buttons">
      <label class="toggle-button toggle-button-off" :class="{ 'active': !tempChecked }" for="{{ $id }}"
        data-value="false">
        نوبت دهی دستی
      </label>
      <label class="toggle-button toggle-button-on" :class="{ 'active': tempChecked }" for="{{ $id }}"
        data-value="true">
        نوبت دهی (آنلاین + دستی)
      </label>
    </div>
  </div>
</div>
<style>
  :root {
    /* پالت رنگی اصلی (بدون تغییر) */
    --primary: #2E86C1;
    --primary-light: #84CAF9;
    --secondary: #1DEB3C;
    --secondary-hover: #15802A;
    --background-light: #F0F8FF;
    --background-footer: #D4ECFD;
    --background-card: #FFFFFF;
    --text-primary: #000000;
    --text-secondary: #707070;
    --text-discount: #008000;
    --text-original: #FF0000;
    --border-neutral: #E5E7EB;
    --shadow: rgba(0, 0, 0, 0.35);
    --gradient-instagram-from: #F92CA7;
    --gradient-instagram-to: #6B1A93;
    --button-mobile: #4F9ACD;
    --button-mobile-light: #A2CDEB;
    --support-section: #2E86C1;
    --support-text: #084D7C;
    --gradient-primary: linear-gradient(90deg, var(--primary-light) 0%, var(--primary) 100%) !important;
    --radius-button: 0.5rem;
    --radius-button-large: 1rem;
    --radius-button-xl: 1.25rem;
    --radius-card: 1.125rem;
    --radius-footer: 1.875rem;
    --radius-nav: 1.25rem;
    --radius-circle: 9999px;
  }

  .button-toggle {
    --bs-toggle-width: auto;
    /* عرض خودکار بر اساس محتوا */
    --bs-toggle-min-width: 340px;
    /* حداقل عرض برای نمایش متن کامل */
    --bs-toggle-height: 48px;
    /* ارتفاع بزرگتر */
    --bs-toggle-font-size: 1rem;
    /* فونت بزرگتر */
    --bs-toggle-border-radius: 12px;
    /* گوشه‌های گرد */
    --bs-toggle-bg: #f1f5f9;
    /* پس‌زمینه خاکستری روشن */
    --bs-toggle-active-bg: var(--gradient-primary);
    /* گرادیان آبی اصلی */
    --bs-toggle-color: #334155;
    /* رنگ متن غیرفعال */
    --bs-toggle-active-color: #ffffff;
    /* رنگ متن فعال */
    --bs-toggle-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    /* سایه قوی‌تر */
    --bs-toggle-glow: 0 0 8px rgba(46, 134, 193, 0.5);
    /* درخشش آبی قوی‌تر */
    --bs-toggle-transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    /* انیمیشن نرم */
  }

  .button-toggle .toggle-wrapper {
    position: relative;
    width: var(--bs-toggle-width);
    min-width: var(--bs-toggle-min-width);
    height: var(--bs-toggle-height);
    display: flex;
    align-items: center;
  }

  .button-toggle .toggle-input {
    display: none;
  }

  .button-toggle .toggle-buttons {
    display: flex;
    width: 100%;
    height: 100%;
    background: var(--bs-toggle-bg);
    border-radius: var(--bs-toggle-border-radius);
    box-shadow: var(--bs-toggle-shadow);
    overflow: hidden;
    border: 2px solid var(--border-neutral);
    transition: var(--bs-toggle-transition);
  }

  .button-toggle .toggle-button {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--bs-toggle-font-size);
    font-weight: 600;
    color: var(--bs-toggle-color);
    cursor: pointer;
    transition: var(--bs-toggle-transition);
    text-align: center;
    padding: 0 12px;
    white-space: nowrap;
    position: relative;
    user-select: none;
    line-height: 1.2;
  }

  .button-toggle .toggle-button.active {
    background: var(--bs-toggle-active-bg);
    color: var(--bs-toggle-active-color);
    box-shadow: var(--bs-toggle-glow);
    transform: scale(1.02);
  }

  .button-toggle .toggle-button:hover {
    background: #e5e7eb;
    color: #1e293b;
    transform: scale(1.01);
  }

  .button-toggle .toggle-button.active:hover {
    background: var(--bs-toggle-active-bg);
    filter: brightness(1.08);
  }

  .button-toggle .toggle-input:focus+.toggle-buttons .toggle-button.active {
    outline: 2px solid var(--primary);
    outline-offset: 2px;
  }

  .button-toggle .toggle-button-on::after {
    content: '';
    position: absolute;
    right: 0;
    top: 10px;
    bottom: 10px;
    width: 1px;
    background: rgba(71, 85, 105, 0.3);
    transition: var(--bs-toggle-transition);
  }

  .button-toggle .toggle-button.active::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: var(--bs-toggle-border-radius);
    box-shadow: var(--bs-toggle-glow);
    z-index: -1;
    opacity: 0.7;
    transition: var(--bs-toggle-transition);
  }

  .button-toggle .toggle-button {
    transition: background 0.3s ease, color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
  }

  .conditional-section {
    transition: opacity 0.4s ease, max-height 0.4s ease;
    overflow: hidden;
  }

  .conditional-section.hidden {
    opacity: 0;
    max-height: 0;
    margin: 0;
    padding: 0;
  }

  @media (max-width: 640px) {
    .button-toggle {
      --bs-toggle-min-width: 300px;
      --bs-toggle-height: 44px;
      --bs-toggle-font-size: 0.9rem;
    }
  }

  @media (max-width: 480px) {
    .button-toggle {
      --bs-toggle-min-width: 280px;
      --bs-toggle-height: 40px;
      --bs-toggle-font-size: 0.85rem;
    }
  }
</style>

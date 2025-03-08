<div class="relative min-h-screen mt-3" dir="rtl">
 <!-- هدر فیکس -->
 <header class="glass-header rounded-b-xl shadow-lg p-3 z-50">
  <div class="container-fluid mx-auto">
   <div class="flex items-center justify-between gap-3 flex-wrap">
    <div class="flex items-center gap-2">
     <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"
      class="hover:rotate-12 transition-transform duration-300">
      <path d="M4 4h16v16H4z" />
      <path d="M4 4l16 16M4 20L20 4" />
     </svg>
     <h4 class="mb-0 font-bold text-white text-lg">صفحه‌ساز</h4>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
     <button wire:click="undo" class="btn btn-glass text-sm py-1 px-3 rounded-full">Undo</button>
     <button wire:click="redo" class="btn btn-glass text-sm py-1 px-3 rounded-full">Redo</button>
     <button wire:click="toggleFullScreenPreview"
      class="btn btn-glass text-sm py-1 px-3 rounded-full">{{ $isFullScreenPreview ? 'خروج' : 'تمام صفحه' }}</button>
     <button wire:click="setPreviewMode('desktop')" class="btn btn-glass text-sm py-1 px-3 rounded-full">دسکتاپ</button>
     <button wire:click="setPreviewMode('tablet')" class="btn btn-glass text-sm py-1 px-3 rounded-full">تبلت</button>
     <button wire:click="setPreviewMode('mobile')" class="btn btn-glass text-sm py-1 px-3 rounded-full">موبایل</button>
     <button wire:click="toggleLivePreview" class="btn btn-gradient-primary text-sm py-1 px-3 rounded-full">پیش‌نمایش
      زنده</button>
    </div>
   </div>
  </div>
 </header>

 <!-- محتوای اصلی -->
 <div class="pt-16 pb-4 overflow-y-auto mt-3" style="height: calc(100vh - 64px);">
  <div class="container-fluid mx-auto px-4">
   <div class="flex gap-4 flex-col md:flex-row">
    <!-- سایدبار چپ -->
    <div class="w-full md:w-80">
     <!-- تاگل فرم افزودن صفحه -->
     <div class="relative">
      <button wire:click="toggleAddForm"
       class="btn btn-gradient-primary w-full py-2 rounded-lg text-white text-sm mb-2">افزودن صفحه
       {{ $isAddFormOpen ? '▲' : '▼' }}</button>
      @if ($isAddFormOpen)
       <div class="card p-4 rounded-xl shadow-lg bg-white absolute top-12 right-0 w-full z-10 animate-slide-in">
        <h6 class="text-indigo-700 font-bold mb-3 text-base">افزودن صفحه</h6>
        <div class="space-y-3">
         <div>
          <label class="block text-sm font-medium text-gray-700">عنوان صفحه</label>
          <input type="text" wire:model="newPageTitle" class="input-shiny p-2 rounded-lg w-full text-sm">
         </div>
         <div>
          <label class="block text-sm font-medium text-gray-700">عنوان متا (SEO)</label>
          <input type="text" wire:model="metaTitle" class="input-shiny p-2 rounded-lg w-full text-sm">
         </div>
         <div>
          <label class="block text-sm font-medium text-gray-700">توضیحات متا (SEO)</label>
          <textarea wire:model="metaDescription" class="input-shiny p-2 rounded-lg w-full text-sm" rows="2"></textarea>
         </div>
         <div class="flex items-center p-3">
          <label class="flex items-center gap-2 text-sm text-gray-700">
           <input type="checkbox" wire:model="isActive" class="form-check-input h-4 w-4 text-indigo-600 cursor-pointer"
            id="isActiveNew">
           <span>فعال</span>
          </label>
         </div>
         <button wire:click="createPage"
          class="btn btn-gradient-success w-full py-2 rounded-lg text-white text-sm">ایجاد صفحه</button>
        </div>
       </div>
      @endif
     </div>

     <!-- المان‌ها -->
     <div class="card p-4 rounded-xl shadow-lg bg-white mt-4">
      <h6 class="text-indigo-700 font-bold mb-3 text-base">المان‌ها</h6>
      <div class="grid grid-cols-2 gap-2">
       @foreach (['text', 'image', 'button', 'video', 'form'] as $type)
        <button wire:click="addElement('{{ $type }}')"
         class="btn btn-outline-primary flex items-center justify-center gap-2 p-2 rounded-lg hover:bg-indigo-50 transition-all duration-300">
         <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          class="hover:rotate-6 transition-transform duration-200">
          @if ($type === 'text')
           <path d="M4 4h16v2H4zM4 10h16v2H4zM4 16h10v2H4z" />
          @elseif ($type === 'image')
           <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
           <circle cx="8.5" cy="8.5" r="1.5" />
           <path d="M21 15l-5-5L5 21" />
          @elseif ($type === 'button')
           <rect x="4" y="8" width="16" height="8" rx="2" />
          @elseif ($type === 'video')
           <path d="M5 4h14v12H5z" />
           <path d="M10 8l5 4-5 4V8z" />
          @elseif ($type === 'form')
           <rect x="4" y="4" width="16" height="16" rx="2" />
           <path d="M8 10h8M8 14h8" />
          @endif
         </svg>
         <span
          class="text-sm font-medium">{{ $type === 'text' ? 'متن' : ($type === 'image' ? 'تصویر' : ($type === 'button' ? 'دکمه' : ($type === 'video' ? 'ویدیو' : ($type === 'form' ? 'فرم' : '')))) }}</span>
        </button>
       @endforeach
      </div>
     </div>


     <!-- تنظیمات المان -->
     @if ($selectedElement)
      <div class="card p-4 mt-4 rounded-xl shadow-lg bg-white">
       <h6 class="text-indigo-700 font-bold mb-3 text-base">تنظیمات المان</h6>
       <div class="space-y-3">
        <div>
         <label class="block text-sm font-medium text-gray-700">محتوا</label>
         @if ($selectedElement->type === 'form')
          <textarea wire:model="content" class="input-shiny p-2 rounded-lg w-full text-sm" rows="3"></textarea>
         @else
          <input type="text" wire:model="content" class="input-shiny p-2 rounded-lg w-full text-sm">
         @endif
        </div>
        @if (in_array($selectedElement->type, ['text', 'button']))
         <div>
          <label class="block text-sm font-medium text-gray-700">رنگ متن</label>
          <input type="color" wire:model="elementSettings.color" class="w-full h-10 rounded-lg cursor-pointer">
         </div>
         <div>
          <label class="block text-sm font-medium text-gray-700">اندازه فونت</label>
          <input type="text" wire:model="elementSettings.font_size"
           class="input-shiny p-2 rounded-lg w-full text-sm">
         </div>
        @endif
        <div>
         <label class="block text-sm font-medium text-gray-700">رنگ پس‌زمینه</label>
         <input type="color" wire:model="elementSettings.background_color"
          class="w-full h-10 rounded-lg cursor-pointer">
        </div>
        <div>
         <label class="block text-sm font-medium text-gray-700">فاصله داخلی (Padding)</label>
         <input type="text" wire:model="elementSettings.padding"
          class="input-shiny p-2 rounded-lg w-full text-sm">
        </div>
        <div>
         <label class="block text-sm font-medium text-gray-700">فاصله خارجی (Margin)</label>
         <input type="text" wire:model="elementSettings.margin" class="input-shiny p-2 rounded-lg w-full text-sm">
        </div>
        <div>
         <label class="block text-sm font-medium text-gray-700">سایه (Box Shadow)</label>
         <input type="text" wire:model="elementSettings.box_shadow"
          class="input-shiny p-2 rounded-lg w-full text-sm" placeholder="0 4px 6px rgba(0,0,0,0.1)">
        </div>
        <div>
         <label class="block text-sm font-medium text-gray-700">بوردر</label>
         <input type="text" wire:model="elementSettings.border" class="input-shiny p-2 rounded-lg w-full text-sm"
          placeholder="1px solid #000">
        </div>
        <div>
         <label class="block text-sm font-medium text-gray-700">شفافیت (Opacity)</label>
         <input type="range" wire:model="elementSettings.opacity" min="0" max="1" step="0.1"
          class="w-full">
        </div>
        <div>
         <label class="block text-sm font-medium text-gray-700">تعداد ستون‌ها (Grid)</label>
         <select wire:model="elementSettings.grid_columns" class="input-shiny p-2 rounded-lg w-full text-sm">
          <option value="1">1 ستون</option>
          <option value="2">2 ستون</option>
          <option value="3">3 ستون</option>
          <option value="4">4 ستون</option>
         </select>
        </div>
        <div>
         <label class="block text-sm font-medium text-gray-700">انیمیشن</label>
         <select wire:model="elementSettings.animation" class="input-shiny p-2 rounded-lg w-full text-sm">
          @foreach ($animations as $animation)
           <option value="{{ $animation }}">{{ $animation }}</option>
          @endforeach
         </select>
        </div>
        <div>
         <label class="block text-sm font-medium text-gray-700">مدت انیمیشن</label>
         <input type="text" wire:model="elementSettings.animation_duration"
          class="input-shiny p-2 rounded-lg w-full text-sm">
        </div>
        <div>
         <div class="">
          <label class="block text-sm font-medium text-gray-700">نمایش در:</label>
          <div class="flex gap-4 mt-2">
           <label class="flex items-center gap-2 text-sm text-gray-700 mx-2">
            <input type="checkbox" wire:model="elementSettings.responsive.desktop"
             class="form-check-input h-4 w-4 text-indigo-600 cursor-pointer">
            <span>دسکتاپ</span>
           </label>
           <label class="flex items-center gap-2 text-sm text-gray-700 mx-2">
            <input type="checkbox" wire:model="elementSettings.responsive.tablet"
             class="form-check-input h-4 w-4 text-indigo-600 cursor-pointer">
            <span>تبلت</span>
           </label>
           <label class="flex items-center gap-2 text-sm text-gray-700 mx-2">
            <input type="checkbox" wire:model="elementSettings.responsive.mobile"
             class="form-check-input h-4 w-4 text-indigo-600 cursor-pointer">
            <span>موبایل</span>
           </label>
          </div>
         </div>
        </div>
        @if (in_array($selectedElement->type, ['image', 'video']))
         <div>
          <label class="block text-sm font-medium text-gray-700">آپلود فایل</label>
          <input type="file" wire:model="uploadedFile" class="input-shiny p-2 rounded-lg w-full text-sm">
         </div>
        @endif
        <div class="flex gap-2">
         <button wire:click="copyElement({{ $selectedElement->id }})"
          class="btn btn-outline-primary w-full py-2 rounded-lg text-sm">کپی</button>
         <button wire:click="pasteElement"
          class="btn btn-outline-primary w-full py-2 rounded-lg text-sm">پیست</button>
        </div>
        <button wire:click="updateElement"
         class="btn btn-gradient-success w-full py-2 rounded-lg text-white text-sm mt-3">ذخیره تغییرات</button>
       </div>
      </div>
     @endif

     <!-- مدیریت لایه‌ها -->
     <div class="card p-4 mt-4 rounded-xl shadow-lg bg-white">
      <h6 class="text-indigo-700 font-bold mb-3 text-base">مدیریت لایه‌ها</h6>
      <ul class="space-y-2">
       @foreach ($elements as $element)
        <li
         class="flex justify-between items-center p-2 rounded-lg shadow-sm hover:bg-indigo-50 transition-all duration-300">
         <span class="text-sm text-gray-700">{{ $element->type }}</span>
         <button wire:click="selectElement({{ $element->id }})"
          class="text-indigo-600 text-sm hover:underline">انتخاب</button>
        </li>
       @endforeach
      </ul>
     </div>
    </div>

    <!-- بخش اصلی: پیش‌نمایش و مدیریت صفحات -->
    <div class="flex-1">
     @if ($selectedPage)
      <div class="card p-4 rounded-xl shadow-lg bg-white">
       <div class="flex justify-between items-center mb-4 flex-wrap gap-3">
      <h5 class="text-indigo-700 font-bold text-lg">{{ $selectedPage->title }}</h5>
      <div class="flex items-center gap-2 flex-wrap">
       <button wire:click="updatePage"
        class="btn btn-gradient-primary py-2 px-4 rounded-lg text-white text-sm">ذخیره</button>
       <button wire:click="exportHtml"
        class="btn btn-gradient-secondary py-2 px-4 rounded-lg text-white text-sm">خروجی HTML</button>
       <button wire:click="saveAsTemplate"
        class="btn btn-gradient-success py-2 px-4 rounded-lg text-white text-sm">ذخیره به‌عنوان قالب</button>
       <input type="text" wire:model="newTemplateName" class="input-shiny p-2 rounded-lg w-32 text-sm"
        placeholder="نام قالب">
      </div>
       </div>

       <!-- پیش‌نمایش -->
       <!-- پیش‌نمایش -->
       <div wire:sortable="updateElementOrder" class="sortable p-4 rounded-xl border-2 border-dashed border-gray-300"
      style="min-height: 500px; background-color: #f9fafb; {{ $isFullScreenPreview ? 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 1000; padding: 20px; background: #fff;' : '' }} {{ $previewMode === 'mobile' ? 'max-width: 400px; margin: 0 auto;' : ($previewMode === 'tablet' ? 'max-width: 768px; margin: 0 auto;' : 'width: 100%;') }}">
      @if ($isFullScreenPreview)
       <button wire:click="toggleFullScreenPreview"
        class="btn btn-danger text-sm py-2 px-4 rounded-full absolute top-4 right-4 z-50">خروج</button>
      @endif
      @forelse ($elements as $element)
       @php
    $defaultSettings = [
      'color' => '#000000',
      'background_color' => '#ffffff',
      'font_size' => '16px',
      'padding' => '10px',
      'margin' => '0px',
      'animation' => 'none',
      'animation_duration' => '1s',
      'responsive' => ['desktop' => true, 'tablet' => true, 'mobile' => true],
      'box_shadow' => 'none',
      'border' => 'none',
      'opacity' => '1',
      'grid_columns' => 1,
      'width' => '100%',
      'height' => 'auto',
      'text_align' => 'right',
      'border_radius' => '0px',
      'rotation' => '0deg',
    ];
    $settings = is_array($element->settings) ? $element->settings : json_decode($element->settings, true);
    $settings = array_merge($defaultSettings, is_array($settings) ? $settings : []);
       @endphp
       <div wire:sortable.item="{{ $element->id }}"
      class="card mb-3 p-3 rounded-lg shadow-md hover:shadow-lg transition-all duration-300"
      style="cursor: move; color: {{ $settings['color'] }}; background-color: {{ $settings['background_color'] }}; font-size: {{ $settings['font_size'] }}; padding: {{ $settings['padding'] }}; margin: {{ $settings['margin'] }}; box-shadow: {{ $settings['box_shadow'] }}; border: {{ $settings['border'] }}; opacity: {{ $settings['opacity'] }}; width: {{ $settings['width'] }}; height: {{ $settings['height'] }}; text-align: {{ $settings['text_align'] }}; border-radius: {{ $settings['border_radius'] }}; transform: rotate({{ $settings['rotation'] }}); animation: {{ $settings['animation'] }} {{ $settings['animation_duration'] }} ease-in-out;">
      <div class="flex justify-between items-center">
       @if ($element->type === 'text')
      <span class="text-base">{{ $element->content }}</span>
       @elseif ($element->type === 'image')
      <img src="{{ $element->content }}" alt="تصویر" class="max-w-full rounded-md"
       onerror="this.src='/storage/default-image.jpg';">
       @elseif ($element->type === 'button')
      <button class="btn btn-primary py-1 px-3 rounded-lg text-sm">{{ $element->content }}</button>
       @elseif ($element->type === 'video')
      <video controls class="w-full max-h-48 rounded-md">
       <source src="{{ $element->content }}" type="video/mp4">
       مرورگر شما از ویدیو پشتیبانی نمی‌کند.
      </video>
       @elseif ($element->type === 'form')
      {!! $element->content !!}

      </div>
      <!-- دکمه‌های قبلی و بعدی -->
      </div>
     @endif
       <div class="flex gap-2">
      <button wire:click="selectElement({{ $element->id }})"
       class="btn btn-sm btn-warning py-1 px-2 rounded-full hover:bg-yellow-600 text-white">
       <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
        stroke-width="2">
        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
       </svg>
      </button>
      <button wire:click="deleteElement({{ $element->id }})"
       class="btn btn-sm btn-danger py-1 px-2 rounded-full hover:bg-red-600 text-white">
       <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
        stroke-width="2">
        <path d="M3 6h18"></path>
        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"></path>
       </svg>
      </button>
       </div>
      </div>
       </div>
    @empty
       <div class="text-center py-4">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2"
         class="mx-auto mb-3">
         <path d="M4 4h16v16H4z" />
         <path d="M4 4l16 16M4 20L20 4" />
        </svg>
        <p class="text-gray-500 text-base">المانی برای نمایش وجود ندارد.</p>
       </div>
      @endforelse
       </div>
      </div>
   @else
      <div class="alert alert-info text-center rounded-xl py-4">لطفاً یک صفحه انتخاب کنید یا صفحه جدیدی بسازید.
      </div>
     @endif

     <!-- لیست صفحات و قالب‌ها -->
     <div class="card p-4 mt-4 rounded-xl shadow-lg bg-white">
      <h6 class="text-indigo-700 font-bold mb-3 text-lg">صفحات و قالب‌ها</h6>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
       <div>
        <h6 class="text-gray-600 font-medium mb-2 text-base">صفحات</h6>
        <ul class="space-y-2">
         @foreach ($pages as $page)
          <li
           class="flex justify-between items-center p-3 rounded-lg shadow-sm hover:shadow-md transition-all duration-300 {{ $selectedPage && $selectedPage->id === $page->id ? 'bg-indigo-50 border-indigo-200' : 'bg-white border-gray-200' }}">
           <a href="#" wire:click="selectPage({{ $page->id }})"
            class="flex items-center gap-2 text-gray-800 hover:text-indigo-700 transition-colors duration-200">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2">
             <path d="M4 4h16v16H4z" />
            </svg>
            <span class="text-sm font-medium">{{ $page->title }}</span>
           </a>
           <div class="flex items-center gap-2">
            <button wire:click="togglePageStatus({{ $page->id }})"
             class="badge {{ $page->is_active ? 'bg-green-500' : 'bg-red-500' }} text-white py-1 px-3 rounded-full text-xs">{{ $page->is_active ? 'فعال' : 'غیرفعال' }}</button>
            <button wire:click="deletePage({{ $page->id }})"
             class="btn btn-sm btn-danger py-1 px-2 rounded-full hover:bg-red-600 text-white">
             <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2">
              <path d="M3 6h18"></path>
              <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"></path>
             </svg>
            </button>
           </div>
          </li>
         @endforeach
        </ul>
       </div>
       <div>
        <h6 class="text-gray-600 font-medium mb-2 text-base">قالب‌ها</h6>
        <ul class="space-y-2">
         @foreach ($templates as $template)
          <li
           class="flex justify-between items-center p-3 rounded-lg shadow-sm hover:shadow-md transition-all duration-300 bg-white border-gray-200">
           <span class="flex items-center gap-2 text-gray-800 hover:text-indigo-700 transition-colors duration-200">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2">
             <rect x="3" y="3" width="18" height="18" rx="2" />
             <path d="M3 9h18M9 3v18" />
            </svg>
            <span class="text-sm font-medium">{{ $template->name }}</span>
           </span>
           <button wire:click="applyTemplate({{ $template->id }})"
            class="btn btn-sm btn-outline-primary py-1 px-3 rounded-full text-xs">استفاده</button>
          </li>
         @endforeach
        </ul>
       </div>
      </div>
     </div>
    </div>
   </div>
  </div>
 </div>

 <!-- پیش‌نمایش زنده -->
 @if ($isLivePreviewOpen)
  <div class="fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center flex-row-reverse">
   <div class="bg-white p-4 rounded-xl shadow-2xl w-11/12 md:w-3/4 max-h-[90vh] overflow-auto relative ">
    <button wire:click="toggleLivePreview" class="btn btn-danger text-sm rounded-full absolute top-4 right-4">
     <img src="{{ asset('admin-assets/icons/times-square-svgrepo-com.svg') }}" alt="" srcset="">
    </button>
    <iframe srcdoc="{{ $generatedHtml }}" class="w-full h-[80vh] rounded-md border-none"></iframe>

   </div>
  </div>
 @endif
 <link rel="stylesheet" href="{{ asset('admin-assets/css/panel/tools/page-builder/page-builder.css') }}">

 <!-- استایل‌ها -->


 <!-- اسکریپت‌ها -->
 <script>
  document.addEventListener('livewire:init', () => {
   Livewire.on('toast', (message, options = {}) => {
    toastr[options.type || 'info'](message);
   });

   Livewire.on('autoSave', () => {
    setInterval(() => {
     Livewire.dispatch('autoSave');
    }, 30000);
   });
  });
 </script>
 <script>
  document.addEventListener('livewire:init', () => {
   const targetNode = document.body; // یا یک المان خاص نزدیک‌تر به sortable
   const observer = new MutationObserver((mutations, observer) => {
    const sortableEl = document.querySelector('[wire\\:sortable="updateElementOrder"]');
    if (sortableEl) {
     new Sortable(sortableEl, {
      animation: 150,
      handle: '[wire\\:sortable\\.item]',
      onEnd: (event) => {
       const items = Array.from(sortableEl.querySelectorAll('[wire\\:sortable\\.item]')).map((item, index) => ({
        id: item.getAttribute('wire:sortable.item'),
        order: index
       }));
       Livewire.dispatch('updateElementOrder', [items]);
      }
     });
     observer.disconnect(); // بعد از پیدا کردن المان، observer را متوقف می‌کنیم
    }
   });
   observer.observe(targetNode, {
    childList: true,
    subtree: true
   });
  });
 </script>
</div>

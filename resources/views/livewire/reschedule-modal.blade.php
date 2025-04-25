<div>
       <div class="modal-header">
           <h6 class="modal-title">جابجایی نوبت</h6>
           <button type="button" class="btn-close" wire:click="hideModal" aria-label="Close"></button>
       </div>
       <div class="modal-body">
           <div class="calendar-header w-100 d-flex justify-content-between align-items-center">
               <div>
                   <button id="prev-month-reschedule" class="btn btn-light">
                       <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                           <g id="Arrow / Chevron_Right_MD">
                               <path id="Vector" d="M10 8L14 12L10 16" stroke="#000000" stroke-width="2"
                                   stroke-linecap="round" stroke-linejoin="round" />
                           </g>
                       </svg>
                   </button>
               </div>
               <div class="w-100">
                   <select id="year-reschedule" class="form-select w-100 border-0"></select>
               </div>
               <div class="w-100">
                   <select id="month-reschedule" class="form-select w-100 border-0"></select>
               </div>
               <div>
                   <button id="next-month-reschedule" class="btn btn-light">
                       <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                           <g id="Arrow / Chevron_Left_MD">
                               <path id="Vector" d="M14 16L10 12L14 8" stroke="#000000" stroke-width="2"
                                   stroke-linecap="round" stroke-linejoin="round" />
                           </g>
                       </svg>
                   </button>
               </div>
           </div>
           <div class="w-100 d-flex justify-content-end">
               <button id="goToFirstAvailableDashboard" class="btn btn-light w-100 border"
                   wire:click="goToFirstAvailableDate">برو به اولین نوبت خالی</button>
           </div>
           <div class="calendar-body calendar-body-g-425 mt-2">
               <div class="calendar-day-name text-center">شنبه</div>
               <div class="calendar-day-name text-center">یک‌شنبه</div>
               <div class="calendar-day-name text-center">دوشنبه</div>
               <div class="calendar-day-name text-center">سه‌شنبه</div>
               <div class="calendar-day-name text-center">چهارشنبه</div>
               <div class="calendar-day-name text-center">پنج‌شنبه</div>
               <div class="calendar-day-name text-center">جمعه</div>
           </div>
           <div class="calendar-body-425 d-none p-2">
               <div class="calendar-day-name text-center">ش</div>
               <div class="calendar-day-name text-center">ی</div>
               <div class="calendar-day-name text-center">د</div>
               <div class="calendar-day-name text-center">س</div>
               <div class="calendar-day-name text-center">چ</div>
               <div class="calendar-day-name text-center">پ</div>
               <div class="calendar-day-name text-center">ج</div>
           </div>
           <div id="calendar-reschedule" class="calendar-body mt-3"></div>
       </div>
   </div>
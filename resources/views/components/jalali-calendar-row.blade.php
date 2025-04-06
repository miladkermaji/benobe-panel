<style>
  .calendar-card {
    background: #ffffff;
    padding: 14px;
    min-width: 120px;
    text-align: center;
    border: 1px solid #e5e7eb;
    border-radius: 4px; /* گوشه‌ها کمی گرد */
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
    transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    cursor: pointer;
  }

  .calendar-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    background: #f9fafb;
  }

  .calendar-card .day-name {
    font-size: 14px; /* فونت کمی ریزتر */
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
  }

  .calendar-card .date {
    font-size: 12px; /* فونت کمی ریزتر */
    font-weight: 500;
    color: #64748b;
  }

  .my-active {
    border: 2px solid #10b981 !important; /* بوردر سبز برای تاریخ امروز */
    background: #f0fdf4;
    box-shadow: 0 3px 10px rgba(16, 185, 129, 0.15);
  }

  .my-active .day-name {
    color: #065f46 !important;
  }

  .my-active .date {
    color: #047857 !important;
  }

  .card-selected {
    border: 2px solid #3b82f6; /* بوردر آبی برای کارت انتخاب‌شده */
    background: #eff6ff;
    box-shadow: 0 3px 10px rgba(59, 130, 246, 0.2);
  }

  .card-selected .day-name {
    color: #1e40af;
  }

  .card-selected .date {
    color: #1e3a8a;
  }

  #calendar {
    overflow-x: auto;
    overflow-y: hidden;
    white-space: nowrap;
    width: 100%;
    padding: 10px 0;
    -ms-overflow-style: none; /* مخفی کردن اسکرول در IE و Edge */
    scrollbar-width: none; /* مخفی کردن اسکرول در Firefox */
  }

  #calendar::-webkit-scrollbar {
    display: none; /* مخفی کردن اسکرول در Chrome و Safari */
  }

  .btn-light {
    background: #ffffff;
    border: 1px solid #d1d5db;
    padding: 8px;
    border-radius: 4px; /* گوشه‌ها کمی گرد */
    transition: background 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
  }

  .btn-light:hover {
    background: #f1f5f9;
    border-color: #10b981 !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
  }

  .btn-light svg {
    vertical-align: middle;
    stroke: #374151;
  }

  .btn-light:hover svg {
    stroke: #10b981;
  }
</style>

<div class="w-100 d-flex justify-content-around align-items-center">
  <div class="w-100 d-flex align-items-center gap-4">
    <div>
      <button id="prevRow" class="btn btn-light">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
          <g id="Arrow / Chevron_Right_MD">
            <path id="Vector" d="M10 8L14 12L10 16" stroke="#000000" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round" />
          </g>
        </svg>
      </button>
    </div>
    <div id="calendar" class="d-flex w-100 justify-content-between gap-4">
      <!-- تقویم اولیه با تاریخ کنونی پر می‌شود -->
    </div>
    <div>
      <button id="nextRow" class="btn btn-light">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
          <g id="Arrow / Chevron_Left_MD">
            <path id="Vector" d="M14 16L10 12L14 8" stroke="#000000" stroke-width="2" stroke-linecap="round"
              stroke-linejoin="round" />
          </g>
        </svg>
      </button>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  let currentDate = moment().format('YYYY-MM-DD');
  const days = 14;
  const calendar = $('#calendar');
  let isAnimating = false;

  function loadCalendar(date) {
    calendar.empty();
    for (let i = 0; i < days; i++) {
      const current = moment(date).add(i, 'days');
      const persianDate = current.locale('fa').format('dddd');
      const persianFormattedDate = current.locale('fa').format('D MMMM YYYY');
      const isActive = current.isSame(moment(), 'day') ? 'my-active' : '';
      const card = `
        <div class="calendar-card btn btn-light ${isActive}" data-date="${current.format('YYYY-MM-DD')}">
          <div class="day-name">${persianDate}</div>
          <div class="date">${persianFormattedDate}</div>
        </div>`;
      calendar.append(card);
    }
  }

  function animateAndLoadCalendar(direction) {
    if (isAnimating) return;

    // دیباگ: هر بار که تابع اجرا می‌شه، این خط توی کنسول نشون داده می‌شه

    isAnimating = true;

    const newDate = direction === 'nextRow'
      ? moment(currentDate).add(days, 'days').format('YYYY-MM-DD')
      : moment(currentDate).subtract(days, 'days').format('YYYY-MM-DD');

    calendar.animate({
      opacity: 0,
      marginLeft: direction === 'nextRow' ? '-50px' : '50px'
    }, 300, function() {
      currentDate = newDate;
      loadCalendar(currentDate);
      calendar.css({
        marginLeft: direction === 'nextRow' ? '50px' : '-50px',
        opacity: 0
      });
      calendar.animate({
        marginLeft: '0px',
        opacity: 1
      }, 300, function() {
        isAnimating = false;
      });
    });
  }

  calendar.on('click', '.calendar-card', function() {
    $('.calendar-card').removeClass('card-selected');
    $(this).addClass('card-selected');
  });

  $('#nextRow').click(function() {
    animateAndLoadCalendar('nextRow');
  });

  $('#prevRow').click(function() {
    animateAndLoadCalendar('prevRow');
  });

  loadCalendar(currentDate);
});
</script>
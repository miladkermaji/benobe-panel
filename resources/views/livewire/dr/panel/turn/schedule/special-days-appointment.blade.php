<div>
  <div wire:ignore>
    <x-special-days-calendar />
  </div>

  <!-- مودال تعطیلات -->
  <div>
    <x-custom-modal id="holiday-modal" title="مدیریت تعطیلات و ساعات کاری"
      size="{{ $selectedDate && in_array($selectedDate, $holidaysData['holidays']) ? 'sm' : 'lg' }}" :show="$showModal"
      wire:key="holiday-modal-{{ $selectedDate ?? 'default' }}">
      <div class="">
        @php
          $isPastDate = $selectedDate ? \Carbon\Carbon::parse($selectedDate)->isPast() : false;
        @endphp
        @if ($selectedDate && in_array($selectedDate, $holidaysData['holidays'] ?? []))
          <div class="alert alert-warning" role="alert">
            <h4 class="alert-heading">تأیید تغییر وضعیت تعطیلات</h4>
            <p>این روز تعطیل است. آیا می‌خواهید آن را از تعطیلی خارج کنید؟</p>
            <hr>
            <div class="d-flex justify-content-center gap-2 mt-3">
              <button class="btn btn-primary w-100 h-50" wire:click="removeHoliday"
                {{ $isProcessing || $isPastDate ? 'disabled' : '' }}>خروج از تعطیلی</button>
              <button class="btn btn-secondary w-100 h-50" wire:click="closeModal"
                {{ $isProcessing ? 'disabled' : '' }}>لغو</button>
            </div>
          </div>
        @else
          <div class="alert alert-info" role="alert">
            <p class="fw-bold text-center">این روز تعطیل نیست در صورت تمایل میتوانید این روز را تعطیل کنید.</p>
          </div>
          <livewire:dr.panel.turn.schedule.special-workhours :selectedDate="$selectedDate" :clinicId="$selectedClinicId" />
          <div class="d-flex justify-content-center gap-2">
            <button class="btn btn-danger w-100 h-50" wire:click="addHoliday"
              {{ $isProcessing || $isPastDate ? 'disabled' : '' }}>تعطیل کردن</button>
            <button class="btn btn-secondary w-100 h-50" wire:click="closeModal"
              {{ $isProcessing ? 'disabled' : '' }}>لغو</button>
          </div>
        @endif
      </div>
    </x-custom-modal>
  </div>

  <!-- مودال جابجایی -->
  <div wire:ignore>
    <x-custom-modal id="transfer-modal" title="جابجایی نوبت‌ها" size="lg" :show="false"
      wire:key="transfer-modal-{{ $selectedDate ?? 'default' }}">
      <div class="alert alert-info" role="alert">
        <p class="fw-bold">
          این روز دارای نوبت است. برای تعطیل کردن باید نوبت‌ها را جابجا کنید
        </p>
      </div>
      <div class="d-flex justify-content-center gap-2 mt-3">
        <button class="btn btn-secondary w-100 h-50" onclick="window.closeXModal('transfer-modal')">بستن</button>
      </div>
    </x-custom-modal>
  </div>


  <script>
    window.holidaysData = @json($holidaysData) || {
      status: true,
      holidays: []
    };
    window.appointmentsData = @json($appointmentsData) || {
      status: true,
      data: []
    };

    document.addEventListener("livewire:initialized", () => {
      console.log('Livewire initialized for SpecialDaysAppointment');

      window.holidaysData = @json($holidaysData) || {
        status: true,
        holidays: []
      };
      window.appointmentsData = @json($appointmentsData) || {
        status: true,
        data: []
      };

      const clinicId = localStorage.getItem("selectedClinicId") || "default";
      if (clinicId !== "default") {
        Livewire.dispatch("setSelectedClinicId", {
          clinicId
        });
      }

      window.addEventListener('openXModal', event => {
        console.log('openXModal event received:', event.detail);
        const modalId = event.detail.id;
        if (modalId) {
          window.openXModal(modalId);
          console.log(`Modal ${modalId} opened`);
        } else {
          console.error('Modal ID not found in openXModal event:', event.detail);
        }
      });

      window.addEventListener('closeXModal', event => {
        console.log('closeXModal event received:', event.detail);
        const modalId = event.detail.id;
        if (modalId) {
          window.closeXModal(modalId);
          console.log(`Modal ${modalId} closed`);
        } else {
          console.error('Modal ID not found in closeXModal event:', event.detail);
        }
      });

      Livewire.on('openTransferModal', (event) => {
        console.log('openTransferModal event received:', event);
        const {
          modalId,
          gregorianDate
        } = event;
        if (modalId === 'transfer-modal' && gregorianDate) {
          window.openXModal(modalId);
          console.log(`Transfer modal opened for date: ${gregorianDate}`);
        }
      });

      try {
        initializeSpecialDaysCalendar();
      } catch (error) {
        console.error('Error initializing special days calendar:', error);
      }
    });
  </script>
</div>

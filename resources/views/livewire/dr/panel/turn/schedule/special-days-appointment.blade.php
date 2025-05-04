<div>
  <div wire:ignore>
    <x-special-days-calendar />
  </div>

  <!-- مودال تعطیلات -->
  <div>
    <x-custom-modal id="holiday-modal" title="مدیریت تعطیلات" size="md" :show="$showModal" wire:key="holiday-modal-{{ $selectedDate ?? 'default' }}">
      <div class="alert alert-warning" role="alert">
        <h4 class="alert-heading">تأیید تغییر وضعیت تعطیلات</h4>
        <p>
          @if ($selectedDate && in_array($selectedDate, $holidaysData['holidays'] ?? []))
            این روز تعطیل است. آیا می‌خواهید آن را از تعطیلی خارج کنید؟
          @else
            این روز تعطیل نیست. آیا می‌خواهید آن را تعطیل کنید؟
          @endif
        </p>
        <hr>
        <div class="d-flex justify-content-center gap-2 mt-3">
          @if ($selectedDate && in_array($selectedDate, $holidaysData['holidays'] ?? []))
            <button class="btn btn-primary w-100 h-50" wire:click="removeHoliday" {{ $isProcessing ? 'disabled' : '' }}>خروج از تعطیلی</button>
          @else
            <button class="btn btn-warning w-100 h-50" wire:click="addHoliday" {{ $isProcessing ? 'disabled' : '' }}>تعطیل کردن</button>
          @endif
          <button class="btn btn-secondary w-100 h-50" wire:click="closeModal" {{ $isProcessing ? 'disabled' : '' }}>لغو</button>
        </div>
      </div>
    </x-custom-modal>
  </div>

  <!-- مودال جابجایی -->
  <div>
    <x-custom-modal id="transfer-modal" title="جابجایی نوبت‌ها" size="md" :show="false" wire:key="transfer-modal-{{ $selectedDate ?? 'default' }}">
      <div class="alert alert-info" role="alert">
        <h4 class="alert-heading">جابجایی نوبت‌ها</h4>
        <p>
          این روز دارای نوبت است. لطفاً نوبت‌ها را به روز دیگری جابجا کنید یا اقدامات لازم را انجام دهید.
        </p>
        <hr>
        <div class="d-flex justify-content-center gap-2 mt-3">
          <button class="btn btn-secondary w-100 h-50" onclick="window.closeXModal('transfer-modal')">بستن</button>
        </div>
      </div>
    </x-custom-modal>
  </div>

  <script>
    window.holidaysData = @json($holidaysData) || { status: true, holidays: [] };
    window.appointmentsData = @json($appointmentsData) || { status: true, data: [] };

    document.addEventListener("livewire:initialized", () => {
      console.log('Livewire initialized for SpecialDaysAppointment');

      window.holidaysData = @json($holidaysData) || { status: true, holidays: [] };
      window.appointmentsData = @json($appointmentsData) || { status: true, data: [] };

      const clinicId = localStorage.getItem("selectedClinicId") || "default";
      if (clinicId !== "default") {
        Livewire.dispatch("setSelectedClinicId", { clinicId });
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

      // اضافه کردن مدیریت مودال جابجایی
      Livewire.on('openTransferModal', (event) => {
        console.log('openTransferModal event received:', event);
        const { modalId, gregorianDate } = event;
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
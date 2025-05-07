<div>
    <div wire:ignore>
        <x-special-days-calendar />
    </div>

    <!-- مودال تعطیلات -->
    <x-modal
        name="holiday-modal"
        title="مدیریت تعطیلات و ساعات کاری"
        size="{{ $selectedDate && in_array($selectedDate, $holidaysData['holidays']) ? 'sm' : 'lg' }}"
        wire:key="holiday-modal-{{ $selectedDate ?? 'default' }}"
    >
        <x-slot:body>
            @php
                $isPastDate = $selectedDate ? \Carbon\Carbon::parse($selectedDate)->isPast() : false;
                $jalaliDate = $selectedDate ? \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($selectedDate))->format('d F Y') : '';
            @endphp
            @if ($selectedDate && in_array($selectedDate, $holidaysData['holidays'] ?? []))
                <div class="alert alert-warning" role="alert">
                    <h4 class="alert-heading">تأیید تغییر وضعیت تعطیلات</h4>
                    <p>روز {{ $jalaliDate }} تعطیل است. آیا می‌خواهید از تعطیلی خارج کنید؟</p>
                    <hr>
                    <div class="d-flex justify-content-center gap-2 mt-3">
                        <button
                            class="btn btn-primary w-100 h-50"
                            wire:click="removeHoliday"
                            {{ $isProcessing || $isPastDate ? 'disabled' : '' }}
                        >
                            خروج از تعطیلی
                        </button>
                        <button
                            class="btn btn-secondary w-100 h-50"
                            x-on:click="$dispatch('close-modal', { name: 'holiday-modal' })"
                            {{ $isProcessing ? 'disabled' : '' }}
                        >
                            لغو
                        </button>
                    </div>
                </div>
            @else
                <livewire:dr.panel.turn.schedule.special-workhours
                    :selectedDate="$selectedDate"
                    :workSchedule="$workSchedule"
                    :clinicId="$selectedClinicId"
                    wire:key="special-workhours-{{ $selectedDate ?? 'default' }}"
                />
                <div class="d-flex justify-content-center gap-2 mt-3">
                    <button
                        class="btn btn-danger w-100 h-50"
                        wire:click="addHoliday"
                        {{ $isProcessing || $isPastDate ? 'disabled' : '' }}
                    >
                        تعطیل کردن
                    </button>
                    <button
                        class="btn btn-secondary w-100 h-50"
                        x-on:click="$dispatch('close-modal', { name: 'holiday-modal' })"
                        {{ $isProcessing ? 'disabled' : '' }}
                    >
                        لغو
                    </button>
                </div>
            @endif
        </x-slot>
    </x-modal>

    <!-- مودال جابجایی -->
    <x-modal
        name="transfer-modal"
        title="جابجایی نوبت‌ها"
        size="lg"
        wire:key="transfer-modal-{{ $selectedDate ?? 'default' }}"
    >
        <x-slot:body>
            <div class="alert alert-info" role="alert">
                <p class="fw-bold">این روز دارای نوبت است. برای تعطیل کردن باید نوبت‌ها را جابجا کنید</p>
            </div>
            <div class="d-flex justify-content-center gap-2 mt-3">
                <button
                    class="btn btn-secondary w-100 h-50"
                    x-on:click="$dispatch('close-modal', { name: 'transfer-modal' })"
                >
                    بستن
                </button>
            </div>
        </x-slot>
    </x-modal>

    <script>
        window.holidaysData = @json($holidaysData) || { status: true, holidays: [] };
        window.appointmentsData = @json($appointmentsData) || { status: true, data: [] };

        document.addEventListener("livewire:initialized", () => {
            window.holidaysData = @json($holidaysData) || { status: true, holidays: [] };
            window.appointmentsData = @json($appointmentsData) || { status: true, data: [] };

            const clinicId = localStorage.getItem("selectedClinicId") || "default";
            if (clinicId !== "default") {
                Livewire.dispatch("setSelectedClinicId", { clinicId });
            }

            Livewire.on('open-modal', ({ id }) => {
                console.log('Dispatching open-modal for:', id);
                window.dispatchEvent(new CustomEvent('open-modal', { detail: { name: id } }));
                Livewire.dispatch('refreshWorkhours');
            });

            Livewire.on('close-modal', ({ id }) => {
                console.log('Dispatching close-modal for:', id);
                window.dispatchEvent(new CustomEvent('close-modal', { detail: { name: id } }));
            });

            Livewire.on('openTransferModal', ({ modalId, gregorianDate }) => {
                if (modalId === 'transfer-modal' && gregorianDate) {
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: { name: modalId } }));
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
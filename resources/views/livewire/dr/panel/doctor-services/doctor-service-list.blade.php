<div class="container-fluid py-4" dir="rtl" wire:init="loadDoctorServices">
    <!-- هدر -->
    <div class="service-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <h1 class="header-title">مدیریت خدمات و بیمه‌ها</h1>
        <div class="header-actions d-flex align-items-center gap-3 flex-wrap">
            <div class="search-container">
                <input type="text" class="search-input" wire:model.live="search" placeholder="جستجو در خدمات...">
                <span class="search-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)"
                        stroke-width="2">
                        <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
                    </svg>
                </span>
            </div>
            <div class="action-buttons d-flex gap-2">
                <a href="{{ route('dr.panel.doctor-services.create') }}"
                    class="btn btn-primary d-flex align-items-center gap-2">
                    <svg style="transform: rotate(180deg)" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14" />
                    </svg>
                    افزودن خدمت
                </a>
                <button wire:click="deleteSelected" class="btn btn-danger d-flex align-items-center gap-2"
                    @if (empty($selectedDoctorServices)) disabled @endif>
                    <svg  width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                    </svg>
                    حذف انتخاب‌شده‌ها
                </button>
            </div>
        </div>
    </div>

    <!-- جدول خدمات -->
    <div class="services-container">
        @if ($readyToLoad)
            @forelse ($insurances as $insurance)
                <!-- نوار بیمه -->
                <div class="insurance-section">
                    <div class="insurance-header">
                        <h2 class="insurance-title">{{ $insurance->name }}</h2>
                    </div>
                    <div class="services-table">
                        <div class="table-header">
                            <span>نام خدمت</span>
                            <span>توضیحات</span>
                            <span>زمان</span>
                            <span>قیمت</span>
                            <span>تخفیف</span>
                            <span>قیمت نهایی</span>
                            <span>وضعیت</span>
                            <span>عملیات</span>
                        </div>
                        @foreach ($insurance->doctorServices as $service)
                            @include('livewire.dr.panel.doctor-services.doctor-service-tree', [
                                'service' => $service,
                                'level' => 0,
                                'index' => $insurances->firstItem() + $loop->index,
                            ])
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <svg width="50" height="50" viewBox="0 0 24 24" fill="none"
                        stroke="var(--text-secondary)" stroke-width="2" class="mb-2">
                        <path d="M5 12h14M12 5l7 7-7 7" />
                    </svg>
                    <p class="text-muted">هیچ خدمتی یافت نشد.</p>
                </div>
            @endforelse
        @else
            <div class="loading-state">
                <p class="text-muted">در حال بارگذاری خدمات...</p>
            </div>
        @endif
    </div>

    <!-- صفحه‌بندی -->
    @if ($insurances && $insurances->hasPages())
        <div class="pagination-container">
            <div class="text-muted">
                نمایش {{ $insurances ? $insurances->firstItem() : 0 }} تا {{ $insurances ? $insurances->lastItem() : 0 }} از
                {{ $insurances ? $insurances->total() : 0 }} ردیف
            </div>
            {{ $insurances->links('livewire::bootstrap') }}
        </div>
    @endif

    <!-- اسکریپت‌ها -->
    <script>
        document.addEventListener('livewire:initialized', function() {
            const selectedClinicId = localStorage.getItem('selectedClinicId') || 'default';
            setTimeout(() => {
                Livewire.dispatch('setSelectedClinicId', {
                    clinicId: selectedClinicId
                });
            }, 100);
        });

        $(document).ready(function() {
            let dropdownOpen = false;
            let selectedClinic = localStorage.getItem('selectedClinic');
            let selectedClinicId = localStorage.getItem('selectedClinicId');

            if (selectedClinic && selectedClinicId) {
                $('.dropdown-label').text(selectedClinic);
                $('.option-card').each(function() {
                    if ($(this).attr('data-id') === selectedClinicId) {
                        $('.option-card').removeClass('card-active');
                        $(this).addClass('card-active');
                    }
                });
            } else {
                localStorage.setItem('selectedClinic', 'ویزیت آنلاین به نوبه');
                localStorage.setItem('selectedClinicId', 'default');
                $('.dropdown-label').text('ویزیت آنلاین به نوبه');
                setTimeout(() => {
                    Livewire.dispatch('setSelectedClinicId', {
                        clinicId: 'default'
                    });
                }, 100);
            }

            function checkInactiveClinics() {
                var hasInactiveClinics = $('.option-card[data-active="0"]').length > 0;
                if (hasInactiveClinics) {
                    $('.dropdown-trigger').addClass('warning');
                } else {
                    $('.dropdown-trigger').removeClass('warning');
                }
            }
            checkInactiveClinics();

            $('.dropdown-trigger').on('click', function(event) {
                event.stopPropagation();
                dropdownOpen = !dropdownOpen;
                $(this).toggleClass('border border-primary');
                $('.my-dropdown-menu').toggleClass('d-none');
                setTimeout(() => {
                    dropdownOpen = $('.my-dropdown-menu').is(':visible');
                }, 100);
            });

            $(document).on('click', function() {
                if (dropdownOpen) {
                    $('.dropdown-trigger').removeClass('border border-primary');
                    $('.my-dropdown-menu').addClass('d-none');
                    dropdownOpen = false;
                }
            });

            $('.my-dropdown-menu').on('click', function(event) {
                event.stopPropagation();
            });

            $('.option-card').on('click', function() {
                let currentDate = moment().format('YYYY-MM-DD');
                let persianDate = moment(currentDate, 'YYYY-MM-DD').locale('fa').format('jYYYY/jMM/jDD');
                var selectedText = $(this).find('.font-weight-bold.d-block.fs-15').text().trim();
                var selectedId = $(this).attr('data-id');
                $('.option-card').removeClass('card-active');
                $(this).addClass('card-active');
                $('.dropdown-label').text(selectedText);
                localStorage.setItem('selectedClinic', selectedText);
                localStorage.setItem('selectedClinicId', selectedId);
                Livewire.dispatch('clinicSelected', {
                    clinicId: selectedId
                });
                $('.dropdown-trigger').removeClass('border border-primary');
                $('.my-dropdown-menu').addClass('d-none');
                dropdownOpen = false;
            });

            Livewire.on('show-alert', (event) => {
                toastr[event.type](event.message);
            });

            Livewire.on('confirm-delete', (event) => {
                Swal.fire({
                    title: 'حذف خدمت',
                    text: 'آیا مطمئن هستید که می‌خواهید این خدمت را حذف کنید؟',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'بله، حذف کن',
                    cancelButtonText: 'خیر'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch('deleteDoctorServiceConfirmed', {
                            id: event.id
                        });
                    }
                });
            });
        });
    </script>
</div>
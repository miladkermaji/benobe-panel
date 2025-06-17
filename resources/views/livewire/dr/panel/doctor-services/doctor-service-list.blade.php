<div class="container-fluid py-4" dir="rtl" wire:init="loadDoctorServices">
    <!-- هدر -->
    <div class="service-header d-flex justify-content-between flex-wrap mb-3">
        <div>
            <h1 class="header-title">مدیریت خدمات و بیمه‌ها</h1>
        </div>
        <div class="header-actions d-flex">
            <div class="search-container">
                <input type="text" class="search-input" wire:model.live="search" placeholder="جستجو در خدمات...">
                <span class="search-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)" stroke-width="2">
                        <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
                    </svg>
                </span>
            </div>
            <div class="action-buttons d-flex gap-2">
                <a href="{{ route('dr.panel.doctor-services.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                    <svg style="transform: rotate(180deg)" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14" />
                    </svg>
                    افزودن خدمت
                </a>
                <button wire:click="deleteSelected" class="btn btn-danger d-flex align-items-center gap-2" @if (empty($selectedDoctorServices)) disabled @endif>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
            @forelse ($services as $service)
                <!-- نوار خدمت -->
                <div class="service-section">
                    <div class="service-header" wire:click="toggleChildren('{{ $service->id }}')">
                        <h2 class="service-title">{{ $service->name }}</h2>
                        <div class="service-toggle {{ in_array($service->id, $openServices) ? 'rotate-180' : '' }}">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M6 9l6 6 6-6" />
                            </svg>
                        </div>
                    </div>
                    <div class="services-table {{ in_array($service->id, $openServices) ? 'show' : '' }}">
                        <div class="table-header">
                            <span>نام بیمه</span>
                            <span>توضیحات</span>
                            <span>زمان</span>
                            <span>قیمت</span>
                            <span>تخفیف</span>
                            <span>قیمت نهایی</span>
                            <span>وضعیت</span>
                            <span>عملیات</span>
                        </div>
                        @if ($service->insurance)
                            @include('livewire.dr.panel.doctor-services.doctor-service-tree', ['service' => $service, 'level' => 0, 'index' => $services->firstItem() + $loop->index])
                        @endif
                        @foreach ($service->children as $child)
                            @if (in_array($service->id, $openServices))
                                @include('livewire.dr.panel.doctor-services.doctor-service-tree', ['service' => $child, 'level' => 1, 'index' => ($services->firstItem() + $loop->parent->index) . '.' . ($loop->index + 1)])
                            @endif
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <svg width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="var(--text-secondary)" stroke-width="2" class="mb-2">
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
    @if ($services && $services->hasPages())
        <div class="pagination-container">
            <div class="text-muted">
                نمایش {{ $services ? $services->firstItem() : 0 }} تا {{ $services ? $services->lastItem() : 0 }} از {{ $services ? $services->total() : 0 }} ردیف
            </div>
            {{ $services->links('livewire::bootstrap') }}
        </div>
    @endif

    <!-- اسکریپت‌ها -->
    <script>
        $(document).ready(function() {
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
                        Livewire.dispatch('deleteDoctorServiceConfirmed', { id: event.id });
                    }
                });
            });
        });
    </script>
</div>
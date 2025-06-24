
<div class="clinics-container">
    <div class="container py-2" dir="rtl" wire:init="loadClinics">
        <div class="glass-header text-white p-2 rounded-2 mb-4 mt-3 shadow-lg bg-gradient-primary">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3 w-100">
                <div class="d-flex flex-column flex-md-row gap-2 w-100 align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <h1 class="m-0 h4 font-thin text-nowrap mb-3 mb-md-0">مدیریت کلینیک‌ها</h1>
                    </div>
                    <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2">
                        <div class="d-flex gap-2 flex-shrink-0 justify-content-center">
                            <div class="search-container position-relative" style="max-width: 100%;">
                                <input type="text"
                                    class="form-control search-input border-0 shadow-none bg-white text-dar k ps-4 rounded-2 text-start"
                                    wire:model.live="search" placeholder="جستجو در کلینیک‌ها..."
                                    style="padding-right: 20px; text-align: right; direction: rtl;">
                                <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-2"
                                    style="z-index: 5; top: 50%; right: 8px;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                                        stroke-width="2">
                                        <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
                                    </svg>
                                </span>
                            </div>
                            <a href="{{ route('admin.panel.clinics.create') }}"
                                class="btn btn-gradient-success rounded-1 px-3 py-1 d-flex align-items-center gap-1">
                                <svg style="transform: rotate(180deg)" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path d="M12 5v14M5 12h14" />
                                </svg>
                                <span>افزودن</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid px-0">
            <div class="card shadow-sm rounded-2">
                <div class="card-body p-0">
                    <!-- Group Actions -->
                    <div class="group-actions p-2 border-bottom" x-data="{ show: false }"
                        x-show="$wire.selectedClinics.length > 0">
                        <div class="d-flex align-items-center gap-2 justify-content-end">
                            <select class="form-select form-select-sm" style="max-width: 200px;" wire:model="groupAction">
                                <option value="">عملیات گروهی</option>
                                <option value="delete">حذف انتخاب شده‌ها</option>
                                <option value="status_active">فعال کردن</option>
                                <option value="status_inactive">غیرفعال کردن</option>
                            </select>
                            <button class="btn btn-sm btn-primary" wire:click="executeGroupAction" wire:loading.attr="disabled">
                                <span wire:loading.remove>اجرا</span>
                                <span wire:loading>در حال اجرا...</span>
                            </button>
                        </div>
                    </div>
                    <!-- Desktop Table View -->
                    <div class="table-responsive text-nowrap d-none d-md-block">
                        <table class="table  w-100 m-0">
                            <thead>
                                <tr>
                                    <th class="text-center align-middle" style="width: 40px;">
                                        <input type="checkbox" wire:model.live="selectAll" class="form-check-input m-0">
                                    </th>
                                    <th class="text-center align-middle" style="width: 60px;">ردیف</th>
                                    <th class="align-middle">نام</th>
                                    <th class="align-middle">پزشکان</th>
                                    <th class="align-middle">تخصص‌ها</th>
                                    <th class="align-middle">بیمه‌ها</th>
                                    <th class="align-middle">استان</th>
                                    <th class="align-middle">شهر</th>
                                    <th class="align-middle">آدرس</th>
                                    <th class="align-middle">توضیحات</th>
                                    <th class="text-center align-middle" style="width: 80px;">گالری</th>
                                    <th class="text-center align-middle" style="width: 100px;">وضعیت</th>
                                    <th class="text-center align-middle" style="width: 120px;">عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($readyToLoad)
                                    @forelse ($clinics as $index => $item)
                                        <tr class="align-middle" x-data="{ showDoctors: false, showSpecialties: false, showInsurances: false }">
                                            <td class="text-center">
                                                <input type="checkbox" wire:model.live="selectedClinics" value="{{ $item->id }}"
                                                    class="form-check-input m-0">
                                            </td>
                                            <td class="text-center">{{ $clinics->firstItem() + $index }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td>
                                                <button class="btn btn-link text-primary p-0" @click="showDoctors = !showDoctors">
                                                    <span class="badge bg-primary-subtle text-primary">{{ $item->doctors->count() }} پزشک</span>
                                                </button>
                                                <div x-show="showDoctors" x-transition class="mt-2 p-2 border rounded shadow-sm" style="max-height: 150px; overflow-y: auto;">
                                                    @if ($item->doctors->isEmpty())
                                                        <span class="text-muted">بدون پزشک</span>
                                                    @else
                                                        @foreach ($item->doctors as $doctor)
                                                            <div class="py-1 border-bottom">
                                                                {{ $doctor->first_name . ' ' . $doctor->last_name }}
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <button class="btn btn-link text-primary p-0" @click="showSpecialties = !showSpecialties">
                                                    <span class="badge bg-info-subtle text-info">{{ count($item->specialty_ids ?? []) }} تخصص</span>
                                                </button>
                                                <div x-show="showSpecialties" x-transition class="mt-2 p-2 border rounded shadow-sm" style="max-height: 150px; overflow-y: auto;">
                                                    @if (empty($item->specialty_ids))
                                                        <span class="text-muted">بدون تخصص</span>
                                                    @else
                                                        @foreach ($item->specialty_ids as $specialtyId)
                                                            <div class="py-1 border-bottom">
                                                                {{ $specialties[$specialtyId] ?? 'نامشخص' }}
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <button class="btn btn-link text-primary p-0" @click="showInsurances = !showInsurances">
                                                    <span class="badge bg-success-subtle text-success">{{ count($item->insurance_ids ?? []) }} بیمه</span>
                                                </button>
                                                <div x-show="showInsurances" x-transition class="mt-2 p-2 border rounded shadow-sm" style="max-height: 150px; overflow-y: auto;">
                                                    @if (empty($item->insurance_ids))
                                                        <span class="text-muted">بدون بیمه</span>
                                                    @else
                                                        @foreach ($item->insurance_ids as $insuranceId)
                                                            <div class="py-1 border-bottom">
                                                                {{ $insurances[$insuranceId] ?? 'نامشخص' }}
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </td>
                                            <td>{{ $item->province?->name ?? '-' }}</td>
                                            <td>{{ $item->city?->name ?? '-' }}</td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 150px;" title="{{ e($item->address) ?? '-' }}">
                                                    {{ e($item->address) ?? '-' }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 150px;" title="{{ e($item->description) ?? '-' }}">
                                                    {{ e($item->description) ?? '-' }}
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.panel.clinics.gallery', $item->id) }}"
                                                    class="btn btn-sm btn-primary">
                                                    <img src="{{ asset('admin-assets/icons/gallery.svg') }}" alt="گالری" width="16">
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-flex justify-content-center">
                                                    <input class="form-check-input" type="checkbox" role="switch"
                                                        wire:click="toggleStatus({{ $item->id }})"
                                                        {{ $item->is_active ? 'checked' : '' }}
                                                        style="width: 3em; height: 1.5em; margin-top: 0;">
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <a href="{{ route('admin.panel.clinics.edit', $item->id) }}"
                                                        class="btn btn-sm btn-gradient-success px-2 py-1">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" stroke-width="2">
                                                            <path
                                                                d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                                        </svg>
                                                    </a>
                                                    <button wire:click="confirmDelete({{ $item->id }})"
                                                        class="btn btn-sm btn-gradient-danger px-2 py-1">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" stroke-width="2">
                                                            <path
                                                                d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="13" class="text-center py-4">
                                                <div class="d-flex justify-content-center align-items-center flex-column">
                                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="2" class="text-muted mb-2">
                                                        <path d="M5 12h14M12 5l7 7-7 7" />
                                                    </svg>
                                                    <p class="text-muted fw-medium">هیچ کلینیکی یافت نشد.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                @else
                                    <tr>
                                        <td colspan="13" class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">در حال بارگذاری...</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <!-- Mobile Card View -->
                    <div class="clinics-cards d-md-none">
                        @if ($readyToLoad)
                            @forelse ($clinics as $index => $item)
                                <div class="clinic-card mb-3 p-3 border rounded-2 shadow-sm" x-data="{ open: false, showDoctors: false, showSpecialties: false, showInsurances: false }">
                                    <div class="clinic-card-header d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="checkbox" wire:model.live="selectedClinics" value="{{ $item->id }}"
                                                class="form-check-input m-0">
                                            <h6 class="m-0 fw-bold">{{ $item->name }}</h6>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <button @click="open = !open" class="btn btn-sm btn-outline-primary p-1">
                                                <svg :class="{ 'rotate-180': open }" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2" class="transition-transform duration-200">
                                                    <path d="M6 9l6 6 6-6" />
                                                </svg>
                                            </button>
                                            <a href="{{ route('admin.panel.clinics.gallery', $item->id) }}"
                                                class="btn btn-sm btn-primary px-2 py-1">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M4 16v4h4M4 20l4-4M20 8v-4h-4M20 4l-4 4M4 4v4M4 4h4M20 20v-4h-4M20 20l-4-4" />
                                                </svg>
                                            </a>
                                            <a href="{{ route('admin.panel.clinics.edit', $item->id) }}"
                                                class="btn btn-sm btn-gradient-success px-2 py-1">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                                </svg>
                                            </a>
                                            <button wire:click="confirmDelete({{ $item->id }})"
                                                class="btn btn-sm btn-gradient-danger px-2 py-1">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="clinic-card-body mt-2" x-show="open" x-transition>
                                        <div class="clinic-card-item">
                                            <span class="clinic-card-label">ردیف:</span>
                                            <span class="clinic-card-value">{{ $clinics->firstItem() + $index }}</span>
                                        </div>
                                        <div class="clinic-card-item">
                                            <span class="clinic-card-label">پزشکان:</span>
                                            <span class="clinic-card-value">
                                                <button class="btn btn-link text-primary p-0" @click="showDoctors = !showDoctors">
                                                    <span class="badge bg-primary-subtle text-primary">{{ $item->doctors->count() }} پزشک</span>
                                                </button>
                                                <div x-show="showDoctors" x-transition class="mt-2 p-2 border rounded shadow-sm" style="max-height: 150px; overflow-y: auto;">
                                                    @if ($item->doctors->isEmpty())
                                                        <span class="text-muted">بدون پزشک</span>
                                                    @else
                                                        @foreach ($item->doctors as $doctor)
                                                            <div class="py-1 border-bottom">
                                                                {{ $doctor->first_name . ' ' . $doctor->last_name }}
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </span>
                                        </div>
                                        <div class="clinic-card-item">
                                            <span class="clinic-card-label">تخصص‌ها:</span>
                                            <span class="clinic-card-value">
                                                <button class="btn btn-link text-primary p-0" @click="showSpecialties = !showSpecialties">
                                                    <span class="badge bg-info-subtle text-info">{{ count($item->specialty_ids ?? []) }} تخصص</span>
                                                </button>
                                                <div x-show="showSpecialties" x-transition class="mt-2 p-2 border rounded shadow-sm" style="max-height: 150px; overflow-y: auto;">
                                                    @if (empty($item->specialty_ids))
                                                        <span class="text-muted">بدون تخصص</span>
                                                    @else
                                                        @foreach ($item->specialty_ids as $specialtyId)
                                                            <div class="py-1 border-bottom">
                                                                {{ $specialties[$specialtyId] ?? 'نامشخص' }}
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </span>
                                        </div>
                                        <div class="clinic-card-item">
                                            <span class="clinic-card-label">بیمه‌ها:</span>
                                            <span class="clinic-card-value">
                                                <button class="btn btn-link text-primary p-0" @click="showInsurances = !showInsurances">
                                                    <span class="badge bg-success-subtle text-success">{{ count($item->insurance_ids ?? []) }} بیمه</span>
                                                </button>
                                                <div x-show="showInsurances" x-transition class="mt-2 p-2 border rounded shadow-sm" style="max-height: 150px; overflow-y: auto;">
                                                    @if (empty($item->insurance_ids))
                                                        <span class="text-muted">بدون بیمه</span>
                                                    @else
                                                        @foreach ($item->insurance_ids as $insuranceId)
                                                            <div class="py-1 border-bottom">
                                                                {{ $insurances[$insuranceId] ?? 'نامشخص' }}
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </span>
                                        </div>
                                        <div class="clinic-card-item">
                                            <span class="clinic-card-label">استان:</span>
                                            <span class="clinic-card-value">{{ $item->province?->name ?? '-' }}</span>
                                        </div>
                                        <div class="clinic-card-item">
                                            <span class="clinic-card-label">شهر:</span>
                                            <span class="clinic-card-value">{{ $item->city?->name ?? '-' }}</span>
                                        </div>
                                        <div class="clinic-card-item">
                                            <span class="clinic-card-label">آدرس:</span>
                                            <span class="clinic-card-value">{{ e($item->address) ?? '-' }}</span>
                                        </div>
                                        <div class="clinic-card-item">
                                            <span class="clinic-card-label">توضیحات:</span>
                                            <span class="clinic-card-value">{{ e($item->description) ?? '-' }}</span>
                                        </div>
                                        <div class="clinic-card-item">
                                            <span class="clinic-card-label">وضعیت:</span>
                                            <div class="form-check form-switch d-inline-block">
                                                <input class="form-check-input" type="checkbox" role="switch"
                                                    wire:click="toggleStatus({{ $item->id }})"
                                                    {{ $item->is_active ? 'checked' : '' }}
                                                    style="width: 3em; height: 1.5em; margin-top: 0;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <div class="d-flex justify-content-center align-items-center flex-column">
                                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-muted mb-2">
                                            <path d="M5 12h14M12 5l7 7-7 7" />
                                        </svg>
                                        <p class="text-muted fw-medium">هیچ کلینیکی یافت نشد.</p>
                                    </div>
                                </div>
                            @endforelse
                        @else
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">در حال بارگذاری...</span>
                                </div>
                            </div>
                        @endif
                    </div>
                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3 px-3 flex-wrap gap-2">
                        @if ($readyToLoad)
                            <div class="text-muted">
                                نمایش {{ $clinics->firstItem() }} تا {{ $clinics->lastItem() }}
                                از {{ $clinics->total() }} ردیف
                            </div>
                            @if ($clinics->hasPages())
                                {{ $clinics->links('livewire::bootstrap') }}
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Scripts -->
    <script>
        document.addEventListener('livewire:init', function () {
            // Re-initialize Alpine.js after Livewire updates
            Livewire.hook('morph.updated', () => {
                if (window.Alpine) {
                    window.Alpine.initTree(document.querySelector('.clinics-container'));
                }
            });
            Livewire.on('show-alert', (event) => {
                toastr[event.type](event.message);
            });
            Livewire.on('confirm-delete', (event) => {
                Swal.fire({
                    title: 'حذف کلینیک',
                    text: 'آیا مطمئن هستید که می‌خواهید این کلینیک را حذف کنید؟',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'بله، حذف کن',
                    cancelButtonText: 'خیر'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch('deleteClinicConfirmed', { id: event.id });
                    }
                });
            });
        });
    </script>
</div>
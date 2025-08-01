<div class="doctor-clinics-container">
  <div class="container py-2 mt-3" dir="rtl" wire:init="loadClinics">
    <div class="glass-header text-white p-2 rounded-2 mb-4 shadow-lg">
      <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3 w-100">
        <div class="d-flex flex-column flex-md-row gap-2 w-100 align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-3">
            <h1 class="m-0 h4 font-thin text-nowrap mb-3 mb-md-0">مطب‌های من</h1>
          </div>
          <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2">
            <div class="d-flex gap-2 flex-shrink-0 justify-content-center">
              <div class="search-container position-relative" style="max-width: 100%;">
                <input type="text"
                  class="form-control search-input border-0 shadow-none bg-white text-dark ps-4 rounded-2 text-start"
                  wire:model.live="search" placeholder="جستجو در مطب‌ها..."
                  style="padding-right: 20px; text-align: right; direction: rtl;">
                <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-2"
                  style="z-index: 5; top: 50%; right: 8px;">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                    stroke-width="2">
                    <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
                  </svg>
                </span>
              </div>
              <a href="{{ route('dr.panel.clinics.create') }}"
                class="btn btn-gradient-success btn-gradient-success-576 rounded-1 px-3 py-1 d-flex align-items-center gap-1">
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
              <button class="btn btn-sm btn-primary" type="button" wire:click="confirmGroupDelete"
                wire:loading.attr="disabled" id="group-action-btn">
                <span wire:loading.remove>اجرا</span>
                <span wire:loading>در حال اجرا...</span>
              </button>
            </div>
          </div>
          <!-- Desktop Table View -->
          <div class="table-responsive text-nowrap d-none d-md-block">
            <table class="table table-hover w-100 m-0">
              <thead>
                <tr>
                  <th class="text-center align-middle" style="width: 40px;">
                    <div class="d-flex justify-content-center align-items-center">
                      <input type="checkbox" wire:model.live="selectAll" class="form-check-input m-0 align-middle">
                    </div>
                  </th>
                  <th class="text-center align-middle" style="width: 60px;">ردیف</th>
                  <th class="align-middle">نام مطب</th>
                  <th class="align-middle">استان</th>
                  <th class="align-middle">شهر</th>
                  <th class="align-middle">آدرس</th>
                  <th class="align-middle">توضیحات</th>
                  <th class="align-middle">تعرفه نسخه</th>
                  <th class="text-center align-middle" style="width: 100px;">وضعیت</th>
                  <th class="text-center align-middle" style="width: 120px;">عملیات</th>
                </tr>
              </thead>
              <tbody>
                @if ($readyToLoad)
                  @forelse ($clinics as $index => $item)
                    <tr class="align-middle">
                      <td class="text-center">
                        <div class="d-flex justify-content-center align-items-center">
                          <input type="checkbox" wire:model.live="selectedClinics" value="{{ $item->id }}"
                            class="form-check-input m-0 align-middle">
                        </div>
                      </td>
                      <td class="text-center">{{ $clinics->firstItem() + $index }}</td>
                      <td>{{ $item->name }}</td>
                      <td>{{ $item->province ? $item->province->name : 'نامشخص' }}</td>
                      <td>{{ $item->city ? $item->city->name : 'نامشخص' }}</td>
                      <td>{{ $item->address ?? '---' }}</td>
                      <td>{{ $item->description ?? '---' }}</td>
                      <td>
                        {{ $item->prescription_tariff !== null ? number_format($item->prescription_tariff) . ' تومان' : '---' }}
                      </td>
                      <td class="text-center">
                        <span class="badge {{ $item->is_active ? 'bg-success' : 'bg-danger' }}">
                          {{ $item->is_active ? 'فعال' : 'غیرفعال' }}
                        </span>
                      </td>
                      <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                          <a href="{{ route('dr.panel.clinics.edit', $item->id) }}"
                            class="btn btn-sm btn-gradient-success px-2 py-1">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                              stroke-width="2">
                              <path
                                d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                          </a>
                          <button wire:click="confirmDelete({{ $item->id }})"
                            class="btn btn-sm btn-gradient-danger px-2 py-1">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                              stroke-width="2">
                              <path
                                d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                            </svg>
                          </button>
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="10" class="text-center py-4">
                        <div class="d-flex justify-content-center align-items-center flex-column">
                          <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" class="text-muted mb-2">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                          </svg>
                          <p class="text-muted fw-medium">هیچ مطبی یافت نشد.</p>
                        </div>
                      </td>
                    </tr>
                  @endforelse
                @else
                  <tr>
                    <td colspan="10" class="text-center py-4">
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
          <div class="notes-cards d-md-none">
            @if ($readyToLoad)
              @forelse ($clinics as $index => $item)
                <div class="note-card mb-3">
                  <div class="note-card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                      <input type="checkbox" wire:model.live="selectedClinics" value="{{ $item->id }}"
                        class="form-check-input m-0 align-middle">
                      <span class="badge bg-primary-subtle text-primary">
                        {{ $item->is_active ? 'تایید شده' : 'تایید نشده' }}
                      </span>
                    </div>
                    <div class="d-flex gap-1">
                      <a href="{{ route('dr.panel.clinics.edit', $item->id) }}"
                        class="btn btn-sm btn-gradient-success  px-2 py-1">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2">
                          <path
                            d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                      </a>
                      <button wire:click="confirmDelete({{ $item->id }})"
                        class="btn btn-sm btn-gradient-danger  px-2 py-1">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2">
                          <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                        </svg>
                      </button>
                    </div>
                  </div>
                  <div class="note-card-body">
                    <div class="note-card-item">
                      <span class="note-card-label">استان:</span>
                      <span class="note-card-value">{{ $item->province ? $item->province->name : 'نامشخص' }}</span>
                    </div>
                    <div class="note-card-item">
                      <span class="note-card-label">شهر:</span>
                      <span class="note-card-value">{{ $item->city ? $item->city->name : 'نامشخص' }}</span>
                    </div>
                    <div class="note-card-item">
                      <span class="note-card-label">آدرس:</span>
                      <span class="note-card-value">{{ $item->address ?? '---' }}</span>
                    </div>
                    <div class="note-card-item">
                      <span class="note-card-label">توضیحات:</span>
                      <span class="note-card-value">{{ $item->description ?? '---' }}</span>
                    </div>
                    <div class="note-card-item">
                      <span class="note-card-label">تعرفه نسخه:</span>
                      <span
                        class="note-card-value">{{ $item->prescription_tariff !== null ? number_format($item->prescription_tariff) . ' تومان' : '---' }}</span>
                    </div>
                    <div class="note-card-item">
                      <span class="note-card-label">وضعیت:</span>
                      <span class="badge {{ $item->is_active ? 'bg-success' : 'bg-danger' }}">
                        {{ $item->is_active ? 'فعال' : 'غیرفعال' }}
                      </span>
                    </div>
                  </div>
                </div>
              @empty
                <div class="text-center py-4">
                  <div class="d-flex justify-content-center align-items-center flex-column">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2" class="text-muted mb-2">
                      <path d="M5 12h14M12 5l7 7-7 7" />
                    </svg>
                    <p class="text-muted fw-medium">هیچ مطبی یافت نشد.</p>
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
    <script>
      document.addEventListener('livewire:init', function() {
        Livewire.on('show-alert', (event) => {
          toastr[event.type](event.message);
        });
        Livewire.on('confirm-delete', (event) => {
          Swal.fire({
            title: 'حذف مطب',
            text: 'آیا مطمئن هستید که می‌خواهید این مطب را حذف کنید؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'بله، حذف کن',
            cancelButtonText: 'خیر'
          }).then((result) => {
            if (result.isConfirmed) {
              Livewire.dispatch('deleteClinicConfirmed', {
                id: event.id
              });
            }
          });
        });
        Livewire.on('confirm-group-delete', () => {
          Swal.fire({
            title: 'حذف گروهی',
            text: 'آیا مطمئن هستید که می‌خواهید همه موارد انتخاب شده حذف شوند؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'بله، حذف کن',
            cancelButtonText: 'خیر'
          }).then((result) => {
            if (result.isConfirmed) {
              Livewire.dispatch('executeGroupAction');
            }
          });
        });
      });
    </script>
  </div>

<div class="container-fluid py-2" dir="rtl" wire:init="loadSpecialties">
  <div class="glass-header text-white p-3 rounded-3 mb-5 shadow-lg">
    <div class="d-flex flex-column flex-md-row gap-2 w-100 align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-3">
        <h1 class="m-0 h4 font-thin text-nowrap mb-3 mb-md-0">مدیریت تخصص‌ها</h1>
      </div>
      <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2 w-100">
        <div class="d-flex gap-2 flex-shrink-0 justify-content-center w-100 flex-column flex-md-row">
          <div class="search-container position-relative flex-grow-1 mb-2 mb-md-0 w-100">
            <input type="text"
              class="form-control search-input border-0 shadow-none bg-white text-dark ps-4 rounded-2 text-start w-100"
              wire:model.live="search" placeholder="جستجو بر اساس نام یا توضیحات..."
              style="padding-right: 20px; text-align: right; direction: rtl; width: 100%; max-width: 400px; min-width: 200px;">
            <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-2"
              style="z-index: 5; top: 50%; right: 8px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="2">
                <path d="M11 3a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm5-1l5 5" />
              </svg>
            </span>
          </div>
          <select class="form-select form-select-sm w-100 mb-2 mb-md-0" style="min-width: 0;"
            wire:model.live="statusFilter">
            <option value="">همه وضعیت‌ها</option>
            <option value="active">فقط فعال</option>
            <option value="inactive">فقط غیرفعال</option>
          </select>
          <a href="{{ route('admin.panel.specialties.create') }}"
            class="btn btn-gradient-success btn-gradient-success-576 rounded-1 px-3 py-1 d-flex align-items-center gap-1 w-100 w-md-auto justify-content-center justify-content-md-start">
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

  <div class="container-fluid px-0">
    <div class="card shadow-sm">
      <div class="card-body p-0">
        <!-- Group Actions -->
        <div class="group-actions p-2 border-bottom" x-data="{ show: false }"
          x-show="$wire.selectedSpecialties.length > 0 || $wire.applyToAllFiltered">
          <div class="d-flex align-items-center gap-2 justify-content-end">
            <select class="form-select form-select-sm" style="max-width: 200px;" wire:model="groupAction">
              <option value="">عملیات گروهی</option>
              <option value="delete">حذف انتخاب شده‌ها</option>
              <option value="status_active">فعال کردن</option>
              <option value="status_inactive">غیرفعال کردن</option>
            </select>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="checkbox" id="applyToAllFiltered" wire:model="applyToAllFiltered">
              <label class="form-check-label" for="applyToAllFiltered">
                اعمال روی همه نتایج فیلترشده ({{ $totalFilteredCount ?? 0 }})
              </label>
            </div>
            <button class="btn btn-sm btn-primary" wire:click="executeGroupAction" wire:loading.attr="disabled">
              <span wire:loading.remove>اجرا</span>
              <span wire:loading>در حال اجرا...</span>
            </button>
          </div>
        </div>
        <!-- Desktop View -->
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
                <th class="align-middle">نام</th>
                <th class="align-middle">توضیحات</th>
                <th class="text-center align-middle" style="width: 100px;">وضعیت</th>
                <th class="text-center align-middle" style="width: 150px;">عملیات</th>
              </tr>
            </thead>
            <tbody>
              @if ($readyToLoad)
                @forelse ($specialties as $index => $item)
                  <tr class="align-middle">
                    <td class="text-center">
                      <div class="d-flex justify-content-center align-items-center">
                        <input type="checkbox" wire:model.live="selectedSpecialties" value="{{ $item->id }}"
                          class="form-check-input m-0 align-middle">
                      </div>
                    </td>
                    <td class="text-center">{{ $specialties->firstItem() + $index }}</td>
                    <td class="align-middle">{{ $item->name }}</td>
                    <td class="align-middle">{{ $item->description }}</td>
                    <td class="text-center align-middle">
                      <button wire:click="toggleStatus({{ $item->id }})"
                        class="badge {{ $item->status ? 'bg-success' : 'bg-danger' }} border-0 cursor-pointer">
                        {{ $item->status ? 'فعال' : 'غیرفعال' }}
                      </button>
                    </td>
                    <td class="text-center align-middle">
                      <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('admin.panel.specialties.edit', $item->id) }}"
                          class="btn btn-gradient-success rounded-pill px-3">
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path
                              d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                          </svg>
                        </a>
                        <button wire:click="confirmDelete({{ $item->id }})"
                          class="btn btn-gradient-danger rounded-pill px-3">
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="text-center py-4">
                      <div class="d-flex justify-content-center align-items-center flex-column">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                          stroke-width="2" class="text-muted mb-2">
                          <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                        <p class="text-muted fw-medium">هیچ تخصصی یافت نشد.</p>
                      </div>
                    </td>
                  </tr>
                @endforelse
              @else
                <tr>
                  <td colspan="6" class="text-center py-4">
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
            @forelse ($specialties as $index => $item)
              <div class="note-card mb-2" x-data="{ open: false }">
                <div class="note-card-header d-flex justify-content-between align-items-center px-2 py-2"
                  @click="open = !open" style="cursor:pointer;">
                  <span class="fw-bold">{{ $item->name }} <span
                      class="text-muted">({{ $specialties->firstItem() + $index }})</span></span>
                  <svg :class="{ 'rotate-180': open }" width="20" height="20" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" style="transition: transform 0.2s;">
                    <path d="M6 9l6 6 6-6" />
                  </svg>
                </div>
                <div class="note-card-body px-2 py-2" x-show="open" x-transition>
                  <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                    <span class="note-card-label">توضیحات:</span>
                    <span class="note-card-value">{{ $item->description }}</span>
                  </div>
                  <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                    <span class="note-card-label">وضعیت:</span>
                    <button wire:click="toggleStatus({{ $item->id }})"
                      class="badge {{ $item->status ? 'bg-success' : 'bg-danger' }} border-0 cursor-pointer">
                      {{ $item->status ? 'فعال' : 'غیرفعال' }}
                    </button>
                  </div>
                  <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                    <a href="{{ route('admin.panel.specialties.edit', $item->id) }}"
                      class="btn btn-gradient-success btn-sm rounded-pill px-3">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path
                          d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                      </svg>
                    </a>
                    <button wire:click="confirmDelete({{ $item->id }})"
                      class="btn btn-gradient-danger btn-sm rounded-pill px-3">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                      </svg>
                    </button>
                  </div>
                  <div class="note-card-item d-flex justify-content-between align-items-center py-1">
                    <input type="checkbox" wire:model.live="selectedSpecialties" value="{{ $item->id }}"
                      class="form-check-input m-0 align-middle">
                    <span class="note-card-label">انتخاب</span>
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
                  <p class="text-muted fw-medium">هیچ تخصصی یافت نشد.</p>
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
        <!-- Pagination and Result Count -->
        <div class="d-flex justify-content-between align-items-center mt-4 px-4 flex-wrap gap-3">
          <div class="text-muted">نمایش {{ $specialties ? $specialties->firstItem() : 0 }} تا
            {{ $specialties ? $specialties->lastItem() : 0 }} از {{ $specialties ? $specialties->total() : 0 }} ردیف
          </div>
          @if ($specialties && $specialties->hasPages())
            <div class="pagination-container">
              {{ $specialties->onEachSide(1)->links('livewire::bootstrap') }}
            </div>
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
          title: 'حذف تخصص',
          text: 'آیا مطمئن هستید که می‌خواهید این تخصص را حذف کنید؟',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#ef4444',
          cancelButtonColor: '#6b7280',
          confirmButtonText: 'بله، حذف کن',
          cancelButtonText: 'خیر'
        }).then((result) => {
          if (result.isConfirmed) {
            Livewire.dispatch('deleteSpecialtyConfirmed', {
              id: event.id
            });
          }
        });
      });
      Livewire.on('confirm-delete-selected', function(data) {
        let text = data.allFiltered ?
          'آیا از حذف همه تخصص‌های فیلترشده مطمئن هستید؟ این عملیات غیرقابل بازگشت است.' :
          'آیا از حذف تخصص‌های انتخاب شده مطمئن هستید؟ این عملیات غیرقابل بازگشت است.';
        Swal.fire({
          title: 'تایید حذف گروهی',
          text: text,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'بله، حذف شود',
          cancelButtonText: 'لغو',
          reverseButtons: true
        }).then((result) => {
          if (result.isConfirmed) {
            if (data.allFiltered) {
              Livewire.dispatch('deleteSelectedConfirmed', 'allFiltered');
            } else {
              Livewire.dispatch('deleteSelectedConfirmed');
            }
          }
        });
      });
    });
  </script>
</div>

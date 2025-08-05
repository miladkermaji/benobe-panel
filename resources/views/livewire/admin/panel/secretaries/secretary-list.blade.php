<div class="secretaries-container" x-data="{ mobileSearchOpen: false }">
  <div class="container py-2 mt-3" dir="rtl" wire:init="loadSecretaries">
    <!-- Header -->
    <header class="glass-header text-white p-3 rounded-3 mb-3 shadow-lg">
      <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 w-100">
        <!-- Title Section -->
        <div class="d-flex align-items-center gap-2 flex-shrink-0 w-md-100 justify-content-between">
          <h2 class="mb-0 fw-bold fs-5">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              class="header-icon">
              <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
              <circle cx="12" cy="7" r="4" />
            </svg>
            مدیریت منشی‌ها
          </h2>
          <!-- Mobile Toggle Button -->
          <button class="btn btn-link text-white p-0 d-md-none mobile-toggle-btn" type="button"
            @click="mobileSearchOpen = !mobileSearchOpen" :aria-expanded="mobileSearchOpen">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              class="toggle-icon" :class="{ 'rotate-180': mobileSearchOpen }">
              <path d="M6 9l6 6 6-6" />
            </svg>
          </button>
        </div>
        <!-- Mobile Collapsible Section -->
        <div x-show="mobileSearchOpen" x-transition:enter="transition ease-out duration-300"
          x-transition:enter-start="opacity-0 transform -translate-y-2"
          x-transition:enter-end="opacity-100 transform translate-y-0"
          x-transition:leave="transition ease-in duration-200"
          x-transition:leave-start="opacity-100 transform translate-y-0"
          x-transition:leave-end="opacity-0 transform -translate-y-2" class="d-md-none w-100">
          <div class="d-flex flex-column gap-2">
            <div class="search-box position-relative">
              <input type="text" wire:model.live="search" class="form-control ps-5"
                placeholder="جستجو بر اساس نام، نام خانوادگی، موبایل یا کدملی...">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" class="search-icon">
                <circle cx="11" cy="11" r="8" />
                <path d="M21 21l-4.35-4.35" />
              </svg>
            </div>
            <select class="form-select form-select-sm" wire:model.live="statusFilter">
              <option value="">همه وضعیت‌ها</option>
              <option value="active">فقط فعال</option>
              <option value="inactive">فقط غیرفعال</option>
            </select>
            <div class="d-flex align-items-center gap-2 justify-content-between">
              <a href="{{ route('admin.panel.secretaries.create') }}"
                class="btn btn-success px-3 py-1 d-flex align-items-center gap-1 flex-shrink-0">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="#fff" stroke="#fff" stroke-width="2">
                  <path d="M12 5v14M5 12h14" />
                </svg>
                <span class="text-white">افزودن</span>
              </a>
              <span class="badge bg-white text-primary px-2 py-1 fw-medium flex-shrink-0">
                {{ $readyToLoad ? $secretaries->count() : 0 }}
              </span>
            </div>
          </div>
        </div>
        <!-- Desktop Search and Actions -->
        <div class="d-none d-md-flex align-items-center gap-3 ms-auto">
          <div class="search-box position-relative">
            <input type="text" wire:model.live="search" class="form-control ps-5"
              placeholder="جستجو بر اساس نام، نام خانوادگی، موبایل یا کدملی...">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              class="search-icon">
              <circle cx="11" cy="11" r="8" />
              <path d="M21 21l-4.35-4.35" />
            </svg>
          </div>
          <select class="form-select form-select-sm" style="min-width: 150px;" wire:model.live="statusFilter">
            <option value="">همه وضعیت‌ها</option>
            <option value="active">فقط فعال</option>
            <option value="inactive">فقط غیرفعال</option>
          </select>
          <a href="{{ route('admin.panel.secretaries.create') }}"
            class="btn btn-success px-3 py-1 d-flex align-items-center gap-1 flex-shrink-0">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="#fff" stroke="#fff" stroke-width="2">
              <path d="M12 5v14M5 12h14" />
            </svg>
            <span class="text-white">افزودن</span>
          </a>
          <span class="badge bg-white text-primary px-2 py-1 fw-medium flex-shrink-0">
            {{ $readyToLoad ? $secretaries->count() : 0 }}
          </span>
        </div>
      </div>
    </header>
    <div class="container-fluid px-0">
      <div class="card shadow-sm rounded-2">
        <div class="card-body p-0">
          <!-- Group Actions -->
          <div class="group-actions p-2 border-bottom" x-data="{ show: false }"
            x-show="$wire.selectedSecretaries.length > 0 || $wire.applyToAllFiltered">
            <div class="d-flex align-items-center gap-2 justify-content-end">
              <select class="form-select form-select-sm" style="max-width: 200px;" wire:model="groupAction">
                <option value="">عملیات گروهی</option>
                <option value="delete">حذف انتخاب شده‌ها</option>
                <option value="set_active">فعال‌سازی</option>
                <option value="unset_active">غیرفعال‌سازی</option>
              </select>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="applyToAllFiltered"
                  wire:model="applyToAllFiltered">
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
          <!-- Desktop Table View -->
          <div class="table-responsive text-nowrap d-none d-md-block">
            <table class="table table-hover w-100 m-0">
              <thead>
                <tr>
                  <th class="text-center align-middle" style="width: 40px;">
                    <input type="checkbox" wire:model.live="selectAll" class="form-check-input m-0 align-middle">
                  </th>
                  <th class="text-center align-middle" style="width: 60px;">ردیف</th>
                  <th class="align-middle">نام پزشک</th>
                  <th class="align-middle">نام منشی</th>
                  <th class="align-middle">موبایل</th>
                  <th class="align-middle">کلینیک</th>
                  <th class="text-center align-middle" style="width: 100px;">وضعیت</th>
                  <th class="text-center align-middle" style="width: 150px;">عملیات</th>
                  <th class="text-center align-middle" style="width: 40px;"></th>
                </tr>
              </thead>
              <tbody>
                @if ($readyToLoad)
                  @php $rowIndex = 0; @endphp
                  @foreach ($doctors as $doctor)
              <tbody x-data="{ open: false }">
                <tr style="background: #f5f7fa; border-top: 2px solid #b3c2d1; cursor:pointer;" @click="open = !open">
                  <td colspan="8" class="py-2 px-3 fw-bold text-primary" style="font-size: 1.05rem;">
                    <svg width="18" height="18" fill="none" stroke="#0d6efd" stroke-width="2"
                      style="vertical-align: middle; margin-left: 6px;">
                      <circle cx="9" cy="9" r="8" />
                      <path d="M9 5v4l3 2" />
                    </svg>
                    {{ $doctor->first_name . ' ' . $doctor->last_name }}
                  </td>
                  <td class="text-center align-middle" style="width: 40px; padding: 0;">
                    <span class="d-flex justify-content-center align-items-center w-100 h-100 p-0 m-0">
                      <svg width="20" height="20" fill="none" stroke="#0d6efd" stroke-width="2"
                        :style="open ? 'display: block; transition: transform 0.2s; transform: rotate(180deg);' :
                            'display: block; transition: transform 0.2s;'">
                        <path d="M6 9l6 6 6-6" />
                      </svg>
                    </span>
                  </td>
                </tr>
                @foreach ($doctor->secretaries as $secretary)
                  <tr x-show="open" x-transition style="border-bottom: 1px solid #e3e6ea; background: #fff;">
                    <td class="text-center align-middle">
                      <input type="checkbox" wire:model.live="selectedSecretaries" value="{{ $secretary->id }}"
                        class="form-check-input m-0 align-middle">
                    </td>
                    <td class="text-center align-middle">{{ ++$rowIndex }}</td>
                    <td class="align-middle">
                      {{ $doctor->first_name . ' ' . $doctor->last_name }}
                    </td>
                    <td class="align-middle">
                      {{ $secretary->first_name . ' ' . $secretary->last_name }}
                    </td>
                    <td class="align-middle">
                      {{ $secretary->mobile }}
                    </td>
                    <td class="align-middle">
                      {{ $secretary->clinic ? $secretary->clinic->name : '-' }}
                    </td>
                    <td class="text-center align-middle">
                      <button wire:click="confirmToggleActive({{ $secretary->id }})"
                        class="badge {{ $secretary->is_active ? 'bg-success' : 'bg-danger' }} border-0 cursor-pointer">
                        {{ $secretary->is_active ? 'فعال' : 'غیرفعال' }}
                      </button>
                    </td>
                    <td class="text-center align-middle">
                      <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('admin.panel.secretaries.edit', $secretary->id) }}"
                          class="btn btn-gradient-primary rounded-pill px-3">
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path
                              d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                          </svg>
                        </a>
                        <button wire:click="confirmDelete({{ $secretary->id }})"
                          class="btn btn-gradient-danger rounded-pill px-3">
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                          </svg>
                        </button>
                      </div>
                    </td>
                    <td class="text-center align-middle"></td>
                  </tr>
                @endforeach
              </tbody>
              @endforeach
              @if ($rowIndex === 0)
                <tr>
                  <td colspan="8" class="text-center py-4">
                    <div class="d-flex justify-content-center align-items-center flex-column">
                      <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" class="text-muted mb-2">
                        <path d="M5 12h14M12 5l7 7-7 7" />
                      </svg>
                      <p class="text-muted fw-medium">هیچ منشی‌ای یافت نشد.</p>
                    </div>
                  </td>
                </tr>
              @endif
            @else
              <tr>
                <td colspan="8" class="text-center py-4">
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
          <div class="secretaries-cards d-md-none">
            @if ($readyToLoad)
              @foreach ($doctors as $doctor)
                <div class="mb-3 p-2 rounded-3 shadow-sm" x-data="{ open: false }"
                  style="border: 2px solid #b3c2d1; background: #f5f7fa;">
                  <div class="fw-bold text-primary mb-2 d-flex align-items-center justify-content-between"
                    style="font-size: 1.08rem; cursor:pointer;" @click="open = !open">
                    <span>
                      <svg width="18" height="18" fill="none" stroke="#0d6efd" stroke-width="2"
                        style="vertical-align: middle; margin-left: 6px;">
                        <circle cx="9" cy="9" r="8" />
                        <path d="M9 5v4l3 2" />
                      </svg>
                      {{ $doctor->first_name . ' ' . $doctor->last_name }}
                    </span>
                    <svg :class="{ 'rotate-180': open }" width="20" height="20" viewBox="0 0 24 24"
                      fill="none" stroke="currentColor" stroke-width="2" style="transition: transform 0.2s;">
                      <path d="M6 9l6 6 6-6" />
                    </svg>
                  </div>
                  <div x-show="open" x-transition>
                    @foreach ($doctor->secretaries as $secretary)
                      <div class="secretary-card mb-2 p-2 rounded-2"
                        style="background: #fff; border: 1px solid #e3e6ea;">
                        <div class="secretary-card-header d-flex justify-content-between align-items-center px-2 py-2"
                          style="cursor:pointer;">
                          <div class="d-flex align-items-center gap-2">
                            <input type="checkbox" wire:model.live="selectedSecretaries"
                              value="{{ $secretary->id }}" class="form-check-input m-0" @click.stop>
                            <span class="fw-bold">
                              {{ $secretary->first_name . ' ' . $secretary->last_name }}
                              <span class="text-muted">({{ $doctor->first_name . ' ' . $doctor->last_name }})</span>
                            </span>
                          </div>
                        </div>
                        <div class="secretary-card-body px-2 py-2">
                          <div class="secretary-card-item d-flex justify-content-between align-items-center py-1">
                            <span class="secretary-card-label">نام پزشک:</span>
                            <span
                              class="secretary-card-value">{{ $doctor->first_name . ' ' . $doctor->last_name }}</span>
                          </div>
                          <div class="secretary-card-item d-flex justify-content-between align-items-center py-1">
                            <span class="secretary-card-label">نام منشی:</span>
                            <span
                              class="secretary-card-value">{{ $secretary->first_name . ' ' . $secretary->last_name }}</span>
                          </div>
                          <div class="secretary-card-item d-flex justify-content-between align-items-center py-1">
                            <span class="secretary-card-label">موبایل:</span>
                            <span class="secretary-card-value">{{ $secretary->mobile }}</span>
                          </div>
                          <div class="secretary-card-item d-flex justify-content-between align-items-center py-1">
                            <span class="secretary-card-label">کلینیک:</span>
                            <span
                              class="secretary-card-value">{{ $secretary->clinic ? $secretary->clinic->name : '-' }}</span>
                          </div>
                          <div class="secretary-card-item d-flex justify-content-between align-items-center py-1">
                            <span class="secretary-card-label">وضعیت:</span>
                            <button wire:click="confirmToggleActive({{ $secretary->id }})"
                              class="badge {{ $secretary->is_active ? 'bg-success' : 'bg-danger' }} border-0 cursor-pointer">
                              {{ $secretary->is_active ? 'فعال' : 'غیرفعال' }}
                            </button>
                          </div>
                          <div class="secretary-card-item d-flex justify-content-between align-items-center py-1">
                            <span class="secretary-card-label">عملیات:</span>
                            <div class="d-flex gap-2">
                              <a href="{{ route('admin.panel.secretaries.edit', $secretary->id) }}"
                                class="btn btn-gradient-primary rounded-pill px-3">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                  stroke="currentColor" stroke-width="2">
                                  <path
                                    d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                </svg>
                              </a>
                              <button wire:click="confirmDelete({{ $secretary->id }})"
                                class="btn btn-gradient-danger rounded-pill px-3">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                  stroke="currentColor" stroke-width="2">
                                  <path
                                    d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                                </svg>
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  </div>
                </div>
              @endforeach
            @else
              <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">در حال بارگذاری...</span>
                </div>
              </div>
            @endif
          </div>
          <!-- Pagination & Counter -->
          <div class="d-flex justify-content-between align-items-center px-4 flex-wrap gap-3">
            <div class="text-muted">تعداد کل: {{ $totalFilteredCount }}</div>
            {{-- صفحه‌بندی اگر نیاز بود اضافه شود --}}
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
            title: 'حذف منشی',
            text: 'آیا مطمئن هستید که می‌خواهید این منشی را حذف کنید؟',

            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'بله، حذف کن',
            cancelButtonText: 'خیر'
          }).then((result) => {
            if (result.isConfirmed) {
              Livewire.dispatch('deleteSecretaryConfirmed', {
                id: event.id
              });
            }
          });
        });

        Livewire.on('confirm-toggle-active', (event) => {
          Swal.fire({
            title: event.action + ' منشی',
            text: 'آیا مطمئن هستید که می‌خواهید وضعیت منشی ' + event.name + ' را ' + event.action + ' کنید؟',

            showCancelButton: true,
            confirmButtonColor: '#1deb3c',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'بله',
            cancelButtonText: 'خیر'
          }).then((result) => {
            if (result.isConfirmed) {
              Livewire.dispatch('toggleActiveConfirmed', {
                id: event.id
              });
            }
          });
        });

        Livewire.on('confirm-delete-selected', function(data) {
          let text = data.allFiltered ?
            'آیا از حذف همه منشی‌های فیلترشده مطمئن هستید؟ این عملیات غیرقابل بازگشت است.' :
            'آیا از حذف منشی‌های انتخاب شده مطمئن هستید؟ این عملیات غیرقابل بازگشت است.';
          Swal.fire({
            title: 'تایید حذف گروهی',
            text: text,

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
</div>

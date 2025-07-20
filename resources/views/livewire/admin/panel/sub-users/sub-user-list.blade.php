@if (session('success'))
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      toastr.success(@json(session('success')));
    });
  </script>
@endif
<div class="subusers-container">
  <div class="container py-2 mt-3" dir="rtl" wire:init="loadSubUsers">
    <div class="glass-header text-white p-2 rounded-2 mb-4 shadow-lg">
      <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3 w-100">
        <div class="d-flex flex-column flex-md-row gap-2 w-100 align-items-center justify-content-between">
          <div class="d-flex align-items-center gap-3">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              class="header-icon">
              <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
              <circle cx="12" cy="7" r="4" />
            </svg>
            <h1 class="m-0 h4 font-thin text-nowrap mb-3 mb-md-0">مدیریت کاربران زیرمجموعه</h1>
          </div>
          <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2 w-100">
            <div class="d-flex gap-2 flex-shrink-0 justify-content-center w-100 flex-column flex-md-row">
              <div class="search-container position-relative flex-grow-1 mb-2 mb-md-0 w-100">
                <input type="text"
                  class="form-control search-input border-0 shadow-none bg-white text-dark ps-4 rounded-2 text-start w-100"
                  wire:model.live="search" placeholder="جستجو بر اساس نام، نام خانوادگی یا موبایل..."
                  style="padding-right: 20px; text-align: right; direction: rtl; width: 100%; max-width: 400px; min-width: 200px;">
                <span class="search-icon position-absolute top-50 start-0 translate-middle-y ms-2"
                  style="z-index: 5; top: 50%; right: 8px;">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6b7280"
                    stroke-width="2">
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
              <a href="{{ route('admin.panel.sub-users.create') }}"
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
    </div>
    <div class="container-fluid px-0">
      <div class="card shadow-sm rounded-2">
        <div class="card-body p-0">
          <!-- Group Actions -->
          <div class="group-actions p-2 border-bottom" x-data="{ show: false }"
            x-show="$wire.selectedSubUsers.length > 0 || $wire.applyToAllFiltered">
            <div class="d-flex align-items-center gap-2 justify-content-end">
              <select class="form-select form-select-sm" style="max-width: 200px;" wire:model="groupAction">
                <option value="">عملیات گروهی</option>
                <option value="delete">حذف انتخاب شده‌ها</option>
                <option value="status_active">فعال‌سازی</option>
                <option value="status_inactive">غیرفعال‌سازی</option>
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
                  <th class="align-middle">نام کاربر</th>
                  <th class="align-middle">موبایل</th>
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
                  <td colspan="7" class="py-2 px-3 fw-bold text-primary" style="font-size: 1.05rem;">
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
                @foreach ($doctor->subUsers as $subUser)
                  <tr x-show="open" x-transition style="border-bottom: 1px solid #e3e6ea; background: #fff;">
                    <td class="text-center align-middle">
                      <input type="checkbox" wire:model.live="selectedSubUsers" value="{{ $subUser->id }}"
                        class="form-check-input m-0 align-middle">
                    </td>
                    <td class="text-center align-middle">{{ ++$rowIndex }}</td>
                    <td class="align-middle">{{ $doctor->first_name . ' ' . $doctor->last_name }}</td>
                    <td class="align-middle">
                      {{ $subUser->subuserable->first_name . ' ' . $subUser->subuserable->last_name }}</td>
                    <td class="align-middle">{{ $subUser->subuserable->mobile }}</td>
                    <td class="text-center align-middle">
                      <button wire:click="toggleStatus({{ $subUser->id }})"
                        class="badge {{ $subUser->status === 'active' ? 'bg-success' : 'bg-danger' }} border-0 cursor-pointer">
                        {{ $subUser->status === 'active' ? 'فعال' : 'غیرفعال' }}
                      </button>
                    </td>
                    <td class="text-center align-middle">
                      <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('admin.panel.sub-users.edit', $subUser->id) }}"
                          class="btn btn-gradient-success rounded-pill px-3">
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path
                              d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                          </svg>
                        </a>
                        <button wire:click="confirmDelete({{ $subUser->id }})"
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
                      <p class="text-muted fw-medium">هیچ کاربر زیرمجموعه‌ای یافت نشد.</p>
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
          <div class="subusers-cards d-md-none">
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
                    @foreach ($doctor->subUsers as $subUser)
                      <div class="subuser-card mb-2 p-2 rounded-2"
                        style="background: #fff; border: 1px solid #e3e6ea;">
                        <div class="subuser-card-header d-flex justify-content-between align-items-center px-2 py-2"
                          style="cursor:pointer;">
                          <span class="fw-bold">
                            {{ $subUser->subuserable->first_name . ' ' . $subUser->subuserable->last_name }}
                            <span class="text-muted">({{ $doctor->first_name . ' ' . $doctor->last_name }})</span>
                          </span>
                        </div>
                        <div class="subuser-card-body px-2 py-2">
                          <div class="subuser-card-item d-flex justify-content-between align-items-center py-1">
                            <span class="subuser-card-label">نام پزشک:</span>
                            <span
                              class="subuser-card-value">{{ $doctor->first_name . ' ' . $doctor->last_name }}</span>
                          </div>
                          <div class="subuser-card-item d-flex justify-content-between align-items-center py-1">
                            <span class="subuser-card-label">نام کاربر:</span>
                            <span
                              class="subuser-card-value">{{ $subUser->subuserable->first_name . ' ' . $subUser->subuserable->last_name }}</span>
                          </div>
                          <div class="subuser-card-item d-flex justify-content-between align-items-center py-1">
                            <span class="subuser-card-label">موبایل:</span>
                            <span class="subuser-card-value">{{ $subUser->subuserable->mobile }}</span>
                          </div>
                          <div class="subuser-card-item d-flex justify-content-between align-items-center py-1">
                            <span class="subuser-card-label">وضعیت:</span>
                            <button wire:click="toggleStatus({{ $subUser->id }})"
                              class="badge {{ $subUser->status === 'active' ? 'bg-success' : 'bg-danger' }} border-0 cursor-pointer">
                              {{ $subUser->status === 'active' ? 'فعال' : 'غیرفعال' }}
                            </button>
                          </div>
                          <div class="subuser-card-item d-flex justify-content-between align-items-center py-1">
                            <span class="subuser-card-label">عملیات:</span>
                            <div class="d-flex gap-2">
                              <a href="{{ route('admin.panel.sub-users.edit', $subUser->id) }}"
                                class="btn btn-gradient-success rounded-pill px-3">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                  stroke="currentColor" stroke-width="2">
                                  <path
                                    d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                </svg>
                              </a>
                              <button wire:click="confirmDelete({{ $subUser->id }})"
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
            title: 'حذف کاربر زیرمجموعه',
            text: 'آیا مطمئن هستید که می‌خواهید این کاربر را حذف کنید؟',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'بله، حذف کن',
            cancelButtonText: 'خیر'
          }).then((result) => {
            if (result.isConfirmed) {
              Livewire.dispatch('deleteSubUser', {
                id: event.id
              });
            }
          });
        });
      });
    </script>
  </div>
</div>
